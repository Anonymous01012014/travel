<?php if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Class name : Traveller
 * 
 * Description :
 * This class contains functions to deal with the traveller table (Add , Edit , Delete)
 * 
 * Created date ; 19-04-2014
 * Modification date : ---
 * Modfication reason : ---
 * Author : Ahmad Mulhem Barakat
 * contact : molham225@gmail.com
 */    

class Traveller_model extends CI_Model{
	/** Traveller class variables **/
	
	//The id field of the traveller
	var $id;
	
	//The mac address of the device that the traveler used
	var $mac_address = "";
	
	//type of the device
	var $device_type = 0;
	
	//The vehicle that the traveler used
	var $vehicle_type = 0;
	
	
	
	/**
     * Constructor
     **/	
	function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Class functions
	 **/
    
    /**
	 * function name : addTraveller
	 * 
	 * Description : 
	 * add new traveller to the database.
	 * 
	 * parameters:
	 * 	
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function addTraveller(){
		$query = "INSERT INTO traveller(
							mac_address,
							device_type,
							vehicle_type
						) 
						VALUES (
							'{$this->mac_address}',
							'{$this->device_type}',
							'{$this->vehicle_type}'
						);
					";
		$this->db->query($query);
		return $this->db->insert_id();
	 }
	 
	 /**
	 * function name : deleteTraveller
	 * 
	 * Description : 
	 * delete the traveller of the given id from the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function deleteTraveller(){
		$query = "delete from traveller
	 			  where id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	 
	 /**
	 * function name : modifyTraveller
	 * 
	 * Description : 
	 * modify the data of the traveller of the given id.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function modifyTraveller(){
		$query = "UPDATE traveller
				  SET
					mac_address = {$this->mac_address},
					device_type = {$this->device_type},
					vehicle_type = {$this->vehicle_type}		
	 			  WHERE id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	
	/**
	 * function name : getAllTravellers
	 * 
	 * Description : 
	 * Returns the data of all of the travellers in the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getAllTravellers(){
		$query = "SELECT * 
				  FROM traveller";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }

	/**
	 * function name : getTravellerByMac
	 * Description : 
	 * returns the traveller info specified by the given mac address.
	 * 
	 * Created date : 25-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat*
	 * contact : molham225@gmail.com
	 */
	 public function getTravellerByMac(){
		$query = "SELECT id 
				  FROM traveller
				  where mac_address like '{$this->mac_address}'";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
}
