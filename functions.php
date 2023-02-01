<?php
defined( 'ABSPATH' ) || exit;

/**
 * Enqueue child scripts
 */
if ( ! function_exists( 'minimog_child_enqueue_scripts' ) ) {
	function minimog_child_enqueue_scripts() {
		wp_enqueue_style( 'minimog-child-style', get_stylesheet_directory_uri() . '/style.css' );
	}
}
add_action( 'wp_enqueue_scripts', 'minimog_child_enqueue_scripts', 15 );

//** @snippet       Hide Prices from search engines **//

 add_filter( 'woocommerce_structured_data_product_offer', '__return_empty_array' );



//** Product Custom Fields for Korean name and Wholesale Price **//
// Display Fields
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');
// Save Fields
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');
function woocommerce_product_custom_fields()
{
    global $woocommerce, $post;
    echo '<div class="product_custom_field">';
    // Custom Product Text Field
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_product_korean_name',
            'placeholder' => '',
            'label' => __('Korean Name', 'woocommerce'),
            'desc_tip' => 'true'
        )
    );
    //Custom Product Number Field
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_product_wholesale_price',
            'placeholder' => '',
            'label' => __('Wholesale Price', 'woocommerce'),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => 'any',
                'min' => '0'
            )
        )
    );
    echo '</div>';
}

function woocommerce_product_custom_fields_save($post_id)
{
    // Custom Product Text Field
    $woocommerce_custom_product_text_field = $_POST['_custom_product_korean_name'];
    if (!empty($woocommerce_custom_product_text_field))
        update_post_meta($post_id, '_custom_product_korean_name', esc_attr($woocommerce_custom_product_text_field));
// Custom Product Number Field
    $woocommerce_custom_product_number_field = $_POST['_custom_product_wholesale_price'];
    if (!empty($woocommerce_custom_product_number_field))
        update_post_meta($post_id, '_custom_product_wholesale_price', esc_attr($woocommerce_custom_product_number_field));
}

// Display the product thumbnail in order view pages
add_filter( 'woocommerce_order_item_name', 'display_product_image_in_order_item', 20, 3 );
function display_product_image_in_order_item( $item_name, $item, $is_visible ) {
    // Targeting view order pages only
    if( is_wc_endpoint_url( 'view-order' ) ) {
			if($item->get_product()){
				$product   = $item->get_product(); // Get the WC_Product object (from order item)
        $thumbnail = $product->get_image(array( 80, 80)); // Get the product thumbnail (from product object)
        if( $product->get_image_id() > 0 )
            $item_name = '<div class="item-thumbnail">' . $thumbnail . '</div>' . $item_name;
			}else{
				$item_name = '<div class="item-thumbnail"><img width="80" height="80" src="' . wc_placeholder_img_src() . '" class="attachment-80x80 size-80x80" alt="" loading="lazy"></div>' . $item_name;
			}
    }
    return $item_name;
}

/**Stop to regenerate Thumbnail**/
add_filter( 'woocommerce_background_image_regeneration', '__return_false' );

//Record user's last login to custom meta
add_action( 'wp_login', 'smartwp_capture_login_time', 10, 2 );

function smartwp_capture_login_time( $user_login, $user ) {
  update_user_meta( $user->ID, 'last_login', time() );
}

//Register new custom column with last login time
add_filter( 'manage_users_columns', 'smartwp_user_last_login_column' );
add_filter( 'manage_users_custom_column', 'smartwp_last_login_column', 10, 3 );

function smartwp_user_last_login_column( $columns ) {
	$columns['last_login'] = 'Last Login';
	return $columns;
}

function smartwp_last_login_column( $output, $column_id, $user_id ){
	if( $column_id == 'last_login' ) {
    $last_login = get_user_meta( $user_id, 'last_login', true );
    $date_format = 'M j, Y';
    $hover_date_format = 'F j, Y, g:i a';
    
		$output = $last_login ? '<div title="Last login: '.date( $hover_date_format, $last_login ).'">'.human_time_diff( $last_login ).'</div>' : 'No record';
	}
  
	return $output;
}

