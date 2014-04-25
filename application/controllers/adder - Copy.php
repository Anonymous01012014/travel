<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Adder extends CI_Controller {

	
	 public function __construct()
    {
        parent::__construct();
        //
    }
    
    
	public function index($num)
	{
		$this->load->model('highway_model');
		$num = $this->highway_model->increment($num);
		echo $num.PHP_EOL;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
