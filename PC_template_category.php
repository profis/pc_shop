<?php $category_menu_data = $this->site->Get_widget_data('PC_plugin_pc_shop_category_menu_widget');
	$this->site->Register_data('additional_menu_' . $this->site->loaded_page['pid'], $category_menu_data['menu']);
?>

<div id="shop_bar" class="pull-left">
	<?php
	if ($this->shop->categories->is_node($this->currentCategory)) {
		echo $this->Render('category.products');
	}
	else {
		echo $this->Render('category.categories');
	}
	?>
</div>
