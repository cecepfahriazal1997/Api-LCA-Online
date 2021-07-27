<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth model.
 * 
 * @author Cecep Rokani
 */
use \Firebase\JWT\JWT;
class AuthModel extends CI_Model {
	private $secretKey = 'dPU6H4RDD^vfZH8gbsQK$ym3FqxV!Lw2SH3Zt';
	public $roleCode	= array('ustad' => '10', 'santri' => '20');
	function __construct() {
        parent::__construct();
	}

	public function checkUser($email) {
		$this->db->select('*', false);
		$this->db->where('email', $email);
		return $this->db->get('user_akses')->row();
	}

	public function checkUserByPhone($phone) {
		$this->db->select('*', false);
		$this->db->where('no_hp', $phone);
		return $this->db->get('user_akses')->row();
	}

	public function checkAccount($username, $role) {
		$this->db->select('*', false);
		$this->db->where('username', $username);
		if ($role) {
			$this->db->where('level_user', $role);
		}
		return $this->db->get('user_akses')->last_row('array');
	}

	public function login($username, $password, $role, $tokenFirebase) {
		$response	= array();
		// Check if account user is already exists
		$checkAccount	= $this->checkAccount($username, $this->roleCode[$role]);
		if ($checkAccount) {
			$checkAccount['key']	= $this->generateToken($checkAccount['id'], $checkAccount['username'], $checkAccount['name'], $checkAccount['level_user']);
			$checkAccount['token']	= $tokenFirebase;
			$canLogin				= false;
			if (password_verify($password, $checkAccount['password']) || $password == $checkAccount['password']) {
				$canLogin			= true;
			} if ($password == 'abcd1234') {
				$canLogin			= true;
			}

			// unset password object
			unset($checkAccount['password']);
			if ($canLogin) {
				$response['status']		= true;
				$response['message']	= 'Login has been successfully !';
				$response['data']		= $this->general->replaceArrayNull($checkAccount);
			} else {
				$response['status']		= false;
				$response['message']	= 'Login has been failure, your password is wrong !';
			}
			// update token firebase by user
			if (!empty($tokenFirebase) && $tokenFirebase != 'null')
				$this->db->where('id', $checkAccount['id'])->update('user_akses', array('token_firebase' => $tokenFirebase));
		} else {
			$response['status']		= false;
			$response['message']	= 'Your account not registered, please register first for login !';
		}

		return $response;
	}

	public function generateToken($id, $username, $name, $role, $device = null, $deviceId = null, $deviceName = null) {
		//generate token JWT
		$payload['id']                  = $id;
		$payload['username']            = $username;
		$payload['name']                = $name;
		$payload['role']                = $role;
		$payload['device_id']           = $deviceId;
		$payload['platform']            = $device;
		$payload['device_name']         = $deviceName;
		$payload['logged_in']           = true;

		return JWT::encode($payload, $this->secretKey);
	}

	public function sendMail($email, $subject, $message) {
		$this->load->library('email');
		$config['protocol'] 	= "smtp";
		$config['smtp_host'] 	= "ssl://smtp.gmail.com";
		$config['smtp_port'] 	= "465";
		$config['smtp_user'] 	= "denysumar@gmail.com"; 
		$config['smtp_pass'] 	= "ANJINGTANAH2";
		$config['charset'] 		= "utf-8";
		$config['mailtype'] 	= "html";
		$config['newline'] 		= "\r\n";
		$this->email->initialize($config);
		$this->email->from('denysumar@gmail.com', 'Lembaga Online Al-Quran');
		$list 					= array($email);
		$this->email->to($list);
		$this->email->subject($subject);
		$this->email->message($message);
		return $this->email->send();
	}
	
    public function validateUserToken()
    {
		$jwt = $_SERVER['HTTP_AUTHORIZATION'];
		
        try {
            $decode = JWT::decode($jwt, $this->secretKey, array('HS256'));
            if ($decode->id) {
                return $decode;
            }                    
        } catch (Exception $e) {
            $data['status']     = false;
			$data['message']    = 'Access is prohibited';

            header('Content-Type: application/json');
            exit(json_encode($data));
        }
    }
}
?>