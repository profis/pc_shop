<?php 

class PC_plugin_pc_shop_all_category_menu_widget extends PC_plugin_pc_shop_category_menu_widget {
	
	public function get_data() {
		$data = array();
		
		$page_path = $this->site->Get_page_path();
	
		$shop_nr = false;
	
		foreach($page_path as $key => $elem){
			if ($elem["controller"] == "pc_shop"){
				//print_pre($elem);
				$shop_nr = $key;
				break;
			}
		}
		
		$params_array = $params = array(
			'all_children' => array(
				'page_data' => $page_path[$shop_nr]
			),
			'parse' => array(
				//'description' => true,
				//'attributes' => true,
				//'recources' => true,
			),
			'full_links' => true
		);
		//print_pre($params_array);
		
		$this->shop = $this->core->Get_object("PC_shop_site");
		$data['menu']  = $this->shop->categories->Get(null, null, null, $params);
		
		return $data;
	}
	
}