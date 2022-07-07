<?php
SeasLog::setBasePath("./data/logs");
SeasLog::setLogger("/mabang-arch-service");
require "./vendor/autoload.php";
use Elasticsearch\ClientBuilder;
$client = ClientBuilder::create()
    ->setHosts(['192.168.2.45:9200'])
    ->build();
$response = $client->info();
var_dump($response);
