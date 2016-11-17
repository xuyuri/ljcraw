<?php
/**
 * Created by PhpStorm.
 * User: Eliot
 * Date: 2016/11/9
 * Time: 22:52
 */
class Helper
{
    public static $maxInt = 99999999;
    public static $minInt = -99999999;

    /**
     * 校验整数
     * @param $int
     * @return int
     * @author      yurixu 2016-11-09
     * @example     Helper::CheckInt();
     */
    public static function CheckInt($int) {
        $int = (int)$int;
        if ($int > self::$maxInt || $int < self::$minInt) {
            exit;
        }
        return $int;
    }

    /**
     * 校验正整数
     * @param $int
     * @return int
     * @author      yurixu 2016-11-09
     * @example     Helper::CheckPlusInt();
     */
    public static function CheckPlusInt($int) {
        $int = (int)$int;
        return ($int > 0 && $int <= self::$maxInt) ? $int : 0;
    }

    /**
     * 校验负整数
     * @param $int
     * @return int
     * @author      yurixu 2016-11-09
     * @example     Helper::CheckMinusInt();
     */
    public static function CheckMinusInt($int) {
        $int = (int)$int;
        return ($int < 0 && $int >= self::$minInt) ? $int : 0;
    }

    /**
     * 获取页面内容
     * @param string $url   页面URL
     * @return string       页面内容
     * @author      yurixu 2016-11-16
     * @example     Helper::getContents();
     */
    public static function getContents($url) {
        $result = '';
        if(!empty($url)) {
            if(LjConfig::CURL_PROXY_HOST != '0.0.0.0') {
                $proxy = stream_context_create(array(
                    'http' => array(
                        'timeout' => 5,
                        'proxy' => LjConfig::CURL_PROXY_HOST.':'.LjConfig::CURL_PROXY_PORT,
                        'request_fulluri' => true,
                    ),
                ));
                $result = file_get_contents($url, false, $proxy);
            } else {
                $result = file_get_contents($url);
            }
        }
        return $result;
    }
}