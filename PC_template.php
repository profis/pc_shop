<?php
/**
 * @var PC_controller_pc_shop $this
 */

$category_menu_data = $this->site->Get_widget_data('PC_plugin_pc_shop_category_menu_widget');
$this->site->Register_data('additional_menu_' . $this->site->loaded_page['pid'], $category_menu_data['menu']);
?>
<div id="shop_bar" class="pull-left">
	<?php echo $this->site->Get_widget_text('PC_plugin_pc_shop_newest_products_widget', array(
		'per_row' => false,
		'params' => array(
			'all_products_of_page' => $this->site->loaded_page['pid']
		)
	)) ?>
</div>
