<?php

abstract class PC_plugin_pc_shop_widget extends PC_widget {
	
	public $plugin_name = 'pc_shop';
	
	public function Init($config = array()) {
		parent::Init($config);
		if (strpos($this->_template_group, ':_plugin/') === false) {
			$this->_template_group = ':_plugin/' . $this->plugin_name . '/' . $this->_template_group;
		}
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
			elseif ($this->site->loaded_page['controller'] == $this->plugin_name) {
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