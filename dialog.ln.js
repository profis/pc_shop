var ln =  {
	en: {
		name: 'e-Shop',
		tab: {
			orders: 'Orders',
			sales: 'Sales',
			coupons: 'Coupons',
			attributes: 'Attributes',
			attribute_categories: 'Attribute categories',
			currencies: 'Currencies',
			currency_rates: 'Currency rates',
			manufacturers: 'Manufacturers',
			delivery_options: 'Delivery options',
			payment_options: 'Payment options',
			settings: 'Settings',
			tab_import: 'Import'
		},
		date_time: 'Date/time',
		author: 'Author',
		comment: 'Comment',
		confirmed: 'Confirmed',
		with_selected: 'Selected',
		confirm: 'Confirm',
		unconfirm: 'Unconfirm',
		btn_clear: 'Clear',
		btn_confirm: 'Confirm',
		ln_currency_list: 'L​ist of currencies for selected Language',
		currency_rate: 'Rate',
		currency_rate_set: 'Currency rate',
		import_rate: 'Import rate',
		import_rates: 'Import rates',
		import_rate_error: 'Failed to import currency rates',
		base_currency_change_confirm: 'After changing the base currency exchange rates will be recalculated. Continue?',
		_delete_order: {
			button: 'Delete',
			confirmation: 'Confirmation',
			confirm_message: 'Are you sure you want to delete this order?',
			error: 'There was an error while trying to delete order.'
		},
		search_label: 'Show',
		search_id: 'with id',
		show_from: 'from',
		to: 'to',
		with_phrase: 'with phrase',
		and_status: 'and status',
		status: 'Status',
		status_labels: {
			0: 'Default',
			1: 'New',
			2: 'Waiting for payment',
			3: 'Being processed',
			4: 'Completed',
			5: 'Canceled'
		},
		payment_option_labels: {
			cash: 'Cash',
			web2pay: 'Mokėjimai.lt',
			bank: 'Bank wire'
		},
		delivery_option_labels: {
			shop: 'Take from shop',
			courier: 'From courier'
		},
		
		order_info: {
			id: 'Id',
			is_paid: 'Is paid',
			status: 'Status',
			payment: 'Payment',
			delivery: 'Delivery',
			date: 'Date',
			choose_order: 'Choose an order',
			buyer_info: 'Recipient information',
			name: 'Name',
			phone: 'Phone',
			email: 'Email',
			address: 'Address',
			additional_info: 'Additional info',
			items: 'Items in order',
			quantity: 'Quantity taken',
			price_for_each: 'Price for each',
			comment: 'Comment',
			delivery_price: 'Delivery price',
			cod_price: 'Cash on delivery fee',
			discount: 'Discount',
			total_price: 'Total price of the order',
			coupon_code: 'Coupon used'
		},
		attribute_categories: {
			name: 'Name',
			ref: 'Reference id'
		},
		manufacturers: {
			name: 'Name',
			code: 'Code'
		},
		delivery_options: {
			code: 'Code',
			enabled: 'Activated',
			no_delivery_price_from: 'No delivery price from',
			no_cod_price_from: 'No cod fee from'
		},
		payment_options: {
			code: 'Code',
			enabled: 'Activated',
			login: 'Login (project id)',
			key: 'Key',
			test: 'Test payments'
		},
		coupons: {
			code: 'Code',
			time_from: 'Valid from',
			time_to: 'Valid till',
			use_limit: 'Limit',
			discount: 'Discount',
			category: 'Category',
			is_for_hot: 'Valid for promo products',
			error: {
				//unique: 'Already exists'
			}
		},
		settings: {
			new_order_email_admin: 'New order notification email address',
			checkout_offer_to_register: 'Offer to register when buying',
			max_coupon_percentage: 'Maximum coupon discount percentage of total price'
		},
		_import: {
			import_method: 'Import method',
			label_file: 'Select file to import',
			label_category: 'Category',
			explain_category: 'Choose category',
			
			title_confirm_confirm: 'Confirm',
			msg_confirm_confirm: 'Do you really want to import data from the grid?',
			title_confirm_success: 'Success',
			msg_confirm_success: 'Data imported. Results: <br />',
			
			errors: {
				wrong_category: 'Wrong category',
				wrong_data: 'Wrong data',
				no_items: 'No items found in import data',
				no_file: 'No file was submitted',
				file_upload_error: 'Error uploading selected file',
				uploaded_file_not_found: 'Uploaded file was not found (probably server issue)'
			}
		}
	},
	lt: {
		name: 'e-Parduotuvė',
		tab: {
			orders: 'Užsakymai',
			sales: 'Pardavimai',
			coupons: 'Kuponai',
			attributes: 'Atributai',
			attribute_categories: 'Atributų kategorijos',
			currencies: 'Valiutos',
			currency_rates: 'Valiutų kursai',
			manufacturers: 'Gamintojai',
			delivery_options: 'Pristatymo būdai',
			payment_options: 'Apmokėjimo būdai',
			settings: 'Nustatymai',
			tab_import: 'Importas'
		},
		date_time: 'Data/laikas',
		author: 'Autorius',
		comment: 'Komentaras',
		confirmed: 'Patvirt.',
		with_selected: 'Pažymėtus',
		confirm: 'Patvirtinti',
		unconfirm: 'Slėpti',
		btn_clear: 'Išvalyti',
		btn_confirm: 'Patvirtinti',
		ln_currency_list: 'Pasirinktos kalbos valiutų sąrašas',
		currency_rate: 'Kursas',
		currency_rate_set: 'Nustatytas kursas',
		import_rate: 'Importuoti kursą',
		import_rates: 'Importuoti kursus',
		import_rate_error: 'Nepavyko importuoti valiutų kursų',
		base_currency_change_confirm: 'Pakeitus bazinę valiutą bus perskaičiuoti valiutų kursai. <br />Ar tęsti?',
		_delete_order: {
			button: 'Ištrinti',
			confirmation: 'Patvirtinimas',
			confirm_message: 'Ar jūs tikrai norite ištrinti šį užsakymą?',
			error: 'Užsakymo ištrinti nepavyko'
		},
		search_label: 'Rodyti',
		search_id: 'su užsakymo numeriu',
		show_from: 'nuo',
		to: 'iki',
		with_phrase: 'su fraze',
		and_status: 'ir statusu',
		status: 'Statusas',
		status_labels: {
			0: 'Numatytas',
			1: 'Naujas',
			2: 'Laukiama apmokėjimo',
			3: 'Apdorojamas',
			4: 'Užbaigtas',
			5: 'Atšauktas'
		},
		payment_option_labels: {
			cash: 'Grynais',
			web2pay: 'Mokėjimai.lt',
			bank: 'Bankinis pavedimas'
		},
		delivery_option_labels: {
			shop: 'Iš parduotuvės',
			courier: 'Iš kurjerio'
		},
		order_info: {
			id: 'Numeris',
			is_paid: 'Apmokėtas',
			status: 'Statusas',
			payment: 'Apmokėjimas',
			delivery: 'Pristatymas',
			date: 'Data',
			choose_order: 'Pasirinkite užsakymą',
			buyer_info: 'Pirkėjo informacija',
			name: 'Vardas',
			phone: 'Telefonas',
			email: 'El. pašras',
			address: 'Adresas',
			additional_info: 'Papildoma informacija',
			items: 'Užsakytos prekės',
			quantity: 'Kiekis',
			price_for_each: 'Vienos prekės kaina',
			comment: 'Komentaras',
			delivery_price: 'Pristatymo kaina',
			cod_price: 'Atsiskaitant grynaisiais pristatymo metu mokestis',
			discount: 'Pritaikyta nuolaida',
			total_price: 'Visa užsakymo suma',
			coupon_code: 'Panaudotas kuponas'
		},
		attribute_categories: {
			name: 'Pavadinimas',
			ref: 'Sąryšio ID'
		},
		manufacturers: {
			name: 'Pavadinimas',
			code: 'Kodas'
		},
		delivery_options: {
			code: 'Kodas',
			enabled: 'Aktyvuota',
			no_delivery_price_from: 'Nemokamas pristatymas nuo',
			no_cod_price_from: 'Nemokamas atsiskaitymas grynaisiais nuo'
		},
		
		payment_options: {
			code: 'Kodas',
			enabled: 'Aktyvuota',
			login: 'Prisijungimas',
			key: 'Raktas',
			test: 'Testiniai mokėjimai'
		},
		coupons: {
			code: 'Kodas',
			time_from: 'Galioja nuo',
			time_to: 'Galioja iki',
			use_limit: 'Limitas',
			discount: 'Nuolaida',
			category: 'Kategorija',
			is_for_hot: 'Galioja akcijinėms prekėms',
			error: {
				//unique: 'Jau egzistuoja'
			}
		},
		settings: {
			new_order_email_admin: 'E. paštas, kuriuo siunčiamas pranešimas apie nauja užsakymą',
			checkout_offer_to_register: 'Siūlyti registruotis perkant',
			max_coupon_percentage: 'Maksimali kupono nuolaida procentais nuo visos užsakymo kainos'
		},
		_import: {
			import_method: 'Importo tipas',
			label_file: 'Pasirinkite importo failą',
			label_category: 'Kategorija',
			explain_category: 'Pasirinkite kategoriją, į kurią importuoti produktus',
			
			title_confirm_confirm: 'Patvirtinti',
			msg_confirm_confirm: 'Ar tikrai importuoti duomenis iš lentelės?',
			title_confirm_success: 'Importas',
			msg_confirm_success: 'Duomenys suimportuoti. Rezultatai: <br />',
			
			errors: {
				wrong_category: 'Netinkama kategorija',
				wrong_data: 'Netinkami duomenys',
				no_items: 'Nerasta duomenų',
				no_file: 'Neįkeltas failas',
				file_upload_error: 'Klaida įkeliant failą',
				uploaded_file_not_found: 'Nerastas įkeltas failas (tikriausiai serverio problemos)'
			}
		}
	},
	ru: {
        name: 'e-Магазин',
		tab: {
			orders: 'Заказы',
			sales: 'Продажи',
			coupons: 'Купоны',
			attributes: 'Атрибуты',
			attribute_categories: 'Категории атрибутов',
			currencies: 'Валюты',
			currency_rates: 'Курсы валют',
			manufacturers: 'Производители',
			delivery_options: 'Варианты доставки',
			payment_options: 'Варианты оплаты',
			settings: 'Настройки',
			tab_import: 'Импорт'
		},
        date_time: 'Дата/время',
        author: 'Автор',
        comment: 'Комментарий',
        confirmed: 'Подтвержден',
        with_selected: 'Помеченные',
        confirm: 'Подтвердить',
        unconfirm: 'Скрыть',
		btn_clear: 'Очистить',
		btn_confirm: 'Подтвердить',
		ln_currency_list: 'Список валют для выбранного языка',
		currency_rate: 'Курс',
		currency_rate_set: 'Установленный курс',
		import_rate: 'Импортировать курс',
		import_rates: 'Импортировать курсы',
		import_rate_error: 'Не удалось импортировать курсы валют',
		base_currency_change_confirm: 'После изменения базовой валюты курсы валют будут пересчитаны. Продолжить?',
       _delete_order: {
			button: 'Удалить',
            confirmation: 'Подтверждение',
            confirm_message: 'Вы действительно хотите удалить учетную запись пользователя?',
            error: 'При попытке удвлить учетную запись пользователя произошла ошибка.'
        },
		search_label: 'Показывать',
		search_id: 'с номером',
        show_from: 'с',
        to: 'по',
        with_phrase: 'с фразой',
		and_status: 'and status',
		status: 'Статус',
		status_labels: {
			0: 'Созданный',
			1: 'Новый',
			2: 'В ожидании оплаты',
			3: 'Обрабатывается',
			4: 'Завершенный',
			5: 'Отменен'
		},
		payment_option_labels: {
			cash: 'Наличные',
			web2pay: 'Mokėjimai.lt',
			bank: 'Банковский'
		},
		delivery_option_labels: {
			shop: 'Из магазина',
			courier: 'Курьер'
		},
		order_info: {
			id: 'Номер',
			is_paid: 'Оплачен',
			status: 'Статус',
			payment: 'Оплата',
			delivery: 'Доставка',
			date: 'Дата',
			choose_order: 'Выберите заказ',
			buyer_info: 'Информация покупателя',
			name: 'Имя',
			phone: 'Телефон',
			email: 'E-почта',
			address: 'Адрес',
			additional_info: 'Дополнительная информация',
			items: 'Заказанные товары',
			quantity: 'Количество',
			price_for_each: 'Количество одного товара',
			comment: 'Комментарий',
			delivery_price: 'Стоимость доставки',
			cod_price: 'Сбор за наложенный платеж',
			discount: 'Ваучер скидка',
			total_price: 'Вся сумма заказа',
			coupon_code: 'Использован купон'
		},
		attribute_categories: {
			name: 'Название',
			ref: 'Идентификационный ID'
		},
		delivery_options: {
			code: 'Код',
			enabled: 'активированный',
			no_delivery_price_from: 'Бесплатная доставка от',
			no_cod_price_from: 'Бесплатная оплата наличными от'
		},
		payment_options: {
			code: 'Код',
			enabled: 'активированный',
			login: 'Логин (ID проекта)',
			key: 'Ключ',
			test: 'Тестовые платежи'
		},
		manufacturers: {
			name: 'Название',
			code: 'Код'
		},
		settings: {
			new_order_email_admin: 'Эл. почта, на которую отсылается сообщение о новом заказе',
			checkout_offer_to_register: 'Предлагать регистрацию при покупке',
			max_coupon_percentage: 'Maximum coupon discount percentage of total price'
		},
		_import: {
			import_method: 'Метод импорта',
			label_file: 'Выберите файл для импорта',
			label_category: 'Категория',
			explain_category: 'Выберите категорию',
			
			title_confirm_confirm: 'Подтверждение',
			msg_confirm_confirm: 'Вы действительно хотите импортировать данные из таблицы?',
			title_confirm_success: 'Импорт завершен успешно',
			msg_confirm_success: 'Данные импортированы успешно. Всего: <br />',
			
			errors: {
				wrong_category: 'Неправильные категорию',
				wrong_data: 'Неправильная категория',
				no_items: 'Никакие товары не были найдены',
				no_file: 'Файл не был представлен',
				file_upload_error: 'Ошибка при загрузке выбранного файла',
				uploaded_file_not_found: 'Загруженный файл не найден (вероятно, проблема с сервером)'
			}
		}
    }
};

PC.utils.localize('mod.pc_shop', ln);

var hook_params = {};
PC.hooks.Init('plugin/pc_shop/dialog-localize', hook_params);