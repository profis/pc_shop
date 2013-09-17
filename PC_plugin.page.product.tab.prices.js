PC.utils.localize('mod.pc_shop.product_prices', {
	lt: {
		title: 'Kainos kitomis valiutomis'

	},
	en: {
		title: 'Prices in other currencies'

	},
	ru: {
		title: 'Цены в других валютах'
	}
});

Plugin_pc_shop_product_prices_crud = Ext.extend(PC.ux.crud, {
	api_url: 'api/plugin/pc_shop/product_prices/',
	
	grid_id: 'Plugin_pc_shop_product_prices_crud_grid',
	
	auto_load: false,
	
	no_ln_fields: true,
	
	//per_page: 20,
	reload_after_save: true,
	
	get_store_fields: function() {
		return [
			'id', 'price', 'c_id', 'code', 'converted_price'
		];
	},
	
	
	get_grid_columns: function() {
		return [
			{header: Plugin.ln.config_titles.currency, dataIndex: 'code'},
			{header: Plugin.ln.price, dataIndex: 'price', renderer: this._render_price},
			{header: 'Kaina pagal kursą', dataIndex: 'converted_price', renderer: this._render_converted_price}
			
		];
	},
			
	_render_price: function(value, metaData, record, rowIndex, colIndex, store) {
		if (parseFloat(record.data.price)) {
			return '<b>' + value + '</b>';
		}
		return value;
	},
			
	_render_converted_price: function(value, metaData, record, rowIndex, colIndex, store) {
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
