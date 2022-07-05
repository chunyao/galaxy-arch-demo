<?php
namespace Galaxy\Core;

use alibaba\nacos\NacosConfig;
use alibaba\nacos\Naming;



class ServiceRegister
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ServiceRegister {--action=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '服务注册到Nacos';

    private $naming;

    protected $config;

    public function __construct()
    {

        $ip = swoole_get_local_ip();
        NacosConfig::setHost("https://dev-nacos.mabangerp.com"); //上面如果启用的了域名就用域名，如果用了VIP就用VIP，这里为了测试简单用了简单模式
        if (isset($ip['en0'])){ $newIp =$ip['en0']; }
        if (isset($ip['eth0'])){ $newIp =$ip['eth0']; }
        $this->naming = Naming::init(
            "archDemo-service",          //服务的名称，随便取，在Nacos里不重复就可以了，如果重复就代表同一个服务的不同节点，用于高可用
            $newIp,   //服务的地址
            "8080",             //服务的端口号
            "cc071b13-5746-4061-bbc5-5f2fc220b810",
            1,
            true
        //设置后nacos服务器会自动检测ip和端口匹配的实例是否存活 设置后就无需客户端发送实例心跳了,

        );

    }

    public function handle($action){
        switch ($action) {
            case 'register':
                $result = $this->naming->register(true,true,'DEFAULT');

                break;
            case 'delete':
                $this->naming->delete();    //测试实例删除返回成功，实际删除不成功，后台一直显示存在
                break;
        }
    }
    public function beat(){
        $result = $this->naming->beat();
    }
}