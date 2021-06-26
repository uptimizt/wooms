<?php

namespace WooMS;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * Product Import Walker
 */
class ProductsWalker
{

  public static $state_transient_key = 'wooms_products_walker_state';

  public static $walker_hook_name = 'wooms_products_walker_batch';


  /**
   * The Init
   */
  public static function init()
  {

    add_action('init', function () {
      if (!isset($_GET['dd'])) {
        return;
      }

      // dd(get_transient('wooms_end_timestamp'));
      // self::set_state('timestamp', 0);

      // self::batch_handler();

      // do_action('wooms_product_data_item', $dddd);

      dd(0);
    });


    add_action('init', [__CLASS__, 'add_schedule_hook']);

    //Main Walker
    add_action('wooms_products_walker_batch', [__CLASS__, 'batch_handler']);

    //Product data
    add_action('wooms_product_data_item', [__CLASS__, 'load_product']);
    add_filter('wooms_product_save', [__CLASS__, 'update_product'], 9, 3);

    //UI and actions manually
    add_action('woomss_tool_actions_btns', [__CLASS__, 'render_ui']);
    add_action('woomss_tool_actions_wooms_products_start_import', [__CLASS__, 'start_manually']);
    add_action('woomss_tool_actions_wooms_products_stop_import', [__CLASS__, 'stop_manually']);
    add_action('wooms_products_display_state', [__CLASS__, 'display_state']);

    //Other
    add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes_post_type']);

    add_action('admin_init', array(__CLASS__, 'add_settings'));
  }


  /**
   * Settings
   */

  public static function add_settings()
  {

    self::add_setting_wooms_batch_size();
    self::add_setting_short_description();

	do_action('wooms_add_settings');
  }


  public static function add_setting_wooms_batch_size()
  {
    $option_name = 'wooms_batch_size';
    register_setting('mss-settings', $option_name);
    add_settings_field(
      $id = $option_name,
      $title = 'Количество элементов в пачке',
      $callback = function ($args) {

        printf(
          '<input type="number" name="%s" value="%s"  />',
          $args['key'],
          $args['value']
        );
        printf(
          '<p>%s</p>',
          'Опция позволяет ускорять обмен данными, но может приводить к перегрузке сервера и связанным с этим ошибкам'
        );
        printf(
          '<p>%s</p>',
          'Подробнее: <a href="https://github.com/wpcraft-ru/wooms/issues/295">https://github.com/wpcraft-ru/wooms/issues/295</a>'
        );
      },
      $page = 'mss-settings',
      $section = 'woomss_section_other',
      $args = [
        'key' => $option_name,
        'value' => get_option($option_name, 20),
      ]
    );
  }

  public static function add_setting_short_description()
  {
    $option_name = 'wooms_short_description';
    register_setting('mss-settings', $option_name);
    add_settings_field(
      $id = $option_name,
      $title = 'Использовать краткое описание продуктов вместо полного',
      $callback = function ($args) {

        printf(
          '<input type="checkbox" name="%s" value="1" %s />',
          $args['key'],
          checked(1, $args['value'], false)
        );

        printf(
          '<p>%s</p>',
          'Подробнее: <a href="https://github.com/wpcraft-ru/wooms/issues/347">https://github.com/wpcraft-ru/wooms/issues/347</a>'
        );
      },
      $page = 'mss-settings',
      $section = 'woomss_section_other',
      $args = [
        'key' => $option_name,
        'value' => get_option($option_name, 20),
      ]
    );
  }


