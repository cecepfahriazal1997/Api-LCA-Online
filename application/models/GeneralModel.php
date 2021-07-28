<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * General model.
 * 
 * @author Cecep Rokani
 */
class GeneralModel extends CI_Model {
	function __construct() {
        parent::__construct();
	}

	public function insertData($table, $data) {
		$this->db->insert($table, $data);
		return $this->db->insert_id();
	}

	public function updateData($id, $table, $data) {
		if (!empty($id)) {
			$this->db->where('id', $id);
			return $this->db->update($table, $data);
		} else {
			return false;
		}
	}

	public function deleteData($id, $table) {
		if (!empty($id)) {
			$this->db->where('id', $id);
			return $this->db->delete($table);
		} else {
			return false;
		}
	}

	public function getData($table, $orderBy) {
		if ($orderBy)
			$this->db->order_by($orderBy[0], $orderBy[1]);
		return $this->db->get($table)->result();
	}

	public function getDataWhere($table, $select='*', $where, $type='single', $orderBy=array('id', 'asc')) {
		if ($type == 'list') {
			return $this->db->select($select)->order_by($orderBy[0], $orderBy[1])->get_where($table, $where)->result_array();
		} else {
			return $this->db->select($select)->order_by($orderBy[0], $orderBy[1])->get_where($table, $where)->last_row();
		}
	}

	public function getDataById($id, $table) {
		$this->db->where('id', $id);
		return $this->db->get($table)->row();
	}

	function randomNumber($len = 6){
		$alphabet = '1234567890';
		$password = array(); 
		$alpha_length = strlen($alphabet) - 1; 
		for ($i = 0; $i < $len; $i++) 
		{
			$n = rand(0, $alpha_length);
			$password[] = $alphabet[$n];
		}
		return implode($password); 
	}

	public function replacePhoneNumber($phone) {
        $separator = substr($phone,0,1);

        if($separator == 0) {
            $separator = "62".''.substr($phone,1);
        }
        else if($separator == 8) {
            $separator = "628".''.substr($phone,1);
        }
        else {
            $separator = $phone;
        }
        return $separator;
	}
	
	public function uploadSingleFile($title, $path, $extension='jpeg|jpg|png', $overwrite=true) {
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}

		chmod($path, 0777);

        $config = array(
            'upload_path'   => $path,
            'allowed_types' => $extension,
            'overwrite'     => $overwrite,
		);
		
		$this->load->library('upload', $config);
		$response		= array();
		
		$this->upload->initialize($config);
		if ($this->upload->do_upload($title)) {
			$data				= $this->upload->data();
			$param				= array();
			$param['name']		= $data['file_name'];
			$param['path']		= base_url().$path.'/'.$data['file_name'];
			$response			= $param;
		} else {
			echo $this->upload->display_errors();
		}

		return $response;
	}

	public function randomPassword($length = 8) {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < $length; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}
	
	public function uploadFile($files, $path, $extension='jpeg|jpg|png', $overwrite=true) {
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}

		chmod($path, 0777);

        $config = array(
            'upload_path'   => $path,
            'allowed_types' => $extension,
            'overwrite'     => $overwrite,
		);
		
		$this->load->library('upload', $config);
		$response		= array();
		$title			= array_keys($files)[0];

        foreach ($files['name'] as $key => $image) {
            $_FILES[$title]['name']	= $files['name'][$key];
            $_FILES[$title]['type']	= $files['type'][$key];
            $_FILES[$title]['tmp_name']= $files['tmp_name'][$key];
            $_FILES[$title]['error']	= $files['error'][$key];
            $_FILES[$title]['size']	= $files['size'][$key];

            $this->upload->initialize($config);

            if ($this->upload->do_upload($title)) {
				$data				= $this->upload->data();
				$param				= array();
				$param['name']		= $data['file_name'];
				$param['path']		= base_url().$path.'/'.$data['file_name'];
				$response[]			= $param;
			} else {
				echo $this->upload->display_errors();
			}
		}

		return $response;
	}

    public function replaceArrayNull($array) {
		foreach ($array as $key => $value) {
			if(is_array($value)) {
				$array[$key] = $this->replaceArrayNull($value);
			 } else {
				if (is_null($value))
					$array[$key] = "";
			}
		}
		return $array;
	}

	public function sendNotification($listToken, $title, $body, $action='android.intent.action.MAIN', $data=array()) {
		$api_access_key = 'AAAAhkc3-ZU:APA91bEBa0aJt2Pc0eOrk1g3y1_6Dt0C8iwf3gIZkP1k9G5UbzUN2ktU6Qd3kiMFI5xRXg880LT_qnQgpv69avqbkfAHugzrQ_W4FX5HQ6Fn_pNT7jxyuyQlqcn8xzJ_WfU2wYA5GWN4';
		$url = 'https://fcm.googleapis.com/fcm/send';
		$fields = array (
				"registration_ids" => $listToken,
				"notification" => array (
					"click_action" => $action,
					"body"  => $body,
					"title" => $title,
					"icon"  => "logo",
					"sound" => "default",
				), 
				"data" => array (
						"title" => $title,
						"message" => $body,
						"action" => $action
				),
				"android" => array(
				  "direct_boot_ok"=> true,
				),
		);

		if (!empty($data) && count($data) > 0) {
			$fields['notification']	= array_merge($fields['notification'], $data);
			$fields['data']			= array_merge($fields['data'], $data);
		}
		$fields = json_encode ( $fields );
		$headers = array (
				'Authorization: key=' . $api_access_key,
				'Content-Type: application/json'
		);
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		if (!empty($result)) {
			return json_decode($result, true);
		} else {
			return array();
		}
	}
}
?>