<div>
<div id="menu_bar" class="pull-left">
	<?php echo $this->site->Get_widget_text('PC_plugin_pc_shop_category_menu_widget') ?>
</div>
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
<div class="clear"></div>
</div>