//Allow the last login columns to be sortable
add_filter( 'manage_users_sortable_columns', 'smartwp_sortable_last_login_column' );
add_action( 'pre_get_users', 'smartwp_sort_last_login_column' );

function smartwp_sortable_last_login_column( $columns ) {
	return wp_parse_args( array(
	 	'last_login' => 'last_login'
	), $columns );
 
}

function smartwp_sort_last_login_column( $query ) {
	if( !is_admin() ) {
		return $query;
	}
 
	$screen = get_current_screen();
 
	if( isset( $screen->base ) && $screen->base !== 'users' ) {
		return $query;
	}
 
	if( isset( $_GET[ 'orderby' ] ) && $_GET[ 'orderby' ] == 'last_login' ) {
 
		$query->query_vars['meta_key'] = 'last_login';
		$query->query_vars['orderby'] = 'meta_value';
 
	}
 
  return $query;
}

//Add [lastlogin] shortcode
function smartwp_lastlogin_shortcode( $atts ) {
  $atts = shortcode_atts(
  array(
      'user_id' => false,
  ), $atts, 'lastlogin' );

  $last_login = get_the_author_meta('last_login', $atts['user_id']);
  if( empty($last_login) ){ return false; };
  $the_login_date = human_time_diff($last_login);
  return $the_login_date; 
}

add_shortcode( 'lastlogin', 'smartwp_lastlogin_shortcode' );


//remove sold item automatically
function ced_out_of_stock_products() {
    if ( WC()->cart->is_empty() ) {
        return;
    }
 
    $removed_products = [];
 
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $product_obj = $cart_item['data'];
 
        if ( ! $product_obj->is_in_stock() ) {
            WC()->cart->remove_cart_item( $cart_item_key );
            $removed_products[] = $product_obj;
        }
    }
 
    if (!empty($removed_products)) {
        wc_clear_notices(); 
 
        foreach ( $removed_products as $idx => $product_obj ) {
            $product_name = $product_obj->get_title();

            //your notice here
            $msg = sprintf( __( "The product '%s' was removed from your cart because it is out of stock.", 'woocommerce' ), $product_name);
 
            wc_add_notice( $msg, 'error' );
        }
    }
 
}
 
add_action('woocommerce_before_cart', 'ced_out_of_stock_products');

/** Change Action Scheduler default purge to 1 week **/
add_filter( 'action_scheduler_retention_period', 'wpb_action_scheduler_purge' );
function wpb_action_scheduler_purge() {
 return WEEK_IN_SECONDS;
}

/**
 * WooCommerce login not working on first try fix
 */
add_filter('nonce_user_logged_out', function($uid, $action) {
  if ($uid && $uid != 0 && $action && $action == 'woocommerce-login') {
     $uid = 0;
  }
   return $uid;
}, 100, 2);





// brand taxonomy에 hierarchical 옵션을 설정하는 코드
// 상위 브랜드를 설정할 수 있게됩니다
// 상위 브랜드 설정 코드 - 시작
add_filter('register_product_brand_taxonomy_args', 'apmmust_change_product_brand_taxonomy_args');
function apmmust_change_product_brand_taxonomy_args($args) {
    $args['hierarchical'] = true;
    return $args;
}
// 상위 브랜드 설정 코드 - 끝





// 사용자 리스트 페이지에 사용자의 국가를 보이도록 하고
// 국가별 필터/정렬 기능을 추가합니다
// 국가별 필터/정렬 기능 추가 코드 - 시작
function apmmust_get_user_country( $country_code ) {
    $countries = WC()->countries->get_countries();
    if ( ! isset( $countries[ $country_code ] ) ) {
        return '';
    }
    return $countries[ $country_code ];
}

function apmmust_get_all_countries() {
    global $wpdb;
    $users = $wpdb->get_col( "SELECT ID FROM $wpdb->users" );

    $countries = array();
    foreach( $users as $user_id ) {
        $country = get_user_meta( $user_id, 'billing_country', true );
        if(!empty($country) && !in_array($country, $countries)){
            $countries[] = $country;
        }
    }
    sort($countries);
    return $countries;
}

add_filter( 'manage_users_columns', 'apmmust_add_country_column' );
function apmmust_add_country_column( $columns ) {
    $columns['billing_country'] = 'Country';
    return $columns;
}

