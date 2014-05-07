<!-- set the fields that will be multiplied :) -->


<div id="container" class="col-md-8 col-md-offset-2">
	
	<div id="travel_map" style="width:60%; height:500px;float:left">
		
	</div>
	
	<div id="parameters" style="width:40%;float:right">
		
		<h1>Search</h1>
		<hr/>	
		<select name="highway" id="highway" class="form-control">
			<?php
				foreach($highways as $highway)
				{
			?>
					<option value="<?php echo $highway['id'];?>"><?php echo $highway['name'];?></option>	
			<?php		
				} 
			?>											
		</select>
		
		<!-- table of information -->
		<table class="table table-striped"> 
			<!-- highway information -->
			<tr>
				<th>
					Highway:
				</th>
				<td id="highway_name"></td>
			</tr>
			
			<!-- trael time -->
			<tr>
				<th>Travel time:</th>
				<td id="highway_travel_time_forward"></td>
			</tr>
			
			<!-- trael time backward-->
			<tr>
				<th>Travel time backward:</th>
				<td id="highway_travel_time_backward"></td>
			</tr>
		</table>
	</div>
	
</div>


<script>
	$( document ).ready(function() {
  	// Handler for .ready() called.
  		$( "#highway" ).change(function() {  			
	  		$.ajax({	
	  		  type: "post",			  
			  url: window.location.protocol + "//" + window.location.host + window.location.pathname + "/ajaxGetTravelTimeByHighway",
			  data: {highway_id : $("#highway").val()},
			  success: function(data){			  	
			  	//Parse json data
			  	result = JSON.parse(data);
			  	travel_times = result[0];
			  	travel_times_back = result[3];
			  	 
			  	
			  	//fill out hte high way info
			  	highway_name = $( "#highway option:selected" ).text();
			  	$("#highway_name").text(highway_name);
			  	
			  	//get the travel time for the highway in the forward direction
			  	highway_travel_time_forward = 0;
			  				  	
			  	for(i = 0 ; i < travel_times.length ; i++)
			  	{
			  		if(travel_times[i]["travel_time"])
			  		{
			  			highway_travel_time_forward += travel_times[i]["travel_time"]*1;			  			
			  		}
			  	}
			  	$("#highway_travel_time_forward").text(highway_travel_time_forward + " sec");
			  	
			  	
			  	//get the travel time for the highway in the backward direction
			  	highway_travel_time_backward= 0;
			  				  	
			  	for(i = 0 ; i < travel_times_back.length ; i++)
			  	{
			  		if(travel_times_back[i]["travel_time"])
			  		{
			  			highway_travel_time_backward += travel_times_back[i]["travel_time"]*1;			  			
			  		}
			  	}
			  	$("#highway_travel_time_backward").text(highway_travel_time_backward + " sec");
			  	
			  	
			  	},
			  error: function (xhr, ajaxOptions, thrownError) {
				        alert(xhr.status);
				        alert(thrownError);
				      }
			}); // end of ajax   	  	
		}); // end of on change
	}); // end of document ready
</script>