<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class SendMail extends CI_Controller {
	public function send($subject,$message){
		
		$config = Array(
			'protocol' => 'smtp',
			'smtp_host' => 'ssl://smtp.googlemail.com',
			'smtp_port' => 465,
			'smtp_user' => 'ecobuild.sy@gmail.com',
			'smtp_pass' => 'ecobuild2120446',
			'mailtype'  => 'html', 
			'charset'   => 'iso-8859-1'
		);
		
		$this->load->library("email", $config);
		$this->email->set_newline("\r\n");
		$this->email->from("ecobuild.sy@gmail.com","Travel Time");
		$this->email->to("itsstulsa@gmail.com");
		$this->email->subject("Duplicate Station ID");
		$this->email->message("A new station (".$station_ID.") is trying to connect the server <br />
							while there is another station with the same id already connected to the server!! ");
		
		
		$result = $this->email->send();
		
		//echo $this->email->print_debugger();


	}






}
