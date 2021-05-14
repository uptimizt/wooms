<?php

/**
 * General functions
 */

/**
 * Helper for request and responses data to moysklad.ru
 *
 * @param string $url
 * @param array $data
 * @param string $type
 *
 * @return array
 */
function wooms_request($url = '', $data = array(), $type = 'GET')
{
  if (empty($url)) {
    return false;
  }

  $url = wooms_fix_url($url);

  if (isset($data) && !empty($data) && 'GET' == $type) {
    $type = 'POST';
  }
  if ('GET' == $type) {
    $data = null;
  } else {
    $data = json_encode($data);
  }

  $args = array(
    'method'      => $type,
    'timeout'     => 45,
    'redirection' => 5,
    'headers'     => array(
      "Content-Type"  => 'application/json;charset=utf-8',
      'Authorization' => 'Basic ' .
        base64_encode(get_option('woomss_login') . ':' . get_option('woomss_pass')),
    ),
    'body'        => $data,
  );

  $request = wp_remote_request($url, $args);
  if (is_wp_error($request)) {
    do_action(
      'wooms_logger_error',
      $type = 'WooMS-Request',
      $title = 'Ошибка REST API WP Error',
      $desc = $request->get_error_message()
    );

    return false;
  }

  if (empty($request['body'])) {
    do_action(
      'wooms_logger_error',
      $type = 'WooMS-Request',
      $title = 'REST API вернулся без требуемых данных'
    );

    return false;
  }

  $response = json_decode($request['body'], true);

  if (!empty($response["errors"]) and is_array($response["errors"])) {
    foreach ($response["errors"] as $error) {
      do_action(
        'wooms_logger_error',
        $type = 'WooMS-Request',
        $title = $url,
        $response
      );
    }
  }

  return $response;
}


function wooms_get_wooms_id_from_href($href = '')
{
  if (empty($href)) {
    return false;
  }

  $url_parts = explode('/', $href);

  return array_pop($url_parts);
}

/**
 * Get product id by UUID from metafield
 * or false
 */
function wooms_get_product_id_by_uuid($uuid)
{
  $posts = get_posts([
    'post_type' => ['product', 'product_variation'],
    'meta_key' => 'wooms_id',
    'meta_value' => $uuid
  ]);

  if (empty($posts[0]->ID)) {
    return false;
  } else {
    return $posts[0]->ID;
  }
}

/**
 * fix bug with url
 *
 * @link https://github.com/wpcraft-ru/wooms/issues/177 
 */
function wooms_fix_url($url = '')
{
  $url = str_replace('product_id', 'product.id', $url);
  $url = str_replace('store_id', 'store.id', $url);
  $url = str_replace('consignment_id', 'consignment.id', $url);
  $url = str_replace('variant_id', 'variant.id', $url);
  $url = str_replace('productFolder_id', 'productFolder.id', $url);
  return $url;
}

/**
 * Check if WooCommerce is activated
 */
if (!function_exists('is_woocommerce_activated')) {
  function is_woocommerce_activated()
  {
    if (class_exists('woocommerce')) {
      return true;
    } else {
      return false;
    }
  }
}

/**
 * Checking if wooms meta is unique and deleting if it is duplicated in save_post action
 * 
 * @param int     $post_ID Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an existing post being updated.
 * 
 * @return void
 * 
 * @link https://github.com/wpcraft-ru/wooms/issues/409
 */
function wooms_id_check_if_unique($post_ID, $post = '', $update = '') {

    if (!$post_ID || !is_numeric($post_ID)) {
        return;
    }

    $uuid = get_post_meta($post_ID, 'wooms_id', true);

    if (!$uuid) {
        return;
    }

    $basic_args = array(
        'post_type'              => array('product', 'product_variation'),
        'numberposts'            => -1,
        'post_status'            => 'any',
        'orderby'                => 'ID',
        'order'                  => 'ASC',
        'update_post_term_cache' => false,
        'update_post_meta_cache' => false,
        'cache_results'          => false,
    );

    $products_args = array(
        'meta_key'   => 'wooms_id',
        'meta_value' => $uuid
    );

    $args = array_merge($basic_args, $products_args);

    $products = get_posts($args);

    $ids = [];

    if (count($products) > 1) {

        foreach ($products as $key => $product) {

            if ($key > 0) {

                $ids[] = $product->ID;
            }
        }
    }

    if (empty($ids)) {
        return;
    }
    
    /* Selecting all child variations */
    $variations_args = array(
        'post_parent__in' => $ids,
    );

    $args = array_merge($basic_args, $variations_args);

    $variations = get_posts($args);

    foreach ($variations as $variation) {

        $ids[] = $variation->ID;
    }

    foreach ($ids as $id) {

        $meta_values = get_post_meta( $id );

        foreach ($meta_values as $key => $values) {
            if (preg_match('/^wooms_/',  $key)) {
                delete_post_meta($id, $key);
            }
        }
    }

    do_action(
        'wooms_logger',
        $type = 'WooMS-Request',
        $title =  sprintf('Дубли meta-полей wooms для товаров и вариаций (%s) удалены', implode(', ', $ids))
    );
}
