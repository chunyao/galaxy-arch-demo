<?php

namespace App\Spi\MabangArchDemo\Service;

use App;
use Mabang\Galaxy\Common\Utils\Arr;
use Mabang\Galaxy\Core\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class BaseService
{
    const SUCCESS = 200;

    const APP_NAME = "mabang-arch-demo";

    /**
     * 日志前缀
     */
    const LOG_PREFIX = '_external_call_service_util_request_';

    /**
     * 请求超时时间，单位：s
     */
    const TIMEOUT = 3;


    public function __construct()
    {
    }

    public static $instance = [];

    /**
     * 单利模式
     * @return static
     */
    public static function instance()
    {
        $className = get_called_class();
        isset(self::$instance[$className]) || self::$instance[$className] = new static();
        return self::$instance[$className];
    }

    public static function call($path, $parmas, $method = 'POST')
    {
        if (App::$bootConfig['env'] == "local") {
            $api_url = env('PROJECT_URL_API_DOMAIN', 'https://api.mabangerp.com/mabang-arch-demo');
        } else {
            $api_url = env('PROJECT_URL_API_DOMAIN', 'http://mabang-arch-demo/mabang-arch-demo');
        }


        $url = $api_url .  $path;

        $result = self::request($url, $parmas,$method);
        return $result;
    }


    /**
     * @param $url
     * @param $data
     *
     * @return array|mixed
     * @throws \ReflectionException
     */
    public static function request($url, $data,$method)
    {
        $data = (array)$data;
        $client = new Client([
            // 'base_uri' => $aiSearchAPIUrl,
            'timeout' => self::TIMEOUT,
        ]);
        $headers = [
            'content-length' => strlen(json_encode($data)),
            //'host'           => parse_url($aiSearchAPIUrl)['host'],
        ];
        try {
            if ($method=="POST"){
            $options = [
                'headers' => $headers,
            ];
            $options += ['json' => $data];
            }
            if ($method=="GET"){
                $options = ['query' => $data];
            }
            $response = $client->request($method, $url, $options);
            $res = json_decode($response->getBody()->getContents(), 1);
            if (Arr::get($res, 'code', false) === 200) {
                return $res['data'];
            }

            $msg = $res['message'];
        } catch (RequestException $e  ) {
            $msg = $e->getMessage();
        } catch (GuzzleException $e) {
            $msg = $e->getMessage();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
        }
        throw new \Exception($msg);
    }
}