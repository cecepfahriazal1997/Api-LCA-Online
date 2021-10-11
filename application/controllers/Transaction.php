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
			foreach ($list as $key => $row) {
				$list[$key]['tipe']		= ucwords($row['tipe']);
				$list[$key]['waktu']	= date('d M y - H:i');
				$list[$key]['jam']		= date('H:i', strtotime($row['hour']));
			}
			$response['status']		= true;
			$response['message']	= '';
			$response['data']	 	= $this->general->replaceArrayNull($list);
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

	public function flagTransaction_post() {
		$response 	= array('status' => false, 'message' => 'Data gagal diperbaharui');
		$userId		= $this->user->id;
		$post		= $this->post(null, true);
		$id			= $post['id'];
		$status		= $post['status'];
		$lat		= $post['lat'];
		$lng		= $post['lng'];

		$check		= $this->general->getDataById($id, 'transaction');
		if (!empty($check->id)) {
			if ($check->id_guru == $userId) {
				if (empty($lat) || empty($lng)) {
					$response['message']	= 'Aktifkan lokasi kamu';
				} else {
					if ($status == 'wait')
						$status = 'confirm';
					elseif ($status == 'confirm')
						$status = 'on_the_way';
					elseif ($status == 'on_the_way')
						$status = 'on_progress';
					elseif ($status == 'on_progress')
						$status = 'done';
	
					$isAllow = true;
					// presence proccess
					if ($status == 'done') {
						$latLngExp = explode(",", $check->lat_lng);
						$lat2 = $latLngExp[0];
						$lng2 = $latLngExp[1];
						$distance = $this->general->distance($lat, $lng, $lat2, $lng2);
						$isAllow = ($distance <= 100);
					}
	
					if ($isAllow) {
						if ($this->general->updateData($id, 'transaction', array('status' => $status))) {
							$response['status'] 		= true;
							$response['message'] 		= 'Data berhasil diperbaharui';
							$response['data']['status']	= $status;
						}
					} else {
						$response['message']	= 'Kamu tidak ada dilokasi';
					}
				}
			} else {
				$response['message']	= 'Kamu tidak memiliki hak akses';
			}
		}
		$this->response($response, 200);
	}

	public function delete_post() {
		$response 	= array('status' => false, 'message' => 'Data gagal dihapus');
		$userId		= $this->user->id;
		$post		= $this->post(null, true);
		$id			= $post['id'];

		$check		= $this->general->getDataById($id, 'transaction');
		if (!empty($check->id)) {
			if ($check->id_santri == $userId) {
				if ($this->general->deleteData($id, 'transaction')) {
					$response['status'] 	= true;
					$response['message'] 	= 'Data berhasil dihapus';
				}
			} else {
				$response['message']	= 'Kamu tidak memiliki hak akses';
			}
		}
		$this->response($response, 200);
	}

	public function listTeacher_get() {
		$response 	= array('status' => false, 'message' => 'Data tidak ditemukan');
		$userId		= $this->user->id;
		$dataUser	= $this->general->getDataById($userId, 'user_akses');

		$list		= $this->general->getDataWhere('user_akses', 'id, nama, alamat, no_hp, foto, "0" as jarak', array('level_user' => '10', 'jenis_kelamin' => $dataUser->jenis_kelamin), 'list', array('nama', 'asc'));
		if (!empty($list)) {
			$response['status']		= true;
			$response['message']	= '';
			$response['data']		= $this->general->replaceArrayNull($list);
		}
		$this->response($response, 200);
	}

	public function presence_post() {
		$response 		= array('status' => false, 'message' => 'Absen gagal dilakukan');
		$userId			= $this->user->id;
		$transactionid 	= $this->post('id');
		$date 			= $this->post('date');
		$hour 			= $this->post('hour');
		$lat			= $this->post('lat');
		$lng			= $this->post('lng');
		$dateNow		= date('Y-m-d H:i:s');

		$transaction	= $this->general->getDataById($transactionid, 'transaction');
		if (!empty($transaction)) {
			$dateFrom 	= strtotime($transaction->tanggal);
			$dateAbsent = strtotime($date);
			if ($userId != $transaction->id_guru) {
				$response 		= array('status' => false, 'message' => 'Kamu tidak memiliki hak akses');
			} elseif ($dateFrom != $dateAbsent) {
				$response 		= array('status' => false, 'message' => 'Tanggal absen tidak sesuai');
			} elseif ($transaction->status != 'done') {
				$response 		= array('status' => false, 'message' => 'Pembelajaran belum selesai');
			} else {
				$latLngExp = explode(",", $transaction->lat_lng);
				$lat2 = $latLngExp[0];
				$lng2 = $latLngExp[1];
				$distance = $this->general->distance($lat, $lng, $lat2, $lng2);
				if ($distance <= 100) {
					$checkPresence	= $this->general->getDataWhere('kehadiran_guru', 'id, tanggal, jam', array('id_user' => $userId, 'id_transaction' => $transactionid), 'single');
					if (!empty($checkPresence)) {
						$response 		= array('status' => false, 'message' => 'Kamu telah melakukan absen pada ' . date('d F Y', strtotime($checkPresence->tanggal)) . ' ' . date('H:i', strtotime($checkPresence->jam)));
					} else {
						$param 						= array();
						$param['id_transaction']	= $transactionid;
						$param['id_user']			= $userId;
						$param['tanggal']			= $date;
						$param['jam']				= $hour;
						$param['status']			= 'hadir';
						$param['create_at']			= $dateNow;
	
						if ($this->general->insertData('kehadiran_guru', $param)) {
							$response	= array('status' => true, 'message' => 'Absen berhasil dilakukan');
						}
					}
				} else {
					$response	= array('status' => false, 'message' => 'Kamu tidak ada dilokasi');
				}
			}
		}

		$this->response($response, 200);
	}

	public function feedback_post() {
		$response 		= array('status' => false, 'message' => 'Feedback gagal disimpan');
		$userId			= $this->user->id;
		$transactionid 	= $this->post('id');
		$note			= $this->post('note');
		
		$checkPresence	= $this->general->getDataWhere('kehadiran_guru', 'id, tanggal, jam', array('id_transaction' => $transactionid), 'single');

		if (!empty($checkPresence)) {
			if ($this->general->updateData($checkPresence->id, 'kehadiran_guru', array('catatan' => $note))) {
				$response	= array('status' => true, 'message' => 'Feedback berhasil disimpan');
			}
		}
		$this->response($response, 200);
	}
}
?>