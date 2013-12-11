<?php
$this->site->Add_script($this->cfg['directories']['media'] . '/form_validation.js');
?>
<form method="post" id="order_form" action="" class="form-horizontal" role="form">
	<input type="hidden" name="order" value="1" />
	
	<div class="form-group">
		<label for="order_name" class="col-lg-2 control-label"><?php echo $this->core->Get_plugin_variable('order_name', $this->plugin_name); ?><span>*</span></label>
		<div class="col-lg-10">
			<input type="name" name="name" id="order_name" value="<?php echo $order_data["name"];?>" class="form-control" placeholder="<?php echo $this->core->Get_plugin_variable('order_name', $this->plugin_name); ?>">
		</div>
	</div>
	
	<div class="form-group">
		<label for="order_address" class="col-lg-2 control-label"><?php echo $this->core->Get_plugin_variable('order_address', $this->plugin_name); ?></label>
		<div class="col-lg-10">
			<input type="text" name="address" id="order_address" value="<?php echo $order_data["address"];?>" class="form-control" placeholder="<?php echo $this->core->Get_plugin_variable('order_address', $this->plugin_name); ?>">
		</div>
	</div>
	
	<div class="form-group">
		<label for="order_email" class="col-lg-2 control-label"><?php echo $this->core->Get_plugin_variable('order_email', $this->plugin_name); ?><span>*</span></label>
		<div class="col-lg-10">
			<input type="email" name="email" id="order_email" value="<?php echo $order_data["email"];?>" class="form-control" placeholder="<?php echo $this->core->Get_plugin_variable('order_email', $this->plugin_name); ?>">
		</div>
	</div>
	
	<div class="form-group">
		<label for="order_phone" class="col-lg-2 control-label"><?php echo $this->core->Get_plugin_variable('order_tel', $this->plugin_name); ?><span>*</span></label>
		<div class="col-lg-10">
			<input type="phone" name="phone" id="order_phone" value="<?php echo $order_data["phone"];?>" class="form-control" placeholder="<?php echo $this->core->Get_plugin_variable('order_tel', $this->plugin_name); ?>">
		</div>
	</div>
	
	<div class="clear"></div>
	
	<?php 
	if (!empty($delivery_options)) {
	?>
	<div class="input_holder">
		<label><?php echo $this->core->Get_plugin_variable('delivery', $this->plugin_name); ?>:<br /></label>
		<?php 
			$count = 0;
			if (!isset($order_data["delivery_option"]) or !isset($delivery_options[$order_data["delivery_option"]])) {
				$delivery_option_keys = array_keys($delivery_options);
				$order_data["delivery_option"] = $delivery_option_keys[0];
			}
			foreach ($delivery_options as $key => $option){
				echo '<span><input type="radio" name="delivery_option" class="radio" id="delivery_option_'.$key.'" value="'.$key.'"'.($key == $order_data["delivery_option"]?' checked':'').'> <label class="for_radio"  for="delivery_option_'.$key.'">'.$option.'</label></span>';
				$count++;
			}
		?>
	</div>
	<div class="clear"></div>
	<?php 
	} 
	?>
	
	<div class="input_holder">
		<label><?php echo $this->core->Get_plugin_variable('order_comment', $this->plugin_name); ?>:<br /><textarea name="comment"><?php echo $order_data["comment"];?></textarea></label>	
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
				echo '<span><input type="radio" name="payment_option" class="radio" id="payment_option_'.$key.'" value="'.$key.'"'.($key == $order_data["payment_option"]?' checked':'').'> <label class="for_radio"  for="payment_option_'.$key.'">'.$option.'</label></span>';
				$count++;
			}
		?>
	</div>
	<div class="clear"></div>

	
<script language="javaScript" type="text/javascript" >
		
	function saveValues (){
		var postData = {order: {}};
		
		$.each($('#order_form').serializeArray(), function(i, field) {
			postData.order[field.name] = field.value;
		});
		
		$.ajax({
			type: "POST",
			data : postData,
			cache: false,  
			url: PC_base_url + "api/plugin/pc_shop/order/save",   
			success: function(data){
				$("#tprice").text(data.totalPrice);
				$("#dprice").text(data.order_delivery_price);
				$("#pprice").text(data.order_cod_price);
				$("#fprice").text(data.order_full_price);	
			}
		});
	}
	
	function order_add_validation() {
		var validation = {
			name: [
				{
					rule: 'required'
				},
				{
					rule: 'min_length',
					param: 3
				}
			],
			phone: [
				{
					rule: 'required'
				}
			],
			email: [
				{
					rule: 'required'
				},
				{
					rule: 'email'
				}
			],
			address: [
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
		$("#order_form input, #order_form textarea, #order_form select").change(saveValues);
		order_add_validation();
	});
</script>
	
	
<?php 