<?php

abstract class PC_shop_payment_method extends PC_base {
	
	const STATUS_SUCCESS = 'success';
	const STATUS_ALREADY_PURCHASED = 'already_purchased';
	const STATUS_WAITING = 'waiting';
	const STATUS_FAILED = 'failed';
	const STATUS_ERROR = 'error';
	
	const _STATUS_IS_PAID = 'is_paid';
	
	protected $_response;
	
	protected $_payment_data;
	protected $_order_data;
	protected $_shop_site;
	
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
	
	protected function _get_order_data($id) {
		return $this->_shop_site->orders->get($this->order_id);
	}
	
	protected function _get_accept_url() {
		return $this->core->Absolute_url(pc_append_route($this->page->Get_current_page_link(), 'order/online_payment_accept'));
	}
	
	protected function _get_cancel_url() {
		return $this->core->Absolute_url(pc_append_route($this->page->Get_current_page_link(), 'order/online_payment_cancel'));
	}
	
	protected function _get_callback_url() {
		return $this->core->Absolute_url(pc_append_route($this->page->Get_current_page_link(), 'order/online_payment_callback'));
	}
	
	abstract public function make_online_payment();
	abstract public function callback();
	abstract public function accept();
	
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
			
		$this->order_id = $this->_get_response_order_id();
		$this->_order_data = $this->_get_order_data($this->order_id);

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
		
		if (number_format($amount, 0, '', '') != number_format(($total_price*100), 0, '', '')) {
			throw new Exception('Bad amount: ' . $amount);
		}

		
		$currency = $this->_get_response_currency();
		if ($currency != $order_currency) {
			throw new Exception('Bad currency: ' . $currency);
		}
		return true;
	}
		
}
