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
	
	public function dashboard_get() {
		$datas['news'] = array('title' => 'Qurban 10 Eko Sapi', 'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
			'title' => '', 'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.');
		$this->response(array('status' => true, 'message' => '', 'data' => $datas), 200);
	}
}
?>