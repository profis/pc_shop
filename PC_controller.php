<?php
use \Profis\GlobalEvents;
use \Profis\ValidationEventArgs;

class PC_controller_pc_shop extends PC_controller {
	public $currentProduct = null, $currentCategory = null;
	
	/** @var PC_shop_site */
	protected $shop = null;
	
	protected $type = null;

	/** @var PC_shop_payment_method */
	public $payment_method = null;

	/** @var array */
	public $order_data = null;
	
	public function Init($do_not_bind_to_site = false) {
		global $shop_controller;
		$shop_controller = $this; // DIRTY HACK SINCE PC_core::Get_object() returns a new instance, not an existing one!
		parent::Init();
		$this->shop = $this->core->Get_object('PC_shop_site');
	}
	
	public function Process($params) {
		//available page types: category/product/register/activate/cart/order/ order/fast
		//fast-order must enter this data: address, recipient, email (for order data to send)
		$pluginName = basename(dirname(__FILE__));
		$this->site->Add_script('plugins/' . $pluginName . '/js/number.format.min.js');
		$this->site->Add_script('plugins/' . $pluginName . '/js/shop.js');
		
		$this->product_was_added_to_cart = false;

		$this->site->force_headings = false;
		$this->action = '';
		if ($this->routes->Get_count() > 1) {
			if ($this->routes->Get_count() >= 2) {
				$this->action = $this->route[2];
				if ($this->route[2] == 'activation') {
					$this->activation_action();
					return true;
				}
				elseif ($this->route[2] == 'cart') {
					$this->cart_action();
					return true;
				}
				elseif ($this->route[2] == 'order') {
					$this->order_action();
					return true;
				}
				elseif ($this->route[2] == 'ordered') {
					$this->ordered_action();
					return true;
				}
			}
			$this->last_route = $last_route = $this->routes->Get_last();
			$this->route_path = $route_path = $this->routes->Get_range(2);
			if (v($this->cfg['router']['no_trailing_slash'])) {
				pc_remove_trailing_slash($route_path);
			}

			$params = array('filter'=> array('pc.route'=> $last_route));
			$params['manufacturer'] = true;
			$products = $this->shop->products->Get(null, null, $params);
			$product_action = false;
			if ($products) {
				$d = false;
				foreach ($products as $key => $product) {
					if ($product['link'] == $route_path) {
						$d = $product;
						break;
					}
				}
				if ($d) {
					$this->type = 'product';
					$this->currentProduct = $d;
					$this->currentCategory = $this->shop->categories->Get($d['category_id']);
					$this->shop->categories->Load_path($this->currentCategory, $this->site->loaded_page['route']);
					if ($this->site->Is_opened($this->currentCategory['path'][0]['pid'])) {
						$product_action = true;
						$this->product_action($d['id']);
					}
					else {
						$this->_before_action_finish();
						$this->core->Do_action('show_error', 404);
					}
				}
				else {
					//invalid product path
					//$this->_before_action_finish();
					//$this->core->Do_action('show_error', 404);
				}
			}
			if (!$product_action) {
				$category_id = $this->_detect_category($this->last_route, $route_path);
				if ($category_id) {
					$this->category_action($category_id);
				}
				else {
					$this->core->Do_action('show_error', 404);
				}
			}
		}
		else {
			//$this->core->Do_action('show_error', 404);

			if ($this->Is_search()) {
				$this->type = 'search';
				$this->Render('search');
			}
			else {
				$this->type = 'home';
				$this->Render();
			}
		}
		$this->_before_action_finish();
	}
	
	protected function _detect_product() {
		
	}
	

