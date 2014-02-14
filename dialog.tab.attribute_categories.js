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

PC.plugin.pc_shop.crud_attribute_categories = Ext.extend(PC.ux.crud, {
	api_url: 'api/plugin/pc_shop/attribute_categories/',
	
	adjust_multiln_params: function(multiln_params) {
		multiln_params.labelAlign = 'top';
	},
	
	get_store_fields: function() {
		return [
				'id', 'ref', 'names',
				{name: 'name', mapping: 'names', convert: function(names, n){return PC.utils.extractName(names);}}
		];
	},
	
	get_grid_columns: function() {
		return [
			//dialog.expander,
			{header: this.ln.name, dataIndex: 'name', width: 150},
			{header: this.ln.ref, dataIndex: 'ref', width: 60}
		];
	},
	
	get_add_form_fields: function() {
		return [
			{	_fld: 'ref',
				fieldLabel: this.ln.ref,
				anchor: '100%',
				xtype: 'textfield',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: ''
			}
		];
	}
}); 
//debugger;
