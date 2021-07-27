<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Auth controller.
 * 
 * @author Cecep Rokani
 */
use chriskacerguis\RestServer\RestController;

class Auth extends RestController {
	function __construct() {
		parent::__construct();
	}
	
	public function login_post() {
		$username			= $this->post('username');
		$password			= $this->post('password');
		$role				= $this->post('role');
		$tokenFirebase		= $this->post('tokenFirebase');

		$response			= $this->auth->login($username, $password, $role, $tokenFirebase);
		$this->response($response, 200);
	}

	public function register_post() {
		$post 		= $this->post(null, true);
		$name		= $post['name'];
		$placeBirth	= $post['place_birth'];
		$dateBirth	= $post['date_birth'];
		$gender		= $post['gender'];
		$phone		= $post['phone'];
		$email		= $post['email'];
		$latLng		= $post['lat_lng'];
		$address	= $post['address'];
		$role		= $post['role'];

		$response	= array('status' => false, 'message' => 'Pendaftaran gagal dilakukan, silahkan hubungi admin');
		//check exits users by email
		$checkEmailExists = $this->auth->checkUser($email);
		//check exits users by phone
		$checkPhoneExists = $this->auth->checkUserByPhone($phone);

		if ($checkEmailExists) {
			$response['message']	= 'Email telah terdaftar, gunakan email lain';
		} elseif ($checkPhoneExists) {
			$response['message']	= 'Nomor handphone telah terdaftar, gunakan nomor handphone lain';
		} else {
			// generate password
			$password				= $this->general->randomPassword(15);
			// register if data has validated
			$param					= array();
			$param['nama']			= $name;
			$param['email']			= $email;
			$param['username']		= $email;
			$param['password']		= $password;
			$param['password_asli']	= $password;
			$param['level_user']	= $this->auth->roleCode[$role];
			$param['tempat_lahir']	= $placeBirth;
			$param['tanggal_lahir']	= $dateBirth;
			$param['alamat']		= $address;
			$param['no_hp']			= $phone;
			$param['jenis_kelamin']	= $gender;
			$param['lat_long']		= $latLng;

			if ($this->general->insertData('user_akses', $param)) {
				$template = $this->load->view('template_email/register', $param, true);
				$this->auth->sendMail($email, 'Registrasi Akun Baru LCA Online', $template);
				$response['status'] 	= true;
				$response['message']	= 'Registrasi berhasil, silahkan cek email'; 
			}
		}
		$this->response($response, 200);
	}
}
?>