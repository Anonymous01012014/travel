<?php if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Class name : Station
 * 
 * Description :
 * This class contains functions to deal with the station table (Add , Edit , Delete)
 * 
 * Created date ; 19-04-2014
 * Modification date : ---
 * Modfication reason : ---
 * Author : Ahmad Mulhem Barakat
 * contact : molham225@gmail.com
 */    

class Station_model extends CI_Model{
	
	/** Station class variables **/
	
	//The id field of the station in the database
	var $id;
	
	//The actual ID of the station
	var $station_ID = "";
	
	//longitude of the station's GPS location
	var $longitude = "";
	
	//latitude of the station's GPS location
	var $latitude = "";
	
	//Current status of the station.
	var $status = "";
	
	//The date of starting this statuion.
	var $start_date = "";
	
	//The date of ending this statuion.
	var $end_date = "";
	
	//The highway of this station
	var $higway_id = "";
	
	//neighbor list for this station
	var $neighbors = array();
	
	
	/* Station states */
	var $CONNECTED = 1;
	var $DISCONNECTED = 2;
	
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
	 * function name : addStation
	 * 
	 * Description : 
	 * add new station to the database.
	 * 
	 * parameters:
	 * 	
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function addStation(){
		$query = "INSERT INTO station(
							station_ID,
							longitude,
							latitude,
							status,
							start_date,
							highway_id
							
						) 
						VALUES (
							'{$this->station_ID}',
							'{$this->longitude}',
							'{$this->latitude}',
							'{$this->status}',
							CAST(GETDATE() AS DATE),
							'{$this->highway_id}'
						);
					";
		$this->db->query($query);
		return $this->db->insert_id();
	 }
	 
    /**
	 * function name : startStation
	 * 
	 * Description : 
	 * add new station to the database.
	 * 
	 * parameters:
	 * 	
	 * Created date : 04-05-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function startStation(){
		$query = "UPDATE station
				  SET
					station_ID = '{$this->station_ID}',
					longitude = {$this->longitude},
					latitude = {$this->latitude},
					status = {$this->status},		
					start_date = CAST(GETDATE() AS DATE),		
					highway_id = {$this->highway_id}		
	 			  WHERE station_ID like '{$this->station_ID}'";
		$this->db->query($query);
		return true;
	 }
	 
	 /**
	 * function name : deleteStation
	 * 
	 * Description : 
	 * delete the station of the given id from the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function deleteStation(){
		$query = "delete from station
	 			  where id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	 
	 /**
	 * function name : modifyStation
	 * 
	 * Description : 
	 * modify the data of the station of the given id.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function modifyStation(){
		$query = "UPDATE station
				  SET
					station_ID = '{$this->station_ID}',
					longitude = {$this->longitude},
					latitude = {$this->latitude},
					status = {$this->status},		
					start_date = {$this->start_date},		
					end_date = {$this->end_date},		
					highway_id = {$this->highway_id}		
	 			  WHERE id = {$this->id}";
		$this->db->query($query);
		return true;
	 }
	
	/**
	 * function name : getAllStations
	 * 
	 * Description : 
	 * Returns the data of all of the stations in the database.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getAllStations(){
		$query = "SELECT * 
				  FROM station";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
	 /**
	 * function name : getStationByStationID
	 * 
	 * Description : 
	 * returns the station specified by the given sstation_ID
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getStationByStationID(){
		$query = "SELECT * 
				  FROM station
				  WHERE station_ID like '{$this->station_ID}'";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
	 /**
	 * function name : getStationById
	 * 
	 * Description : 
	 * returns the station specified by the given id.
	 * 
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getStationById(){
		$query = "SELECT * 
				  FROM station
				  WHERE id = {$this->id}";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
	 /**
	 * function name : getStationsbyHighway
	 * 
	 * Description : 
	 * Returns the Highway stations in the database.
	 * 
	 * Created date : 21-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getStationsbyHighway(){
		$query = "SELECT  *
				  FROM station
				  where highway_id={$this->highway_id}";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
	 /**
	 * function name : getTwoWayHighwayStationsNeighborCount
	 * 
	 * Description : 
	 * Returns the count of the neighbors of each station in the specified highway.
	 * 
	 * Created date :29-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getTwoWayHighwayStationsNeighborCount(){
		$query = "SELECT  station.id as id ,count(neighbor.neighbor_id) as neighbor_count
				  FROM station INNER JOIN neighbor ON station.id = neighbor.station_id
				  where station.highway_id={$this->highway_id}
				  GROUP BY station.id 
				  Having count(neighbor.neighbor_id) = 1;";
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 /**
	 * function name : getOneWayHighwayLastStation
	 * 
	 * Description : 
	 * Returns the last station of a oneway highway
	 * 
	 * Created date :29-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 *
	 public function getOneWayHighwayLastStation(){
		$query = "SELECT  station.id as id ,count(neighbor.neighbor_id) as neighbor_count
				  FROM station LEFT JOIN neighbor ON station.id = neighbor.station_id
				  where station.highway_id={$this->highway_id}
				  GROUP BY station.id,neighbor.neighbor_id 
				  Having count(neighbor.neighbor_id) = 0;";
				  echo $query;
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
	 
	 /**
	 * function name : getOneWayHighwayFirstStation
	 * 
	 * Description : 
	 * Returns the first station of a oneway highway
	 * 
	 * Created date :29-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 *
	 public function getOneWayHighwayFirstStation(){
		$query = "SELECT  station.id as id ,count(neighbor.station_id) as neighbor_count
				  FROM station LEFT JOIN neighbor ON station.id = neighbor.neighbor_id
				  where station.highway_id={$this->highway_id}
				  GROUP BY station.id,neighbor.station_id 
				  Having count(neighbor.neighbor_id) = 0;";
				  echo $query;
		$query = $this->db->query($query);
		return $query->result_array();
	 }*/
	 
	 /**
	 * function name : getStationsWithOneNeighbor
	 * 
	 * Description : 
	 * Gets the stations with only one neighbor and in the specified highway
	 * 
	 * parameters:
	 * 	
	 * Created date : 19-04-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getStationsWithOneNeighbor(){
		$query = "SELECT * 
				  FROM station
				  where highway_id ={$this->highway_id}
					AND count(SELECT id 
								FROM neighbor 
								WHERE {$this->id} = station) = 1";
				  
		$query = $this->db->query($query);
		return $query->result_array();
	 }
	 
	 /**
	 * function name : changeStationStatus
	 * 
	 * Description : 
	 * This function changes the status of the station with the given id to the give status
	 * 
	 * parameters:
	 * 	
	 * Created date : 16-05-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function changeStationStatus(){
		$query = "UPDATE station 
					SET  status = {$this->status}
					WHERE id = {$this->id}";
				  
		$this->db->query($query);
		return true;
	 }
	 
}
