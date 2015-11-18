CREATE TABLE IF NOT EXISTS `{prefix}shop_attributes_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_id` (`attribute_id`,`category_id`),
  CONSTRAINT `fk_sacm_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `{prefix}shop_attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sacm_attribute_category` FOREIGN KEY (`category_id`) REFERENCES `{prefix}shop_attribute_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
