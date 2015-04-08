<?php

class PC_shop_orders_admin_api extends PC_shop_admin_api {

	/**
	 * Plugin access is being checked
	 */
	protected function _before_action() {
		$this->_check_plugin_access();
		$this->shop = $this->core->Get_object('PC_shop_manager');
	}
	
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_order_model');
	}

	protected function _get_available_order_columns() {
		return array(
			'id' => 'id',
			'date' => 'date',
			'dateFormatted' => 'date',
			'name' => 'name',
			'is_paid' => 'is_paid',
			'status' => 'status',
			'payment_option' => 'payment_option',
			'delivery_option' => 'delivery_option',

		);
	}
	
	protected function _get_available_filters() {
		return array(
			'order_id' => 'o.id',
			'date_from' => array(
				'field' => 'o.date',
				'op' => '>=',
				'model_filters' => array('strtotime')
			),
			'date_to' => array(
				'field' => 'o.date',
				'op' => '<=',
				'model_filters' => array('strtotime', create_function('$v', 'return $v + 86400;'))
			),
			'search_phrase' => array(
				'callback' => array($this, '_adjust_search_by_search_phrase')
			)
		);
	}
	
	protected function _adjust_search_by_search_phrase($search_phrase, &$params) {
		//---
		if (!empty($search_phrase)) {
			$text_fields = array('o.name', 'o.email', 'o.address', 'o.phone', 'o.comment', 'o.data');
			$or_where = array();
			foreach ($text_fields as $key => $value) {
				$or_where[] = $value . ' like ?';
				$params['query_params'][] = '%' . $search_phrase . '%';
			}
			if (!empty($or_where)) {
				$params['where'][] = '(' . implode(' OR ', $or_where) . ')';
			}
		};
	}
	
	public function get() {
		$g_p = array_merge($_GET, $_POST);
		$start = (int) v($g_p['start']);
		$limit = (int) v($g_p['limit']);
		if ($start < 0)
			$start = 0;
		if ($limit < 1)
			$limit = v($items_per_page, 10);
		
		$paging = false;
		
		if ($limit != 0) {
			$paging = array(
				'perPage' => $limit,
				'start' => $start
			);
		}
		
		
		$date_from = v($_POST['date_from']);
		$date_to = v($_POST['date_to']);
		$search_phrase = v($_POST['search_phrase']);
		$site_id = v($_POST['site']);
		if (!ctype_digit($site_id))
			$site_id = 0; //all sites
			
		//$ln = $routes->Get(3);
		//---
		//
				$where = array();
		$parameters = array();
		//---
		if (!empty($date_from)) {
			if (!empty($date_to)) {
				$where[] = 'date between ? and ?';
				array_push($parameters, strtotime($date_from), strtotime($date_to) + 86400);
			} else {
				$where[] = 'date >= ?';
				$parameters[] = strtotime($date_from);
			}
		} elseif (!empty($date_to)) {
			$where[] = 'date <= ?';
			$parameters[] = strtotime($date_to) + 86400;
		}
				
		if ($site_id > 0) {
			$where[] = 'site=?';
			$parameters[] = $site_id;
		}
		$this->shop = $this->core->Get_object('PC_shop_manager');
		
		$scope = array(
			'where' => $where,
			'query_params' => $parameters
		);
		$this->_model = $model = $this->_get_model();
		$this->_adjust_search($scope);
		
		$params = array(
			'paging' => &$paging,
			'scope' => $scope
		);
		
		
		//print_pre($params);
		$this->_adjust_order_params($params);
		$this->_out['list'] = $this->shop->orders->Get(null, $params);
		$this->_out['total'] = $paging->Get_total();
		
		if (isset($_GET['callback'])) {
			echo $_GET['callback'] . "(" . json_encode($this->_out) . ")";
			exit;
		}
		
	}

	public function edit() {
		$id = v($_POST['id']);
		$data = json_decode(v($_POST['data'], '{}'), true);
		$order_model = null;
		$order = null;
		if( $id && isset($data['other']['status']) ) {
			/** @var PC_shop_order_model $order_model */
			$order_model = $this->core->Get_object('PC_shop_order_model');
			$order = $order_model->get_one(array('id' => $id));
		}

		parent::edit();

		if( $order && $order['coupon_id'] && $this->_out['success'] ) {
			$q = null;
			if( $order['status'] != PC_shop_order_model::STATUS_CANCELED && $data['other']['status'] == PC_shop_order_model::STATUS_CANCELED ) {
				// decrement coupon usage since order was cancelled
				$q = "UPDATE {$this->db_prefix}shop_coupons SET used = used - 1 WHERE id = ?";
			}
			else if( $order['status'] == PC_shop_order_model::STATUS_CANCELED && $data['other']['status'] != PC_shop_order_model::STATUS_CANCELED ) {
				// increment coupon usage since order was restored
				$q = "UPDATE {$this->db_prefix}shop_coupons SET used = used + 1 WHERE id = ?";
			}

			if( $q ) {
				$r = $this->db->prepare($q);
				if( !$r->execute($p = array($order['coupon_id'])) )
					throw new \Profis\Db\DbException($r->errorInfo(), $q, $p);
			}
		}
	}

	public function edit_() {
		$data = json_decode(v($_POST['changes'], '{}'), true);
		if (isset($data['is_paid_icon'])) {
			$data['is_paid'] = $data['is_paid_icon'];
		}

		$fields = array('is_paid', 'status');
		//$fields = array('is_paid', 'status', 'payment_option_id', 'delivery_option_id');
		
		$data = PC_utils::filterArray($fields, $data);

		if (empty($data)) {
			return;
		}
		$order_model = $this->core->Get_object('PC_shop_order_model');
		$order_model->update($data, array(
			'where' => array(
				'id = ?',
			),
			'query_params' => array(
				intval($_POST['id'])
			)
		));
	}
	
	public function delete() {
		$ids = json_decode(v($_POST['ids'], '{}'), true);
		foreach ($ids as  $id) {
			$this->shop->orders->Delete($id);
		}
		$this->_out['success'] = true;
	}
	
}

?>
