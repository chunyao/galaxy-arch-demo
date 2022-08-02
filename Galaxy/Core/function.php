<?php

function getTableSuffix(string $table, int $companyId, $subTable = 100): string
{
    // 根据企业编号，对100取余分表
    $suffix = is_numeric($companyId) ? (int)$companyId % $subTable : null;
    return $table . $suffix;
}

function libfile($path, $filename)
{
    return ROOT_PATH . "/" . $path . $filename . ".php";
}

function datajson($code, $data, $message)
{
    $json = array("code" => $code, "data" => $data, "result" => $message);
    $return = json_encode($json);
    return $return;
}

function toTimeZone($src, $from_tz = 'UTC', $to_tz = 'Asia/Shanghai', $fm = 'Y-m-d H:i:s')
{
    $datetime = new DateTime($src, new DateTimeZone($from_tz));
    $datetime->setTimezone(new DateTimeZone($to_tz));
    return $datetime->format($fm);
}

function setCacheArray($key, $array, $time = null)
{

    $key = Config::$data['redisPre'] . $key;
    $data = json_encode($array);

    if (isset($time)) {
        RedisClusters::set($key, $data, $time);
    } else {
        RedisClusters::set($key, $data, Config::$data['redisTimeout']);
    }
    return true;
}

function clearCache()
{
    RedisClusters::clear();
}

function getCacheArray($key)
{
    $key = Config::$data['redisPre'] . $key;
    if ($return = RedisClusters::get($key)) {
        return json_decode($return);
    } else {
        return false;
    }

}

function setCache($key, $value, $time)
{

    $key = Config::$data['redisPre'] . $key;

    if ($time != "") {
        RedisClusters::set($key, $value, $time);
    } else {
        RedisClusters::set($key, $value, Config::$data['redisTimeout']);
    }

    return true;
}


function getCache($key)
{

    $key = Config::$data['redisPre'] . $key;
    if ($return = RedisClusters::get($key)) {
        return $return;
    } else {
        return false;
    }

}

function increase($key)
{

    $key = Config::$data['redisPre'] . $key;
    if ($return = RedisClusters::incr($key)) {
        return $return;
    } else {
        return false;
    }

}

function delCache($key)
{
    $key = Config::$data['redisPre'] . $key;
    RedisClusters::remove($key);
}

function dunserialize($data)
{
    if (($ret = unserialize($data)) === false) {
        $ret = unserialize(stripslashes($data));
    }
    return $ret;
}

function dintval($int, $allowarray = false)
{
    $ret = intval($int);
    if ($int == $ret || !$allowarray && is_array($int)) return $ret;
    if ($allowarray && is_array($int)) {
        foreach ($int as &$v) {
            $v = dintval($v, true);
        }
        return $int;
    } elseif ($int <= 0xffffffff) {
        $l = strlen($int);
        $m = substr($int, 0, 1) == '-' ? 1 : 0;
        if (($l - $m) === strspn($int, '0987654321', $m)) {
            return $int;
        }
    }
    return $ret;
}

function rest_curl($url, $method, $header = null, $data = null)
{
    $handle = curl_init();
    if ($header) {
        curl_setopt($handle, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_HEADER, 0);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

    switch (strtoupper($method)) {
        case 'GET':
            break;
        case 'POST':
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
            break;
        case 'PUT':
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
            break;
        case 'DELETE':
            curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }

    $res = curl_exec($handle);

    $errMsg = curl_error($handle);
    if ($errMsg) {
        throw new Exception('请求发生错误，出错信息为：' . $errMsg);
    }
    curl_close($handle);
    return $res;
}

function rest_post_use($url, $data, $type, $cookie)
{
    $cookie_string = "";
    foreach ($_COOKIE as $key => $value) {

        if (substr($key, 0, 8) == "nsession") {
            $cookie_string .= "$key=$value;";
        }
        if ($key == "slSessionId") {
            $cookie_string .= "JSESSIONID=$value;";
            $cookie_string .= "$key=$value;";
        } else {
            continue;
            $cookie_string .= "$key=$value;";
        }

    };
    if ($cookie != "") {
        foreach ($cookie as $key => $value) {

            if (substr($key, 0, 8) == "nsession") {
                $cookie_string .= "$key=$value;";
            }
            if ($key == "slSessionId") {
                $cookie_string .= "JSESSIONID=$value;";
                $cookie_string .= "$key=$value;";
            } else {
                continue;
                $cookie_string .= "$key=$value;";
            }

        };
    }
    if (is_array($data)) {
        $data_string = urldecode(http_build_query($data));
    } else {
        $data_string = "";
    }


    if ($type == "POST") {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . "?" . $data_string);
        curl_setopt($ch, CURLOPT_HEADER, 0);
    }
    //echo $url."?".$data_string;
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.2) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.2.149.27 Safari/525.13");
    curl_setopt($ch, CURLOPT_REFERER, "https://www.lljr.com/");
    curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);


    /*	$filename = libfile("mod/api/vip/","stock");
        $fp = fopen($filename, 'a');
        $line=array($url,$data_string,$cookie_string,$result);
        fputcsv($fp, $line);
        fclose($fp);
    */
    return $result;
}


