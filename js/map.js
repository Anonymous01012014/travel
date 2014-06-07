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
//distances to be compared for drawing routes between stations on the map
var distance_foreward = 0 ,
	distance_backward = 0 ;


/*
 * function name: showOklahomeMap
 * Parameters:
 * latitude
 * longitude
 * 
 */
function showMap(latitude, longitude,zoom)
{
	var mapProp = {
	  center:new google.maps.LatLng(latitude,longitude),
	  zoom:zoom,
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
						//"http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|4EC23D",
						window.location.protocol+"//"+window.location.host + "/travel/images/google_marker/number_"+i+".png",
						null,null,null,new google.maps.Size(30, 30)
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
							infowindow.setContent("<div style='min-width:100px;min-height:30px;'>Longitude: "+ stations[i]['longitude'] + "<br/>Latitude: "+ stations[i]['latitude'] +"</div>");
							  	
							var station_id = stations[i]['id'];
							
							infowindow.open(map,marker[station_id.toString()]);								
						}	  
					}(i));
							
				}	
			
				
				//set the center of the map .. get the average of first and last station lat and long 
				center_lat  = 	(stations[0]['latitude'] + stations[stations.length - 1]['latitude']) / 2;
				center_long = 	(stations[0]['longitude']+ stations[stations.length - 1]['longitude']) / 2;			
				setMapCenter(map , center_lat , center_long);			
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
	  url: window.location.protocol + "//" + window.location.host +  "/travel/dashboard/ajaxGetTravelTimeByHighway",
	  data: {highway_id : highway_id},
	  success: function(data){	
	  	
	  	// on success show highway information on the right sidebar
	  	// show travel time - travel time backward - show travel time for stations
	  	
	  			  	
	  	//Parse json data
	  	result = JSON.parse(data);
	  	
	  	travel_times = result[0];
	  	travel_times_back = result[3];
	  	stations = result[2];
	  	 
	  	
	  	//fill out hte high way info
	  	
	  	
	  	//highway name
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
	  	
	  	
	  	//show station duration header information
	  	//delete the prev information
	  	$( "#stations_info tr td" ).remove();	
	  	
	  	//show station travel time in forward direction
	  	for(i= 0 ; i < travel_times.length ; i++)
	  	{	  			
	  			if(travel_times[i])
	  			{
	  				info_line = "<tr><td>";
	  			
		  			//show from station name 
		  			info_line += getStationIndex(stations ,  travel_times[i]['from_station']);
		  			info_line += "</td><td>";
		  			
		  			//show to station name
		  			info_line += getStationIndex(stations ,  travel_times[i]['to_station']); 
		  			info_line += "</td><td>";
		  			
		  			//show travel time
		  			info_line += travel_times[i]['travel_time'] + " sec";
		  			info_line += "</td></tr>";
		  			
		  			//add this line to the table
		  			$( "#stations_info" ).append( info_line );	
	  			}	  			
	  	}
	  	
	  	//show station travel time in BACKWORD direction
	  	for(i= 0 ; i < travel_times_back.length ; i++)
	  	{	  			
	  			if(travel_times_back[i])
	  			{
	  				info_line = "<tr><td>";
	  			
		  			//show from station name 
		  			info_line +=  getStationIndex(stations ,  travel_times_back[i]['from_station']);  
		  			info_line += "</td><td>";
		  			
		  			//show to station name
		  			info_line += getStationIndex(stations ,  travel_times_back[i]['to_station']); 
		  			info_line += "</td><td>";
		  			
		  			//show travel time
		  			info_line += travel_times_back[i]['travel_time'] + " sec";
		  			info_line += "</td></tr>";
		  			
		  			//add this line to the table
		  			//$( "#stations_info" ).append( info_line );	
	  			}
	  			
	  			
	  	}
	  	
	  	
	  	
	  		
	  	}
	  	
	  	
	  	,
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
 * created by: Mohanad kaleia
 * contact: ms.kaleia@gmail.com 
 */
function setMapCenter(map , latitude , longitude)
{
	//set the center of the map 
	map.setCenter(new google.maps.LatLng(latitude, longitude));
	map.setZoom(12);												
}


