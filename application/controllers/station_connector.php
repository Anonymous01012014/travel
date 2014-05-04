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
			// check if the message is from an already authenticated station
			if($from->authenticated){
				//get the satation_ID from the message
				//check this station existence in the database
				$station_exists = shell_exec("php index.php message checkStation ".$station_ID);
				if($station_exists){
					$from->authenticated = true;
					$numRecv = count($this->clients) - 1;
				
				echo sprintf('Connection %d sending message "%s"\n'
					, $from->resourceId, $msg);
					//send the message to the station controller to be parsed
					$result = shell_exec("php index.php message receive_message ".$msg);
					//send the result back to the station
					$from->send($result);
				}else{
					echo "Unauthorized connection {$from->resourceId} closed\n";
					$from->send("This connection is unauthorized so it was closed!");
					$from->close();
				}
			}else{
			
				$numRecv = count($this->clients) - 1;
				
				echo sprintf('Connection %d sending message "%s"\n'
					, $from->resourceId, $msg);
				//send the message to the station controller to be parsed
				$result = shell_exec("php index.php message receive_message ".$msg);
				//send the result back to the station
					$from->send($result);
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
        9100
    );

    $server->run();



