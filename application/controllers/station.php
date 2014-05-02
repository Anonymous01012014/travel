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
		//if this is n new passing
		$this->newPass($station_id,$mac,$passing_time);
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
		/*isTwoWay from text to boolean
		if($isTwoWay == 'false'){
			$isTwoWay = false;
		}else{
			$isTwoWay = true;
		}*/
		//loading station model
		$this->load->model('station_model');
		//id of the highway
		$highway_id = 0;
		//var_dump($isTwoWay);
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
			//$this->highway_model->two_way = ($isTwoWay)? 1:0;
			
			//execute the addition function and get its id
			$highway_id = $this->highway_model->addHighway();
		}else{
			$highway_id = $highway['id']; 
		}
		//filling the model fields
		$this->station_model->station_ID = $station_ID;
		$this->station_model->longitude = $long;
		$this->station_model->latitude = $lat;
		$this->station_model->status = 0;
		$this->station_model->highway_id = $highway_id;
		
		//execute station adding function
		$station_id = $this->station_model->addStation();
		
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
	 *	6-if (the highyway way is bi directional)  
	 *		then  
	 *			if this is the second station in the highway
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
	 *		else (A != A1)(the highyway is one direction)
	 *			if this is the second station in the highway
	 *				then
	 *					if the distance from N to A is less than the distance from A1 to N
	 *						then 
	 *							add A as neighbor to N (a flow from N to A).
	 *						else
	 *							add N as neighbor to A1 (a flow from A1 to N).
	 *				else				
	 *					if the distance from N to A is less than the distance from A1 to N
	 *						then 
	 *							1-get the station that leads to A (call it D).
	 * 							2-delete the neighborhood relation from D to A.
	 *							3-add A as neighbor to N (a flow from N to A).
	 *							4-add N as neighbor to D (a flow from D to N)
	 *						else
	 *							1-get neighbor of A1 (call it D).
	 * 							2-delete the neighborhood relation from A1 to D.
	 *							3-add D as neighbor to N (a flow from N to D).
	 *							4-add N as neighbor to A1 (a flow from A1 to N).
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
		 echo 1000;
		if($highway){
			echo 1;
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
					echo $order."/n";
				}else{
					if($destinations == "")
						$destinations = $station['latitude'].",".$station['longitude'];
					else
						$destinations .= "|".$station['latitude'].",".$station['longitude'];
					$order++;
					echo $order."/n";
				}
				
			}
			//if there is more than one station in the highway
			if($destinations !== ""){
				echo 2;
				//prepare distances arrays
				$distances = array();//forward distances (from the new station to the other stations)
				$distances_back = array();//backward distances (from the other stations to the new station)
				//loading the curl helper
				$this->load->helper("curl_helper"); 
				//forming the url to be sent
				var_dump($origin);
				$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$origin}&destinations={$destinations}&sensor=false&mode=driving&key=AIzaSyCqJs3iw4UIvhFB2VXV3k4Nc79VlyMn_LA";
				echo $url;
				// send the request and get the response body
				$body = send_request($url);
				//decode the json encoded body
				echo $body;
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
				echo $url;
				// send the request and get the response body
				$body = send_request($url);
				echo $body;
				//decode the json encoded body
				$decoded = json_decode($body);
				//extracting distances from the decoded object and put them in the distances array
				foreach($decoded->rows as $row){
					$distances_back[] = $row->elements[0]->distance->value;
				}
				//finding the nearest station's index in backward direction
				$nearest_back = array_keys($distances_back, min($distances_back));
				$nearest_back = $nearest_back[0];
				
				var_dump($stations);
				var_dump($distances);
				var_dump($nearest);
				var_dump($distances_back);
				var_dump($nearest_back);
				//if the highway is bidirectional
				//if($isTwoWay){
					echo 3;
					//if this is the second station to be added to the highway
					if(count($stations) == 2){
						echo 4;
						//add each of the stations as a neighbor to the other one
						$this->neighbor_model->station_id = $stations[0]['id'];//the old station
						$this->neighbor_model->neighbor_id = $stations[1]['id'];//the new station
						$this->neighbor_model->distatnce = $distances_back[0];//the backward distance
						echo 'add against';
						$this->neighbor_model->addNeighbor();
						$this->neighbor_model->station_id = $stations[1]['id'];//the new station
						$this->neighbor_model->neighbor_id = $stations[0]['id'];//the old station
						$this->neighbor_model->distatnce = $distances[0];//the forward distance
						
						$this->neighbor_model->addNeighbor();
					}else if(count($stations) > 2){
						echo 5;
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
									$distance = $distances[$i];
									$n_index = $i;
									break;
								}
							}
							//if the nearst's neighbor's distance from the new station is less than its distatnce 
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
							$this->neighbor_model->distatnce = $distances_back[$neighbor];//the backward distance
							
							$this->neighbor_model->addNeighbor();
							
							$this->neighbor_model->station_id = $station_id;//the new station
							$this->neighbor_model->neighbor_id = $stations[$neighbor]['id'];//the old station
							$this->neighbor_model->distatnce = $distances[$neighbor];//the forward distance
							
							$this->neighbor_model->addNeighbor();
						}
					}
				/*}else{//if the road is one-way
					//if this is the second station to be added to the highway
					if(count($stations) == 2){
						//if the foreward distance is less than the backward distance
						if($distances[0] < $distances_back[0]){
							//the road direction is forward so add the old station as a neighbor to the new one.
							$this->neighbor_model->station_id = $station_id;//the new statoin id
							$this->neighbor_model->neighbor_id = $stations[0]['id'];//the old station id
							$this->neighbor_model->distance = $distances[0];//the forward distance
							
							$this->neighbor_model->addNeighbor();					
						}else{//if the backward distance is less than the foreward distance
							//the road direction is backward so add the new station as a neighbor to the old one.
							$this->neighbor_model->station_id = $stations[0]['id'];//the old station id
							$this->neighbor_model->neighbor_id = $station_id;//the new statoin id
							$this->neighbor_model->distance = $distances_back[0];//the backward distance
							
							$this->neighbor_model->addNeighbor();	
						}
					}else if(count($station > 2)){
						//if the foreward distance is less than the backward distance then the road flow direction is foreward
						if($distances[0] < $distances_back[0]){
	
							//get the station that has the nearest station as a neighbor to it
							$this->neighbor_model->neighbor_id = $stations[$nearest]['id'];
							
							$n_neighbor = $this->neighbor_model->getNeighborsByNeighborId();
							//if the new neighbor isn't the end of the highway
							if(isset($n_neighbor[0])){
								//modify this neighborhood relationship to become with the new station instead of the nearest station
								$this->neighbor_model->id = $n_neighbor[0]['id'];
								$this->neighbor_model->station_id = $n_neighbor[0]['station_id'];
								$this->neighbor_model->neighbor_id = $station_id;
								$n_index = -1;
								//getting the distance from the new neighbor to the new station
								for($i=0;$i<count($stations);$i++){
									if($stations[$i]['id'] == $n_neighbor[0]['station_id']){
										//get the index of the new neighbor in the stations array
										$n_index = $i;
										break;
									}
								}
								if($n_index !== -1){// if the station was found in the stationss array
									$this->neighbor_model->distance = $distances_back[$n_index];
									$this->neighbor_model->modifyNeighbor();
								}
							}
							//add the nearest station as a neighbor to the new station
							$this->neighbor_model->station_id = $station_id;// the new station
							$this->neighbor_model->neighbor_id = $stations[$nearest]['id'];// the neighbor of the new station
							$this->neighbor_model->distance = $distances[$nearest];// the foreward distance 
							
							$this->neighbor_model->addNeighbor();
						}//the road direction is backward
						else{
							
							//get the station that is a neighbor to the nearest station
							$this->neighbor_model->station_id = $stations[$nearest_back]['id'];
							$n_neighbor = $this->neighbor_model->getNeighborsByStationId();
							// if this station has neighbors
							if(isset($n_neighbor[0])){
								//modify this relationship in the database by replacing the neighbor_id with the new station id
								$this->neighbor_model->id = $n_neighbor[0]['id'];
								$this->neighbor_model->station_id = $station_id;
								$this->neighbor_model->neighbor_id = $n_neighbor[0]['neighbor_id'];
								$n_index = -1;
								//getting the distance from the new station to the nearest's neighbor
								for($i=0;$i<count($stations);$i++){
									if($stations[$i]['id'] ==  $n_neighbor[0]['neighbor_id']){
										$n_index = $i;
										break;
									}
								}
								if($n_index !== -1){
									$this->neighbor_model->distance = $distances[0]['distance'];
									$this->neighbor_model->modifyNeighbor();
								}
								
							}else{//if this  is a terminal
								//just add the new station as a neighbor to the nearest station
								$this->neighbor_model->station_id = $stations[$nearest_back]['id'];
								$this->neighbor_model->neighbor_id = $station_id;
								$this->neighbor_model->distance = $distances_back[$nearest_back];
								
								$this->neighbor_model->addNeighbor();
							}
						}				
					}
				}*/
			}
		}	
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
			echo " highway NOT exists";
			//add the station as the first and last stations of the highway
			//fill model fields
			$this->highway_model->start_station = $stations[0]['id'];
			$this->highway_model->end_station = $stations[0]['id'];
			$this->highway_model->id = $highway_id;
			
			$this->highway_model->setHighwayTerminals();
		}else{
			echo " highway  exists";
			 //load the station model
			$this->load->model('station_model');
			$this->load->model('highway_model');
			//if the road is a two way
			$this->highway_model->id = $highway_id;
			//$isTwoWay = $this->highway_model->isTwoWay();
			//$isTwoWay = ($isTwoWay['two_way'] == 1)? true : false;
			//if($isTwoWay){	
				echo 2;			
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
		
		
		$body = substr($body, 14, -1); 
		$code = json_decode($body);
		//var_dump ($a);
		//get the highway name froimthe decoded body
		$highway = $code->results[0]->locations[0]->street;
		$highway_fragments = explode(' ',$highway);
		if(is_numeric($highway_fragments[0]) AND count($highway_fragments) > 1){
			$highway_fragments = array_slice($highway_fragments,1);
			$highway = $highway_fragments[1];
			$cont = true;//if this is not the3 first index in the array continue
			foreach($highway_fragments as $highway_fragment){
				if($cont){
					continue;
				}
				$highway .= " ".$highway_fragment;
			}
		}
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
	public function newPass($station_id,$mac,$passing_time)
	{
		//loading  passing model
		$this->load->model("passing_model");
		//check if this traveller exists in the database
		$traveller = $this->checkTraveller($mac);
		if(!$traveller){
			//adding new traveller if not existed or get the mac record id 
			$traveller_id =$this->addTraveller($mac);
			
			//prepare fields to add a new pass
			$this->passing_model->passing_time=$passing_time;
			$this->passing_model->traveller_id=$traveller_id;
			$this->passing_model->station_id=$station_id;
			$pass_to=$this->passing_model->addPassing();
		}else{
			$traveller_id = $traveller['id'];
			//get the last passing for this station 
			$this->passing_model->station_id=$station_id;
			$pass_from=$this->passing_model->getLastStationPassing();
			
			//prepare fields to add a new pass
			$this->passing_model->passing_time=$passing_time;
			$this->passing_model->traveller_id=$traveller_id;
			$this->passing_model->station_id=$station_id;
			$pass_to=$this->passing_model->addPassing();
			
			//determine if we should add new travel
			if(count($pass_from)>0)
			{
				//new travel should be added
				$this->addTravel($pass_from[0]['id'],$pass_to,$pass_from[0]['passing_time'],$passing_time);
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
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat*
	 * contact: molham225@gmail.com
	 */
	 
	public function addTravel($pass_from,$pass_to,$date1,$date2)
	{
		//loading  travel model
		$this->load->model("travel_model");
		
		//adding new travel
		$this->travel_model->passing_from=$pass_from;
		$this->travel_model->passing_to=$pass_to;
		//caculating the travel duration in sec
		$this->travel_model->travel_time=strtotime($date2) - strtotime($date1);	
		$this->travel_model->is_valid=true;	
		$this->travel_model->addTravel();
	}
	/* End of file welcome.php */
	/* Location: ./application/controllers/welcome.php */
}

