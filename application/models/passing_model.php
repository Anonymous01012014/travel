<?php if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Class name : Passing
 * 
 * Description :
 * This class contains functions to deal with the passing table (Add , Edit , Delete)
 * 
 * Created date ; 19-04-2014
 * Modification date : ---
 * Modfication reason : ---
 * Author : Ahmad Mulhem Barakat
 * contact : molham225@gmail.com
 */    

class Passing_model extends CI_Model{
	/** Passing class variables **/
	
	//The id field of the passing
	var $id;
	
	//The passing time
	var $passing_time = "";
	
	//The id of the traveller passed
	var $traveller_id = "";
	
	//The id of station wich passed
	var $station_id = "";
	
	
	
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
	 * function name : addPassing
	 * 
	 * Description : 
	 * add new passing to the database.
	 * 
	 * parameters:
	 * 	
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function addPassing(){
		$query = "INSERT INTO passing(
							passing_time,
							traveller_id,
							station_id
						) 
						VALUES (
							'{$this->passing_time}',
							'{$this->traveller_id}',
							'{$this->station_id}'
						);
					";
		$this->db->query($query);
		return $this-db->insert_id;
	 }
	 
	 /**
	 * function name : deletePassing
	 * 
	 * Description : 
	 * delete the passing of the given id from the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function deletePassing(){
		$query = "delete from passing
	 			  where id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	 
	 /**
	 * function name : modifyPassing
	 * 
	 * Description : 
	 * modify the data of the passing of the given id.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function modifyPassing(){
		$query = "UPDATE passing
				  SET
					passing_time = {$this->passing_time},
							traveller_id = {$this->traveller_id},
							station_id = {$this->station_id}		
	 			  WHERE id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	
	/**
	 * function name : getAllPassing
	 * 
	 * Description : 
	 * Returns the data of all of the passing in the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getAllPassing(){
		$query = "SELECT * 
				  FROM passing";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	
	 /**
	 * function name : getDurationBetweenTwoStations
	 * 
	 * Description : 
	 * Returns the duration time for all the passing between two stations in the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getDurationBetweenTwoStations($fromStation,$toStation){
		$query = "SELECT travel_time 
				  FROM travel
				  where passing_from=select id from passing where station_id={$fromStation}
						and passing_to=select id from passing where station_id={$toStation}";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
	 /**
	 * function name : getPassingDatabyTraveller
	 * 
	 * Description : 
	 * Returns the duration time for all the passing between two stations in the database.
	 * 
	 * Created date : 21-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getPassingDatabyTraveller(){
		$query = "SELECT * 
				  FROM passing
				  where traveller_id={$this->id}";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
}
