<?php

namespace Mabang\Galaxy\Core;

class ConfigLoad
{


    /**
     * 解析文件路径
     */
    public static function findFile()
    {

// 扫描$con目录下的所有文件
       // echo ROOT_PATH.DIRECTORY_SEPARATOR."service-main".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."App".DIRECTORY_SEPARATOR."Config".DIRECTORY_SEPARATOR."\n";
        $filename = scandir(ROOT_PATH.DIRECTORY_SEPARATOR."service-main".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."App".DIRECTORY_SEPARATOR."Config".DIRECTORY_SEPARATOR);

// 定义一个数组接收文件名

        $conname = array();

        foreach ($filename as $k => $v) {

            // 跳过两个特殊目录   continue跳出循环

            if ($v == "." || $v == "..") {
                continue;
            }

            //截取文件名，我只需要文件名不需要后缀;然后存入数组。如果你是需要后缀直接$v即可

            $conname[] = "\\App\\Config\\".substr($v, 0, strpos($v, "."));

        }

        return $conname; // 文件标准路径
    }


}