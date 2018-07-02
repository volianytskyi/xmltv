<?php

try {

  if(!isset($argv[1]))
  {
    throw new Exception('Need to specify the path to the file to convert: php ' . basename(__FILE__) . ' <file.xml>', 1);
  }

  $file = $argv[1];
  if(!file_exists($file))
  {
    throw new Exception($file . ' not exist', 1);
  }

  if(!function_exists('simplexml_load_file'))
  {
    throw new Exception('PHP XML modules is not installed. Run sudo apt-get install php-xml', 1);
  }

  function convertStartTime($time)
  {
    $date = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $time);
    $res = date_format($date, 'YmdHis') . ' +0000';
    return $res;
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
  echo $dom->saveXML();


} catch (Exception $e) {
  error_log($e->getMessage());
}




 ?>
