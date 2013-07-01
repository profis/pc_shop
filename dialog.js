PC_plugin_dialog_pc_shop = {}

PC_plugin_dialog_pc_shop.js_factory = {
	
	handler_for_import_file_selected: function(field, val, old) {
		if (val.length) {
			Ext.MessageBox.show({
				title: PC.i18n.msg.title.loading,
				msg: PC.i18n.msg.loading,
				width: 300,
				wait: true,
				waitConfig: {interval:100}
			});
			var form = Ext.getCmp('pc_shop_import_products_form').getForm();
			form.submit({
				//params: {categoryId: PC.editors.Data.data.id},
				params: form.getFieldValues(),
				
				success: function(form, action) {
					if (action.result.success === true) {
						Ext.getCmp('pc_shop_import_products_grid')._load_data(action.result.data, action.result.columns);
						//PC_plugin_dialog_pc_shop.import_attributes_store._data = action.result.data;
						Ext.Msg.hide();
						return;
					} 
					Ext.Msg.alert('Failure', (action.result.error!=undefined?action.result.error:'Unknown error'));
				},
				failure: function(form, action) {
					//ed._import._sheet.disable();
					switch (action.failureType) {
						case Ext.form.Action.CLIENT_INVALID:
							Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
							break;
						case Ext.form.Action.CONNECT_FAILURE:
							Ext.Msg.alert('Failure', 'Ajax communication failed');
							break;
						case Ext.form.Action.SERVER_INVALID:
						   Ext.Msg.alert('Failure', action.result.msg);
						default:
							Ext.Msg.alert('Failure', (action.result.error!=undefined?action.result.error:'Unknown error'));
				   }
				}
			});
		}
	},
	
	handler_for_clear_data: function() {
		var ed = PC.editors.Get();
		ed._pc_shop_automera_category_import._import_file.setValue('');
		if (ed._pc_shop_automera_category_import._import_file.fileInput) {
			if (ed._pc_shop_automera_category_import._import_file.fileInput.dom) {
				ed._pc_shop_automera_category_import._import_file.fileInput.dom.value = '';
			}
		}
		PC_plugin_dialog_pc_shop.import_attributes_store.store.removeAll()
	},
		
	get_store_for_attributes: function(config) {
		var data_store = new Ext.data.ArrayStore({
			fields: ['attributes', 'price'],
			listeners: {
				load: function(store, records, options){
					store.commitChanges();
					store._deletedFields = [];
				},
				remove: function(store, record, index){
					if (record.data.id != null) store._deletedFields.push(record);
				}
			},
			_deletedFields: [],
			_getDeletedFields: function(){
				return this._deletedFields;
			}//,
			//reader: new PC.ux.DynamicArrayReader()
		});
		if (config) {
			Ext.apply(data_store, config);
		}
		return data_store;
	},
	handler_for_load_data_to_grid: function(d, columns) {
		//debugger;
		//fill new store fields & grid columns configuration
		
		var store = this.store;
		store.fields.clear();

		/*Ext.iterate(d, function(id, name){
			store.fields.add(new Ext.data.Field({
				_id: id,
				name: id
			}));
		});*/
		
		Ext.iterate(columns, function(column, index){
			//debugger;
			store.fields.add(new Ext.data.Field({
				_id: index,
				name: column.dataIndex,
				mapping: column.dataIndex
			}));
		});
		
		this.reconfigure(store, new Ext.grid.ColumnModel(columns));
		store.loadData(d);
	},
	
	handler_for_import_confirm: function() {
		Ext.MessageBox.show({
			title: Plugin_pc_shop.ln._import.title_confirm_confirm,
			msg: Plugin_pc_shop.ln._import.msg_confirm_confirm,
			buttons: Ext.MessageBox.YESNO,
			icon: Ext.MessageBox.QUESTION,
			fn: PC_plugin_dialog_pc_shop.js_factory.handler_for_import_confirm_confirm
		});
	},
	
	handler_for_import_confirm_confirm: function(r) {
		if (r == 'yes') {
			Ext.MessageBox.show({
				title: PC.i18n.msg.title.loading,
				msg: PC.i18n.msg.loading,
				width: 300,
				wait: true,
				waitConfig: {interval:100}
			});
			var ed = PC.editors.Get();
			var category_node_id = Ext.getCmp('pc_shop_import_products_category_hidden').getValue();
			Ext.Ajax.request({
				url: Plugin_pc_shop.api.Admin +'import_products/confirm',
				method: 'POST',
				params: {
					category_id: category_node_id,
					product_import_method: Ext.getCmp('pc_shop_import_products_product_import_method').getValue(),
					products: Ext.encode(Ext.pluck(PC_plugin_dialog_pc_shop.import_attributes_store.data.items, 'data')),
					missing_products_strategy: null
				},
				success: function(result){
					var json = Ext.util.JSON.decode(result.responseText);
					if (json) if (json.success) {
						//ed._import._clearData();
						Ext.MessageBox.show({
							title: Plugin_pc_shop.ln._import.title_confirm_success,
							msg: Plugin_pc_shop.ln._import.msg_confirm_success + json.imported,// + '(inserted: ' + json.inserted + 'updated:' + json.updated + ')',
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.INFO
						});
						var imported_node = PC.tree.component.getNodeById(category_node_id);
						if (imported_node) {
							imported_node.reload();
						}
						return true;
					}
					Ext.MessageBox.show({
						title: 'Error',
						msg: (json.error!=undefined?Plugin_pc_shop.ln._import.errors[json.error]:'error'),
						buttons: Ext.MessageBox.OK,
						icon: Ext.MessageBox.ERROR
					});
				},
				failure: function(){
					Ext.MessageBox.show({
						title: 'Error',
						msg: 'error',
						buttons: Ext.MessageBox.OK,
						icon: Ext.MessageBox.ERROR
					});
				}
			});
		}
	}

	
	
}