/**
 * Function name : drawRoute
 * Description: 
 * set the center of the map depending on the 
 * parameres:
 * map: map object of google map
 * latitude: latitude
 * longitude: longitude 
 * 
 * created date: 11-5-2014
 * created by: Mohanad kaleia
 * contact: ms.kaleia@gmail.com 
 * 
 * 
 * 
 * modifications:
 * isolate creating polyline in a separate function.
 * make infowindow appear at click location on the polyline.
 * add travel info to each route's infowindow.
 * 
 * Modification date: 2-6-2014
 * modified by: Eng. Ahmad Mulhem Barakat
 * contact: molham225@gamil.com 
 */
function drawRoute(map , stations)
{
    var polylines = new Array();
    for(var i=0; i< stations.length -1;i++ ){
		var lat_lng = new Array();

		var src = new google.maps.LatLng(stations[i]['latitude'], stations[i]['longitude']);			
		lat_lng.push(src);

		var des = new google.maps.LatLng(stations[i + 1]['latitude'], stations[i + 1]['longitude']);			
		lat_lng.push(des);
		//alert(lat_lng[0]+"-"+lat_lng[1]);
		var poly = drawPolyline(map,lat_lng,i);

		polylines.push(poly);
	}
	
	
}



/**
 * Function name : drawPolyline
 * Description: 
 * draws a poly line on the given map object using the lat_lng array that have the start and end point coordinates.
 * parameres:
 * map: map object of google map
 * lat_lng: array of latLng objects that represent the start and end points of the poly line.
 * index: the index of this polyline in the polylines array.
 * 
 * created date: 11-5-2014
 * created by: Mohanad kaleia
 * contact: ms.kaleia@gmail.com 
 * 
 * modifications:
 * this function now only measures the distance between the stations in opposite directions
 * to get the shortest distance then calls the getPolyline function.
 * 
 * Modification date: 7-6-2014
 * modified by: Eng. Ahmad Mulhem Barakat
 * contact: molham225@gamil.com 
 **/
function drawPolyline(map,lat_lng,index){		
	var distanceService = new google.maps.DistanceMatrixService();
	alert(lat_lng);
	var rev_lat_lng = lat_lng.slice();
	rev_lat_lng.reverse();
	alert(lat_lng);
	distanceService.getDistanceMatrix({
		   origins: lat_lng,
		   destinations: rev_lat_lng,
		   travelMode: google.maps.DirectionsTravelMode.DRIVING
	   }, function (result, status) {
			if (status == google.maps.DirectionsStatus.OK) {
					alert(1);
					distance_backward = result.rows[1].elements[1].distance.value;//result.routes[0].legs[0].distance.value;
					distance_foreward = result.rows[0].elements[0].distance.value;
					alert(distance_foreward+"::"+distance_backward);
					alert(lat_lng);
					return getPolyline(map,lat_lng,index);
			}			
	   });
}

/**
 * Function name : getPolyline
 * Description: 
 * This function compares the distances calculated in the previous functions and 
 * creates the polyline using the shorter distance direction.then calls the add listeners function to add info window to the poly line.
 * lastly it returns the created poly line
 * 
 * parameres:
 * map: map object of google map
 * lat_lng: array of latLng objects that represent the start and end points of the poly line.
 * index: the index of this polyline in the polylines array.
 * 
 * created date: 7-6-2014
 * created by: Ahmad Mulhem Barakat & Mohanad kaleia
 * contact: molham25@gmail.com , ms.kaleia@gmail.com 
 * 
 **/
