<?php 

class PC_plugin_pc_shop_order_history_widget extends PC_plugin_pc_shop_widget {
	
	protected $_template_group = 'order_history';
	
	public function Init($config = array()) {
		parent::Init($config);
		$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
	}
	
	protected function _get_default_config() {
		return array(
			'params' => array(
				'order' => 'date',
				'order_dir' => 'DESC'
			),
			'limit' => 0,
			'per_page' => 10,
		);
	}
	
	public function get_data() {
		global $site_users;
		
		if (v($_GET["page"])){
			$paging_cr_pg = $_GET["page"];
		} else {
			$paging_cr_pg = 1;
		}

		if (v($_GET["ppage"])){
			$paging_cr_ppg = $_GET["ppage"];
		} else {
			$paging_cr_ppg = 10;
		}

		$params = array();
		if (isset($this->_config['params']) and is_array($this->_config['params'])) {
			$params = $this->_config['params'];
		}
		
		if ($this->_config['per_page']) {
			$per_page = $this->_config['per_page'];
			$limit = $this->_config['per_page'];
			if ($this->_config['limit']) {
				$limit = $this->_config['limit'];
				if ($limit < $per_page) {
					$per_page = $limit;
					$this->_config['per_page'] = 0;
					$paging_cr_pg = 1;
				}
			}
			$params['paging'] = array(
				'perPage' => $per_page,
				'page' => $paging_cr_pg,
				'limit' => $limit,
			);
		}
		elseif($this->_config['limit']) {
			$params['paging'] = array(
				'perPage' => $this->_config['limit'],
				'page' => 1,
				'limit' => $this->_config['limit'],
			);
		}
		
		vv($params['filter'], array());
		$params['filter']['user_id'] = $site_users->GetID();
		
		$data = array(
			'orders' => $this->shop->orders->Get(null, $params),
			'params' => $params,
		);
		
		
		return $data;
	}
	
}