PC_plugin_dialog_pc_shop.view_factory = {
	
	get_tab_for_import: function() {
	   if (PC_plugin_dialog_pc_shop.import_tab) {
		   return PC_plugin_dialog_pc_shop.import_tab;
	   }
	   
	   PC_plugin_dialog_pc_shop.import_attributes_store = PC_plugin_dialog_pc_shop.js_factory.get_store_for_attributes();
	   
	   	var tab = new Ext.Panel({
			title: PC.i18n.mod.pc_shop.tab.tab_import,
			//ref: '_pc_shop_automera_category_import',
						
			buttonAlign: 'center',
			bbarCfg: {
				buttonAlign: 'center'
			},
			bbar: [
				{	
					xtype: 'button', icon: 'images/delete.png',
					text: Plugin_pc_shop.ln.btn_clear,
					handler: function(field){
						Ext.getCmp('pc_shop_import_products_grid').store.removeAll();
						var import_file = Ext.getCmp('pc_shop_import_products_import_file');
						import_file.setValue('');
						if (import_file.fileInput) {
							if (import_file.fileInput.dom) {
								import_file.fileInput.dom.value = '';
							}
						}
						/*Ext.MessageBox.show({
							title: ProfisCMS.i18n.msg.title.confirm,
							msg: ln.confirm.delete_additional,
							buttons: Ext.MessageBox.YESNO,
							icon: Ext.MessageBox.QUESTION,
							fn: function(r) {
								if (r == 'yes') {
									return dialog.additional.store.removeAt(cell[0]);
								}
							}
						});*/
						//1. Empty grid
						//2. Disable grid
					}
				},


				{xtype:'tbseparator'},
				{	xtype: 'button', icon: 'images/add.png',
					text: Plugin_pc_shop.ln.btn_confirm,

					handler: PC_plugin_dialog_pc_shop.js_factory.handler_for_import_confirm
				}
			],

			layout: {
				//type: 'form' //vbox
				type: 'vbox',
				align : 'stretch'
			},
									
			items: [
				PC_plugin_dialog_pc_shop.view_factory._get_import_form(),
				PC_plugin_dialog_pc_shop.view_factory._get_import_grid(PC_plugin_dialog_pc_shop.import_attributes_store)
			],
			_clearData: PC_plugin_dialog_pc_shop.js_factory.handler_for_clear_data 
			
		});
		PC_plugin_dialog_pc_shop.import_tab = tab;
		return tab;
	   
	},
	
	_get_import_form: function() {
		var form_panel = new Ext.form.FormPanel({
			border: false,
			bodyCssClass: 'x-border-layout-ct',
			padding: 6,
			autoScroll: true,
			labelWidth: 120,
			labelAlign: 'right',
			//form config
			fileUpload: true,
			method: 'post',
			url: Plugin_pc_shop.api.Admin +'import_products',
			//items
			defaults: {xtype: 'textfield', anchor: '98%'},
			id: 'pc_shop_import_products_form',
			height: 110,
			//flex: 2,
			frame: true,
			items: [
				{
					xtype: 'hidden',
					id: 'pc_shop_import_products_category_hidden'
				},
				
				{	
					//fieldLabel: PC.i18n.menu.shortcut_to.replace(/\s/, '&nbsp;'),
					fieldLabel: Plugin_pc_shop.ln._import.label_category,
					//id: 'db_fld_redirect_container',
					xtype: 'container',
					layout: 'fit',
					width: 400,
					items: PC.view_factory.get_shortcut_field({
						id: 'pc_shop_import_products_category',
						width: 400,
						value: Plugin_pc_shop.ln._import.explain_category
					}, 
					{
						callback: function(value, lang, node_id){
							Ext.getCmp('pc_shop_import_products_category_hidden').setValue(node_id);
						},
						page_selector_params: {
							tree_params : {
								additionalBaseParams : {
									controller: 'pc_shop',
									pc_shop: {
										categories_only: true
									},
									default_controller: 'pc_shop'
								}
							},
							return_type: 'name_path'
						}
					})
				},
				PC_plugin_dialog_pc_shop.view_factory._get_import_method_field(),
				PC_plugin_dialog_pc_shop.view_factory._get_file_upload_field(),
			]
		});
		//debugger;
		return form_panel;
	},
	
	_get_file_upload_field: function() {
		return new Ext.ux.form.FileUploadField({
			ref: '_pc_shop_automera_category_import_file',
			id: 'pc_shop_import_products_import_file',
			name: 'file',
			fieldLabel: Plugin_pc_shop.ln._import.label_file,
			//renderTo: 'fi-basic',
			width: 400,
			listeners: {
				fileselected: PC_plugin_dialog_pc_shop.js_factory.handler_for_import_file_selected
			}
		});
	},
	
	_get_import_method_field: function() {
		var store = {
			xtype: 'arraystore',
			fields: ['value', 'name'],
			idIndex: 0,
			data: []
		};
		var default_value = false;
		Ext.iterate(PC.plugin.pc_shop.product_import_methods, function(key, method) {
			default_value = method.code;
			var title = method.code;
			if (method.title_js_var) {
				title = eval(method.title_js_var);
			}
			store.data.push([method.code, title]);
		});
		
		var combo_field = {	
			fieldLabel: Plugin_pc_shop.ln._import.import_method,
			//anchor: '95%',
			name: 'product_import_method',
			id: 'pc_shop_import_products_product_import_method',
			xtype: 'combo',
			mode: 'local',
			store: store,
			displayField: 'name',
			valueField: 'value',
			editable: false,
			forceSelection: true,
			triggerAction: 'all',
			value: null
		};
		if (default_value) {
			combo_field.value = default_value;
		}
		return combo_field;
	},
	
	_get_import_grid: function(data_store) {
		var grid = new Ext.grid.EditorGridPanel({
			flex:2,
			id: 'pc_shop_import_products_grid',
			fileUpload: true,
			bodyCssClass: 'x-border-layout-ct',
			store: data_store,
			border: false,
			//height: 'auto',
			style: 'border: 2px solid #D2DCEB;',
			columns: [{header: '...'}],
			//autoExpandColumn: 'pc_additional_field_value',
			bbar: new Ext.PagingToolbar({
				store: data_store,
				displayInfo: true,
				pageSize: 50,
				prependButtons: true
			}),
			_data: {},
			_load_data: PC_plugin_dialog_pc_shop.js_factory.handler_for_load_data_to_grid
		});
		return grid;
	}
	
};

