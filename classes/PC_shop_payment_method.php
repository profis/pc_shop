<?php

abstract class PC_shop_payment_method extends PC_base {

	const STATUS_SUCCESS = 'success';
	const STATUS_ALREADY_PURCHASED = 'already_purchased';
	const STATUS_WAITING = 'waiting';
	const STATUS_FAILED = 'failed';
	const STATUS_ERROR = 'error';
	
	const _STATUS_IS_PAID = 'is_paid';

	protected $_response_amount_in_cents = false;
	
	protected $_response;
	
	protected $_payment_data;
	/** @var array */
	protected $_order_data;
	/** @var PC_shop_site */
	protected $_shop_site;

	/** @var bool */
	protected $_test = false;
	
	protected $_error = '';

	public $order_id;
	
	public function Init($payment_data, $order_data = array(), $shop_site = null) {
		$this->_payment_data = $payment_data;
		$this->_order_data = $order_data;
		$this->_shop_site = $shop_site;
	}
	
	public function get_error() {
		return $this->_error;
	}
	
	public function set_test($test) {
		$this->_test = $test;
	}
	
	public function get_order_data() {
		return $this->_order_data;
	}
	
	protected function _get_order_data() {
		return $this->_shop_site->orders->get($this->order_id);
	}
	
	protected function _is_test() {
		if ($this->_payment_data['test']) {
			return true;
		}
		return false;
	}
	
	protected function _get_accept_url() {
		return $this->core->Absolute_url(pc_append_route($this->page->Get_current_page_link(), 'order/online_payment_accept/' . $this->_payment_data['code']));
	}
	
	protected function _get_cancel_url() {
		return $this->core->Absolute_url(pc_append_route($this->page->Get_current_page_link(), 'order/online_payment_cancel'));
	}
	
	protected function _get_callback_url() {
		return $this->core->Absolute_url(pc_append_route($this->page->Get_current_page_link(), 'order/online_payment_callback/' . $this->_payment_data['code']));
	}
	
	abstract public function make_online_payment();
	abstract public function callback();
	abstract public function accept();
	
	
	protected function _get_additional_prices() {
		$prices = array();
		
		$m = new PC_shop_delivery_option_model();
		$delivery_options = $m->get_all(array(
			'content' => true,
			'where' => array(
				'enabled' => 1
			),
			'key' => 'code',
			'value' => 'name',
			'order' => 'position',
			//'query_only' => true
		));
		
		if ($this->_order_data['delivery_price']) {	
			$prices[] = array(
				'id' => 'delivery_method_' . $this->_order_data['delivery_option'],
				'price' => $this->_order_data['delivery_price'],
				'quantity' => 1,
				'name' => v($delivery_options[$this->_order_data['delivery_option']])
			);
		}
		
		return $prices;
	}
	
	protected function _is_order_paid() {
		return $this->_order_data['is_paid'];
	}
	
	protected function _get_order_total_price() {
		return $this->_order_data['total_price'];
	}
	
	protected function _get_order_currency() {
		return $this->_order_data['currency'];
	}
	
	abstract protected function _get_response_payment_status();
	
	abstract protected function _get_response_order_id();
	
	abstract protected function _get_response_test();
	
	abstract protected function _get_response_amount();
	