	/**
	 * Method finds id of category that is published, is in current page and satisfies the following conditions (arguments).
	 * @param type $last_route route of category
	 * @param type $route_path route path of category (without page route)
	 * @return boolean
	 */
	protected function _detect_category($last_route, $route_path) {
		$categories = $this->shop->categories->Get_id_by_content('route', $last_route, $this->site->ln, false);
		$category_id = false;
		if ($categories) {
			foreach ($categories as $cat_id) {
				$page_id = $this->shop->categories->Get_page_id($cat_id);
				if ($page_id == $this->page->get_id()) {
					$native_link = true;
					$cat_route_path = $this->shop->categories->Get_link_by_id($cat_id, $this->site->ln, $some_page_id, $native_link);
					if ($cat_route_path == $route_path) {
						return $cat_id;
					}
				}
			}
		}
		return $category_id;
	}
	
	public function get_link_to_cart() {
		return 'cart';
	}
	
	public function get_link_to_order() {
		return 'order';
	}
	
	public function get_link_to_order_fast() {
		return 'order/fast';
	}
	
	public function activation_action() {
		$user = $this->core->Get_object('PC_user');
		$code = (isset($this->route[3])?$this->route[3]:(isset($_POST['activationCode'])?$_POST['activationCode']:false));
		if ($code) {
			$s = $user->Activate($code);
			if ($s) {
				$type = $user->Get_meta_data('type');
				if ($type != 'person' && $type != 'entity') $type = 'person';
				$this->Render('order_'.$type);
				return true;
			}
		}
		$this->Render('activate');
	}
	
	public function cart_action() {
		$this->site->Set_url_suffix_callback(
			$this, 
			'get_link_to_cart', 
			array()
		);
		$this->Render('cart');
	}
	
	protected function _validate_fast_order($data) {
		$args = new ValidationEventArgs($data);
		GlobalEvents::invoke('pc_shop.validateFastOrderFormEvent', $args); // using global events since PC_controller_pc_shop might not be loaded when other plugin tries to add a listener
		$hasErrors = $args->hasErrors();
		if( $hasErrors || $args->isDefaultPrevented() )
			return !$hasErrors;

		if (!trim($data['email'])) {
			return false;
		}
		if (v($data['is_company'])) {
			if (!trim(v($data['company_name']))) {
				return false;
			}
			if (!trim(v($data['company_code']))) {
				return false;
			}
		}
		else {
			if (!trim($data['name'])) {
				return false;
			}
		}
		return true;
	}
	
	protected function _get_payment_option_data() {
		$payment_option_model = $this->core->Get_object('PC_shop_payment_option_model');
		$code = $this->routes->Get(4);
		if (isset($this->order_data) and isset($this->order_data['payment_option'])) {
			$code = $this->order_data['payment_option'];
		}
		return $payment_option_model->get_one(array(
			'where' => array(
				'code' => $code,
				'enabled' => 1
			)
		));
	}
	
	protected function _get_payment_method_object() {
		$payment_option_data = $this->_get_payment_option_data();
		if ($payment_option_data) {
			$class_name = 'PC_shop_' . $payment_option_data['code'] . '_payment_method';
			$payment_method_class_path = $this->cfg['path']['plugins'] . 'pc_shop_payment_' . $payment_option_data['code'] . '/' . $class_name . '.php';
			if (file_exists($payment_method_class_path)) {
				require_once $this->cfg['path']['plugins'] . 'pc_shop/classes/PC_shop_payment_method.php';
				require_once $payment_method_class_path;
				$this->payment_method = $this->core->Get_object($class_name, array($payment_option_data, v($this->order_data, array()), $this->shop));
				return $this->payment_method;
			}
		}
		return false;
	}
	
	protected function _make_payment() {
		$content = '';
		if ($this->_get_payment_method_object()) {
			$content = $this->payment_method->make_online_payment();
		}
		else {
			$this->ordered_action();
		}
		return $content;
	}
	
	protected function _order_online_payment_cancel() {
		$this->core->Redirect_local(pc_append_route($this->page->Get_current_page_link(), 'cart'));
	}
	
