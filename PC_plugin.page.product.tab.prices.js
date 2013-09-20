PC.utils.localize('mod.pc_shop.product_prices', {
	lt: {
		title: 'Kainos kitomis valiutomis',
		suggested_price: 'Siųloma kaina pagal kursą'

	},
	en: {
		title: 'Prices in other currencies',
		suggested_price: 'Proposed price according to rate'

	},
	ru: {
		title: 'Цены в других валютах',
		suggested_price: 'Предлагаемая цена по курсу'
	}
});

Plugin_pc_shop_product_prices_crud = Ext.extend(PC.ux.LocalCrud, {
	api_url: 'api/plugin/pc_shop/product_prices/',
	api_url_get: 'api/plugin/pc_shop/product_prices/get/',
	
	grid_id: 'Plugin_pc_shop_product_prices_crud_grid',
	
	auto_load: false,
	
	no_ln_fields: true,
	
	//per_page: 20,
	reload_after_save: false,
	
	no_commit_after_edit: true,
	
	get_store_fields: function() {
		return [
			'id', 'price', 'c_id', 'code', 'converted_price'
		];
	},
	
	get_grid_columns: function() {
		return [
			{header: Plugin.ln.config_titles.currency, dataIndex: 'code'},
			{header: Plugin.ln.price, dataIndex: 'price', renderer: this._render_price},
			{header: this.ln.suggested_price, dataIndex: 'converted_price', renderer: this._render_converted_price, width: 300}
			
		];
	},
			
	_render_price: function(value, metaData, record, rowIndex, colIndex, store) {
		if (value == 0 || value == '' || value == null) {
			return '<img style="vertical-align: bottom;" src="images/delete.png" alt="" /> ' + PC.i18n.price_not_set;
		}
		if (parseFloat(record.data.price)) {
			return '<b>' + value + '</b>';
		}
		return value;
	},
			
	_render_converted_price: function(value, metaData, record, rowIndex, colIndex, store) {
		if (value == 0 || value == '' || value == null) {
			return '<img style="vertical-align: bottom;" src="images/delete.png" alt="" /> ' + PC.i18n.price_not_set;
		}
		if (!parseFloat(record.data.price) && parseFloat(record.data.converted_price)) {
			return '<b>' + value + '</b>';
		}
		return value;
	},
	
	_get_edit_form_fields: function() {
		return [
			{	
				_fld: 'price',
				fieldLabel: Plugin.ln.price,
				anchor: '100%',
				xtype:'textfield'
			},
			{	
				_fld: 'c_id',
				anchor: '100%',
				xtype:'hidden'
			}
		];
	},
	
	get_empty_edit_form_fields: function() {
		return this._get_edit_form_fields();
	},
	
	get_add_form_fields: function() {
		return [
			{
				ref: '_c_id', fieldLabel: Plugin.ln.config_titles.currency,
				_fld: 'c_id',
				//anchor: '100%',
				width: 200,
				xtype: 'combo',
				emptyText: ' -- ',
				//mode: 'local',
				store: new Ext.data.JsonStore({
					url: 'api/plugin/pc_shop/currencies/get_for_combo?active_only&empty&ln=' + PC.global.admin_ln,
					fields: [
						'id', 
						'name', 
					],
					idProperty: 'id',
					autoLoad: true
				}),
				displayField: 'name',
				valueField: 'id',
				editable: true,
				typeAhead: true,
				enableKeyEvents: true,
				forceSelection: true,
				triggerAction: 'all'
			
			}
		].concat(this._get_edit_form_fields());
	},
			
	get_tbar_buttons: function() {
		var buttons =  [
			this.get_button_for_edit()
		];
		return buttons;
	}
}); 
//debugger;

PC.hooks.Register('plugin/pc_shop/add_tab_for_product_', function(params) {
	params.tabs.push(new Plugin_pc_shop_product_prices_crud({
		pc_no_ln: true,
		ln: PC.i18n.mod.pc_shop.product_prices
	}));
});


PC.hooks.Register('plugin/pc_shop/load_tab_panel_for_product', function(params) {
	var grid = Ext.getCmp('Plugin_pc_shop_product_prices_crud_grid');
	if (grid) {
		grid.store.rejectChanges();
		//grid.pc_crud.mask = new Ext.LoadMask(grid.pc_crud, {msg:"Please wait..."});
		//grid.pc_crud.mask.show();
		grid.store.setBaseParam('product_id', params.itemId);
		grid.store.setBaseParam('ln', PC.global.ln);
	
		grid.store.url = grid.pc_crud.api_url +'get/' + params.itemId + '/' + PC.global.ln;
		grid.pc_crud.base_params = {
			product_id: params.itemId
		}
		grid.store.proxy.setUrl(grid.store.url);
		grid.store.proxy.url = grid.store.url;
		grid.store.reload();
	}
});
