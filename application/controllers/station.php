<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	/**
	 * Filename: station.php
	 * Description: 
	 * station contoller that control the station functions (add,receivcemessage,...)
	 * 
	 *  
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
class Station extends CI_Controller {
	// The message received from the station
	var $message = "";
	
	/**
	 * Function name : __construct
	 * Description: 
	 * this contructor is called as this object is initiated.
	 * 
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	 public function __construct()
    {
        parent::__construct();
        
    }
    
    /**
	 * Function name : receiveMessage
	 * Description: 
	 * this function receives the sent message and stores it in the message 
	 * local variable. then calls parse function.
	 * 
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function receiveMessage($msg)
	{
		$this->message = $msg.PHP_EOL;
		parse_message();
	}
	
	/**
	 * Function name : parseMessage
	 * 
	 * Description: 
	 * this function parses the received message and calls the control functions
	 * depending on the parsing result.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function parseMessage(){
		//parse message
		//check if the station exists in the datbase
		if(!checkStation($station_ID)){
			//if not add it
			newStation($station_ID,$long,$lat,$isTwoWay);
		}
	}
	
	/**
	 * Function name : checkStation
	 * 
	 * Description: 
	 * This function checks if the station exists in the database.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function checkStation($station_ID){
		//loading station model
		$this->load->model('station_model');
		
		$this->station_model->station_ID = $station_ID;
		//get the station specified by the station_ID
		$station = $this->station_model->getStationByStationID();
		if(isset($station[0]))
		//if the station was found return its object
		return $station[0];
		// else return false
		return false;
	}
	
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
	 * Function name : newStation
	 * 
	 * Description: 
	 * This function adds new station to the database.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function newStation($station_ID,$long,$lat,$isTwoWay){
		//loading station model
		$this->load->model('station_model');
		//id of the highway
		$highway_id = 0;
	
		/* getting the station's highway id */
		//get the highway of the station
		$highway_name = findoutHighway($long,$lat);
		//check if this highway is in the database
		$highway = checkHighway($highway_name);
		if(!$highway){
			//if it doesn't exist add it
			//load the model
			$this->load->model("highway_model");
			//fill the model fields 
			$this->highway_model->name = $highway_name;
			$this->highway_model->isTwoWay = $isTwoWay;
			
			//execute the addition function and get its id
			$highway_id = $this->highway_model->addHighway();
		}else{
			$highway_id = $highway->id; 
		}
		//filling the model fields
		$this->station_model->station_ID = $station_ID;
		$this->station_model->longitude = $long;
		$this->station_model->latitude = $lat;
		$this->station_model->status = 0;
		$this->station_model->highway_id = $highway_id;
		
		//execute station adding function
		$station_id = $this->station_model->addStation();
		
		/* finding and adding the new station's neighbors */
		findStationNeighbors($station_id,$highway,$isTwoWay);
			
		/* recalculate highways beginning and ending stations */
		determineHighwayTerminals($highway_id,$highway);
	}
	
	
	/**
	 * Function name : findStationNeighbors
	 * 
	 * Description: 
	 * This function finds and adds the new station's neighbors.
	 * 
	 * Algorithm of finding station neighbors:
	 *	1- if this is the first station in the highway we do nothing.
	 *	2- calculate the distances from the new station(call it N) to all the stations in the same highway.
	 *	3- Find the nearest station that N leads to (call it A).   
	 *	4- calculate the distances from stations in the same highway to N.
	 *	5- Find the nearest station that leads to N (call it A1).
	 *	6-if (the highyway way is bi directional)  
	 *		then  
	 *			if this is the second station in the highway
	 *				then 
	 *					1- add a new neighborhood relationship from N to A.
	 *					2- add a new neighborhood relationship from A to N.
	 *				else	
	 *					1- for each neighbor can be reached from A  (we call it B) (B is neighbor to A)
	 *						 if the distance from N to B is less than the distance from A to B 
	 *							then  1- we delete the neighborhood relationship from A to B.
	 *								  2- add a new neighborhood relationship from N to B.
	 *							 else
	 *								  we do nothing.
	 *	
	 *					2- for each station (call it C) leads to A directly (A is neighbor to C)
	 *						 if the distance from C to N is less than the distance from C to A
	 *							then 1- we delete the neighborhood relationship from C to A.
	 *								 2- add a new neighborhood relationship from  C to N.
	 *							else
	 *								 we do nothing.
	 *			 
	 *					3- add a new neighborhood relationship from N to A.
	 *					4- add a new neighborhood relationship from A to N.
	 *						 
	 *		else (A != A1)(the highyway is one direction)
	 *			if this is the second station in the highway
	 *				then
	 *					if the distance from N to A is less than the distance from A to N
	 *						then 
	 *							add A as neighbor to N (a flow from N to A).
	 *						else
	 *							add N as neighbor to A (a flow from A to N).
	 *				else				
	 *					if the distance from N to A is less than the distance from N to A1
	 *						then 
	 *							1-add A as neighbor to N (a flow from N to A).
	 *							2-get the station that leads to A (call it D).
	 *							3-delete the neighborhood relation from D to A.
	 *							4-add N as neighbor to D (a flow from D to N)
	 *						else
	 *							1-get neighbor of A1 (call it D).
	 *							2-delete the neighborhood relation from A1 to D.
	 *							3-add N as neighbor to A1 (a flow from A1 to N).
	 *							4-add D as neighbor to N (a flow from N to D).
	 *					
	 *					 
	 *	important remark(B is neighbor to A <=> there is a flow from A to B)
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	 public function findStationNeighbors($station_id,$highway_new,$isTwoWay){
		 //if the new station is not the first one
		if(!$highway_new){
		//load the neighbor model
		$this->load->model("neighbor_model");	
		//prepare the neighbors array which is an array of the indeces of the neighbors in the stations array
		$neighbors = array()
		//get all of the highway's stations
		$this->load->model('station_model');
		$this->station_model->highway_id = $highway_id;
		$stations = $this->station_model->getStationsbyHighway();
		//preparing origin and destination distances
		$origin = "";
		$destinations = "";
		//The order of the station in the stations' array
		$station_order = 0;
		//setting the origin and destinations of google's distance matrix request
		$order = 0;
		foreach($stations as $station){
			if($station->id == $station_id){
				$origin[] = $station->lat.",".$station->long;
				$station_order = $order;
			}else{
				if($destinations == "")
					$destinations = $station->lat.",".$station->long;
				else
					$destinations .= "|".$station->lat.",".$station->long;
				$order++;
			}
			
		}
		//if there is more than one station in the highway
		if($destinations !== ""){
			//prepare distances arrays
			$distances = array();//forward distances (from the new station to the other stations)
			$distances_back = array();//backward distances (from the other stations to the new station)
			//loading the curl helper
			$this->load->helper("curl_helper"); 
			//forming the url to be sent
			$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$destinations}&sensor=false&mode=driving&key=Fmjtd%7Cluur2q0z20%2C75%3Do5-9ab25r";
			// send the request and get the response body
			$body = send_request($url);
			//decode the json encoded body
			$decoded = json_decode($body);
			//extracting distances from the decoded object and put them in the distances array
			foreach($decoded->rows->elements as element){
				$distances[] = element["distance"]->value;
			}
			//finding the nearest station's index in forward direction
			$nearest = array_keys($distances, min($distances));
			//take the first index
			$nearest = $nearest[0];
			
			//getting the backward(from all the other stations to the new station) distances
			$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$destinations}&destinations={$origin}&sensor=false&mode=driving&key=Fmjtd%7Cluur2q0z20%2C75%3Do5-9ab25r";
			// send the request and get the response body
			$body = send_request($url);
			//decode the json encoded body
			$decoded = json_decode($body);
			//extracting distances from the decoded object and put them in the distances array
			foreach($decoded->rows as row){
				$distances_back[] = ->elements->distance->value;
			}
			//finding the nearest station's index in backward direction
			$nearest_back = array_keys($distances_back, min($distances_back));
			$nearest_back = $nearest_back[0];
			//if the highway is bidirectional
			if($isTwoWay){
				//if this is the second station to be added to the highway
				if(count($stations) == 2){
					//add each of the stations as a neighbor to the other one
					$this->neighbor_model->station_id = $stations[0]['id'];//the old station
					$this->neighbor_model->neighbor_id = $stations[1]['id'];//the new station
					$this->neighbor_model->distatnce = $distances_back[0];//the backward distance
					
					$this->neighbor_model->addNeighbor();
					$this->neighbor_model->station_id = $stations[0]['id'];//the new station
					$this->neighbor_model->neighbor_id = $stations[1]['id'];//the old station
					$this->neighbor_model->distatnce = $distances[0];//the forward distance
					
					$this->neighbor_model->addNeighbor();
				}else{
					
				}
				
			}
			
			/* get the new neighbor's neighbors */
			//loading neighbor model
			$this->load->model("neighbor_model");
			//filling the model's fields
			$this->neighbor_model->station_id = $stations[$neighbors[0]]['id'];
			$n_neighbors = $this->neighbor_model->getNeighborsByStationId();
			
			foreach($n_neighbors as $n_neighbor){
				//get the distance between this neighbor and the original station(the newly deployed)
				$distance = PHP_INT_MAX;
				//the index of the neighbor in the stations array
				$n_index = -1;
				for($i=0;$i<count($distances);$i++){
					if($stations[$i]['id'] == $n_neighbor['neighbor_id']){
						$distance = $distances[$i];
						$n_index = $i;
						break;
					}
				}
				//if the distance between the original station and its neighbor's neighbor is less than 
				//the distance between the neighbor and the neighbor's neighbor then the neighbor's 
				//neighbor is a neighbor to the original station
				if($distance < $n_neighbor['distance']){
					// add the neighbor to the station's neighbors
					$neighbors[] = $n_index;
					//delete the neighborhood relationship between the neighbor 
					//and its neighbor and add the new station as a neighbor to 
					//the current neighbor instead or we can just modify the current neighbor row in the database
					$this->neighbor_model->id = $n_neighbor['id'];
					$this->neighbor_model->station_id = $n_neighbor['station_id'];
					$this->neighbor_model->neighbor_id = $station_id;
					$this->neighbor_model->distance = $distances[$neighbors[0]];
					
					break;//this break is put on the basis of that there are maximum two neighbors to a single station
					
				}
			}
			
			//adding the new neighbors to the current station in the database
			foreach($neighbors as $neighbor){
				//add the neighborto the station
				$this->neighbor_model->station_id = $stations[$order]['id'];
				$this->neighbor_model->neighbor_id = $stations[$neighbor]['id'];
				$this->neighbor_model->distance = $distances[$neighbor];
				$this->neighbor_model->addNeighbor();
			}
			
			//getting the stations that are neigbors to the nearest neighbor to the new station
			$this->neighbor_model->neighbor_id = $stations[$nearest]['id'];
			$neighbors_n = $this->neighbor_model->getNeighborsByNeighborId();
			
			//looping on the neighbors_n to learn the station that the new station is neighbor to
			foreach($neighbors_n as $neighbor_n){
				//skip the new station if found
				if($neighbor_n['id'] == $station_id){
					continue;
				}
				//get the distance betweenthe original station(the newly deployed) and this neighbor 
				$distance = PHP_INT_MAX;
				//the index of the neighbor in the stations array
				$index_n = -1;
				for($i=0;$i<count($distances);$i++){
					if($stations[$i]['id'] == $neighbor_n['station_id']){
						$distance = $distances[$i];
						$index_n = $i;
						break;
					}
				}
				
				//if the distance between the original station(new) and this neighbor is less than 
				//the distance between this neighbor and its neighbor then this neighbor has the
				//station as neighbor instead of its neighbor
				if($distance < $n_neighbor['distance']){
					//delete the neighborhood relationship between the neighbor 
					//and its neighbor and add the new station as a neighbor to 
					//the current neighbor instead .Or we can just modify the current neighbor row in the database
					$this->neighbor_model->id = $neighbor_n['id'];
					$this->neighbor_model->station_id = $neighbor_n['station_id'];
					$this->neighbor_model->neighbor_id = $station_id;
					$this->neighbor_model->distance = $distances[$index_n];
					
					break;//this break is put on the basis of that there are maximum two neighbors to a single station
					
				}
			}
		}	
		}
	 }
	
	
	
	/**
	 * Function name : findoutHighway
	 * 
	 * Description: 
	 * This function finds out the high way in the given GPS long and lat.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function findoutHighway($long,$lat){
		//load curl_helper
		$this->load->helper("curl");
		//forming the revrse geocoding service url to be used to get the highway name
		$url = "http://www.mapquestapi.com/geocoding/v1/reverse?key=Fmjtd%7Cluur2q0z2l%2Cr2%3Do5-9ab250&callback=renderReverse&location={$lat},{$long}";        
		//get the body of the request result
		$body = send_request($url);
		//decode it from json format
		$code = json_decode($body);
		//get the highway name froimthe decoded body
		$highway = $code->results[0]->locations[0]->street;
		return $highway;
	}

	/**
	 * Function name : pass
	 * 
	 * Description: 
	 * This function adds new passing  to the database.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat*
	 * contact: molham225@gmail.com
	 */
	public function pass($station_id,$mac,$passing_time)
	{
		//loading  passing model
		$this->load->model("passing_model");
		
		//adding new travel if not existed or get the mac record id 
		$traveller_id =$this->addTraveller($mac);
		
		//get the last passing for this station 
		$this->passing_model->station_id=$station_id;
		$pass_from=$this->passing_model->getLastStationPassing();
		
		//prepare fields to add a new pass
		$this->passing_model->passing_time=$passing_time
		$this->passing_model->traveller_id=$traveller_id;
		$this->passing_model->station_id=$station_id;
		$pass_to=$this->passing_model->addPassing();
		
		//determine if we should add new travel
		if(count($pass_from)>0)
		{
			//new travel should be added
			$this->addTravel($pass_from[0]['id'],$pass_to[0]['id'],$pass_from[0]['passing_time'],$pass_to[0]['passing_time']);
		}
		
	}
	
	
	/**
	 * Function name : addTraveller
	 * 
	 * Description: 
	 * This function adds new Traveller  to the database.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat*
	 * contact: molham225@gmail.com
	 */
	 
	public function addTraveller($mac)
	{
		//loading traveller model
		$this->load->model("traveller_model");
		
		//findout if the mac is allready existed
		$this->traveller_model->mac_address=$mac;
		$traveller_id=$this->passing_model->getTravellerID();
		if(count($traveller_id)>0)
		{
			//the mac is allready existed and returning the traveller record id
			return $traveller_id[0]['id'];
		}
		else
		{
			//adding new traveller
			$this->traveller_model->mac_address=$mac;
			$traveller_id=$this->traveller_model->addTravel();
			//returning the new traveller record id
			return $traveller_id[0]['id'];
		}
		
	}
	
	
	/**
	 * Function name : addTravel
	 * 
	 * Description: 
	 * This function adds new Travel  to the database.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat*
	 * contact: molham225@gmail.com
	 */
	 
	public function addTravel($pass_from_id,$pass_to,$date1,$date2);
	{
		//loading  travel model
		$this->load->model("travel_model");
		
		//adding new travel
		$this->travel_model->passing_from=$pass_from_id;
		$this->travel_model->passing_to=$pass_to;
		//caculating the travel duration in sec
		$this->travel_model->travel_time=abs(strtotime($date2) - strtotime($date1));	
		$this->travel_model->is_valid=true;	
	}
	/* End of file welcome.php */
	/* Location: ./application/controllers/welcome.php */
}

