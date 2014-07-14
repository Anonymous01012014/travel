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
	
	//The running average of the current segment of the highway
	var $travel_time_average = "";
	
	//The id of the last travel used to calculate the running average
	var $last_travel_id = "";
	
	
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
							distance,
							travel_time_average,
							last_travel_id
						) 
						VALUES (
							'{$this->station_id}',
							'{$this->neighbor_id}',
							'{$this->distance}',
							'0',
							'0'
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
					distance = {$this->distance},
					travel_time_average = {$this->travel_time_average},
					last_travel_id = {$this->last_travel_id}			
	 			  WHERE id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	 
	 /**
	 * function name : modifySegmentAverage
	 * 
	 * Description : 
	 * change the running average value to the given one and the last travel used to the new one's id.
	 * 
	 * Created date : 12-07-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function modifySegmentAverage(){
		$query = "UPDATE neighbor
				  SET
					travel_time_average = {$this->travel_time_average},
					last_travel_id = {$this->last_travel_id}			
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
				  FROM neighbor
				  ORDER BY id";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
	/**
	 * function name : getAllNeighborsWithLastPassTime
	 * 
	 * Description : 
	 * Returns the data of all of the neighbors in the database along with 
	 * the timing of the passing of the last travel to be considered for 
	 * calculating travel time average.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getAllNeighborsWithLastPassTime(){
		$query = "SELECT   n.[id] as id
						  ,n.[distance] as distance
						  ,n.[station_id] as station_id
						  ,n.[neighbor_id] as neighbor_id
						  ,n.[travel_time_average] as travel_time_average
						  ,n.[last_travel_id] as last_travel_id
						  ,p.[passing_time] as pass_time
				  FROM 	 [travel_time].[dbo].[neighbor] AS n 
						,[travel_time].[dbo].[travel] AS t
						,[travel_time].[dbo].[passing] AS p
				  WHERE n.last_travel_id = t.id 
					AND t.passing_to = p.id
				  ORDER BY n.id";
				  
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
