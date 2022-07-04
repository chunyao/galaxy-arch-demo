<?php

namespace Galaxy\Core;
use \SeasLog;
class Log
{


    /*
        * SEASLOG_DEBUG      "DEBUG"       - debug信息、细粒度信息事件
        * SEASLOG_INFO       "INFO"        - 重要事件、强调应用程序的运行过程
        * SEASLOG_NOTICE     "NOTICE"  - 一般重要性事件、执行过程中较INFO级更为重要的信息
        * SEASLOG_WARNING    "WARNING"     - 出现了非错误性的异常信息、潜在异常信息、需要关注并且需要修复
        * SEASLOG_ERROR      "ERROR"       - 运行时出现的错误、不必要立即进行修复、不影响整个逻辑的运行、需要记录并做检测
        * SEASLOG_CRITICAL   "CRITICAL"    - 紧急情况、需要立刻进行修复、程序组件不可用
        * SEASLOG_ALERT      "ALERT"       - 必须立即采取行动的紧急事件、需要立即通知相关人员紧急修复
        * SEASLOG_EMERGENCY  "EMERGENCY"   - 系统不可用
    */

    public static function init($basePath = "", $logger = "")
    {
        if (class_exists("SeasLog")) {
            if ($basePath) {
                SeasLog::setBasePath($basePath);
            }
            if ($logger) {
                SeasLog::setLogger($logger);
            }
        }
    }

    /**
     * Log constructor.
     * 写日志
     * @param $level 日志级别8个
     * @param string $message 日志记录信息
     * @param array $data 替换$message中的占位符数据
     * @param string $module 指定一个logger来存储日志，只会改变当前存储的日志，不会对下面的日志造成影响
     */
    public static function log($level, $message = "", $data = [], $module = "")
    {
        if (class_exists("SeasLog")) {
            //记录当前请求的uri，便于寻找记录日志的地址
            $msg['url'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $msg['msg'] = $message;
            SeasLog::log($level, json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $data, $module); //中文和斜杠不转义，正常显示。
            if (PHP_SAPI == "cli" or defined('STDIN')) {
                //运行模式是否是cli模式,是的话手动清除缓存
                SeasLog::closeLoggerStream(SEASLOG_CLOSE_LOGGER_STREAM_MOD_ALL);
            }

        }
    }

    /**
     * debug
     * @param string $message
     * @param array $data
     * @param string $module
     */
    public static function debug($message = "", $data = [], $module = "")
    {
        if (class_exists("SeasLog")) {
            self::log(SEASLOG_DEBUG, $message, $data, $module);
        }
    }

    /**
     * info
     * @param string $message
     * @param array $data
     * @param string $module
     */
    public static function info($message = "", $data = [], $module = "")
    {
        if (class_exists("SeasLog")) {
            self::log(SEASLOG_INFO, $message, $data, $module);
        }
    }

    /**
     * notice
     * @param string $message
     * @param array $data
     * @param string $module
     */
    public static function notice($message = "", $data = [], $module = "")
    {
        if (class_exists("SeasLog")) {
            self::log(SEASLOG_NOTICE, $message, $data, $module);
        }
    }

    public static function warning($message = "", $data = [], $module = "")
    {
        if (class_exists("SeasLog")) {
            self::log(SEASLOG_WARNING, $message, $data, $module);
        }
    }

    /**
     * error
     * @param string $message
     * @param array $data
     * @param string $module
     */
    public static function error($message = "", $data = [], $module = "")
    {
        if (class_exists("SeasLog")) {
            self::log(SEASLOG_ERROE, $message, $data, $module);
        }
    }

    /**
     * critical
     * @param string $message
     * @param array $data
     * @param string $module
     */
    public static function critical($message = "", $data = [], $module = "")
    {
        if (class_exists("SeasLog")) {
            self::log(SEASLOG_CRITICAL, $message, $data, $module);
        }
    }

    /**
     * alert
     * @param string $message
     * @param array $data
     * @param string $module
     */
    public static function alert($message = "", $data = [], $module = "")
    {
        if (class_exists("SeasLog")) {
            self::log(SEASLOG_ALERT, $message, $data, $module);
        }
    }

    /**
     * emergency
     * @param string $message
     * @param array $data
     * @param string $module
     */
    public static function emergency($message = "", $data = [], $module = "")
    {
        if (class_exists("SeasLog")) {
            self::log(SEASLOG_EMERGENCY, $message, $data, $module);
        }
    }

    /**
     * 统计日志信息
     * @param $level 日志等级
     * @param $module 模块名
     * @param $date 日期格式为:Ymd格式
     * @return mixed  返回详细数组
     */
    public static function countLog($level, $module = "default", $date = "")
    {
        if (class_exists("SeasLog")) {
            if (empty($module)) {
                $module = "default";
            }
            SeasLog::setLogger($module);
            return SeasLog::analyzerCount($level, $date);
        }
    }

    /**
     * @param $level 日志等级
     * @param string $module 模块名
     * @param string $date 日期格式为:Ymd格式
     * @return mixed 返回详细数组
     */
    public static function detailLog($level, $module = "default", $date = "")
    {
        if (class_exists("SeasLog")) {
            if (empty($module)) {
                $module = "default";
            }
            SeasLog::setLogger($module);
            return SeasLog::analyzerDetail($level, $date);
        }
    }


}