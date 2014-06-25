<?php

class PC_plugin_pc_shop_product_widget extends PC_plugin_pc_shop_widget {
	
	public $plugin_name = 'pc_shop';

	protected $_template_group = 'product';
	
	public $shop_products_site;
	
	public $product;
	
	public function Init($config = array(), $product = null, $shop_products_site = null) {
		parent::Init($config);
		$this->product = $product;
		$this->shop_products_site = $shop_products_site;
		$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
	}
	
	protected function _get_default_config() {
		return array(
			//'thumb_types' => array('small',
		);
	}
	
	protected function _get_product_variants() {
		$variants = array();
		//print_pre($this->product);
		//print_pre($this->product['multiple_attributes']);
		//print_pre($this->product['price_attributes']);
		
		if (isset($this->product['price_attributes']) and !empty($this->product['price_attributes'])) {
			//print_pre($item['price_attributes']);
			//print_pre($item['multiple_attributes']);
			//print_pre($item);
			foreach ($this->product['price_attributes'] as $ref => $price_attribute_array) {
				//print_pre($price_attribute_array);
				$attribute_values_without_price = array_keys($this->product['multiple_attributes'][$ref]);
				$attribute_values_with_price = array();
				foreach ($price_attribute_array as $price_attribute) {
					if ($price_attribute['attribute_item_id']) {
						$attribute_values_with_price[] = '#' . $price_attribute['attribute_item_id'];
					}
					else {
						$attribute_values_with_price[] = $price_attribute['attribute_value_id'];
					}
					
				}
				
				$attribute_values_without_price = array_diff($attribute_values_without_price, $attribute_values_with_price);
				//print_pre($attribute_values_without_price);
				if (!empty($attribute_values_without_price)) {
					$multiple_attributes_array_keys = array_keys($this->product["multiple_attributes"][$ref]);
					$attribute_value_names_without_price = array();
					$attributes_info = array();
					$attributes_info[$ref] = array();
					foreach ($attribute_values_without_price as $a_value) {
						$attribute_value_names_without_price[] = $this->product["multiple_attributes"][$ref][$a_value]['value'];
					}
					$attribute_value_name_string_without_price = implode(' ', $attribute_value_names_without_price);
					
					
					
					$variants[] = array(
						'name' => $this->product['name'] . ' (' . $this->product["multiple_attributes"][$ref][$a_value]['name'] . ' - ' . $attribute_value_name_string_without_price . ')',
						'percentage_discount' => $this->product['percentage_discount'],
						'discount' => $this->product['discount'],
						'real_price' => $this->product['real_price'],
						'price' => $this->product['price'],
						'attribute_name' => $this->product["multiple_attributes"][$ref][$a_value]['name'],
						'attribute_values' => $attribute_value_names_without_price,
						'attributes_string' => $attribute_value_name_string_without_price
					);
				}
				foreach ($price_attribute_array as $price_attribute) {
					$price_attributes = array();
					if ($price_attribute["attribute_item_id"]) {
						$price_attributes[$price_attribute["attribute_id"]] = '#' . $price_attribute["attribute_item_id"];
					}
					else {
						$price_attributes[$price_attribute["attribute_id"]] = $price_attribute["attribute_value_id"];
					}
					//print_pre($price_attributes);
					$price_data = $this->shop->products->adjust_price($this->product['real_price'], $this->product, $price_attributes);
					$product_name =  $this->product['name'] . ' (' . $price_data['attributes_string'] . ')';
			
					$post_price_attributes = array();
					
					$variants[] = array(
						'name' => $product_name,
						'percentage_discount' => $price_data['percentage_discount'],
						'discount' => $price_data['discount'],
						'real_price' => $price_data['price'],
						'price' => $price_data['full_price'],
						'attribute_name' => $price_data['attributes_info'][$price_attribute["attribute_id"]]['data']['name'],
						'attribute_values' => $price_data['attribute_values'],
						'attributes_string' => $price_data['attribute_values_string'],
						'post_price_attributes' => $price_data['post_price_attributes'],
						'price_data' => $price_data
					);

				}
				break;
			}
		}
		else {
			$variants[] = array(
				'name' => $this->product['name'],
				'percentage_discount' => $this->product['percentage_discount'],
				'discount' => $this->product['discount'],
				'real_price' => $this->product['real_price'],
				'price' => $this->product['price'],
				'attribute_name' => '',
				'attribute_values' => array(),
				'attributes' => array(),
				'attributes_string' => ''
			);
			
		}
		//print_pre($variants);
		return $variants;
	}
	
	public function get_data() {
		$data = array(
			//'images' => $this->product['resources']->Get(false, 'small')
			'gallery_url' => $this->core->Get_url("gallery"),
			'item' => $this->product,
			'price' => number_format($this->shop_products_site->get_price($this->product, $discount, $percentage_discount), 2, ",", ""),
			'product_variants' => $this->_get_product_variants()
		);
		
		$data['discount'] = $discount;
		$data['percentage_discount'] = $percentage_discount;
		//print_pre($data);
		return $data;
	}
}