function rest_post($url, $data, $timeout = 10)
{

    $data_string = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json')
    );
    $data = curl_exec($ch);
    curl_close($ch);
    //	print_r($data);
    return $data;
}
function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
{
    static $recursive_counter = 0;
    if (++$recursive_counter > 1000) {
        die('possible deep recursion attack');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arrayRecursive($array[$key], $function, $apply_to_keys_also);
        } else {
            $array[$key] = $function($value);
        }

        if ($apply_to_keys_also && is_string($key)) {
            $new_key = $function($key);
            if ($new_key != $key) {
                $array[$new_key] = $array[$key];
                unset($array[$key]);
            }
        }
    }
    $recursive_counter--;
}
function JSON($array) {
    arrayRecursive($array, 'urlencode', true);
    $json = json_encode($array);
    return urldecode($json);
}
function parseattach($message) {
    $patterns = "/\[attach\]\d+\[\/attach\]/i";
    //$patterns = '/\[attach\]\d+/im';
    preg_match_all($patterns,$message,$arr);

    SeasLog::info(" attachment:" . json_encode($arr));
    foreach ($arr[0] as $item){
        $patterns = '/\d+/';

        preg_match($patterns,$item,$newitem);

        $tableid=table_forum_attachment::fetch_tableid_all_by_aid($newitem[0]);
        $attachment = table_forum_attachment_n::fetch($tableid[0]->tableid,$newitem[0],true);
        SeasLog::info(" attachment:" . json_encode($attachment));
        $image=urlencode($attachment['attachment']);

        $message = preg_replace("/\[attach\]".$newitem[0]."\[\/attach\]/i","[img]".Config::$data['imageserver']."v1/image/getimages?get=".$image."[/img]",$message);
        print_r($message);
    }
    return $message;
}
function rest_get($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
}

function getavatarbak($uid, $size = 'middle', $token = "")
{
    $ucenterurl = "/uc_server";

    $size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
    $uid = abs(intval($uid));
    $uid = sprintf("%09d", $uid);
    $dir1 = substr($uid, 0, 3);
    $dir2 = substr($uid, 3, 2);
    $dir3 = substr($uid, 5, 2);
    $file = $ucenterurl . '/data/avatar/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, -2) . '_avatar_' . $size . '.jpg';

    $file .= "?random=" . $token;

    return $file;


}

function getavatar($uid, $size = 'middle', $token = "")
{
    $defaultUrl = 'https://bkjk-public-dev-1256212241.image.myqcloud.com/gh-forum/lljr/709d1d31dc47636e4f5ccbfd07601c19-default-avator.png';
    $return="";
    if ($return = getCache("avatar-aid-" . $uid) && $return != "") {
        SeasLog::info("avatar cache return :" . $return);
        return Config::$data['oss'].$return;
    } else {
        $hash = table_ll_avatar::fetchbyuid($uid);

        if (isset($hash['avatarhash'])){
            $return = $hash['avatarhash'];
        }
        else{
            $return ="";
        }

        setCache("avatar-aid-" . $uid, $hash['avatarhash'], "3600");
    }
    if ($return==""){
        return $defaultUrl;
    }else{
        return Config::$data['oss'].$hash['avatarhash'];
    }
}

function avatarsave($aid, $uid, $hash)
{
    $data['aid'] = $aid;
    $data['uid'] = $uid;
    $data['hash'] = $hash;

}

