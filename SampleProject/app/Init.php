<?php

namespace app;

/**
 * Bootstrapping class that handles autoloading and setting application path variables
 * 
 * @author David Mans
 */
final class Init 
{
	/**
	 * Establishes the application directory and registers the autoloader
	 */
	public function __construct()
	{
		define("APP_PATH", dirname(__FILE__));
	
		spl_autoload_extensions("php");
	
		spl_autoload_register(array($this, 'Autoloader'), true);
		
	}
	
	/**
	 * Handles the auto loading of classes based on namespace and class name.
	 * 
	 * @param string $class
	 * @throws \Exception
	 */
	private function Autoloader($class)
	{
		//Rewrite the class string, swapping the backslash for a forward slash
		$class = str_replace("\\", "/", $class);
	
		$filepath = APP_PATH . "/{$class}.php";
		
		//Ensuring the file exists and is readable
		if(is_readable($filepath))
		{
			require_once "{$class}.php";
		}
		else
		{
			throw new \Exception("Trying to load a class that either doesn't exist or is unreadable.");
		}
	}
}

?>