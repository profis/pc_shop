<?php
/**
 * @var PC_plugin_pc_shop_products_widget $this
 * @var string $tpl_group
 * @var string $base_url
 * @var PC_params $params
 * @var array $items
 */

/** @var PC_shop_products_site $shop_products_site */
$shop_products_site = $this->core->Get_object('PC_shop_products_site');

if ($this->sort_widget) {
	echo $this->sort_widget->get_text($this->sort_widget_data);
}

include $this->core->Get_tpl_path($tpl_group, 'tpl.list');

if ($this->_config['per_page']) {
	echo $this->site->Get_widget_text('PC_paging_widget', array(
		'base_url' => $this->_get_url(),
		'get_vars' => '_all',
		'per_page' => $this->_config['per_page'],
		'total_items' => $params->paging->Get_total(),
		//'total_pages' => $params->paging->totalPages
	));
}