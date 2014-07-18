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
	 * Parameters:
	 * $msg: The received message to be parsed.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function receiveMessage($msg)
	{
		$this->message = $msg.PHP_EOL;
		return $this->parseMessage();
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
			$this->load->helper("message_helper");
		try{
			//echo "<br /> ".$this->message;
			//parse message
			$this->message = urldecode($this->message);
			//echo "<br /> ".$this->message;
			//echo $this->message;
			//decode the message from json to php object
			$this->message = json_decode($this->message);
			//define the type of the message 
			if ($this->message->msg_type == 1)//first deployment message(Registeration message)
			{
				if(isset($this->message->dev_long) && isset($this->message->dev_lat) && isset($this->message->highway)){
					$result = $this->newStation($this->message->station_id,$this->message->dev_long,$this->message->dev_lat,$this->message->highway);
					echo $result;
					return;
				}else{//if the message didn't have the expected fields return this error message
					echo MESSAGE_TYPE_CONTENT_MISMATCH;
					return;
				}
			}else{
				//get the highway of the sending station
				//loading the station model
				$this->load->model("station_model");
				//filling the required fields
				
				$this->station_model->station_ID = $this->message->station_id;
				//getting the station specified by the station_ID from the database
				$station = $this->station_model->getStationByStationID();
				//check if the station is registered in the database using the highway id field
				if($station[0]['highway_id'] != "" && $station[0]['highway_id'] != null){//if the highway_id field has a value then the station is registerd in the database
					 if($this->message->msg_type == 2)//single detection message
					{
						if(isset($this->message->dev_lap) && isset($this->message->dev_time)){
							//echo $this->message->station_id . $this->message->dev_lap . $this->message->dev_time;
							$returned_value = $this->newPass($this->message->station_id,$this->message->dev_lap,$this->message->dev_time);
							if($returned_value != "valid"){//if the returned value not equal to "valid" then an error happened
								echo PASS_ADDING_ERROR;
								return;
							}else{
								echo SUCCESS;
								return;
							}
						}else{//if the message didn't have the expected fields return this error message
							echo MESSAGE_TYPE_CONTENT_MISMATCH;
							return;
						}
					}
					else if($this->message->msg_type == 3)//multiple detections message
					{
						if(isset($this->message->dev_multilap)){
							foreach($this->message->dev_multilap as $detection){//add all the detections to the database
								//echo $detection->dev_lap."::".$detection->dev_time;
								$returned_value = $this->newPass($this->message->station_id,$detection->dev_lap,$detection->dev_time);
								if($returned_value != "valid"){//if the returned value not equal to "valid" then an error happened
									echo PASS_ADDING_ERROR;
									return;
								}
							}
							echo SUCCESS;
							return;
						}else{//if the message didn't have the expected fields return this error message
							echo MESSAGE_TYPE_CONTENT_MISMATCH;
							return;
						}
					}else{//if the mesdsage type didn't match any of the previously specified types return this error message
						echo MESSAGE_TYPE_ERROR;
					}
				}else{//if the highway_id is not registered in the database send error message
					echo NOT_REGISTERED;				
				}
			}
		}catch(Exception $e){
			echo MESSAGE_PARSING_ERROR; //"The following error happened wihle parsing the received message: \n ".$e->getMessage();
		}
	}
	
	/* Station Section*/
	
	/**
	 * Function name : connectStation
	 * 
	 * Description: 
	 * This method changes the status of the station to connected in the database.
	 * 
	 * Parameters:
	 * $id: the id of the station that connected.
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function connectStation($id){
		//load the stataion model
		$this->load->model("station_model");
		//fill the fields of the model
		$this->station_model->id = $id;
		//echo "\n".$this->station_model->CONNECTED . "\n";
		$this->station_model->status = $this->station_model->CONNECTED;
		//execute the change status function
		$this->station_model->changeStationStatus();
	}
	
	/**
	 * Function name : disconnectStation
	 * 
	 * Description: 
	 * This method changes the status of the station to disconnected in the database.
	 * 
	 * Parameters:
	 * $id: The id of the station that disconnected.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function disconnectStation($id){
		//load the stataion model
		$this->load->model("station_model");
		//fill the fields of the model
		$this->station_model->id = $id;
		$this->station_model->status = $this->station_model->DISCONNECTED;
		//execute the change status function
		$this->station_model->changeStationStatus();
	}
	
	/**
	 * Function name : checkStation
	 * 
	 * Description: 
	 * This function checks if the station exists in the database.
	 * 
	 * Parameters:
	 * $station_ID: The identification alpha-numeric code that we want to check its availability in the database.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function checkStation($station_ID){
		//getting the station id from the message
		
		//loading station model
		$this->load->model('station_model');
		
		$this->station_model->station_ID = $station_ID;
		//get the station specified by the station_ID
		$station = $this->station_model->getStationByStationID();
		if(isset($station[0])){
			//if the station was found set the station status to connected and return its id
			$this->connectStation($station[0]['id']);
			echo $station[0]['id'];
			return;
		}
		// else return 0
		echo 0;
	}
	
	/**
	 * Function name : newStation
	 * 
	 * Description: 
	 * This function adds new station to the database.
	 * 
	 * Parameters:
	 * $station_ID: The identification alpha-numeric code of the station that will be added to the database.
	 * $lat: the GPS latitude of the new station.
	 * $long: the GPS longitude of the new station.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function newStation($station_ID,$long,$lat,$st_highway){
		
		//loading station model
		$this->load->model('station_model');
		//id of the highway
		$highway_id = 0;
		/* getting the station's highway id */
		try{
			//get the highway of the station
			$highway_name = $this->findoutHighway($long,$lat,$st_highway);
			if($highway_name == $st_highway)
			{
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
				$this->station_model->station_ID = $station_ID;
				$this->station_model->longitude = $long;
				$this->station_model->latitude = $lat;
				$this->station_model->status = $this->station_model->CONNECTED;
				$this->station_model->highway_id = $highway_id;
				
				//execute station adding function
				$this->station_model->startStation();
				
				//getting the station id
				$this->station_model->station_ID = $station_ID;
				
				$station_id = $this->station_model->getStationByStationID();
				$station_id = $station_id[0]['id'];
				
				//get all of the highway's stations
				$this->station_model->highway_id = $highway_id;
				$highway_stations = $this->station_model->getStationsbyHighway();
				
				/* finding and adding the new station's neighbors */
				$this->findStationNeighbors($station_id,$highway,$highway_id,$highway_stations);
					
				/* recalculate highways beginning and ending stations */
				$this->determineHighwayTerminals($highway,$highway_id,$highway_stations);
			}else{
				$msg_subject = "Highway Mismatch..";
				$msg_body = "The highway name (".$st_highway.") provided with the registration message of station (".$station_ID.") 
									doesn't match the highway name (".$highway_name.") received from the reverse geocoding service..";
				$this->sendEmail($msg_subject,$msg_body);
				return HIGHWAY_NOT_FOUND;
			}
		}catch(Exception $e){
			return STATION_REGITRATION_ERROR;//"could not add the new station to the database because of : \n".$e->getMessage();
		}
		return SUCCESS;
	}
	
	
	/**
	 * Function name : findStationNeighbors
	 * 
	 * Description: 
	 * This function finds and adds the new station's neighbors.
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
	 *	Remarks: 
	 * 	. Highways are always two way so no need for one way processing.				 
	 *	. B is neighbor to A <=> there is a flow from A to B.
	 * 
	 * Parameters:
	 * $station_id: the id of the station in the database.
	 * $highway: this variable contains a highway model object if the highway of this station was found in the database ,
	 * 				OR
	 * 			  It contains a false boolean value if the highway of this station wasn't in the database (it was newly added).
	 * 			  It's used as a flag to know if the highway was already in the database or newly added. 
	 * $highway_id : The id of the highway in the database.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	 public function findStationNeighbors($station_id,$highway,$highway_id){
		
		$this->load->model("station_model");
		//get all of the highway's stations
			$this->station_model->highway_id = $highway_id;
			$stations = $this->station_model->getStationsbyHighway();
		
		 //if the new station is not the first one in its highway
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
				//echo $station['id'] . " ".$station_id;
				//echo $station['id']."::".$station_id."<br/>";
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
				//var_dump($origin);
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
				//var_dump($distances);
				//finding the nearest station's index in forward direction
				$nearest = array_keys($distances, min($distances));
				//take the first index
				$nearest = $nearest[0];
				//echo $nearest;
				
				//getting the backward(from all the other stations to the new station) distances
				$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$destinations}&destinations={$origin}&sensor=false&mode=driving&key=AIzaSyCqJs3iw4UIvhFB2VXV3k4Nc79VlyMn_LA";
				//// send the request and get the response body
				$body = send_request($url);
				//echo $body;
				//decode the json encoded body
				$decoded = json_decode($body);
				//extracting distances from the decoded object and put them in the distances array
				foreach($decoded->rows as $row){
					$distances_back[] = $row->elements[0]->distance->value;
				}
				//var_dump($distances_back);
				//finding the nearest station's index in backward direction
				$nearest_back = array_keys($distances_back, min($distances_back));
				$nearest_back = $nearest_back[0];
				//echo $nearest_back;
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
					//find out the nearest neighbor
					if($distances[$nearest] <= $distances_back[$nearest_back]){
						//if the distance from the nearest in the foreward direction is lesser than the distance from the nearest neighbor in the back direction
						//we consider the nearest station as a neighbor
						$neighbors[] = $nearest; 
						//get the neighbors of the nearest station
						$this->neighbor_model->station_id = $stations[$nearest]['id'];
					}else{//if the back neighbor is nearer than the foreward neighbor
						//we consider the nearest station as a neighbor
						$neighbors[] = $nearest_back; 
						//get the neighbors of the nearest station
						$this->neighbor_model->station_id = $stations[$nearest_back]['id'];
					}
					
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
						//echo "neighbors: <br /> neighbor[".$neighbor."] = ".$stations[$neighbor]['id'];
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
	 * Parameters:
	 * $foreward_distances: an array of the distances between the new station and other stations on 
	 * 						the highway in the foreward direction (from the new station to the other stations).
	 * $backwaord_distances: an array of the distances between the new station and other stations on 
	 * 						 the highway in the backword direction (from the other stations to the new station).
	 * $index: the index of the station we want to get the distance for in the previous arrays. 
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
	 * Parameters:
	 * $highway_name: The name of the highway that was returned from the web service. 
	 * 
	 * Return:
	 * $highway object if it was found in the database .
	 * OR
	 * boolean false value.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function checkHighway($highway_name){
		//loading station model
		$this->load->model('highway_model');
		
		$this->highway_model->name = $highway_name;
		//get the highway specified by the its name
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
	 * Parameters:
	 * $highway: this variable contains a highway model object if the highway of this station was found in the database ,
	 * 				OR
	 * 			  It contains a false boolean value if the highway of this station wasn't in the database (it was newly added).
	 * 			  It's used as a flag to know if the highway was already in the database or newly added. 
	 * $highway_id : The id of the highway in the database.
	 * $stations: An array of the sations on the specified highway.
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
	 * Parameters:
	 * $long: The GPS longitude of the station.
	 * $lat: The GPS latitude of the station.
	 * $st_highway: the highway name we got from the station.
	 * 
	 * created date: 25-04-2014 
	 * created by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	public function findoutHighway($long,$lat,$st_highway){
		//a flag variable is set to true if the st_highway name was found in the returned json from the service.
		$highway_found =false;
		$highway_name = "";
		
		$st_highway_uppercase = strtoupper($st_highway);
		
		//load curl_helper
		$this->load->helper("curl");
		//forming the revrse geocoding service url to be used to get the highway name
		$url = "http://api.geonames.org/findNearbyStreetsOSMJSON?formatted=true&lat={$lat}&lng={$long}&username=ecobuild&style=full";
		//get the body of the request result
		$body = send_request($url);
		//decode it from json format
		
		
		//$body = substr($body, 14, -1); 
		$code = json_decode($body);
		
		//searching for the station highway name in the decoded body
		if(isset($code->streetSegment->ref)){
			$highway_refs = $this->getHighwayRefs($code->streetSegment->ref);
			foreach($highway_refs as $highway_ref){
				$highway_ref_uppercase = strtoupper($highway_ref);
				if($highway_ref_uppercase == $st_highway_uppercase){
					$highway_found = true;
					break;
				}else{
					$highway_name = $highway_ref[0];
				}
			}
		}
		if(isset($code->streetSegment->name) && !$highway_found){
			$highway = $code->streetSegment->name;
			$highway_uppercase = strtoupper($highway);
			if($highway_uppercase == $st_highway_uppercase){
				$highway_found = true;
			}
			if($highway_name == "")
				$highway_name = $highway;
				
		}
		if(isset($code->streetSegment[0]) && !$highway_found){
			foreach($code->streetSegment as $segment){
				if(isset($segment->ref) && !$highway_found){
					$highway_refs = $this->getHighwayRefs($segment->ref);
					foreach($highway_refs as $highway_ref){
						$highway_ref_uppercase = strtoupper($highway_ref);
						if($highway_ref_uppercase == $st_highway_uppercase){
							$highway_found = true;
							break;
						}else{
							if($highway == "")
								$highway_name = $highway_ref[0];
						}
					}
				}
				if(isset($segment->name) && !$highway_found){
					$highway = $segment->name;
					$highway_uppercase = strtoupper($highway);
					if($highway_uppercase == $st_highway_uppercase){
						$highway_found = true;
					}
					if($highway_name == "")
						$highway_name = $highway;
				}
				if($highway_found){
					break;
				}
			}
		}
		/*get the highway name from the decoded body
		if(isset($code->streetSegment->ref)){
			$highway = $code->streetSegment->ref;
		}else if(isset($code->streetSegment[0]->ref)){
			$highway = $code->streetSegment[0]->ref;
		}else if(isset($code->streetSegment->name)){
			$highway = $code->streetSegment->name;
		}else if(isset($code->streetSegment[0]->name)){
			$highway = $code->streetSegment[0]->name;
		}
		//if the highway of the given lat,long was found return its name
		else{//else return false
			return false;
		}*/
		//if the st_highway was found in the returned JSON it'll be returned
		if($highway_found){
			return $st_highway;
		}else{//else we return the highway name returned from the service.
			return $highway_name;
		}
	}
	
	
	public function getHighwayRefs($highway){
		if(isset($highway)){
			$highway_fragments = explode(';',$highway);
			
			return $highway_fragments;
		}
	}
	
	/* End of Highway Section*/
	
	
	
	/* Travel Section*/
	
	/**
	 * Function name : pass
	 * 
	 * Description: 
	 * This function adds new passing  to the database.
	 * 
	 * Parameters:
	 * $station_id: The station's unique alpha-numeric id.
	 * $mac: mac adderss of the detected BT device.
	 * $passing_time: A time stamp at which the BT device was detected.
	 * 
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat*
	 * contact: molham225@gmail.com
	 */
	public function newPass($station_id,$mac,$passing_time)
	{
		try{
			//$passing_time = urldecode($passing_time);
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
					/* *
					 * if the traveller not found in the database
					 * just add the new traveller and the new passing at the specified station
					 * */
					//echo 1;
					//adding the new traveller to database
					$traveller_id =$this->addTraveller($mac);
					
					//prepare fields to add a new pass
					$this->passing_model->passing_time=$passing_time;
					$this->passing_model->traveller_id=$traveller_id;
					$this->passing_model->station_id=$station[0]['id'];
					$pass_to=$this->passing_model->addPassing();
				}else{
					//if the traveller already exists in the database
					//echo 2;
					$traveller_id = $traveller['id'];
					//get the last passing for this traveller 
					$this->passing_model->traveller_id=$traveller_id;
					$pass_from=$this->passing_model->getLastTravellerPassing();
					
					//determine if we should add new travel or not
					if(count($pass_from)>0)
					{
						$pass_from_time = strtotime($pass_from[0]['passing_time']);//getting the pass from time
						$pass_to_time = strtotime($passing_time);//getting the pass to time
						//echo "<br/>".$pass_from_time."||".$pass_to_time."<br />";
						//if the pass to happened after the pass from then this is a valid pass timing
						if($pass_from_time < $pass_to_time){
							/* *
							 * If the new passing was the latest pass for this traveller in the database 
							 * just add the pass and add atravel from the previous pass to this pass
							 * */
							//echo 3 . "<br />";
							//prepare fields to add a new pass
							$this->passing_model->passing_time=$passing_time;
							$this->passing_model->traveller_id=$traveller_id;
							$this->passing_model->station_id=$station[0]['id'];
							$pass_to=$this->passing_model->addPassing();
							//if the two passings are not at the same station then add a travel
							if($pass_from[0]['station_id'] != $station[0]['id']){
								//new travel should be added
								$this->addTravel($pass_from[0]['id'],$pass_to,$pass_from[0]['passing_time'],$passing_time);
							}
						}else{//if the new pass happened before the latest added pass
							/* *
							 * if the new pass happened before the latest added pass we do the following:
							 * 1- we check if the new pass is added before.
							 * 2- if not we find the passings before and after this passing.
							 * 3- we delete the travel between the passings found in the previous step(if it was found).
							 * 4- we add a travel from the previous passing to the new passing and a travel from the
							 * 	  new passing to the next passing.
							 * */
							 //echo 4 . "<br />";
							 //check if this passing is already added
							$this->passing_model->station_id=$station[0]['id'];
							$this->passing_model->passing_time=$passing_time;
							$this->passing_model->traveller_id=$traveller_id;
							$passing_exist = $this->passing_model->checkPassingExist();
							if(!isset($passing_exist[0])){//if it's not already in the database
								//add the new passing to the database
								$this->passing_model->passing_time=$passing_time;
								$this->passing_model->traveller_id=$traveller_id;
								$this->passing_model->station_id=$station[0]['id'];
								
								$pass_to=$this->passing_model->addPassing();
								//echo $pass_to. "<br />";
								//set the model's passing time 
								$this->passing_model->passing_time = $passing_time;
								//set the model's traveller id
								$this->passing_model->traveller_id = $traveller_id;
								//get the pass previous to the new pass
								$previous = $this->passing_model->getPreviousPassing();
								//get the pass after to the new pass
								$next = $this->passing_model->getNextPassing();
								//echo "next: ".$next[0]["id"]. "<br />";
								//echo "previous: ".$previous[0]["id"]. "<br />";
								//if there is ap passing that happened before this passing
								if(isset($previous[0])){
									//if there is ap passing that happened after this passing
									if(isset($next[0])){
										//delete the travel from the previous passing to the next passing if it was found
										//load travel model
										$this->load->model("travel_model");
										//fill model fields
										$this->travel_model->passing_from = $previous[0]["id"]; 
										$this->travel_model->passing_to = $next[0]["id"]; 
										
										//get the travel
										$travel = $this->travel_model->getTravelByPassings();
										//if the travel exists
										if(isset($travel[0])){
											//set the id field in the model
											$this->travel_model->id = $travel[0]["id"];
											//delete the travel
											$this->travel_model->deleteTravel();
										}
										//add a travel from the previous passing to the new passing
										$this->addTravel($previous[0]["id"],$pass_to,$previous[0]["passing_time"],$passing_time);
										//add a travel from the new passing to the next passing
										$this->addTravel($pass_to,$next[0]["id"],$passing_time,$next[0]["passing_time"]);
									}
								}
							}
						}
					}
				}
			}
		}catch(Exception $e){
			echo "couldn't add new pass to the database because of : \n".$e->getMessage()."\n";
			return;
		}
		return "valid";
	}
	
	
	/**
	 * Function name : checkTraveller
	 * 
	 * Description: 
	 * This function checks if the traveller exists in the database.
	 * 
	 * Parameters:
	 * $mac: The mac address of the detected BT device.
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
	 * Parameters:
	 * $mac: The mac address of the detected BT device.
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
	 * Parameters:
	 * $pass_from: the id of the last passing before the current passing of this traveller(BT device) in the database.
	 * $pass_to: the id of the current passing of this traveller(BT device) in the database.
	 * $date_from: the time stamp of the last passing.
	 * $date_to:the time stamp of the current passing.
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
	
	
	/**
	 * Function name : sendEmail
	 * 
	 * Description: 
	 * This function sends an email to itsstulsa@gmail.com that has the provided subject and message.
	 * 
	 * Parameters:
	 * $subject: The subject of the message to be sent.
	 * $message: The body of the email will be sent.
	 * 
	 * created date: 25-04-2014 
	 * created by: Eng. Ahmad Mulhem Barakat*
	 * contact: molham225@gmail.com
	 */
	public function sendEmail($subject,$message){
		
		$config = Array(
			'protocol' => 'smtp',
			'smtp_host' => 'ssl://smtp.googlemail.com',
			'smtp_port' => 465,
			'smtp_user' => 'ecobuild.sy@gmail.com',
			'smtp_pass' => 'ecobuild2120446',
			'mailtype'  => 'html', 
			'charset'   => 'iso-8859-1'
		);
		
		$this->load->library("email", $config);
		$this->email->set_newline("\r\n");
		$this->email->from("ecobuild.sy@gmail.com","Travel Time");
		$this->email->to("itsstulsa@gmail.com");
		$this->email->subject($subject);
		$this->email->message($message);
		
		
		$result = $this->email->send();
		
		//echo $this->email->print_debugger();


	}
	
	/* End of file message.php */
	/* Location: ./application/controllers/message.php */
}

