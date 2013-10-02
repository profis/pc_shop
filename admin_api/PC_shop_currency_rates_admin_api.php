<?php

class PC_shop_currency_rates_admin_api extends PC_shop_admin_api {
	
	protected function _get_model() {
		$model = $this->core->Get_object('PC_shop_currency_rate_model');
		$model->absorb_debug_settings($this);
		return $model;
	}
	
	protected function _get_sync_fields() {
		return array('rate');
	}
	
	protected function _adjust_search(&$params) {
		$ln_currency_model = new PC_shop_ln_currency_model();
		$currency_ids = $ln_currency_model->get_all(array(
			'select' => 'distinct(t.c_id)',
			'value' => 'c_id'
		));
		
		$rated_currency_ids = $this->_model->get_all(array(
			'value' => 'c_id'
		));
		
		$not_rated_currency_ids = array_diff($currency_ids, $rated_currency_ids);
		
		foreach ($not_rated_currency_ids as $c_id) {
			$this->_model->insert(array(
				'c_id' => $c_id,
				'rate' => 0
			));
		}
		
		$params['where']['c_id'] = $currency_ids;
		$params['where'][] = array(
			'field' => 'sc.code',
			'op' => '!=',
			'value' => $this->cfg['pc_shop']['currency']
		);
		vv($params['join'], array());
		vv($params['query_params'], array());
		$params['join'][] = "LEFT JOIN {$this->db_prefix}shop_currencies sc ON sc.id = t.c_id";
		$params['select'] = 't.*, sc.code';
		$params['formatter'] = '_format_relation';
		
	}
	
	
	public function import($code = '') {
		$model = $this->_get_model();
		$rated_currency_codes = $model->get_all(array(
			'key' => 'code',
			'value' => 'c_id',
			'join' => array("LEFT JOIN {$this->db_prefix}shop_currencies sc ON sc.id = t.c_id"),
			'select' => 't.*, sc.code'
		));
		$this->debug('$rated_currency_codes:', 1);
		$this->debug($rated_currency_codes, 2);
			
		$file = 'http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
		$xml = @simplexml_load_file($file);
		
		if (!$xml or !v($xml->Cube) or !v($xml->Cube->Cube) or !v($xml->Cube->Cube->Cube)) {
			$this->_out['success'] = false;
			$this->_out['error'] = 'wrong_xml';
			return;
		}
		
		$data = array();
		$base_currency = $this->cfg['pc_shop']['currency'];
		foreach ($xml->Cube->Cube->Cube as $currency) {
			$currency_code = (string) $currency['currency'];
			if (isset($rated_currency_codes[$currency_code]) or $currency_code == $base_currency) {
				$d = array(
					'c_id' => v($rated_currency_codes[$currency_code], false),
					'code' => $currency_code,
					'rate' => (string) $currency['rate']
				);
				$data[$currency_code] = $d;
			}
		}
		
		$this->debug('rates from  provider:', 1);
		$this->debug($data, 2);
		
		$exchange_currency = 'EUR';
		
		if ($base_currency != $exchange_currency) { 
			if (isset($data[$base_currency])) {
				$base_currency_factor = $data[$base_currency]['rate'];
				
				if (isset($rated_currency_codes[$exchange_currency]) and !isset($data[$exchange_currency])) {
					$data[$exchange_currency] = array(
						'c_id' => $rated_currency_codes[$exchange_currency],
						'code' => $exchange_currency,
						'rate' => 1
					);
				}
				
				foreach ($data as $key => $currency_data) {
					if ($currency_data['c_id'] == false) {
						unset($data[$key]);
						continue;
					}
					if ($key == $base_currency) {
						$data[$key]['rate'] = 1;
					}
					else {
						$data[$key]['rate'] = round($data[$key]['rate'] / $base_currency_factor, 4);
					}
					
				}
				$data = array_values($data);
			}
			else {
				$data = array();
			}
			
		}
		//print_pre($data);
		if (!empty($code)) {
			foreach ($data as $key => $value) {
				if ($value['code'] == $code) {
					$data = $value;
					break;
				}
			}
		}
		$this->_out['success'] = true;
		$this->_out['data'] = $data;
	}
	
}

?>
