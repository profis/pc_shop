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
PC.utils.localize('mod.pc_shop', {
	en: {
		name: 'e-Shop',
		tab: {
			orders: 'Orders',
			sales: 'Sales',
			coupons: 'Coupons',
			attributes: 'Attributes',
			currencies: 'Currencies',
			manufacturers: 'Manufacturers',
			settings: 'Settings'
		},
		date_time: 'Date/time',
		author: 'Author',
		comment: 'Comment',
		confirmed: 'Confirmed',
		with_selected: 'Selected',
		confirm: 'Confirm',
		unconfirm: 'Unconfirm',
		_delete: 'Delete',
		show_from: 'Show from',
		to: 'to',
		with_phrase: 'with phrase',
		and_status: 'and status',
		status: 'Status'
	},
	lt: {
		name: 'e-Parduotuvė',
		tab: {
			orders: 'Užsakymai',
			sales: 'Pardavimai',
			coupons: 'Kuponai',
			attributes: 'Atributai',
			currencies: 'Valiutos',
			manufacturers: 'Gamintojai',
			settings: 'Nustatymai'
		},
		date_time: 'Data/laikas',
		author: 'Autorius',
		comment: 'Komentaras',
		confirmed: 'Patvirt.',
		with_selected: 'Pažymėtus',
		confirm: 'Patvirtinti',
		unconfirm: 'Slėpti',
		_delete: 'Ištrinti',
		show_from: 'Rodyti nuo',
		to: 'iki',
		with_phrase: 'su fraze',
		and_status: 'ir statusu',
		status: 'Statusas'
	},
	ru: {
        name: 'e-Магазин',
		tab: {
			orders: 'Заказы',
			sales: 'Продажи',
			coupons: 'Купоны',
			attributes: 'Атрибуты',
			currencies: 'Валюты',
			manufacturers: 'Производители',
			settings: 'Настройки'
		},
        date_time: 'Дата/время',
        author: 'Автор',
        comment: 'Комментарий',
        confirmed: 'Подтвержден',
        with_selected: 'Помеченные',
        confirm: 'Подтвердить',
        unconfirm: 'Скрыть',
        _delete: 'Удалить',
        show_from: 'Показывать с',
        to: 'по',
        with_phrase: 'с фразой',
		and_status: 'and status',
		status: 'Status'
    }
});

