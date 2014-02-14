PC.utils.localize('mod.pc_shop.prices', {
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

Plugin_pc_shop_prices_crud = Ext.extend(PC.ux.LocalCrud, {
	api_url: 'api/plugin/pc_shop/prices/',
	api_url_get: 'api/plugin/pc_shop/prices/get/',
	
	auto_load: false,
	
	no_ln_fields: true,
	
	//per_page: 20,
	reload_after_save: false,
	
	no_commit_after_edit: true,
	
	row_editing: true,
	
	get_default_ln: function() {
		return PC.i18n.mod.pc_shop.prices;
	},
	
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
		if (record.data.code == PC.plugin.pc_shop.base_currency) {
			return;
		}
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
				name: 'price',
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
						'name'
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
			//this.get_button_for_edit()
		];
		return buttons;
	}
}); 
