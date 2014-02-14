<?php

class PC_shop_product_model extends PC_model {
	
	const STATE_DEFAULT = 0;
	const STATE_IMPORTING = 2;
	
	/**
	 *
	 * @var PC_shop_price
	 */
	public $price;
	
	public function Init($id = 0) {
		parent::Init($id);
		$this->price = $this->core->Get_object('PC_shop_price');
	}
	
	protected function _set_tables() {
		$this->_table = 'shop_products';
		
		$this->_content_table = 'shop_product_contents';
		$this->_content_table_relation_col = 'product_id';
	}
	
	protected function _adjust_page_scope(&$params, $page_id) {
		
		$category_model = $this->core->Get_object('PC_shop_category_model');
		$category_model->absorb_debug_settings($this);
		
		$page_categories_params = array(
					'select' => 't.id, t.lft, t.rgt',
					'where' => array(),
					'query_params' => array()
				);
		$category_model->adjust_page_scope($page_categories_params, $page_id);
		
		$page_categories = $category_model->Get_all($page_categories_params);
		
		$params['where'][] = PC_database_tree::get_between_condition($page_categories, $params['query_params'], 'c');
		
	}
		
	public function get_price_select($table = null) {
		if (is_null($table)) {
			$table = 't';
		};
		if (!empty($table)) {
			$table .= '.';
		}
		return "LEAST({$table}price, ({$table}price - IFNULL({$table}discount, 0)), ROUND({$table}price * (100 - IFNULL({$table}percentage_discount, 0)) / 100, 2))";
		
	}
	
	public static function format_price($price) {
		return number_format($price, 2, ".", "");
	}
	
	public function get_attributes_info(&$data, $attributes, $attributes_string = '') {
		$attributes_string = '';
		$attributes_strings = array();
		$attributes_info = array();
		print_pre($this->price_attribute_refs);
		print_pre($data);
		foreach ($attributes as $attribute_id => $attribute_value_id) {
			if (v($this->price_attribute_refs) and v($this->price_attribute_refs[$attribute_id])) {
				$ref = $this->price_attribute_refs[$attribute_id];
			}
			//$attributes_strings[] = $data["multiple_attributes"][$ref][$price_attribute["attribute_value_id"]]['name'] . ' - ' . $data["multiple_attributes"][$ref][$price_attribute["attribute_value_id"]]['value'];
			//$attributes_strings[] = 
			$attributes_info[$key] = array(
				''
			);
		}
	}
	
	public function get_price(&$data, &$discount = 0, &$percentage_discount = 0, $attributes = array()) {
		$discount = 0;
		$percentage_discount = 0;
		$full_price = $price = $data['price'];
		$user_currency = $this->price->get_user_currency();
		$user_currency_id = $this->price->get_user_currency_id();
		if ($user_currency != $this->price->get_base_currency()) {
			if (isset($data['prices'][$user_currency_id]) and $data['prices'][$user_currency_id] > 0) {
				$full_price = $price = $data['prices'][$user_currency_id];
			}
			else {
				$full_price = $price = $this->price->get_converted_price_in_currency($full_price, $user_currency, true);
			}
			return number_format($full_price, 2, ".", "");;
		}
		
		if (true or v($data['hot'])) {
			if (v($data['discount']) and $data['discount'] > 0 and $data['discount'] < $price) {
				$price -= $data['discount'];
			}
			if (v($data['percentage_discount']) and $data['percentage_discount'] > 0 and $data['percentage_discount'] < 100) {
				$discount_percent_price = floor($full_price * (100 - $data['percentage_discount'])) / 100;
				if ($discount_percent_price < $price) {
					$price = $discount_percent_price;
				}
			}
		}
		
		if (!empty($attributes)) {
			$price_data = $this->adjust_price($price, $data, $attributes);
			$price = $price_data['price'];
		}
		
		$discount = $full_price - $price;
		if ($discount and $full_price) {
			$percentage_discount = $discount * 100 / $full_price;
		}
		if ($price < 0) {
			$price = 0;
		}
		return number_format($price, 2, ".", "");;
	}
	
