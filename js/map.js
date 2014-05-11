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
			//compute the center of lat and long of the highway
			if(stations.length > 0)
			{				
				
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
					
					google.maps.event.addListener(marker[station_id.toString()],'click',function(i) {
								  
						//show site info in the right panel
						return function(){							
							infowindow.setContent("<div style='min-width:100px;min-height:30px;'>Station ID: "+ stations[i]['station_id'] +"</div>");
							  	
							var station_id = stations[i]['id'];
							
							infowindow.open(map,marker[station_id.toString()]);								
						}	  
					}(i));
								
				}	
			
				
				//set the center of the map .. get the average of first and last station lat and long 
				center_lat  = 	(stations[0]['latitude'] + stations[stations.length - 1]['latitude']) / 2;
				center_long = 	(stations[0]['longitude']+ stations[stations.length - 1]['longitude']) / 2;			
				setMapCenter(map , center_lat , center_long);
			
				//draw line between two stations
				drawRoute(map , stations);
				
			
		     } //end stations is not empty if statement 		
		     
		     
		    
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





/**
 * Function name : setMapCenter
 * Description: 
 * set the center of the map depending on the 
 * parameres:
 * map: map object of google map
 * latitude: latitude
 * longitude: longitude 
 * created date: 11-5-2014
 * ccreated by: Mohanad kaleia
 * contact: ms.kaleia@gmail.com 
 */
function setMapCenter(map , latitude , longitude)
{
	//set the center of the map 
	map.setCenter(new google.maps.LatLng(latitude, longitude));												
}




/**
 * Function name : drawRoute
 * Description: 
 * set the center of the map depending on the 
 * parameres:
 * map: map object of google map
 * latitude: latitude
 * longitude: longitude 
 * created date: 11-5-2014
 * ccreated by: Mohanad kaleia
 * contact: ms.kaleia@gmail.com 
 */
function drawRoute(map , stations)
{
	var path = new google.maps.MVCArray();
    var service = new google.maps.DirectionsService();
	var poly = new google.maps.Polyline({ map: map, strokeColor: '#00FF00' });
	
	var lat_lng = new Array();
	
	myLatlng = new google.maps.LatLng(stations[0]['latitude'], stations[0]['longitude']);			
	lat_lng.push(myLatlng) ;
	
	myLatlng = new google.maps.LatLng(stations[stations.length - 1]['latitude'], stations[stations.length - 1]['longitude']);			
	lat_lng.push(myLatlng) ;
	
	var src = lat_lng[0];
    var des = lat_lng[1];
    path.push(src);
    poly.setPath(path);
	service.route({
           origin: src,
           destination: des,
           travelMode: google.maps.DirectionsTravelMode.DRIVING
       }, function (result, status) {
           if (status == google.maps.DirectionsStatus.OK) {
               for (var i = 0, len = result.routes[0].overview_path.length; i < len; i++) 
               {
                   path.push(result.routes[0].overview_path[i]);
               }
           }
       });	
       
       google.maps.event.addListener(poly, 'click', function (event) {
		        //alert the index of the polygon
		        alert("hello");
		    }); 	
}
