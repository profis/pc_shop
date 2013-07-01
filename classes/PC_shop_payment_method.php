<?php

abstract class PC_shop_payment_method extends PC_base {
	
	const RESPONSE_STATUS_SUCCESS = 'success';
	const RESPONSE_STATUS_WAITING = 'waiting';
	const RESPONSE_STATUS_FAILED = 'failed';
	
	protected $_payment_data;
	protected $_order_data;
	protected $_shop_site;
	
	protected $_test = false;
	
	public $order_id;
	
	public function Init($payment_data, $order_data = array(), $shop_site = null) {
		$this->_payment_data = $payment_data;
		$this->_order_data = $order_data;
		$this->_shop_site = $shop_site;
		
	}
	
	public function set_test($test) {
		$this->_test = $test;
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
	
		
}
