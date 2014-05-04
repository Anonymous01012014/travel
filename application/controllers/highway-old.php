<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	/**
	 * Function name : checkHighway
	 * 
	 * Description: 
	 * This function checks if the highway exists in the database.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function checkHighway($highway){
		//loading station model
		$this->load->model('highway_model');
		
		$this->highway_model->name = $highway;
		//get the highway specified by the station_ID
		$highway = $this->highway_model->getHighwayByName();
		if(isset($highway[0]))
		//if the highway was found return its object
		return $highway[0];
		// else return false
		return false;
	}
	
	
	
	
	
	
	/**
	 * Function name : determineHighwayTerminals
	 * 
	 * Description: 
	 * determine the highway terminals.
	 * 
	 * created date: 29-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function determineHighwayTerminals($highway,$highway_id,$stations){
		
		//load the highway model
		$this->load->model("highway_model");
		//if the highway is new then it has only one station 
		if(!$highway){
			//echo " highway NOT exists";
			//add the station as the first and last stations of the highway
			//fill model fields
			$this->highway_model->start_station = $stations[0]['id'];
			$this->highway_model->end_station = $stations[0]['id'];
			$this->highway_model->id = $highway_id;
			
			$this->highway_model->setHighwayTerminals();
		}else{
			//echo " highway  exists";
			 //load the station model
			$this->load->model('station_model');
			$this->load->model('highway_model');
			//if the road is a two way
			$this->highway_model->id = $highway_id;
			//$isTwoWay = $this->highway_model->isTwoWay();
			//$isTwoWay = ($isTwoWay['two_way'] == 1)? true : false;
			//if($isTwoWay){	
				//echo 2;			
				$this->station_model->highway_id = $highway_id;
				//get the neighbor count for all the stations in the highway
				$neighbor_counts = $this->station_model->getTwoWayHighwayStationsNeighborCount();
				//set the highway id in the highway model
				$this->highway_model->id = $highway_id;
				//set the highway terminals in the model
				//the terminal is a station that has only one neighbor
				if(count($neighbor_counts) == 2){//We assume that the highway has only two terminals
						$this->highway_model->start_station = $neighbor_counts[0]['id'];
						$this->highway_model->end_station = $neighbor_counts[1]['id'];
						//set the highway terminals
						$this->highway_model->setHighwayTerminals();
				}
				
			/*}else{//if the road is one way
				echo 3;
				$this->load->model('station_model');
				//set the highway id in the model
				$this->station_model->highway_id = $highway_id;
				//set the highway id in the highway model
				$this->highway_model->id = $highway_id;
				//the start station is the station that is not a neighbor to any other station on the highway
				//get the start station
				$start_station = $this->station_model->getOneWayHighwayFirstStation();
				//if the start station was found
				if(isset($start_station[0])){
					$this->highway_model->start_station = $start_station[0]['id'];
				}
				//getting the last station in the highway
				$neighbor_counts = $this->station_model->getOneWayHighwaylastStation();
				if(isset($neighbor_counts[0])){//if the last stationwas found
					$this->highway_model->end_station = $neighbor_counts[0]['id'];
				}
				//setting the highway terminals
				$this->highway_model->setHighwayTerminals();
			}*/
		}
	}
	
	/**
	 * Function name : findoutHighway
	 * 
	 * Description: 
	 * This function finds out the high way in the given GPS long and lat.
	 * 
	 * created date: 25-04-2014 
	 * created by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function findoutHighway($long,$lat){
		//load curl_helper
		$this->load->helper("curl");
		//forming the revrse geocoding service url to be used to get the highway name
		//$url = "http://www.mapquestapi.com/geocoding/v1/reverse?key=Fmjtd%7Cluur2q0z2l%2Cr2%3Do5-9ab250&callback=renderReverse&location={$lat},{$long}";        
		$url = "http://api.geonames.org/findNearbyStreetsOSMJSON?formatted=true&lat={$lat}&lng={$long}&username=ecobuild&style=full";
		//get the body of the request result
		$body = send_request($url);
		//decode it from json format
		
		
		//$body = substr($body, 14, -1); 
		$code = json_decode($body);
		//var_dump ($a);
		//get the highway name froimthe decoded body
		//$highway = $code->results[0]->locations[0]->street;
		if(isset($code->streetSegment->ref)){
			$highway = $code->streetSegment->ref;
		}else if(isset($code->streetSegment[0]->ref)){
			$highway = $code->streetSegment[0]->ref;
		}else if(isset($code->streetSegment->name)){
			$highway = $code->streetSegment->name;
		}else if(isset($code->streetSegment[0]->name)){
			$highway = $code->streetSegment[0]->name;
		}
		$highway_fragments = explode(';',$highway);
		/*if(is_numeric($highway_fragments[0]) AND count($highway_fragments) > 1){
			$highway_fragments = array_slice($highway_fragments,1);
			var_dump($highway_fragments);
			$highway = $highway_fragments[0];
			echo $highway;
			$cont = true;//if this is not the3 first index in the array continue
			foreach($highway_fragments as $highway_fragment){
				if($cont){
					$cont = false;
					continue;
				}
				$highway .= " ".$highway_fragment;
			}
		}
		* */
		
		return $highway_fragments[count($highway_fragments) - 1];
	}
