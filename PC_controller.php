<?php
class PC_controller_pc_shop extends PC_controller {
	public $currentProduct = null, $currentCategory = null;
	
	/**
	 *
	 * @var PC_shop_site
	 */
	protected $shop;
	
	protected $type = null;
	
	public function Init($do_not_bind_to_site = false) {
		global $shop_controller;
		$shop_controller = $this; // DIRTY HACK SINCE PC_core::Get_object() returns a new instance, not an existing one!
		parent::Init();
		$this->shop = $this->core->Get_object('PC_shop_site');
	}
	public function Process($params) {
		//available page types: category/product/register/activate/cart/order/ order/fast
		//fast-order must enter this data: address, recipient, email (for order data to send)
		$this->payment_logger = new PC_debug();
		$this->payment_logger->debug = true;
		$this->payment_logger->debug_forced = true;
		$this->payment_logger->set_instant_debug_to_file($this->cfg['path']['logs'] . 'pc_shop/payments.html', false, 500);
		$this->payment_logger->debug('ip: ' . pc_ip());		
		
		if (isset($_GET['debug'])) {
			$this->debug = true;
			$this->set_instant_debug_to_file($this->cfg['path']['logs'] . 'pc_shop/controller_log.html', false, 5);
			$this->shop->orders->debug = $this->shop->attributes->debug = $this->shop->products->debug = $this->shop->categories->debug = $this->debug;
		}
		$this->click('Controller process started');
		
		$this->logger = new PC_debug();
		$this->logger->absorb_debug_settings($this);
		
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

			$this->debug('last:');
			$this->debug($last_route);
			$this->debug('path:');
			$this->debug($route_path);
			$params = array('filter'=> array('pc.route'=> $last_route));
			$params['manufacturer'] = true;
			$products = $this->shop->products->Get(null, null, $params);
			$product_action = false;
			$this->click('Tried to fetch products');
			$this->debug('Products data:');
			$this->debug($products);
			if ($products) {
				$this->debug('Products where found:');
				$d = false;
				foreach ($products as $key => $product) {
					$this->debug('   $product[link]:' . $product['link']);
					if ($product['link'] == $route_path) {
						$this->debug('BINGO!');
						$d = $product;
						break;
					}
				}
				if ($d) {
					$this->type = 'product';
					$this->currentProduct = $d;
					$this->currentCategory = $this->shop->categories->Get($d['category_id']);
					$this->shop->categories->Load_path($this->currentCategory, $this->site->loaded_page['route']);
					$this->debug('$this->currentCategory:');
					$this->debug($this->currentCategory);
					if ($this->site->Is_opened($this->currentCategory['path'][0]['pid'])) {
						$product_action = true;
						$this->product_action($d['id']);
					}
					else {
						$this->debug('404');
						$this->_before_action_finish();
						$this->core->Do_action('show_error', 404);
					}
				}
				else {
					//invalid product path
					//$this->debug('invalid product path');
					//$this->_before_action_finish();
					//$this->core->Do_action('show_error', 404);
				}
			}
			if (!$product_action) {
				$this->debug('Products were not found. Looking for gategories');
				$category_id = $this->_detect_category($this->last_route, $route_path);
				$this->click('Category was detected');
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
			$this->shop->products->set_new_debug($this->debug);
			
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
		$this->debug("_detect_category(last_route: $last_route, route_path: $route_path) [current page is ]" . $this->page->get_id());
		$categories = $this->shop->categories->Get_id_by_content('route', $last_route, $this->site->ln, false);
		$this->debug('Categories by route ' . $last_route . ':', 1);
		$this->debug($categories, 2);
		$category_id = false;
		if ($categories) {
			foreach ($categories as $cat_id) {
				$page_id = $this->shop->categories->Get_page_id($cat_id);
				$this->debug('category page is ' . $page_id, 2);
				if ($page_id == $this->page->get_id()) {
					$native_link = true;
					$cat_route_path = $this->shop->categories->Get_link_by_id($cat_id, $this->site->ln, $some_page_id, $native_link);
					$this->debug('category route path is ' . $cat_route_path, 3);
					if ($cat_route_path == $route_path) {
						$this->debug(':) category detected ' . $cat_id, 4);
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
	
	protected function _validate_fast_order() {
		if (!trim($_POST['name'])) {
			return false;
		}
		if (!trim($_POST['email'])) {
			return false;
		}
		if (v($_POST['is_company'])) {
			if (!trim(v($_POST['company_name']))) {
				return false;
			}
			if (!trim(v($_POST['company_code']))) {
				return false;
			}
			if (!trim(v($_POST['company_pvm_code']))) {
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
		$this->payment_logger->debug('code:' . $code, 2);
		//$payment_option_model->absorb_debug_settings($this->payment_logger);
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
			$this->debug($payment_method_class_path);
			if (file_exists($payment_method_class_path)) {
				$this->debug('Creating payment method object', 3);
				require_once $this->cfg['path']['plugins'] . 'pc_shop/classes/PC_shop_payment_method.php';
				require_once $payment_method_class_path;
				$this->payment_method = $this->core->Get_object($class_name, array($payment_option_data, v($this->order_data, array()), $this->shop));
				$this->payment_method->absorb_debug_settings($this->payment_logger);
				return $this->payment_method;
			}
			else {
				$this->debug(':( payment method file does not exist', 3);
			}
		}
		else {
			$this->debug(':( Could not get payment option data', 3);
		}
		return false;
	}
	
	protected function _make_payment() {
		$this->debug('controller:_make_payment()');
		$content = '';
		if ($this->_get_payment_method_object()) {
			$content = $this->payment_method->make_online_payment();
			$this->debug('controller:onlined payment made ' . $this->payment_method->file);
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
		$this->payment_logger->debug('Accept()');
		if ($this->_get_payment_method_object()) {
			$result = $this->payment_method->accept();
			$this->payment_logger->debug("accept result: " . $result, 1);
			switch ($result) {
				case PC_shop_payment_method::STATUS_SUCCESS:
				case PC_shop_payment_method::STATUS_ALREADY_PURCHASED:	
					if ($result == PC_shop_payment_method::STATUS_SUCCESS) {
						$this->order_data = $this->payment_method->get_order_data();
						$this->order_id = $this->order_data['id'];
						$this->_order_set_is_paid();
						$this->_inform_about_order(true);
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
		$this->payment_logger->debug('Callback()');
		if ($this->_get_payment_method_object()) {
			$result = $this->payment_method->callback();
			if ($result) {
				$this->order_data = $this->payment_method->get_order_data();
				$this->order_id = $this->order_data['id'];
				$this->_order_set_is_paid();
				$this->_inform_about_order(true);
			}
		}
		else {
			$this->payment_logger->debug(':( could not get payment method object', 3);
		}
		exit;
	}
	
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
		$this->payment_logger->debug('Setting is_paid for order ' . $this->order_id, 2);
		$this->shop->orders->Set_is_paid($this->order_id);
	}
	
	protected function _insert_order() {
		$this->debug("_insert_order()");
		$this->site->Register_data('createOrderSubmitted', true);
		$this->shop->orders->Preserve_order_data($_POST);
		$name = v($_POST['name']);
		$email = v($_POST['email']);
		$comment = v($_POST['comment']);
		$address = v($_POST['address']);
		$phone = v($_POST['phone']);
		$payment_option = v($_POST['payment_option']);
		$delivery_option = v($_POST['delivery_option']);
		$params = array();
		$data = PC_utils::getRequestData(array('country', 'city', 'region', 'flat', 'post_index', 'is_company', 'company_name', 'company_code', 'company_pvm_code'));
		if (isset($_POST['order_data']) and is_array($_POST['order_data'])) {
			$data = array_merge($data, $_POST['order_data']);
		}
		//$data = pc_sanitize_value($data, 'strip_tags');
		$this->debug('Additional data:', 1);
		$this->debug($data, 1);
		$clear_cart = true;
		$clear_cart = false; //For testing
		$user_id = null;
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
		$this->debug('order_action()');
		
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
			if (isset($_POST['order']) and $this->_validate_fast_order()) {
				//$this->payment_method = new $();
				$r = $this->_insert_order();
				$this->order_data = $this->shop->orders->get($this->order_id);
				if ($r and $this->order_id and $this->order_data) {
					$this->core->Init_hooks('plugin/pc_shop/after-order-create', array(
						'order_id'=> $this->order_id,
						'order_data' => &$this->order_data,
						'other_data' => &$this->other_data,
						'logger' => &$this
					));
				}
				$content = $this->_make_payment();
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
		$this->debug("ordered_action()");
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
			
		$this->debug("order data:", 2);
		$this->debug($this->order_data, 2);
		
		$this->_inform_about_order();
		
		$this->Render('ordered');
		$this->_before_action_finish();
	}
	
	protected function _inform_about_order($is_paid = false) {
		$this->payment_logger->debug('Sending emails about order', 2);
		$this->_tpl_is_paid = $is_paid;
		$this->_send_order_email_to_admin($is_paid);
		$this->_send_order_email_to_buyer($is_paid);
	}
	
	protected function _send_order_email_to_buyer($is_paid = false) {
		$buyer_email_body = $this->Render('order.email.buyer');
		
		$this->debug('Email text for buyer:', 3);
		$this->debug($buyer_email_body, 3);
		
		$this->payment_logger->debug('Email text for buyer:', 3);
		$this->payment_logger->debug($this->order_data['email'], 4);
		$this->payment_logger->debug($buyer_email_body, 4);
		
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
		
		$this->debug('Email text for admin:', 3);
		$this->debug($email_body, 3);
		
		$this->payment_logger->debug('Email text for admin:', 3);
		$this->payment_logger->debug($this->cfg['pc_shop']['new_order_email_admin'], 4);
		$this->payment_logger->debug($email_body, 4);
		
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
		$this->debug("category_action($id)");
		$c_params = array(
			'load_path' => true,
			'page_link' => $this->site->loaded_page['route']
		);
		$this->shop->categories->debug = true;
		$category_data = $this->shop->categories->Get($id, null, null, $c_params);
		$this->click('$category_data were fetched');
		if (!$category_data) {
			$this->core->Do_action('show_error', 404);
		}
	
		if (!empty($category_data['permalink']) and strpos($_SERVER['REQUEST_URI'], $category_data['permalink'])=== false) {
			$redirect_link = $this->shop->categories->Get_link_from_data($category_data);
			$this->debug("Redirecting to permalink {$category_data['permalink']}: $redirect_link", 5);
			$this->core->Redirect_local($redirect_link, 301);
		}

		$this->page->process_redirect($category_data, false, false);
		
		$this->_before_action_finish();
		
		$this->type = 'category';
		
		$this->debug("Rendering category", 3);
		$this->debug("Debug from this->shop->categories", 5);
		$debug_from_categories_inside_action = $this->shop->categories->get_debug_string();
		
		
		$this->debug($category_data, 4);
		$this->currentCategory = $category_data;
		$this->site->Register_data('currentCategory', $this->currentCategory);
		$this->currentCategoryLink = $this->currentCategory['full_link'] = $this->shop->categories->Get_link_from_data($category_data, $this->site->loaded_page['route']);
		$this->_set_seo_for_category();
		$this->site->Set_url_suffix_callback(
			$this->shop->categories, 
			'Get_link_by_id', 
			array($this->currentCategory['id'])
		);
		$this->shop->categories->set_new_debug(true);
		$this->Render('category');
		
		$debug_from_categories_inside_template = $this->shop->categories->get_debug_string();
		$this->debug('debug_from_categories_inside_action:', 6);
		$this->debug($debug_from_categories_inside_action, 6);
		$this->debug('debug_from_categories_inside_template:', 6);
		$this->debug($debug_from_categories_inside_template, 6);
		$this->debug($this->shop->categories->get_exec_times_summary(), 7);
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
		$this->debug('Rendered product');
		
	}
	
	protected function _before_action_finish() {
		$this->click('Action is finished');
		$this->debug('shop->categories debug:');
		$this->debug($this->shop->categories->get_debug_string(), 2);
		$this->debug($this->shop->categories->get_exec_times_summary(), 2);
		$this->debug('shop->products debug:');
		$this->debug($this->shop->products->get_debug_string(), 2);
		$this->debug($this->shop->products->get_exec_times_summary(), 2);
		
		$this->debug('shop->attributes debug:');
		$this->debug($this->shop->attributes->get_debug_string(), 2);
		
		$this->debug('shop->orders debug:');
		$this->debug($this->shop->orders->get_debug_string(), 2);
		
		$this->debug($this->logger->get_exec_times_summary(), 1);
		$this->debug($this->get_exec_times_summary());
		$this->file_put_debug($this->cfg['path']['logs'] . 'pc_shop_controller_log.html');
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