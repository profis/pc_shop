

PC.plugin.pc_shop.shop_currency_rates = Ext.extend(PC.ux.LocalCrud, {
	api_url: 'api/plugin/pc_shop/currency_rates/',
	api_url_get: 'api/plugin/pc_shop/currency_rates/get/',
	api_url_import: 'api/plugin/pc_shop/currency_rates/import/',
	
	no_ln_fields: true,
	
	no_commit_after_edit: true,
	
	auto_load: true,
	id_property: 'c_id',
	
	constructor: function(config) {
		config.bbar = this.get_bbar();
		PC.plugin.pc_shop.shop_currency_rates.superclass.constructor.call(this, config);
	},
	
	get_store_fields: function() {
		return [
			'c_id', 'code', 'relation', 'rate'
		];
	},
	
	get_grid_columns: function() {
		return [
			//dialog.expander,
			{header: '', dataIndex: 'relation'},
			{header: 'Rate', dataIndex: 'rate'}		
		];
	},
	
	get_add_form_fields: function() {
		return [
			{	_fld: 'rate',
				fieldLabel: 'Rate',
				anchor: '100%',
				xtype: 'textfield',
				mode: 'local',
				editable: false,
				forceSelection: true,
				value: '',
				allowBlank: false
			}
		];
	},
			
	get_tbar_buttons: function() {
		var buttons =  [
			this.get_button_for_edit(),
			this.get_button_for_import(),
			this.get_button_for_refresh()
		];
		return buttons;
	},
			
	get_button_for_refresh: function() {
		return {	
			ref: '../action_refresh',
			icon: 'images/refresh.gif',
			handler: Ext.createDelegate(function() {
				this.store.reload();
			}, this)
		};
	},		
			
			
	get_button_for_import: function() {
		return {	
			ref: '../action_import',
			text: 'Import rates',
			icon: 'images/money_euro.png',
			handler: Ext.createDelegate(this.button_handler_for_import, this)
		}
	},
			
	button_handler_for_import: function() {
		Ext.Ajax.request({
			url: this.api_url_import,
			method: 'POST',
			callback: Ext.createDelegate(this.ajax_import_respone_handler, this)
		});
	},
			
	ajax_import_respone_handler: function(opts, success, response) {
		if (success && response.responseText) {
			try {
				var data = Ext.decode(response.responseText);
				if (data.success) {
					this.ajax_import_success_respone_handler.defer(0, this, [data]);
					return;
				}
				else {
					error = data.error;
				}
			}
			catch(e) {
				var error = this.ln.error.json;
			};
		}
		else var error = this.ln.error.connection;
		Ext.MessageBox.show({
			title: PC.i18n.error,
			msg: (error?'<b>'+ error +'</b><br />':''),
			buttons: Ext.MessageBox.OK,
			icon: Ext.MessageBox.ERROR
		});
	},
			
	ajax_import_success_respone_handler: function(data) {
		Ext.iterate(data.data, function(currency) {
			var record = this.store.getById(currency.c_id);
			if (record) {
				record.set('rate', currency.rate);
			}
		}, this);
	},
	
	buttonAlign: 'left',		
	
	get_bbar: function() {
	
		return [
			{	text: PC.i18n.save,
				iconCls: 'icon-save',
				ref: '../../_btn_save',

				scope: this,
				handler: this.sync_grid
			}
		];
	}
}); 
//debugger;
