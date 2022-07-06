<?php
SeasLog::setBasePath("./data/logs");
SeasLog::setLogger("/mabang-arch-service");

$options = ["connect" => true];
$result =  new MongoDB\Driver\Manager('mongodb://jiagou@192.168.2.20:27017,192.168.2.21:27017/?replicaSet=mongos&authSource=admin', $options);
var_dump($result);