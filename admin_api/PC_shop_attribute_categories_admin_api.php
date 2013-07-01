<?php

class PC_shop_attribute_categories_admin_api extends PC_shop_admin_api {

	public function get_for_combo() {
		$attr_category_model = $this->core->Get_object('PC_shop_attribute_category_model');
		$attr_category_model->absorb_debug_settings($this);
		
		$this->_out = $attr_category_model->get_all(array(
			'select' => 't.id',
			'content' => array(
				'select' => 'ct.name'
			),
			'ln' => false
		));
		if (isset($_GET['empty'])) {
			array_unshift($this->_out, array(
				'id' => 0,
				'name' => ''
			));	
		}
	}
	
	public function get() {
		$paging = array(
			'start' => v($_POST['start'], 0),
			'perPage' => v($_POST['limit'], 1000)
		);
		$params = array('paging' => &$paging);
		$attr_category_model = $this->core->Get_object('PC_shop_attribute_category_model');
		$attr_category_model->absorb_debug_settings($this);
		
		$this->_out['list'] = $attr_category_model->get_all(array(
			'content' => array(
				'select' => 'ct.name'
			),
			'ln' => false
		));
		//$this->_out['total'] = $paging->Get_total();
	}
	
	public function create() {
		$attr_category_model = $this->core->Get_object('PC_shop_attribute_category_model');
		$attr_category_model->absorb_debug_settings($this);
		
		$data = json_decode(v($_POST['data'], '{}'), true);
		
		$this->debug($data);
		
		$content = array();
		
		foreach ($data['names'] as $ln => $name) {
			v($content[$ln], array());
			$content[$ln]['name'] = $name;
		}
		
		$id = $attr_category_model->insert($data['other'], $content);
			
		if ($id) {
			$this->_out['success'] = true;
			$this->_out['id'] = $id;
			$this->_out = array_merge($this->_out, $data['other']);
			$this->_out = array_merge($this->_out, $data);
		}
		

	}
	
	public function delete() {
		$this->debug('delete()');
		if (!isset($_POST['ids']) or empty($_POST['ids'])) {
			return;
		}
		$ids = json_decode(v($_POST['ids'], '{}'), true);
		$this->debug($ids);
		
		$attr_category_model = $this->core->Get_object('PC_shop_attribute_category_model');
		$attr_category_model->absorb_debug_settings($this);
		
		$deleted = $attr_category_model->delete(array(
			'where' => array(
				'id' => $ids
			)
		));
		
		if ($deleted) {
			$this->_out['success'] = true;
		}
	}
	
	public function edit() {
		$this->debug('edit()');
		
		$data = json_decode(v($_POST['data'], '{}'), true);
		
		$this->debug($data);
		
		$content = array();
		
		if (isset($data['names'])) {
			foreach ($data['names'] as $ln => $name) {
				v($content[$ln], array());
				$content[$ln]['name'] = $name;
			}
		}
		
		$new_data = $data['other'];
		
		$new_data['_content'] = $content;
		
		$attr_category_model = $this->core->Get_object('PC_shop_attribute_category_model');
		$attr_category_model->absorb_debug_settings($this);
		
		$params = array(
			'where' => array(
				'id' => intval($_POST['id'])
			)
		);
		
		$this->_out['success'] = $attr_category_model->update($new_data, $params);
		
	}
	
}


?>