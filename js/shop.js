var PC_shop_cart = {
	priceFormat: "### ### ##0,00",

	format: function(price) {
		return format(PC_shop_cart.priceFormat, price);
	},

	update: function($object, type) {
		$this = $object.closest('[key]');
		var $quantity = $this.find("input.cart_quantity");
		var key = $this.attr("key");
		console.log($quantity.length);
		var requestType = type;
		var requestData = {};

		if( typeof(requestType) == 'string' ) {
			var quantity = parseInt($quantity.val());
			if( isNaN(quantity) || quantity < 1 )
				quantity = 1;
			var url = PC_base_url + "api/plugin/pc_shop/cart/";
			if (requestType == "plus") url += "addAt/" + key + "/1";
			else if (requestType == "minus") {
				if( quantity <= 1 )
					return;
				url += "remove/" + key + "/1";
			}
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
					else if( requestType == 'set' || requestType == 'plus' || requestType == 'minus' ) {
						if( parseInt(data.success) != parseInt($quantity.val()) )
							$quantity.val(data.success);
					}
				}
				else if( typeof(requestType) == 'object' ) {
					if( typeof(requestType.success) == 'function' )
						requestType.success.call($this, data, requestType);
				}

				if (data.item){
					$this.find("span.price_val").text(PC_shop_cart.format(data.item.totalPrice));
					$this.find(".total_price_val").text(PC_shop_cart.format(data.item.totalPrice));
				}

				PC_shop_cart.setCartData(data);
			},
			error: function() {
				$this.trigger('cartdone');
				if( typeof(requestType) == 'object' ) {
					if( typeof(requestType.error) == 'function' )
						requestType.error.call($this, requestType);
				}
			}
		});
	},

	setCartData: function(data) {
		$("#cqty, #cart_item_count").text(data.total);
		$('#cart_total_quantity').text(data.totalQuantity);
		$("#tprice, #ctprice").text(PC_shop_cart.format(data.totalPrice));
		$("#dprice, #cdprice, .dprice").text(PC_shop_cart.format(data.order_delivery_price));
		$("#pprice").text(PC_shop_cart.format(data.order_cod_price));
		$("#fprice, #cfprice").text(PC_shop_cart.format(data.order_full_price));
		$("#discount_price").text(PC_shop_cart.format(-data.total_discount)); // discount, total_discount or eligible_discount?
		$("#delivery_form").html(data.delivery_form).trigger('changed');

		if( typeof(data.discounts) == 'object' )
			$('#promo_discount').text(PC_shop_cart.format(-data.discounts.coupon));

		if ( !data.total ) {
			$("#product_cart, #cart_prices, table.cart").remove();
			$(".empty_cart").show();
		}

		if( typeof(data.errors) == "string" ) {
			var $errorMsg = $("#order_errors");
			var $nextStepBtn = $("#next_step_btn");
			$errorMsg.html(data.errors);
			if (data.errors) {
				$errorMsg.removeClass('hidden');
				$nextStepBtn.addClass('disabled');
			}
			else {
				$errorMsg.addClass('hidden');
				$nextStepBtn.removeClass('disabled');
			}
		}
	},

	canContinueToNextStep: function() {
		var $errors = $('#order_errors');
		return $errors.length == 0 || $errors.is('.hidden');
	}
};

$(document).ready(function(){
	$('.btn.plus').click(function(e){
		e.preventDefault();
		PC_shop_cart.update($(this), "plus");
	});
	$('.btn.minus').click(function(e){
		e.preventDefault();
		PC_shop_cart.update($(this), "minus");
	});
	$('.del_from_cart').click(function(e){
		e.preventDefault();
		PC_shop_cart.update($(this), "remove");
	});
	$('.cart_quantity').change(function(){
		PC_shop_cart.update($(this), "set");
	});

	$('#next_step_btn').on('click', function(e) {
		if( !PC_shop_cart.canContinueToNextStep() )
			e.preventDefault();
	});
	$('#order_form').on('submit', function(e) {
		if( !PC_shop_cart.canContinueToNextStep() )
			e.preventDefault();
	});
});