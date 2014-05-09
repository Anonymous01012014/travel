<script>
	$( document ).ready(function() {		
		// Handler for .ready() called.
		site = "Oklahome";
		latitude = 35.47278;
		longitude = -98.75722;				
		//get Oklahome map		
		map = showMap(latitude , longitude);			
	});		
</script>

<div id="container" class="col-md-8 col-md-offset-2">
	
	<div id="travel_map" style="width:60%; height:500px;float:left">		
		<div id="googleMap" style="width:100%;height:100%;"></div>  		
	</div>
	
	<div id="parameters" style="width:40%;float:right">
		
		<h1>Search</h1>
		<hr/>	
		<select name="highway" id="highway" class="form-control">
			<option value="0">---</option>
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
  			
  			//show highway info  
  			highway_id = $("#highway").val();
  			
  			//if highway id == 0 then nothing will happen
  			if(highway_id != 0 )
  			{
				//show highway information  				
  				stations = showHighwayInfo(highway_id);
  				
  			//	stations = stations[2];				
				//set stations markers			
				//addStationToMap( map  , stations);
  			}			
	  				
		}); // end of on change
	}); // end of document ready
</script>



