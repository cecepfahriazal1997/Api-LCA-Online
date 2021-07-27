<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Welcome controller.
 * 
 * @author Cecep Rokani
 */
use chriskacerguis\RestServer\RestController;

class Welcome extends RestController {
	function __construct() {
        // Construct the parent class
        parent::__construct();
        $this->user = $this->auth->validateUserToken();
	}
	
	public function index_get() {
		$this->response(array('status' => false, 'message' => 'No such user found !'), 404);
	}
}
?>