<?php
/**
 * This script tries to detect current version of the plugin's database part.
 * 
 * @var array $cfg
 * @var PC_database $db
 * @var PC_core $core
 */

if( !$db->getTableInfo('shop_products') )
	return null; // plugin was not installed (enabled)

if( $db->getColumnInfo('shop_item_attributes', 'next_attribute_id') )
	return '1.6.0';

if( $db->getColumnInfo('shop_products', 'auth_user_id') )
	return '1.5.3';

if( $db->getTableInfo('shop_category_products') )
	return '1.5.2';

if( $db->getTableInfo('shop_attributes_categories') )
	return '1.5.1';

if( $db->getTableInfo('shop_coupons') )
	return '1.5.0';

if( $db->getColumnInfo('shop_products', 'created_on') )
	return '1.4.4';

if( $db->getTableInfo('shop_currency_rates') )
	return '1.4.2';

if( $db->getTableInfo('shop_currencies') )
	return '1.4.0';

if( $db->getTableInfo('shop_attributes') )
	return '1.3.0';

return '1.0.0';