add_action( 'manage_users_custom_column', 'apmmust_show_country_data', 10, 3 );
function apmmust_show_country_data( $value, $column_name, $user_id ) {
    if ( $column_name === 'billing_country' ) {
        $country_code = get_user_meta( $user_id, 'billing_country', true );
        return apmmust_get_user_country($country_code);
    }
    return $value;
}

add_filter( 'manage_users_sortable_columns', 'apmmust_sortable_billing_country_column' );
function apmmust_sortable_billing_country_column( $columns ) {
	return wp_parse_args( array(
	 	'billing_country' => 'billing_country'
	), $columns );
}

add_action( 'pre_get_users', 'apmmust_sort_billing_country_column' );
function apmmust_sort_billing_country_column( $query ) {
    global $pagenow;
    if (!is_admin() || 'users.php' !== $pagenow) return $query;

    if ( ! $query->get( 'orderby' ) ) {
		return $query;
	}
    $orderby = $query->get( 'orderby' );
    if ($orderby !== 'billing_country') return $query;

    $query->set( 'meta_key', 'billing_country' );
	return $query;
}

add_action( 'restrict_manage_users', 'apmmust_add_filter_by_country_filter');
function apmmust_add_filter_by_country_filter() {
    $countries = apmmust_get_all_countries();
    $country_map = WC()->countries->get_countries();

    if ( isset( $_GET[ 'filter_by_country' ]) ) {
        $section = $_GET[ 'filter_by_country' ];
        $section = !empty( $section[ 0 ] ) ? $section[ 0 ] : $section[ 1 ];
    } else {
        $section = -1;
    }

    echo ' <select name="filter_by_country[]" style="float:none;"><option value="">'.esc_html__('Country Filter','apmmust') . '</option>';

    foreach ($countries as $country_code) {
        $country_name = $country_map[$country_code];
        $selected = $country_code === $section ? ' selected="selected"' : '';
        echo '<option value="' . $country_code . '"' . $selected . '>' . $country_name . '</option>';
    }

    echo '</select>';
    echo '<input type="submit" class="button" value="Filter">';

    remove_action('restrict_manage_users', 'apmmust_add_filter_by_country_filter');
}

add_filter( 'pre_get_users', 'apmmust_filter_users_by_filter_by_country' );
function apmmust_filter_users_by_filter_by_country( $query ) {
    global $pagenow;
    if (!is_admin() || 'users.php' !== $pagenow) return $query;

    $empty = 'yes';
    if (!empty($_GET[ 'filter_by_country' ])){
        foreach ($_GET[ 'filter_by_country' ] as $item){
            if (!empty($item)){
                $empty = 'no';
            }
        }
    }

    if ($empty === 'yes') return $query;

    $country_code = $_GET['filter_by_country'];
    $country_code = !empty( $country_code[ 0 ] ) ? $country_code[ 0 ] : $country_code[ 1 ];

    $meta_query = array(
        'relation' => 'AND',
        array(
            'key' => 'billing_country',
            'value' => $country_code,
            'compare' => '=='
        ),
    );
    $query->set( 'meta_query', $meta_query );
    return $query;
}
// 국가별 필터/정렬 기능 추가 코드 - 끝





// 배송비 계산 기능 추가
add_action('plugins_loaded', 'apmmust_add_shipping_fee_option_page');
function apmmust_add_shipping_fee_option_page() {
  do_action( 'qm/start', 'GOGOGO' );
  if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title'    => '배송비 관리',
        'menu_title'    => '배송비 관리',
        'menu_slug'     => 'global-shopping-fee-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
  }
}


add_action( 'wp_enqueue_scripts', 'apmmust_enqueue_scripts', 10 );
function apmmust_enqueue_scripts() {
  wp_enqueue_script( 'apmmust-main-js', get_stylesheet_directory_uri() . '/main.js', array('jquery'), '0.0.1', true );
  wp_localize_script( 'apmmust-main-js', 'apmmust_ajax_obj', array('ajaxurl' => admin_url('admin-ajax.php' )));
}

