<?php

include_once "../app/Init.php";

use models\Color;
use models\Votes;
use app\Init;

//Since this is an ajax script, the bootstrapper is ran to make use of autoloading
$init = new Init();

//Although not 100% secure, this attempts to ensure we are receiving an ajax request
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
{
	$color = new Color();
	$response = array();
	$votes = new Votes();
	
	//Ensure we receive a valid string for the color
	if(isset($_POST['color']) && is_string($_POST['color']) && strlen($_POST['color']) > 0)
	{
		$providedColor = $_POST['color'];

		$count = $votes->fetchVoteCountByColor($providedColor);
		
		if(is_null($count)) $count = 0;
		
		$response['count'] = $count;
	}
	elseif($_POST['color'] == "total")
	{
		$count = $votes->fetchTotalVoteCount();
		
		if(is_null($count)) $count = 0;
		
		$response['count'] = $count;
	}
	else 
	{
		echo null;
	}
	
	//Response is sent back via JSON array
	echo json_encode($response); 
}