function getPolyline(map,lat_lng,index){
	//directions service object for drawing the route between stations
	var directionService = new google.maps.DirectionsService();
	alert(2);
	var path = new google.maps.MVCArray();
	var poly = new google.maps.Polyline({ map: map ,strokeColor: '#333',strokeWeight: 5,strokeOpacity: 0.5});
	if(distance_foreward < distance_backward)
   {
		path.push(lat_lng[0]);

		directionService.route({
			   origin: lat_lng[0],
			   destination: lat_lng[1],
			   travelMode: google.maps.DirectionsTravelMode.DRIVING
		   }, function (result, status) {
			   if (status == google.maps.DirectionsStatus.OK) {
				   alert("chosen :: "+result.routes[0].legs[0].distance.value + " meters");
				   for (var j = 0, len = result.routes[0].overview_path.length; j < len; j++) 
				   {
					   path.push(result.routes[0].overview_path[j]);
				   }
				   poly.setPath(path);
				   addListeners(poly,map,index);
			   }
		   });	
   }else{
	   path.push(lat_lng[1]);

		directionService.route({
		   origin: lat_lng[1],
		   destination: lat_lng[0],
		   travelMode: google.maps.DirectionsTravelMode.DRIVING
	   }, function (result, status) {
		   if (status == google.maps.DirectionsStatus.OK) {
			   alert("chosen :: "+result.routes[0].legs[0].distance.value + " meters");
			   for (var j = 0, len = result.routes[0].overview_path.length; j < len; j++) 
			   {
				   path.push(result.routes[0].overview_path[j]);
			   }
			   poly.setPath(path);
			   addListeners(poly,map,index);
			   
			}
		});
	
	}
	return poly;
}

/**
 * Function name : addListeners
 * Description: 
 * This function adds the click listeners to the poly line and map to show the infowindow.
 * 
 * parameres:
 * poly: the polyline created in the previous function.
 * map: map object of google map.
 * i: the index of this polyline in the polylines array.
 * 
 * created date: 7-6-2014
 * created by: Ahmad Mulhem Barakat & Mohanad kaleia
 * contact: molham25@gmail.com , ms.kaleia@gmail.com 
 * 
 **/

function addListeners(poly,map,i){
	var infowindow = new google.maps.InfoWindow({
		maxWidth: 320
	});
	
	var closeWindow = false;
	
	google.maps.event.addListener(poly,'click',function(i) {
		
		//show site info in the right panel
		return function(){
			infowindow.setContent("<div style='min-width:175px;min-height:50px;'>distance: "+travel_times[i]['distance']+" m<br/>"+
																			"travel time from ("+i+") to ("+(i+1)+"): "+travel_times[i]['travel_time']+" secs<br />"+
																			"travel time from ("+(i+1)+") to ("+i+"): "+travel_times_back[i]['travel_time']+" secs<br /></div>");
		//infowindow.open(map,this);								
		};
	}(i));
	google.maps.event.addListener(poly,'click',function(event) {
		closeWindow = false;
		var markerPosition=new google.maps.LatLng(event.latLng.lat(),  event.latLng.lng());
		var pinColor = 'FFFF00';
		var pinIcon = new google.maps.MarkerImage(
		window.location.protocol+"//"+window.location.host + "/travel/images/google_marker/number_"+i+".png",
		null,null,null,new google.maps.Size(1, 1)
		);
									
		var marker = new google.maps.Marker({
		  position:markerPosition,
		  icon: pinIcon
		});
		//infowindow.setPosition(new google.maps.LatLng(event.latLng.lat() , event.latLng.lng()));						
		infowindow.open(map,marker);
	});
		
	google.maps.event.addListener(map,'click',function(event) {
		if(!closeWindow){
			infowindow.close();
		}
		closeWindow = true;
	});	
	   
}


/**
 * Function name : getStationIndex
 * Description: 
 * get station index by station_id
 * parameres:
 * station: an array of stations
 * station_id: the station id that we want to get its 
 * longitude: longitude 
 * created date: 11-5-2014
 * ccreated by: Mohanad kaleia
 * contact: ms.kaleia@gmail.com 
 */
function getStationIndex(stations , station_id)
{
	for(station_index=0;station_index<stations.length;station_index++)
	{		
	   if(stations[station_index]["station_id"] == station_id)
	   {		   	
	      //alert("found station Id :" + station_id + " is for index " + station_index);   		   	  
	      //found
	      return station_index;	      
	   }	     	 
	}
	
	
	//if no station was found then return 0
	return 0;											
}

