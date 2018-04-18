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
    return date_format($date, 'YmdHis O');
  }

  function getStopTime($start, $duration)
  {
    $stopTimestamp = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $start);
    $stopTimestamp->modify("+$duration minute");
    return $stopTimestamp->format('YmdHis O');
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

  $programs = [];
  foreach ($xml->programs->program as $p)
  {
    $startTime = current($p->attributes())['startTime'];
    $duration = current($p->attributes())['duration'];
    $programs[] = [
      'channel' => current($p->attributes())['channel'],
      'start' => convertStartTime($startTime),
      'stop' => getStopTime($startTime, $duration),
    ];
  }
  print_r($programs);



} catch (Exception $e) {
  error_log($e->getMessage());
}




 ?>