	abstract protected function _get_response_currency();
	
	
	protected function _is_payment_successful() {
		if (!$this->_get_response_payment_status()) {
			throw new Exception("Payment hasn't been accepted yet");
		}
		
		if (empty($this->order_id)) {
			$this->order_id = $this->_get_response_order_id();
		}
		if (!$this->order_id) {
			throw new Exception(':( could not get respose order id');
		}
		if (empty($this->_order_data)) {
			$this->_order_data = $this->_get_order_data($this->order_id);
		}
		
		if (!$this->_order_data) {
			throw new Exception('Order was not found');
		}
		
		if ($this->_is_order_paid()) {
			throw new Exception(self::_STATUS_IS_PAID);
		}
		
		if (!$this->_payment_data['test'] and $this->_get_response_test()) {
			throw new Exception('Testing, real payment was not made');
		}

		$total_price = $this->_get_order_total_price();
		$order_currency = $this->_get_order_currency();
		
		$amount = $this->_get_response_amount();
		$expected_amount = $total_price*($this->_response_amount_in_cents?100:1);
		if (number_format($amount, 0, '', '') != number_format(($expected_amount), 0, '', '')) {
			throw new Exception('Bad amount: ' . $amount . ', expected: ' . $expected_amount);
		}

		
		$currency = $this->_get_response_currency();
		if ($currency != $order_currency) {
			throw new Exception('Bad currency: ' . $currency);
		}
		return true;
	}
	
	
	/**
	 * Makes curl http request.
	 * @param string $url request url, may contain query string ( $_GET params ).
	 * @param string|array $post_vars post vars as string (ex.: prm1=prm1val&prm2=prm2val&...) or as key value pair array.
	 * @param string $phd additional request headers.
	 * @param string $out_file file path to save response to.
	 * @return array response as key value pair array.
	 */
	function httpRequest($url, $post_vars = null, $phd = null, $out_file = null) {
		$post_contents = "";
		if ($post_vars) {
			if (is_array($post_vars))
				foreach ($post_vars as $key => $val)
					$post_contents .= ($post_contents?"&":"") . urlencode($key) . "=" . urlencode($val);
			else
				$post_contents = $post_vars;
		}
		
		$uinf = parse_url($url);
		$host = $uinf['host'];
		$path = isset($uinf['path']) ? $uinf['path'] : '';
		$path .= (isset($uinf['query']) && $uinf['query']) ? '?'.$uinf['query'] : '';
		if ($out_file) {
			$fl = fopen($out_file, 'w+');
			if (!$fl)
				return FALSE;
		}
		$headers = array(
			($post_contents?"POST":"GET") . " $path HTTP/1.1",
			"Host: $host",
		);
		if ($phd) {
			$headers[count($headers)] = $phd;	
		}
		if( $post_contents ) {
			$headers[] = "Content-Type: application/x-www-form-urlencoded";
			$headers[] = "Content-Length: " . strlen($post_contents);
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL,$url);
		if ($out_file)
			curl_setopt($ch, CURLOPT_FILE, $fl );
		else {
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER , 1);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		// Apply the XML to our curl call
		if( $post_contents ) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_contents);
		}

		$data = curl_exec($ch);
		$header_size		= curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$result['shd']		= $headers;
		$result['header']	= substr($data, 0, $header_size);
		$result['body']		= substr($data, $header_size );
		$result['http_code']= curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$result['last_url']	= curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		
		if ($out_file)
			fclose($fl);
		if (curl_errno($ch)) {
			//echo "<li><b>CRITICAL CURL ERROR</b>: " . curl_error($ch) . "</li>";
			return false;
		}
		curl_close($ch);
		return $result;
	}
	
	
	/**
	 * Converts key value pairs array to query string
	 * @param array $params key value pairs array
	 * @return string query string
	 */
	function urlParamsToString($params) {
		if (!is_array($params)) return $params;
		$prm = '';
		foreach ($params as $k => $v) {
			if (!$k || is_null($v) || $v === false) continue;
			$prm .= ($prm ? '&' : '').urlencode($k).(($uv = urlencode($v)) ? ('='.$uv) : '');
		}
		$prm = str_replace('%2F', '/', $prm);
		return $prm ? $prm : false;
	}
	
	/**
	 * Converts query string to key value pairs array
	 * @param string $params query string
	 * @return array key value pairs array
	 */
	function urlParamsToArray($params) {
		if (is_array($params)) return $params;
		$params = explode('&', $params);
		$prm = Array();
		for ($i = 0, $count = count($params); $i < $count; $i++) {
			$p = explode('=', $params[$i], 2);
			$prm[trim($p[0])] = isset($p[1]) ? urldecode($p[1]) : '';
		}
		return $prm ? $prm : false;
	}
	
	/**
	 * Get variable value
	 * 
	 * @return mixed
	 */
	static function gvv($key, $arr = false, $def = '', $html_spec_chars = false, $not_empty = false) {
		$val = $def;
		if ($arr === false) {
			global $$key;
			$val = (isset($$key) && (!$not_empty || !empty($$key))) ? $$key : $def;
		} else if (is_array($arr)) {
			$val = (isset($arr[$key]) && (!$not_empty || !empty($arr[$key]))) ? $arr[$key] : $def;
		} else if (is_object($arr)) {
			$val = (isset($arr->$key) && (!$not_empty || !empty($arr->$key))) ? ($arr->$key) : $def;
		}
		return $html_spec_chars ? htmlspecialchars($val) : $val;
	}
		
}
