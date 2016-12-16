<?php

use app\Init;
use models\Color;

include "../app/Init.php";

$init = new Init();

$color = new Color();

$colors = $color->fetchColors();


?>
<!DOCTYPE html>
<head>
	<title>Achieve3000 Assessment</title>
	<script type="text/javascript" src="scripts/jquery-1.11.2.min.js"></script>
	<script type="text/javascript" src="scripts/bootstrap.min.js"></script>
	<script type="text/javascript" src="scripts/votes.js"></script>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-theme.min.css">
</head>
<body>
	<table class="table table-striped table-condensed">
		<tr>
			<th>Color</th>
			<th>Votes</th>
		</tr>
		<?php foreach($colors as $c) :?>
		<tr>
			<td class="name" id="<?php echo $c?>"><a href="" onclick="return false"><?php echo $c?></a></td>
			<td class="count" id="<?php echo $c?>-count"></td>
		</tr>
		<?php endforeach;?>
		<tr>
			<td class="total"><a href="" onclick="return false">Total</a></td>
			<td id="total-count"></td>
		</tr>
	<table>
<script>
	$(document).ready(function(){

		$(".name").click(function() {
			
			Votes.getVotesByColor(this.id);
		});
		
		$(".total").click(function() {
			
			Votes.getTotalShownVotes();
		});

		
		
	});
</script>
</body>
</html>
