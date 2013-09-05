<?php

class PC_shop_ln_currencies_admin_api extends PC_shop_admin_api {

	protected $_default_order = 'position';
	
	protected $_ln;
	
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_ln_currency_model');
	}
	
	protected function _before_action() {
		$this->_ln = v($_POST['ln'], v($this->route[3]));
		parent::_before_action();
	}
	
	protected function _adjust_search(&$params) {
		$params['where']['ln'] = v($_POST['ln'], v($this->route[3]));
		vv($params['join'], array());
		$params['join'][] = "LEFT JOIN {$this->db_prefix}shop_currencies sc ON sc.id = t.c_id";
		$params['select'] = 't.*, sc.code';
	}
	
	protected function _before_insert(&$data, &$content) {
		$data['ln'] = $this->_ln;
		$data['position'] = 1 + $this->_model->get_all(array(
			'select' => 'max(t.position) as max_position',
			//'query_only' => true,
			'value' => 'max_position',
			'limit' => 1
		));
	}
	
	public function test() {
		$model = $this->_get_model();
		$params = array(
			'select' => 'max(t.position) as max_position',
			//'query_only' => true,
			'value' => 'max_position',
			'limit' => 1
		);
		echo $result = $model->get_all($params);
	}
	
}

?>
