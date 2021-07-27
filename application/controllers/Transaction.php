<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Welcome controller.
 * 
 * @author Cecep Rokani
 */
use chriskacerguis\RestServer\RestController;

class Transaction extends RestController {
	function __construct() {
        // Construct the parent class
        parent::__construct();
		$this->load->model('TransactionModel', 'model');
        $this->user = $this->auth->validateUserToken();
	}
	
	public function index_get() {
		$response 	= array('status' => false, 'message' => 'Data tidak ditemukan');
		$list		= $this->model->getListTransaction($this->user->id, array_search($this->user->role, $this->auth->roleCode));
		if (!empty($list)) {
			$response['status']		= true;
			$response['message']	= '';
			$response['data']	 	= $list;
		}
		$this->response($response, 200);
	}
	
	public function save_post() {
		$response 	= array('status' => false, 'message' => 'Data gagal disimpan');
		$userId		= $this->user->id;
		$post		= $this->post(null, true);
		$id			= $post['id'];
		$type		= $post['type'];
		$teacherId	= $post['id_teacher'];
		$address	= $post['address'];
		$latLng		= $post['lat_lng'];
		$date		= $post['date'];
		$hour		= $post['hour'];
		$phone		= $post['phone'];
		$note		= $post['note'];

		$param				= array();
		$param['id_santri']	= $userId;
		$param['id_guru']	= $teacherId;
		$param['tipe']		= $type;
		$param['alamat']	= $address;
		$param['lat_lng']	= $latLng;
		$param['tanggal']	= $date;
		$param['jam']		= $hour;
		$param['no_hp']		= $phone;
		$param['catatan']	= $note;

		// check user is registered ?
		$checkUser 		= $this->general->getDataById($userId, 'user_akses');
		if (!empty($checkUser) && array_search($this->user->role, $this->auth->roleCode) == 'santri') {
			if (!empty($teacherId)) {
				if (empty($id)) {
					// insert data
					$proccess = $this->general->insertData('transaction', $param);
				} else {
					// update data
					$check 		= $this->general->getDataById($id, 'transaction');
					if (!empty($check)) {
						$proccess 	= $this->general->updateData($id, 'transaction', $param);
					} else {
						$response['message']	= 'Data tidak ditemukan';
					}
				}
	
				if ($proccess) {
					$response['status']		= true;
					$response['message']	= 'Data berhasil disimpan';
				}
			} else {
				$response['message']	= 'Silahkan pilih guru terlebih dahulu';
			}
		} else {
			$response['message']	= 'Kamu tidak memiliki hak akses';
		}
		$this->response($response, 200);
	}
}
?>