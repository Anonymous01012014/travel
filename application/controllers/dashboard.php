<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class dashboard extends CI_Controller {

	/**
	 * dashboard controller to show interface with map and options to show travel time parameters
	 */
	 
	 
	 
	/**
	 * Function name : index
	 * Description: 
	 * show the main interface of travel time
	 * 
	 * created date: 5-5-2014
	 * ccreated by: Eng. Mohanad Shab Kaleia
	 * contact: ms.kaleia@gmail.com 
	 */ 
	public function index()
	{
		
		//load highways
		$this->load->model("highway_model");
		$highways = $this->highway_model->getAllHighways();								
		$data["highways"] = $highways;
		
		//set the active menu
		$data["active_menu"] = "dashboard";
		
		//call the general views for page structure	
		$this->load->view('gen/header');
		$this->load->view('gen/main_menu' , $data);
		$this->load->view('gen/logo');
		$this->load->view('gen/main_content');
	
		//load the map view
		$this->load->view("travel_map" , $data);
		
		
		$this->load->view('gen/footer');
	}
	
	
	/**
	 * Function name : ajaxGetTravelTimeByHighway
	 * Description: 
	 * get travel times by highway
	 * 
	 * created date: 5-5-2014
	 * ccreated by: Eng. Ahmad Molham Barakat
	 * contact: molham255@gmail.com
	 */ 
	public function ajaxGetTravelTimeByHighway()
	{
		$highway_id = $this->input->post("highway_id");
		
		//loading required models
		$this->load->model("highway_model");
		$this->load->model("station_model");
		$this->load->model("neighbor_model");
		//load views model
		$this->load->model("views_model");
		//foreward (from start to end )travel times
		$travel_times = array();
		//foreward (from start to end )travel times
		$travel_times_back = array();
		//highway travel time in foreward direction
		$highway_travel_time = 0;
		//highway travel time in back direction
		$highway_travel_time_back = 0;
		//get the highway 
		$this->highway_model->id = $highway_id;
		$highway = $this->highway_model->getHighwayById();
		if(isset($highway[0])){//if the highway exists
			$highway = $highway[0];
			
			//highway ordered stations from start to end
			$highway_stations = array();
			//the start station of the highway
			$start = $highway['start_station'];
			//the end station of the highway
			$end = $highway['end_station'];
			//current station
			$current = $start;
			//next station
			$next = 0;
			//previous station
			$previous = 0;
			//add the start station to the highway stations array
			$this->station_model->id = $start;
			
			$temp_station = $this->station_model->getStationById();
			
			if(isset($temp_station[0])){//if the start station exists in the database
				$highway_stations[] = $temp_station[0];
			
				//start looping from the start station to the end station in the highway
				while($current != $end){
					//get current station's neighbors
					$this->neighbor_model->station_id = $current;
					$neighbors = $this->neighbor_model->getNeighborsByStationId();
					
					//get the neighbor that doesn't have the same id as the previous station
					foreach($neighbors as $neighbor){
						//if the neighbor is the previous neighbor don't take it
						if($previous == $neighbor['neighbor_id']){
							continue;
						}
						$next = $neighbor['neighbor_id'];
						break;//get the first found foreward
					}
					//get the next station and add it to the highway stations 
					$this->station_model->id = $next;
					
					$temp_station = $this->station_model->getStationById();
					
					if(isset($temp_station[0])){
						$highway_stations[] = $temp_station[0];
					}
					//set the previous station to the current station value
					$previous = $current;
					//set the current station to the next station value
					$current = $next;
				}
				
				
				//get highway segments travel times
				$segments_travel_times = $this->views_model->getSegmentTravelTimes($highway_id);
				//getting the foreward travel times of the highway
				for($i=1;$i<count($highway_stations);$i++){
					$travel_time = 0;
					//echo "from ".$highway_stations[$i - 1]['id']." to ".$highway_stations[$i]['id']."<br/>";
					foreach($segments_travel_times as $segment_travel_time){
						//echo "segment: from ".$segment_travel_time['from_station_id']." to ".$segment_travel_time['to_station_id']."<br/>";
						if($segment_travel_time['from_station_id'] == $highway_stations[$i - 1]['id']){
							if($segment_travel_time['to_station_id'] == $highway_stations[$i]['id']){
								//$travel_time = $segments_travel_time;
								$travel_time = $segment_travel_time;
								break;
							}
						}
					}
					
					//if($travel_time > 0){
						$travel_times[] = $travel_time;
					//}
				}
				//getting the backward travel times of the highway
				for($i=count($highway_stations) - 2;$i>= 0 ;$i--){
					$travel_time = 0;
					//echo "from ".$highway_stations[$i - 1]['id']." to ".$highway_stations[$i]['id']."<br/>";
					foreach($segments_travel_times as $segment_travel_time){
						//echo "segment: from ".$segment_travel_time['from_station_id']." to ".$segment_travel_time['to_station_id']."<br/>";
						if($segment_travel_time['from_station_id'] == $highway_stations[$i + 1]['id']){
							if($segment_travel_time['to_station_id'] == $highway_stations[$i]['id']){
								//$travel_time = $segments_travel_time;
								$travel_times_back[] = $segment_travel_time;
								break;
							}
						}
					}
					$travel_times_back[] = $travel_time;
				}
				//var_dump($travel_times);
				//var_dump($segments_travel_times);
				//var_dump($highway_stations);
				//var_dump($travel_times_back);
				
				//gather the results and encode it using json encode
				$result[] = $travel_times;
				$result[] = $segments_travel_times;
				$result[] = $highway_stations;
				$result[] = $travel_times_back;
				
				echo json_encode($result);	
			}
		}
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
