<?php 

use \Profis\Db\DbException;

class PC_plugin_pc_shop_category_menu_widget extends PC_vmenu_widget {
	
	public $plugin_name = 'pc_shop';

	/** @var PC_shop_site */
	public $shop;

	protected $counters;
	protected $menuBuildParams;

	public function get_template_group() {
		 return parent::get_template_group() . ':_plugin/' . $this->plugin_name . '/category_menu';
	}
	
	public function get_data($args = null) {
		$data = array();

		$params = array(
			'parse' => array(
				//'description' => true,
				//'attributes' => true,
				//'recources' => true,
			),
			'full_links' => true
		);
		if( v($this->_config['fetchResources']) )
			$params['parse']['resources'] = true;
		$this->shop = $this->core->Get_object("PC_shop_site");
		/** @var PC_shop_categories_site $categories */
		$categories = $this->shop->categories;

		$this->counters = array();
		if( isset($this->_config['showProductCount']) && $this->_config['showProductCount'] ) {
			$cmd = $this->db->prepare($q = "SELECT c.id, COUNT(DISTINCT p.id) AS cnt FROM {$this->db_prefix}shop_categories c LEFT JOIN {$this->db_prefix}shop_categories s ON s.lft > c.lft AND s.rgt < c.rgt INNER JOIN {$this->db_prefix}shop_products p ON p.category_id=s.id OR p.category_id = c.id GROUP BY c.id");
			if( !$cmd->execute() )
				throw new DbException($cmd->errorInfo(), $q);
			while( $row = $cmd->fetch() )
				$this->counters[$row['id']] = $row['cnt'];
			$data['showProductCount'] = true;
		}
		$this->menuBuildParams = $params;
		$data['menu']  = $categories->Get(null, 0, isset($this->_config['pageId']) ? $this->_config['pageId'] : $this->page->Get_id(), $params);
		$this->_build_menu($data['menu']);
		return $data;
	}
	
	protected function _build_menu(&$menu, $level = 1) {
		$maxLevels = v($this->_config['maxLevels'], 0);
		foreach ($menu as $key => $menu_item) {
			if ($menu_item["published"] != 1) continue; 
			if ($menu_item["nomenu"] == 1) continue; 
			if (trim($menu_item["name"]) == "") continue;

			$menu[$key]['productCount'] = isset($this->counters[$menu_item['id']]) ? $this->counters[$menu_item['id']] : 0;

			$menu[$key]['_active'] = $active = $this->isActiveCategory($menu_item['id']);
			if ($active || $level < v($this->_config['expandToLevel'], 1) ) {
				if( $maxLevels < 1 || $level < $maxLevels ) {
					$params = $this->menuBuildParams;
					$menu[$key]['_submenu'] = $this->shop->categories->Get(null, $menu_item['id'], null, $params);
					$this->_build_menu($menu[$key]['_submenu'], $level + 1);
				}
			}
		}
	}

	protected function isActiveCategory($categoryId) {
		if (!is_array(v($this->site->loaded_page['subpath']))) return false;
		foreach ($this->site->loaded_page['subpath'] as $i) {
			if (v($i['_correspondingPlugin']) != $this->plugin_name || isset($i['product_id']) )
				continue;
			if ($i['id'] == $categoryId)
				return true;
		}
		return false;
	}
}