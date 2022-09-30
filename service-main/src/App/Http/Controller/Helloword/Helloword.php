<?php
declare(strict_types=1);

namespace App\Http\Controller\Helloword;


use App;
use App\Common\Utils\Test;
use App\Config\ES;
use App\Config\RDS;

use App\Repository\Model\Mongo\Product;
use App\Service\SayService;
use App\Service\WishbrandService;
use Galaxy\Common\Configur\Cache;
use Galaxy\Common\Configur\SnowFlake;
use Galaxy\Common\Utils\SnowFlakeUtils;
use Galaxy\Core\BaseController;
use Galaxy\Core\Log;
use App\Service\MsgService;
use Mix\Vega\Context;
use Swoole\Coroutine as co;
use Swoole;

class Helloword extends BaseController
{
    private MsgService $msgSevice;
    private SayService $sayService;
    private Product $product;
    private WishbrandService $wishbrandService;

    public function __construct()
    {
        //    $this->product = new Product();
        //   $this->msgSevice = new MsgService();
        //   $this->wishbrandService = new WishbrandService();
    }
    public function ht(Context $ctx){
        $body = $ctx->getQuery("get");
        co::set(['hook_flags' => SWOOLE_HOOK_CURL]);
        co::create(function() use($body) {
          var_dump($body);
        });

        $ctx->JSON(200, [
            'code' => 200,
            'message' => 'success',
            'data' => 1
        ]);
    }
    public function helloword(Context $ctx)
    {
        var_dump($ctx->getQuery('test'));
        /*ES*/
        $ctx->JSON(200, [
            'code' => 200,
            'message' => 'success',
            'data' => Test::test()
        ]);
        /*mongo*/
        /* $data = $this->product->insertData();
         $ctx->JSON(200, [
             'code' => 10200,
             'message' => 'success',
             'data' => $data
         ]);*/
        // unset($data['_id']);
        /*      $data['data']='作者：nesmto
      链接：https://www.zhihu.com/question/542336391/answer/2565631310
      来源：知乎
      著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。

      威海2022年7月8日上午，威海市环翠区报告1例新冠病毒阳性感染者。该感染者居住地为环翠区鲸园街道古北二巷，自省外乘飞机到达烟台蓬莱机场，自驾返回威海。在烟台蓬莱机场核酸混采检测结果异常。接协查函后，环翠区疾控中心核酸检测为阳性，已转运至定点医院接受集中隔离医学观察。经专家组会诊，诊断为新冠肺炎确诊病例（轻型）。该病例于7月6日在省外核酸检测为阴性，7月7日8时许自省外乘飞机到达烟台蓬莱机场，由其朋友一人自驾接回，途中未作停留，返回家中后，社区管控居家未外出。[4]海南（复阳，风险较低）央广网琼海7月8日消息 7月8日，琼海市新型冠状病毒肺炎疫情防控工作指挥部办公室发布通告称，7月7日，琼海市在博鳌机场对来琼海人员“落地检”中，发现2例核酸检测初筛阳性，琼海市疫情防控指挥部第一时间启动应急处置工作机制，对相关涉疫人员落实管控措施。经流调显示，以上2例初筛阳性有既往感染史，经琼海市医疗救治专家组诊断为新冠肺炎无症状感染者（复阳病例），传播风险较低。目前，相关涉疫人员均按规定进行隔离管控。舟山2022年7月9日，岱山县发现1例省外来岱新冠肺炎核酸检测阳性人员，现正对其密切接触者、次密切接触者进行紧急排查，并开展核酸采样检测，排查到的人员已纳入管控，已完成接触场所消毒，相应区域已按规范落实管控措施。初步流调结果显示，该人员近日活动轨迹如下：7月6日吴某从省外包车至杭州。7月8日上午包车从杭州出发到岱山。11:04到达岱山，与梁某、叶某、叶某平、漆某平4人在宁兴水产叶某平办公室短暂停留。12:00—14:00在高亭镇新天地高佳庄就餐，然后回到宁兴水产叶某平办公室。15:30在舟山锦舟宝盛大酒店办理入住。15:56到岱山县第一人民医院做核酸检测，后返回酒店。18:45在舟山锦舟宝盛大酒店慢生活咖啡茶室用餐，后未出酒店。7月9日0:15闭环转运至定点医院治疗。以上信息提示，上海没能控制住所有传染链，疫情还有内部和外部扩散隐患四、关于防疫政策看到 @画和风 的一段话，分享下共存思想的本质，是对现状的不满。这和早年很多人对欧美的崇拜是一个缘由。现实中的不满和遭遇，让他们逃避式的在脑海中构筑了一座完美的乌托邦。任何试图揭开真相的人，都会遭到他们歇斯底里的攻击。他们不在乎事实，他们只是在发泄自己生活中的怨气。美国目前受新冠后遗症困扰的人占成年人人口比例的1/13，至少160万人不能正常工作美国 Q1 GDP 下跌 1.6%，下跌的原因是什么？292 赞同 · 29 评论回答一部分人失去工作能力，从生产者变为纯消费者如果他们拿不到社保补助，生活会非常困难，活下去都不容易。美国人患上新冠后遗症，还失去了房子';
      */
        //$es=  $this->wishbrandService->insertEs($data);
        //$data = $this->wishbrandService->findById(522);


        //  $indexData = ES::instance()->getDocumentById("57ce61f064e915204367f296");


       // if (!RDS::instance()->set(App::$innerConfig['rabbitmq.queue'][0] . ":" . $this->msg['messageId'], 1, array('nx', 'ex' => 30000))) {

            $ctx->JSON(200, [
                'code' => 200,
                'message' => 'success',
                'data' => RDS::instance()->set("qweqwe", 1, array('nx', 'ex' => 30000))
            ]);

          //  echo "消息重复消费 id:" . $this->msg['id'] . "\n";
            //   log::info("消息重复消费 id:". $this->msg['id']);
       //     return true;
      /*  } else {
            $ctx->JSON(200, [
                'code' => 200,
                'message' => 'success',
                'data' => RDS::instance()->set("qweqwe", 1, array('nx', 'ex' => 30000))
            ]);
        }*/

        /*
          $id = rand(1, 239368);
          $data = $this->msgSevice->findById($id);
  */
        /*  $ctx->JSON(200, [
              'code' => 10200,
              'message' => 'success',
              'data' => 1
          ]);
  */

        /* 写法1*/
        //  $ctx->string(200, $echo_string);
        /* 写法2*/
        /*
        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => $data
        ]);*/

    }

