CREATE TABLE IF NOT EXISTS `{prefix}shop_category_products` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `category_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `flags` smallint(5) NOT NULL,
  `price` decimal(15,2) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_id` (`category_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `{prefix}shop_order_items`
	CHANGE `price` `price` DECIMAL(10,2) UNSIGNED NOT NULL;

ALTER TABLE `{prefix}shop_prices`
	CHANGE `price` `price` DECIMAL(10,2) UNSIGNED NOT NULL;

ALTER TABLE `{prefix}shop_attribute_values`
	CHANGE `position` `position` SMALLINT(5) NOT NULL DEFAULT 0;
