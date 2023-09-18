<?php

namespace Mabang\Galaxy\Common\HttpClient;

use Exception;
use SoapFault;

/**
 * 接口请求类
 * Class Http
 * @package App\Helpers
 * @Author : li jiaxiang
 * @Email  : 15201065801@163.com
 * @Date   : 2021/7/30
 * @Notice :
 */
class Http
{
    public static $info;//请求的详细信息
    public static $error;//错误信息
    public static $lastRequestInfo;//请求xml soap请求可用

    private $ch;
    private static $url = '';
    private static $option = array();
    private static $method = '';
    private static $requestData;

    public static $timeOut = 600;//等待响应超时时间
    protected static $connectTimeOut = 5;//建立链接超时时间
    // 允许的http code
    public static $acceptHttpCode = array();
    public static $retryHttpCode = array();

    public function __construct()
    {
        $this->ch = curl_init();
    }

    /**
     * @param array $requestData
     * @param string $orderSn
     * @param string $prefix
     * @return array|bool|mixed|string
     * @Author : li jiaxiang
     * @Email  : 15201065801@163.com
     * @Date   : 2021/8/2
     * @Notice :
     * @throws
     */
    public function request(string $method, $uri, array $options = [])
    {
        //初始化数据
        self::$url = $uri;
        self::$method = $method;
        self::$requestData = $options['data'] ?? '';
        self::$option = $options['options'] ?? array();

        // 需要重试的HTTPCode
        self::$retryHttpCode = $options['options']['httpCode']['retry'] ?? array();
        //被认定为成功的的HTTPCode
        self::$acceptHttpCode = $options['options']['httpCode']['accept'] ?? array();

        // 兼容一下 非数组的情况
        is_array(self::$retryHttpCode) or self::$retryHttpCode = array(self::$retryHttpCode);
        is_array(self::$acceptHttpCode) or self::$acceptHttpCode = array(self::$acceptHttpCode);
        try {

            switch (strtolower($requestData['type'])) {
                case 'soap' :
                    $response = self::callSoap();
                    break;
                case 'curl' :
                default :
                    $response = $this->callCurl();
            }

            return $response;
        } catch (Exception $exception) {
            $response = $exception->getMessage();
            throw  new Exception($response, $exception->getCode());
        } finally {
            unset($requestData);
        }


    }