Ext.namespace('PC.plugins');

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
	
	dialog.selectionChange = function(selModel){
		var del = selModel.grid.action_delete;
		if (selModel.grid.action_delete == undefined) return;
		var selected = selModel.getSelections();
		if (selected.length) {
			del.enable();
		}
		else del.disable();
	}
	
	dialog.expander = new Ext.ux.grid.RowExpander({
		tpl : new Ext.XTemplate(
			'<p style="padding:5px;margin:2px 2px 2px 42px;border:1px solid #eee;color:#555">',
				//------
				'<b>Recipient information:</b><br />',
				'Name: <i>{name}</i><br />',
				'Phone: <i>{phone}</i><br />',
				'Email: <i>{email}</i><br />',
				'Address: <i>{address}</i><br />',
				//------
				'<br /><b>Items in order:</b><br />',
				'<tpl for="items">', //should be table
					'{#}. {name} - {short_description} - Quantity taken: {quantity} - Price for each: {price}<br />',
				'</tpl>',
				//------
				'Comment: <i>{comment}</i><br />',
				'<br /><b>Total price of the order:</b> {total_price}',
			'</p>'
			/*
			'<p style="padding:5px;margin:2px 2px 2px 42px;border:1px solid #eee;color:#555">',
				'<img style="vertical-align:-3px;margin-right:2px" src="images/folder.png" alt="" /><b>{values.subject.title}</b><br />',
				'<img style="vertical-align:-4px;margin-right:2px" src="images/comment.png" alt="" />{comment}',
			'</p>'*/
		),
		autoExpandColumn: 'items'
	});
	
	dialog.Get_status_icon = function(confirmed) {
		if (confirmed) var image = 'tick.png';
		else var image = 'hourglass.png';
		return '<img src="images/'+ image +'" alt="" />';
	}
	
	dialog.FormatDate = function(date){
		return new Date(date*1000).format('Y-m-d H:i');
	}
	
	dialog.store = new Ext.data.JsonStore({
		url: apiUrl +'orders/get/'+ PC.global.ln,
		remoteSort: true,
		fields: [
			'id', 'address', 'comment', 'date', 'email', 'name', 'phone', 'user_id', 'items', 'total_price',
			{name: 'dateFormatted', mapping: 'date', convert: dialog.FormatDate}
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
	
	dialog.grid = new Ext.grid.GridPanel({
		title: ln.tab.orders,
		//region: 'center',
		border: false,
		store: dialog.store,
		plugins: dialog.expander,
        columns: [
			//uzsakovas, adresas, data, prekiu skaicius, suma
			//dialog.selModel,
			dialog.expander,
			{header: 'Data', dataIndex: 'dateFormatted', width: 100},
			{header: 'Name', dataIndex: 'name', width: 150},
			{header: 'Address', dataIndex: 'address', width: 200},
			{header: 'Phone', dataIndex: 'phone', width: 200},
			{header: 'Comment', dataIndex: 'comment'}
            //{header: 'Prekės', dataIndex: '_author', width: 180, css: 'font-weight:bold'},
            //{id: 'pc_shop_comment_column', header: ln.comment, dataIndex: 'comment'},
            //{header: 'Statusas', dataIndex: 'status_icon', width: 80}
        ],
		//autoExpandColumn: 'pc_shop_comment_column',
		//sm: dialog.selModel,
		tbar: [
			{	ref: '../action_delete',
				disabled: true,
				text: ln._delete,
				icon: 'images/delete.png',
				handler: function() {
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
			},
			{xtype:'tbfill'},
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
			{xtype:'tbtext', text: ln.and_status, style:'margin:0 2px;'},
			{xtype:'combo', width: 100},
			{	icon:'images/zoom.png',
				handler: function() {
					//site
					//var site = dialog.w.site.getValue();
					//dialog.store.setBaseParam('site', site);
					//date from
					var date_from = dialog.w.date_from.getValue();
					if (date_from instanceof Date) {
						dialog.store.setBaseParam('date_from', date_from.format('Y-m-d'));
					}
					else {
						dialog.store.setBaseParam('date_from', undefined);
					}
					//date to
					var date_to = dialog.w.date_to.getValue();
					if (date_to instanceof Date) {
						dialog.store.setBaseParam('date_to', date_to.format('Y-m-d'));
					}
					else {
						dialog.store.setBaseParam('date_to', undefined);
					}
					//search phrase
					var search_phrase = dialog.w.search_phrase.getValue();
					if (search_phrase.length) {
						dialog.store.setBaseParam('search_phrase', search_phrase);
					}
					else {
						dialog.store.setBaseParam('search_phrase', undefined);
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
					//dialog.store.setBaseParam('site', dialog.Initial_site_value);
					dialog.store.setBaseParam('date_from', undefined);
					dialog.store.setBaseParam('date_to', undefined);
					dialog.store.setBaseParam('search_phrase', undefined);
					dialog.store.load({
						params: {
							start: 0 // reset the start to 0 since you want the filtered results to start from the first page
						}
					});
					dialog.w.search_phrase.setValue('');
					dialog.w.date_from.setValue(initial_date_from);
					dialog.w.date_to.setValue(initial_date_to);
				}
			}
		],
		bbar: new Ext.PagingToolbar({
			store: dialog.store,
			displayInfo: true,
			pageSize: 10,
			prependButtons: true
		})
        //iconCls: 'icon-grid'
    });
	
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
	
	dialog.attributes.store = new Ext.data.JsonStore({
		url: apiUrl +'attributes/get',
		method: 'POST',
		autoLoad: true,
		remoteSort: true,
		root: 'list',
		totalProperty: 'total',
		idProperty: 'id',
		fields: [
			'id', 'is_category_attribute', 'is_custom', 'is_searchable', 'names',
			{name: 'name', mapping: 'names', convert: function(names, n){return PC.utils.extractName(names);}}
		],
		perPage: 1000
	});
	
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
		store: dialog.attributes.store,
		//plugins: dialog.expander,
        columns: [
			//dialog.expander,
			{header: 'Name', dataIndex: 'name', width: 200},
			{header: 'Attribute for', dataIndex: 'is_category_attribute', 
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					if (value == 1) return 'Category';
					return 'Item';
				}
			},
			{header: 'Searchable', dataIndex: 'is_searchable', 
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					if (value == 1) return 'Yes';
					return 'No';
				}
			},
			{header: 'Type', dataIndex: 'is_custom',
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					if (value == 1) return 'Custom';
					return 'Predefined';
				}
			}
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
		bbar: new Ext.PagingToolbar({
			store: dialog.attributes.store,
			displayInfo: true,
			pageSize: dialog.attributes.store.perPage,
			prependButtons: true
		}),
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
					}
				],
				Save: function(data, renameWindow, renameDialog) {
					var params =  {
						id: n.data.id,
						is_custom: data.other.is_custom,
						is_searchable: data.other.is_searchable,
						is_category_attribute: data.other.is_category_attribute,
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
										//is_searchable
										n.set('is_searchable', data.other.is_searchable);
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
	
	dialog.attributes.center = {
		xtype: 'panel',
		region: 'center',
		layout: 'fit',
		width: 400,
		padding: '6px 0 6px 6px',
		bodyCssClass: 'x-border-layout-ct',
		split: true,
		border: false,
		items: dialog.attributes.grid
	}
	
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
	
	dialog.attributes.east = {
		xtype: 'panel',
		region: 'east',
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
			dialog.grid,
			{title: ln.tab.sales, html:'Under construction'},
			{title: ln.tab.coupons, html:'Under construction'},
			dialog.attributes.tab,
			{title: ln.tab.currencies, html:'Under construction'},
			{title: ln.tab.manufacturers, html:'Under construction'},
			{title: ln.tab.settings, html:'Under construction'}
		],
		activeTab: 3
	});
	
	dialog.w = new Ext.Window({
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

Ext.ns('PC.plugin.pc_shop');
var cfg = {
	name: PC.i18n.mod.pc_shop.name,
	onclick: mod_pc_shop_click,
	icon: <?php echo json_encode(get_plugin_icon()) ?>,
	priority: <?php echo $mod['priority'] ?>
};
Ext.apply(PC.plugin.pc_shop, cfg);
</script>