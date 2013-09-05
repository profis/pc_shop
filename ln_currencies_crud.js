if (!PC_plugin_dialog_pc_shop.tabs) {
	PC_plugin_dialog_pc_shop.tabs = {};
}

PC.plugin.pc_shop.ln_currencies_crud = Ext.extend(PC.ux.crud, {
	api_url: 'api/plugin/pc_shop/ln_currencies/',
	
	sortable: true,
	
	auto_load: false,
	reload_after_save: true,
	
	no_ln_fields: true,
	
	get_store_fields: function() {
		return [
			'id', 'c_id', 'position', 'code'
		];
	},
	
	get_store: function() {
		var store = PC.plugin.pc_shop.ln_currencies_crud.superclass.get_store.call(this);
		store.setDefaultSort('position', 'asc');
		return store;
	},
	
	get_grid_columns: function() {
		return [
			{header: Plugin.ln.config_titles.currency, dataIndex: 'code'}
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
