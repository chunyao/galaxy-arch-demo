<?php

namespace App\Spi\MabangArchDemo\Service;

use App;
use Galaxy\Common\Utils\Arr;
use Galaxy\Core\Log;
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
            $api_url = env('PROJECT_URL_API_DOMAIN', 'http://' . MabangUtil::mabangUrl('api.mabangerp.com') . '/v2');
        } else {
            $api_url = env('PROJECT_URL_API_DOMAIN', 'http://mabang-arch-demo/mabang-arch-demo');
        }


        $url = $api_url .  $path;

        $result = self::request($url, $method, $parmas);
        return $result;
        /* $code   = Arr::get($data, 'code', 0);
         if ( $code == self::SUCCESS ) {
             return $data;
         }
         return [];
        */
    }


    /**
     * @param $url
     * @param $data
     *
     * @return array|mixed
     * @throws \ReflectionException
     */
    public static function request($url, $method, $data)
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
            $options = [
                'headers' => $headers,
            ];
            $options += ['json' => $data];
            $response = $client->request($method, $url, $options);
            $res = json_decode($response->getBody()->getContents(), 1);
            if (Arr::get($res, 'code', false) === 0) {
                return ['code' => $res['code'], 'data' => $res['data'], 'msg' => $res['msg']];
            }
            $msg = $res;
        } catch (RequestException $e) {
            $msg = $e->getMessage();
        } catch (\InvalidArgumentException $e) {
            $msg = $e->getMessage();
        } catch (GuzzleException $e) {
            $msg = $e->getMessage();
        } catch (\Exception $e) {
            $msg = $e->getMessage();
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
        }
        $code = -1;
        if (isset($res['code'])) {
            $code = $res['code'];
        }
        return ['code' => $code, 'data' => [], 'msg' => $msg];
    }
}