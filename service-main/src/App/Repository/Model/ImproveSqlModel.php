<?php

namespace App\Repository\Model;

use App\Config\ES;
use App\Config\SQL;
use Galaxy\Core\Log;
use Galaxy\Repository\Model\BaseModel;

class ImproveSqlModel extends BaseModel
{
    private string $table;

    public function __construct()
    {
        $this->table = 'StockWarehouse';
    }

    public function findByCompanyId(int $id):array
    {

        $data =  SQL::instance()->tableSuffix( $this->table,$id,100,"_" )
            ->select("id,isPurchase,remarks,stockCost,stockId,warehouseId,minPaidtime,id AS stockWarehouseIds,processWaitingQuantity,shippingQuantity,processingQuantity,fbaWaitingQuantity,allotShippingQuantity,stockQuantity,maxPurchaseQuantity,minPurchaseQuantity,forecastDaySale,purchaseDays,quantityInterval1,quantityInterval2,quantityInterval3,quantityInterval4,quantityInterval5,quantityInterval6,stockWarningDays,stockWarningQuantity,aitingQuantity,defaultGridCode ")
            ->where('companyId = ?', $id)->get();

        return $data;
    }
    public function sycncByCompanyId(int $id):array
    {
        for($i=0;$i<2500;$i++) {
            $data = SQL::instance()->tableSuffix($this->table, $id, 100, "_")
                ->select("*")
                ->where('companyId = ?', $id)->order("id","asc")->limit(2000)->offset($i)->get();
            foreach ($data as $item){
              try{
                  ES::instance()->setIndex("stock")->setType("stockwarehouse")->insertDocument($item,$item['id']);
              }catch (\Throwable $ex){
                  Log::error(sprintf('%s in %s on line %d', $ex->getMessage(), $ex->getFile(), $ex->getLine()));
              }

            }

        }
        return $data;
    }
}