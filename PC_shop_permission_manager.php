<?php

final class PC_shop_permission_manager extends PC_base {
	private $subject, $subject_id, $ln;
	public function Init($subject, $subject_id=null, $ln=null) {
		$this->subject = $subject;
		if (!empty($subject_id)) $this->subject_id = $subject_id;
		$this->ln = $ln ? $ln : $this->site->ln;
	}
	

	static function Authorize_by_pid($data, $pid) {
		$r = $this->prepare("SELECT * FROM {$this->db_prefix}pages ");
	}
	
}

?>
