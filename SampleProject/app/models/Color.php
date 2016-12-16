<?php

namespace models;

/**
 * Class to correspond with the Colors table.  Primary functions provide retrieval
 * and validation of colors.
 *
 * @author David Mans
 *        
 */
class Color extends Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Verifies that the provided color is located in the colors table.
	 *   
	 * @param string $color
	 * @return boolean
	 */
	public function validateColor($color)
	{
		$colorArray = $this->fetchColors();
				
		if(in_array($color, $colorArray))
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
	
	/**
	 * Retreives all of the colors stored in the colors table as an associative array.
	 * 
	 * @return array
	 */
	public function fetchColors()
	{
		$sql = "SELECT color FROM colors";
		
		$result = $this->dbConn->query($sql);
		
		$colorArray = array();
		
		while($row = $result->fetch_assoc())
		{
			array_push($colorArray, $row["color"]);
		}
		
		return $colorArray;
	}
	
}

?>