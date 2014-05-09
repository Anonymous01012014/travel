/**
 * @author Mohanad Kaleia
 * 
 * File name: map.js
 * Description: 
 * This file contain a functions to dray map, stations, highway and others
 * created date : 7-5-2014 
 */





//marker array
var marker = new Array();


/*
 * function name: showOklahomeMap
 * Parameters:
 * latitude
 * longitude
 * 
 */
function showMap(latitude, longitude)
{
	var mapProp = {
	  center:new google.maps.LatLng(latitude,longitude),
	  zoom:12,
	  mapTypeId:google.maps.MapTypeId.ROADMAP
	  };
	  
	
	var map=new google.maps.Map(document.getElementById("googleMap"),mapProp);
	  	 
	return map;	
}




/**
 * Function name : getSiteInfo
 * Description: 
 * get site information by id and set it to the site info table
 * 
 * created date: 14-2-2014
 * ccreated by: Eng. Mohanad Shab Kaleia
 * contact: ms.kaleia@gmail.com 
 */

function getSiteInfo(url , site_id)
{	
	$.get(url + "/" + site_id , function(data){		
		var site = jQuery.parseJSON(data);		
		
		//get site with index 0
		site = site[0];			
			
		//set the site data in the site information pane
		
		//site name		
		$("#wim_name").html(site.Site_Name);
		
		//last response
		$("#last_response").html(site.DateTime);
		
		//last check
		$("#last_check").html(site.DateTime);
		
		//Result
		$("#result").html(site.SchedulerStatus);
		
		//signal
		$("#signal").html(site.signal);
		
		//Latitude
		$("#latitude").html(site.Site_Latitude);
		
		//longitude
		$("#longitude").html(site.Site_Longitude);
		
		
	});	
}





/**
 * Function name : addStationToMap
 * Description: 
 * add station to the map
 * parameres:
 * map: map object of google map
 * stations: staions array 
 * created date: 7-5-2014
 * ccreated by: Mohanad kaleia
 * contact: ms.kaleia@gmail.com 
 */
function addStationToMap(map , stations)
{		
			alert("hi");									
			//print a marker for each site
			for(i = 0 ; i < stations.length ; i++)
			{
				var markerPosition=new google.maps.LatLng(stations[i]['latitude'] , stations[i]['longitude']);
				var pinColor = 'FFFF00';
				var pinIcon = new google.maps.MarkerImage(
					"http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|4EC23D",
					null,null,null,new google.maps.Size(15, 30)
								);
					
					var infowindow = new google.maps.InfoWindow({
						maxWidth: 320
							  });
				 marker[stations[i]['id']]=new google.maps.Marker({
				  position:markerPosition,
				  icon: pinIcon,
				  siteId: stations[i]['id']
				  });
	
				station_id = stations[i]['id'];
				marker[station_id.toString()].setMap(map);				
			}					
}



/**
 * Function name : showHighwayInfo
 * Description: 
 * show highway info in the highway panel {highway name - travel time - backward travel time}
 * parameres:
 * 
 * created date: 7-5-2014
 * ccreated by: Eng. Mohanad Kaleia
 * contact: ms.kaleia@gmail.com
 */
function showHighwayInfo(highway_id)
{
	var result;
	
	$.ajax({	
	  type: "post",
	  async: false,
      cache: false,			  
	  url: window.location.protocol + "//" + window.location.host + window.location.pathname + "/ajaxGetTravelTimeByHighway",
	  data: {highway_id : highway_id},
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
	
	return result;  	  	
}
