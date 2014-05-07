/**
 * @author Mohanad Kaleia
 * 
 * File name: site_map.js
 * Description: 
 * This file contain functions about google map to show sites on map 
 * 
 * created date : 19-2-2014  
 */

function initialize()
{
	var mapProp = {
	  center:new google.maps.LatLng(lat,long),
	  zoom:16,
	  mapTypeId:google.maps.MapTypeId.ROADMAP
	  };
	var map=new google.maps.Map(document.getElementById("googleMap")
	  ,mapProp);
	  
	  var myCenter=new google.maps.LatLng(lat , long);
	
	var marker=new google.maps.Marker({
	  position:myCenter,
	  });
	
	marker.setMap(map);
	var infowindow = new google.maps.InfoWindow({
	  content: site_name
	  });
	
	
	google.maps.event.addListener(marker,'click',function() {
	  map.setZoom(16);
	  map.setCenter(marker.getPosition());
	  });
	
	  
	  google.maps.event.addListener(map,'center_changed',function() {
	  window.setTimeout(function() {
	    map.panTo(marker.getPosition());
	  },3000);
	});
	
	google.maps.event.addListener(marker, 'click', function() {
	  infowindow.open(map,marker);
	  });
}
