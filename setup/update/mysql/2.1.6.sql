ALTER TABLE `{prefix}shop_products` 
	ADD COLUMN `views` INT(11) UNSIGNED NULL DEFAULT 0 AFTER `height`,
	ADD KEY `views` (`views`);