<?php

$output = '/var/www/epg/epg.xml';

try {

  function logError($msg, $log = '/var/log/stalkerd/epg_convert.log')
  {
    error_log('['. date("Y-m-d H:i:s") . ']: ' . "$msg\n", 3, $log);
  }

  if(!isset($argv[1]))
  {
    logError('Need to specify the path to the file to convert: php ' . basename(__FILE__) . ' <file.xml>');
    exit;
  }

  $file = $argv[1];
  if(!file_exists($file))
  {
    logError("$file not exist");
    exit;
  }

  if(!function_exists('simplexml_load_file'))
  {
    logError('PHP XML modules is not installed. Run sudo apt-get install php-xml');
    exit;
  }

  function convertStartTime($time)
  {
    $date = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $time);
    $res = date_format($date, 'YmdHis');
    return $res . ' +0000';
  }

  function getStopTime($start, $duration)
  {
    $stopTimestamp = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $start);
    $stopTimestamp->modify("+$duration minute");
    $res = $stopTimestamp->format('YmdHis');
    return $res . ' +0000';
  }

  $xml = simplexml_load_file($file);

  $channels = [];
  foreach ($xml->meta->channels->channel as $ch)
  {
    $channels[] = [
      'display-name' => current($ch->attributes())['name'],
      'id' => current($ch->attributes())['id']
    ];
  }

  function getAttribute(SimpleXMLElement $description, $attr)
  {
    if(!$description)
    {
      return null;
    }
    return current($description->attributes()[$attr]);
  }

  $programs = [];
  foreach ($xml->programs->program as $p)
  {
    $startTime = current($p->attributes())['startTime'];
    $duration = current($p->attributes())['duration'];
    $description = $p->descriptions->description;

    $programs[] = [
      'channel' => current($p->attributes())['channel'],
      'start' => convertStartTime($startTime),
      'stop' => getStopTime($startTime, $duration),
      'lang' => getAttribute($description, 'lang'),
      'title' => getAttribute($description, 'title'),
      'desc' => (is_array($description->synopsis)) ? current($description->synopsis) : null
    ];
  }

  $xmltv = '<?xml version="1.0" encoding="UTF-8"?><tv generator-info-name="xml tv converter script" generator-info-url="https://github.com/volyanytsky/xmltv"></tv>';

  $epg = new SimpleXMLElement($xmltv);
  foreach ($channels as $ch)
  {
    $channel = $epg->addChild('channel');
    $channel->addAttribute('id', $ch['id']);
    $displayName = $channel->addChild('display-name', $ch['display-name']);
  }

  foreach ($programs as $p)
  {
    $programme = $epg->addChild('programme');
    $programme->addAttribute('start', $p['start']);
    $programme->addAttribute('stop', $p['stop']);
    $programme->addAttribute('channel', htmlspecialchars($p['channel']));
    $title = $programme->addChild('title', htmlspecialchars($p['title']));
    $title->addAttribute('lang', $p['lang']);
    $desc = $programme->addChild('desc', htmlspecialchars($p['desc']));
    $desc->addAttribute('lang', $p['lang']);
  }


  $dom = new DOMDocument('1.0');
  $dom->preserveWhiteSpace = false;
  $dom->formatOutput = true;
  $dom->loadXML($epg->asXML());

  if(!file_exists($output))
  {
    touch($output);
  }
  
  if(is_writable($output))
  {
    file_put_contents($output, $dom->saveXML(), LOCK_EX);
  }
  else
  {
    logError("$output can't be written");
    exit;
  }

} catch (Exception $e) {
  logError($e->getMessage());
}




 ?>
