<?php

namespace App\Http\Controller\Msg;

use App;
use App\Config\RDS;

use Galaxy\Core\BaseController;
use Galaxy\Core\Log;
use App\Service\MsgService;
use Mix\Vega\Context;

class Msg
{

    private $msgService;

    public function __construct()
    {

        //   $this->msgService = new MsgService();

    }

    public function handler(Context $ctx)
    {
        echo "start:" . self::getMillisecond()."\n";

        RDS::instance()->get(App::$innerConfig['rabbitmq.queue'][0] . ":e94cbf36-cd9e-40fb-b268-3e7334ca8928-3a");
        echo "end:" . self::getMillisecond();
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => self::getMillisecond()
        ]);
        /*$msgService = new MsgService();

        $id = rand(1, 1695705);

        $data = $this->msgService->findById($id);
        $echo_string = datajson("10200", $data, "success", $cache = false);
        return $echo_string;*/
    }

    /** * 时间戳 - 精确到毫秒 * @return float */
    public static function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}
