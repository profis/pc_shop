<?php
/** ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
$cfg['core']['no_login_form'] = true;
require_once '../../admin/admin.php';
$site->Identify();
if (isset($_GET['ln'])) $site->Set_language($_GET['ln']);

$apiUrl = $cfg['url']['base'].'admin/api/plugin/'.$plugins->Get_currently_parsing().'/';
$pluginUrl = $cfg['url']['base'].$cfg['directories']['plugins'].'/'.$plugins->Get_currently_parsing().'/';

$mod['name'] = 'Shop / Orders';
$mod['onclick'] = 'mod_pc_shop_click()';
$mod['priority'] = 100;
?>
<style type="text/css"></style>
<script type="text/javascript">
	
<?php
	$js_files = array(
		'dialog.ln.js',
		'dialog.js',
		'dialog.tab.orders.js',
		'dialog.tab.attribute_categories.js',
		'dialog.tab.manufacturers.js',
		'dialog.tab.delivery_options.js',
		'dialog.tab.payment_options.js',
		'dialog.tab.settings.js'
	);
	foreach ($js_files as $js_file) {
		if (@file_exists($js_file)) {
			include $js_file;
			echo "
";
		}
	}
?>

Ext.namespace('PC.plugins');

var plugin_title = PC.i18n.mod.pc_shop.name;

var hook_params = {};
PC.hooks.Init('plugin/pc_shop/titles', hook_params);
if (hook_params.titles) {
	Ext.apply(PC.i18n.mod.pc_shop, hook_params.titles);
}

<?php

	$product_import_methods = array();
	$core->Init_hooks('plugin/pc_shop/import-products/register-import-method', array(
		'import_methods'=> &$product_import_methods,
	));
?>	

	//Ext.ns('PC');
	PC.plugin.pc_shop.product_import_methods = <?php  echo json_encode($product_import_methods) ?>;




function mod_pc_shop_click() {
	PC.plugin.pc_shop.dialog = {};
	var dialog = PC.plugin.pc_shop.dialog;
	var ln = PC.i18n.mod.pc_shop;
	var initial_date_from = '';
	var initial_date_to = '';
	
	dialog.Initial_site_value = PC.global.site;
	
	if (dialog.w) {
		dialog.w.show();
		return;
	}
	
	var pluginUrl = '<?php echo $pluginUrl; ?>';
	var apiUrl = '<?php echo $apiUrl; ?>';
	
	var re = new Ext.ux.grid.RowEditor({
		saveText: 'OK',
		clicksToEdit: 2,
		listeners: {
			afteredit: function(editor, changes, record, a3) {
				Ext.Ajax.request({
					url: apiUrl +'orders/edit',
					params: {
						id: record.id,
						changes: Ext.util.JSON.encode(changes)
					},
					method: 'POST',
					callback: function(opts, success, rspns) {
						if (success && rspns.responseText) {
							try {
								if (rspns.responseText == 'errors') {
									//alert('errors occured');
								}
								else {
									dialog.store.reload();
									return; // OK
								}
							} catch(e) {};
						}
					}
				});
			}
		}
	});
	
	dialog.selectionChange = function(selModel){
		var del = selModel.grid.action_delete;
		if (!del && selModel.grid.button_container_id) {
			var button_container = Ext.getCmp(selModel.grid.button_container_id);
			if (button_container) {
				del = button_container.action_delete;
			}
		}
		if (del == undefined) return;
		var selected = selModel.getSelections();
		if (selected.length) {
			del.enable();
		}
		else del.disable();
	}
	
	dialog.is_paid_icon = function(is_paid, n) {
		if (parseInt(is_paid)) {
			return PC.i18n.yes;
			//return '<img src="images/tick.png" alt="" />';
		}
		return PC.i18n.no;
		//return '<img src="images/delete.png" alt="" />';
	}
	
	
	dialog.expander = new Ext.ux.grid.RowExpander({
		tpl_ : new Ext.XTemplate(

		),
		autoExpandColumn: 'items',
		expandOnDblClick: false
	});
	
	dialog.Get_status_icon = function(confirmed) {
		if (confirmed) var image = 'tick.png';
		else var image = 'hourglass.png';
		return '<img src="images/'+ image +'" alt="" />';
	}
	
	dialog.FormatDate = function(date){
		return new Date(date*1000).format('Y-m-d H:i');
	}
	
	dialog.attr_category_store = new Ext.data.JsonStore({
		url: apiUrl +'attribute_categories/get_for_combo?empty&ln=' + PC.global.admin_ln,
		fields: [
			'id', 
			'names', 
			{name: 'name', mapping: 'names', convert: function(names, n){return PC.utils.extractName(names, false, {greyOut: false});}}
		],
		idProperty: 'id',
		autoLoad: true,
		get_value_by_id: function (id){
			if (id) {
				var data = this.getById(id);
				if (data) {
					return data.data.name;
				}
			}
			return '...';
			//(values.category_id && PC.plugin.pc_shop.dialog.attr_category_store.getById(values.gvalue))?
		}
	})
	
	dialog.store = new Ext.data.JsonStore({
		url: apiUrl +'orders/get/'+ PC.global.admin_ln + '/?ln=' + PC.global.admin_ln,
		//proxy: new Ext.data.HttpProxy({
		//proxy: new Ext.data.ScriptTagProxy({
		//	url: apiUrl +'orders/get/'+ PC.global.admin_ln + '/?ln=' + PC.global.admin_ln
		//}),
		baseParams: {
			//ln: PC.global.ln
			limit: 10
			//test: 'test'
		},
		remoteSort: true,
		fields: [
			'id', 'address', 'comment', 'date', 'email', 'name', 'phone', 'user_id', 'items', 'data', 'total_price', 'status', 'is_paid', 'payment_option', 'delivery_option', 'delivery_price', 'cod_price',
			{name: 'dateFormatted', mapping: 'date', convert: dialog.FormatDate}
			//{name: 'is_paid_icon', mapping: 'is_paid', convert: dialog.is_paid_icon}
			//{name: 'status_icon', mapping: 'status', convert: dialog.Get_status_icon}
		],
		/*baseParams: {
			site: dialog.Initial_site_value
		},*/
		totalProperty: 'total',
		root: 'list',
		idProperty: 'id',
		autoLoad: true
	});
	
	dialog.store.setDefaultSort('date', 'desc');
	
	//dialog.store.setBaseParam('ln', PC.global.ln);
	
	dialog.selModel = new Ext.grid.RowSelectionModel({
		listeners: {
			//selectionchange: dialog.selectionChange
			selectionchange: function(selModel) {
				dialog.selectionChange(selModel);
				var selected = selModel.getSelected();
				if (selected) {
					var data = selModel.getSelected().data;
					data.show_details = 'yes';
					Ext.getCmp('pc_shop_dialog_order_details').update(data);
				}
			}
		}
	});
	
	var tbar = [
		{	ref: '../action_delete',
			disabled: true,
			text: ln._delete_order.button,
			icon: 'images/delete.png',
			handler: function(b, e) {
				Ext.MessageBox.show({
					buttons: Ext.MessageBox.YESNO,
					title: ln._delete_order.confirmation,
					msg: ln._delete_order.confirm_message,
					icon: Ext.MessageBox.WARNING,
					maxWidth: 320,
					fn: function(btn_id) {
						if (btn_id == 'yes') {
							var ids = dialog.getSelectedIds();
							if (!ids) return;
							Ext.Ajax.request({
								url: apiUrl +'orders/delete',
								method: 'POST',
								params: {ids: ids},
								callback: function(opts, success, response) {
									if (success && response.responseText) {
										try {
											var data = Ext.decode(response.responseText);
											if (data.success) {
												//debugger;
												dialog.store.reload();
												return;
											}
											else error = data.error;
										} catch(e) {
											var error = 'Invalid JSON data returned.';
										};
									}
									else {
										var error = 'Connection error.';
									}
									Ext.MessageBox.show({
										title: PC.i18n.error,
										msg: (error?'<b>'+ error +'</b><br />':'') +'Comments was not deleted.',
										buttons: Ext.MessageBox.OK,
										icon: Ext.MessageBox.ERROR
									});
								}
							});
						}
					}
				});
			}
		},
		{xtype:'tbfill'},
		{xtype:'tbtext', text: ln.search_label + ':', style:'margin:0 2px;'},
		{	xtype:'tbtext',
			text: ln.search_id,
			style:'margin:0 2px;'
		},
		{	ref: '../order_id',
			xtype:'textfield',
			width: 55
		},
		{xtype:'tbtext', text: ln.show_from, style:'margin:0 2px;'},
		{	ref: '../date_from',
			xtype:'datefield',
			width: 80,
			value: initial_date_from,
			maxValue: new Date()
		},
		{xtype:'tbtext', text: ln.to, style:'margin:0 2px;'},
		{	ref: '../date_to',
			xtype:'datefield',
			width: 80,
			value: initial_date_to,
			maxValue: new Date()
		},
		{	xtype:'tbtext',
			text: ln.with_phrase,
			style:'margin:0 2px;'
		},
		{	ref: '../search_phrase',
			xtype:'textfield',
			width: 80
		},
		/*
		{xtype:'tbtext', text: ln.and_status, style:'margin:0 2px;'},
		{
			xtype:'combo', 
			width: 100, 
			store: {
				xtype: 'arraystore',
				fields: ['status_id', 'status_label'],
				idIndex: 0,
				data: PC.utils.getComboArrayFromObject(ln.status_labels)
				//data: []
			},
			displayField: 'status_id',
			valueField: 'status_label',
			//editable: false,
			//forceSelection: true,
			triggerAction: 'all'
		},
		*/
		{	icon:'images/zoom.png',
			handler: function() {
				//site
				//var site = dialog.w.site.getValue();
				//dialog.store.setBaseParam('site', site);
				//date from
				var filters = Ext.getCmp('pc_shop_dialog_orders_tab');
				var date_from = filters.date_from.getValue();
				if (date_from instanceof Date) {
					dialog.store.setBaseParam('date_from', date_from.format('Y-m-d'));
				}
				else {
					dialog.store.setBaseParam('date_from', undefined);
				}
				//date to
				var date_to = filters.date_to.getValue();
				if (date_to instanceof Date) {
					dialog.store.setBaseParam('date_to', date_to.format('Y-m-d'));
				}
				else {
					dialog.store.setBaseParam('date_to', undefined);
				}
				//search phrase
				var search_phrase = filters.search_phrase.getValue();
				if (search_phrase.length) {
					dialog.store.setBaseParam('search_phrase', search_phrase);
				}
				else {
					dialog.store.setBaseParam('search_phrase', undefined);
				}
				
				//order id
				var order_id = filters.order_id.getValue();
				if (order_id.length) {
					dialog.store.setBaseParam('order_id', order_id);
				}
				else {
					dialog.store.setBaseParam('order_id', undefined);
				}
				dialog.store.load({
					params: {
						start: 0 // reset the start to 0 since you want the filtered results to start from the first page
					}
				});
			}
		},
		{	icon:'images/zoom_out.png',
			handler: function() {
				var filters = Ext.getCmp('pc_shop_dialog_orders_tab');
				//dialog.store.setBaseParam('site', dialog.Initial_site_value);
				dialog.store.setBaseParam('order_id', undefined);
				dialog.store.setBaseParam('date_from', undefined);
				dialog.store.setBaseParam('date_to', undefined);
				dialog.store.setBaseParam('search_phrase', undefined);
				dialog.store.load({
					params: {
						start: 0 // reset the start to 0 since you want the filtered results to start from the first page
					}
				});
				filters.order_id.setValue('');
				filters.search_phrase.setValue('');
				filters.date_from.setValue(initial_date_from);
				filters.date_to.setValue(initial_date_to);
			}
		}
	];
	
	dialog.grid = new Ext.grid.GridPanel({
		//title: ln.tab.orders,
		//region: 'center',
		button_container_id: 'pc_shop_dialog_orders_tab',
		border: false,
		store: dialog.store,
		plugins: [
			//dialog.expander,
			re
		],
		columns: [
			//uzsakovas, adresas, data, prekiu skaicius, suma
			//dialog.selModel,
			//dialog.expander,
			{header: ln.order_info.id, dataIndex: 'id',  width: 60, sortable: true},
			{header: ln.order_info.date, dataIndex: 'dateFormatted', width: 100, sortable: true},
			{header: ln.order_info.name, dataIndex: 'name', width: 150, sortable: true},
			//{header: 'Address', dataIndex: 'address', width: 180},
			//{header: 'Phone', dataIndex: 'phone', width: 90},
			//{header: 'Comment', dataIndex: 'comment'},
			{
				header: ln.order_info.is_paid, 
				dataIndex: 'is_paid', 
				width: 70, 
				sortable: true,
				editor: {
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['is_paid_id', 'is_paid_name'],
						idIndex: 0,
						data: [[0, PC.i18n.no], [1, PC.i18n.yes]]
					},
					displayField: 'is_paid_name',
					valueField: 'is_paid_id',
					editable: false,
					forceSelection: true,
					triggerAction: 'all'
				},
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					if (value == 1) {
						return PC.i18n.yes;
					}
					return PC.i18n.no;
				}
			},
            //{header: 'Prekės', dataIndex: '_author', width: 180, css: 'font-weight:bold'},
            //{id: 'pc_shop_comment_column', header: ln.comment, dataIndex: 'comment'},
            //{header: 'Statusas', dataIndex: 'status_icon', width: 80}
			{
				header: ln.order_info.status, 
				dataIndex: 'status', 
				width_: 90,
				sortable: true,
				editor: {
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['status_id', 'status_label'],
						idIndex: 0,
						data: PC.utils.getComboArrayFromObject(ln.status_labels)
					},
					displayField: 'status_id',
					valueField: 'status_label',
					editable: false,
					forceSelection: true,
					triggerAction: 'all'
				},
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					if (ln.status_labels[value]) {
						return ln.status_labels[value];
					}
					return value;
				}
			},
			{
				header: ln.order_info.payment, 
				dataIndex: 'payment_option', 
				width_: 90,
				sortable: true,
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					if (ln.payment_option_labels[value]) {
						return ln.payment_option_labels[value];
					}
					return value;
				}
			},
			{
				header: ln.order_info.delivery, 
				dataIndex: 'delivery_option', 
				width_: 90, 
				sortable: true,
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					if (ln.delivery_option_labels[value]) {
						return ln.delivery_option_labels[value];
					}
					return value;
				}
			}
        ],
		//autoExpandColumn: 'pc_shop_comment_column',
		sm: dialog.selModel,
		
		bbar: new Ext.PagingToolbar({
			store: dialog.store,
			displayInfo: true,
			pageSize: 10,// dialog.store.baseParams.limit,
			prependButtons: true
		})
		

		
        //iconCls: 'icon-grid'
    });
	
	dialog.orders = {}
	
	dialog.orders.center = {
		xtype: 'panel',
		region: 'west',
		layout: 'fit',
		width: 600,
		padding: '6px 0 6px 6px',
		bodyCssClass: 'x-border-layout-ct',
		split: true,
		border: false,
		items: dialog.grid
	}
	
	dialog.orders.east = {
		xtype: 'panel',
		region: 'center',
		layout: 'fit',
		padding: '6px 6px 6px 0',
		bodyCssClass: 'x-border-layout-ct',
		split: true,
		border: false,
		items: [new Ext.BoxComponent({
			id: "pc_shop_dialog_order_details",
			//html: '<span>My Content</span>',
			//tpl: dialog.orders.xtemplate,
			tpl: new Ext.XTemplate(
				'<tpl if="show_details==\'no\'">',
					'<strong>' + ln.order_info.choose_order + ':</strong>',
				'</tpl>',
				'<tpl if="show_details==\'yes\'">',

					'<p style="padding:5px;margin:2px 2px 2px 2px;border:1px solid #eee;color:#555">',
						//------
						'<b>' + ln.order_info.buyer_info + ':</b><br />',
						'' + ln.order_info.name + ': <i>{name}</i><br />',
						'' + ln.order_info.phone + ': <i>{phone}</i><br />',
						'' + ln.order_info.email + ': <i>{email}</i><br />',
						'' + ln.order_info.address + ': <i>{address}</i><br />',
						//------
						'<br /><b>' + ln.order_info.additional_info + ':</b><br />',
						'<tpl for="data">', //should be table
							'<tpl if="!value==\'\'">',
								'{name} - {value} <br />',
							'</tpl>',
						'</tpl>',//------
						'<br />',
						'<tpl if="this.non_empty_array(items)">',
							'<b>' + ln.order_info.items + ':</b><br />',
							'<tpl for="items">', //should be table
								'{#}. {name} ' + ln.order_info.quantity + ': {quantity} - ' + ln.order_info.price_for_each + ': {price}<br />',
								//'{#}. {name} - {short_description} - ' + ln.order_info.quantity + ': {quantity} - ' + ln.order_info.price_for_each + ': {price}<br />',
							'</tpl>',
						'</tpl>',
						//------
						'<tpl if="!comment==\'\'">',
							'<br /><b>' + ln.order_info.comment + ': </b><br /><i>{comment}</i><br />',
						'</tpl>',
						'<tpl if="delivery_price &gt; 0">',
							'<br /><b>', ln.order_info.delivery_price, '</b> - {delivery_price} <br />',
						'</tpl>',
						'<tpl if="cod_price &gt; 0">',
							'<br /><b>', ln.order_info.cod_price, '</b> - {cod_price} <br />',
						'</tpl>',
						'<br /><b>' + ln.order_info.total_price + ':</b> {total_price}',
					'</p>',
				'</tpl>',
				{
					non_empty_array: function(value) {
						if (value.length) {
							return true
						}
						return false;
					}
				}
			),
			data: {show_details: 'no'}
		})		
		]
	}
	
	dialog.orders.tab = {
		id: "pc_shop_dialog_orders_tab",
		title: ln.tab.orders,
		layout: 'border',
		tbar: tbar,
		items: [dialog.orders.center, dialog.orders.east]
	}
	
	dialog.getSelectedIds = function() {
		var selected = dialog.grid.selModel.getSelections();
		if (!selected.length) return false;
		var ids = '';
		for (var a=0; selected[a]; a++) {
			if (ids != '') ids += ',';
			ids += selected[a].data.id;
		}
		return ids;
	}
	
	/* Attributes Tab */
	
	dialog.attributes = {
		_getCurrentId: function() {
			var attribute = dialog.attributes.grid.selModel.getSelected();
			if (!attribute) return false;
			return attribute.data.id;
		}
	};
	
	dialog.attributes.store = new Ext.data.GroupingStore({ 
		url: apiUrl +'attributes/get',
		root: 'list',
		autoLoad: true,
		reader: new Ext.data.JsonReader({
			//id: 'Line.id',
			root: 'list',
			idProperty: 'id',
			fields: [
				'id', 'ref', 'is_category_attribute', 'is_custom', 'is_searchable', 'names', 'category_id',
				{name: 'name', mapping: 'names', convert: function(names, n){return PC.utils.extractName(names);}}
			]
		}), 
		groupField: 'category_id',
		perPage: 1000
	});

	/*
	dialog.attributes.store_ = new Ext.data.JsonStore({
		url: apiUrl +'attributes/get',
		method: 'POST',
		autoLoad: true,
		remoteSort: true,
		root: 'list',
		totalProperty: 'total',
		idProperty: 'id',
		fields: [
			'id', 'ref', 'is_category_attribute', 'is_custom', 'is_searchable', 'names', 'category_id',
			{name: 'name', mapping: 'names', convert: function(names, n){return PC.utils.extractName(names);}}
		],
		perPage: 1000
	});
	*/
	
	dialog.attributes.selModel = new Ext.grid.RowSelectionModel({
		listeners: {
			selectionchange: function(selModel) {
				dialog.selectionChange(selModel);
				var n = selModel.getSelected();
				if (n == undefined) return;
				if (n.data.is_custom == 1) {
					dialog.attributes.values.disable(n.data.id);
				}
				else dialog.attributes.values.enable(n.data.id);
			}
		}
	});
	
	dialog.attributes.grid = new Ext.grid.GridPanel({
		view: new Ext.grid.GroupingView({
			forceFit: true,
			//groupTextTpl: '{[values.category_id?'
			//+'PC.plugin.pc_shop.dialog.attr_category_store.getById(19).data.name'
			//+':PC.plugin.pc_shop.dialog.attr_category_store.getById(21).data.name]}'
			groupTextTpl: '{[PC.plugin.pc_shop.dialog.attr_category_store.get_value_by_id(values.gvalue)]}'
		}),
		store: dialog.attributes.store,
		//plugins: dialog.expander,
        columns: [
			//dialog.expander,
			{header: 'Name', dataIndex: 'name', width: 150},
			{header: 'Attribute for', dataIndex: 'is_category_attribute', 
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					if (value == 1) return 'Category';
					return 'Item';
				}
			},
			{header: 'Searchable', dataIndex: 'is_searchable', width: 70,
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					if (value == 1) return 'Yes';
					return 'No';
				}
			},
			{header: 'Type', dataIndex: 'is_custom', width: 80,
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					if (value == 1) return 'Custom';
					return 'Predefined';
				}
			},
			{header: 'Ref', dataIndex: 'ref', width: 60},
			{header: 'Category id', dataIndex: 'category_id', hidden: true}
        ],
		//autoExpandColumn: 'pc_shop_comment_column',
		sm: dialog.attributes.selModel,
		tbar: [
			{	ref: '../action_add',
				text: PC.i18n.add,
				icon: 'images/add.png',
				handler: function() {
					PC.dialog.multilnedit.show({
						title: PC.i18n.menu.rename,
						fields: [
							{	_fld: 'is_category_attribute',
								fieldLabel: 'Attribute for',
								anchor: '100%',
								xtype: 'combo',
								mode: 'local',
								store: {
									xtype: 'arraystore',
									fields: ['value', 'name'],
									idIndex: 0,
									data: [
										['0', 'Item'],
										['1', 'Category']
									]
								},
								displayField: 'name',
								valueField: 'value',
								editable: false,
								forceSelection: true,
								triggerAction: 'all',
								value: null
							}
						],
						Save: function(data, w, dlg) {
							Ext.Ajax.request({
								url: apiUrl +'attributes/create',
								method: 'POST',
								params: {names: Ext.util.JSON.encode(data.names), is_category_attribute: data.other.is_category_attribute},
								callback: function(opts, success, response) {
									if (success && response.responseText) {
										try {
											var data = Ext.decode(response.responseText);
											if (data.success) {
												var store = dialog.attributes.store;
												var n = new store.recordType({
													id: data.id,
													names: data.names,
													is_searchable: data.is_searchable,
													is_category_attribute: data.is_category_attribute,
													is_custom: data.is_custom
												});
												store.addSorted(n);
												n.set('name', PC.utils.extractName(data.names));
												//refresh attribute selection store
												PC.plugin.pc_shop.attributes.Store.reload();
												return;
											}
											else error = data.error;
										}
										catch(e) {
											var error = 'Invalid JSON data returned.';
										};
									}
									else var error = 'Connection error.';
									Ext.MessageBox.show({
										title: PC.i18n.error,
										msg: (error?'<b>'+ error +'</b>':''),
										buttons: Ext.MessageBox.OK,
										icon: Ext.MessageBox.ERROR
									});
								}
							});
							return true;
						}
					});
				}
			},
			{	ref: '../action_delete',
				disabled: true,
				text: PC.i18n.del,
				icon: 'images/delete.png',
				handler: function() {
					var n = dialog.attributes.grid.selModel.getSelected();
					if (!n) return false;
					Ext.MessageBox.show({
						title: PC.i18n.msg.title.confirm,
						msg: String.format(PC.i18n.msg.confirm_delete, '"'+n.data.name+'"'),
						buttons: Ext.MessageBox.YESNO,
						icon: Ext.MessageBox.WARNING,
						fn: function(r) {
							if (r == 'yes') {
								Ext.Ajax.request({
									url: apiUrl +'attributes/delete',
									method: 'POST',
									params: {id: n.id},
									callback: function(opts, success, response) {
										if (success && response.responseText) {
											try {
												var data = Ext.decode(response.responseText);
												if (data.success) {
													dialog.attributes.values.disable();
													//remove attribute from selection in editors
													var toDel = PC.plugin.pc_shop.attributes.Store.getById(n.id);
													if (toDel) PC.plugin.pc_shop.attributes.Store.remove(toDel);
													//remove attribute
													dialog.attributes.store.remove(n);
													return;
												}
												else error = data.error;
											}
											catch(e) {
												var error = 'Invalid JSON data returned.';
											};
										}
										else var error = 'Connection error.';
										Ext.MessageBox.show({
											title: PC.i18n.error,
											msg: (error?'<b>'+ error +'</b>':''),
											buttons: Ext.MessageBox.OK,
											icon: Ext.MessageBox.ERROR
										});
									}
								});
							}
						}
					});
				}
			}
		],
		/*
		bbar: new Ext.PagingToolbar({
			store: dialog.attributes.store,
			displayInfo: true,
			pageSize: dialog.attributes.store.perPage,
			prependButtons: true
		}),
		*/
		renameCell: function(n, ev) {
			if (!n || !ev) return false;
			var xy = ev.getXY();
			PC.dialog.multilnedit.show({
				title: PC.i18n.menu.rename,
				values: n.data.names,
				pageX: xy[0], pageY: xy[1],
				fields: [
					{	_fld: 'is_category_attribute',
						fieldLabel: 'Attribute for',
						anchor: '100%',
						xtype: 'combo',
						mode: 'local',
						store: {
							xtype: 'arraystore',
							fields: ['value', 'name'],
							idIndex: 0,
							data: [
								['0', 'Item'],
								['1', 'Category']
							]
						},
						displayField: 'name',
						valueField: 'value',
						editable: false,
						forceSelection: true,
						triggerAction: 'all',
						value: n.data.is_category_attribute
					},
					{	_fld: 'is_searchable',
						fieldLabel: 'Searchable',
						anchor: '100%',
						xtype: 'combo',
						mode: 'local',
						store: {
							xtype: 'arraystore',
							fields: ['value', 'name'],
							idIndex: 0,
							data: [
								['0', PC.i18n.no],
								['1', PC.i18n.yes]
							]
						},
						displayField: 'name',
						valueField: 'value',
						editable: false,
						forceSelection: true,
						triggerAction: 'all',
						value: n.data.is_searchable
					},
					{	_fld: 'is_custom',
						fieldLabel: 'Type',
						anchor: '100%',
						xtype: 'combo',
						mode: 'local',
						store: {
							xtype: 'arraystore',
							fields: ['value', 'name'],
							idIndex: 0,
							data: [
								['1', 'Custom'],
								['0', 'Predefined']
							]
						},
						displayField: 'name',
						valueField: 'value',
						editable: false,
						forceSelection: true,
						triggerAction: 'all',
						value: n.data.is_custom
					},
					{	_fld: 'category_id',
						fieldLabel: 'Category',
						anchor: '100%',
						xtype: 'combo',
						emptyText: ' -- ',
						//mode: 'local',
						store: dialog.attr_category_store,
						displayField: 'name',
						valueField: 'id',
						editable: false,
						//forceSelection: true,
						triggerAction: 'all',
						value: n.data.category_id
					},
					{	_fld: 'ref',
						fieldLabel: 'Reference',
						anchor: '100%',
						xtype: 'textfield',
						mode: 'local',
						displayField: 'ref',
						valueField: 'value',
						editable: false,
						forceSelection: true,
						triggerAction: 'all',
						value: n.data.ref
					}
				],
				Save: function(data, renameWindow, renameDialog) {
					var params =  {
						id: n.data.id,
						is_custom: data.other.is_custom,
						is_searchable: data.other.is_searchable,
						is_category_attribute: data.other.is_category_attribute,
						ref: data.other.ref,
						category_id: data.other.category_id,
						names: Ext.util.JSON.encode(data.names)
					}
					Ext.Ajax.request({
						url: apiUrl +'attributes/save',
						method: 'POST',
						params: params,
						callback: function(opts, success, response) {
							if (success && response.responseText) {
								try {
									var responseData = Ext.decode(response.responseText);
									if (responseData.success) {
										//is_category_attribute
										n.set('is_category_attribute', data.other.is_category_attribute);
										//is_custom
										if (n.data.is_custom != 1) {
											if (data.other.is_custom == 1) dialog.attributes.values.disable();
										}
										else if (data.other.is_custom != 1) {
											dialog.attributes.values.enable();
										}
										n.set('is_custom', data.other.is_custom);
										n.set('category_id', data.other.category_id);
										//is_searchable
										n.set('is_searchable', data.other.is_searchable);
										
										//ref
										n.set('ref', data.other.ref);
										//names
										n.set('names', data.names);
										n.set('name', PC.utils.extractName(data.names));
										n.commit();
										renameWindow.close();
										//refresh attribute selection store
										PC.plugin.pc_shop.attributes.Store.reload();
										return;
									}
									else error = responseData.error;
								} catch(e) {
									var error = 'Invalid JSON data returned.';
								};
							}
							else {
								var error = 'Connection error.';
							}
							Ext.MessageBox.show({
								title: PC.i18n.error,
								msg: (error?'<b>'+ error +'</b><br />':'') +'Attribute has not been saved.',
								buttons: Ext.MessageBox.OK,
								icon: Ext.MessageBox.ERROR
							});
						}
					});
				}
			});
		},
		listeners: {
			cellcontextmenu: function(grid, rowIndex, cellIndex, ev) {
				ev.preventDefault();
				var n = dialog.attributes.store.getAt(rowIndex);
				if (!n) return false;
				var menu = new Ext.menu.Menu({
					items: [{
						text: PC.i18n.menu.rename,
						icon: 'images/edit.gif',
						handler: function(){
							grid.renameCell(n, ev);
						}
					}]
				});
				return menu.showAt(ev.getXY());
			},
			containercontextmenu: function(grid, ev){
				ev.preventDefault();
			},
			celldblclick: function(grid, rowIndex, cellIndex, ev) {
				var n = dialog.attributes.store.getAt(rowIndex);
				if (!n) return false;
				grid.renameCell(n, ev);
				return false;
			}
		}
    });
	
	dialog.attributes.values = {};
	
	dialog.attributes.values.enable = function(attributeId) {
		dialog.attributes.values.grid.enable();
		if (attributeId == undefined) {
			var n = dialog.attributes.grid.selModel.getSelected();
			if (!n) return false;
			var attributeId = n.data.id;
		}
		dialog.attributes.values.store.setBaseParam('attribute_id', attributeId);
		dialog.attributes.values.store.load();
	}
	dialog.attributes.values.disable = function(attributeId) {
		dialog.attributes.values.grid.disable();
		dialog.attributes.values.store.removeAll();
	}
					
	dialog.attributes.values.storeFields = ['id', 'attribute_id', 'names'];
	PC.sites.languages.ext.FillStoreFields(dialog.attributes.values.storeFields, 'value_');
	
	dialog.attributes.values.store = new Ext.data.JsonStore({
		url: apiUrl +'attributes/values/get',
		remoteSort: true,
		fields: dialog.attributes.values.storeFields,
		totalProperty: 'total',
		root: 'list',
		idProperty: 'id',
		perPage: 1000
	});
	
	dialog.attributes.values.selModel = new Ext.grid.CheckboxSelectionModel({
		listeners: {
			selectionchange: dialog.selectionChange
		},
		editable: false
	});
	
	dialog.attributes.values.gridCols = PC.sites.languages.ext.GetGridColumns('value_');
	//dialog.attributes.values.gridCols.unshift(dialog.attributes.values.selModel);
	
	dialog.attributes.values.editor = new Ext.ux.grid.RowEditor({
		saveText: PC.i18n.save,
		cancelText: PC.i18n.cancel,
		clicksToEdit: 2,
		listeners: {
			beforeedit: function(editor) {
				editor.justCancelled = false;
			},
			canceledit: function(editor, button, record) {
				if (record.phantom) dialog.attributes.values.store.remove(record);
				editor.justCancelled = true;
			},
			hide: function(editor){
				if (editor.justCancelled) return;
				var record = editor.record;
				var names = {};
				Ext.iterate(record.data, function(key, value){
					if (key.substr(0,6) == 'value_') {
						var ln = key.substring(6);
						names[ln] = value;
					}
				});
				record.set('names', names);
				if (record.data._new != undefined) {
					var attributeId = dialog.attributes._getCurrentId();
					if (!attributeId) return false;
					//create new
					var values = record.data.names;
					Ext.Ajax.request({
						url: apiUrl +'attributes/values/create',
						method: 'POST',
						params: {attribute_id: attributeId, values: Ext.util.JSON.encode(values)},
						callback: function(opts, success, response) {
							if (success && response.responseText) {
								try {
									var data = Ext.decode(response.responseText);
									if (data.success) {
										record.set('id', data.id);
										record.id = data.id;
										record.phantom = false;
										delete record.data._new;
										delete record.data.phantom;
										//refresh attribute selection store
										PC.plugin.pc_shop.attributes.Store.reload();
										return;
									}
									else error = data.error;
								} catch(e) {
									var error = 'Invalid JSON data returned.';
								};
							}
							else {
								var error = 'Connection error.';
							}
							Ext.MessageBox.show({
								title: PC.i18n.error,
								msg: (error?'<b>'+ error +'</b><br />':'') +'Attribute has not been created.',
								buttons: Ext.MessageBox.OK,
								icon: Ext.MessageBox.ERROR
							});
						}
					});
				}
				else {
					//edit existing
					var values = record.data.names;
					Ext.Ajax.request({
						url: apiUrl +'attributes/values/save',
						method: 'POST',
						params: {id: record.data.id, values: Ext.util.JSON.encode(values)},
						callback: function(opts, success, response) {
							if (success && response.responseText) {
								try {
									var data = Ext.decode(response.responseText);
									if (data.success) {
										record.commit();
										//refresh attribute selection store
										PC.plugin.pc_shop.attributes.Store.reload();
										return;
									}
									else error = data.error;
								} catch(e) {
									var error = 'Invalid JSON data returned.';
								};
							}
							else {
								var error = 'Connection error.';
							}
							//record.rejectChanges();
							Ext.MessageBox.show({
								title: PC.i18n.error,
								msg: (error?'<b>'+ error +'</b><br />':'') +'Error while editing attribute value.',
								buttons: Ext.MessageBox.OK,
								icon: Ext.MessageBox.ERROR
							});
						}
					});
				}
			},
			afteredit: function(rowEditor, changes, record, rowIndex) {
				//
			}
		}
	});
	
	dialog.attributes.values.grid = new Ext.grid.EditorGridPanel({
		disabled: true,
		store: dialog.attributes.values.store,
		plugins: dialog.attributes.values.editor,//dialog.expander,
        columns: dialog.attributes.values.gridCols,
		/*[
			dialog.attributes.values.selModel,
			//dialog.expander,
			{header: 'English', dataIndex: 'address'},
			{header: 'Lietuvių', dataIndex: 'comment'},
			{header: 'Русский', dataIndex: 'comment'}
        ],*/
		//autoExpandColumn: 'pc_shop_comment_column',
		sm: dialog.attributes.values.selModel,
		tbar: [
			{	ref: '../action_add',
				text: PC.i18n.add,
				icon: 'images/add.png',
				handler: function() {
					var attributeId = dialog.attributes._getCurrentId();
					if (!attributeId) return false;
					var selModel = dialog.attributes.values.grid.selModel;
					var currentNode = selModel.getSelected();
					var attributes = {
						attribute_id: attributeId,
						names: {},
						_new: true,
						phantom: true
					};
					var store = dialog.attributes.values.store;
					if (dialog.attributes.values.editor.editing) dialog.attributes.values.store.removeAt(dialog.attributes.values.editor.rowIndex);
					var n = new store.recordType(attributes);
					store.addSorted(n);
					return dialog.attributes.values.editor.startEditing(dialog.attributes.values.store.getCount()-1);
				}
			},
			{	ref: '../action_delete',
				disabled: true,
				text: PC.i18n.del,
				icon: 'images/delete.png',
				handler: function() {
					var n = dialog.attributes.values.grid.selModel.getSelected();
					if (!n) return false;
					Ext.MessageBox.show({
						title: PC.i18n.msg.title.confirm,
						msg: String.format(PC.i18n.msg.confirm_delete, '"'+ PC.utils.extractName(n.data.names) +'"'),
						buttons: Ext.MessageBox.YESNO,
						icon: Ext.MessageBox.WARNING,
						fn: function(r) {
							if (r == 'yes') {
								Ext.Ajax.request({
									url: apiUrl +'attributes/values/delete',
									method: 'POST',
									params: {id: n.id},
									callback: function(opts, success, response) {
										if (success && response.responseText) {
											try {
												var data = Ext.decode(response.responseText);
												if (data.success) {
													//remove attribute value from selection store in editors
													var valueAttribute = PC.plugin.pc_shop.attributes.Store.getById(n.data.attribute_id);
													if (valueAttribute) {
														delete valueAttribute.data.values[n.id]
													}
													dialog.attributes.values.store.remove(n);
													return;
												}
												else error = data.error;
											}
											catch(e) {
												var error = 'Invalid JSON data returned.';
											};
										}
										else var error = 'Connection error.';
										Ext.MessageBox.show({
											title: PC.i18n.error,
											msg: (error?'<b>'+ error +'</b>':''),
											buttons: Ext.MessageBox.OK,
											icon: Ext.MessageBox.ERROR
										});
									}
								});
							}
						}
					});
				}
			}
		],
		bbar: new Ext.PagingToolbar({
			store: dialog.attributes.values.store,
			displayInfo: true,
			pageSize: dialog.attributes.values.store.perPage,
			prependButtons: true
		})
    });
	
	dialog.attributes.center = {
		xtype: 'panel',
		region: 'west',
		layout: 'fit',
		width: 500,
		padding: '6px 0 6px 6px',
		bodyCssClass: 'x-border-layout-ct',
		split: true,
		border: false,
		items: dialog.attributes.grid
	}
	
	dialog.attributes.east = {
		xtype: 'panel',
		region: 'center',
		layout: 'fit',
		padding: '6px 6px 6px 0',
		bodyCssClass: 'x-border-layout-ct',
		split: true,
		border: false,
		items: dialog.attributes.values.grid
	}
	
	dialog.attributes.tab = {
		title: ln.tab.attributes,
		layout: 'border',
		items: [dialog.attributes.center, dialog.attributes.east]
	}
	
	/* Window Layout */
	dialog.tab = new Ext.TabPanel({
		region: 'center',
		border: false,
		items: [
			//dialog.orders.tab,
			new PC.plugin.pc_shop.crud_orders({
				ln: Ext.apply({title: ln.tab.orders}, ln)
			}),
			{title: ln.tab.coupons, html:'Under construction'},
			dialog.attributes.tab,
			new PC.plugin.pc_shop.crud_attribute_categories({
				ln: Ext.apply({title: ln.tab.attribute_categories}, ln.attribute_categories),
				per_page: 2
			}),
			//new PC.ux.crud({
				//api_url: 'api/plugin/pc_shop/attribute_categories/'
			//}),
			{title: ln.tab.currencies, html:'Under construction'},
			//{title: ln.tab.manufacturers, html:'Under construction'},
			new PC.plugin.pc_shop.crud_manufacturers({
				ln: Ext.apply({title: ln.tab.manufacturers}, ln.manufacturers)
			}),
			new PC.plugin.pc_shop.crud_delivery_options({
				ln: Ext.apply({title: ln.tab.delivery_options}, ln.delivery_options)
			}),
			new PC.plugin.pc_shop.crud_payment_options({
				ln: Ext.apply({title: ln.tab.payment_options}, ln.payment_options)
			}),
			new PC.plugin.pc_shop.settings({
				ln: Ext.apply({title: ln.tab.settings}, ln.settings)
			}),
			PC_plugin_dialog_pc_shop.view_factory.get_tab_for_import()
		],
		activeTab: 0
	});
	
	var hook_params = {};
	PC.hooks.Init('plugin/pc_shop/tabs', hook_params);
	if (hook_params.allowed_tabs) {
		var total_tabs = dialog.tab.items.length;
		for(var i = total_tabs-1; i >= 0; i--) {
			if (hook_params.allowed_tabs.indexOf(i) == -1) {
				dialog.tab.items.removeAt(i);
			}
		};
	}
	
	if (typeof(hook_params.active_tab) != "undefined") {
		dialog.tab.activeTab = hook_params.active_tab;
	}
	
	
	dialog.w = new PC.ux.Window({
		layout: 'border',
		title: ln.name,
		width: 900,
		height: 550,
		maximizable: true,
		items: [dialog.tab],
		buttonAlign: 'left',
		buttons: [
			//{xtype: 'tbtext', text: ''},
			{xtype: 'tbfill'},
			{	ref: '../ok_btn',
				text: Ext.Msg.buttonText.ok,
				handler: function() {
					dialog.w.hide();
				}
			}
		],
		closeAction: 'hide'
	});
	
	dialog.w.show();
}

var icon = <?php echo json_encode(get_plugin_icon()) ?>;

var hook_params = {};
PC.hooks.Init('plugin/pc_shop/icon', hook_params);
if (hook_params.icon) {
	icon = hook_params.icon;
}

Ext.ns('PC.plugin.pc_shop');

var cfg = {
	name: PC.i18n.mod.pc_shop.name,
	onclick: mod_pc_shop_click,
	icon: icon,
	priority: <?php echo $mod['priority'] ?>
};
Ext.apply(PC.plugin.pc_shop, cfg);
</script>