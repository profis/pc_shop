<?php 

class PC_plugin_pc_shop_products_widget extends PC_plugin_pc_shop_widget {

	protected $_template_group = 'products';
	
	protected $_category_id;

	/** @var PC_plugin_pc_shop_sort_products_widget */
	public $sort_widget = null;
	/** @var array */
	public $sort_widget_data = null;
	
	protected function _get_default_config() {
		return array(
			'category' => false,
			'params' => array(),
			'limit' => 0,
			'per_page' => 10,
			'per_row' => 0,
			'list_item_thumb_type' => 'small',
			'default_sort' => '',
			'empty_sort_name' => '',
			'sort_options' => array(
				'price_asc' => array('field' => 'real_price', 'var' => 'price', 'dir' => 'asc'),
				'price_desc' => array('field' => 'real_price', 'var' => 'price', 'dir' => 'desc'),
				'name_asc' => array('field' => 'pc.name', 'var' => 'name', 'dir' => 'asc'),
			)
		);
	}
	
	public function get_params() {
		if (isset($this->_config["page"])) {
			$paging_cr_pg = $this->_config["page"];
		} else if (v($_REQUEST["page"])) {
			$paging_cr_pg = $_REQUEST["page"];
		} else {
			$paging_cr_pg = 1;
		}

		$params = array();
		if (isset($this->_config['params']) and is_array($this->_config['params'])) {
			$params = $this->_config['params'];
		}
		
		vv($params['flags'], array());
		$params['flags'][] = PC_shop_products::PF_PUBLISHED;
		
		if ($this->_config['per_page']) {
			$per_page = $this->_config['per_page'];
			$limit = $this->_config['per_page'];
			if ($this->_config['limit']) {
				$limit = $this->_config['limit'];
				if ($limit < $per_page) {
					$per_page = $limit;
					$this->_config['per_page'] = 0;
					$paging_cr_pg = 1;
				}
			}
			$params['paging'] = array(
				'perPage' => $per_page,
				'page' => $paging_cr_pg,
				'limit' => $limit,
			);
		}
		elseif($this->_config['limit']) {
			$params['paging'] = array(
				'perPage' => $this->_config['limit'],
				'page' => 1,
				'limit' => $this->_config['limit'],
			);
		}

		$params['parse'] = array(
			//'description' => true,
			//'attributes' => true,
			//'resources' => true,
			'resources' => 'first_img_only'
		);
		if( v($this->_config['fetchAttributes']) )
			$params['parse']['attributes'] = true;
		if (!isset($params['full_links'])) {
			$params['full_links'] = true;
		}
		return $params;
	}
	
	public function get_data() {
		/** @var PC_shop_products_site $shop_products_site */
		$shop_products_site = $this->core->Get_object('PC_shop_products_site');
		$params = $this->get_params();
		$get_vars = false;

		$base_url = $this->site->Get_link();
		//$base_url = PC_utils::getCurrUrl();

		$itemIdList = (isset($this->_config['itemIdList']) && is_array($this->_config['itemIdList'])) ? $this->_config['itemIdList'] : null;
		$category_id = null;

		if( $itemIdList === null ) {
			if (isset($this->_config['category'])) {
				if (is_array($this->_config['category'])) {
					$category_id = $this->_config['category']['id'];
					$base_url =$this->_config['category']['full_link'];
				}
				else {
					$category_id = $this->_config['category'];
				}
			}
		}

		if ($this->_config['sort_options']) {
			$this->sort_widget = new PC_plugin_pc_shop_sort_products_widget(array(
				'base_url' => $this->_get_url(),
				'sort_options' => $this->_config['sort_options'],
				'default_sort' => $this->_config['default_sort'],
				'empty_sort_name' => $this->_config['empty_sort_name'],
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

		$data = array(
			'items' => $shop_products_site->Get($itemIdList, $category_id, $params),
			'params' => $params,
			'base_url' => $base_url
		);

		if ($get_vars) {
			$data['get_vars'] = $get_vars;
		}
		return $data;
	}
	
}