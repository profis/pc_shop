$(document).ready(function(){
	
	function updateCart($this, $type){
		var del_parent = $this.parent().parent();
		$this = $this.parent().parent().parent();
		if ($type == 'plus' || $type == 'minus') {
			$this = $this.parent();
		}
		if ($type == 'remove') {
			$this = del_parent;
		}
		var $key = $this.attr("key");
		//var $vnt_price = $this.attr("vnt_price");
		var $quantity = $this.find("input.cart_quantity").val();
		
		var url = PC_base_url + "api/plugin/pc_shop/cart/";
		
		if ($type == "plus") url += "addAt/"+$key+"/1";
		else if ($type == "minus") url += "remove/"+$key+"/1";
		else if ($type == "set") url += "set/"+$key+"/"+$quantity;
		else if ($type == "remove") url += "remove/"+$key;			
			
		$.ajax({
			"url": url,
			success: function(data) {
				if (data.success > 0){
					$this.find("input.cart_quantity").val(data.success);
				} else {
					$this.remove();
				}
				
				if (data.item){
					$this.find("span.price_val").text(data.item.totalPrice);
				}
				
				
				var $tprice = format( "###0,00", data.totalPrice);
				var $dprice = format( "###0,00", data.delivery_price);
				var $fprice = format( "###0,00", data.full_price);

				$("#tprice").text($tprice);
				$("#dprice").text($dprice);
				$("#fprice").text($fprice);

				$("#ctprice").text($tprice);
				$("#cdprice").text($dprice);
				$("#cfprice").text($fprice);

				//alert(JSON.stringify(data, null, 2));

				$("#cqty").text(data.total);
				
				if (data.totalPrice > 0){
					
				} else {
					$("table.cart").remove();
					$("#cart_prices").remove();
					$(".empty_cart").show();
				
				}
			}
		});				
	}
	
	$('.btn.plus').click(function(){
		updateCart($(this), "plus");
	});
	$('.btn.minus').click(function(){
		updateCart($(this), "minus");
	});
	$('.del_from_cart').click(function(){
		updateCart($(this), "remove");
	});
	$('.cart_quantity').change(function(){
		updateCart($(this), "set");
	});
	
});