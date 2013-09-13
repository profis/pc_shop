if (!PC_plugin_dialog_pc_shop.tabs) {
	PC_plugin_dialog_pc_shop.tabs = {};
}

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
