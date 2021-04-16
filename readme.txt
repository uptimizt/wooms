=== WooMS ===
Contributors: casepress
Donate link: https://wpcraft.ru/product/wooms-extra/
Tags: moysklad, woocommerce, sync, integration
Requires at least: 4.0
Tested up to: 5.7
Stable tag: 4.3
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

MoySklad (moysklad.ru) and WooCommerce - sync, integration, connection

== Description ==

Integration WooCommerce & MoySklad http://moysklad.ru (for Russia)

Интеграция приложения МойСклад (торговля, опт, розница, склад, производство, CRM) и WooCommerce (WordPress)

Особенности:

*   Синхронизация товаров по протоколу REST API
*   Загрузка категорий
*   Загрузка картинок
*   Простые настройки

[Руководство по быстрому началу работы](https://github.com/wpcraft-ru/wooms/wiki/GettingStarted)

[Инструкция по правильному запуску Интернет магазина на базе WordPress & WooCommerce](https://wpcraft.ru/blog/internet-magazin-na-wordpress-woocommerce-storefront-mojsklad/)

Для больших возможностей можно приобрести расширенную версию: [https://wpcraft.ru/product/wooms-extra/](https://wpcraft.ru/product/wooms-extra/)

Исходники для желающих принять участие в разработке: [https://github.com/wpcraft-ru/wooms/](https://github.com/wpcraft-ru/wooms/)

По вопросам доработки: [https://github.com/wpcraft-ru/wooms/issues](https://github.com/wpcraft-ru/wooms/issues)

Ссылка на релизы с описанием улучшений: [https://github.com/wpcraft-ru/wooms/releases](https://github.com/wpcraft-ru/wooms/releases)

Страница плагина: [https://wordpress.org/plugins/wooms/](https://wordpress.org/plugins/wooms/)

Roadmap (Статус задача по разработке): [https://github.com/wpcraft-ru/wooms/projects/1](https://github.com/wpcraft-ru/wooms/projects/1)

Плагин спроектирован таким образом, что относительно просто позволяет дорабатывать механику под задачи и автоматизацию конкретного магазина/каталога.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to Settings / MoySklad and setup
1. Got to Tools / MoySklad and run sync

== Frequently Asked Questions ==

= Какие товары синхронизируются? =

По умолчанию только с артикулами. Чтобы можно было синхронизировать товары МойСклад и сайта без удаления.
Но если включить опцию UUID, то товары можно синхронизировать без артикула. В этом случае придется сначала удалить продукты с сайта.

= Что нужно чтобы синхронизация заработала? =

Нужно правильно указать реквизиты доступа на странице настроек плагина в панели управления сайтом. На стороне МойСклад ничего делать не нужно.

= Как устроен механизм синхронизации? =

Используется протокол REST API. Без протокола CommerceML. Вся логика находится на стороне сайта и сайт сам запрашиует данные из МойСклад.
В зависимости от особенностей конфигурации сервера бот синхронизации может зависать из-за таймаутов. Для этого в плагине встроен супервайзер, который следит за ботом и пинает его в случае остановки.

= Какие минимальные требования? =

WordPress 4.5
WooCommerce 3.0 - мб будет работать на Woo 2.х но не факт.
PHP 5.6


== Screenshots ==

1. Страница настроек.
2. Страница продуктов
3. Журнал обработки

== Changelog ==

= 8.2 =
- Сокрытие wooms_id из деталей Заказа видимых клиенту https://github.com/wpcraft-ru/wooms/issues/398
- Проверка совместимости с WooCommerce 5.0 https://github.com/wpcraft-ru/wooms/issues/396

= 8.1 =
- Краткое описание товара вместо полного как опция https://github.com/wpcraft-ru/wooms/issues/347
- XT: При создании нового контрагента - нет email https://github.com/wpcraft-ru/wooms/issues/346
* Тест плагинов с новыми версиями WordPress и WooCommerce https://github.com/wpcraft-ru/wooms/issues/396
* [XT] Публикация решения для отображения остатков со множества складов через ACF https://github.com/wpcraft-ru/wooms/issues/327
* [XT] Публикация решения для передачи склада в заказе через методы доставки https://github.com/wpcraft-ru/wooms/issues/327

= 8.0 =
- Добавлена ссылка на услугу сопровождения магазинов
- XT: 2х сторонняя синхронизация Заказов - Обновление позиций заказа из МойСклад https://github.com/wpcraft-ru/wooms/issues/338
- XT: устранен ряд проблем с состоянием гонок при 2х стороннем обмене данными

= 7.14 =
- Навигация в настройках https://github.com/wpcraft-ru/wooms/issues/360
- XT: Добавлена опция обновления клиента в МойСклад по Заказам https://github.com/wpcraft-ru/wooms/issues/361

= 7.13 =
* Исправлено. Ошибка при загрузке картинок https://github.com/wpcraft-ru/wooms/issues/348
* Улучшение. Документация и инструкции по плагину https://github.com/wpcraft-ru/wooms/issues/325
* Тест плагинов с новой версией WooCommerce https://github.com/wpcraft-ru/wooms/issues/351
* [XT] Пропал метод доставки в комментах к заказу https://github.com/wpcraft-ru/wooms/issues/357 

= 7.12 =
* [XT] Рефакторинг кода по отправке заказов https://github.com/wpcraft-ru/wooms/issues/342
* [XT] Исправление проблемы с новым механизмом обновления заказов в 2 стороны https://github.com/wpcraft-ru/wooms/issues/344

= 7.11 =
* [XT] Фикс проблемы поиска по номерам заказа https://github.com/wpcraft-ru/wooms/issues/331
* [XT] Исправление проблемы с новым механизмом обновления заказов в 2 стороны https://github.com/wpcraft-ru/wooms/issues/344

= 7.10 =
* [XT] Проработка решения для множества складов https://github.com/wpcraft-ru/wooms/issues/327
* [XT] Синхронное присвоение номера заказа в магазине https://github.com/wpcraft-ru/wooms/issues/330
* [XT] Исправлено. Сбрасывается заказ в "Мой склад" https://github.com/wpcraft-ru/wooms/issues/333
* [XT] Ошибка обновления кастомных статусов https://github.com/wpcraft-ru/wooms/issues/332
* [XT] Улучшили поиск контрагента по телефону https://github.com/wpcraft-ru/wooms/issues/326
* [XT] Связь позиций заказа и wooms_id https://github.com/wpcraft-ru/wooms/issues/335
* [XT] Исправление диагностики по веб хукам https://github.com/wpcraft-ru/wooms/issues/321

= 7.9 =
* Добавить опцию для ускорения синхронизации https://github.com/wpcraft-ru/wooms/issues/295
* XT: Исправление: Заказы. Нумерация с сайта перебивает нумерацию на складе https://github.com/wpcraft-ru/wooms/issues/319
* XT: Поиск дубля контрагента по телефону или user_id https://github.com/wpcraft-ru/wooms/issues/146
* ЛК: Скида 50% автоматически назначается Клиентам которе покупают проделение подписки по плагину https://github.com/wpcraft-ru/wooms/issues/318
* ЛК: Исправлена ошибка которая выдавала Клиентам доступ к новым версиям более чем на 1 год https://github.com/wpcraft-ru/wooms/issues/274

= 7.8 =
* Логгер - доп данные в JSON формате https://github.com/wpcraft-ru/wooms/issues/317
* Использование кода в МойСклад как артикула в WooCommerce (код, code) https://github.com/wpcraft-ru/wooms/issues/98
* XT: Учет НДС в Заказе https://github.com/wpcraft-ru/wooms/issues/173
* XT: Работа с валютой в WooCommerce и МойСклад (USD, EUR) https://github.com/wpcraft-ru/wooms/issues/189
* XT: Конвертер валют если цена указана в евро, долларах и рублях https://github.com/wpcraft-ru/wooms/issues/277
* Рефакторинг, мелкие улучшения и исправления 

= 7.7 =
* Добавить поддержку услуг с учетом REST API 1.2 https://github.com/wpcraft-ru/wooms/issues/314
* Выбор всего дерева категорий у продукта https://github.com/wpcraft-ru/wooms/issues/282
* XT Fix в версии 7.5 не работает синхранизация сетов https://github.com/wpcraft-ru/wooms/issues/313
* XT Скрытие товаров в черновики если нет остатков - добавлен хук https://github.com/wpcraft-ru/wooms/issues/287
* XT Заказы - опция передачи вручную, если автомат отключен https://github.com/wpcraft-ru/wooms/issues/316
* Рефакторинг, мелкие улучшения и исправления 

= 7.6 =
* Добавлена опция указания всего деревая категорий по продукту https://github.com/wpcraft-ru/wooms/issues/282
* Исправлена проблема при которой товары иногда могли скрываться без причины https://github.com/wpcraft-ru/wooms/issues/305
* Данные для отладки теперь сохраняются только если включена опция с журналом https://github.com/wpcraft-ru/wooms/issues/300
* Рефакторинг, мелкие улучшения и исправления 

= 7.5 =
* Анимация синхронизации https://github.com/wpcraft-ru/wooms/issues/306
* Мелкие улучшения и исправления 
* XT Улучшен лог данных. Товары в наличии пропадают в каталоге https://github.com/wpcraft-ru/wooms/issues/302
* XT Исправлено - Заказы передаются с отключенной опцией https://github.com/wpcraft-ru/wooms/issues/309
* XT Рефакторинг опции выбора склада https://github.com/wpcraft-ru/wooms/issues/308

= 7.4 =
* XT: Исправили проблему с сохранением цен распродажи
* Рефакторинг кода и мелкие улучшения

= 7.3 =
* Fix: Проблема с работой базового плагина в отрыве от расширения https://github.com/wpcraft-ru/wooms/issues/298
* Fix: Не передаются доп атрибуты типа text https://github.com/wpcraft-ru/wooms/issues/299

= 7.2 =
* Внимание! Большая часть обработчиков переведена на версию 1.2 REST API MoySklad и обновляться нужно осторожно и только обе версии сразу иначе могут быть проблемы https://github.com/wpcraft-ru/wooms/issues/296
* Изображения продукта для вариаций https://github.com/wpcraft-ru/wooms/issues/192
* Выбор нескольких групп для синхронизации https://github.com/wpcraft-ru/wooms/issues/297
* Кастомные статусы Заказов плохо передаются https://github.com/wpcraft-ru/wooms/issues/292
* Добавлена механика проверки данных по заказам отправленных в МойСклад https://github.com/wpcraft-ru/wooms/issues/290
* Разницы во времени создания заказа при синхронизации магазина и моего склада https://github.com/wpcraft-ru/wooms/issues/285
* Опция "Отправлять выбранный склад в Заказе" - улучшить инструкцию https://github.com/wpcraft-ru/wooms/issues/284
* Синхронизация заказов при каждом сохранении без опции https://github.com/wpcraft-ru/wooms/issues/289

= 7.1 =
* Импорт услуг - первая версия https://github.com/wpcraft-ru/wooms/issues/60
* Доступ к принудительной синхронизации для менеджеров https://github.com/wpcraft-ru/wooms/issues/280
* Если ошибка передачи Заказа - сброс очереди https://github.com/wpcraft-ru/wooms/issues/191
* Мелкие улучшения и исправления 

= 7.0 =
* глобальный рефакторинг, много улучшений
* обновляться стоит осторожно и сразу обе версии плагина (базовую и XT)
* дубль информации во вкладе Здоровье Сайта > Информация - чтобы пользователь мог скопировать 1 кнопкой информацию и отправить в поддержку https://github.com/wpcraft-ru/wooms/issues/254
* в здоровье сайта проверка на наличие платного тарифа МойСклад https://github.com/wpcraft-ru/wooms/issues/252
* улучшения механизмов диагностики проблем https://github.com/wpcraft-ru/wooms/issues/264
* диагностика проблем - добавлена ссылка https://github.com/wpcraft-ru/wooms/issues/260
* рефакторинг механизма скрытия продутов - выше надежность, меньше ошибок 
* оптимизация главного обработчика продуктов 
* оптимизация обработчика картинок по продуктам
* XT: синк комплектов (сгруппированных продуктов) - рефакторинг, исправление ошибок https://github.com/wpcraft-ru/wooms/issues/256

= 6.3 =
* XT исправление проблемы с множеством вебхуков и статусами Заказов https://github.com/wpcraft-ru/wooms/issues/246
* обновлены данные в readme.txt 
* обновление скриншотов

= 6.2 =
* улучшена работа плановых заданий
* тест WooCommerce 4.0 https://github.com/wpcraft-ru/wooms/issues/242
* XT исправлена ошибка по неправильной стоимости доставки https://github.com/wpcraft-ru/wooms/issues/244

= 6.1 = 
* исправлена ошибка по дублированию картинок https://github.com/wpcraft-ru/wooms/issues/221
* добавлен вывод ошибок в новой странице Инструменты->Здоровье сайта ( проверка и вывод всех возможных ошибок )
* перенесено большинство крон задач на Action Sheduler
* в качестве эксперимента реализована поддержка Action Sheduler в части синка галлереи (сильно упрощает понимние истории синхронизации и диагностику ошибок) https://github.com/wpcraft-ru/wooms/issues/212
* добавлен вывод ошибок 'не правильный пароль' в раздел Здоровье Cайта https://github.com/wpcraft-ru/wooms/issues/216
* добавлен вывод ошибок при разных версиях базого и XT в раздел Здоровье Cайта https://github.com/wpcraft-ru/wooms/issues/216

= 6.0 =
* добавлена поддержка галлереи изображений продукта https://github.com/wpcraft-ru/wooms/issues/27
* XT улучшена работа обновления статусов из Сайта на Склад - удалено накопление очереди без активной опции 
* XT улучшен лог передачи данных по юр лицам 

= < 6.0 =
* More https://github.com/wpcraft-ru/wooms/releases