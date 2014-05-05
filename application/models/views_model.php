<?php if (!defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Class name : Views_model
 * 
 * Description :
 * This class contains functions to deal with the vies in the database
 * 
 * Created date : 05-05-2014
 * Modification date : ---
 * Modfication reason : ---
 * Author : Ahmad Mulhem Barakat
 * contact : molham225@gmail.com
 */    

class Views_model extends CI_Model{
	/** views class variables **/
	
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
	 * function name : getSegmentTravelTimes
	 * 
	 * Description : 
	 * gets the travel times of the segments of the highway specified by the given id.
	 * 
	 * parameters:
	 * highway_id: the id of the highway for these segments.	
	 * 
	 * Created date :  05-05-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	 public function getSegmentTravelTimes($highway_id){
		$query = "SELECT * 
					FROM segment_travel_time
					WHERE highway_id = {$highway_id};";
		$result = $this->db->query($query);
		return $result->result_array();
	 }
	 
}