add_shortcode('shipping_calculator', 'apmmust_shipping_calculator');
function apmmust_shipping_calculator($attrs) {
  if( function_exists('acf_add_options_page') ) {
    // return get_template_part('shipping-calculator');
  }
  return 'ACF Pro 플러그인이 설치되어 있지 않습니다';
}

function apmmust_get_shipping_fields() {
  $page_slug = 'option';
  $repeater_field_name = 'my_global_shipping_fee_setting_repeater';
  $country_field_name = 'country';
  $shipping_type_field_name = 'shipping_type';
  $estimate_shpping_date = 'estimate_shipping_date';
  $shipping_fee_kg_field_name = 'shipping_fee_kg';

  $rows = [];
  if (have_rows($repeater_field_name, $page_slug)):
    while (have_rows($repeater_field_name, $page_slug)): the_row();
      $rows[] = array(
        'country' => get_sub_field($country_field_name),
        'shipping_type' => get_sub_field($shipping_type_field_name),
        'estimate_shipping_date' => get_sub_field($estimate_shpping_date),
        'shipping_fee_kg' => array_reduce(get_sub_field($shipping_fee_kg_field_name), function($acc, $cur) {
          if ($acc === null) $acc = [];
          return array_merge($acc, $cur);
        }),
      );
    endwhile;
  endif;

  return $rows;
}

function apmmust_get_shipping_box_dimensions() {
  $page_slug = 'option';
  $box_dimension_field_name = 'box_dimension';
  $box_dimension_width_field_name = 'width';
  $box_dimension_height_field_name = 'height';
  $box_dimension_depth_field_name = 'depth';

  $box_dimensions = [];
  if (have_rows($box_dimension_field_name, $page_slug)):
    while (have_rows($box_dimension_field_name, $page_slug)): the_row();
      $box_dimensions[] = array(
        'width' => get_sub_field($box_dimension_width_field_name),
        'height' => get_sub_field($box_dimension_height_field_name),
        'depth' => get_sub_field($box_dimension_depth_field_name),
      );
    endwhile;
  endif;

  return $box_dimensions;
}

function apmmust_get_weight_fomula() {
  $result = get_field('kg_fomula', 'option');
  return array(
    "ems" => intval($result['ems']),
    "ups" => intval($result['ups']),
  );
}

function apmmust_get_possible_shipping_type_of_country(&$rows) {
  $country_possible_shipping_type_map = [];
  foreach($rows as $row) {
    $country = $row['country'];
    if (!isset($country_possible_shipping_type_map[$country])) {
      $country_possible_shipping_type_map[$country] = [];
    }
    $country_possible_shipping_type_map[$country][] = $row['shipping_type'];
  }
  return $country_possible_shipping_type_map;
}

