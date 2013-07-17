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

PC.plugin.pc_shop.settings = Ext.extend(PC.ux.configPanel, {
	controller: 'pc_shop',
	
		
	get_form_fields: function() {
		return [
			{	_fld: 'name',
				name: 'new_order_email_admin',
				fieldLabel: this.ln.new_order_email_admin,
				anchor: '100%',
				//width: 300,
				xtype: 'textfield',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: '',
				allowBlank: true
			}
		];
	}
}); 
//debugger;