	protected function _order_online_payment_accept() {
		if ($this->_get_payment_method_object()) {
			$result = $this->payment_method->accept();
			switch ($result) {
				case PC_shop_payment_method::STATUS_SUCCESS:
				case PC_shop_payment_method::STATUS_ALREADY_PURCHASED:	
					if ($result == PC_shop_payment_method::STATUS_SUCCESS) {
						$this->order_data = $this->payment_method->get_order_data();
						$this->order_id = $this->order_data['id'];
					}
					$this->render('order.online_payment.success');
					break;

				case PC_shop_payment_method::STATUS_WAITING:
					$this->render('order.online_payment.waiting');
					break;
				
				case PC_shop_payment_method::STATUS_ERROR:
					$this->_online_payment_error = $this->payment_method->get_error();
					$this->render('order.online_payment.error');
					break;
				
				case PC_shop_payment_method::STATUS_FAILED:
					$this->render('order.online_payment.failed');
					break;
				
				default:
					break;
			}
		}
	}
	
	protected function _order_online_payment_callback() {
		if ($this->_get_payment_method_object()) {
			$result = $this->payment_method->callback();
			if ($result) {
				$this->order_data = $this->payment_method->get_order_data();
				$this->order_id = $this->order_data['id'];
				$this->_order_set_is_paid();
				$this->_inform_about_order(true);
			}
		}
		exit;
	}

	/** @deprecated */
	protected function _order_online_payment_successful($response) {
		if ($response['status'] != 1) {
			throw new Exception("Payment hasn't been accepted yet");
		}
			
		$this->order_id = $response['orderid'];
		$this->order_data = $this->shop->orders->get($this->order_id);

		if (!$this->order_data) {
			throw new Exception('Order was not found');
		}
		
		if ($this->order_data['is_paid']) {
			throw new Exception('is_paid');
		}
		
		if (!$this->cfg['pc_shop']['web2pay_test'] and $response['test'] !== '0') {
			throw new Exception('Testing, real payment was not made');
		}

		$total_price = $this->order_data['total_price'];

		$order_currency = $this->order_data['currency'];
		
		if (number_format($response['amount'], 0, '', '') != number_format(($total_price*100), 0, '', '')) {
			throw new Exception('Bad amount: ' . $response['amount']);
		}

		if ($response['currency'] != $order_currency) {
			throw new Exception('Bad currency: ' . $response['currency']);
		}
		$this->_order_set_is_paid();
		$this->_inform_about_order(true);
		return true;
	}
	
	protected function _order_set_is_paid() {
		$this->shop->orders->Set_is_paid($this->order_id);
	}
	
	protected function _insert_order() {
		$this->site->Register_data('createOrderSubmitted', true);
		$this->shop->orders->Preserve_order_data();
		$data = $this->shop->orders->Get_preserved_order_data();
		$name = v($data['name']);
		$email = v($data['email']);
		$comment = v($data['comment']);
		$address = v($data['address']);
		$phone = v($data['phone']);
		$payment_option = v($data['payment_option']);
		$delivery_option = v($data['delivery_option']);
		$params = array();

		// collect old style fields ($_POST['company_name'], ...) for compatibility
		$data = PC_utils::getRequestData(array('country', 'city', 'region', 'flat', 'post_index', 'is_company', 'company_name', 'company_code', 'company_pvm_code'));

		// merge with current style fields ($_POST['order']['company_name'], ...)
		$data = array_merge($data, array_intersect_key($_POST['order'], array_flip(array('country', 'city', 'region', 'flat', 'post_index', 'is_company', 'company_name', 'company_code', 'company_pvm_code'))));

		if (isset($_POST['order_data']) and is_array($_POST['order_data'])) {
			$data = array_merge($data, $_POST['order_data']);
		}
		//$data = pc_sanitize_value($data, 'strip_tags');
		$clear_cart = true;
		$user_id = null;
		/** @var PC_user $site_users */
		global $site_users;
		if ($site_users) {
			$user_id = $site_users->GetID();
		}
		$r = $this->shop->orders->Create($user_id, $name, $address, $phone, $email, $comment, $params, $clear_cart, $payment_option, $delivery_option, 0, $data);
		$this->order_id = $this->shop->orders->last_order_id;
		$this->other_data = $this->shop->orders->data;
		$this->payment_option = $payment_option;
		$this->site->Register_data('createOrderResult', $r);
		$this->site->Register_data('createOrderParams', $params);
		return $r;
	}

