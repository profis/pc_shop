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
