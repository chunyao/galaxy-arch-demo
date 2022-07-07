<?php
SeasLog::setBasePath("./data/logs");
SeasLog::setLogger("/mabang-arch-service");

$options = ["connect" => true];
$manager =  new MongoDB\Driver\Manager('mongodb://jiagou:mtViTGogNLH2iwg@192.168.2.20:27017,192.168.2.21:27017/?replicaSet=mongos&authSource=admin',$options);
var_dump($manager);
$manager->
$filter = ['appId' => ['$gt' => 1]];
$options = [
    'sort' => ['x' => -1],
];
$query = new MongoDB\Driver\Query($filter);
$cursor = $manager->executeQuery('mdc_product_online.tb_product', $query);