	protected function _set_coupon() {
		if (isset($_POST['pc_shop_coupon'])) {
			$coupon_model = $this->core->Get_object('PC_shop_coupon_model');
			$coupon_data = $coupon_model->get_valid_coupon($_POST['pc_shop_coupon']);
			if ($coupon_data) {
				$this->shop->cart->set_coupon_data($coupon_data);
			}
			
		}
	}
	
	public function order_action() {
		if ($this->routes->Get(3) == 'online_payment_cancel') {
			$this->_order_online_payment_cancel();
			return;
		}
		if ($this->routes->Get(3) == 'online_payment_accept') {
			$this->_order_online_payment_accept();
			return;
		}
		if ($this->routes->Get(3) == 'online_payment_callback') {
			$this->_order_online_payment_callback();
			return;
		}
		
		if ($this->shop->cart->Is_empty()) {
			$this->core->Redirect_local(pc_append_route($this->page->Get_current_page_link(), 'cart'));
			return;
		}
		$content = '';		

		//if ($this->routes->Get(3) == 'fast') {
			$this->site->Set_url_suffix_callback(
				$this,
				'get_link_to_order_fast',
				array()
			);
			if ($this->routes->Get(3) == 'fast') {
				$this->site->Register_data('isFastOrder', true);
			}
			//print_r($_POST);
			if (isset($_POST['order']) && $this->_validate_fast_order($_POST['order']) ) {
				$cartData = $this->shop->cart->Get();
				if( empty($cartData['errors'])) {
					$r = $this->_insert_order();
					$this->order_data = $this->shop->orders->get($this->order_id);
					if ($r and $this->order_id and $this->order_data) {
						$this->core->Init_hooks('plugin/pc_shop/after-order-create', array(
							'order_id'=> $this->order_id,
							'order_data' => &$this->order_data,
							'other_data' => &$this->other_data,
						));
					}
					$content = $this->_make_payment();
				}
			}

			if (!v($this->action_rendered)) {
				$this->_set_coupon();
				$this->Render('order');
				$this->_before_action_finish();
			}
			if (!empty($content)) {
				$this->text = $content;
			}
			return true;
		//}
		$this->site->Set_url_suffix_callback(
			$this, 
			'get_link_to_order', 
			array()
		);
		$user = $this->core->Get_object('PC_user');
		if (!$user->Logged_in) {
			if (isset($_POST['register'])) {
				$login = v($_POST['login']);
				$email = v($_POST['email']);
				$pass = v($_POST['pass']);
				$pass2 = v($_POST['pass2']);
				$address = v($_POST['address']);
				$phone = v($_POST['phone']);
				$type = v($_POST['_registerType']);
				$user = $this->core->Get_object('PC_user');
				if ($type == 'person') {
					$name = v($_POST['name']);
					$r = $user->Create($email, $pass, $pass2, $name, true, null, $login, array(
						'type'=> $type,
						'address'=> $address,
						'phone'=> $phone
					));
				}
				elseif ($type == 'entity') {
					$name = v($_POST['title']);
					$organization = v($_POST['organization']);
					$domicile = v($_POST['domicile']);
					$inn = v($_POST['inn']);
					$kpp = v($_POST['kpp']);
					$okpo = v($_POST['okpo']);
					$r = $user->Create($email, $pass, $pass2, $name, true, null, $login, array(
						'type'=> $type,
						'address'=> $address,
						'phone'=> $phone,
						'organization'=> $organization,
						'domicile'=> $domicile,
						'inn'=> $inn,
						'kpp'=> $kpp,
						'okpo'=> $okpo
					));
				}
				if (v($r['success'])) {
					$_POST['user_login'] = $login;
					$_POST['user_password'] = $pass;
					$this->Render('activate');
					return true;
				}
				else $this->site->Register_data('pc_shop_register_result', v($r, array()));
			}
			$this->Render('register');
			return true;
		}
		else {
			$metaData = $user->Get_meta_data();
			$params = array();
			$r = $this->shop->orders->Create($user->ID, $user->Data['name'], $metaData['address'], $metaData['phone'], $user->Data['email'], null, $params);
			$this->site->Register_data('createOrderSubmitted', true);
			$this->site->Register_data('createOrderResult', $r);
			$this->site->Register_data('createOrderParams', $params);
			$this->Render('order');
			return true;
		}
	}
	
