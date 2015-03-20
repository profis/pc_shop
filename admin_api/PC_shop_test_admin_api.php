<?php

class PC_shop_test_admin_api extends PC_shop_admin_api {
	
	public function all_page_categories_children() {
		$page_model = new PC_page_model();
		$page_data = $page_model->get_all(array(
			'content' => true,
			'ln' => 'lt',
			'limit' => 1,
			'where' => 't.reference_id = ?',
			'query_params' => array(
				'automobiliai'
			)
		));
		$page_data['link'] = $this->page->Get_page_link_from_data($page_data);
		$shop = $this->core->Get_object('PC_shop_site');
		$page_id = $this->page->Get_page_id_by_reference('automobiliai');
		$params = array(
			'all_children' => array(
				'page_data' => $page_data
			)
		);
		$all = $shop->categories->Get(null, null, null, $params);
		print_pre($all);
	}
	
	public function search() {
		$shop = $this->core->Get_object('PC_shop_site');

		$attr_id_for_country = $shop->attributes->get_id_from_ref('keliones_salis');
		//$countries = $shop->attributes->Get_values($attr_id_for_country);
		
		$attr_value_model = new PC_shop_attribute_value_model();

		$countries = $attr_value_model->get_all(array(
			'content' => array(
				'select' => 'ct.value'
			),
			'where' => array(
				'attribute_id' => $attr_id_for_country
			)
		));
		
		print_pre($countries);
		
		$attr_id_for_travel_type = $shop->attributes->get_id_from_ref('keliones_tipas');
		$travel_types = $attr_value_model->get_all(array(
			'content' => array(
				'select' => 'ct.value'
			),
			'where' => array(
				'attribute_id' => $attr_id_for_travel_type
			)
		));
		
		print_pre($travel_types);
		
		
		$params = array();
		
		$params['filter'] = array(
			/*
			array(
				'field' => 'p.category_id',
				'query_param' => 731
			),
			 */
			/*
			array(
				'field' => 'p.id',
				'op' => 'IN',
				'value' => " (SELECT product_id FROM {$this->db_prefix}shop_product_periods WHERE time_from >= ? and time_from <= ?) ",
				'query_params' => array('2013-02-10', '2013-02-13')
			)
			*/
		);
		//$params['query_params'] = array('2013-02-10', '2013-02-13');
		
		$params['attribute_filter'] = array();		
				
		$last_minute_attr_id = $shop->attributes->get_id_from_ref('last_minute');
		if ($last_minute_attr_id) {
			$params['attribute_filter'][$last_minute_attr_id] = '';
		}
		
		//$params['attribute_filter'][$attr_id_for_country] = 70;	//Ispanija
		//$params['attribute_filter'][$attr_id_for_country] = 69;	//Italija
		$params['attribute_filter'][$attr_id_for_country] = 67;	//Rusija
		
		$params['attribute_filter'][$attr_id_for_travel_type] = array(73, 74); //Pazintine arba egzotine
		
		$products = $shop->products->Get(null,null, $params);
		
		print_pre($products);
		
	}
	
}

?>
