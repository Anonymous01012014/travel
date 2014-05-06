<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	/**
	 * Filename: main.php
	 * Description: 
	 * This class is the main class for handling message driven commands.
	 * It receives the message coming from the websocket server and parses it.
	 * then it executes controlling processes depending on the parsing result of these messages.
	 * 
	 *  
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
class Main extends CI_Controller {
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
		//if(/*New station*/){
			
			newStation($station_ID,$long,$lat);
		//}else{
			if(checkStation($station_ID)){
				//if(/* this is a new passing*/)
					//$this->newPass($station_id,$mac,$passing_time);
			}
		//}
	}
	
	/* Station Section*/
	
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
		if(isset($station[0])){
			//if the station was found put its object in the station field return true
			$this->station = $station[0];
			return true;
		}
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
		$highway_name = $this->findoutHighway($long,$lat);
		//check if this highway is in the database
		$highway = $this->checkHighway($highway_name);
		if(!$highway){
			//if it doesn't exist add it
			//load the model
			$this->load->model("highway_model");
			//fill the model fields 
			$this->highway_model->name = $highway_name;
			
			//execute the addition function and get its id
			$highway_id = $this->highway_model->addHighway();
		}else{
			$highway_id = $highway['id']; 
		}
		//filling the model fields
		$station_id = $this->station['id'];
		$this->station_model->id = $station_id;
		$this->station_model->station_ID = $station_ID;
		$this->station_model->longitude = $long;
		$this->station_model->latitude = $lat;
		$this->station_model->status = 0;
		$this->station_model->highway_id = $highway_id;
		
		//execute station adding function
		$this->station_model->startStation();
		
		//get all of the highway's stations
		$this->load->model('station_model');
		$this->station_model->highway_id = $highway_id;
		$highway_stations = $this->station_model->getStationsbyHighway();
		
		/* finding and adding the new station's neighbors */
		$this->findStationNeighbors($station_id,$highway,$highway_id,$highway_stations);
			
		/* recalculate highways beginning and ending stations */
		$this->determineHighwayTerminals($highway,$highway_id,$highway_stations);
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
	 *	6- if this is the second station in the highway
	 *				then 
	 *					1- add a new neighborhood relationship from N to A.
	 *					2- add a new neighborhood relationship from A to N.
	 *				else	
	 *					1- for each neighbor can be reached from A  (we call it B) (B is neighbor to A)
	 *						 if the distance from N to B is less than the distance from A to B 
	 *							then  1- we delete the neighborhood relationship from A to B and from B to A.
	 *								  2- add a new neighborhood relationship from N to B and from B to N.
	 *							 else
	 *								  we do nothing.
	 *			 
	 *					2- add a new neighborhood relationship from N to A.
	 *					3- add a new neighborhood relationship from A to N.
	 *		
	 *	remark: Highways are always two way so no need for one way processing.
	 *					 
	 *	important remark(B is neighbor to A <=> there is a flow from A to B)
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	 public function findStationNeighbors($station_id,$highway,$highway_id,$stations){
		
		 //if the new station is not the first one
		if($highway){
			//echo 1;
			//load the neighbor model
			$this->load->model("neighbor_model");	
			//prepare the neighbors array which is an array of the indeces of the neighbors in the stations array
			$neighbors = array();
			//preparing origin and destination distances
			$origin = "";
			$destinations = "";
			//The order of the station in the stations' array
			$station_order = 0;
			//setting the origin and destinations of google's distance matrix request
			$order = 0;
			foreach($stations as $station){
				if($station['id'] == $station_id){
					$origin = $station['latitude'].",".$station['longitude'];
					$station_order = $order;
					//echo $order."/n";
				}else{
					if($destinations == "")
						$destinations = $station['latitude'].",".$station['longitude'];
					else
						$destinations .= "|".$station['latitude'].",".$station['longitude'];
					$order++;
					//echo $order."/n";
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
				var_dump($origin);
				$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$destinations}&sensor=false&mode=driving&key=AIzaSyCqJs3iw4UIvhFB2VXV3k4Nc79VlyMn_LA";
				//echo $url;
				// send the request and get the response body
				$body = send_request($url);
				//decode the json encoded body
				//echo $body;
				$decoded = json_decode($body);
				//extracting distances from the decoded object and put them in the distances array
				foreach($decoded->rows[0]->elements as $element){
					$distances[] = $element->distance->value;
				}
				//finding the nearest station's index in forward direction
				$nearest = array_keys($distances, min($distances));
				//take the first index
				$nearest = $nearest[0];
				
				//getting the backward(from all the other stations to the new station) distances
				$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$destinations}&destinations={$origin}&sensor=false&mode=driving&key=AIzaSyCqJs3iw4UIvhFB2VXV3k4Nc79VlyMn_LA";
				//echo $url;
				// send the request and get the response body
				$body = send_request($url);
				//echo $body;
				//decode the json encoded body
				$decoded = json_decode($body);
				//extracting distances from the decoded object and put them in the distances array
				foreach($decoded->rows as $row){
					$distances_back[] = $row->elements[0]->distance->value;
				}
				//finding the nearest station's index in backward direction
				$nearest_back = array_keys($distances_back, min($distances_back));
				$nearest_back = $nearest_back[0];
				//if this is the second station to be added to the highway
				if(count($stations) == 2){
					//add each of the stations as a neighbor to the other one
					$this->neighbor_model->station_id = $stations[0]['id'];//the old station
					$this->neighbor_model->neighbor_id = $stations[1]['id'];//the new station
					$this->neighbor_model->distance = $this->getDistance($distances,$distances_back,0);
					
					$this->neighbor_model->addNeighbor();
					
					$this->neighbor_model->station_id = $stations[1]['id'];//the new station
					$this->neighbor_model->neighbor_id = $stations[0]['id'];//the old station
					$this->neighbor_model->distance = $this->getDistance($distances,$distances_back,0);
					
					$this->neighbor_model->addNeighbor();
				}else if(count($stations) > 2){
					//We consider that the nearest station is a neighbor
					$neighbors[] = $nearest; 
					//get the neighbors of the nearest station
					$this->neighbor_model->station_id = $stations[$nearest]['id'];
					
					$n_neighbors = $this->neighbor_model->getNeighborsByStationId();
					//get the other neighbor of the new station
					foreach($n_neighbors as $n_neighbor){
						//get the distance between this neighbor and the original station(the newly deployed)
						$distance = PHP_INT_MAX;
						//the index of the neighbor in the stations array
						$n_index = -1;
						for($i=0;$i<count($distances);$i++){
							if($stations[$i]['id'] == $n_neighbor['neighbor_id']){
								$distance = $this->getDistance($distances,$distances_back,$i);
								$n_index = $i;
								break;
							}
						}
						//if the nearst's neighbor's distance from the new station is less than its distance 
						//from the nearest delete this station from the nearest's neighbors and add it as a 
						//neighbor to the new station
						if($distance < $n_neighbor['distance']){
							// add the neighbor to the station's neighbors
							$neighbors[] = $n_index;
							//delete the neighborhood relationships between the nearest and its neighbor
							$this->neighbor_model->id = $n_neighbor['id'];
							$this->neighbor_model->deleteNeighbor();
							
							$this->neighbor_model->station_id = $n_neighbor['neighbor_id'];
							$this->neighbor_model->neighbor_id = $n_neighbor['station_id'];
							$this->neighbor_model->deleteNeighborByStationAndNeighbor();
							
							break;//this break is put on the basis of that there are maximum two neighbors to a single station
							
						}
					}
					
					//adding the neighbors relationships for the new station
					foreach($neighbors as $neighbor){
						//add each of the stations as a neighbor to the other one
						$this->neighbor_model->station_id = $stations[$neighbor]['id'];//the old station
						$this->neighbor_model->neighbor_id = $station_id;//the new station
						$this->neighbor_model->distance = $this->getDistance($distances,$distances_back,$neighbor);
													
						$this->neighbor_model->addNeighbor();
						
						$this->neighbor_model->station_id = $station_id;//the new station
						$this->neighbor_model->neighbor_id = $stations[$neighbor]['id'];//the old station
						$this->neighbor_model->distance = $this->getDistance($distances,$distances_back,$neighbor);
													
						$this->neighbor_model->addNeighbor();
					}
				}
			}
		}	
	 }

	/**
	 * Function name : getDistance
	 * 
	 * Description: 
	 * This function returns the shorter distance from the given index in thwe two given distance array.
	 * 
	 * created date: 03-05-2014 
	 * created by: Eng. Ahmad Mulhem Barakat*
	 * contact: molham225@gmail.com
	 */
	
	public function getDistance($foreward_distances,$backward_distances,$index){
		if($foreward_distances[$index] >= $backward_distances[$index]){
			return $backward_distances[$index];
		}else{
			return $foreward_distances[$index];
		}
	}
	
	/* End of Station Section*/
	
	
	
	/* Highway Section*/
	
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
			 //load the station model
			$this->load->model('station_model');
			$this->load->model('highway_model');
			//if the road is a two way
			$this->highway_model->id = $highway_id;			
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
		$url = "http://api.geonames.org/findNearbyStreetsOSMJSON?formatted=true&lat={$lat}&lng={$long}&username=ecobuild&style=full";
		//get the body of the request result
		$body = send_request($url);
		//decode it from json format
		
		
		//$body = substr($body, 14, -1); 
		$code = json_decode($body);
		//get the highway name froimthe decoded body
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
		
		return $highway_fragments[count($highway_fragments) - 1];
	}
	
	/* End of Highway Section*/
	
	
	
	/* Travel Section*/
	
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
	public function newPass($station_id,$mac,$passing_time)
	{
		$passing_time = urldecode($passing_time);
		//loading  passing model
		$this->load->model("passing_model");
		//getting the station from the database
		$this->load->model("station_model");
		
		$this->station_model->station_ID = $station_id;
		
		$station = $this->station_model->getStationByStationID();
		//if the station exists in the database
		if(isset($station[0])){
			//check if this traveller exists in the database
			$traveller = $this->checkTraveller($mac);
			if(!$traveller){
				echo 1;
				//adding new traveller if not existed or get the mac record id 
				$traveller_id =$this->addTraveller($mac);
				
				//prepare fields to add a new pass
				$this->passing_model->passing_time=$passing_time;
				$this->passing_model->traveller_id=$traveller_id;
				$this->passing_model->station_id=$station[0]['id'];
				$pass_to=$this->passing_model->addPassing();
			}else{
				echo 2;
				$traveller_id = $traveller['id'];
				//get the last passing for this traveller 
				$this->passing_model->traveller_id=$traveller_id;
				$pass_from=$this->passing_model->getLastTravellerPassing();
				
				//prepare fields to add a new pass
				$this->passing_model->passing_time=$passing_time;
				$this->passing_model->traveller_id=$traveller_id;
				$this->passing_model->station_id=$station[0]['id'];
				$pass_to=$this->passing_model->addPassing();
				var_dump($pass_from);
				//determine if we should add new travel
				if(count($pass_from)>0)
				{
					//new travel should be added
					$this->addTravel($pass_from[0]['id'],$pass_to,$pass_from[0]['passing_time'],$passing_time);
				}
			}
		}
	}
	
	
	/**
	 * Function name : checkTraveller
	 * 
	 * Description: 
	 * This function checks if the traveller exists in the database.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat*
	 * contact: molham225@gmail.com
	 */
	 
	public function checkTraveller($mac){
		//loading traveller model
		$this->load->model("traveller_model");
		
		//findout if the mac is allready existed
		$this->traveller_model->mac_address=$mac;
		$traveller = $this->traveller_model->getTravellerByMac();
		if(isset($traveller[0])){
			return $traveller[0];
		}
		return false;
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
		//adding new traveller
		$traveller_id=$this->traveller_model->addTraveller();
		//returning the new traveller record id
		return $traveller_id;
	}
	
	
	/**
	 * Function name : addTravel
	 * 
	 * Description: 
	 * This function adds new Travel  to the database.
	 * 
	 * 
	 * 
	 * created date: 25-04-2014 
	 * created by: Eng. Ahmad Mulhem Barakat*
	 * contact: molham225@gmail.com
	 */
	public function addTravel($pass_from,$pass_to,$date_from,$date_to)
	{
		//loading  travel model
		$this->load->model("travel_model");
		
		//adding new travel
		$this->travel_model->passing_from=$pass_from;
		$this->travel_model->passing_to=$pass_to;
		//caculating the travel duration in sec
		$this->travel_model->travel_time=strtotime($date_to) - strtotime($date_from);	
		$this->travel_model->is_valid=true;	
		$this->travel_model->addTravel();
	}
	
	/* End of Travel Section*/
	
	
	/* End of file message.php */
	/* Location: ./application/controllers/message.php */
}
