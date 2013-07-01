<?php 

class PC_plugin_pc_shop_category_menu_widget extends PC_vmenu_widget {
	
	public $plugin_name = 'pc_shop';


	public function get_template_group() {
		 return parent::get_template_group() . ':_plugin/' . $this->plugin_name . '/category_menu';
	}
	
	public function get_data() {
		$data = array();
		
		$params_array = $params = array(
			'parse' => array(
				//'description' => true,
				//'attributes' => true,
				//'recources' => true,
			),
			'full_links' => true
		);
		
		$this->shop = $this->core->Get_object("PC_shop_site");
		$data['menu']  = $this->shop->categories->Get(null, 0, $this->page->Get_id(), $params);
		
		$this->_build_menu($data['menu'], $params_array);
		
		return $data;
	}
	
	protected function _build_menu(&$menu, $params) {
		$params_array = $params;
		foreach ($menu as $key => $menu_item) {
			if ($menu_item["published"] != 1) continue; 
			if ($menu_item["nomenu"] == 1) continue; 
			if (trim($menu_item["name"]) == "") continue; 

			if ($this->site->Is_opened($menu_item['id'], 'id', 'pc_shop')) {
				$menu[$key]['_active'] = true;
				$menu[$key]['_submenu'] = $this->shop->categories->Get(null, $menu_item['id'], null, $params);
				$this->_build_menu($menu[$key]['_submenu'], $params_array);
				break;
			}
			
		}
	}
	
}