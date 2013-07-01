
<?php 
if ($item['quantity'] > 0){  ?>
	<form method="POST" action="">
		<input type="hidden" name="product_id" value="<?php echo $item["id"] ?>" />
		<input type="hidden" name="add_to_basket" value="1" />
		<div class="to_cart btn btn-success" onclick="this.parentNode.submit();return false;"><?php echo $this->core->Get_plugin_variable('to_basket', 'pc_shop') ?></div>
	</form>
<?php 
} else {  
?>
	<div class="sold_out btn"><?php echo $this->core->Get_plugin_variable('sold_out', 'pc_shop') ?></div>
<?php 
}  
?>



