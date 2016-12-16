<?php

namespace AppData;

/**
 * Wrapper class for the PHP OCI library to connect and
 * interact with an Oracle SQL server.
 *
 * @author David Mans
 * @package AppData
 */
class OracleAdapter implements iDatabase
{

    const defaultCharset = "UTF8";

    /**
     * @var resource
     */
    protected $connection;

    /**
     * @var resource
     */
    protected $stmtId;

    /**
     * @var bool
     */
    protected $dbConnected = false;

    /**
     * @var bool
     */
    protected $dbInTransaction = false;

    /**
     * @var string
     */
    protected $dbHost;

    /**
     * @var string
     */
    protected $dbUser;

    /**
     * @var string
     */
    protected $dbPass;

    /**
     * @var string
     */
    protected $dbName;

    /**
     * @var string
     */
    protected $dbPort;

    /**
     * @var string
     */
    protected $dbSock;

    /**
     * @var array
     */
    protected $dbResult = array();

    /**
     * @var bool
     */
    protected $dbExecuteSuccess;

    /**
     * @var int
     */
    protected $dbAffectedRows;

    /**
     * OracleAdapter constructor.
     *
     * @param string|null $host
     * @param string|null $user
     * @param string|null $pass
     * @param string|null $db
     * @param string|null $port
     * @param string|null $sock
     *
     * @throws \Exception
     */
    public function __construct($host = null, $user = null, $pass = null, $db = null, $port = null, $sock = null)
    {
        $this->dbHost = (!is_null($host)) ? $host : constant('DB_HOST');
        $this->dbUser = (!is_null($user)) ? $user : constant('DB_USER');
        $this->dbPass = (!is_null($pass)) ? $pass : constant('DB_PASS');
        $this->dbName = (!is_null($db)) ? $db : constant('DB');

        if($this->checkConfig())
            $this->connect();
        else
            throw new \Exception("Invalid connection parameters provided");

        oci_set_client_info($this->connection, "");
        oci_set_module_name($this->connection, "");
        oci_set_client_identifier($this->connection, "");

        $this->dbConnected = true;

    }

    /**
     * Connect to the database
     *
     * @throws \Exception
     */
    protected function connect()
    {
        $this->connection = @oci_connect($this->dbUser, $this->dbPass, $this->dbHost, self::defaultCharset);

        if(!$this->connection)
        {
            $msg = oci_error();
            throw new \Exception($msg);
        }
    }

    /**
     * Bind the parameters to the statement.
     *
     * @param array $params Key/value pair (associative) array.
     * @param string|null $action Name for the action for logging purposes
     * @param string $placeholderChar character to signify placeholder
     *
     * @throws \Exception
     */
    public function bindParams(array $params, $action = null, $placeholderChar = '@')
    {
        if(count($params) > 0 && $this->stmtId != null)
        {
            foreach($params as $name => $value)
            {
                oci_bind_by_name($this->stmtId, $placeholderChar . $name, $value);
            }

            if(!is_null($action))
                oci_set_action($this->connection, $action);
        }
        else
            throw new \Exception("Attempting to bind empty values or an empty statement");
    }

    /**
     * Prepares a sql statement for execution
     *
     * @param string $statement
     *
     * @throws \Exception
     */
    public function prepare($statement)
    {
        if(!is_null($statement) && !empty($statement))
        {
            $res = oci_parse($this->connection, $statement);

            if($res != false)
                $this->stmtId = $res;
            else
                $this->stmtId = null;
        }
        else
            throw new \Exception("Attempted to prepare an invalid statement");
    }

    /**
     * Execute a non-fetching statement such as INSERT, UPDATE, DELETE.  If $autoCommit
     * is set to true, the transaction cannot be rolled back
     *
     * @param bool $autoCommit
     *
     * @return int Number of rows affected
     *
     * @throws \Exception
     */
    public function executeNonQuery($autoCommit = false)
    {
        if($this->stmtId != null)
        {
            $this->dbExecuteSuccess = oci_execute($this->stmtId, (!is_null($autoCommit) && $autoCommit !== false) ? OCI_COMMIT_ON_SUCCESS : OCI_NO_AUTO_COMMIT);

            if($this->dbExecuteSuccess)
                $this->dbAffectedRows = oci_num_rows($this->stmtId);
            else
                $this->dbAffectedRows = 0;

            oci_free_statement($this->stmtId);

            return $this->dbAffectedRows;
        }
        else
            throw new \Exception("Statement id is null");
    }

    /**
     * Execute a select statement
     *
     * @return int Number of rows returned
     */
    public function executeQuery()
    {
        if($this->stmtId != null)
        {
            $this->dbExecuteSuccess = oci_execute($this->stmtId, 0);

            if($this->dbExecuteSuccess)
            {
                while($row = oci_fetch_assoc($this->stmtId) != false)
                    array_push($this->dbResult, $row);


                oci_free_statement($this->stmtId);

                return count($this->dbResult);
            }
            else
                return 0;
        }
    }

    /**
     * Get an instance of the database adapter
     *
     * @param null $host
     * @param null $user
     * @param null $pass
     * @param null $db
     * @param null $port
     * @param null $sock
     *
     * @return OracleAdapter
     */
    public static function getDbInstance($host = null, $user = null, $pass = null, $db = null, $port = null, $sock = null)
    {
        $c = __CLASS__;

        return new $c($host, $user, $pass, $db, $port, $sock);
    }

    /**
     * Verify the required connection parameters are set
     *
     * @return bool
     */
    public function checkConfig()
    {
        foreach (['dbHost', 'dbUser', 'dbName'] as $cfgKey)
        {
            if (!property_exists($this, $cfgKey) || empty($this->{$cfgKey}) || is_null($this->{$cfgKey}))
                return false;
        }

        return true;
    }

    /**
     * Verify the database is connected
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->dbConnected;
    }

    /**
     * Not implemented in the oracle adapter.
     *
     * @throws \Exception
     */
    public function inTransaction()
    {
        throw new \Exception("In transaction is not implemented with Oracle");
    }

    /**
     * Not implemented in the oracle adapter.
     *
     * @throws \Exception
     */
    public function beginTransaction()
    {
        throw new \Exception("Begin transaction is not implemented with Oracle");
    }

    /**
     * Commits the current transaction
     *
     * @return bool
     */
    public function commitTransaction()
    {
        return oci_commit($this->connection);
    }

    /**
     * Rolls back the transaction
     *
     * @return bool
     */
    public function rollbackTransaction()
    {
        return oci_rollback($this->connection);
    }

    /**
     * Returns the result property set from the executeQuery method
     *
     * @return array
     */
    public function getResult()
    {
        return $this->dbResult;
    }

    /**
     * Return the error from the last execute
     *
     * @return array | false
     */
    public function getLastExecuteError()
    {
        return oci_error($this->stmtId);
    }

    /**
     * Returns the connection error
     *
     * @return array | false
     */
    public function getLastConnectionError()
    {
        return oci_error();
    }

}