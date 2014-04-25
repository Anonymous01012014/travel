<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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
