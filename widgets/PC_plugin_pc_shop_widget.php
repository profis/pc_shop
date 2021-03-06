<?php

abstract class PC_plugin_pc_shop_widget extends PC_widget {
	
	public $plugin_name = 'pc_shop';
	
	/**
	 *
	 * @var PC_shop_price 
	 */
	public $price;
	
	/**
	 *
	 * @var PC_shop_site
	 */
	public $shop;
	
	public function Init($config = array()) {
		parent::Init($config);
		if (strpos($this->_template_group, ':_plugin/') === false) {
			$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
		}
		if (isset($this->_config['category'])) {
			$this->currentCategory = $this->_config['category'];
		}

		$this->price = $this->core->Get_object('PC_shop_price');
		$this->shop = $this->core->Get_object('PC_shop_site');

		$this->site->Add_script('plugins/' . $this->plugin_name . '/js/number.format.min.js');
		$this->site->Add_script('plugins/' . $this->plugin_name . '/js/shop.js');
	}
	
	public function Get_variable($var) {
		return $this->core->Get_plugin_variable($var, $this->plugin_name);
	}
	
	protected function _get_url($page_only = false) {
		$url = v($this->_config['url']);
		if (empty($url)) {
			if (v($this->_config['page_id'])) {
				$url = $this->page->Get_page_link_by_id(v($this->_config['page_id']));
			}
			elseif (isset($this->_config['page_ref']) and !empty($this->_config['page_ref'])) {
				$url = $this->page->Get_page_link_by_reference($this->_config['page_ref']);
			}
			elseif (isset($this->site->loaded_page['controller']) && $this->site->loaded_page['controller'] == $this->plugin_name) {
				if ($page_only) {
					$url = $this->page->Get_current_page_link();
				}
				else {
					$url = $this->site->Get_link();
				}
			}
			else {
				$shop_pages = $this->page->Get_by_controller($this->plugin_name);
				if (count($shop_pages)) {
					$url = $this->page->Get_page_link_by_id($shop_pages[0]);
				}
			}
		}
		return $url;
	}
	
	
}