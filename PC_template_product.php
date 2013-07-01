<div>
<div id="menu_bar" class="pull-left">
	<?php 
	echo $this->site->Get_widget_text('PC_plugin_pc_shop_all_category_menu_widget') 
	?>
</div>
<div id="shop_bar" class="pull-left">
	<?php
	echo $this->site->Get_widget_text('PC_plugin_pc_shop_product_widget', array(
		
	), $this->currentProduct, $this->shop->products);
	?>
</div>
<div class="clear"></div>
</div>