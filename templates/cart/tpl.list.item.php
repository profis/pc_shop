<div class="row" qty="<?php echo $item['quantity'] ?>" prid="<?php echo $item['id'] ?>" key="<?php echo $key ?>">
	<div class="col-6 col-sm-6 col-lg-6">
		<strong><a href="<?php echo $item['link'] ?>"><?php echo $item['name'] ?></a></strong>
	</div>
	<div class="col-3 col-sm-3 col-lg-3">
		<div class="input-group">
			<span class="input-group-btn">
				<button class="btn btn-primary minus" type="button"><span class="glyphicon glyphicon-minus"></span></button>
			</span>
			<input type="text" class="form-control cart_quantity" value="<?php echo $item['basket_quantity'] ?>">
			<span class="input-group-btn">
				<button class="btn btn-primary plus" type="button"><span class="glyphicon glyphicon-plus"></span></button>
			</span>
		</div>
	</div>
	<div class="col-1 col-sm-1 col-lg-1 pull-right">
		<button class="btn btn-danger" type="button"><span class="glyphicon glyphicon-remove"></span></button>
	</div>
	<div class="col-2 col-sm-2 col-lg-2 pull-right">
		<strong><?php echo  number_format($item['totalPrice'], 2, ",", "") ?> <?php echo $this->core->Get_plugin_variable('currency', $this->plugin_name)?></strong>
	</div>
</div>

<?php
return
?>


<tr qty="<?php echo $item['quantity'] ?>" prid="<?php echo $item['id'] ?>" key="<?php echo $key ?>">
	<td class="name"><a href="<?php echo $item['link'] ?>"><?php echo $item['name'] ?></a></td>
	<td class="quantity input-prepend input-append"><span class="btn minus add-on">-</span><input size=6 type="text" class="cart_quantity" value="<?php echo $item['basket_quantity'] ?>" /><span class="btn plus add-on">+</span></td>			
	<td class="price"><span class="price_val"><?php echo  number_format($item['totalPrice'], 2, ",", "") ?></span><?php echo $this->core->Get_plugin_variable('valiuta', $this->plugin_name) ?><span class="del_from_cart"></span></td>
</tr>

