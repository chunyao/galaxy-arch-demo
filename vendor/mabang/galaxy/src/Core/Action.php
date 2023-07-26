<?php
namespace Mabang\Galaxy\Core;
class Action
{
    public static $action;
    public static $instance;


    public static function getapi($action, $appName)
    {
        $action = str_replace("v1/", "", $action);
        $action = str_replace(".", "", $action);
        $str = "App\Http\Controller";
        $str .= str_replace("/", "\\", $action);
        $str = ucwords($str, "\\");
        //     $str .= ".php";


        return $str;

    }
}