<?php
/**
 * @describe	
 * @author	yurixu	2016/11/15
 */
Class LjCommand {
    private $_connection;
    private $_text;
    private $_statement;
    private $_fetchMode = array(PDO::FETCH_ASSOC);

    /**
     * Constructor.
     * @param LjConnection $connection the database connection
     * @param mixed $query the DB query to be executed. This can be either
     * a string representing a SQL statement, or an array whose name-value pairs
     * will be used to set the corresponding properties of the created command object.
     */
    public function __construct(LjConnection $connection,$query=null)
    {
        $this->_connection=$connection;
        if(is_array($query))
        {
            foreach($query as $name=>$value)
                $this->$name=$value;
        }
        else
            $this->setText($query);
    }

    /**
     * Set the statement to null when serializing.
     * @return array
     */
    public function __sleep()
    {
        $this->_statement=null;
        return array_keys(get_object_vars($this));
    }

    /**
     * Set the default fetch mode for this statement
     * @param mixed $mode fetch mode
     * @return static
     * @see http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php
     * @since 1.1.7
     */
    public function setFetchMode($mode)
    {
        $params=func_get_args();
        $this->_fetchMode = $params;
        return $this;
    }

    /**
     * @return string the SQL statement to be executed
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * Specifies the SQL statement to be executed.
     * Any previous execution will be terminated or cancel.
     * @param string $value the SQL statement to be executed
     * @return static this command instance
     */
    public function setText($value)
    {
        $this->_text=$value;
        $this->cancel();
        return $this;
    }

    /**
     * @return CDbConnection the connection associated with this command
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * @return PDOStatement the underlying PDOStatement for this command
     * It could be null if the statement is not prepared yet.
     */
    public function getPdoStatement()
    {
        return $this->_statement;
    }

    /**
     * Prepares the SQL statement to be executed.
     * For complex SQL statement that is to be executed multiple times,
     * this may improve performance.
     * For SQL statement with binding parameters, this method is invoked
     * automatically.
     * @throws CDbException if CDbCommand failed to prepare the SQL statement
     */
    public function prepare()
    {
        if($this->_statement==null)
        {
            try
            {
                $this->_statement=$this->getConnection()->getPdoInstance()->prepare($this->getText());
            }
            catch(Exception $e)
            {
                $errorInfo=$e instanceof PDOException ? $e->errorInfo : null;
                throw new Exception('LjCommand failed to prepare the SQL statement:'.$e->getMessage(), (int)$e->getCode(),$errorInfo);
            }
        }
    }

    /**
     * Cancels the execution of the SQL statement.
     */
    public function cancel()
    {
        $this->_statement=null;
    }

    /**
     * Executes the SQL statement.
     * This method is meant only for executing non-query SQL statement.
     * No result set will be returned.
     * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
     * to {@link bindParam} and {@link bindValue}. If you have multiple input parameters, passing
     * them in this way can improve the performance. Note that if you pass parameters in this way,
     * you cannot bind parameters or values using {@link bindParam} or {@link bindValue}, and vice versa.
     * Please also note that all values are treated as strings in this case, if you need them to be handled as
     * their real data types, you have to use {@link bindParam} or {@link bindValue} instead.
     * @return integer number of rows affected by the execution.
     * @throws CDbException execution failed
     */
    public function execute($params=array())
    {
        try
        {
            $this->prepare();
            if($params===array())
                $this->_statement->execute();
            else
                $this->_statement->execute($params);
            $n=$this->_statement->rowCount();
            return $n;
        }
        catch(Exception $e)
        {
            $errorInfo=$e instanceof PDOException ? $e->errorInfo : null;
            $message=$e->getMessage();
            throw new Exception('LjCommand failed to execute the SQL statement:'.$message,(int)$e->getCode(),$errorInfo);
        }
    }

    /**
     * Executes the SQL statement and returns all rows.
     * @param boolean $fetchAssociative whether each row should be returned as an associated array with
     * column names as the keys or the array keys are column indexes (0-based).
     * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
     * to {@link bindParam} and {@link bindValue}. If you have multiple input parameters, passing
     * them in this way can improve the performance. Note that if you pass parameters in this way,
     * you cannot bind parameters or values using {@link bindParam} or {@link bindValue}, and vice versa.
     * Please also note that all values are treated as strings in this case, if you need them to be handled as
     * their real data types, you have to use {@link bindParam} or {@link bindValue} instead.
     * @return array all rows of the query result. Each array element is an array representing a row.
     * An empty array is returned if the query results in nothing.
     * @throws CException execution failed
     */
    public function queryAll($fetchAssociative=true,$params=array())
    {
        return $this->queryInternal('fetchAll',$fetchAssociative ? $this->_fetchMode : PDO::FETCH_NUM, $params);
    }

    /**
     * Executes the SQL statement and returns the first row of the result.
     * This is a convenient method of {@link query} when only the first row of data is needed.
     * @param boolean $fetchAssociative whether the row should be returned as an associated array with
     * column names as the keys or the array keys are column indexes (0-based).
     * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
     * to {@link bindParam} and {@link bindValue}. If you have multiple input parameters, passing
     * them in this way can improve the performance. Note that if you pass parameters in this way,
     * you cannot bind parameters or values using {@link bindParam} or {@link bindValue}, and vice versa.
     * Please also note that all values are treated as strings in this case, if you need them to be handled as
     * their real data types, you have to use {@link bindParam} or {@link bindValue} instead.
     * @return mixed the first row (in terms of an array) of the query result, false if no result.
     * @throws CException execution failed
     */
    public function queryRow($fetchAssociative=true,$params=array())
    {
        return $this->queryInternal('fetch',$fetchAssociative ? $this->_fetchMode : PDO::FETCH_NUM, $params);
    }

    /**
     * Executes the SQL statement and returns the first column of the result.
     * This is a convenient method of {@link query} when only the first column of data is needed.
     * Note, the column returned will contain the first element in each row of result.
     * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
     * to {@link bindParam} and {@link bindValue}. If you have multiple input parameters, passing
     * them in this way can improve the performance. Note that if you pass parameters in this way,
     * you cannot bind parameters or values using {@link bindParam} or {@link bindValue}, and vice versa.
     * Please also note that all values are treated as strings in this case, if you need them to be handled as
     * their real data types, you have to use {@link bindParam} or {@link bindValue} instead.
     * @return array the first column of the query result. Empty array if no result.
     * @throws CException execution failed
     */
    public function queryColumn($params=array())
    {
        return $this->queryInternal('fetchAll',array(PDO::FETCH_COLUMN, 0),$params);
    }

    /**
     * @param string $method method of PDOStatement to be called
     * @param mixed $mode parameters to be passed to the method
     * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
     * to {@link bindParam} and {@link bindValue}. If you have multiple input parameters, passing
     * them in this way can improve the performance. Note that if you pass parameters in this way,
     * you cannot bind parameters or values using {@link bindParam} or {@link bindValue}, and vice versa.
     * Please also note that all values are treated as strings in this case, if you need them to be handled as
     * their real data types, you have to use {@link bindParam} or {@link bindValue} instead.
     * @throws CDbException if CDbCommand failed to execute the SQL statement
     * @return mixed the method execution result
     */
    private function queryInternal($method,$mode,$params=array())
    {
        try
        {
            $this->prepare();
            if($params===array())
                $this->_statement->execute();
            else
                $this->_statement->execute($params);

            $mode=(array)$mode;
            call_user_func_array(array($this->_statement, 'setFetchMode'), $mode);
            $result=$this->_statement->$method();
            $this->_statement->closeCursor();
            return $result;
        }
        catch(Exception $e)
        {
            $errorInfo=$e instanceof PDOException ? $e->errorInfo : null;
            $message=$e->getMessage();
            throw new Exception('LjCommand failed to execute the SQL statement:'.$message,(int)$e->getCode(),$errorInfo);
        }
    }
}