<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//require dirname(__DIR__)."\\models\\neighbor_model.php";

class Timer extends CI_Controller {
	//loading models
	
	
	var $nValue = 15;//the default value of 'N' to be used in calculating travel time average.
	
	
	 /**
	 * function name : start
	 * 
	 * Description : 
	 * this function starts the timer.in this function we check every hour if this is 12 AM after a week from the last average value update.
	 * if it's the function calls the calcAVG function
	 * 
	 * parameters:
	 * 	
	 * Created date : 14-07-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	public function start(){
		$CI =& get_instance();//code igniter instance
		
		date_default_timezone_set('America/Chicago');//change time zone to the america/central which is the time
		while(true){
			$hour = date('H');
			echo $hour;
			if(($hour * 1) == 7){
				echo "in";
				/* check if this is the time for refreshing the average calculation */
				//load required models
				$CI->load->model("neighbor_model");
				//$neighbor_model = new Neighbor_model();
				//get all of the segmennts(neighbors)
				$all_segments = $CI->neighbor_model->getAllNeighbors();
				//get all segments with last passing time
				$all_segments_with_pass_time = $CI->neighbor_model->getAllNeighborsWithLastPassTime();
				if(count($all_segments_with_pass_time) > 0 && isset($all_segments_with_pass_time[0]["pass_time"])){
					$pass_time = explode(" ",$all_segments_with_pass_time[0]["pass_time"]);
					$last_update_date = date('Y-m-d',strtotime($pass_time[0]. ' + 7 days'));
					$current_date = date("Y-m-d");
					//if today's date is a week after the last update date or more then calculate the new running average 
					if(strtotime($last_update_date) <= strtotime($current_date)){
						$this->calcAVG($all_segments);
					}
				}else if(count($all_segments_with_pass_time) == 0){
					$this->calcAVG($all_segments);
				}
				
			}
			sleep(3600);
		}
	}
	
	
	 /**
	 * function name : calcAVG
	 * 
	 * Description : 
	 * This function loops over the segment array and calculates the travel time average for each one.
	 * 
	 * parameters:
	 * $segments: an array of segments(Neighbor model objects) to calculate the travel time for.	
	 * 
	 * Created date : 14-07-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	public function calcAVG($segments){
		
		$CI =& get_instance();//code igniter instance
		
		//calculate the new average for each segment
		foreach($segments as $segment){
			//The average
			$avg = 0;
			echo "segment: ". $segment["station_id"]. " : ". $segment["neighbor_id"]."\n";
			
			//load required models
			$CI->load->model("travel_model");
			//getting travels of this segment that happened after last travel entered in the avg calculations 
			//along with the travels used for the calculations. 
			$CI->travel_model->id = $segment["last_travel_id"] * 1 - $this->nValue;
			$segment_travels = $CI->travel_model->getSegmentTravels($segment["station_id"],$segment["neighbor_id"]);
			//check if we have enough travel times for calculating the average.
			if(count($segment_travels) >= $this->nValue){
				$segment_tts = array();//segment travel times
				//put all travel times in an array to start avg calculation
				foreach($segment_travels as $travel){
					$segment_tts[] = $travel["travel_time"];
				}
				//check if this the first time to calculate the average or not.
				if(($segment["travel_time_average"]*1) > 0){//if not first time
					$avg = $this->SMAWithInitialValue($segment_tts,$this->nValue,$segment["travel_time_average"]);
				}else{//if first time
					$avg = $this->SMA($segment_tts,$this->nValue);
				}
				echo "New Average: ".$avg."\n";
				//update the average and the last used travel in the database
				$this->load->model("neighbor_model");
				
				$this->neighbor_model->id = $segment["id"];
				$this->neighbor_model->travel_time_average = $avg;
				$this->neighbor_model->last_travel_id = $segment_travels[count($segment_travels) - 1]["id"];
				
				$this->neighbor_model->modifySegmentAverage();
			}
		}
	}
	
	
	 /**
	 * function name : SMA
	 * 
	 * Description : 
	 * This function is used to calculate the moving average of an array of numbers:
	 * moving average rule:
	 * 			Pm + Pm-1 + ... + Pm-n
	 * SMA0 =  ------------------------
	 * 					n
	 * 
	 * 				 Pm-n     Pm    
	 * SMA = SMA0 - ------ + -----
	 * 				   n       n
	 * 
	 * parameters:
	 * $array: an array of numbers to calculate the moving average for.
	 * $step: repressnts the n value in the moving average rule	
	 * 
	 * 
	 * Created date : 14-07-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	public function SMA($array,$step){
		$sma = 0;
		//calculate the initial SMA
		for($i=0; $i<$step ; $i++){
			$sma = $sma + $array[$i];
		}
		
		$sma = $sma/$step;
		
		//calculate the moving average
		for ($i=$step; $i< count($array);$i++){
			if(isset($array[$i - $step])){
				$sma = $sma -  (($array[$i - $step])/$step) + ($array[$i]/$step);
			}
		}
		//echo $sma;
		return $sma;
	}
	
	
	/**
	 * function name : SMAWithInitialValue
	 * 
	 * Description : 
	 * This function is used to calculate the moving average of an array of numbers 
	 * but here the initial average value is already given
	 * moving average rule:
	 * 			Pm + Pm-1 + ... + Pm-n
	 * SMA0 =  ------------------------ //this rule is not used
	 * 					n
	 * 
	 * 				 Pm-n     Pm    
	 * SMA = SMA0 - ------ + -----
	 * 				   n       n
	 * 
	 * parameters:
	 * $array: an array of numbers to calculate the moving average for.
	 * $step: repressnts the n value in the moving average rule
	 * $initialAVG: the initial value of the moving average (SMA0).	
	 * 
	 * 
	 * Created date : 14-07-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	public function SMAWithInitialValue($array,$step,$initialAVG){
		$sma = $initialAVG;
		//calculate the moving average
		for ($i=$step; $i< count($array);$i++){
			if(isset($array[$i - $step])){
				$sma = $sma -  (($array[$i - $step])/$step) + ($array[$i]/$step);
			}
		}
		//echo $sma;
		return $sma;
	}
}
