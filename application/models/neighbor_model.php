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
	var $station_id = "";
	
	//The id of the station that is a neighbor for the specified station.
	var $neighbor_id = "";
	
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
							station_id,
							neighbor_id,
							distance
						) 
						VALUES (
							'{$this->station_id}',
							'{$this->neighbor_id}',
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
		$query = "delete from neighbor
	 			  where id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	 /**
	 * function name : deleteNeighborByStationAndNeighbor
	 * 
	 * Description : 
	 * delete the neighbor of station and neighbor's ads.
	 * 
	 * Created date : 27-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function deleteNeighborByStationAndNeighbor(){
		$query = "delete from neighbor
	 			  where station_id = {$this->station_id}
					AND neighbor_id = {$this->neighbor_id}";
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
					station_id = {$this->station_id},
					neighbor_id = {$this->neighbor_id},
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
	 * function name : getNeighborsByStationId
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
	 public function getNeighborsByStationId(){
		$query = "SELECT * 
				  FROM neighbor
				  Where station_id = {$this->station_id}";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 /**
	 * function name : getNeighborsByNeighborId
	 * 
	 * Description : 
	 * Returns the data of all of the neighbors of the specified neighbor in the database.
	 * 
	 * Created date : 28-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getNeighborsByNeighborId(){
		$query = "SELECT * 
				  FROM neighbor
				  Where neighbor_id = {$this->neighbor_id}";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
	 
	 
}
