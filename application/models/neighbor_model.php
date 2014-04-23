<?php if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Class name : Neighbor
 * 
 * Description :
 * This class contains functions to deal with the neighbor table (Add , Edit , Delete)
 * 
 * Created date ; 19-04-2014
 * Modification date : ---
 * Modfication reason : ---
 * Author : Ahmad Mulhem Barakat
 * contact : molham225@gmail.com
 */    

class Neighbor_model extends CI_Model{
	/** Neighbor class variables **/
	
	//The id field of the neighbor
	var $id;
	
	//The id of the station for which we are defining the neighbor.
	var $station = "";
	
	//The id of the station that is a neighbor for the specified station.
	var $neighbor = "";
	
	//The distance between the station and its neighbor in miles.
	var $distance = "";
	
	
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
	 * function name : addNeighbor
	 * 
	 * Description : 
	 * add new neighbor to the database.
	 * 
	 * parameters:
	 * 	
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function addNeighbor(){
		$query = "INSERT INTO neighbor(
							station,
							neighbor,
							distance
						) 
						VALUES (
							'{$this->station}',
							'{$this->neighbor}',
							'{$this->distance}'
						);
					";
		$this->db->query($query);
		return true;
	 }
	 
	 /**
	 * function name : deleteNeighbor
	 * 
	 * Description : 
	 * delete the neighbor of the given id from the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function deleteNeighbor(){
		$query = "delete from highway
	 			  where id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	 
	 /**
	 * function name : modifyNeighbor
	 * 
	 * Description : 
	 * modify the data of the neighbor of the given id.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function modifyNeighbor(){
		$query = "UPDATE neighbor
				  SET
					station = {$this->station},
					neighbor = {$this->neighbor},
					distance = {$this->distance}			
	 			  WHERE id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	
	/**
	 * function name : getAllNeighbors
	 * 
	 * Description : 
	 * Returns the data of all of the neighbors in the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getAllNeighbors(){
		$query = "SELECT * 
				  FROM neighbor";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
	 /**
	 * function name : getAllNeighborsByStationId
	 * 
	 * Description : 
	 * Returns the data of all of the neighbors of the specified station in the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getAllNeighborsByStationId(){
		$query = "SELECT * 
				  FROM neighbor
				  Where station = {$this->station}";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
}
