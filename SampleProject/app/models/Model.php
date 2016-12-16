<?php

namespace models;


use lib\Database;
/**
 * Base Model class for all objects to inherit.  Permits the use of the database connection object.
 * 
 * @author David
 *        
 */
class Model 
{
	
	/**
	 * Database connection object 
	 * 
	 * @var mysqli
	 */
	protected $dbConn;
	
	/**
	 * Connect to the database
	 */
	function __construct() 
	{
		$this->connectDB();		
	}
	
	/**
	 * Assigns the database connection object for use by all models.
	 */
	private function connectDB()
	{
		$db = new Database();
		
		$this->dbConn = $db->getDbConnection();
		
	}
}

?>