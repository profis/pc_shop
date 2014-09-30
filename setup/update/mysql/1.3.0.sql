CREATE TABLE `{prefix}shop_attribute_categories` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `flags` smallint(5) unsigned NOT NULL DEFAULT '1',
  `ref` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `{prefix}shop_attribute_category_contents` (
  `attr_category_id` int(10) unsigned NOT NULL,
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`attr_category_id`,`ln`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `{prefix}shop_attribute_contents` (
  `attribute_id` int(10) unsigned NOT NULL,
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  KEY `attribute_id` (`attribute_id`),
  KEY `attribute_id_2` (`attribute_id`,`ln`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `{prefix}shop_attribute_value_contents` (
  `value_id` int(10) unsigned NOT NULL,
  `ln` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `value_id` (`value_id`,`ln`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `{prefix}shop_attribute_values` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `{prefix}shop_attributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_custom` tinyint(1) unsigned NOT NULL,
  `is_searchable` tinyint(1) unsigned NOT NULL,
  `is_category_attribute` tinyint(1) unsigned NOT NULL,
  `position` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ref` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `{prefix}shop_category_product_filters` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `category_id` int(10) NOT NULL,
  `attribute` smallint(5) NOT NULL,
  `disabled` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_id` (`category_id`,`attribute`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `{prefix}shop_delivery_option_contents` (
  `delivery_option_id` smallint(5) unsigned NOT NULL,
  `ln` varchar(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `delivery_option_id` (`delivery_option_id`,`ln`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `{prefix}shop_delivery_options` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `position` smallint(5) NOT NULL DEFAULT '0',
  `delivery_price` decimal(10,2) unsigned NOT NULL,
  `no_delivery_price_from` decimal(10,2) unsigned NOT NULL,
  `cod_price` decimal(10,2) unsigned NOT NULL,
  `no_cod_price_from` decimal(10,2) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE `{prefix}shop_item_attributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` mediumint(8) unsigned NOT NULL,
  `attribute_id` int(10) unsigned NOT NULL,
  `flags` smallint(5) unsigned NOT NULL,
  `value_id` int(10) unsigned DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `{prefix}shop_payment_option_contents` (
  `payment_option_id` smallint(5) unsigned NOT NULL,
  `ln` varchar(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `payment_option_id` (`payment_option_id`,`ln`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `{prefix}shop_payment_options` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `position` smallint(5) NOT NULL DEFAULT '0',
  `login` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `payment_key` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `test` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE `{prefix}shop_product_categories` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  `flags` smallint(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_id` (`product_id`,`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `{prefix}shop_product_products` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL,
  `product_id_2` int(10) NOT NULL,
  `flags` smallint(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `{prefix}shop_categories`
	ADD `pid` INT(10) UNSIGNED DEFAULT NULL AFTER `parent_id`,
	ADD `redirect` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL AFTER `percentage_discount`,
	ADD `filters` TEXT COLLATE utf8_unicode_ci NOT NULL AFTER `redirect`,
	ADD KEY `lft` (`lft`),
	ADD KEY `rgt` (`rgt`);

ALTER TABLE `{prefix}shop_category_contents`
	ADD `custom_name` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL AFTER `name`,
	CHANGE `description` `description` MEDIUMTEXT COLLATE utf8_unicode_ci NOT NULL,
	CHANGE `seo_description` `seo_description` TEXT COLLATE utf8_unicode_ci NOT NULL,
	CHANGE `seo_keywords` `seo_keywords` TEXT COLLATE utf8_unicode_ci NOT NULL,
	ADD `permalink` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL AFTER `route`;

ALTER TABLE `{prefix}shop_manufacturers`
	ADD CONSTRAINT `name` UNIQUE (`name`);

ALTER TABLE `{prefix}shop_order_statuses`
	CHANGE `user_id` `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';
	
ALTER TABLE `{prefix}shop_orders`
	ADD `currency` VARCHAR(3) COLLATE utf8_unicode_ci NOT NULL AFTER `total_price`,
	ADD `payment_option` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL AFTER `currency`,
	ADD `delivery_option` VARCHAR(20) COLLATE utf8_unicode_ci NOT NULL AFTER `payment_option`,
	ADD `delivery_price` DECIMAL(10,2) UNSIGNED NOT NULL AFTER `delivery_option`,
	ADD `cod_price` DECIMAL(10,2) UNSIGNED NOT NULL AFTER `delivery_price`,
	ADD `is_paid` TINYINT(1) UNSIGNED NOT NULL AFTER `is_paid`,
	ADD `status` SMALLINT(5) NOT NULL DEFAULT '0' AFTER `status`,
	ADD `data` TEXT COLLATE utf8_unicode_ci NOT NULL AFTER `data`;

ALTER TABLE `{prefix}shop_product_contents`
	CHANGE `short_description` `short_description` TEXT COLLATE utf8_unicode_ci NOT NULL,
	CHANGE `description` `description` MEDIUMTEXT COLLATE utf8_unicode_ci NOT NULL,
	ADD FULLTEXT KEY `name` (`name`),
	ADD FULLTEXT KEY `description` (`description`);

ALTER TABLE `{prefix}shop_products`
	ADD `hot_from` INT(10) UNSIGNED DEFAULT NULL AFTER `percentage_discount`,
	ADD `import_method` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL AFTER `price`,
	ADD `state` TINYINT(3) NOT NULL DEFAULT '0' AFTER `import_method`;

ALTER TABLE `{prefix}shop_resources`
	ADD `position` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' AFTER `resource_id`,
	ADD KEY `position` (`position`);

INSERT IGNORE INTO `{prefix}variables` (`vkey`, `controller`, `site`, `ln`, `value`) VALUES 

('description', 'pc_shop', 0, 'lt', 'Aprašymas'),
('description', 'pc_shop', 0, 'en', 'Description'),
('description', 'pc_shop', 0, 'ru', 'Описание'),

('attribute', 'pc_shop', 0, 'lt', 'Parametras'),
('attribute', 'pc_shop', 0, 'en', 'Parameter'),
('attribute', 'pc_shop', 0, 'ru', 'Параметр'),

('attributes', 'pc_shop', 0, 'lt', 'Parametrai'),
('attributes', 'pc_shop', 0, 'en', 'Parameters'),
('attributes', 'pc_shop', 0, 'ru', 'Параметры'),

('cart', 'pc_shop', 0, 'en', 'Cart'),
('cart', 'pc_shop', 0, 'ru', 'Корзина'),
('cart', 'pc_shop', 0, 'lt', 'Krepšelis'),

('go_to_cart', 'pc_shop', 0, 'en', 'Go to cart'),
('go_to_cart', 'pc_shop', 0, 'ru', 'B корзину'),
('go_to_cart', 'pc_shop', 0, 'lt', 'Į krepšelį'),

('cart_full_price', 'pc_shop', 0, 'en', 'All price'),
('cart_full_price', 'pc_shop', 0, 'ru', 'Вся цена'),
('cart_full_price', 'pc_shop', 0, 'lt', 'Visa kaina'),

('cart_empty', 'pc_shop', 0, 'en', 'Your cart is empty'),
('cart_empty', 'pc_shop', 0, 'ru', 'Ваша корзина пуста'),
('cart_empty', 'pc_shop', 0, 'lt', 'Jūsų krepšelis tuščias'),

('price', 'pc_shop', 0, 'en', 'Price'),
('price', 'pc_shop', 0, 'ru', 'Цена'),
('price', 'pc_shop', 0, 'lt', 'Kaina'),

('price_from', 'pc_shop', 0, 'en', 'Price from'),
('price_from', 'pc_shop', 0, 'ru', 'Цена от'),
('price_from', 'pc_shop', 0, 'lt', 'Kaina nuo'),

('price_to', 'pc_shop', 0, 'en', 'Price to'),
('price_to', 'pc_shop', 0, 'ru', 'Цена до'),
('price_to', 'pc_shop', 0, 'lt', 'Kaina iki'),

('sold_out', 'pc_shop', 0, 'en', 'Sold out'),
('sold_out', 'pc_shop', 0, 'ru', 'Продана'),
('sold_out', 'pc_shop', 0, 'lt', 'Išparduota'),

('to_basket', 'pc_shop', 0, 'en', 'Add to cart'),
('to_basket', 'pc_shop', 0, 'ru', 'B корзину'),
('to_basket', 'pc_shop', 0, 'lt', 'Į krepšelį'),

('details', 'pc_shop', 0, 'lt', 'Plačiau'),
('details', 'pc_shop', 0, 'en', 'View details'),
('details', 'pc_shop', 0, 'ru', 'Подробнее'),

('currency', 'pc_shop', 0, 'lt', 'Valiuta'),
('currency', 'pc_shop', 0, 'en', 'Currency'),
('currency', 'pc_shop', 0, 'ru', 'Валюта'),

('sort_by', 'pc_shop', 0, 'en', 'Sort by'),
('sort_by', 'pc_shop', 0, 'ru', 'Сортировать по'),
('sort_by', 'pc_shop', 0, 'lt', 'Rūšiuoti pagal'),

('sort_by_price_asc', 'pc_shop', 0, 'en', 'Price ▲'),
('sort_by_price_asc', 'pc_shop', 0, 'ru', 'Цена ▲'),
('sort_by_price_asc', 'pc_shop', 0, 'lt', 'Kainą ▲'),

('sort_by_price_desc', 'pc_shop', 0, 'en', 'Price ▼'),
('sort_by_price_desc', 'pc_shop', 0, 'ru', 'Цена ▼'),
('sort_by_price_desc', 'pc_shop', 0, 'lt', 'Kainą ▼'),

('sort_by_name_asc', 'pc_shop', 0, 'en', 'Title A-Z'),
('sort_by_name_asc', 'pc_shop', 0, 'ru', 'Название А-Я'),
('sort_by_name_asc', 'pc_shop', 0, 'lt', 'Pavadinimą A-Ž'),

('order', 'pc_shop', 0, 'en', 'Order'),
('order', 'pc_shop', 0, 'ru', 'Заказ'),
('order', 'pc_shop', 0, 'lt', 'Užsakymas'),

('go_to_order', 'pc_shop', 0, 'en', 'Order'),
('go_to_order', 'pc_shop', 0, 'ru', 'Заказать'),
('go_to_order', 'pc_shop', 0, 'lt', 'Užsakyti'),

('go_to_order_anon', 'pc_shop', 0, 'en', 'Order without registration'),
('go_to_order_anon', 'pc_shop', 0, 'ru', 'Заказать без регистрации'),
('go_to_order_anon', 'pc_shop', 0, 'lt', 'Užsakyti be registracijos'),

('order_finish', 'pc_shop', 0, 'en', 'Finish order'),
('order_finish', 'pc_shop', 0, 'ru', 'Оформить заказ'),
('order_finish', 'pc_shop', 0, 'lt', 'Baigti užsakymą'),

('order_id', 'pc_shop', 0, 'ru', 'Номер заказа'),
('order_id', 'pc_shop', 0, 'en', 'Order number'),
('order_id', 'pc_shop', 0, 'lt', 'Užsakymo numeris'),

('order_email', 'pc_shop', 0, 'lt', 'El. paštas'),
('order_email', 'pc_shop', 0, 'ru', 'Эл. почта'),
('order_email', 'pc_shop', 0, 'en', 'E-mail'),
('order_name', 'pc_shop', 0, 'lt', 'Kontaktinis asmuo'),
('order_name', 'pc_shop', 0, 'ru', 'Контактное лицо'),
('order_name', 'pc_shop', 0, 'en', 'Contact person'),
('order_phone', 'pc_shop', 0, 'lt', 'Telefonas'),
('order_phone', 'pc_shop', 0, 'ru', 'Телефон'),
('order_phone', 'pc_shop', 0, 'en', 'Telephone'),
('order_country', 'pc_shop', 0, 'lt', 'Šalis'),
('order_country', 'pc_shop', 0, 'en', 'Country'),
('order_country', 'pc_shop', 0, 'ru', 'Страна'),
('order_city', 'pc_shop', 0, 'lt', 'Miestas'),
('order_city', 'pc_shop', 0, 'ru', 'Город '),
('order_city', 'pc_shop', 0, 'en', 'City'),
('order_address', 'pc_shop', 0, 'lt', 'Adresas'),
('order_address', 'pc_shop', 0, 'ru', 'Адрес'),
('order_address', 'pc_shop', 0, 'en', 'Address'),
('order_post_index', 'pc_shop', 0, 'lt', 'Pašto indeksas'),
('order_post_index', 'pc_shop', 0, 'en', 'Postal index'),
('order_post_index', 'pc_shop', 0, 'ru', 'Почтовый индекс'),
('order_comment', 'pc_shop', 0, 'lt', 'Komentaras'),
('order_comment', 'pc_shop', 0, 'ru', 'Комментарий'),
('order_comment', 'pc_shop', 0, 'en', 'Comment'),


('order_is_company', 'pc_shop', 0, 'lt', 'Perka įmonė?'),
('order_is_company', 'pc_shop', 0, 'en', 'Is it a company ordering?'),
('order_is_company', 'pc_shop', 0, 'ru', 'Заказывает компания?'),
('order_comment_more', 'pc_shop', 0, 'lt', '(pageidavimai arba papildoma informacija kurjeriui - laiptinės durų kodas ir t.t.)'),
('order_comment_more', 'pc_shop', 0, 'en', '(requests or additional information for the delivery man: door code, etc.)'),
('order_comment_more', 'pc_shop', 0, 'ru', '(пожелания или дополнительная информация для курьера: код двери и т.д.)'),
('order_company_code', 'pc_shop', 0, 'lt', 'Įmonės kodas'),
('order_company_code', 'pc_shop', 0, 'en', 'Company code'),
('order_company_code', 'pc_shop', 0, 'ru', 'Код компании'),
('order_company_name', 'pc_shop', 0, 'lt', 'Įmonės pavadinimas'),
('order_company_name', 'pc_shop', 0, 'en', 'Company name'),
('order_company_name', 'pc_shop', 0, 'ru', 'Название компании'),
('order_company_pvm_code', 'pc_shop', 0, 'lt', 'Įmonės PVM kodas'),
('order_company_pvm_code', 'pc_shop', 0, 'en', 'Compaty VAT code'),
('order_company_pvm_code', 'pc_shop', 0, 'ru', 'НДС код компании'),

('cod_price', 'pc_shop', 0, 'en', 'Cash on delivery fee'),
('cod_price', 'pc_shop', 0, 'ru', 'Сбор за наложенный платеж'),
('cod_price', 'pc_shop', 0, 'lt', 'Atsiskaitant grynaisiais pristatymo metu mokestis'),

('delivery', 'pc_shop', 0, 'lt', 'Pristatymas'),
('delivery', 'pc_shop', 0, 'en', 'Delivery'),
('delivery', 'pc_shop', 0, 'ru', 'Доставка'),
('delivery_option', 'pc_shop', 0, 'lt', 'Pristatymo būdas'),
('delivery_option', 'pc_shop', 0, 'en', 'Shipping method'),
('delivery_option', 'pc_shop', 0, 'ru', 'Способ доставки'),
('delivery_price', 'pc_shop', 0, 'en', 'Delivery price'),
('delivery_price', 'pc_shop', 0, 'ru', 'Стоимость доставки'),
('delivery_price', 'pc_shop', 0, 'lt', 'Pristatymo kaina'),
('delivery_option_courier', 'pc_shop', 0, 'lt', 'Atsiėmimas iš kurjerio'),
('delivery_option_courier', 'pc_shop', 0, 'en', 'Collect from a delivery man'),
('delivery_option_courier', 'pc_shop', 0, 'ru', 'Курьерская служба доставки'),
('delivery_option_shop', 'pc_shop', 0, 'lt', 'Atsiėmimas parduotuvėje'),
('delivery_option_shop', 'pc_shop', 0, 'en', 'Collect from the shop'),
('delivery_option_shop', 'pc_shop', 0, 'ru', 'Забрать из магазина'),

('payment', 'pc_shop', 0, 'lt', 'Apmokėjimas'),
('payment', 'pc_shop', 0, 'en', 'Payment'),
('payment', 'pc_shop', 0, 'ru', 'Оплата'),

('payment_option_cash', 'pc_shop', 0, 'lt', 'Grynais'),
('payment_option_cash', 'pc_shop', 0, 'en', 'Cash'),
('payment_option_cash', 'pc_shop', 0, 'ru', 'Наличные'),
('payment_option_web2pay', 'pc_shop', 0, 'lt', 'Per mokėjimai.lt sistema'),
('payment_option_web2pay', 'pc_shop', 0, 'en', 'Using mokėjimai.lt system'),
('payment_option_web2pay', 'pc_shop', 0, 'ru', 'Используя систему mokėjimai.lt'),


('product_name', 'pc_shop', 0, 'ru', 'Название продукта'),
('product_name', 'pc_shop', 0, 'lt', 'Prekės pavadinimas'),
('product_name', 'pc_shop', 0, 'en', 'Product name'),
('item_amount', 'pc_shop', 0, 'lt', 'Kiekis'),
('item_amount', 'pc_shop', 0, 'en', 'Quantity'),
('item_amount', 'pc_shop', 0, 'ru', 'Количество'),
('item_price', 'pc_shop', 0, 'lt', 'Vieneto kaina'),
('item_price', 'pc_shop', 0, 'en', 'Unit price'),
('item_price', 'pc_shop', 0, 'ru', 'Цена одного товара'),
('same_items_price', 'pc_shop', 0, 'en', 'All price'),
('same_items_price', 'pc_shop', 0, 'ru', 'Вся цена'),
('same_items_price', 'pc_shop', 0, 'lt', 'Visa kaina'),
('items_price', 'pc_shop', 0, 'lt', 'Prekių kaina'),
('items_price', 'pc_shop', 0, 'en', 'The price of goods'),
('items_price', 'pc_shop', 0, 'ru', 'Цена товаров'),
('order_total_price', 'pc_shop', 0, 'lt', 'Visa suma'),
('order_total_price', 'pc_shop', 0, 'ru', 'Общая цена'),
('order_total_price', 'pc_shop', 0, 'en', 'Total price'),

('new_order_email_admin', 'pc_shop', 0, 'lt', ''),
('new_order_email_admin', 'pc_shop', 0, 'en', ''),
('new_order_email_admin', 'pc_shop', 0, 'ru', ''),

('new_order_to_admin', 'pc_shop', 0, 'lt', 'Gautas naujas užsakymas'),
('new_order_to_admin', 'pc_shop', 0, 'en', 'New order'),
('new_order_to_admin', 'pc_shop', 0, 'ru', 'Новый заказ'),
('new_paid_order_to_admin', 'pc_shop', 0, 'lt', 'Gautas naujas apmokėtas užsakymas'),
('new_paid_order_to_admin', 'pc_shop', 0, 'en', 'New paid order'),
('new_paid_order_to_admin', 'pc_shop', 0, 'ru', 'Новый оплачен заказ'),
('new_order_email_sender_to_admin', 'pc_shop', 0, 'lt', ''),
('new_order_email_sender_to_admin', 'pc_shop', 0, 'en', ''),
('new_order_email_sender_to_admin', 'pc_shop', 0, 'ru', ''),
('new_order_email_subject_to_admin', 'pc_shop', 0, 'lt', 'Gautas užsakymas'),
('new_order_email_subject_to_admin', 'pc_shop', 0, 'en', 'New order'),
('new_order_email_subject_to_admin', 'pc_shop', 0, 'ru', 'Новый заказ'),
('new_paid_order_email_subject_to_admin', 'pc_shop', 0, 'lt', 'Gautas užsakymas (apmokėtas)'),
('new_paid_order_email_subject_to_admin', 'pc_shop', 0, 'en', 'New order (paid)'),
('new_paid_order_email_subject_to_admin', 'pc_shop', 0, 'ru', 'Новый заказ (оплаченый)'),


('new_order_to_buyer', 'pc_shop', 0, 'lt', 'Gautas jūsų užsakymas'),
('new_order_to_buyer', 'pc_shop', 0, 'en', 'We have received your order'),
('new_order_to_buyer', 'pc_shop', 0, 'ru', 'Получен новый заказ'),
('new_paid_order_to_buyer', 'pc_shop', 0, 'lt', 'Gautas jūsų apmokėtas užsakymas'),
('new_paid_order_to_buyer', 'pc_shop', 0, 'en', 'We have received your paid order'),
('new_paid_order_to_buyer', 'pc_shop', 0, 'ru', 'Получен новый оплачен заказ'),
('new_order_email_sender_to_buyer', 'pc_shop', 0, 'lt', ''),
('new_order_email_sender_to_buyer', 'pc_shop', 0, 'en', ''),
('new_order_email_sender_to_buyer', 'pc_shop', 0, 'ru', ''),
('new_order_email_subject_to_buyer', 'pc_shop', 0, 'lt', 'Jūsų užsakymas gautas'),
('new_order_email_subject_to_buyer', 'pc_shop', 0, 'en', 'Your order received'),
('new_order_email_subject_to_buyer', 'pc_shop', 0, 'ru', 'Ваш заказ получен'),
('new_paid_order_email_subject_to_buyer', 'pc_shop', 0, 'lt', 'Jūsų užsakymas gautas ir apmokėtas'),
('new_paid_order_email_subject_to_buyer', 'pc_shop', 0, 'en', 'Your order has been received and paid'),
('new_paid_order_email_subject_to_buyer', 'pc_shop', 0, 'ru', 'Ваш заказ получен и оплачен'),


('online_payment_error', 'pc_shop', 0, 'lt', 'Apmokėjimo klaida'),
('online_payment_error', 'pc_shop', 0, 'en', 'Payment error'),
('online_payment_error', 'pc_shop', 0, 'ru', 'Ошибка'),
('online_payment_failed', 'pc_shop', 0, 'lt', 'Mokėjimas nepavyko'),
('online_payment_failed', 'pc_shop', 0, 'en', 'Payment failed'),
('online_payment_failed', 'pc_shop', 0, 'ru', 'Оплата не удалась'),
('online_payment_success', 'pc_shop', 0, 'lt', 'Apmokėta'),
('online_payment_success', 'pc_shop', 0, 'en', 'Payment succesful'),
('online_payment_success', 'pc_shop', 0, 'ru', 'Уплаченно'),

('order_success', 'pc_shop', 0, 'lt', 'Užsakymas sėkmingai sukurtas ir pradėtas vykdyti'),
('order_success', 'pc_shop', 0, 'en', 'Your order has been received and is being processed'),
('order_success', 'pc_shop', 0, 'ru', 'Заказ принят и выполняется'),

('order_success_summary', 'pc_shop', 0, 'lt', 'Užsakymo duomenys išsiųsti jums elektroniniu paštu.'),
('order_success_summary', 'pc_shop', 0, 'en', 'The information about the order was sent to your email.'),
('order_success_summary', 'pc_shop', 0, 'ru', 'Данные о заказе высланы на вашу эл. почту.'),

('search_label', 'pc_shop', 0, 'lt', 'Įveskite paieškos žodžius'),
('search_label', 'pc_shop', 0, 'en', 'Enter your search terms'),
('search_label', 'pc_shop', 0, 'ru', 'Введите условия поиска'),

('search_button', 'pc_shop', 0, 'lt', 'Ieškoti'),
('search_button', 'pc_shop', 0, 'en', 'Search'),
('search_button', 'pc_shop', 0, 'ru', 'Поиск'),

('filter_button', 'pc_shop', 0, 'lt', 'Filtruoti'),
('filter_button', 'pc_shop', 0, 'en', 'Filter'),
('filter_button', 'pc_shop', 0, 'ru', 'Фильтровать'),

('filter_cancel', 'pc_shop', 0, 'lt', 'Atšaukti'),
('filter_cancel', 'pc_shop', 0, 'en', 'Cancel'),
('filter_cancel', 'pc_shop', 0, 'ru', 'Отменить'),

('discount', 'pc_shop', 0, 'lt', 'Pritaikyta nuolaida'),
('discount', 'pc_shop', 0, 'en', 'Applied discount'),
('discount', 'pc_shop', 0, 'ru', 'Скидка'),

('manufacturer', 'pc_shop', 0, 'lt', 'Gamintojas'),
('manufacturer', 'pc_shop', 0, 'en', 'Manufacturer'),
('manufacturer', 'pc_shop', 0, 'ru', 'Производитель');