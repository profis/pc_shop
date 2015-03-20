<?php
/**
 * @var PC_controller_pc_shop $this
 */
$orderSubmitted = $this->site->Get_data('createOrderSubmitted');
$orderResult = $this->site->Get_data('createOrderResult');
$orderParams = $this->site->Get_data('createOrderParams');
$isFastOrder = $this->site->Get_data('isFastOrder');

if ($isFastOrder) {
	echo $this->site->Get_widget_text('PC_plugin_pc_shop_order_widget');
}
else {
	echo $this->site->Get_widget_text('PC_plugin_pc_shop_checkout_user_widget');
}

return;

if ($orderSubmitted) {
	if ($orderResult) {
		echo 'Order was placed successfully.';
		return;
	}
	else {
		print_pre($orderParams); //see PC_params object
		print_pre($orderParams->errors->_list);
	}
}
if ($isFastOrder) {
	?>
	Fast Order
	<form method="post">
		<label>Buyer name:<br /><input type="text" name="name" value="" /></label><br />
		<label>E-mail:<br /><input type="text" name="email" value="" /></label><br />
		<label>Address:<br /><input type="text" name="address" value="" /></label><br />
		<label>Phone:<br /><input type="text" name="phone" value="" /></label>
		<input type="submit" value="Order" />
	</form>
	<?php
}
?>