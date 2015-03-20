<?php

class PC_shop_categories_products_admin_api extends PC_shop_admin_api {

	protected $_default_order = 'position';
	
	
	protected function _get_model() {
		if ($this->_method == 'get') {
			$this->_content_fields = array(
				'name'
			);
			return $this->core->Get_object('PC_shop_product_model');
			
		}
		return $this->core->Get_object('PC_shop_category_product_model');
	}
	
	protected function _get_sync_fields() {
		return array('category_id', 'product_id', 'price');
	}
	
	protected function _before_action() {
		$this->category_id = intval(v($_POST['category_id'], v($this->route[3])));
	}
	
	protected function _adjust_search(&$params) {
		//$params['where']['attribute_id'] = $this->attribute_id;
		if (v($this->cfg['pc_shop_categories_products']) and v($this->cfg['pc_shop_categories_products']['ref'])) {
			$category_model = new PC_shop_category_model();
			$params['where']['category_id'] = $category_model->get_one(array(
				'where' => array(
					'external_id' => $this->cfg['pc_shop_categories_products']['ref']
				),
				'value' => 'id'
			));
		}
		$this->core->Init_hooks('pc_shop_categories_products/adjust_search', array(
			'params'=> &$params,
			'category_id' => $this->category_id
		));
	}
	
	protected function _after_get() {
		$model = $this->core->Get_object('PC_shop_category_product_model');
		$category_products = $model->get_all(array(
			'where' => array(
				'category_id' => $this->category_id,
			),
			'key' => 'product_id'
		));

		//print_pre($attribute_categories);
		foreach ($this->_out['list'] as $key => $value) {
			$this->_out['list'][$key]['product_id'] = $this->_out['list'][$key]['id'];
			$this->_out['list'][$key]['id'] = 0;
			$this->_out['list'][$key]['price'] = '';
			if (isset($category_products[$value['id']])) {
				$this->_out['list'][$key]['checked'] = true;
				$this->_out['list'][$key]['id'] = $category_products[$value['id']]['id'];
				$this->_out['list'][$key]['price'] = $category_products[$value['id']]['price'];
			}
		}
	
	}
	
}


?>