	public function ordered_action() {
		$this->action_rendered = true;

		if (!v($this->order_id)) {
			$this->Render('ordered_error');
			return;
		}
		
		if (!v($this->order_data)) {
			$this->order_data = $this->shop->orders->get($this->order_id);
		}
		
		if (!v($this->order_data)) {
			$this->Render('ordered_error');
			return;
		}
			
		$this->_inform_about_order();
		
		$this->Render('ordered');
		$this->_before_action_finish();
	}
	
	protected function _inform_about_order($is_paid = false) {
		$this->_tpl_is_paid = $is_paid;
		$this->_send_order_email_to_buyer($is_paid);
		$this->_send_order_email_to_admin($is_paid);
	}
	
	protected function _send_order_email_to_buyer($is_paid = false) {
		$buyer_email_body = $this->Render('order.email.buyer');

		$subject = '';
		if ($is_paid) {
			$subject = $this->Get_variable('new_paid_order_email_subject_to_buyer');
		}
		if (empty($subject)) {
			$subject = $this->Get_variable('new_order_email_subject_to_buyer');
		}
		
		$email_params = array(
			'from_email' => $this->cfg['from_email'],
			'from_name' => $this->Get_variable('new_order_email_sender_to_buyer'),
			'subject' => $subject,
		);
		PC_utils::sendEmail($this->order_data['email'], $buyer_email_body, $email_params);
	}
	
	protected function _send_order_email_to_admin($is_paid = false) {
		$email_body = $this->Render('order.email.admin');

		$subject = '';
		if ($is_paid) {
			$subject = $this->Get_variable('new_paid_order_email_subject_to_admin');
		}
		if (empty($subject)) {
			$subject = $this->Get_variable('new_order_email_subject_to_admin');
		}
		
		$email_params = array(
			'from_email' => $this->cfg['from_email'],
			'from_name' => $this->Get_variable('new_order_email_sender_to_admin'),
			'subject' => $subject,
		);
		PC_utils::sendEmail($this->cfg['pc_shop']['new_order_email_admin'], $email_body, $email_params, array('is_paid' => $is_paid));
	}
	
	public function category_action($id) {
		$c_params = array(
			'load_path' => true,
			'page_link' => $this->site->loaded_page['route']
		);
		$category_data = $this->shop->categories->Get($id, null, null, $c_params);
		if (!$category_data) {
			$this->core->Do_action('show_error', 404);
		}
	
		if (!empty($category_data['permalink']) and strpos($_SERVER['REQUEST_URI'], $category_data['permalink'])=== false) {
			$redirect_link = $this->shop->categories->Get_link_from_data($category_data);
			$this->core->Redirect_local($redirect_link, 301);
		}

		$this->page->process_redirect($category_data, false, false);
		
		$this->_before_action_finish();
		
		$this->type = 'category';
		
		$this->currentCategory = $category_data;
		$this->site->Register_data('currentCategory', $this->currentCategory);
		$this->currentCategoryLink = $this->currentCategory['full_link'] = $this->shop->categories->Get_link_from_data($category_data, $this->site->loaded_page['route']);
		$this->_set_seo_for_category();
		$this->site->Set_url_suffix_callback(
			$this->shop->categories, 
			'Get_link_by_id', 
			array($this->currentCategory['id'])
		);
		$this->Render('category');
	}
	
