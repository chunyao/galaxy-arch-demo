#!/bin/bash
if [ "$1" = "dev" ];
then
  echo "dev"
  php app.php --env=dev --url=https://dev-nacos.mabangerp.com --user=nacos --password=Nacosadmin --dataId=mico_service --group=V2SYSTEM_GROUP
fi

if [ "$1" = "test" ];
then
  echo "test"
  php app.php --env=test --url=https://dev-nacos.mabangerp.com --user=nacos --password=Nacosadmin --dataId=mico_service --group=V2SYSTEM_GROUP
fi

if [ "$1" = "prod" ];
then
  echo "prod"
  php app.php --env=prod --url=https://dev-nacos.mabangerp.com --user=$NACOSUSER --password=$NACOSPASSWORD --dataId=mico_service --group=V2SYSTEM_GROUP
fi
