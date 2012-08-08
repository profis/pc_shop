<?php
final class PC_controller_pc_shop extends PC_controller {
	public $currentProduct = null, $currentCategory = null;
	private $shop;
	protected $type = null;
	public function Init() {
		global $shop_controller;
		$shop_controller = $this; // DIRTY HACK SINCE PC_core::Get_object() returns a new instance, not an existing one!
		parent::Init();
		$this->shop = $this->core->Get_object('PC_shop_site');
	}
	public function Process($params) {
		//available page types: category/product/register/activate/cart/order/ order/fast
		//fast-order must enter this data: address, recipient, email (for order data to send)
		$this->site->force_headings = false;
		if ($this->routes->Get_count() > 1) {
			if ($this->routes->Get_count() >= 2) {
				if ($this->route[2] == 'activation') {
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
					return true;
				}
				elseif ($this->route[2] == 'cart') {
					$this->Render('cart');
					return true;
				}
				elseif ($this->route[2] == 'order') {
					if ($this->routes->Get(3) == 'fast') {
						$this->site->Register_data('isFastOrder', true);
						if (isset($_POST['order'])) {
							$this->site->Register_data('createOrderSubmitted', true);
							$name = v($_POST['name']);
							$email = v($_POST['email']);
							$address = v($_POST['address']);
							$phone = v($_POST['phone']);
							$params = array();
							$r = $this->shop->orders->Create(null, $name, $address, $phone, $email, null, $params);
							$this->site->Register_data('createOrderResult', $r);
							$this->site->Register_data('createOrderParams', $params);
						}
						$this->Render('order');
						return true;
					}
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
			}
			$last = $this->routes->Get_last();
			$path = $this->routes->Get_range(2);
			$params = array('filter'=> array('pc.route'=> $last));
			$d = $this->shop->products->Get(null, null, $params);
			if ($d) {
				$d = $d[0];
				if ($d['link'] == $path) {
					$this->type = 'product';
					$this->currentProduct = $d;
					$this->currentCategory = $this->shop->categories->Get($d['category_id']);
					$this->shop->categories->Load_path($this->currentCategory);
					$this->currentPath = $this->currentCategory['path'];
					//$this->site->Use_component('js/hooks');
					$this->site->Add_script($this->core->Get_url('plugins','','pc_shop')."js/products.js");
					$this->Render('product');
				}
				else {
					//invalid product path
					$this->core->Do_action('show_error', 404);
				}
			}
			else {
				$params = array('filter'=> array('cc.route'=> $last), 'load_path'=> true);
				$d = $this->shop->categories->Get(null, null, $params);
				if ($d) {
					$d = $d[0];
					if ($d['link'] == $path) {
						$this->type = 'category';
						$this->currentCategory = $d;
						$this->currentPath = $d['path'];
						$this->Render('category');
					}
					else {
						//invalid category path
						$this->core->Do_action('show_error', 404);
					}
				}
				else {
					//invalid route specified
					$this->core->Do_action('show_error', 404);
				}
			}
		}
		else {
			$this->core->Do_action('show_error', 404);
			/*
			$this->type = 'home';
			$this->Render();
			*/
		}
	}
	public function Get_full_path() {
		if (is_null($this->currentProduct)) return $this->currentPath;
		$path = $this->currentPath;
		$pr = $this->currentProduct;
		$pr['type'] = 'product';
		$path[] = $pr;
		return $path;
	}
	public function Is_category_opened($id) {
		foreach ($this->currentPath as &$i) {
			if ($i['id'] === $id) return true;
		}
		return false;
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
	public function Get_type() {
		return $this->type;
	}
}