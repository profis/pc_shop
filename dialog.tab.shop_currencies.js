

PC.plugin.pc_shop.shop_currencies = Ext.extend(Ext.Panel, {
	layout: 'form',
	//layout: {
	//	type: 'vbox',
	//	align: 'stretch'
	//},
	labelAlign: 'top',
	padding: 6,
	border: false,
	bodyCssClass: 'x-border-layout-ct',
	constructor: function(config) {
		if (!config) {
			config = {};
		}
	
		config = Ext.apply({
			items: this.get_items()
        }, config);

        PC.ux.LocalCrud.superclass.constructor.call(this, config);
    },
	
	get_items: function() {
		var items = [];
		items.push({ref: '_base_currency', fieldLabel: Plugin.ln.config_titles.base_currency,
			//anchor: '100%',
			width: 250,
			xtype: 'combo',
			emptyText: ' -- ',
			//mode: 'local',
			store: new Ext.data.JsonStore({
				url: 'api/plugin/pc_shop/currencies/get_for_combo?empty&ln=' + PC.global.admin_ln,
				fields: [
					'code', 
					'name', 
				],
				idProperty: 'code',
				autoLoad: true
			}),
			displayField: 'name',
			valueField: 'code',
			value: PC.plugin.pc_shop.base_currency,
			editable: true,
			typeAhead: true,
			enableKeyEvents: true,
			forceSelection: true,
			triggerAction: 'all',
			
			listeners: {
				select: Ext.createDelegate(function(combo) {
					if (PC.plugin.pc_shop.base_currency == combo.getValue()) {
						return;
					}
					Ext.MessageBox.show({
						buttons: Ext.MessageBox.YESNO,
						//title: this.ln._delete.confirm_title,
						msg: PC.i18n.mod.pc_shop.base_currency_change_confirm,
						icon: Ext.MessageBox.WARNING,
						maxWidth: 320,
						fn: Ext.createDelegate(function (btn_id) {
							if (btn_id == 'yes') {
								PC.plugin.pc_shop.base_currency = this._base_currency.getValue();
								Ext.Ajax.request({
									url:  'api/plugin/config/config/save/pc_shop',
									method: 'POST',
									params: {controller: 'pc_shop', data: Ext.util.JSON.encode({
										currency: PC.plugin.pc_shop.base_currency
									})},
									callback: Ext.createDelegate(function () {
										var grid = Ext.getCmp('Plugin_pc_shop_currency_rates_crud_grid');
										if (grid) {
											grid.store.reload();
										}
										this._ln_currencies.store.reload();
										var product_price_field = Ext.getCmp('pc_shop_price_in_base_currency');
										if (product_price_field) {
											product_price_field.label.update(PC.i18n.mod.pc_shop.price + ' (' + PC.plugin.pc_shop.base_currency + ')');
										}
										var product_prices_crud = Ext.getCmp('pc_shop_product_prices_crud');
										if (product_prices_crud) {
											product_prices_crud.store.reload();
										}
									}, this)
								});
							}
							else {
								this._base_currency.setValue(PC.plugin.pc_shop.base_currency);
							}
						}, this)
					});
					
				}, this)
			}

		});
		
		var ln_currencies_crud = new PC.plugin.pc_shop.ln_currencies_crud({
			fieldLabel: PC.i18n.mod.pc_shop.ln_currency_list,
			height: 300,
			ref: '_ln_currencies'
		});
		
		items.push(new PC.ux.LnCombo({
			ref: '../_language',
			fieldLabel: PC.i18n.language,
			labelStyle: '',
			anchor: '49%',
			store_data: Get_all_site_languages(),
			ln_currencies_crud: ln_currencies_crud,
			listeners: {
				select: function(combo) {
					combo._set_value(combo);
				},
				render: function(combo) {
					combo._set_value(combo);
				}
			},
			_set_value: function(combo) {
				var ln = combo.getValue();
				var grid = combo.ln_currencies_crud.grid;
				
				grid.store.setBaseParam('ln', ln);
				grid.store.setBaseParam('start', 0);

				if (grid.pc_crud._paging) {
					grid.pc_crud._paging.changePage(1);
				}

				grid.store.url = grid.pc_crud.api_url +'get/' + ln;
				grid.pc_crud.base_params = {
					ln: ln
				}
				grid.store.proxy.setUrl(grid.store.url);
				grid.store.proxy.url = grid.store.url;
				grid.store.reload();
			}
		}));
		
		items.push(ln_currencies_crud);
		
		return items;
	}
	
	//items_: [
	//	{fieldLabel: 'Testas', ref: '../../_emails',xtype: 'textarea', height: 32}
	//]
	/*
	items: [
		new Ext.form.FormPanel({
			flex: 1,
			layout: 'form',
			padding: 6,
			border: false,
			bodyCssClass: 'x-border-layout-ct',
			labelAlign: 'top',
			items: [
				new PC.ux.LnCombo({
					ref: '../_language',
					fieldLabel: PC.i18n.language,
					anchor: '49%',
					store_data: Get_all_site_languages()
				})
			]
		})
	]
	*/
	
}); 
//debugger;
