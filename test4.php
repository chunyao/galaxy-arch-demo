<?php


/**
 * @property-read int $SOCKET_EPIPE
 * @property-read int $SOCKET_ENETDOWN
 * @property-read int $SOCKET_ENETUNREACH
 * @property-read int $SOCKET_ENETRESET
 * @property-read int $SOCKET_ECONNABORTED
 * @property-read int $SOCKET_ECONNRESET
 * @property-read int $SOCKET_ECONNREFUSED
 * @property-read int $SOCKET_ETIMEDOUT
 * @property-read int $SOCKET_EWOULDBLOCK
 * @property-read int $SOCKET_EINTR
 * @property-read int $SOCKET_EAGAIN
 */
final class SocketConstants
{
    /**
     * @var int[]
     */
    private $constants;

    /** @var self */
    private static $instance;

    public function __construct()
    {
        $constants = get_defined_constants(true);
        if (isset($constants['sockets'])) {
            $this->constants = $constants['sockets'];
        } else {
            trigger_error('Sockets extension is not enabled', E_USER_WARNING);
            $this->constants = array();
        }
    }

    /**
     * @param string $name
     * @return int
     */
    public function __get($name)
    {
        return isset($this->constants[$name]) ? $this->constants[$name] : 0;
    }

    /**
     * @param string $name
     * @param int $value
     * @internal
     */
    public function __set($name, $value)
    {
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->constants[$name]);
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

class Single
{
    public static $instance = [];

    /**
     * 单利模式
     * @return static
     */
    public static function me()
    {
        $className = get_called_class();
        isset(self::$instance[$className]) || self::$instance[$className] = new static();
        return self::$instance[$className];
    }

}

class Trace extends Single
{
    public $application_instance;
    public $pid;
    public $application_id;
    public $uuid;
    public $version;
    public $segment;
    public $globalTraceIds;

    // sw8
    public $traceId;

    public $service;
    public $serviceInstance;

}

class segment
{
    public $traceSegmentId;
    public $isSizeLimited;
    public $spans;
}

class Span
{
    public $tags;
    public $spanId;
    public $parentSpanId;
    public $startTime;
    public $operationName;
    public $peer;
    public $spanType;
    public $spanLayer;
    public $componentId;
    public $component;
    public $refs;
    public $endTime;
    public $isError;
}

class ref
{
    public $type;
    public $parentTraceSegmentId;
    public $parentSpanId;
    public $parentApplicationInstanceId;
    public $networkAddress;
    public $entryApplicationInstanceId;
    public $entryServiceName;
    public $parentServiceName;

    //	sw8
    public $traceId;
    public $parentService;
    public $parentServiceInstance;
    public $parentEndpoint;
    public $targetAddress;
}


abstract class AbstractIO
{
    const BUFFER_SIZE = 8192;

    /** @var int|float */
    protected $connection_timeout;

    /** @var float */
    protected $read_timeout;

    /** @var float */
    protected $write_timeout;

    /** @var int|float */
    protected $last_read;

    /** @var int|float */
    protected $last_write;

    /** @var array|null */
    protected $last_error;


    /**
     * @param string $data
     */
    abstract public function write($data);

    /**
     * @return void
     */
    abstract public function close();


    /**
     * Set ups the connection.
     * @return void
     */
    abstract public function connect();


    /**
     * @return float|int
     */
    public function getLastActivity()
    {
        return max($this->last_read, $this->last_write);
    }

    public function getReadTimeout(): float
    {
        return $this->read_timeout;
    }


    /**
     * Begin tracking errors and set the error handler
     */
    protected function setErrorHandler(): void
    {
        $this->last_error = null;
        set_error_handler(array($this, 'error_handler'));
    }

    protected function throwOnError(): void
    {
        if ($this->last_error !== null) {
            throw new \ErrorException(
                $this->last_error['errstr'],
                0,
                $this->last_error['errno'],
                $this->last_error['errfile'],
                $this->last_error['errline']
            );
        }
    }

