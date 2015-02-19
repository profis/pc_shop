<?php
/**
 * @var PC_plugin_pc_shop_order_widget $this
 * @var string $tpl_group
 * @var array $cart_data
 * @var array $order_data
 * @var array $coupon_data
 * @var array $delivery_options
 * @var array $payment_options
 * @var string $currency
 */
$this->site->Add_script($this->cfg['directories']['media'] . '/form_validation.js');
?>
<!-- <input type="hidden" name="order" value="1" /> -->

<div class="form-group">
	<label for="order_name" class="col-lg-2 control-label"><?php echo $this->core->Get_plugin_variable('order_name', $this->plugin_name); ?><span>*</span></label>
	<div class="col-lg-10">
		<input type="text" name="order[name]" id="order_name" value="<?php echo htmlspecialchars(v($order_data["name"], '')); ?>" class="form-control" placeholder="<?php echo $this->core->Get_plugin_variable('order_name', $this->plugin_name); ?>">
	</div>
</div>

<div class="form-group">
	<label for="order_address" class="col-lg-2 control-label"><?php echo $this->core->Get_plugin_variable('order_address', $this->plugin_name); ?></label>
	<div class="col-lg-10">
		<input type="text" name="order[address]" id="order_address" value="<?php echo htmlspecialchars(v($order_data["address"], '')); ?>" class="form-control" placeholder="<?php echo $this->core->Get_plugin_variable('order_address', $this->plugin_name); ?>">
	</div>
</div>

<div class="form-group">
	<label for="order_email" class="col-lg-2 control-label"><?php echo $this->core->Get_plugin_variable('order_email', $this->plugin_name); ?><span>*</span></label>
	<div class="col-lg-10">
		<input type="email" name="order[email]" id="order_email" value="<?php echo htmlspecialchars(v($order_data["email"], '')); ?>" class="form-control" placeholder="<?php echo $this->core->Get_plugin_variable('order_email', $this->plugin_name); ?>">
	</div>
</div>

<div class="form-group">
	<label for="order_phone" class="col-lg-2 control-label"><?php echo $this->core->Get_plugin_variable('order_phone', $this->plugin_name); ?><span>*</span></label>
	<div class="col-lg-10">
		<input type="tel" name="order[phone]" id="order_phone" value="<?php echo htmlspecialchars(v($order_data["phone"], '')); ?>" class="form-control" placeholder="<?php echo $this->core->Get_plugin_variable('order_phone', $this->plugin_name); ?>">
	</div>
</div>

<div class="clear"></div>

<?php
if (!empty($deliveryOptions)) {
?>
<div class="input_holder">
	<label><?php echo $this->core->Get_plugin_variable('delivery', $this->plugin_name); ?>:<br /></label>
	<?php
		$count = 0;
		if (!isset($order_data["delivery_option"]) or !isset($deliveryOptions[$order_data["delivery_option"]])) {
			$delivery_option_keys = array_keys($deliveryOptions);
			$order_data["delivery_option"] = $delivery_option_keys[0];
		}
		foreach ($deliveryOptions as $key => $option){
			echo '<span><input type="radio" name="order[delivery_option]" class="radio" id="delivery_option_'.$key.'" value="'.$key.'"'.($key == $order_data["delivery_option"]?' checked':'').' /> <label class="for_radio"  for="delivery_option_'.$key.'">'.$option.'</label></span>';
			$count++;
		}
	?>
</div>
<div id="delivery_form"><?php echo $this->site->Get_widget_text('PC_plugin_pc_shop_delivery_form_widget'); ?></div>
<div class="clear"></div>
<?php
}
?>

<div class="input_holder">
	<label><?php echo $this->core->Get_plugin_variable('order_comment', $this->plugin_name); ?>:<br /><textarea name="order[comment]"><?php echo v($order_data["comment"]); ?></textarea></label>
</div>

<div class="clear"></div>


<div class="input_holder">
	<label><?php echo $this->core->Get_plugin_variable('payment', $this->plugin_name); ?>:<br /></label>
	<?php
		$count = 0;
		if (!isset($order_data["payment_option"]) or !isset($payment_options[$order_data["payment_option"]])) {
			$payment_option_keys = array_keys($payment_options);
			$order_data["payment_option"] = $payment_option_keys[0];
		}
		foreach ($payment_options as $key => $option){
			echo '<span><input type="radio" name="order[payment_option]" class="radio" id="payment_option_'.$key.'" value="'.$key.'"'.($key == $order_data["payment_option"]?' checked':'').' /> <label class="for_radio"  for="payment_option_'.$key.'">'.$option.'</label></span>';
			$count++;
		}
	?>
</div>
<div class="clear"></div>

<div id="order_errors" class="alert alert-danger<?php echo empty($cart_data['errors']) ? ' hidden' : ''; ?>"><?php echo $cart_data['errors']; ?></div>

<script language="javaScript" type="text/javascript" >
	function saveValues (){
		$.ajax({
			type: "POST",
			data : $('#order_form').serializeArray(),
			cache: false,
			url: PC_base_url + "api/plugin/pc_shop/order/save",
			success: function(data) {
				PC_shop_cart.setCartData(data);
			}
		});
	}

	function order_add_validation() {
		var validation = {
			'id:order_name' : [
				{
					rule: 'required'
				},
				{
					rule: 'min_length',
					param: 3
				}
			],
			'id:order_phone': [
				{
					rule: 'required'
				}
			],
			'id:order_email': [
				{
					rule: 'required'
				},
				{
					rule: 'email'
				}
			],
			'id:order_address': [
				{
					//rule: 'required'
				}
			]
		};
		var config = {
			scroll_to_invalid_field: true,
			//ok_background_color: false,
			//error_background_color: false,
			error_parent_class: 'error'
		};
		PC_add_validation('#order_form', validation, config);
	}

	$(document).ready(function(){
		$('#order_form').on('change', 'input, textarea, select', saveValues);
		order_add_validation();
	});
</script>
	
	
<?php 