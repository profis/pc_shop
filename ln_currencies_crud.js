if (!PC_plugin_dialog_pc_shop.tabs) {
	PC_plugin_dialog_pc_shop.tabs = {};
}

PC.plugin.pc_shop.ln_currencies_crud = Ext.extend(PC.ux.crud, {
	api_url: 'api/plugin/pc_shop/ln_currencies/',
	
	sortable: true,
	
	auto_load: false,
	reload_after_save: true,
	
	no_ln_fields: true,
	
	grid_id: 'Plugin_pc_shop_ln_currencies_crud_grid',
	
	get_store_fields: function() {
		return [
			'id', 'c_id', 'position', 'code', 'name', 'country_name', 'rate'
		];
	},
	
	get_store: function() {
		var store = PC.plugin.pc_shop.ln_currencies_crud.superclass.get_store.call(this);
		store.setDefaultSort('position', 'asc');
		return store;
	},
	
	_currency_rate: function (value) {
		if (value == 0 || value == '' || value == null) {
			return '<img style="vertical-align: bottom;" src="images/delete.png" alt="" /> ' + PC.i18n.not_set;
		}
		return value;
	},
	
	get_grid_columns: function() {
		return [
			{header: PC.i18n.country, dataIndex: 'country_name', width: 150},
			{header: PC.i18n.name, dataIndex: 'name'},
			{header: PC.i18n.currency_code, dataIndex: 'code'},
			{header: Plugin.ln.currency_rate_set, dataIndex: 'rate', renderer: this._currency_rate}
		];
	},
	
	adjust_multiln_params: function(multiln_params) {
		multiln_params.labelAlign = 'top';
	},
	
	get_add_form_fields: function() {
		return [
			{
				ref: '_c_id', fieldLabel: Plugin.ln.config_titles.currency,
				_fld: 'c_id',
				//anchor: '100%',
				width: 250,
				xtype: 'combo',
				emptyText: ' -- ',
				//mode: 'local',
				store: new Ext.data.JsonStore({
					url: 'api/plugin/pc_shop/currencies/get_for_combo?empty&ln=' + PC.global.admin_ln,
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
			
		];
	},
			
	ajax_add_response_success_handler: function (data) {
		PC.plugin.pc_shop.ln_currencies_crud.superclass.ajax_add_response_success_handler.call(this, data);
		var grid = Ext.getCmp('Plugin_pc_shop_currency_rates_crud_grid');
		if (grid) {
			grid.store.reload();
		}
	},		
			
	get_tbar_buttons: function() {
		var buttons =  [
			this.get_button_for_add(),
			this.get_button_for_edit(),
			this.get_button_for_del()
		];
		if (this.sortable) {
			buttons.push(this.get_button_for_move_up());
			buttons.push(this.get_button_for_move_down());
		}
		return buttons;
	}
}); 
//debugger;
