<?php
/**
 * @describe	
 * @author	yurixu	2016/11/15
 */
require_once "Lj.ZDBTool.php";
require_once "Lj.Connection.php";
Class ZDBTool {
    private $connectionString;
    /**
     * @var string the username for establishing DB connection. Defaults to empty string.
     */
    private $username='';
    /**
     * @var string the password for establishing DB connection. Defaults to empty string.
     */
    private $password='';
    /**
     * @var object  the object of LjConnection
     */
    private $conn = null;

    public function connect() {
        if($this->conn === null) {
            $this->connectionString = LjConfig::BD_CONNECTION;
            $this->username = LjConfig::DB_USER_NAME;
            $this->password = LjConfig::DB_PASSWORD;
            $this->conn = new LjConnection($this->connectionString, $this->username, $this->password);
        }
        return $this->conn;
    }

    function __construct() {
        $this->conn = null;
    }

    /**
     * Search single data
     * @param string    $sql        the sql to search
     * @param array     $params     params bind to sql
     * @return array    result
     * @author          yurixu 2016-11-15
     * @example         ZDBTool::queryRow()
     */
    public function queryRow($sql, $params=array()) {
        $result = $this->connect()->createCommand($sql)->queryRow($params);
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
    public function queryAll($sql, $params=array()) {
        $result = $this->connect()->createCommand($sql)->queryAll($params);
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
    public function execute($sql, $params=array()) {
        $result = $this->connect()->createCommand($sql)->execute($params);
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
        $result = 0;
        $table = Helper::EscapeString($table);
        $id = Helper::CheckPlusInt($id);
        $data = is_array($data) && !empty($data) ? $data : array();

        if(!empty($table) && $id > 0 && !empty($data)) {
            $params = array();
            foreach($data as $k => $v) {
                if($v != '') {
                    $data[$k] = '`' . $k . '`' . " = :$k ";
                    $params[":$k"] = $v;
                }
            }
            $data = implode(',', $data);
            $sql = 'UPDATE '.$table.' SET '. $data. ' WHERE id = :id ';
            $params[':id'] = $id;
            /*echo $sql;
            print_r($params);die;*/
            $tool = new ZDBTool();
            $result = $tool->execute($sql, $params);
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
        $result = 0;
        $name = '';
        $values = '';

        if(is_array($data) && !empty($data)) {
            date_default_timezone_set('PRC');
            foreach($data as $k => $v) {
                $value = '';
                if(!array_key_exists('create_time', $v)) {
                    $time = date('Y-m-d H:i:s');
                    $v['create_time'] = $time;
                    $v['operate_time'] = $time;
                }
                foreach ($v as $sk => $sv) {
                    if ($k == 0) {
                        $name .= $sk . ',';
                    }
                    $value .= "'".$sv . "',";
                }
                $value = substr($value, 0, strlen($value) - 1);
                $values .= '(' . $value . '),';
            }
            $name = substr($name, 0, strlen($name) - 1);
            $values = substr($values, 0, strlen($values) - 1);
            $sql = "INSERT INTO $table ($name) VALUES $values ";
            $tool = new ZDBTool();
            $result = $tool->execute($sql);
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
            $select = empty($fields) ? '*' : implode(',', $fields);
            $sql = "SELECT $select FROM $table WHERE id = :id ";
            $params = array(':id' => $id);
            $tool = new ZDBTool();
            $result = $tool->queryRow($sql, $params);
        }
        return $result;
    }

    /**
     *
     * @param $table
     * @param array $fields
     * @param string $condition
     * @param array $params
     * @param int $type
     * @return array
     */
    public function getQuery($table, $fields=array(), $condition='', $params=array(), $type=0) {
        $type = Helper::CheckPlusInt($type);
        $table = Helper::EscapeString($table);
        $fields = !empty($fields) && is_array($fields) ? $fields : array();
        $params = !empty($params) && is_array($params) ? $params : array();
        $condition = (is_string($condition) && !empty($condition)) ? $condition : '';
        $result = array();

        $select = empty($fields) ? '*' : implode(',', $fields);
        $sql = "SELECT $select FROM $table ";
        if(!empty($condition)) {
            $sql .= $condition;
        }
        $tool = new ZDBTool();
        $result = $type ? $tool->queryRow($sql, $params) :$tool->queryAll($sql, $params) ;
        return $result;
    }

}