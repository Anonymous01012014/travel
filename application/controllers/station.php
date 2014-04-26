<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
<<<<<<< HEAD
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
		$exists = checkStation();
		if(!$exists){
			//if not add it
			newStation();
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
		//if the station was found return true
		return true;
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
		
		$this->station_model->station_ID = $station_ID;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
=======

class Station extends CI_Controller {

	public function index()
	{
		$this->load->view('welcome_message');
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
	
}
>>>>>>> 8bcbcd2e628ecd9b5c5294abc41939c681956332