  /**
   * Load data and set product type simple
   */
  public static function load_product($value)
  {
    if (!empty($value['archived'])) {
      return;
    }

    /**
     * Определение способов связи
     */
    $product_id = 0;
    if (empty($value['article'])) {
      if (!$product_id = apply_filters('wooms_get_product_id', $product_id, $value)) {
        if (empty(get_option('wooms_use_uuid'))) {
          return;
        }
      }
    }

    //попытка получить id по артикулу
    if (!empty($value['article'])) {
      $product_id = wc_get_product_id_by_sku($value['article']);
    }

    //попытка получить id по uuid
    if (empty($product_id)) {
      $product_id = self::get_product_id_by_uuid($value['id']);
    }

    //создаем продукт, если не нашли
    if (empty(intval($product_id))) {
      $product_id = self::add_product($value);
    }

    if (empty(intval($product_id))) {
      do_action(
        'wooms_logger_error',
        __CLASS__,
        'Ошибка определения и добавления ИД продукта',
        $value
      );
      return;
    }

    $product = wc_get_product($product_id);

    /**
     * rename vars
     */
    $data_api = $value;

    /**
     * Хук позволяет работать с методами WC_Product
     * Сохраняет в БД все изменения за 1 раз
     * Снижает нагрузку на БД
     */
    $product = apply_filters('wooms_product_save', $product, $data_api, $product_id);

    //save data of source
    if (apply_filters('wooms_logger_enable', false)) {
      $product->update_meta_data('wooms_data_api', json_encode($data_api, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } else {
      $product->delete_meta_data('wooms_data_api');
    }

    $product_id = $product->save();

    do_action(
      'wooms_logger',
      __CLASS__,
      sprintf('Продукт: %s (%s) сохранен', $product->get_title(), $product_id)
    );
  }


  /**
   * Update product from source data
   */
  public static function update_product($product, $data_api, $data = 'deprecated')
  {

    $data_of_source = $data_api;
    $product_id = $product->get_id();

    //Set session id for product
    if ($session_id = get_option('wooms_session_id')) {
      $product->update_meta_data('wooms_session_id', $session_id);
    }

    $product->update_meta_data('wooms_updated_timestamp', date("Y-m-d H:i:s"));

    $product->update_meta_data('wooms_id', $data_api['id']);

    $product->update_meta_data('wooms_updated_from_api', $data_api['updated']);

    //update title
    if (isset($data_api['name']) and $data_api['name'] != $product->get_title()) {
      if (!empty(get_option('wooms_replace_title'))) {
        $product->set_name($data_api['name']);
      }
    }

    $product_description = isset($data_of_source['description']) ? $data_of_source['description'] : '';
    //update description
    if (apply_filters('wooms_added_description', true, $product_description)) {

      if ($product_description && !empty(get_option('wooms_replace_description'))) {

        if (get_option('wooms_short_description')) {
          $product->set_short_description($product_description);
        } else {
          $product->set_description($product_description);
        }
      } else {

        if (empty($product->get_description())) {

          if (get_option('wooms_short_description')) {
            $product->set_short_description($product_description);
          } else {
            $product->set_description($product_description);
          }
        }
      }
    }

    //Price Retail 'salePrices'
    if (isset($data_of_source['salePrices'][0]['value'])) {

      $price_source = floatval($data_of_source['salePrices'][0]['value']);

      $price = floatval($price_source) / 100;

      $product->set_price($price);
      $product->set_regular_price($price);
    }

    // issue https://github.com/wpcraft-ru/wooms/issues/302
    $product->set_catalog_visibility('visible');

    if ($reset = apply_filters('wooms_reset_state_products', true)) {
      $product->set_stock_status('instock');
      $product->set_manage_stock('no');
      $product->set_status('publish');
    }

    return $product;
  }


  /**
   * Walker for data from MoySklad
   */
  public static function batch_handler($state = [])
  {
    $state = self::get_state();

    //state reset for new session
    if (empty($state['timestamp'])) {

      self::walker_started();

      $batch_size = get_option('wooms_batch_size', 20);

      $query_arg_default = [
        'offset' => 0,
        'limit'  => apply_filters('wooms_iteration_size', $batch_size),
      ];

      self::set_state('query_arg', $query_arg_default);
    }

    $query_arg = self::get_state('query_arg');

    /**
     * issue https://github.com/wpcraft-ru/wooms/issues/296
     */
    $url = 'https://online.moysklad.ru/api/remap/1.2/entity/product';

    $url = add_query_arg($query_arg, $url);

    $url = apply_filters('wooms_url_get_products', $url);

    $filters = [
      // 'pathName~=Диваны',
    ];

    $filters = apply_filters('wooms_url_get_products_filters', $filters);

    if (!empty($filters)) {
      $filters = implode(';', $filters);
      $url = add_query_arg('filter', $filters, $url);
    }

    try {

      $data = wooms_request($url);
      // dd($data);

      do_action('wooms_logger', __CLASS__, sprintf('Отправлен запрос %s', $url));

      //If no rows, that send 'end' and stop walker
      if (isset($data['rows']) && empty($data['rows'])) {
        self::walker_finish();
        return;
      }

      do_action('wooms_walker_start_iteration', $data);

      foreach ($data['rows'] as $key => $value) {

        if (apply_filters('wooms_skip_product_import', false, $value)) {
          continue;
        }

        /**
         * в выдаче могут быть не только товары, но и вариации и мб что-то еще
         * птм нужна проверка что это точно продукт
         */
        if ('variant' == $value["meta"]["type"]) {
          continue;
        }

        do_action('wooms_product_data_item', $value);

        /**
         * deprecated - for remove
         */
        do_action('wooms_product_import_row', $value, $key, $data);
      }

      //update count
      self::set_state('count', self::get_state('count') + count($data['rows']));

      //update offset 
      $query_arg['offset'] = $query_arg['offset'] + count($data['rows']);
      self::set_state('query_arg', $query_arg);



      self::add_schedule_hook(true);

      do_action('wooms_products_batch_end');
    } catch (\Exception $e) {

      /**
       * need to protect the site
       * from incorrectly hidden products
       */
      delete_option('wooms_session_id');

      do_action('wooms_logger_error', __CLASS__, 'Главный обработчик завершился с ошибкой' . $e->getMessage());
    }
  }

  /**
   * Add metaboxes
   */
  public static function add_meta_boxes_post_type()
  {
    add_meta_box('wooms_product', 'МойСклад', [__CLASS__, 'display_metabox_for_product'], 'product', 'side', 'low');
  }

  /**
   * Meta box in product
   */
  public static function display_metabox_for_product()
  {
    $post = get_post();
    $box_data = '';
    $data_id   = get_post_meta($post->ID, 'wooms_id', true);
    $data_meta = get_post_meta($post->ID, 'wooms_meta', true);
    $data_updated = get_post_meta($post->ID, 'wooms_updated', true);
    if ($data_id) {
      printf('<div>ID товара в МойСклад: <div><strong>%s</strong></div></div>', $data_id);
    } else {
      echo '<p>Товар еще не синхронизирован с МойСклад.</p> <p>Ссылка на товар отсутствует</p>';
    }

    if ($data_meta) {
      printf('<p><a href="%s" target="_blank">Посмотреть товар в МойСклад</a></p>', $data_meta['uuidHref']);
    }

    if ($data_updated) {
      printf('<div>Дата последнего обновления товара в МойСклад: <strong>%s</strong></div>', $data_updated);
    }

    do_action('wooms_display_product_metabox', $post->ID);
  }

  /**
   * get_product_id_by_uuid
   */
  public static function get_product_id_by_uuid($uuid)
  {

    $posts = get_posts('post_type=product&post_status=any&meta_key=wooms_id&meta_value=' . $uuid);

    if (empty($posts[0]->ID)) {
      return false;
    } else {
      return $posts[0]->ID;
    }
  }

  /**
   * Add product from source data
   *
   * @param $data_source
   *
   */
  public static function add_product($data_source)
  {

    if (!apply_filters('wooms_add_product', true, $data_source)) {
      return false;
    }

    $product = new \WC_Product_Simple();

    $product->set_name(wp_filter_post_kses($data_source['name']));

    $product_id = $product->save();

    if (empty($product_id)) {
      return false;
    }

    $product->update_meta_data('wooms_id', $data_source['id']);

    $product->update_meta_data('wooms_meta', $data_source['meta']);

    $product->update_meta_data('wooms_updated', $data_source['updated']);

    if (isset($data_source['article'])) {
      $product->set_sku($data_source['article']);
    }

    $product_id = $product->save();

    return $product_id;
  }

  /**
   * hook restart
   */
  public static function add_schedule_hook($force = false)
  {
    if (self::is_wait()) {
      return;
    }

    if (as_next_scheduled_action(self::$walker_hook_name) && !$force) {
      return;
    }

    if ($force) {
      self::set_state('force', 1);
    }

    as_schedule_single_action(time() + 11, self::$walker_hook_name, self::get_state(), 'WooMS');
  }

  /**
   * Проверяем стоит ли обработчик на паузе?
   */
  public static function is_wait()
  {
    //reset state if lock deleted and isset state
    if (empty(get_transient('wooms_end_timestamp')) and !empty(self::get_state('end_timestamp'))) {
      delete_transient(self::$state_transient_key);
    }

    if (self::get_state('end_timestamp')) {
      return true;
    }


    return false;
  }


  /**
   * walker_started
   */
  public static function walker_started()
  {
    $timestamp = date("YmdHis");
    update_option('wooms_session_id', $timestamp, 'no'); //set id session sync

    self::set_state('timestamp', date("YmdHis"));
    self::set_state('end_timestamp', 0);
    self::set_state('count', 0);

    delete_transient('wooms_end_timestamp');

    do_action('wooms_main_walker_started');

    do_action('wooms_logger', __CLASS__, 'Старт основного волкера: ' . $timestamp);
  }

  /**
   * Finish walker
   */
  public static function walker_finish()
  {

    self::set_state('end_timestamp', date("Y-m-d H:i:s"));

    //Отключаем обработчик или ставим на паузу
    if (empty(get_option('woomss_walker_cron_enabled'))) {
      $timer = 0;
    } else {
      $timer = 60 * 60 * intval(get_option('woomss_walker_cron_timer', 24));
    }
    set_transient('wooms_end_timestamp', date("Y-m-d H:i:s"), $timer);

    do_action('wooms_main_walker_finish');

    do_action('wooms_recount_terms');

    as_unschedule_all_actions(self::$walker_hook_name);

    do_action(
      'wooms_logger',
      __CLASS__,
      sprintf('Основной обработчик продуктов завершил работу: %s', date("Y-m-d H:i:s"))
    );

    return true;
  }

  /**
   * Start manually actions
   */
  public static function start_manually()
  {
    delete_transient(self::$state_transient_key);

    delete_transient('wooms_end_timestamp');

    do_action('wooms_products_sync_manual_start');

    self::batch_handler();

    wp_redirect(admin_url('admin.php?page=moysklad'));
  }

  /**
   * Stop manually actions
   */
  public static function stop_manually()
  {
    as_unschedule_all_actions(self::$walker_hook_name);
    self::set_state('stop_manual', 1);
    self::set_state('timestamp', 0);
    self::walker_finish();

    /**
     * issue https://github.com/wpcraft-ru/wooms/issues/305
     */
    delete_option('wooms_session_id');

    wp_redirect(admin_url('admin.php?page=moysklad'));
    exit;
  }


  /**
   * display_state
   */
  public static function display_state()
  {
    $strings = [];

    if (as_next_scheduled_action(self::$walker_hook_name)) {
      $strings[] = do_shortcode('[wooms_loader_icon]');
      $strings[] = sprintf('<strong>Статус:</strong> %s', 'Выполняется очередями в фоне');
    }

    if ($end_timestamp = self::get_state('end_timestamp')) {
      $strings[] = sprintf('Последняя успешная синхронизация (отметка времени UTC): %s', $end_timestamp);
    }

    $strings[] = sprintf('Количество обработанных записей: %s', empty(self::get_state('count')) ? 0 : self::get_state('count'));

    if ($session = get_option('wooms_session_id')) {
      $strings[] = sprintf('Сессия (номер/дата): %s', $session);
    } else {
      $strings[] = sprintf('Сессия (номер/дата): %s', 'отсутствует');
    }

    $strings[] = sprintf('Очередь задач: <a href="%s">открыть</a>', admin_url('admin.php?page=wc-status&tab=action-scheduler&s=wooms_products_walker_batch&orderby=schedule&order=desc'));

    if (defined('WC_LOG_HANDLER') && 'WC_Log_Handler_DB' == WC_LOG_HANDLER) {
      $strings[] = sprintf('Журнал обработки: <a href="%s">открыть</a>', admin_url('admin.php?page=wc-status&tab=logs&source=WooMS-ProductsWalker'));
    } else {
      $strings[] = sprintf('Журнал обработки: <a href="%s">открыть</a>', admin_url('admin.php?page=wc-status&tab=logs'));
    }

    $strings = apply_filters('wooms_main_walker_info_string', $strings);

?>
    <div class="wrap">
      <div id="message" class="notice notice-warning">
        <?php

        foreach ($strings as $string) {
          printf('<p>%s</p>', $string);
        }

        do_action('wooms_products_state_before');

        ?>
      </div>
    </div>
<?php
  }


  /**
   * User interface for manually actions
   */
  public static function render_ui()
  {
    printf('<h2>%s</h2>', 'Продукты (Товары)');

    if (as_next_scheduled_action(self::$walker_hook_name)) {
      printf('<a href="%s" class="button button-secondary">Остановить синхронизацию</a>', add_query_arg('a', 'wooms_products_stop_import', admin_url('admin.php?page=moysklad')));
    } else {

      printf(
        "<p>%s</p>",
        'Нажмите на кнопку ниже, чтобы запустить синхронизацию данных о продуктах вручную'
      );

      printf(
        '<a href="%s" class="button button-primary">Запустить синхронизацию продуктов вручную</a>',
        add_query_arg('a', 'wooms_products_start_import', admin_url('admin.php?page=moysklad'))
      );
    }

    do_action('wooms_products_display_state');
  }


  /**
   * get state data
   */
  public static function get_state($key = '')
  {
    if (!$state = get_transient(self::$state_transient_key)) {
      $state = [];
      set_transient(self::$state_transient_key, $state);
    }

    if (empty($key)) {
      return $state;
    }

    if (empty($state[$key])) {
      return null;
    }

    return $state[$key];
  }


  /**
   * set state data
   */
  public static function set_state($key, $value)
  {

    if (!$state = get_transient(self::$state_transient_key)) {
      $state = [];
    }

    if (is_array($state)) {
      $state[$key] = $value;
    } else {
      $state = [];
      $state[$key] = $value;
    }

    set_transient(self::$state_transient_key, $state);
  }
}
