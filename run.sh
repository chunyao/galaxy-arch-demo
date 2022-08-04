#!/bin/bash
if [ "$1" = "dev" ];
then
  echo "dev"
  php app.php --env=test --url=https://dev-nacos.mabangerp.com/nacos/v1/cs/configs  --dataId=mas_shopee_micro_queue_data_id --group=dev_erp_group --management.server.port=8081 --tenant=eb133ea5-2987-47be-beac-03022efd6798 --log.path=/data/logs
fi

if [ "$1" = "test" ];
then
  echo "test"
  php app.php --env=test --url=https://dev-nacos.mabangerp.com/nacos/v1/cs/configs  --dataId=mas_shopee_micro_queue_data_id --group=dev_erp_group --management.server.port=8081  --tenant=eb133ea5-2987-47be-beac-03022efd6798 --log.path=/data/logs
fi

if [ "$1" = "public" ];
then
  echo "prd"
  php app.php --env=prd --url=https://nacos.mabangerp.com/nacos/v1/cs/configs --dataId=mas_shopee_micro_queue_data_id --group=prd_erp_group --management.server.port=8081  --tenant=ae6ba4f8-dc91-42fb-b79c-4c4f7bb01482 --log.path=/data/logs
fi

if [ "$1" = "private" ];
then
  echo "prd"
  php app.php --env=prd --url=https://nacos.mabangerp.com/nacos/v1/cs/configs --dataId=mas_shopee_micro_queue_data_id --group=prd_erp_group --management.server.port=8081  --tenant=a899e072-8d9c-411f-be51-cb6a467b868c --log.path=/data/logs
fi