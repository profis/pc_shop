<?php
class PC_plugin_pc_shop_currency_selector_widget extends PC_plugin_pc_shop_widget {
	
	protected $_template_group = 'currency_selector';
	
	protected function _get_default_config() {
		return array(
		);
	}
	
	protected function _get_currencies() {
		$currencies = array();
		foreach ($this->price->currencies as $currency_code => $currency_id) {
			$currencies[] = array(
				'code' => $currency_code,
				'link' => PC_utils::getUrl(PC_utils::getCurrUrl(), array('pc_currency' => $currency_code))
			);
		}
		return $currencies;
	}
	
	public function get_data() {
		$data = array(
			'currencies' => $this->_get_currencies(),
		);
		return $data;
	}
}