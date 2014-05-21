<?php if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Class name : Travel
 * 
 * Description :
 * This class contains functions to deal with the travel table (Add , Edit , Delete)
 * 
 * Created date ; 19-04-2014
 * Modification date : ---
 * Modfication reason : ---
 * Author : Ahmad Mulhem Barakat
 * contact : molham225@gmail.com
 */    

class Travel_model extends CI_Model{
	/** Travel class variables **/
	
	//The id field of the travel
	var $id;
	
	//The duration of the travel
	var $travel_time = "";
	
	//determine if this travel is valid or not
	var $is_valid = "";
	
	//The id of the passing that represent the start of this travel
	var $passing_from = "";
	
	//The id of the passing that represent the end of this travel
	var $passing_to = "";
	
	
	
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
	 * function name : addTravel
	 * 
	 * Description : 
	 * add new travel to the database.
	 * 
	 * parameters:
	 * 	
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function addTravel(){
		$query = "INSERT INTO travel(
							travel_time,
							is_valid,
							passing_from,
							passing_to
						) 
						VALUES (
							'{$this->travel_time}',
							'{$this->is_valid}',
							'{$this->passing_from}',
							'{$this->passing_to}'
						);
					";
					//echo $query;
		$this->db->query($query);
		return $this->db->insert_id();
	 }
	 
	 /**
	 * function name : deleteTravel
	 * 
	 * Description : 
	 * delete the travel of the given id from the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function deleteTravel(){
		$query = "delete from travel
	 			  where id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	 
	 /**
	 * function name : modifyTravel
	 * 
	 * Description : 
	 * modify the data of the travel of the given id.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function modifyTravel(){
		$query = "UPDATE travel
				  SET
					travel_time = {$this->travel_time},
							is_valid = {$this->is_valid},
							passing_from = {$this->passing_from},
							passing_to = {$this->passing_to}
									
	 			  WHERE id = {$this->id}";
	 			  
		$this->db->query($query);
		return true;
	 }
	
	/**
	 * function name : getAllTravel
	 * 
	 * Description : 
	 * Returns the data of all of the travel in the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getAllTravel(){
		$query = "SELECT * 
				  FROM travel";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	/**
	 * function name : getTravelByPassings
	 * 
	 * Description : 
	 * Returns the data of the travel specified by the $passing_from and $passing_to fields
	 * 
	 * Created date : 14-05-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getTravelByPassings(){
		$query = "SELECT * 
				  FROM travel
				  WHERE passing_from = {$this->passing_from}
					AND passing_to = {$this->passing_to}";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
}
