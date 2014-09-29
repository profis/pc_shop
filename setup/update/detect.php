<?php
/**
 * This script tries to detect current version of the framework database.
 * 
 * @var array $cfg
 * @var PC_database $db
 * @var PC_core $core
 */

if( $db->getColumnInfo('shop_item_attributes', 'next_attribute_id') )
	return '1.6.0';

if( $db->getColumnInfo('shop_products', 'auth_user_id') )
	return '1.5.3';

if( $db->getTableInfo('shop_category_products') )
	return '1.5.2';

if( $db->getTableInfo('shop_attributes_categories') )
	return '1.5.1';

return '1.0.0';