CREATE TABLE IF NOT EXISTS `{prefix}shop_attributes_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) NOT NULL,
  `category_id` smallint(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_id` (`attribute_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `{prefix}shop_products`
	CHANGE `price` `price` DECIMAL(15,2) UNSIGNED NOT NULL;

ALTER TABLE `{prefix}shop_product_prices`
	CHANGE `price` `price` DECIMAL(15,2) UNSIGNED NOT NULL;

ALTER TABLE `{prefix}shop_order_items`
	CHANGE `price` `price` DECIMAL(15,2) UNSIGNED NOT NULL;

ALTER TABLE `{prefix}shop_attribute_values`
	ADD `position` `position` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0 AFTER `attribute_id`;