    /**
     * 发起CURL请求
     * @return bool|string
     * @Author : li jiaxiang
     * @Email  : 15201065801@163.com
     * @Date   : 2021/7/30
     * @Notice : 超时默认时间60S
     * @throws Exception
     */
    protected function callCurl()
    {
        self::$method = strtoupper(self::$method);
        $urlArr = parse_url(self::$url);
        empty(self::$option['timeOut']) or self::$timeOut = self::$option['timeOut'];
        empty(self::$option['connectTimeOut']) or self::$connectTimeOut = self::$option['connectTimeOut'];
        $header = self::$option['header'] ?? array();
        $data = is_array(self::$requestData) && empty(self::$option['isJson']) ? http_build_query(self::$requestData) : json_encode(self::$requestData);
        $version = self::$option['version'] ?? 2;
        $ch = $this->ch;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connectTimeOut);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::$timeOut);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        if (isset(self::$option['keepalive'])) {
            curl_setopt(curl, CURLOPT_TCP_KEEPALIVE, 1);
            /* keep-alive idle time to 120 seconds */
            curl_setopt(curl, CURLOPT_TCP_KEEPIDLE, $timeout);
            /* interval time between keep-alive probes: 60 seconds */
            curl_setopt(curl, CURLOPT_TCP_KEEPINTVL, 60);

        }
        if (isset(self::$option['useragent'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, self::$option['useragent']);
        }
        if (isset(self::$option['proxyIp']))//Contains IP and Port
        {
            curl_setopt($ch, CURLOPT_PROXY, self::$option['proxyIp']);
        }
        if (isset(self::$option['encoding'])) {
            curl_setopt($ch, CURLOPT_ENCODING, self::$option['encoding']);
        }
        if (isset(self::$option['userpwd'])) {
            curl_setopt($ch, CURLOPT_USERPWD, self::$option['userpwd']);
        }
        if (isset(self::$option['cookietext'])) {
            curl_setopt($ch, CURLOPT_COOKIE, self::$option['cookietext']);
        }
        if (isset($urlArr['scheme']) && strtolower($urlArr['scheme']) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        if ($version == 1) {
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        }
        if (isset($urlArr['port'])) {
            curl_setopt($ch, CURLOPT_PORT, $urlArr['port']);
        }
        switch (self::$method) {
            case 'POST' :
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case 'DELETE' :
            case 'PUT' :
                self::$option['header'][] = "X-HTTP-Method-Override: " . self::$method;
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::$method);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PATCH' :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
                break;
            case 'GET':
            default :
                if ($data) {
                    self::$url .= (false === strpos(self::$url, '?') ? '?' . $data : '&' . $data);
                }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, self::$url);
        $output = curl_exec($ch);
        self::$error = curl_error($ch);
        self::$info = curl_getinfo($ch);
        self::$lastRequestInfo = array(
            'xml' => '',
            'header' => $header,
            'error' => self::$error,
            'info' => self::$info,
        );


        // 如果设定了 响应的http code 则校验，不符合则按照规则抛出异常
        if (!empty(self::$acceptHttpCode) && !in_array(self::$info['http_code'], self::$acceptHttpCode)) {
            $errorCode = 0;
            if (!empty(self::$retryHttpCode) && in_array(self::$info['http_code'], self::$retryHttpCode)) {
                $errorCode = Constant::TRADE_RETRY;
            }
            throw new Exception(
                "第三方接口服务异常，httpCode:" . self::$info['http_code'] . ";errorMessage:" . self::$error,
                $errorCode
            );
        }

        return $output;
    }

    protected function close()
    {
        curl_close($ch);
    }

    /**
     * 发起soap请求
     * @return array|mixed
     * @Author : li jiaxiang
     * @Email  : 15201065801@163.com
     * @Date   : 2021/8/2
     * @Notice :
     * @throws Exception
     */
    protected static function callSoap()
    {
        try {
            $options = array(
                'trace' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'exceptions' => true,
                'keep_alive' => false
            );
            empty(self::$option['options']) or $options = array_merge($options, self::$option['options']);
            $client = new \SoapClient(self::$url, $options);

            //特殊的 method 操作
            switch (self::$method) {
                case '__getFunctions' ://获取方法
                    return $client->__getFunctions();
                case '__getTypes' : //获取参数
                    return $client->__getTypes();
            }
            //如果请求体是string 则走__doRequest 方法
            if (is_string(self::$requestData)) {
                $locationUrl = empty(self::$option['locationUrl']) ? self::$url : self::$option['locationUrl'];
                return $client->__doRequest(self::$requestData, $locationUrl, self::$method, SOAP_1_1, false);
            }

            //如果有header头
            if (!empty(self::$option['header'])) {
                $headerUrl = empty(self::$option['headerUrl']) ? self::$url : self::$option['headerUrl'];
                $header = new \SoapHeader($headerUrl, self::$option['header']['key'], self::$option['header']['value']);
                $client->__setSoapHeaders($header);
            }

            return $client->__soapCall(self::$method, self::$requestData, $options);

        } catch (SoapFault $e) {
            self::$error = $e->getMessage() ?: $e->headerfault;
            self::$info = array(
                'faultString' => $e->faultstring ?? '',
                'headerFault' => $e->headerfault ?? '',
                'faultCode' => $e->faultcode ?? '',
                'errorDetail' => isset($e->detail) ? (is_string($e->detail) ? $e->detail : json_encode($e->detail)) : '',
                'faultActor' => $e->faultactor ?? '',
                'faultName' => $e->faultname ?? '',
            );
            return false;

        } finally {
            if (isset($client)) {
                self::$lastRequestInfo = array(
                    'xml' => $client->__getLastRequest(),
                    'header' => $client->__getLastRequestHeaders(),
                    'error' => self::$error,
                    'info' => self::$info,
                );

            }

            unset($client);
        }


    }
}