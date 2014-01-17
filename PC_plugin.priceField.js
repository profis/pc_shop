Ext.namespace('PC.ux');

PC.ux.priceField = Ext.extend(Ext.form.CompositeField, {
	
	
	constructor: function(config) {
		if (!config) {
			config = {};
		}
		var width = 400;
		if (config.width) {
			width = config.width;
		}
		var value = '';
		if (config.value) {
			value = config.value;
		}
		config.items = [
			/*
			{
				fieldLabel: PC.i18n.mod.pc_shop.price,
				xtype: 'numberfield',
				ref: '../_price',
				value: value
			},
			*/
			{	
				ref: '_prices',
				fieldLabel: PC.i18n.mod.pc_shop.product_prices.title, 
				crud: new Plugin_pc_shop_prices_crud({
					height: 200,
					flex: 1
				}),
				xtype: 'profis_crud_field'
			}
			
		];
		PC.ux.priceField.superclass.constructor.call(this, config);
	},
			
	sync: function() {
		this.items.items[0].crud.sync_grid();
	},
			
	getValue_: function() {
		return this.items.items[0].getValue();
	},
	
	setValue: function(value) {
		var grid = this.items.items[0].crud.grid;
		if (grid) {
			grid.store.rejectChanges();
			//grid.pc_crud.mask = new Ext.LoadMask(grid.pc_crud, {msg:"Please wait..."});
			//grid.pc_crud.mask.show();
			grid.store.setBaseParam('pkey',value );
			grid.store.setBaseParam('ln', PC.global.ln);

			grid.store.url = grid.pc_crud.api_url +'get/' + value + '/' + PC.global.ln;
			grid.pc_crud.base_params = {
				pkey: value
			};
			grid.store.proxy.setUrl(grid.store.url);
			grid.store.proxy.url = grid.store.url;
			grid.store.reload();
		}
		return;
		//this.items.items[0].setValue(value);
	},
			
	isDirty_: function() {
		var dirty = false;
		return dirty;
	}
	
});

Ext.ComponentMgr.registerType('pc_shop_price_field', PC.ux.priceField);