function getactorIdbyuid($uid)
{
    if ($return = getCache("aid-" . $uid)) {
        return $return;
    } else {
        $aid = table_common_function::fetch_actorid($uid);
        setCache("aid-" . $uid, $aid, "3600");
    }
    return $aid;
}

function getforumname($fid)
{
    if ($return = getCache("aid-" . $fid)) {
        return $return;
    } else {
        $aid = table_forum_forum::fetch_actorid($fid);
        setCache("aid-" . $fid, $aid, "3600");
    }
    return $aid;
}


function getforuminfo($fid)
{
    if ($return = getCacheArray("forum-info-" . $fid)) {

        return $return;
    } else {
        $data = table_forum_forum::fetch_info_by_fid($fid);

        setCacheArray("forum-info-" . $fid, $data, "60");
    }
    return $data;
}

function getvar(&$str)
{
    if (isset($str) && $str != "") {
        return $str;
    } else {
        return "";
    }
}

function getgroup($uid)
{
    if ($return = getCacheArray("group-aid-" . $uid)) {
        return $return;
    } else {
        $return = table_common_member::fetchgroup($uid);
        getCacheArray("group-aid-" . $uid, $return, "3600");
    }
    return $return;
}

function getuidbyactorId($aid)
{

    if ($return = getCache("uid-" . $aid)) {

        return $return;
    } else {
        $uid = table_common_function::fetch_uid($aid);
        setCache("uid-" . $aid, $return, "3600");
    }
    return $uid;
}


function getHeatLevel($score)
{
    if ($data = getCache("iconlevels")) {
        $data = explode(",", $data);
    } else {
        $data = table_common_setting::fetch('heatthread', true);
        setCache("iconlevels", $data['iconlevels'], '36000');
        $data = explode(",", $data['iconlevels']);

    }
    if ($score < $data[0]) return "0";
    if ($score > $data[0] && $score <= $data[1]) return "1";
    if ($score > $data[1] && $score <= $data[2]) return "2";
    if ($score > $data[2]) return "3";

}

function getlikes($postid)
{
    if ($data = getCache("iconlevels")) {
        $data = explode(",", $data);
    } else {
        $data = table_common_setting::fetch('heatthread', true);
        setCache("iconlevels", $data['iconlevels'], '36000');
        $data = explode(",", $data['iconlevels']);

    }
    if ($score < $data[0]) return "0";
    if ($score > $data[0] && $score <= $data[1]) return "1";
    if ($score > $data[1] && $score <= $data[2]) return "2";
    if ($score > $data[2]) return "3";

}


function errorlog($string)
{
    $filename = libfile("logs/", "#error_logs");
    $fp = fopen($filename, 'a');
    $line = array($string);
    fputcsv($fp, $line);
    fclose($fp);
}