    protected function restoreErrorHandler(): void
    {
        restore_error_handler();
    }

    /**
     * Internal error handler to deal with stream and socket errors.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     * @return void
     */
    public function error_handler($errno, $errstr, $errfile, $errline, $errcontext = null)
    {
        // throwing an exception in an error handler will halt execution
        //   set the last error and continue
        $this->last_error = compact('errno', 'errstr', 'errfile', 'errline', 'errcontext');
    }


}

class SocketIO extends AbstractIO
{
    /** @var null|resource */
    private $sock;

    /**
     * @param string $host
     */
    public function __construct(
        $host

    )
    {

        $this->host = $host;
        $this->read_timeout = 3;

    }

    public static function splitSecondsMicroseconds($number)
    {
        return array((int)floor($number), (int)(fmod($number, 1) * 1000000));
    }

    /**
     * @inheritdoc
     */
    public function connect()
    {
        $this->sock = socket_create(AF_UNIX, SOCK_STREAM, 0);

        list($sec, $uSec) = self::splitSecondsMicroseconds($this->read_timeout);
        socket_set_option($this->sock, SOL_SOCKET, SO_SNDTIMEO, array('sec' => $sec, 'usec' => $uSec));
        list($sec, $uSec) = self::splitSecondsMicroseconds(3);
        socket_set_option($this->sock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $sec, 'usec' => $uSec));

        $this->setErrorHandler();
        try {
            $connected = socket_connect($this->sock, $this->host);
            $this->throwOnError();
        } catch (\ErrorException $e) {
            $connected = false;
        } finally {
            $this->restoreErrorHandler();
        }
        if (!$connected) {
            $errno = socket_last_error($this->sock);
            $errstr = socket_strerror($errno);
            throw new \ErrorException(sprintf(
                'Error Connecting to server (%s): %s',
                $errno,
                $errstr
            ), $errno);
        }

        socket_set_block($this->sock);


    }

    /**
     * @inheritdoc
     */
    public function getSocket()
    {
        return $this->sock;
    }

    /**
     * @return int|bool
     */
    protected function select_write()
    {
        $read = $except = null;
        $write = array($this->sock);

        return socket_select($read, $write, $except, 0, 100000);
    }

