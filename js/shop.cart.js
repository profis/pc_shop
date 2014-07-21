var PC_shop_cart = {
	update: function($object, type){
		$this = $object.closest('[key]');
		var $quantity = $this.find("input.cart_quantity");
		var key = $this.attr("key");

		var requestType = type;
		var requestData = {};

		if( typeof(requestType) == 'string' ) {
			var quantity = parseInt($quantity.val());
			if( isNaN(quantity) || quantity < 1 )
				quantity = 1;
			var url = PC_base_url + "api/plugin/pc_shop/cart/";
			if (requestType == "plus") url += "addAt/" + key + "/1";
			else if (requestType == "minus") url += "remove/" + key + "/1";
			else if (requestType == "set") url += "set/" + key + "/" + quantity;
			else if (requestType == "remove") url += "remove/" + key;
		}
		else if( typeof(requestType) == 'object' ) {
			url = requestType.url;
			requestData = requestType.data;
		}
		else
			return;

		$this.trigger('cartbusy');

		$.ajax({
			url: url,
			data: requestData,
			type: 'post',
			dataType: 'json',
			success: function(data) {
				$this.trigger('cartdone');

				if( typeof(requestType) == 'string' ) {
					if( requestType == 'remove')
						$this.remove();
					else if( requestType == 'set' ) {
						if( parseInt(data.success) != parseInt($quantity.val()) )
							$quantity.val(data.success);
					}
				}
				else if( typeof(requestType) == 'object' ) {
					if( typeof(requestType.success) == 'function' )
						requestType.success.call($this, data, requestType);
				}

				if (data.item){
					$this.find("span.price_val").text(format("### ### ##0,00", data.item.totalPrice));
					$this.find(".total_price_val").text(format("### ### ##0,00", data.item.totalPrice));
				}

				$("#cqty, #cart_item_count").text(data.total);
				$('#cart_total_quantity').text(data.totalQuantity);

				var tprice = format("### ### ##0,00", data.totalPrice);
				var dprice = format("### ### ##0,00", data.delivery_price);
				var fprice = format("### ### ##0,00", data.order_full_price); // full_price or order_full_price?

				$("#tprice").text(tprice);
				$("#dprice").text(dprice);
				$("#fprice").text(fprice);

				$("#ctprice").text(tprice);
				$("#cdprice").text(dprice);
				$("#cfprice").text(fprice);

				if( typeof(data.discounts) == 'object' )
					$('#promo_discount').text(format("### ### ##0,00", -data.discounts.coupon));

				if (data.totalPrice > 0){

				} else {
					$("table.cart").remove();
					$("#cart_prices").remove();
					$(".empty_cart").show();
				}
			},
			error: function() {
				$this.trigger('cartdone');
				if( typeof(requestType) == 'object' ) {
					if( typeof(requestType.error) == 'function' )
						requestType.error.call($this, requestType);
				}
			}
		});
	}
};

$(document).ready(function(){
	$('.btn.plus').click(function(){
		PC_shop_cart.update($(this), "plus");
	});
	$('.btn.minus').click(function(){
		PC_shop_cart.update($(this), "minus");
	});
	$('.del_from_cart').click(function(){
		PC_shop_cart.update($(this), "remove");
	});
	$('.cart_quantity').change(function(){
		PC_shop_cart.update($(this), "set");
	});
});