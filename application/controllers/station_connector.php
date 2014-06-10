<?php if (!defined("BASEPATH")) exit("No direct script access allowed");
	/**
	 * Filename: station_connector
	 * Description: 
	 * station connector controller is for controlling websocket connection from stations and receive their messages.
	 * 
	 *  
	 * created date: 25-04-2014 
	 * ccreated by: Eng. Ahmad Mulhem Barakat
	 * contact: molham225@gmail.com
	 */
	 
	 /* loading important libraries */
	use Ratchet\Server\IoServer;
	use Ratchet\Http\HttpServer;
	use Ratchet\WebSocket\WsServer;
	use Ratchet\MessageComponentInterface;
	use Ratchet\ConnectionInterface;
	require dirname(__DIR__) . '\\vendor\\autoload.php';
	
	
	class Station_connector extends CI_Controller implements MessageComponentInterface {
	
		protected $clients;
		
		/* server to station message fields constants */
		
		/* message types */
		var $ACK = 0;	
		var $ERROR = 9;
		
		/* codes */
		var $SUCCESS = 0;
		var $UNAUTHORIZED = 1;	
		var $NOT_REGISTERED = 2;
		var $INVALID_MESSAGE = 3;	
		var $EXECUTION_ERROR = 4;
		
		/* message details codes */	
		//var $SUCCESS = 0; code = 0
		//var $UNAUTHORIZED = 1; code = 1
		//var $NOT_REGISTERED = 2; code = 2
		
		var $MESSAGE_PARSING_ERROR = 3;//code = 3
		var $MESSAGE_TYPE_CONTENT_MISMATCH = 4;//code = 3
		var $MESSAGE_TYPE_ERROR = 5;//code = 3
		
		var $HIGHWAY_NOT_FOUND = 6;//code = 4
		var $STATION_REGITRATION_ERROR = 7;//code = 4
		var $PASS_ADDING_ERROR = 8;//code = 4

		/* End of server to station message fields constants */
		
		/**
		 * Function name : __construct
		 * Description: 
		 * this contructor is called as this object is initiated.
		 * 
		 * 
		 * created date: 25-04-2014 
		 * ccreated by: Eng. Ahmad Mulhem Barakat
		 * contact: molham225@gmail.com
		 */
		public function __construct() {
			$this->clients = new \SplObjectStorage;
		}
		/**
		 * Function name : onOpen
		 * Description: 
		 * this function adds the client's connection to the clients storage for later usage.
		 * it also adds the authenticated field to the connection object to be used for 
		 * authenticating this connection.
		 * 
		 * 
		 * Parameters:
		 * $conn: the object that represent the current connection with the specified station
		 * 
		 * created date: 25-04-2014 
		 * ccreated by: Eng. Ahmad Mulhem Barakat
		 * contact: molham225@gmail.com
		 */
		public function onOpen(ConnectionInterface $conn) {
			//add authenticated field to the connection object before attaching it to the clients storage
			$conn->authenticated = false;
			//add last message seq field to the client's object for future usage.
			$conn->last_message_seq = "";
			// Store the new connection to send messages to later
			$this->clients->attach($conn);
			echo "New connection! ({$conn->resourceId})\n";
			$this->logEvent("New connection interface {$conn->resourceId} opened..");
		}
		/**
		 * Function name : onMessage
		 * Description: 
		 * this function handles the messages coming from the connected stations.
		 * 
		 * 
		 * Parameters:
		 * $from: the object that represent the current connection with the specified station.
		 * $msg: the message sent from the current station to the server.
		 * 
		 * created date: 25-04-2014 
		 * ccreated by: Eng. Ahmad Mulhem Barakat
		 * contact: molham225@gmail.com
		 */
		public function onMessage(ConnectionInterface $from, $msg) {
			//log the received message to the cmd
			echo "New message from ".$from->resourceId." :\n".$msg."\n";
			$this->logEvent("New Message from interface ".$from->resourceId." :\n".$msg."");
			//load the message helper
			//$this->load->helper("message_helper");
			//the json decoded message
			$decoded_msg = "";
			//check if the message is in JSON format
			try{
				//echo "\n".$msg."\n";
				$decoded_msg = json_decode($msg);
				//echo "\n".$decoded_msg."\n";
				//get the message sequence 
				$message_sequence =  $decoded_msg->msg_seq;
				if($message_sequence != "")// if the message sequence is valid
				{
					//echo "\n".$message_sequence."\n";
					//echo "\n".$from->last_message_seq."\n";
					if($message_sequence != $from->last_message_seq){//if the message wasn't a duplicate to the last message
						//parse the not allowed characters using the url_encode
						$msg = urlencode($msg);
						//if the connection is not authorized yet then this message should be the authentication message
						if(!$from->authenticated){
							//get the satation_ID from the message
							$station_ID = $decoded_msg->station_id;
							echo "\n".$station_ID."\n";
							//check this station existence in the database
							$station_exists = shell_exec("php index.php main checkStation ".$station_ID." &");
							echo "\n".$station_exists."\n";
							//if the returned station id > 0 then the station was found
							if($station_exists > 0){
								$this->logEvent("Station ".$station_ID." connected on interface ".$from->resourceId.".");
								//if the station exists in the database then set the connection authenticated field to true 
								$from->authenticated = true;
								//and add the station id to the connection object
								$from->station_id = $station_exists * 1;
								$this->logEvent("Parsing and executing message(seq = ".$message_sequence.") from interface ".$from->resourceId.".");
								//send the message to the station controller to be parsed and executed
								$result = shell_exec("php index.php main receiveMessage ".$msg." &");
								
								//log the action to the cmd
								//echo sprintf('Connection %d sending main "%s"\n', $from->resourceId, $msg);
								//if the result came back from the execution == valid then acknoledge the 
								//message else just return the error message
								//echo $result."\n";
								//if($result == SUCCESS){
								//setting the last message sequence as the current message
								$from->last_message_seq = $message_sequence;
								//send back the result message to the station
								$this->sendToClient($from,$result,$message_sequence);
								/*}else{
									//send back the returned error message to the station
									$this->sendToClient($from,$result,$message_sequence);
								}*/
								
							}else{
								$this->logEvent("Connected on interface ".$from->resourceId." is not authorized.");
								echo "Unauthorized connection {$from->resourceId} closed 1\n";
								$this->sendToClient($from,$this->UNAUTHORIZED,"");
								$from->close();
							}
						}else{
							
							//echo sprintf('Connection %d sending message "%s"\n', $from->resourceId, $msg);
							$this->logEvent("Parsing and executing message(seq = ".$message_sequence.") from interface ".$from->resourceId.".");
							//send the message to the station controller to be parsed
							$result = shell_exec("php index.php main receiveMessage ".$msg." &");
							//if the result came back from the execution == valid then acknoledge the 
							//message else just return the error message
							//echo "\n".$result."\n";
							//if($result == |SUCCESS){
								//setting the last message sequence as the current message
							$from->last_message_seq = $message_sequence;
							//}
							//send back the result message to the station
							$this->sendToClient($from,$result,$message_sequence);
						}
					}
				}else{
					throw new Exception('Invalid message sequence');
				}
			}catch(Exception $e){
				$this->logEvent("Invalid message sequence sent from interface ".$from->resourceId.".");
				echo "Invalid message from connection {$from->resourceId}\n".$e->getMessage();
				$code = $this->MESSAGE_PARSING_ERROR;
				$this->sendToClient($from,$code,"");
				//if the connection that sent the misformatted message is not authenticated close the connection
				if(!$from->authenticated){
					$this->logEvent("Connected on interface ".$from->resourceId." is not authorized.");
					echo "Unauthorized connection {$from->resourceId} closed 2\n";
					$code = $this->UNAUTHORIZED;
					$this->sendToClient($from,$code,"");
					$from->close();
				}
			}
		}
		
		/**
		 * Function name : sendToClient 
		 * Description: 
		 * this function sends the formatted message to the specified client after 
		 * logging it to the cmd.
		 * 
		 * Parameters:
		 * client: the client that the message will be sent to.
		 * code : the code of the returned message.
		 * sequence : the sequence of the message that this message is a response to.
		 * 
		 * created date: 21-05-2014 
		 * ccreated by: Eng. Ahmad Mulhem Barakat
		 * contact: molham225@gmail.com
		 */
		public function sendToClient($client,$code,$sequence){
			//load message helper
			//$this->load->helper("message_helper");
			//format the message to be sent
			$message = $this->formatMessage($code,$sequence);
			//log the message to the cmd
			$this->logEvent("sending a message to interface ".$from->resourceId.":\n".$message);
			echo "sending Message to client ".$client->resourceId." :\n".$message."\n";
			//send the message
			$client->send($message);
		}
		
		/**
		 * Function name :onClose
		 * Description: 
		 * this function is called when the connection with the station is being closed.
		 * 
		 * 
		 * Parameters:
		 * $conn: the object that represent the current connection with the specified station
		 * 
		 * created date: 25-04-2014 
		 * ccreated by: Eng. Ahmad Mulhem Barakat
		 * contact: molham225@gmail.com
		 */
		public function onClose(ConnectionInterface $conn) {
			//if the connecion had a station id field disconnect the station
			if(isset($conn->station_id)){
				$this->logEvent("connection closed with station ".$conn->station_id." on interface ".$conn->resourceId);
				shell_exec("php index.php message discoonectStation ".$conn->station_id." &");
			}else{
				$this->logEvent("connection closed on interface ".$conn->resourceId);
			}
			// The connection is closed, remove it, as we can no longer send it messages
			$this->clients->detach($conn);
			echo "Client {$conn->resourceId} disconnected\n";
		}
		/**
		 * Function name : onError 
		 * Description: 
		 * this function closes the connection with the client if an error occurred.
		 * 
		 * 
		 * Parameters:
		 * $conn: the object that represent the current connection with the specified station
		 * 
		 * created date: 25-04-2014 
		 * ccreated by: Eng. Ahmad Mulhem Barakat
		 * contact: molham225@gmail.com
		 */
		public function onError(ConnectionInterface $conn, \Exception $e) {
			echo "An error has occurred: {$e->getMessage()}\n";
			$this->logEvent("An error has occurred: {$e->getMessage()}");
			$conn->close();
		}
		
		
		
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
			case $this->SUCCESS:
				$message["msg_type"] = $this->ACK;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = $this->SUCCESS;
				$message["code_msg"] = "success";
				$message["code_details"] = "Message executed successfully";
				$this->logEvent("Message(sequence = ".$message_sequence.") executed successfully.");
				break;
			case $this->UNAUTHORIZED:
				$message["msg_type"] = $this->ERROR;
				$message["code"] = $this->UNAUTHORIZED;
				$message["code_msg"] = "unauthorized";
				$message["code_details"] = "This connection is unauthorized so it will be closed!";
				break;
			case $this->NOT_REGISTERED:
				$message["msg_type"] = $this->ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = $this->NOT_REGISTERED;
				$message["code_msg"] = "not registered";
				$message["code_details"] = "Station not registered in the database!";
				break;
			case $this->MESSAGE_PARSING_ERROR:
				$message["msg_type"] = $this->ERROR;
				$message["code"] = $this->INVALID_MESSAGE;
				$message["code_msg"] = "invalid message";
				$message["code_details"] = "Error while parsing the message!";
				break;
			case $this->MESSAGE_TYPE_CONTENT_MISMATCH:
				$message["msg_type"] = $this->ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = $this->INVALID_MESSAGE;
				$message["code_msg"] = "invalid message";
				$message["code_details"] = "message type doesn't match its content!";
				break;
			case $this->MESSAGE_TYPE_ERROR:
				$message["msg_type"] = $this->ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = $this->INVALID_MESSAGE;
				$message["code_msg"] = "invalid message";
				$message["code_details"] = "Invalid message Type";
				break;
			case $this->HIGHWAY_NOT_FOUND:
				$message["msg_type"] = $this->ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = $this->EXECUTION_ERROR;
				$message["code_msg"] = "execution error";
				$message["code_details"] = "Could not get the highway name from the given long,lat";
				break;
			case $this->STATION_REGITRATION_ERROR:
				$message["msg_type"] = $this->ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = $this->EXECUTION_ERROR;
				$message["code_msg"] = "execution error";
				$message["code_details"] = "Error while registering the station!";
				break;
			case $this->PASS_ADDING_ERROR:
				$message["msg_type"] = $this->ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = $this->EXECUTION_ERROR;
				$message["code_msg"] = "execution error";
				$message["code_details"] = "Error while adding new pass!";
				break;
			default:
				$message["msg_type"] = $this->ERROR;
				$message["msg_seq"] = $message_sequence;
				$message["code"] = $this->EXECUTION_ERROR;
				$message["code_msg"] = "execution error";
				$message["code_details"] = "Unknown error occurred during execution!";
				break;
		}
		return json_encode($message);
	}
	
	
	/**
	 * file name : logEvent
	 * 
	 * Description :
	 * this function is for logging events to the log.txt file.
	 * 
	 * Created date ; 8-06-2014
	 * Modification date : ---
	 * Modfication reason : ---
	 * Author : Ahamad Mulhem Barakat
	 * contact : molham225@gmail.com
	 */    
	
	function logEvent($message)
	{
		$fp = fopen('files/log.txt', 'a');
		fwrite($fp, $message);
		fwrite($fp, "\n");
		fclose($fp);
	}
	
	
}

    /**
	 * Starting the web socket server using station_connector object on port 9100
	 */
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Station_connector()
            )
        ),
        9000
    );

    $server->run();



