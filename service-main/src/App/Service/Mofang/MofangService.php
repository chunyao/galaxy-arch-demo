<?php
declare(strict_types=1);//开启严格模式

namespace App\Service\Mofang;

use App\Config\RDS;
use App\Repository\Model\BaseModel;
use App\Repository\Model\MdcassociateshopModel;
use App\Repository\Model\MiddleCallbackDataLogModel;
use App\Repository\Model\ShopModel;
use Mabang\Galaxy\Core\Log;
use Mabang\Galaxy\Service\BaseService;


class MofangService extends BaseService
{
    private string $appKey = 'HQsKaOd/4zDagfBMMNMD';



    private string $channelUrl = 'http://b.dm.mfyc9.com:8119/wgs/v1/openapi/channels';

    private string $stockUrl = 'http://b.dm.mfyc9.com:8119/wgs/v1/openapi/warehouses';

    private string $inventory = 'http://b.dm.mfyc9.com:8119/wgs/v1/openapi/products?productName=&warehouseCode=FR-RMSM&upcBarcode';

    private static function getDate()
    {
       return  date("Y-m-d h:i:s");
    }
    private static function getSign($params,$date,$query = "")
    {
        $signature = 'cueUKdi4nl03THTeAOzcB1x4aLFiBFB115jQ6TzJ';
        $sign=http_build_query($params);
        if ($query!=""){
            $sign=$query;
        }
        $sign .= $signature;
        return  date("Y-m-d h:i:s");
    }

}