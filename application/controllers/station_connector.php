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
	use Ratchet\Server\IoServer;
	use Ratchet\Http\HttpServer;
	use Ratchet\WebSocket\WsServer;
	use Ratchet\MessageComponentInterface;
	use Ratchet\ConnectionInterface;
	require dirname(__DIR__) . '\\vendor\\autoload.php';
	class Station_connector extends CI_Controller implements MessageComponentInterface {
		protected $clients;
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
		}
		/**
		 * Function name : onMessage
		 * Description: 
		 * this function handles the messages coming from the connected stations.
		 * 
		 * 
		 * created date: 25-04-2014 
		 * ccreated by: Eng. Ahmad Mulhem Barakat
		 * contact: molham225@gmail.com
		 */
		public function onMessage(ConnectionInterface $from, $msg) {
			//the json decoded message
			$decoded_msg = "";
			//check if the message is in JSON format
			try{
				$decoded_msg = json_decode($msg);
				//get the message sequence 
				$message_sequence =  $decoded_msg->msg_seq;
				if($message_sequence != "")// if the message sequence is valid
				{
					if($message_sequence != $from->last_message_seq){//if the message wasn't a duplicate to the last message
						//parse the not allowed characters using the url_encode
						$msg = urlencode($msg);
						//if the connection is not authorized yet then this message should be the authentication message
						if(!$from->authenticated){
							//get the satation_ID from the message
							$station_ID = $decoded_msg->station_id;
							//check this station existence in the database
							$station_exists = shell_exec("php index.php main checkStation ".$msg." ");
							//if the returned station id > 0 then the station was found
							if($station_exists > 0){
								//if the station exists in the database then set the connection authenticated field to true 
								$from->authenticated = true;
								//and add the station id to the connection object
								$from->station_id = $station_exists * 1;
								
								//send the message to the station controller to be parsed and executed
								$result = shell_exec("php index.php message receive_message ".$msg." &");
								
								$numRecv = count($this->clients) - 1;
								//log the action to the cmd
								echo sprintf('Connection %d sending main "%s"\n', $from->resourceId, $msg);
								//if the result came back from the execution == valid then acknoledge the 
								//message else just return the error message
								if($result == "valid"){
									//send back an Acknoledgement message to the station
									$message = array("ACK"=> $message_sequence);
									$message = json_encode($message);
									$from->send(message);
								}else{
									//send back the returned error message to the station
									$message = array("error"=> $result);
									$message = json_encode($message);
									$from->send(message);
								}
								
							}else{
								echo "Unauthorized connection {$from->resourceId} closed\n";
								$error = array("error"=>"This connection is unauthorized so it will be closed!");
								$from->send(json_encode($error));
								$from->close();
							}
						}else{
						
							$numRecv = count($this->clients) - 1;
							
							echo sprintf('Connection %d sending message "%s"\n'
								, $from->resourceId, $msg);
							//send the message to the station controller to be parsed
							$result = shell_exec("php index.php message receive_message ".$msg." &");
							//if the result came back from the execution == valid then acknoledge the 
							//message else just return the error message
							if($result == "valid"){
									//send back an Acknoledgement message to the station
									$message = array("ACK"=> $message_sequence);
									$message = json_encode($message);
									$from->send(message);
								}else{
									//send back the returned error message to the station
									$message = array("error"=> $result);
									$message = json_encode($message);
									$from->send(message);
								}
						}
					}
				}else{
					throw new Exception('Invalid message sequence');
				}
			}catch(Exception $e){
				echo "Invalid message from connection {$from->resourceId}\n".$e->getMessage();
				$error = array("error"=>"Invalid message!");
				$from->send(json_encode($error));
				//if the connection that sent the misformatted message is not authenticated close the connection
				if(!$from->authenticated){
					echo "Unauthorized connection {$from->resourceId} closed\n";
					$error = array("error"=>"This connection is unauthorized so it will be closed!");
					$from->send(json_encode($error));
					$from->close();
				}
			}
		}
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
		public function onClose(ConnectionInterface $conn) {
			//if the connecion had a station id field disconnect the station
			if(isset($conn->station_id)){
				shell_exec("php index.php message discoonectStation ".$conn->station_id." &");
			}
			// The connection is closed, remove it, as we can no longer send it messages
			$this->clients->detach($conn);
			echo "Connection {$conn->resourceId} has disconnected\n";
		}
		/**
		 * Function name : onError 
		 * Description: 
		 * this function closes the connection with the client if an error occurred.
		 * 
		 * 
		 * created date: 25-04-2014 
		 * ccreated by: Eng. Ahmad Mulhem Barakat
		 * contact: molham225@gmail.com
		 */
		public function onError(ConnectionInterface $conn, \Exception $e) {
			echo "An error has occurred: {$e->getMessage()}\n";

			$conn->close();
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



