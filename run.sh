#!/bin/bash
if [ "$1" = "dev" ];
then
  echo $1
  echo $2
  echo $3
  php app.php --env=dev --url=https://dev-nacos.mabangerp.com  --node.ip=$2 --node.port=$3 --dataId=mico_service --group=V2SYSTEM_GROUP  --tenant= --server.port=8080 --management.server.port=8081 --log.path=/data/logs
fi

if [ "$1" = "test" ];
then
  echo $1
  echo $2
  echo $3
  php app.php --env=test --url=https://dev-nacos.mabangerp.com  --dataId=mico_service --group=V2SYSTEM_GROUP  --tenant= --log.path=/data/logs
fi

if [ "$1" = "prod" ];
then
  echo $1
  echo $2
  echo $3
  php app.php --env=prod --url=https://dev-nacos.mabangerp.com --dataId=mico_service --group=V2SYSTEM_GROUP --tenant= --log.path=/data/logs
fi
