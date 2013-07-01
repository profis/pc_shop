<?php

class PC_shop_product_product_model extends PC_model {
	
	protected function _set_tables() {
		$this->_table = 'shop_product_products';
	}
	
		
	public function get_related_products_counts(array $ids) {
		$this->debug('get_related_products_counts()');
		$this->debug($ids);
		
		$counts = array();
		
		$related_products_counts = $this->get_all(array(
			'select' => 'product_id, count(*) as c',
			'group' => 'product_id',
			'where' => array(
				'product_id' => $ids
			),
		));
		$this->debug($related_products_counts, 1);
		
		foreach ($related_products_counts as $key => $d) {
			v($counts[$d['product_id']], 0);
			$counts[$d['product_id']] += $d['c'];
		}
		
		$related_products_counts_2 = $this->get_all(array(
			'select' => 'product_id_2, count(*) as c',
			'group' => 'product_id_2',
			'where' => array(
				'product_id_2' => $ids
			),
		));
		$this->debug($related_products_counts_2, 1);
		
		foreach ($related_products_counts_2 as $key => $d) {
			v($counts[$d['product_id_2']], 0);
			$counts[$d['product_id_2']] += $d['c'];
		}
		
		return $counts;
	}
	
}