function dgmdate($timestamp, $format = 'dt', $timeoffset = '9999', $uformat = '')
{
    $lang = array(
        'before' => '前',
        'day' => '天',
        'month' => '个月',
        'yday' => '昨天',
        'byday' => '前天',
        'hour' => '小时',
        'half' => '半',
        'min' => '分钟',
        'sec' => '秒',
        'now' => '刚刚',
    );
    $dformat = 'Y-n-j';
    $tformat = 'H:i';
    $dtformat = $dformat . ' ' . $tformat;
    // $format == 'u' && !$_G['setting']['dateconvert'] && $format = 'dt';
    static $dformat, $tformat, $dtformat, $offset, $lang;

    $timeoffset = $timeoffset == 9999 ? $offset : $timeoffset;
    $timestamp += $timeoffset * 3600;
    $format = empty($format) || $format == 'dt' ? $dtformat : ($format == 'd' ? $dformat : ($format == 't' ? $tformat : $format));
    if ($format == 'u') {
        $todaytimestamp = strtotime(date('Y-m-d'));
        $s = gmdate(!$uformat ? $dtformat : $uformat, $timestamp);
        $time = time() - $timestamp;
        if ($timestamp >= $todaytimestamp) {
            if ($time > 3600) {
                $return = intval($time / 3600) . '&nbsp;' . $lang['hour'] . $lang['before'];
            } elseif ($time > 1800) {
                $return = $lang['half'] . $lang['hour'] . $lang['before'];
            } elseif ($time > 60) {
                $return = intval($time / 60) . '&nbsp;' . $lang['min'] . $lang['before'];
            } elseif ($time > 0) {
                $return = $time . '&nbsp;' . $lang['sec'] . $lang['before'];
            } elseif ($time == 0) {
                $return = $lang['now'];
            } else {
                $return = $s;
            }
            if ($time >= 0 && !defined('IN_MOBILE')) {
                $return = '<span title="' . $s . '">' . $return . '</span>';
            }
        } elseif (($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
            if ($days == 0) {
                $return = $lang['yday'] . '&nbsp;' . gmdate($tformat, $timestamp);
            } elseif ($days == 1) {
                $return = $lang['byday'] . '&nbsp;' . gmdate($tformat, $timestamp);
            } else {
                $return = ($days + 1) . '&nbsp;' . $lang['day'] . $lang['before'];
            }
            if (!defined('IN_MOBILE')) {
                $return = '<span title="' . $s . '">' . $return . '</span>';
            }
        } else {
            $return = $s;
        }
        return $return;
    } else {
        return gmdate($format, $timestamp);
    }
}

function m_dgmdate($timestamp, $format = 'dt', $timeoffset = '9999', $uformat = '')
{
    $format = 'u';
    $lang = array(
        'before' => '前',
        'day' => '天',
        'month' => '个月',
        'yday' => '昨天',
        'byday' => '前天',
        'hour' => '小时',
        'half' => '半',
        'min' => '分钟',
        'sec' => '秒',
        'now' => '刚刚',
    );
    $dformat = 'Y-n-j';
    $tformat = 'H:i';
    $dtformat = $dformat . ' ' . $tformat;
    if ($format == 'u') {
        $todaytimestamp = strtotime(date('Y-m-d'));
        $s = gmdate(!$uformat ? $dtformat : $uformat, $timestamp);
        $time = time() - $timestamp;
        if ($timestamp >= $todaytimestamp) {
            if ($time > 3600) {
                $return = intval($time / 3600) . 'function.php' . $lang['hour'] . $lang['before'];
            } elseif ($time > 1800) {
                $return = $lang['half'] . $lang['hour'] . $lang['before'];
            } elseif ($time > 60) {
                $return = intval($time / 60) . 'function.php' . $lang['min'] . $lang['before'];
            } elseif ($time > 0) {
                $return = $time . '' . $lang['sec'] . $lang['before'];
            } elseif ($time == 0) {
                $return = $lang['now'];
            } else {
                $return = $s;
            }

        } elseif (($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
            if ($days == 0) {
                $return = $lang['yday'] . '' . gmdate($tformat, $timestamp);
            } elseif ($days == 1) {
                $return = $lang['byday'] . '' . gmdate($tformat, $timestamp);
            } else {
                $return = ($days + 1) . '' . $lang['day'] . $lang['before'];
            }

        } elseif (($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days > 6) {
            if ($days < 30) {
                $return = "7" . '' . $lang['day'] . $lang['before'];
            }
            if ($days >= 30) {
                $mon = intval($days / 30);
                $return = $mon . '' . $lang['month'] . $lang['before'];
            }

        } else {
            $return = $s;
        }
        return $return;
    } else {
        return gmdate($format, $timestamp);
    }
}

function parseurl($url, $text, $scheme)
{
    global $_G;
    if (!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
        $url = $matches[0];
        $length = 65;
        if (strlen($url) > $length) {
            $text = substr($url, 0, intval($length * 0.5)) . ' ... ' . substr($url, -intval($length * 0.3));
        }
        $nofllow = strpos($url, $_G['siteurl']) !== 0 ? '" rel="nofollow' : '';
        return '<a href="' . (substr(strtolower($url), 0, 4) == 'www.' ? 'http://' . $url : $url) . $nofllow . '" target="_blank">' . $text . '</a>';
    } else {
        $url = substr($url, 1);
        if (substr(strtolower($url), 0, 4) == 'www.') {
            $url = 'http://' . $url;
        }
        $url = !$scheme ? $_G['siteurl'] . $url : $url;
        $nofllow = strpos($url, $_G['siteurl']) !== 0 ? '" rel="nofollow' : '';
        return '<a href="' . $url . $nofllow . '" target="_blank">' . $text . '</a>';
    }
}