	protected function _set_seo_for_category() {
		$this->Set_current_path($this->currentCategory['path']);
		
		if (!empty($this->currentCategory['seo_title'])) {
			$this->site->loaded_page['title'] = pc_e($this->currentCategory['seo_title']);
		}
		else {
			$path_count = count($this->currentCategory['path']);
			$last_path_item = $this->currentCategory['path'][$path_count - 1];
			$seo_title = $last_path_item['name'];
			$parent_seo_title = '';
			if ($path_count > 1) {
				$parent_path_item = $this->currentCategory['path'][$path_count - 2];
				v($parent_path_item['seo_title']);
				v($parent_path_item['title']);
				$parent_seo_title = v($parent_path_item['name']);
				if (!empty($parent_path_item['seo_title'])) {
					$parent_seo_title = $parent_path_item['seo_title'];
				}
				elseif(!empty($parent_path_item['title'])) {
					$parent_seo_title = $parent_path_item['title'];
				}
			}
			if (empty($parent_seo_title)) {
				$parent_seo_title = $this->site->loaded_page['name'];
			}
			if (!empty($parent_seo_title)) {
				$seo_title .= ' | ' . $parent_seo_title;
			}
			$this->site->loaded_page['title'] = pc_e($seo_title);
		}
		if (!empty($this->currentCategory['seo_keywords'])) {
			$this->site->loaded_page['keywords'] = pc_e($this->currentCategory['seo_keywords']);
		}
		if (!empty($this->currentCategory['seo_description'])) {
			$this->site->loaded_page['description'] = pc_e($this->currentCategory['seo_description']);
		}
	}
	
	protected function _set_seo_for_product() {
		//$this->Set_current_path($this->currentCategory['path']);
		
		if (!empty($this->currentProduct['seo_title'])) {
			$this->site->loaded_page['title'] = pc_e($this->currentProduct['seo_title']);
		}else {
			$this->site->loaded_page['title'] = pc_e($this->currentProduct['name']);
		}
		
		if (!empty($this->currentProduct['seo_keywords'])) {
			$this->site->loaded_page['keywords'] = pc_e($this->currentProduct['seo_keywords']);
		}
		if (!empty($this->currentProduct['seo_description'])) {
			$this->site->loaded_page['description'] = pc_e($this->currentProduct['seo_description']);
		}
	}
	
	public function product_action($id) {
		$this->_set_seo_for_product();
		$this->Set_current_path($this->currentCategory['path']);
		$this->site->Register_data('currentCategory', $this->currentCategory);
		$this->site->Register_data('currentProduct', $this->currentProduct);
		$this->currentProduct['page_type'] = 'pc_shop_product';
		$this->site->Path_append($this->name, $this->currentProduct);
		//$this->site->Use_component('js/hooks');
		$this->site->Add_script($this->core->Get_url('plugins','','pc_shop')."js/products.js");
		$this->site->Set_url_suffix_callback(
			$this->shop->products, 
			'Get_link_by_id', 
			array($this->currentProduct['id'])
		);
		$this->Render('product');
	}
	
	protected function _before_action_finish() {
	}
	
	public function Set_current_path($path) {
		if (!is_array($path)) return false;
		if (count($path)) {
			foreach ($path as $i) {
				$this->site->Path_append($this->name, $i);
			}
		}
		return true;
	}
	public function Get_full_path() {
		if (is_null($this->currentProduct)) return $this->currentPath;
		$path = $this->currentPath;
		$pr = $this->currentProduct;
		$pr['type'] = 'product';
		$path[] = $pr;
		return $path;
	}
	public function Is_product_opened($id) {
		if (!is_array($this->currentProduct)) return false;
		if ($this->currentProduct['id'] === $id) return true;
		return false;
	}
	public function Is_current_category($id) {
		if (v($this->currentCategory['id']) == $id) return true;
		else return false;
	}
	public function Is_current_product($id) {
		if (v($this->currentProduct['id']) == $id) return true;
		else return false;
	}
	public function Is_category() {
		return ($this->type == 'category');
	}
	public function Is_product() {
		return ($this->type == 'product');
	}
	public function Is_home() {
		return ($this->type == 'home');
	}
	public function Is_search() {
		return isset($_GET['q']);
	}
	public function Get_type() {
		return $this->type;
	}
}