	public function adjust_price($price, &$data, $attributes = array()) {
		//echo 'adjust price';
		$discount = 0;
		$percentage_discount = 0;
		
		$additional_discount = 0;
		if ($price == $data['real_price'] and $data['price'] > $data['real_price']) {
			$additional_discount = $data['price'] - $data['real_price'];
			$price = $data['price'];
		}
		
		$full_price = $price;
		
		$price_data = array(
			'price' => $price,
			'full_price' => $price,
			'discount' => $discount,
			'percentage_discount' => $percentage_discount,
		);
		
		
		$attributes_strings = array();
		$attribute_values_strings = array();
		$attributes_info = array();
		$post_price_attributes = array();
		//$cart_item['attributes_info'] = $this->shop->products->get_attributes_info($p, $cart_item['attributes']);
		//print_pre($attributes);
		//print_pre($this->price_attribute_refs);
		if (is_array($attributes)) {
			foreach ($attributes as $attribute_id => $attribute_value_id) {
				$ref = v($this->price_attribute_refs[$attribute_id]);
				if (!$ref) {
					continue;
				}
				if (v($data['price_attributes'][$ref]) and v($data['price_attributes'][$ref][$attribute_value_id])) {
					$attribute_price_data = $data['price_attributes'][$ref][$attribute_value_id];
					$price += $attribute_price_data['price_diff'];
					if ($attribute_price_data['price'] > 0) {
						$price = $attribute_price_data['price'];
						$additional_discount = 0;
					}
					$full_price = $price;
					$discount = v($attribute_price_data['discount']);
					if ($discount and $price) {
						//$percentage_discount = $discount * 100 / $price;
						$price -= $discount;
					}
					if (isset($data["multiple_attributes"][$ref][$attribute_value_id])) {
						$attributes_strings[] = $data["multiple_attributes"][$ref][$attribute_value_id]['name'] . ' - ' . $data["multiple_attributes"][$ref][$attribute_value_id]['value'];
						$attribute_values_strings[] = $data["multiple_attributes"][$ref][$attribute_value_id]['value'];
						
						//$attributes_strings[] = 
						$attributes_info[$attribute_id] = array(
							'price_data' => $attribute_price_data,
							'data' => $data["multiple_attributes"][$ref][$attribute_value_id]
						);
						
					}
					
					$post_price_attribute_value = $attribute_value_id;
					$post_price_attributes[$attribute_id] = $attribute_value_id;
				}

			}
		}
		
		//print_pre($attributes_strings);
		
		if ($additional_discount > 0) {
			$additional_discount;
			$price -= $additional_discount;
			$discount += $additional_discount;
		}
		
		$price_data['price'] = $price;
		$price_data['full_price'] = $full_price;
		$price_data['discount'] = $discount;
		$price_data['percentage_discount'] = $discount * 100 / $price;
		$price_data['attributes_info'] = $attributes_info;
		$price_data['attributes_string'] = implode('; ', $attributes_strings);
		$price_data['attribute_values'] = $attribute_values_strings;
		$price_data['attribute_values_string'] = implode('; ', $attribute_values_strings);
		$price_data['post_price_attributes'] = $post_price_attributes;
		//print_pre($price_data);
		return $price_data;
	}
	
	public function get_full_price(&$data) {
		return number_format($data['price'], 2, ".", "");
	}
	
	public function get_discount_percent(&$data, $price = null, $full_price = null) {
		if (is_null($full_price)) {
			$full_price = $this->get_full_price($data);
		}
		if (is_null($price)) {
			$price = $this->get_price($data);
		}
		if ($price == 0) {
			return 0;
		}
		return floor(($full_price - $price) / $full_price * 100);
	}
	
	
	public function get_eligible_coupon_discount($coupon_data, $total_item_price, $data) {
		$discount = 0;
		
		//print_pre($coupon_data);
		//print_pre($data);
				
		if (!$coupon_data['is_for_hot']) {
			if ($data['hot'] or $data['real_price'] < $data['price']) {
				//echo 'hot';
				return 0;
			}
			
			
		}
		if ($coupon_data['category_id']) {
			$category_model = new PC_shop_category_model();
			if (!$category_model->is_descendant($data['category_id'], $coupon_data['category_id'])) {
				//echo 'wrong category';
				return 0;
			}
		}
		
		if ($coupon_data['percentage_discount'] > 0) {
			$discount = $total_item_price * $coupon_data['percentage_discount'] / 100;
			if ($discount > $total_item_price) {
				$discount = $total_item_price;
			}
			return $discount;
		}
		
		if ($coupon_data['discount'] > 0) {
			$discount = $coupon_data['discount'];
			if ($discount > $total_item_price) {
				$discount = $total_item_price;
			}
			return $discount;
		}
		
		$price_model = $this->core->Get_object('PC_shop_price_model');
		
		return $discount;
	}
	
	public function get_related_product_ids($product_id) {
		$query_params = array();
		$query_params[] = $product_id;
		$query_params[] = $product_id;
		
		$where_s = "(p_p.product_id = ? OR p_p.product_id_2 = ?)";

		$query = "SELECT * FROM {$this->db_prefix}shop_product_products p_p
			WHERE $where_s 
			";
			
		$this->debug_query($query, $query_params, 1);
		
		$r = $this->prepare($query);
		$s = $r->execute($query_params);
		
		$related_products = array();
		if ($s ) {
			while($d = $r->fetch()) {
				if ($d['product_id'] <> $product_id) {
					$related_products[$d['product_id']] = $d['product_id'];
				}
				elseif($d['product_id_2'] <> $product_id) {
					$related_products[$d['product_id_2']] = $d['product_id_2'];
				}
			}
		}
		return $related_products;
	}
	
	public function set_category_scope($category_id) {
		$this->_where[] = 't.category_id = ?';
		$this->_query_params[] = $category_id;
	}
	
	public function set_category_branch_scope($category_data) {
		return;
		$this->_where[] = PC_database_tree::get_between_condition_for_range(
			$category_data, 
			$this->_query_params, 
			$table = 't'
		);
	}

}
