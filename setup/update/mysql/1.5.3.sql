ALTER TABLE  `{prefix}shop_products`
	ADD `auth_user_id` INT(10) UNSIGNED NOT NULL AFTER `created_on`;

INSERT IGNORE INTO `{prefix}variables` (`vkey`, `controller`, `site`, `ln`, `value`) VALUES 
('go_to_order_anon', 'pc_shop', 0, 'en', 'Order without registration'),
('go_to_order_anon', 'pc_shop', 0, 'ru', 'Заказать без регистрации'),
('go_to_order_anon', 'pc_shop', 0, 'lt', 'Užsakyti be registracijos'),
('discount', 'pc_shop', 0, 'lt', 'Pritaikyta nuolaida'),
('discount', 'pc_shop', 0, 'en', 'Applied discount'),
('discount', 'pc_shop', 0, 'ru', 'Скидка');
