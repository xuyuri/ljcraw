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
     * 处理字符串，过滤特殊字符，防止SQL注入
     * @param $string
     * @return string
     * @author      yurixu 2016-11-09
     * @example     Helper::EscapeString();
     */
    public static function EscapeString($string) {
        if(!empty($string)) {
            $string = trim($string);
            $string = htmlspecialchars($string, ENT_QUOTES);

            if (get_magic_quotes_gpc()) {
                $string = stripslashes($string);
            }
            /*// 如果不是数字则加引号
            if (!is_numeric($string)) {
                $string = mysql_real_escape_string($string);
            }*/
        }

        return $string;

    }

    /**
     * 返回多维数组中指定的一列，组合成一个新的一维数组
     * 【备注】PHP5.5会自带array_column()函数，此方法用于PHP5.5以下版本
     * @param array $array          原数组
     * @param int $type             0：对整个数组获取value组成新数组；1：获取某一列value，返回一维数组
     * @param string $columnName    列名
     * @return array
     * @author      yurixu 2016-11-16
     * @example     Helper::arrayColumn();
     */
    public static function  arrayColumn(array $array, $columnName='', $type=1) {
        return array_map(function ($element) use ($type, $columnName) {
            return $type ? $element[$columnName] : array_values($element);
        }, $array);
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

    /**
     * 计算两个日期之间的所有日期
     * @param string $start     开始日期
     * @param string $end       结束日期
     * @return array            key为年月，value为日期集合
     * @author      yurixu 2016-11-28
     * @example     Helper::getDateList();
     */
    public static function getDateList($start, $end) {
        $result = array();
        $start = strtotime($start);
        $end = strtotime($end);
        while ($start <= $end) {
            $result[] = date('Ymd',$start);
            $start = strtotime('+1 day',$start);
        }
        return $result;
    }

    /**
     * 获取某天是当月的第几周
     * @param string $timestamp     时间戳
     * @return string               第N周
     * @author      yurixu 2016-12-21
     * @example     Helper::getWeekNumber();
     */
    public static function getWeekNumber($timestamp) {
        return !empty($timestamp) ? date("W", $timestamp) - date("W", strtotime(date("Y-m-01", $timestamp))) + 1 : 0;
    }

    /**
     * 数组分页
     * @param array $array  待分页数组
     * @param int   $count  每页记录数
     * @param int   $page   页码
     * @param int   $order  分页顺序，0：正序；1：倒序
     * @return array        分页后的数据
     * @author      yurixu 2016-12-21
     * @example     Helper::arrayPage();
     */
    public static function arrayPage($array, $count, $page=1, $order=0) {
        $result = array();
        if(is_array($array) && !empty($array) && $count > 0) {
            $page = empty($page) ? '1' : $page;
            $start = ($page - 1) * $count; //计算每次分页的开始位置
            if ($order == 1) {
                $array = array_reverse($array);
            }
            $result = array_slice($array, $start, $count);
        }
        return $result;
    }

    /**
     * 数组转换
     * @param array $array      二维数组
     * @param string $key       作为目标键的键名
     * @param string|array $val 作为值的键名
     * @return array            目标数组
     * @author      yurixu 2016-12-21
     * @example     Helper::arrayKeyVal();
     * @example string          原数组：array(0 => array('id' => 1, 'name' = 'aa')),
     *                          调用Helper::arrayKeyVal(array, 'id', 'name')返回数组array(1 => 'name')
     * @example array           原数组：array(array('id' => 1, 'name' => 'yurixu', 'age' => 23)),
     *                          调用Helper::arrayKeyVal(array, 'id', array('name', 'age'))返回数组array(1=>array('name' => 'yurixu', 'age' => 23))
     */
    public static function arrayKeyVal($array=array(), $key='', $val='') {
        $result = array();
        $key = Helper::EscapeString($key);
        if (!empty($array)) {
            if (is_array($val) && !empty($val)) {
                foreach ($array as $item) {
                    foreach ($val as $v) {
                        $result[$item[$key]][$v] = $item[$v];
                    }
                }
            } else {
                $val = Helper::EscapeString($val);
                foreach ($array as $item) {
                    $result[$item[$key]] = $item[$val];
                }
            }
        }
        return $result;
    }
}