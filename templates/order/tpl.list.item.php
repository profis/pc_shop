<tr qty="<?php echo $item['quantity'] ?>" prid="<?php echo $item['id'] ?>" key="<?php echo $key ?>">
	<td class="name"><a href="<?php echo $item['link'] ?>"><?php echo $item['name'] ?></a></td>
	<td class="quantity"><?php echo $item['basket_quantity'] ?></td>			
	<td class="price"><span class="price_val"><?php echo  number_format($item['totalPrice'], 2, ",", "") ?></span><?php echo $this->core->Get_plugin_variable('valiuta', $this->plugin_name) ?><span class="del_from_cart"></span></td>
</tr>