<?php if (!defined("BASEPATH")) exit("No direct script access allowed");

	/**
	 * file name : log_helper
	 * 
	 * Description :
	 * this helper contain a function for logging events to the log.txt file
	 * server to station messages
	 * 
	 * Created date ; 8-06-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Mohanad Kaleia
	 * contact : ms.kaleia@gmail.com
	 */    
	
	function logEvent($message)
	{
		$CI =& get_instance();				

		if ( ! write_file('files/log.txt', $message , 'a'))
		{
		     echo 'Unable to write the file';
		}
		else
		{
		     echo 'File written!';
		}	
	}
	
?>
