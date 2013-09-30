<?php
class PC_plugin_pc_shop_sort_products_widget extends PC_widget {
	
	public $plugin_name = 'pc_shop';

	protected $_template_group = 'sort';
	
	public function Init($config = array(), $product = null, $shop_products_site = null) {
		parent::Init($config);
		$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
	}
	
	protected function _get_default_config() {
		return array(
			'sort_options' => array(),
			'base_url' => '',
			'default_sort' => '',
			'order_var' => 'order',
			'order_dir_var' => 'dir',
			'list_type_row_var' => 'rows',
			'empty_sort_name' => '  --------  '
		);
	}

	
	public function get_menu() {
		$menu = array();
		
		$row_base_url = $base_url = $this->_config['base_url'];
		$query_string = $_SERVER['QUERY_STRING'];
		$query_string_without_row = preg_replace('/(\?)?(&)?('.$this->_config['list_type_row_var'].')/ui', '$1', $_SERVER['QUERY_STRING']);
			
		$row_base_url .= '?' . $query_string_without_row;
		if (strpos($base_url, '?') === false and !empty($query_string)) {
			$query_string = preg_replace('/(\?)?(&)?('.$this->_config['order_var'].'|'.$this->_config['order_dir_var'].')=[^=&]*/ui', '$1', $_SERVER['QUERY_STRING']);
			if (!empty($query_string)) {
				$base_url .= '?' . $query_string;
			}
		}
		
		$this->base_url_full = $row_base_url;
		$this->base_url_row_full = PC_utils::getUrl($row_base_url, array($this->_config['list_type_row_var'] => ''));
		
		if (empty($this->_config['default_sort'])) {
			$menu[] = array(
				'key' => '',
				'link' => $base_url,
				'name' => $this->_config['empty_sort_name']
			);
		}
		
		foreach ($this->_config['sort_options'] as $key => $sort_option) {
			$order_vars = array(
				$this->_config['order_var'] => $sort_option['var']
			);
			$order_vars[$this->_config['order_dir_var']] = $sort_option['dir'];
			$menu_item = array(
				'key' => $key,
				'link' => PC_utils::getUrl($base_url, $order_vars),
				'name' => $this->core->Get_plugin_variable('sort_by_' . $key, 'pc_shop')
			);
			if (isset($_GET[$this->_config['order_var']]) 
				and $_GET[$this->_config['order_var']] == $sort_option['var']
				and isset($_GET[$this->_config['order_dir_var']])
				and $_GET[$this->_config['order_dir_var']] == $sort_option['dir']
			) {
				$menu_item['active'] = true;
				$this->sort_option = $sort_option;
				$this->order_vars = $order_vars;
				$this->site->Register_data('pc_shop_sort_products_option', $sort_option);
			}
			$menu[] = $menu_item;
		}
		
		return $menu;
	}
	
	public function get_data() {
		$shop = $this->core->Get_object('PC_shop_site');
		$data = array(
			'menu' => $this->get_menu(),
			'sort_option' => v($this->sort_option, false),
			'get_vars' => v($this->order_vars, array()),
			'list_type_row' => isset($_GET['rows']),
			'base_url_full' => $this->base_url_full,
			'base_url_row_full' => $this->base_url_row_full
		);
		return $data;
	}
}