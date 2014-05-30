<?php if (!defined("BASEPATH")) exit("No direct script access allowed");

	/**
	 * file name : messasge_helper
	 * 
	 * Description :
	 * This file contains functions and enumeration to deal with 
	 * server to station messages
	 * 
	 * Created date ; 28-05-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */    
	
	/* server to station message fields constants */
		
	/* message types */
	const ACK = 0;	
	const ERROR = 1;
	
	/* codes */
	const SUCCESS = 0;
	const UNAUTHORIZED = 1;	
	const NOT_REGISTERED = 2;
	const INVALID_MESSAGE = 3;	
	const EXECUTION_ERROR = 4;
	
	/* message details codes */	
	//const SUCCESS = 0; code = 0
	//const UNAUTHORIZED = 1; code = 1
	//const NOT_REGISTERED = 2; code = 2
	
	const MESSAGE_PARSING_ERROR = 3;//code = 3
	const MESSAGE_TYPE_CONTENT_MISMATCH = 4;//code = 3
	const MESSAGE_TYPE_ERROR = 5;//code = 3
	
	const HIGHWAY_NOT_FOUND = 6;//code = 4
	const STATION_REGITRATION_ERROR = 7;//code = 4
	const PASS_ADDING_ERROR = 8;//code = 4

	/* End of server to station message fields constants */


	/**
	 * function name : formatMeaasage
	 * 
	 * Description : 
	 * This function formats the message that will be sent to the station.
	 * 
	 * parameters:
	 * detail_code: the detail code of the message that will be sent.
	 * message_sequence: the message sequence.
	 * 
	 * Created date : 28-05-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahmad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */
	function formatMessage($detail_code,$message_sequence){
		//preparing message object
		$message = array();
		//adding the fields of the message
		$message["msg_type"] = "";
		$message["msg_seq"] = "";
		$message["code"] = "";
		$message["code_msg"] = "";
		$message["code_details"] = "";
		
		//finding out the detail code sent and filling the message fields depending on it.
		switch($detail_code){
			case SUCCESS:
				$message["msg_type"] = ACK;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = SUCCESS;
				$message["code_msg"] = "success";
				$message["code_details"] = "Message executed successfully";
				break;
			case UNAUTHORIZED:
				$message["msg_type"] = ERROR;
				$message["code"] = UNAUTHORIZED;
				$message["code_msg"] = "unauthorized";
				$message["code_details"] = "This connection is unauthorized so it will be closed!";
				break;
			case NOT_REGISTERED:
				$message["msg_type"] = ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = NOT_REGISTERED;
				$message["code_msg"] = "not registered";
				$message["code_details"] = "Station not registered in the database!";
				break;
			case MESSAGE_PARSING_ERROR:
				$message["msg_type"] = ERROR;
				$message["code"] = INVALID_MESSAGE;
				$message["code_msg"] = "invalid message";
				$message["code_details"] = "Error while parsing the message!";
				break;
			case MESSAGE_TYPE_CONTENT_MISMATCH:
				$message["msg_type"] = ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = INVALID_MESSAGE;
				$message["code_msg"] = "invalid message";
				$message["code_details"] = "message type doesn't match its content!";
				break;
			case MESSAGE_TYPE_ERROR:
				$message["msg_type"] = ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = INVALID_MESSAGE;
				$message["code_msg"] = "invalid message";
				$message["code_details"] = "Invalid message Type";
				break;
			case HIGHWAY_NOT_FOUND:
				$message["msg_type"] = ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = EXECUTION_ERROR;
				$message["code_msg"] = "execution error";
				$message["code_details"] = "Could not get the highway name from the given long,lat";
				break;
			case STATION_REGITRATION_ERROR:
				$message["msg_type"] = ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = EXECUTION_ERROR;
				$message["code_msg"] = "execution error";
				$message["code_details"] = "Error while registering the station!";
				break;
			case PASS_ADDING_ERROR:
				$message["msg_type"] = ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = EXECUTION_ERROR;
				$message["code_msg"] = "execution error";
				$message["code_details"] = "Error while adding new pass!";
				break;
			default:
				$message["msg_type"] = ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = EXECUTION_ERROR;
				$message["code_msg"] = "execution error";
				$message["code_details"] = "Unknown error occurred during execution!";
				break;
		}
		return json_encode($message);
	}
