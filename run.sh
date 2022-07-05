#!/bin/bash
if [ "$1" = "dev" ];
then
  echo "dev"
  php ./mabang-arch-demo/app.php --env=dev --url=https://dev-nacos.mabangerp.com  --dataId=mico_service --group=V2SYSTEM_GROUP
fi

if [ "$1" = "test" ];
then
  echo "test"
  php ./mabang-arch-demo/app.php --env=test --url=https://dev-nacos.mabangerp.com  --dataId=mico_service --group=V2SYSTEM_GROUP
fi

if [ "$1" = "prod" ];
then
  echo "prod"
  php ./mabang-arch-demo/app.php --env=prod --url=https://dev-nacos.mabangerp.com --dataId=mico_service --group=V2SYSTEM_GROUP
fi
