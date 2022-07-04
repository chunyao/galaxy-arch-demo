<?php

class Config
{
    public static $config = array();

    public static function load()
    { //定义构造函数

        $domain['main'] = getenv("DOMAIN_MAIN") ? getenv("DOMAIN_MAIN") : 'www.lljr.com';
        $domain['forum'] = getenv("DOMAIN_FORUM") ? getenv("DOMAIN_FORUM") : 'forum.lljr.com';

        self::$bkconfig['cookies']['secret'] = "abcd.1234";
        self::$bkconfig['cookies']['timeout'] = 3600;
        self::$bkconfig['cookies']['prefix'] = "config_";
    }
}