<?php if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Class name : Highway
 * 
 * Description :
 * This class contains functions to deal with the highway table (Add , Edit , Delete)
 * 
 * Created date ; 19-04-2014
 * Modification date : ---
 * Modfication reason : ---
 * Author : Ahmad Mulhem Barakat
 * contact : molham225@gmail.com
 */    

class Highway_model extends CI_Model{
	/** Highway class variables **/
	
	//The id field of the highway
	var $id;
	
	//Name of the highway that came back from the google service
	var $name = "";
	
	//type of the highway
	var $type = "";
	
	//The id of the the first station in the highway.
	var $start_station = "";
	
	//The id of the the last station in the highway.
	var $end_station = "";
	
	
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
	 * function name : addHighway
	 * 
	 * Description : 
	 * add new highway to the database.
	 * 
	 * parameters:
	 * 	
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function addHighway(){
		$query = "INSERT INTO highway(
							name,
							type,
							start_station,
							end_station
						) 
						VALUES (
							'{$this->name}',
							'{$this->type}',
							'{$this->start_station}',
							'{$this->end_station}'
						);
					";
		$this->db->query($query);
		return $this->db->insert_id();
	 }
	 
	 /**
	 * function name : deleteHighway
	 * 
	 * Description : 
	 * delete the highway of the given id from the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function deleteHighway(){
		$query = "delete from highway
	 			  where id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	 
	 /**
	 * function name : modifyHighway
	 * 
	 * Description : 
	 * modify the data of the highway of the given id.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function modifyHighway(){
		$query = "UPDATE highway
				  SET
					name = {$this->name},
					type = {$this->type},
					start_station = {$this->start_station},
					end_station = {$this->end_station}			
	 			  WHERE id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	
	/**
	 * function name : getAllHighways
	 * 
	 * Description : 
	 * Returns the data of all of the highways in the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getAllHighways(){
		$query = "SELECT * 
				  FROM highway";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
	 /**
	 * function name : getHighwayByName
	 * 
	 * Description : 
	 * Returns the Highway stations in the database.
	 * 
	 * Created date : 26-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getHighwayByName(){
		$query = "SELECT  
				  FROM highway
				  where name={$this->name}";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
}
