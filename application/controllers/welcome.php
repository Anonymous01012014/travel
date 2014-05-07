<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	
	public function index($highway_id)
	{
		//loading required models
		$this->load->model("highway_model");
		$this->load->model("station_model");
		$this->load->model("neighbor_model");
		//load views model
		$this->load->model("views_model");
		//foreward (from start to end )travel times
		$travel_times = array();
		//backward (from end to start )travel times
		$travel_times_back = array();
		//highway travel time in foreward direction
		$highway_travel_time = 0;
		//highway travel time in back direction
		$highway_travel_time_back = 0;
		//get the highway 
		$this->highway_model->id = $highway_id;
		$highway = $this->highway_model->getHighwayById();
		if(isset($highway[0])){
			$highway = $highway[0];
		}
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
		
		if(isset($temp_station[0])){
			$highway_stations[] = $temp_station[0];
		}
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
			echo "from ".$highway_stations[$i - 1]['id']." to ".$highway_stations[$i]['id']."<br/>";
			foreach($segments_travel_times as $segment_travel_time){
				echo "segment: from ".$segment_travel_time['from_station_id']." to ".$segment_travel_time['to_station_id']."<br/>";
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
		for($i=count($highway_stations) - 2;$i> 0 ;$i--){
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
		var_dump($travel_times);
		var_dump($segments_travel_times);
		var_dump($highway_stations);
		var_dump($travel_times_back);
		
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
