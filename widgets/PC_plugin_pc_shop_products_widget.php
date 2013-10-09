<?php 

class PC_plugin_pc_shop_products_widget extends PC_plugin_pc_shop_widget {

	protected $_template_group = 'products';
	
	protected $_category_id;
	
	protected function _get_default_config() {
		return array(
			'category' => false,
			'params' => array(),
			'per_page' => 10,
			'per_row' => 0,
			'list_item_thumb_type' => 'small',
			'sort_options' => array(
				'price_asc' => array('field' => 'real_price', 'var' => 'price', 'dir' => 'asc'),
				'price_desc' => array('field' => 'real_price', 'var' => 'price', 'dir' => 'desc'),
				'name_asc' => array('field' => 'pc.name', 'var' => 'name', 'dir' => 'asc'),
			)
		);
	}
	
	public function get_params() {
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
		
		vv($params['flags'], array());
		$params['flags'][] = PC_shop_products::PF_PUBLISHED;
		
		$params['paging'] = array(
			'perPage' => $this->_config['per_page'],
			'page' => $paging_cr_pg,
			'limit' => $this->_config['per_page'],
		);
		$params['parse'] = array(
			//'description' => true,
			//'attributes' => true,
			//'recources' => true,
			'recources' => 'first_img_only'
		);
		$params['full_links'] = true;
		return $params;
	}
	
	public function get_data() {
		$shop_products_site = $this->core->Get_object('PC_shop_products_site');
		$shop_products_site->debug = true;
		$shop_products_site->set_instant_debug_to_file($this->cfg['path']['logs'] . 'pc_shop/widget_products.html');
		$params = $this->get_params();
		$get_vars = false;
		$this->sort_widget = false;
		$this->sort_widget_data = false;
		
		$category_id = null;
		$base_url = $this->site->Get_link();
		if (isset($this->_config['category'])) {
			if (is_array($this->_config['category'])) {
				$category_id = $this->_config['category']['id'];
				$base_url =$this->_config['category']['full_link'];
			}
			else {
				$category_id = $this->_config['category'];
			}
			
		}
		//$base_url = PC_utils::getCurrUrl();
		
		if ($this->_config['sort_options']) {
			$this->sort_widget = new PC_plugin_pc_shop_sort_products_widget(array(
				'base_url' => $this->_get_url(),
				'sort_options' => $this->_config['sort_options']
			));
			$this->sort_widget_data = $this->sort_widget->get_data();
			if ($this->sort_widget_data['sort_option']) {
				$params['order_by'] = $this->sort_widget_data['sort_option']['field'];
				if (isset($this->sort_widget_data['sort_option']['dir'])) {
					$params['order_direction'] = $this->sort_widget_data['sort_option']['dir'];
				}
				$get_vars = $this->sort_widget_data['get_vars'];				
			}
		}
		//print_pre($params);
		//echo $category_id; 
		$data = array(
			'items' => $shop_products_site->Get(null, $category_id, $params),
			'params' => $params,
			'base_url' => $base_url
		);
		if ($get_vars) {
			$data['get_vars'] = $get_vars;
		}
		return $data;
	}
	
}