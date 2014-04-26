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
			newStation($station_ID,$long,$lat);
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
	public function newStation($station_ID,$long,$lat){
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
		
		/* finding the new station's neighbors */
		//The distance limit that if exceeded the station isn't considered a neioghbor
		//It's measured in meters
		$neighbor_limit = 1000;
		//prepare the neighbors array
		$neighbors = array()
		//get all of the highway's stations
		$this->load->model('station_model');
		$this->station_model->highway_id = $highway_id;
		$stations = $this->station_model->getStationsbyHighway();
		//preparing origin and destination distances
		$origin = "";
		$destinations = "";
		$destinations = array();
		//The order of the station in the stations' array
		$station_order = 0;
		//setting the origin and destinations of google's distance matrix request
		$order = 0;
		foreach($stations as $station){
			if($station->id == $station_id){
				$origin[] = $station->lat.",".$station->long);
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
			//loading the curl helper
			$this->load->helper("curl_helper"); 
			//forming the url to be sent
			$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$destinations}&sensor=false&mode=driving&key=Fmjtd%7Cluur2q0z20%2C75%3Do5-9ab25r"
			// send the request and get the response body
			$body = send_request($url);
			//decode the json encoded body
			$decoded = json_decode($body);
			//extracting distances from the decoded object and put them in the distances array
			foreach($decoded->rows->elements as element){
				$distances[] = element["distance"]->value;
			}
			
			for($i=0;$i<count($distances)){
				//to skip the current station's index
				if($i >= $station_order){
					$index = $i+1;
				}else{
					$index = $i;
				}
				//if the distance isn't greater than neighbor limit add the station id to neighbors array
				if($distance[$i] <= $neighbor_limit){
					//loading neighbor model
					$this->load->model("neighbor_model");
					//adding the neighbor to current station
					$this->neighbor_model->station_id = $station_id;
					$this->neighbor_model->neighbor_id = $stations[$index];
					$this->neighbor_model->distance = $distance[$i];
					
					$this->neighbor_model->addNeighbor();
					
					//adding the current station as a neighbor to its neighbor
					$this->neighbor_model->station_id = $stations[$index];
					$this->neighbor_model->neighbor_id = $station_id;
					$this->neighbor_model->distance = $distance[$i];
					
					$this->neighbor_model->addNeighbor();
				}
			}
			
			/* recalculate highways beginning and ending stations */
			
			 
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

