<?php
/**
 * @describe	
 * @author	yurixu	2016/11/15
 */
require_once "Lj.Command.php";
Class LjConnection {
    /**
     * @var string The Data Source Name, or DSN, contains the information required to connect to the database.
     * @see http://www.php.net/manual/en/function.PDO-construct.php
     *
     * Note that if you're using GBK or BIG5 then it's highly recommended to
     * update to PHP 5.3.6+ and to specify charset via DSN like
     * 'mysql:dbname=mydatabase;host=127.0.0.1;charset=GBK;'.
     */
    public $connectionString;
    /**
     * @var string the username for establishing DB connection. Defaults to empty string.
     */
    public $username='';
    /**
     * @var string the password for establishing DB connection. Defaults to empty string.
     */
    public $password='';

    public $pdoClass = 'PDO';

    private $_pdo;
    private $_active=false;

    /**
     * Constructor.
     * Note, the DB connection is not established when this connection
     * instance is created. Set {@link setActive active} property to true
     * to establish the connection.
     * @param string $dsn The Data Source Name, or DSN, contains the information required to connect to the database.
     * @param string $username The user name for the DSN string.
     * @param string $password The password for the DSN string.
     * @see http://www.php.net/manual/en/function.PDO-construct.php
     */
    public function __construct($dsn='',$username='',$password='')
    {
        $this->connectionString=$dsn;
        $this->username=$username;
        $this->password=$password;
    }

    /**
     * Close the connection when serializing.
     * @return array
     */
    public function __sleep()
    {
        $this->close();
        return array_keys(get_object_vars($this));
    }

    /**
     * Initializes the component.
     * This method is required by {@link IApplicationComponent} and is invoked by application
     * when the CDbConnection is used as an application component.
     * If you override this method, make sure to call the parent implementation
     * so that the component can be marked as initialized.
     */
    public function init()
    {
        $this->setActive(true);
    }

    /**
     * Open or close the DB connection.
     * @param boolean $value whether to open or close DB connection
     * @throws CException if connection fails
     */
    public function setActive($value)
    {
        if($value!=$this->_active)
        {
            if($value)
                $this->open();
            else
                $this->close();
        }
    }

    /**
     * Opens DB connection if it is currently not
     * @throws CException if connection fails
     */
    protected function open()
    {
        if($this->_pdo===null)
        {
            if(empty($this->connectionString))
                throw new Exception('LjConnection.connectionString cannot be empty.');
            try
            {
                $this->_pdo=$this->createPdoInstance();
                $this->initConnection($this->_pdo);
                $this->_active=true;
            }
            catch(PDOException $e)
            {
                throw new Exception('LjConnection failed to open the DB connection.',(int)$e->getCode(),$e->errorInfo);
            }
        }
    }

    /**
     * Creates the PDO instance.
     * When some functionalities are missing in the pdo driver, we may use
     * an adapter class to provide them.
     * @throws CDbException when failed to open DB connection
     * @return PDO the pdo instance
     */
    protected function createPdoInstance()
    {
        $pdoClass=$this->pdoClass;
        if(!class_exists($pdoClass))
            throw new Exception('LjConnection is unable to find PDO class . Make sure PDO is installed correctly.');

        @$instance=new $pdoClass($this->connectionString,$this->username,$this->password);

        if(!$instance)
            throw new Exception('LjConnection failed to open the DB connection.');

        return $instance;
    }

    /**
     * Initializes the open db connection.
     * This method is invoked right after the db connection is established.
     * The default implementation is to set the charset for MySQL, MariaDB and PostgreSQL database connections.
     * @param PDO $pdo the PDO instance
     */
    protected function initConnection($pdo)
    {
        $driver=strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        if(in_array($driver,array('pgsql','mysql','mysqli')))
            $pdo->exec('SET NAMES '.$pdo->quote('UTF8'));
    }

    /**
     * Closes the currently active DB connection.
     * It does nothing if the connection is already closed.
     */
    protected function close()
    {
        $this->_pdo=null;
    }

    /**
     * Returns the PDO instance.
     * @return PDO the PDO instance, null if the connection is not established yet
     */
    public function getPdoInstance()
    {
        return $this->_pdo;
    }

    /**
     * Creates a command for execution.
     * @param mixed $query the DB query to be executed. This can be either a string representing a SQL statement,
     * or an array representing different fragments of a SQL statement. Please refer to {@link CDbCommand::__construct}
     * for more details about how to pass an array as the query. If this parameter is not given,
     * you will have to call query builder methods of {@link CDbCommand} to build the DB query.
     * @return CDbCommand the DB command
     */
    public function createCommand($query=null)
    {
        $this->setActive(true);
        return new LjCommand($this,$query);
    }
}