    /**
     * @inheritdoc
     */
    public function write($data)
    {
        // Null sockets are invalid, throw exception
        if (is_null($this->sock)) {
            throw new \ErrorException(sprintf(
                'Socket was null! Last SocketError was: %s',
                socket_strerror(socket_last_error())
            ));
        }
        $written = 0;
        $len = mb_strlen($data, 'ASCII');
        $write_start = microtime(true);

        while ($written < $len) {
            $this->setErrorHandler();
            try {
                $this->select_write();
                $buffer = mb_substr($data, $written, self::BUFFER_SIZE, 'ASCII');
                $result = socket_write($this->sock, $buffer);
                $this->throwOnError();
            } catch (\ErrorException $e) {
                $code = socket_last_error($this->sock);
                $constants = SocketConstants::getInstance();
                switch ($code) {
                    case $constants->SOCKET_EPIPE:
                    case $constants->SOCKET_ENETDOWN:
                    case $constants->SOCKET_ENETUNREACH:
                    case $constants->SOCKET_ENETRESET:
                    case $constants->SOCKET_ECONNABORTED:
                    case $constants->SOCKET_ECONNRESET:
                    case $constants->SOCKET_ECONNREFUSED:
                    case $constants->SOCKET_ETIMEDOUT:
                        $this->close();
                        throw new \ErrorException(socket_strerror($code), $code, $e);
                    default:
                        throw new \ErrorException(sprintf(
                            'Error sending data. Last SocketError: %s',
                            socket_strerror($code)
                        ), $code, $e);
                }
            } finally {
                $this->restoreErrorHandler();
            }

            if ($result === false) {
                throw new \ErrorException(sprintf(
                    'Error sending data. Last SocketError: %s',
                    socket_strerror(socket_last_error($this->sock))
                ));
            }

            $now = microtime(true);
            if ($result > 0) {
                $this->last_write = $write_start = $now;
                $written += $result;
            } else {
                if (($now - $write_start) > $this->write_timeout) {
                    throw \ErrorException($this->write_timeout);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function close()
    {

        if (is_resource($this->sock)) {
            socket_close($this->sock);
        }
        $this->sock = null;
        $this->last_read = 0;
        $this->last_write = 0;
    }


    /**
     * @throws \PhpAmqpLib\Exception\AMQPIOException
     */
    protected function enable_keepalive()
    {
        if (!defined('SOL_SOCKET') || !defined('SO_KEEPALIVE')) {
            throw new AMQPIOException('Can not enable keepalive: SOL_SOCKET or SO_KEEPALIVE is not defined');
        }

        socket_set_option($this->sock, SOL_SOCKET, SO_KEEPALIVE, 1);
    }

    /**
     * @inheritdoc
     */
    public function error_handler($errno, $errstr, $errfile, $errline, $errcontext = null)
    {
        $constants = SocketConstants::getInstance();
        // socket_select warning that it has been interrupted by a signal - EINTR
        if (isset($constants->SOCKET_EINTR) && false !== strrpos($errstr, socket_strerror($constants->SOCKET_EINTR))) {
            // it's allowed while processing signals
            return;
        }

        parent::error_handler($errno, $errstr, $errfile, $errline, $errcontext);
    }

    /**
     * @inheritdoc
     */
    protected function setErrorHandler(): void
    {
        parent::setErrorHandler();
        socket_clear_error($this->sock);
    }
}

class SkyTraceUtil extends Single
{
    public static $data;

    public static $traceId;

    public static $uuid;

    public static function getTraceId()
    {
        if (!empty(self::$traceId)) return self::$traceId;
        self::$traceId = self::getUUID();
    }

    public static function getUUID()
    {
        if (!empty(self::$uuid)) return self::$uuid;
        self::$uuid = self::getUUID();
    }

    public static function genUUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for the time_low
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            // 16 bits for the time_mid
            mt_rand(0, 0xffff),
            // 16 bits for the time_hi,
            mt_rand(0, 0x0fff) | 0x4000,

            // 8 bits and 16 bits for the clk_seq_hi_res,
            // 8 bits for the clk_seq_low,
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for the node
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    public static function getMillisecondTimeStamp() {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }
}

$socket_file = '/Users/chunyao/mabang/arch/SkyAPM-php-sdk/sky-agent.sock';
$socket = new SocketIO($socket_file);
try {
    $socket->connect();
    $data = '0{"app_code":"mabang-test","pid":' . getmypid() . ',"version":8}' . PHP_EOL;
    $socket->write($data);
    Trace::me()->application_id = 1;
    Trace::me()->application_instance = 1;
    Trace::me()->uuid = SkyTraceUtil::me()::$uuid;
    Trace::me()->pid = getmypid();
    Trace::me()->version=8;

    $span = new span();

    $data = '1{"application_instance":1,"uuid":"981d3254-957f-45e0-936c-4ac68b8fbf72","pid":' . getmypid() . ',"application_id":1,"version":8,"segment":{"traceSegmentId":"1.668.16946621960001","isSizeLimited":0,"spans":[{"tags":{"url":"/test3.php"},"spanId":0,"parentSpanId":-1,"startTime":1694662196917,"operationName":"/test3.php","peer":"127.0.0.1:10000","spanType":0,"spanLayer":3,"componentId":49,"refs":[],"endTime":1694662196921,"isError":0}]},"globalTraceIds":["1.668.16946621960001"],"service":"MyProjectName","serviceInstance":"981d3254-957f-45e0-936c-4ac68b8fbf72","traceId":"1.668.16946621960001"}';
    $socket->write($data);
} catch (\Exception $er) {

}
