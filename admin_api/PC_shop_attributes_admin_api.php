<?php

class PC_shop_attributes_admin_api extends PC_shop_admin_api {
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_attribute_model');
	}
	
	/**
	 * Plugin access is being checked only for some actions
	 */
	protected function _before_action() {
		$allowed_actions = array('getWithValues', 'getForItem', 'getSuggestions');
		if (!in_array($this->_method, $allowed_actions)) {
			$this->_check_plugin_access();
		}
		
		$this->shop = $this->core->Get_object('PC_shop_manager');
	}

	protected function _after_action_success() {
		if (!in_array($this->_method, array('get', 'getWithValues', 'getForItem', 'getSuggestions'))) {
			$this->_init_data_change_hook();
		}
	}
	
	public function get() {
		$paging = array(
			'start' => v($_POST['start'], 0),
			'perPage' => v($_POST['limit'], 1000)
		);
		$params = array('paging' => &$paging);
		//$search = v($_POST['params']);
		//if (!is_null($search)) $params['filter'] = array('c.name like ?'=> $search);
		$this->_out['list'] = $this->shop->attributes->Get(null, $params);
		$this->_out['total'] = $paging->Get_total();
	}

	public function getWithValues() {
		$params = array('includeValues' => true);
		$this->_out = $this->shop->attributes->Get(null, $params);
	}

	public function getForItem() {
		$itemId = v($_POST['itemId']);
		$type = v($_POST['type']);
		if ($type == 'category') {
			$this->_check_category_access($itemId);
			$type = PC_shop_attributes::ITEM_IS_CATEGORY;
		}
		else if ($type == 'product') {
			$type = PC_shop_attributes::ITEM_IS_PRODUCT;
		}
		else {
			$this->_out['error'] = 'Invalid item type';
			return;
		}
		$params = array();
		$this->_out = $this->shop->attributes->Get_for_item($itemId, $type, $params);
	}

	public function delete() {
		if (!isset($_POST['id'])) {
			$this->_out['error'] = 'Invalid ID specified';
			return;
		}
		$params = array();
		$this->_out['success'] = $this->shop->attributes->Delete($_POST['id'], $params);
		if (!$this->_out['success']) {
			$this->_out['error'] = $params->errors->Get();
		}
	}

	public function create() {
		$params = array();
		$names = json_decode(v($_POST['names'], '{}'), true);
		$this->_out['success'] = $this->shop->attributes->Create(v($_POST['is_category_attribute']), $names, $params);
		if ($this->_out['success']) {
			$this->_out = array_merge($this->_out, $this->shop->attributes->Get($this->_out['success']));
		}
		else
			$this->_out['error'] = $params->errors->Get();
	}

	public function save() {
		$params = array();
		$id = v($_POST['id']);
		$data = array(
			'names' => json_decode(v($_POST['names'], '{}'), true),
			'is_category_attribute' => v($_POST['is_category_attribute']),
			'is_searchable' => v($_POST['is_searchable']),
			'is_custom' => v($_POST['is_custom']),
			'ref' => v($_POST['ref']),
			'category_id' => v($_POST['category_id'])
		);
		$this->_out['success'] = $this->shop->attributes->Edit($id, $data, $params);
		if (!$this->_out['success'])
			$this->_out['error'] = $params->errors->Get();
	}

	public function getSuggestions() {
		$this->_out = $this->shop->attributes->Get_suggestions(v($_POST['attributeId']), 20);
	}

	public function values() {
		switch ($this->routes->Get(3)) {
			case 'save':
				$values = json_decode(v($_POST['values'], '{}'), true);
				$this->_out['success'] = $this->shop->attributes->Edit_value(v($_POST['id']), $values);
				break;
			case 'create':
				$params = array();
				$values = json_decode(v($_POST['values'], '{}'), true);
				$this->_out['success'] = $this->shop->attributes->Assign_value(v($_POST['attribute_id']), $values, $params);
				if ($this->_out['success']) {
					$this->_out['id'] = $this->_out['success'];
				}
				else
					$this->_out['error'] = $params->errors->Get();
				break;
			case 'get':
				$paging = array(
					'start' => v($_POST['start'], 0),
					'perPage' => v($_POST['limit'], 1000)
				);
				$params = array('paging' => &$paging);
				$this->_out['list'] = $this->shop->attributes->Get_values(v($_POST['attribute_id'], null), $params);
				if (count($this->_out['list'])) {
					foreach ($this->_out['list'] as &$i) {
						foreach ($i['names'] as $ln => $name) {
							$i['value_' . $ln] = $name;
						}
					}
				}
				$this->_out['total'] = $paging->Get_total();
				break;
			case 'delete':
				if (!isset($_POST['id'])) {
					$this->_out['error'] = 'Invalid ID specified';
					break;
				}
				$params = array();
				$this->_out['success'] = $this->shop->attributes->DeleteValue($_POST['id'], $params);
				if (!$this->_out['success'])
					$this->_out['error'] = $params->errors->Get();
				break;
			default: $this->_out['error'] = 'Unknown action';
		}
	}

}

?>
