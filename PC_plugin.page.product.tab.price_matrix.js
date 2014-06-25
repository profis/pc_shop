PC.utils.localize('mod.pc_shop.product_price_matrix', {
	lt: {
		title: 'Kainų matrica',
		suggested_price: 'Siųloma kaina pagal kursą',
		attr_1: 'Pirmas atributas',
		attr_2: 'Antras atributas',
		col_attributes: 'Atributų reikšmės'

	},
	en: {
		title: 'Price matrix',
		suggested_price: 'Proposed price according to rate'

	},
	ru: {
		title: 'Матрица цен',
		suggested_price: 'Предлагаемая цена по курсу'
	}
});

Plugin_pc_shop_product_price_matrix_crud = Ext.extend(PC.ux.LocalCrud, {
	api_url: 'api/plugin/pc_shop/product_prices/',
	api_url_get: 'api/plugin/pc_shop/product_prices/get/',
	
	grid_id: 'Plugin_pc_shop_product_price_matrix_crud_grid',
	
	auto_load: false,
	
	no_ln_fields: true,
	
	row_editing: true,
	
	//per_page: 20,
	reload_after_save: false,
	
	no_commit_after_edit: true,
	
	_create_store: function(config) {
		//delete config['root'];
		//delete config['idProperty'];
		return new Ext.data.ArrayStore(config);
	},
	
	get_store_fields: function() {
		return [
			'id', 'attributes_shit'
		];
	},
	
	get_grid_columns: function() {
		return [
			{header: this.ln.col_attributes, dataIndex: 'attributes'}//,
			//{header: Plugin.ln.price, dataIndex: 'price', renderer: this._render_price},
			//{header: this.ln.suggested_price, dataIndex: 'converted_price', renderer: this._render_converted_price, width: 300}
			
		];
	},
	
	_get_edit_form_fields: function() {
		return [
			{	
				_fld: 'price',
				fieldLabel: Plugin.ln.price,
				anchor: '100%',
				xtype:'textfield'
			},
			{	
				_fld: 'c_id',
				anchor: '100%',
				xtype:'hidden'
			}
		];
	},
	
	get_empty_edit_form_fields: function() {
		return this._get_edit_form_fields();
	},
	
	get_add_form_fields: function() {
		return [
			{
				ref: '_c_id', fieldLabel: Plugin.ln.config_titles.currency,
				_fld: 'c_id',
				//anchor: '100%',
				width: 200,
				xtype: 'combo',
				emptyText: ' -- ',
				//mode: 'local',
				store: new Ext.data.JsonStore({
					url: 'api/plugin/pc_shop/currencies/get_for_combo?active_only&empty&ln=' + PC.global.admin_ln,
					fields: [
						'id', 
						'name'
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
		].concat(this._get_edit_form_fields());
	},
			
	get_tbar_buttons: function() {
		var buttons =  [
			this.get_button_for_sync()
		];
		return buttons;
	},
	
	
	get_tbar_items: function() {
		var items = Plugin_pc_shop_product_price_matrix_crud.superclass.get_tbar_items.call(this);
		items.push({xtype:'tbtext', text: this.ln.attr_1 + ':', style:'margin:0 2px;'});
		
		items.push({	xtype: 'combo', mode: 'local',
			ref: '../_attribute_id_1',
			store: PC.plugin.pc_shop.attributes.Store,
			valueField: 'id',
			displayField: 'nameClean',
			triggerAction: 'all',
			//tpl: '<tpl for="."><div class="x-combo-list-item">{'+ this.displayField +'}</div></tpl>',
			tpl: '<tpl for="."><div class="x-combo-list-item">{[PC.utils.extractName(values.names)]}</div></tpl>',
			editable: false,
			listeners: {
				change: function(field, value, ovalue) {},
				select: Ext.createDelegate(this.attribute_changed, this),
				expand: function(field){
					PC.plugin.pc_shop.attributes.Store.filter('is_category_attribute', (PC.editors.Current[1]=='category'?'1':'0'));
					PC.plugin.pc_shop.attributes.Store.filter('is_custom', '0');
				}
			}
		});
		
		items.push({xtype:'tbtext', text: this.ln.attr_2 + ':', style:'margin:0 2px;'});
		
		items.push({	xtype: 'combo', mode: 'local',
			ref: '../_attribute_id_2',
			store: PC.plugin.pc_shop.attributes.Store,
			valueField: 'id',
			displayField: 'nameClean',
			triggerAction: 'all',
			//tpl: '<tpl for="."><div class="x-combo-list-item">{'+ this.displayField +'}</div></tpl>',
			tpl: '<tpl for="."><div class="x-combo-list-item">{[PC.utils.extractName(values.names)]}</div></tpl>',
			editable: false,
			listeners: {
				change: function(field, value, ovalue) {},
				select: Ext.createDelegate(this.attribute_changed, this),
				expand: function(field){
					PC.plugin.pc_shop.attributes.Store.filter('is_category_attribute', (PC.editors.Current[1]=='category'?'1':'0'));
					PC.plugin.pc_shop.attributes.Store.filter('is_custom', '0');
				}
			}
		});
		
		return items;
	},
	
	attribute_changed: function(cb, rec, idx) {
		var val_1 = this._attribute_id_1.getValue();
		var val_2 = this._attribute_id_2.getValue();
		var store_item_1 = PC.plugin.pc_shop.attributes.Store.getById(val_1);
		var store_item_2 = PC.plugin.pc_shop.attributes.Store.getById(val_2);
		if (val_1 != val_2 && store_item_1 && store_item_2) {
			
			var data = [];
			var columns = [];
			columns.push({
				header: this.ln.col_attributes,
				dataIndex: 'attributes'
			});
			
			var data_row = {};
			
			///*
			Ext.iterate(store_item_2.data.values, function(index, value){
				var unique_id = 'attr_value_' + index;
				columns.push({
					header: PC.utils.extractName(value),
					dataIndex: unique_id,
					editor: {	
						_fld: unique_id,
						ref: '_' + unique_id,
						anchor: '100%',
						xtype: 'textfield',
						mode: 'local',
						//editable: false,
						forceSelection: true,
						//value: '',
						allowBlank: true
					}
				});
				data_row['attr_value_' + index] = '12';
			});
			//*/

			Ext.iterate(store_item_1.data.values, function(index, value){
				data.push(Ext.apply({
					attributes: PC.utils.extractName(value),
					id: 0
				}, data_row));
			});

			/*
			Ext.iterate(d, function(id, name){
				store.fields.add(new Ext.data.Field({
					_id: id,
					name: id
				}));
			});
			*/
			
			
			
			var store = this.store;
			//*
			store.fields.clear();
			store.fields.add(new Ext.data.Field({
				name: 'id',
				dataIndex: 'id',
				mapping: 'id'
			}));

			Ext.iterate(columns, function(column, index){
				store.fields.add(new Ext.data.Field({
					_id: index,
					//name: column.header,
					name: column.dataIndex,
					mapping: column.dataIndex
				}));
			});
			//*/
			
			
			this.grid.reconfigure(store, new Ext.grid.ColumnModel(columns));
			debugger;
			//store.loadData(data);
			store.loadData({list: data});
		}
	
	},
	
	get_tbar_filters_: function() {
		this.tbar_filter_refs = [
			'_attr_1'
		];
		return [
			{xtype:'tbtext', text: this.ln.search_label + ':', style:'margin:0 2px;'},
			{	
				xtype:'tbtext',
				text: this.ln.search_id,
				style:'margin:0 2px;'
			},
			{	
				ref: '../_order_id',
				xtype:'textfield',
				_filter_name: '_attr_1',
				width: 55
			}
		];
	}
}); 
//debugger;

PC.hooks.Register('plugin/pc_shop/add_tab_for_product_', function(params) {
	params.tabs.push(new Plugin_pc_shop_product_price_matrix_crud({
		pc_no_ln: true,
		ln: PC.i18n.mod.pc_shop.product_price_matrix
	}));
});


PC.hooks.Register('plugin/pc_shop/load_tab_panel_for_product_', function(params) {
	return;
	var grid = Ext.getCmp('Plugin_pc_shop_product_price_matrix_crud_grid');
	if (grid) {
		grid.store.rejectChanges();
		//grid.pc_crud.mask = new Ext.LoadMask(grid.pc_crud, {msg:"Please wait..."});
		//grid.pc_crud.mask.show();
		grid.store.setBaseParam('product_id', params.itemId);
		grid.store.setBaseParam('ln', PC.global.ln);
	
		grid.store.url = grid.pc_crud.api_url +'get/' + params.itemId + '/' + PC.global.ln;
		grid.pc_crud.base_params = {
			product_id: params.itemId
		};
		grid.store.proxy.setUrl(grid.store.url);
		grid.store.proxy.url = grid.store.url;
		grid.store.reload();
	}
});
