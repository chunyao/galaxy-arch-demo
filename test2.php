<?php
function http_request($host, $data)
{
    if (!($socket = socket_create(AF_UNIX, SOCK_STREAM, 0))) {
        echo "socket_create() error!\r\n";
        exit;
    }
    if (!socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1)) {
        echo "socket_set_option() error!\r\n";
        exit;
    }
    if (!socket_connect($socket, $host, 80)) {
        echo "socket_connect() error!\r\n";
        exit;
    }
    if (!socket_write($socket, $data, strlen($data))) {
        echo "socket_write() errror!\r\n";
        exit;
    }
    while ($get = socket_read($socket, 1024, PHP_NORMAL_READ)) {
        $content .= $get;
    }
    socket_close($socket);
    $array = array('HTTP/1.1 404 Not Found', 'HTTP/1.1 300 Multiple Choices', 'HTTP/1.1 301 Moved Permanently', 'HTTP/1.1 302 Found', 'HTTP/1.1 304 Not Modified', 'HTTP/1.1 400 Bad Request', 'HTTP/1.1 401 Unauthorized', 'HTTP/1.1 402 Payment Required', 'HTTP/1.1 403 Forbidden', 'HTTP/1.1 405 Method Not Allowed', 'HTTP/1.1 406 Not Acceptable', 'HTTP/1.1 407 Proxy Authentication Required', 'HTTP/1.1 408 Request Timeout', 'HTTP/1.1 409 Conflict', 'HTTP/1.1 410 Gone', 'HTTP/1.1 411 Length Required', 'HTTP/1.1 412 Precondition Failed', 'HTTP/1.1 413 Request Entity Too Large', 'HTTP/1.1 414 Request-URI Too Long', 'HTTP/1.1 415 Unsupported Media Type', 'HTTP/1.1 416 Request Range Not Satisfiable', 'HTTP/1.1 417 Expectation Failed', 'HTTP/1.1 Retry With');
    for ($i = 0; $i <= count($array); $i++) {
        if (preg_match($array[$i], $content)) {
            return "{$array[$i]}\r\n";
            break;
        } else {
            return "{$content}\r\n";
            break;
        }
    }
}

$data = "123";
echo http_request("./myserv.sock",$data);

/*
if ($socket < 0) {
    $errmsg = socket_strerror($socket);
    echo "failed to create socket: {$errmsg}" . PHP_EOL;
    exit;
}

$host = "0.0.0.0";
$port = 9601;
$ret = socket_bind($socket, $host, $port);
if ($ret < 0) {
    echo "failed to bind socket: {$ret}" . PHP_EOL;
    exit;
}

$ret = socket_listen($socket, 0);
if ($ret < 0) {
    $errmsg = socket_strerror($ret);
    echo "failed to listen: {$errmsg}" . PHP_EOL;
    exit;
}

while (pcntl_fork() == 0) {

    if (pcntl_fork() == 0) {
        $recv = socket_read($connection, 8192);
        $data = "serverr: {$recv}";


        exit(0);
    } else {
        socket_close($connection);
    }
}*/