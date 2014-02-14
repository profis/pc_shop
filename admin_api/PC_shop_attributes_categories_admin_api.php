<?php

class PC_shop_attributes_categories_admin_api extends PC_shop_admin_api {

	protected function _get_model() {
		if ($this->_method == 'get') {
			$this->_content_fields = array(
				'name'
			);
			return $this->core->Get_object('PC_shop_attribute_category_model');
			
		}
		return $this->core->Get_object('PC_shop_attributes_category_model');
	}
	
	protected function _get_sync_fields() {
		return array('category_id', 'attribute_id');
	}
	
	protected function _before_action() {
		$this->attribute_id = intval(v($_POST['attribute_id'], v($this->route[3])));
	}
	
	protected function _adjust_search(&$params) {
		//$params['where']['attribute_id'] = $this->attribute_id;
		
	}
	
	protected function _after_get() {
		$this->debug('_after_get()');
		
		$model = $this->core->Get_object('PC_shop_attributes_category_model');
		
		$attribute_categories = $model->get_all(array(
			'where' => array(
				'attribute_id' => $this->attribute_id,
			),
			'key' => 'category_id'
		));
		
		//print_pre($attribute_categories);
		foreach ($this->_out['list'] as $key => $value) {
			$this->_out['list'][$key]['category_id'] = $this->_out['list'][$key]['id'];
			$this->_out['list'][$key]['id'] = 0;
			if (isset($attribute_categories[$value['id']])) {
				$this->_out['list'][$key]['checked'] = true;
				$this->_out['list'][$key]['id'] = $attribute_categories[$value['id']]['id'];
			}
		}
	
	}
	
	public function _get() {
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
	
}


?>