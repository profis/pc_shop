PC_shop_product_tab_related = {}

PC_shop_product_tab_related.view_factory = {
		
	get_part_analogs_grid: function() {
		var part_id = 0;
		var id_data = Plugin.ParseID(Get_active_page_id());
		if (id_data) {
			if (id_data.type == 'product') {
				part_id = id_data.id;
			}
		}
		
		var auto_store = new Ext.data.JsonStore({
			id: 'pc_shop_product_page_tab_part_analogs_store',
			url: Plugin.api.Admin +'product_products/get/' + part_id + '/' + PC.global.ln,
			idProperty: 'id',
			fields: [
				'id',
				'full_name'
			],
			//autoLoad: true,
			root: 'data'
		});
		
		PC_shop_product_tab_related.part_analogs_store = auto_store;
		
		var grid_config = {
			id: 'pc_shop_product_page_tab_related_grid',
			store: auto_store,
			columns: [
				{
					header: Plugin.ln.page.product.tab.related.header_name,
					dataIndex: 'full_name'
				}
			],
			height: 400,
			
			viewConfig: {
				forceFit: true
			}
		}
		return new Ext.grid.GridPanel(grid_config);
	},
	
	get_tab_for_related_products: function (config) {
		var tab = {	
			pc_no_ln: true,
			title: Plugin.ln.page.product.tab.related.title,
			//ref: '_properties',
			//id: Plugin.editorId.Category +'_tab_properties',
			layout: 'form',
			bodyCssClass: 'x-border-layout-ct',
			padding: 6,
			labelWidth: 200,
			labelAlign: 'right',
			defaults: {
				anchor: '100%'
			},
			border: false,
			tbar: [
				{	xtype: 'button', icon: 'images/add.png',
					text: Plugin.ln.page.product.tab.related.add_related,
					handler: function(field){
						
						Ext.Ajax.request({
							url: Plugin_pc_shop.api.Admin +'product_products/get/' + PC.editors.Data.data.id + '/for_page_tree',
							method: 'POST',
							success: function(result){
								var checked_nodes = [];
								var data = Ext.util.JSON.decode(result.responseText);
								if (data && data.products) {
									checked_nodes = data.products;
								}
								var callback_ok = function(w) {
									Ext.Ajax.request({
										url: Plugin_pc_shop.api.Admin +'product_products/save',
										method: 'POST',
										params: {
											product_id: PC.editors.Data.data.id,
											products: Ext.encode(w._tree.checked_nodes)
										},
										success: function(result){
											w.close();
											PC_shop_product_tab_related.part_analogs_store.setBaseParam('product_id', PC.editors.Data.data.id);
											PC_shop_product_tab_related.part_analogs_store.setBaseParam('ln', PC.global.ln);
											PC_shop_product_tab_related.part_analogs_store.reload();
										},
										failure: function(){
											w.close();
											Ext.MessageBox.show({
												title: 'Error',
												msg: 'error',
												buttons: Ext.MessageBox.OK,
												icon: Ext.MessageBox.ERROR
											});
										}
									});
								};
								var page_selector_params = {
									tree_params : {
										additionalBaseParams : {
											controller: 'pc_shop',
											default_controller: 'pc_shop',
											pc_shop: {
												//categories_only: true
												checkbox_for: 'product'
											}
										}
									},
									enable_ok_button: true,
									callback_ok: callback_ok,
									tree_config: {
										checked_nodes: checked_nodes
									},
									title: Plugin.ln.page.product.tab.related.page_selector_title

								};
								PC.hooks.Init('plugin/pc_shop/page/product/tab/related/page_selector_params', page_selector_params);
								Show_redirect_page_window(false, page_selector_params);
							}
						});
					
					}
				},
				{	icon: 'images/delete.png',
					text: PC.i18n.del,
					handler: function(){
						var grid = Ext.getCmp('pc_shop_product_page_tab_related_grid');
						var records = grid.selModel.getSelections();
						if (!records.length) return;
						Ext.MessageBox.show({
							title: PC.i18n.msg.title.confirm,
							msg: String.format(PC.i18n.msg.confirm_delete, Plugin.ln.page.product.tab.related.selected_related_products),
							buttons: Ext.MessageBox.YESNO,
							icon: Ext.MessageBox.WARNING,
							fn: function(clicked) {
								if (clicked == 'yes') {
									var deleted = [];
									Ext.iterate(Ext.getCmp('pc_shop_product_page_tab_related_grid').selModel.getSelections(), function(rec){
										deleted.push(rec.data.id);
									});
									Ext.Ajax.request({
										url: Plugin_pc_shop.api.Admin +'product_products/delete',
										method: 'POST',
										params: {
											product_id: PC.editors.Data.data.id,
											products: Ext.encode(deleted)
										}
									});
									grid.store.remove(records);
									Ext.Msg.hide();
								}
							}
						});
					}
				}
			],
			items: [
				PC_shop_product_tab_related.view_factory.get_part_analogs_grid()
			]
		};
		
		return tab;
	}
}



PC.hooks.Register('plugin/pc_shop/add_tab_for_product', function(params) {
	//debugger;
	params.tabs.push(PC_shop_product_tab_related.view_factory.get_tab_for_related_products());
});

PC.hooks.Register('plugin/pc_shop/load_tab_panel_for_product', function(params) {
	var grid = Ext.getCmp('pc_shop_product_page_tab_related_grid');
	if (grid) {
		grid.store.setBaseParam('product_id', params.itemId);
		grid.store.setBaseParam('ln', PC.global.ln);
		grid.store.setBaseParam('start', 0);
	
		grid.store.url = Plugin.api.Admin +'product_products/get/' + params.itemId + '/' + PC.global.ln;
		grid.store.proxy.setUrl(grid.store.url);
		grid.store.proxy.url = grid.store.url;
		//grid.store.load();
		//debugger;
		grid.store.reload();
	}
});