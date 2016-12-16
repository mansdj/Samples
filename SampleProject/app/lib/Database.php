<?php

namespace lib;

/**
 * Handles the database connection for the Achieve3000 assessment
 *
 * @author David Mans
 *        
 */
class Database {
	
	
	/**
	 * Database connection variables
	 * 
	 * Note: The authentication credentials are unsecure, use only for demonstration purposes
	 */
	private static $dbHost = "localhost";
	private static $dbUser = "root";
	private static $dbPass = "root";
	private static $dbName = "achieve3000";
	
	/**
	 * The database connection object
	 * 
	 * @var \mysqli
	 */
	protected $dbConnection;
	
	
	/**
	 * Connects to the database, instantiating a mysqli object, throwing an
	 * exception if there was an error connecting to the database.  
	 * 
	 * @throws \Exception
	 */
	function __construct() 
	{
		//Execute the db connection
		$this->Connect();
		
		//Check for connection erros
		if($this->dbConnection->connect_error)
			throw new \Exception($this->dbConnection->connect_error);
		
		//Set the default database character set.  This will be important for escaping strings
		$charset = "utf8";
		$this->dbConnection->set_charset($charset);
	}
	
	/**
	 * Getter for retreiving the database mysqli object
	 * 
	 * @return the $dbConnection
	 */
	public function getDbConnection() 
	{
		return $this->dbConnection;
	}

	/**
	 * Basic mysqli connection method.
	 *
	 */
	protected function Connect()
	{
		//Connect to the database and set the mysqli object
		$this->dbConnection = new \mysqli(self::$dbHost, self::$dbUser, self::$dbPass, self::$dbName);
	}
	

	public function EscapeString($input)
	{
		//Set the escaped string
		$output = $this->dbConnection->real_escape_string($input);
	
		return $output;
	}
}

?>