<?php

namespace models;

/**
 * Class to correspond with the Votes table. Allows for the retrieval of vote count by color.
 * 
 * @author David Mans
 *
 */
class Votes extends Model 
{
	/**
	 * @var string
	 */
	protected $color;
	
	/**
	 * @var string
	 */
	protected $city;
	
	/**
	 * @var int
	 */
	protected $votecount;
	
	function __construct() 
	{
		parent::__construct();
	}
	
	/**
	 * Retreives the total vote count for the color that was provided in the parameter.  This
	 * color is also validated ensure valid data is being submitted to the database.
	 * 
	 * @param string $providedColor
	 * @return array
	 */
	public function fetchVoteCountByColor($providedColor)
	{
		
		$sql = "SELECT SUM(votecount) AS count FROM votes WHERE color = ?";

		$statement = $this->dbConn->prepare($sql);
		
		$color = new Color();
		
		//Verifying that the provided color is valid so no poor or malicious data is sent
		if($color->ValidateColor($providedColor))
		{
			$statement->bind_param("s", $providedColor);
			
			$statement->execute();
			
			$statement->bind_result($count);
			
			$statement->fetch();
			
			return $count;
		}
		
	}
	
}

?>