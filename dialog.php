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

$items_per_page = 10;
$plugin_path = $cfg['url']['base'].$cfg['directories']['plugins'].'/'.$plugins->Get_currently_parsing().'/'.basename(__FILE__);

if (isset($_GET['action'])) {
	header('Content-Type: application/json');
	header('Cache-Control: no-cache');
	$out = array();
	switch ($_GET['action']) {
		case 'get':
			$start = (int)v($_POST['start']);
			$limit = (int)v($_POST['limit']);
			if ($start < 0) $start = 0;
			if ($limit < 1) $limit = $items_per_page;
			$date_from = v($_POST['date_from']);
			$date_to = v($_POST['date_to']);
			$search_phrase = v($_POST['search_phrase']);
			$site_id = v($_POST['site']);
			if (!ctype_digit($site_id)) $site_id = 0; //all sites
			//---
			//
			$where = array();
			$parameters = array();
			//---
			if (!empty($date_from)) {
				if (!empty($date_to)) {
					$where[] = 'date between ? and ?';
					array_push($parameters, strtotime($date_from), strtotime($date_to)+86400);
				}
				else {
					$where[] = 'date >= ?';
					$parameters[] = strtotime($date_from);
				}
			}
			elseif (!empty($date_to)) {
				$where[] = 'date <= ?';
				$parameters[] = strtotime($date_to)+86400;
			}
			//---
			if (!empty($search_phrase)) {
				$where[] = 'comment like ?';
				$parameters[] = '%'.$search_phrase.'%';
			};
			if ($site_id>0) {
				$where[] = 'site=?';
				$parameters[] = $site_id;
			}
			$shop = $core->Get_object('PC_shop_manager');
			$paging = array(
				'page'=> v($additional['page'], 1),
				'perPage'=> v($additional['perPage'], 30)
			);
			$params = array(
				'paging'=> &$paging
			);
			$out['list'] = $shop->orders->Get(null, $params);
			$out['total'] = $paging->Get_total();
			//---
			break;
		case 'confirm':
			$ids = v($_POST['ids']);
			$confirm = v($_POST['confirm'], 1);
			if (!empty($ids)) {
				$ids = explode(',', $ids);
				if (count($ids)) {
					if ($pc_comments->Confirm($ids, $confirm)) {
						$out = array('success'=> true);
						break;
					}
					$error = 'Database error';
				}
				else $error = 'These comments was not found in the database.';
			}
			else $error = 'No comments were selected.';
			$out['error'] = (!empty($error)?$error:'');
			break;
		case 'delete':
			$ids = v($_POST['ids']);
			if (!empty($ids)) {
				$ids = explode(',', $ids);
				if (count($ids)) {
					if ($pc_comments->Delete($ids)) {
						$out = array('success'=> true);
						break;
					}
					$error = 'Database error';
				}
				else $error = 'These comments was not found in the database.';
			}
			else $error = 'No comments were selected.';
			$out['error'] = (!empty($error)?$error:'');
			break;
		default:
			$out['error'] = 'Unknown action';
	}
	echo json_encode($out);
	return;
}

$mod['name'] = 'Shop / Orders';
$mod['onclick'] = 'mod_pc_shop_click()';
$mod['priority'] = 100;
?>
<style type="text/css"></style>
<script type="text/javascript">
PC.utils.localize('mod.pc_shop', {
	en: {
		name: 'Shop / Orders',
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
		with_phrase: 'with phrase'
	},
	lt: {
		name: 'Parduotuvė / Užsakymai',
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
		with_phrase: 'su fraze'
	},
	ru: {
        name: 'Shop / Orders',
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
        with_phrase: 'с фразой'
    }
});

Ext.namespace('PC.plugins');

function mod_pc_shop_click() {
	var dialog = PC.plugin.pc_shop;
	var ln = PC.i18n.mod.pc_shop;
	var items_per_page = <?php echo $items_per_page; ?>;
	var initial_date_from = '';
	var initial_date_to = '';
	
	dialog.Initial_site_value = PC.global.site;
	
	if (dialog.w) {
		dialog.w.show();
		return;
	}
	
	var plugin_path = '<?php echo $plugin_path; ?>';
	
	dialog.gridSelectionModel = new Ext.grid.CheckboxSelectionModel({
		listeners: {
			selectionchange: function(sm) {
				var selected = dialog.grid.selModel.getSelections();
				if (selected.length) {
					dialog.w.action_delete.enable();
				}
				else {
					dialog.w.action_delete.disable();
				}
			}
		}
	});
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
		url: plugin_path +'?action=get&ln='+ PC.global.ln,
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
		//layout: 'fit',
		region: 'center',
		border: false,
		store: dialog.store,
		plugins: dialog.expander,
        columns: [
			//uzsakovas, adresas, data, prekiu skaicius, suma
			dialog.gridSelectionModel,
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
		sm: dialog.gridSelectionModel
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
	
	dialog.w = new Ext.Window({
		title: ln.name,
		layout: 'border',
		width: 800,
		height: 400,
		maximizable: true,
		items: [dialog.grid],
		tbar: [
			{	ref: '../action_delete',
				disabled: true,
				text: ln._delete,
				icon: 'images/delete.png',
				handler: function() {
					var ids = dialog.getSelectedIds();
					if (!ids) return;
					Ext.Ajax.request({
						url: plugin_path +'?action=delete',
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
			{xtype:'tbtext', text: ln.show_from},
			{	ref: '../date_from',
				xtype:'datefield',
				width: 80,
				value: initial_date_from,
				maxValue: new Date()
			},
			{xtype:'tbtext', text: ln.to, style:'margin: 0 2px;'},
			{	ref: '../date_to',
				xtype:'datefield',
				width: 80,
				value: initial_date_to,
				maxValue: new Date()
			},
			{	xtype:'tbtext',
				text: ln.with_phrase,
				style:'margin: 0 2px;'
			},
			{	ref: '../search_phrase',
				xtype:'textfield',
				width: 80
			},
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
			pageSize: items_per_page,
			prependButtons: true
		}),
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

PC.plugin.pc_shop = {
	name: PC.i18n.mod.pc_shop.name,
	onclick: mod_pc_shop_click,
	icon: <?php echo json_encode(get_plugin_icon()) ?>,
	priority: <?php echo $mod['priority'] ?>
};
</script>