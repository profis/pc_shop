<?php
$filter_widget = new PC_plugin_pc_shop_category_products_filter_widget(array(
	'category' => $this->currentCategory
));
echo $filter_widget->get_text();

?>
<?php
echo $this->site->Get_widget_text('PC_plugin_pc_shop_products_widget', array(
	'category' => $this->currentCategory,
	'params' => $filter_widget->products_params
));