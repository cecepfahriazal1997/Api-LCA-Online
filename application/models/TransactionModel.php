<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth model.
 * 
 * @author Cecep Rokani
 */
use \Firebase\JWT\JWT;
class TransactionModel extends CI_Model {
	function __construct() {
        parent::__construct();
	}

	public function getListTransaction($userId, $role) {
		if ($role == 'santri') {
			$this->db->select('transaction.*, user_akses.nama, user_akses.foto, user_akses.lat_long as lat_lng_guru, IF(kehadiran_guru.id is not null, "1", "0") as presence, kehadiran_guru.catatan', false);
			$this->db->join('user_akses', 'user_akses.id = transaction.id_guru', 'inner');
			$this->db->join('kehadiran_guru', 'transaction.id = kehadiran_guru.id_transaction', 'left');
			$this->db->where('transaction.id_santri', $userId);
		} elseif ($role == 'ustad') {
			$this->db->select('transaction.*, user_akses.nama, user_akses.foto, IF(kehadiran_guru.id is not null, "1", "0") as presence, kehadiran_guru.catatan', false);
			$this->db->join('user_akses', 'user_akses.id = transaction.id_santri', 'inner');
			$this->db->join('kehadiran_guru', 'transaction.id = kehadiran_guru.id_transaction AND kehadiran_guru.id_user = ' . $userId, 'left');
			$this->db->where('transaction.id_guru', $userId);
		}
		$this->db->order_by('transaction.create_at', 'desc');
		$this->db->order_by('user_akses.nama', 'asc');
		$this->db->group_by('transaction.id');
		return $this->db->get('transaction')->result_array();
	}
}
?>