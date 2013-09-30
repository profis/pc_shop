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
	
	public function get_price(&$data, &$discount = 0, &$percentage_discount = 0) {
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
				$discount_percent_price = ceil($full_price * (100 - $data['percentage_discount'])) / 100;
				if ($discount_percent_price < $price) {
					$price = $discount_percent_price;
				}
			}
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
