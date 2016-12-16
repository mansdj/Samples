/**
 * Modular JS pattern implemented to separate processing logic from presentation logic
 */
var Votes = (function() {
	var votes = {};
	
	//Ajax method for retrieving vote counts
	votes.getVotesByColor = function(color) {
		var data = "color="+color;
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "ajaxhandler.php",
			data: data,
			success: function(response) {
				$("#"+color+"-count").text(response["count"]);
			}
		});
		
	};
	
	//Method for the calculation of displayed votes
	votes.getTotalShownVotes = function() {
		var count = 0;
		
		$(".count").each(function() {
			
			var value = $(this).text();
			
			if(!isNaN(value) && value.length !=0) {
				count += parseInt(value);
			}
		});
			
		$("#total-count").text(count);
	};
	
	return votes;
	
}(jQuery));