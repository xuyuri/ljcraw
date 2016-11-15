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

    /**
     * Search single data
     * @param string    $sql        the sql to search
     * @param array     $params     params bind to sql
     * @return array|CDbDataReader|mixed
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
     * @return array|CDbDataReader|mixed
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
     * @return array|CDbDataReader|mixed
     * @author          yurixu 2016-11-15
     * @example         ZDBTool::queryAll()
     */
    public function execute($sql, $params=array()) {
        $result = $this->connect()->createCommand($sql)->execute($params);
        return $result ? $result : 0;
    }
}