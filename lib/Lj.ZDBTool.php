<?php
/**
 * @describe	
 * @author	yurixu	2016/11/15
 */
require_once "Lj.ZDBTool.php";
require_once "Lj.Connection.php";
Class ZDBTool {
    /**
     * @var object  the object of LjConnection
     */
    protected static $conn;

    protected static $redis;

    /**
     * get the object of LjConnection
     * @return bool|LjConnection|object
     * @author          yurixu 2016-11-19
     * @example         ZDBTool::getConnection()
     */
    public static function getConnection()
    {
        if (!isset(self::$conn)) {
            self::$conn = new LjConnection(LjConfig::CITY . LjConfig::DB_NAME . LjConfig::BD_CONNECTION, LjConfig::DB_USER_NAME, LjConfig::DB_PASSWORD);
        }
        if (self::$conn === false) {
            return false;
        }
        return self::$conn;
    }

    /**
     *  get the object of Redis
     * @return bool|Redis
     * @author          yurixu 2016-12-19
     * @example         ZDBTool::redis()
     */
    public static function redis() {
        if(!isset(self::$redis)) {
            self::$redis = new Redis();
            self::$redis->pconnect(LjConfig::REDIS_HOST, LjConfig::REDIS_PORT);
        }
        if(self::$redis === false) {
            return false;
        }
        return self::$redis;
    }

    /**
     * Search single data
     * @param string    $sql        the sql to search
     * @param array     $params     params bind to sql
     * @return array    result
     * @author          yurixu 2016-11-15
     * @example         ZDBTool::queryRow()
     */
    public static function queryRow($sql, $params=array()) {
        $result = self::getConnection()->createCommand($sql)->queryRow($params);
        return $result ? $result : array();
    }

    /**
     * Search data set
     * @param string    $sql        the sql to search
     * @param array     $params     params bind to sql
     * @return array    result
     * @author          yurixu 2016-11-15
     * @example         ZDBTool::queryAll()
     */
    public static function queryAll($sql, $params=array()) {
        $result = self::getConnection()->createCommand($sql)->queryAll($params);
        return $result ? $result : array();
    }

    /**
     * execute sql
     * @param string    $sql        the sql to execute
     * @param array     $params     params bind to sql
     * @return int      影响记录数
     * @author          yurixu 2016-11-15
     * @example         ZDBTool::queryAll()
     */
    public static function execute($sql, $params=array()) {
        $result = self::getConnection()->createCommand($sql)->execute($params);
        return $result ? $result : 0;
    }

    /**
     * 更新一行记录
     * @param string $table     数据表名称
     * @param int    $id        主键ID
     * @param array  $data      更新字段数组，array('agentid' => 1, 'name' => '清华大学')
     * @return int   影响记录数
     * @author       yurixu 2016-11-22
     * @example      ZDBTool::updateRow()
     */
    public static function updateRow($table, $id, $data) {
        date_default_timezone_set('PRC');
        $result = 0;
        $table = Helper::EscapeString($table);
        $id = Helper::CheckPlusInt($id);
        $data = is_array($data) && !empty($data) ? $data : array();

        if(!empty($table) && $id > 0 && !empty($data)) {
            $params = array();
            foreach($data as $k => $v) {
                if($v !== '') {
                    $data[$k] = '`' . $k . '`' . " = :$k ";
                    $params[":$k"] = $v;
                } else {
                    unset($data[$k]);
                }
            }
            if(!array_key_exists('operate_time', $data)) {
                $data['operate_time'] = 'operate_time = :operate_time ';
                $params[':operate_time'] = date('Y-m-d H:i:s');
            }
            $data = implode(',', $data);
            $sql = 'UPDATE '.$table.' SET '. $data. ' WHERE id = :id ';
            $params[':id'] = $id;
            /*echo $sql;
            print_r($params);die;*/
            $result = self::execute($sql, $params);
        }
        return $result;
    }

    /**
     * 批量插入数据
     * @param string $table     数据表名称
     * @param array  $data      插入数据
     * @return int   影响记录数
     * @author       yurixu 2016-11-17
     * @example      ZDBTool::multiInsert()
     */
    public static function multiInsert($table, $data=array()) {
        date_default_timezone_set('PRC');
        $result = 0;
        $name = '';
        $values = '';

        if(is_array($data) && !empty($data)) {
            foreach($data as $k => $v) {
                $value = '';
                if(!array_key_exists('create_time', $v)) {
                    $time = date('Y-m-d H:i:s');
                    $v['create_time'] = $time;
                    $v['operate_time'] = $time;
                }
                foreach ($v as $sk => $sv) {
                    if ($k == 0) {
                        $name .= '`'.$sk.'`' . ',';
                    }
                    $value .= "'".$sv . "',";
                }
                $value = substr($value, 0, strlen($value) - 1);
                $values .= '(' . $value . '),';
            }
            $name = substr($name, 0, strlen($name) - 1);
            $values = substr($values, 0, strlen($values) - 1);
            $sql = "INSERT INTO $table ($name) VALUES $values ";
//            echo $sql;die;
            $result = self::execute($sql);
        }
        return $result;
    }

    public static function insert($table, $data=array()) {
        date_default_timezone_set('PRC');
        $result = 0;

        if(is_array($data) && !empty($data)) {
            if(!array_key_exists('create_time', $data)) {
                $time = date('Y-m-d H:i:s');
                $data['create_time'] = $time;
                $data['operate_time'] = $time;
            }
            $fields = array_keys($data);
            $fields_str = '`'.implode('`,`', $fields).'`';
            $values = array_values($data);
            $values_str = "('".implode("','", $values)."')";
            $sql = "INSERT INTO $table ($fields_str) VALUES $values_str ";
//            echo $sql;die;
            $result = self::execute($sql);
        }
        return $result;
    }

    /**
     * 获取单条数据记录
     * @param string $table     数据表名
     * @param int    $id        主键ID
     * @param array $fields     查询的字段数组
     * @return array            单条数据
     * @author       yurixu 2016-11-23
     * @example      ZDBTool::getInfoByid()
     */
    public static function getInfoByid($table, $id, $fields=array()) {
        $result = array();
        $table = Helper::EscapeString($table);
        $id = Helper::CheckPlusInt($id);
        $fields = is_array($fields) ? $fields : array();

        if(!empty($table) && $id > 0) {
            $select = empty($fields) ? '*' : '`'.implode('`,`', $fields).'`';
            $sql = "SELECT $select FROM $table WHERE id = :id ";
            $params = array(':id' => $id);
            $result = self::queryRow($sql, $params);
        }
        return $result;
    }

    /**
     * 查询
     * @param string $table         数据表名
     * @param array  $fields        查询字段
     * @param string $condition     查询条件
     * @param array  $params        查询条件参数
     * @param int    $type          0：数据集合; 1：单条记录
     * @return array                查询结果
     * @author       yurixu 2016-11-23
     * @example      ZDBTool::getQuery()
     */
    public static function getQuery($table, $fields=array(), $condition='', $params=array(), $type=0) {
        $type = Helper::CheckPlusInt($type);
        $table = Helper::EscapeString($table);
        $fields = !empty($fields) && is_array($fields) ? $fields : array();
        $params = !empty($params) && is_array($params) ? $params : array();
        $condition = (is_string($condition) && !empty($condition)) ? $condition : '';
        $result = array();

        $select = empty($fields) ? '*' : '`'.implode('`,`', $fields).'`';
        $sql = "SELECT $select FROM $table ";
        if(!empty($condition)) {
            $sql .= $condition;
        }
        //echo $sql;die;
        $result = $type ? self::queryRow($sql, $params) :self::queryAll($sql, $params) ;
        return $result;
    }

}