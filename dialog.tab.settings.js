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
			},
			
			{
				_fld: 'checkout_offer_to_register',
				name: 'checkout_offer_to_register',
				fieldLabel: this.ln.checkout_offer_to_register,
				xtype: 'combo',
				mode: 'local',
				store: {
					xtype: 'arraystore',
					fields: ['value', 'title'],
					idIndex: 0,
					data: [[0, PC.i18n.no], [1, PC.i18n.yes]]
				},
				displayField: 'title',
				valueField: 'value',
				editable: false,
				forceSelection: true,
				triggerAction: 'all'
			},
			
			{	_fld: 'name',
				name: 'max_coupon_percentage',
				fieldLabel: this.ln.max_coupon_percentage,
				anchor: '100%',
				//width: 300,
				xtype: 'numberfield',
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
