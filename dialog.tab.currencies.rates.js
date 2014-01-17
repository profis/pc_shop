

PC.plugin.pc_shop.shop_currency_rates = Ext.extend(PC.ux.LocalCrud, {
	api_url: 'api/plugin/pc_shop/currency_rates/',
	api_url_get: 'api/plugin/pc_shop/currency_rates/get/',
	api_url_import: 'api/plugin/pc_shop/currency_rates/import/',
	
	no_ln_fields: true,
	
	no_commit_after_edit: true,
	
	auto_load: true,
	id_property: 'c_id',
	
	grid_id: 'Plugin_pc_shop_currency_rates_crud_grid',
	
	constructor: function(config) {
		config.bbar = this.get_bbar();
		PC.plugin.pc_shop.shop_currency_rates.superclass.constructor.call(this, config);
	},
	
	get_store_fields: function() {
		return [
			'c_id', 'code', 'relation', 'rate'
		];
	},
	
	_currency_rate: function (value) {
		if (value == 0 || value == '' || value == null) {
			return '<img style="vertical-align: bottom;" src="images/delete.png" alt="" /> ' + PC.i18n.not_set;
		}
		return value;
	},
	
	get_grid_columns: function() {
		return [
			//dialog.expander,
			{header: '', dataIndex: 'relation'},
			{header: PC.i18n.mod.pc_shop.currency_rate, dataIndex: 'rate', renderer: this._currency_rate}		
		];
	},
	
	get_add_form_fields: function() {
		return [
			{	_fld: 'rate',
				ref: '_rate',
				fieldLabel: PC.i18n.mod.pc_shop.currency_rate,
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
		
	adjust_multiln_params: function(multiln_params) {
		multiln_params.save_button_label = PC.i18n.change;
		multiln_params.pre_buttons = [
			{	
				text: PC.i18n.mod.pc_shop.import_rate,
				icon: 'images/money_euro.png',
				handler: Ext.createDelegate (function() {
					this.button_handler_for_import_single(this.selected_record.data.code);
				}, this)
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
			
	button_handler_for_import_single: function(currency_code) {
		Ext.Ajax.request({
			url: this.api_url_import + currency_code,
			method: 'POST',
			callback: Ext.createDelegate(function(opts, success, response) {
				if (success && response.responseText) {
					try {
						var data = Ext.decode(response.responseText);
						if (data.success) {
							var w = PC.dialog.styles.multilnedit;
							if (data.data.rate) {
								w._rate.setValue(data.data.rate);
							}
							return;
						}
						else {
							error = data.error;
							if (this.ln.error && this.ln.error[error]) {
								error = this.ln.error[error];
							}
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
			}, this)
		});
	},	
			
	get_button_for_import: function() {
		return {	
			ref: '../action_import',
			text: PC.i18n.mod.pc_shop.import_rates,
			icon: 'images/money_euro.png',
			handler: Ext.createDelegate(this.button_handler_for_import, this)
		};
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
					if (this.ln.error && this.ln.error[error]) {
						error = this.ln.error[error];
					}
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
	
	ajax_sync_success_respone_handler: function(data) {
		PC.plugin.pc_shop.shop_currency_rates.superclass.ajax_sync_success_respone_handler.call(this, data);
		var grid = Ext.getCmp('Plugin_pc_shop_ln_currencies_crud_grid');
		if (grid) {
			grid.store.reload();
		}
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
