<?php

class PC_shop_currencies_admin_api extends PC_shop_admin_api {

	protected $_default_order = 'country_name';
	
	protected function _get_model() {
		return $this->core->Get_object('PC_shop_currency_model');
	}
	
	public function import() {
		$file = __DIR__ . '/../res/currencies_from_webservisex.xml';
		
		$xml = simplexml_load_file($file);
		
		$model = $this->_get_model();
		
		$data = array();
		foreach ($xml->NewDataSet->Table as $currency) {
			$data = array(
				'name' => (string) $currency->Currency,
				'code' => (string) $currency->CurrencyCode,
				'country_name' => (string) $currency->Name,
				'country_code' => (string) $currency->CountryCode,
			);
			//print_pre($data);
			if (empty($data['code'])) {
				continue;
			}
			$model->insert($data, array(), array('ignore' => true));
		}
		
		//Insert default titles for some currencies
		$content_model = new PC_shop_currency_content_model();
		$default_contents = array(
			'LTL' => array(
				'lt' => array('title' => 'Lt'),
				'en' => array('title' => 'Lt'),
				'ru' => array('title' => 'Лт'),
			)
		);
		
		foreach ($default_contents as $code => $contents) {
			$currency_id = $model->get_id_from_field('code', $code);
			if ($currency_id) {
				foreach ($contents as $ln => $content_data) {
					$content_data['currency_id'] = $currency_id;
					$content_data['ln'] = $ln;
					$content_model->insert($content_data, array(), array('ignore' => true));
				}
				
			}
		}
		
		
		//Insert some ln currencies
		$ln_currencies = array(
			'ln' => array('LTL')
		);
		
	}
	
	
	public function get_for_combo() {
		$this->_model = $this->_get_model();
		$this->_model->absorb_debug_settings($this);
		
		$params = array(
			'select' => 't.id, t.code, t.name, t.country_name',
			'ln' => false,
			'order' => 't.country_name',
			'where' => array()
		);
		
		if (isset($_GET['active_only'])) {
			$ln_currency_model = new PC_shop_ln_currency_model();
			$currency_ids = $ln_currency_model->get_all(array(
				'select' => 'distinct(t.c_id)',
				'value' => 'c_id'
			));
			$params['where']['t.id'] = $currency_ids;
		}
		
		$this->_out = $this->_model->get_all($params);
		
		foreach ($this->_out as $key => $value) {
			$this->_out[$key]['name'] = $value['country_name'] . ' - ' . $value['name'] . ' (' . $value['code'] . ')'; 
		}
		
		if (isset($_GET['empty'])) {
			array_unshift($this->_out, array(
				'code' => '',
				'name' => ''
			));	
		}
	}
	
	
}

?>