    public function snow(Context $ctx)
    {
        $n =0;
        $chan1 = new Swoole\Coroutine\Channel(1);
        co::create(function () use ($chan1) {
            $snowId = SnowFlake::instance()->generateID();

            $chan1->push($snowId);
            sleep(5);
        });

       /* if ( Cache::instance()->get((string)$snowId)){
            echo "重复";
        }
        Cache::instance()->set((string)$snowId,"1",30000);
*/

        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' =>  $chan1->pop()
        ]);

    }

    public
    function upload(Context $ctx)
    {
        $chan = new Swoole\Coroutine\Channel(1);
        $chan2 = new Swoole\Coroutine\Channel(1);
        co::create(function () use ($chan) {
            for ($i = 0; $i < 100000; $i++) {
                co::sleep(1.0);

                /* url 文件存本地 */
                $chan->push(['rand' => rand(1000, 9999), 'index' => $i]);
                echo "a $i\n";
            }
        });

        $n = 0;
        co::create(function () use ($chan, $chan2, $n) {
            while (1) {
                $data = $chan->pop();
                $chan2->push(['rand2' => rand(1000, 9999), 'index' => $n]);
                $n++;
                var_dump($data);
            }
        });
        while (1) {
            $data2 = $chan2->pop();
            var_dump($data2);
        }


        $ctx->JSON(200, [
            'code' => 10200,
            'message' => 'success',
            'data' => 1
        ]);
    }

    public
    function __destruct()
    {
        unset($this->msgModel);

    }

}



