<?php
/**
 * @var PC_plugin_pc_shop_order_history_widget $this
 * @var string $tpl_group
 * @var PC_params $params
 * @var array[] $orders
 * @var array $cart_data
 */
?>
Order history
<?php

print_pre($orders);

if ($this->_config['per_page']) {
	echo $this->site->Get_widget_text('PC_paging_widget', array(
		'base_url' => $this->page->Get_current_page_link(),
		'get_vars' => '_all',
		'per_page' => $this->_config['per_page'],
		'total_items' => $params->paging->Get_total(),
		//'total_pages' => $params->paging->totalPages
	));
}

return

?>

<table class="cart" style="width:100%;">
	<tr class="header">
		<td class="first"><?php echo $this->core->Get_plugin_variable('product_name', $this->plugin_name) ?></td>
		<td><?php echo $this->core->Get_plugin_variable('item_amount', $this->plugin_name) ?></td>
		<td><?php echo $this->core->Get_plugin_variable('price', $this->plugin_name) ?></td>
	</tr>
<?php

foreach ($cart_data['items'] as $key => $item) {
	include $this->core->Get_tpl_path($tpl_group, 'tpl.list.item');
}

?>
</table>
