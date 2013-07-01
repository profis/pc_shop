if (!PC_plugin_dialog_pc_shop.tabs) {
	PC_plugin_dialog_pc_shop.tabs = {};
}

/*
PC_shop_attribute_categories_crud = PC.ux.crud;



PC_shop_attribute_categories_crud.prototype.init = function() {
	this.ln = {
		title: 'Attributu kategorijos'
	};
}

*/
//debugger;
//PC.plugin.pc_shop.crud_attribute_categories = Ext.extend(PC.ux.crud);

PC.plugin.pc_shop.crud_manufacturers = Ext.extend(PC.ux.crud, {
	api_url: 'api/plugin/pc_shop/manufacturers/',
	
	no_ln_fields: true,
	
	get_store_fields: function() {
		return [
			'id', 'name'
		];
	},
	
	get_grid_columns: function() {
		return [
			//dialog.expander,
			{header: this.ln.name, dataIndex: 'name', width: 150}		
		];
	},
	
	get_add_form_fields: function() {
		return [
			{	_fld: 'name',
				fieldLabel: this.ln.name,
				anchor: '100%',
				xtype: 'textfield',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: '',
				allowBlank: false
			}
		];
	}
}); 
//debugger;