add_action( 'wp_ajax_apmmust_calculate_shipping_fee_action', 'apmmust_calculate_shipping_fee_action' );
add_action( 'wp_ajax_nopriv_apmmust_calculate_shipping_fee_action', 'apmmust_calculate_shipping_fee_action' );
function apmmust_calculate_shipping_fee_action() {
  if (!isset($_POST['country']) || !isset($_POST['shipping_type']) || !isset($_POST['weight']) || !isset($_POST['box_dimension'])) {
    wp_send_json_error(array(
      "code" => 401,
      "message" => '값이 설정되어 있지 않습니다'
    ), 401);
    return;
  }

  $country = $_POST['country'];
  $shipping_type = $_POST['shipping_type'];
  $weight = $_POST['weight'];
  $box_dimension = $_POST['box_dimension'];

  if (empty($country) || empty($shipping_type) || empty($weight) || empty($box_dimension)) {
    wp_send_json_error(array(
      "code" => 403,
      "message" => '값이 없습니다'
    ));
    return;
  }

  if ($shipping_type !== 'ems' && $shipping_type !== 'ups') {
    wp_send_json_error(array(
      "code" => 402,
      "message" => '올바르지 않은 값입니다'
    ));
    return;
  }

  $weight = intval($weight);

  if ($weight <= 0) {
    wp_send_json_error(array(
      "code" => 402,
      "message" => '올바르지 않은 값입니다'
    ));
    return;
  }

  if (!isset($box_dimension['width']) || !isset($box_dimension['height']) || !isset($box_dimension['depth'])) {
    wp_send_json_error(array(
      "code" => 404,
      "message" => '박스 크기 값이 없습니다'
    ));
    return;
  }

  $width = intval($box_dimension['width']);
  $height = intval($box_dimension['height']);
  $depth = intval($box_dimension['depth']);

  $rows = apmmust_get_shipping_fields();
  $fomula = apmmust_get_weight_fomula()[$shipping_type];

  $divisor = 30;

  for ($i = 0; $i < count($rows); $i += 1) {
    $row = $rows[$i];
    
    if ($country === $row['country'] && $shipping_type === $row['shipping_type']) {
      $quotient = intdiv($weight, $divisor);
      $remainder = $weight % $divisor;

      // 30으로 나눈 나머지와 부피중량중 더 큰값을 사용한다
      $weight = intval(max([$remainder, ceil(($width * $height * $depth)/$fomula) ]));

      // 우선 30kg을 초과했다면 이래나 저래나 30kg중량으로 계산한다
      $price = $quotient * intval($row['shipping_fee_kg']["shipping_fee_kg_30"]);

      $price += intval($row['shipping_fee_kg']["shipping_fee_kg_{$weight}"]);

		  $data = WOOMULTI_CURRENCY_Data::get_ins();
		  $rates = $data->get_exchange( 'USD', 'KRW' );

      $paypal_fee = 1.043;
      $usd = number_format(($price / $rates['KRW']) * $paypal_fee, 2); // paypal 수수료 적용

      $result = array (
        'price_krw' => $price,
        'weight' => $weight,
        'price_usd' => $usd,
        'estimate_date' => intval($row['estimate_shipping_date']),
        'rate_krw' => $rates['KRW'],
      );

      wp_send_json_success($result);
      return;
    }
  }
}

add_action( 'wp_ajax_apmmust_shipping_fee_table_action', 'apmmust_shipping_fee_table_action' );
add_action( 'wp_ajax_nopriv_apmmust_shipping_fee_table_action', 'apmmust_shipping_fee_table_action' );
function apmmust_shipping_fee_table_action() {
  if (!isset($_POST['country']) || !isset($_POST['shipping_type'])) {
    wp_send_json_error(array(
      "code" => 404,
      "message" => '값이 설정되어 있지 않습니다'
    ));
    return;
  }

  $country = $_POST['country'];
  $shipping_type = $_POST['shipping_type'];

  if (empty($country) || empty($shipping_type)) {
    wp_send_json_error(array(
      "code" => 403,
      "message" => '값이 없습니다'
    ), 401);
    return;
  }

  if ($shipping_type !== 'ems' && $shipping_type !== 'ups') {
    wp_send_json_error(array(
      "code" => 402,
      "message" => '올바르지 않은 값입니다'
    ), 401);
    return;
  }

  $rows = apmmust_get_shipping_fields();
  
  // 가능한 운송 수단을 선택했는지 점검
  $country_possible_shipping_type_map = apmmust_get_possible_shipping_type_of_country($rows);
  if (!in_array($shipping_type, $country_possible_shipping_type_map[$country])) {
    wp_send_json_error(array(
      'code' => 401,
      'message' => '가능한 운송 방법이 아닙니다',
    ));
    return;
  }

  for ($i = 0; $i < count($rows); $i += 1) {
    $row = $rows[$i];
    
    if ($country === $row['country'] && $shipping_type === $row['shipping_type']) {
		  $data = WOOMULTI_CURRENCY_Data::get_ins();
		  $rates = $data->get_exchange( 'USD', 'KRW' );

      $paypal_fee = 1.043;
      $shipping_fee_table = [];
      foreach ($row['shipping_fee_kg'] as $key => $price) {
        $usd = number_format(($price / $rates['KRW']) * $paypal_fee, 2); // paypal 수수료 적용
        $shipping_fee_table[] = $usd;
      }

      $result = array (
        'estimate_date' => intval($row['estimate_shipping_date']),
        'rate_krw' => $rates['KRW'],
        'table' => $shipping_fee_table
      );

      wp_send_json_success($result);
      return;
    }
  }
}


