<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * get product config data
 * @param type $product_id
 * @return boolean|\VPC_Config
 */
function get_product_config( $product_id ) {
    $ids		 = vpc_get_product_root_and_variations_ids( $product_id );
    $config_meta	 = get_post_meta( $ids[ "product-id" ], "vpc-config", true );
    $configs	 = Orion_Library::get_proper_value( $config_meta, $product_id, array() );
    $config_id	 = Orion_Library::get_proper_value( $configs, "config-id", false );

//        $config_meta = get_post_meta($product_id, "vpc-config", true);
//        $config_id=  Orion_Library::get_proper_value($config_meta, "config-id");
    if ( ! $config_id || empty( $config_id ) )
	return false;

    $config_obj = new VPC_Config( $config_id );
    return $config_obj;
}

function vpc_get_price_container() {
    if ( is_admin() && ! is_ajax() )
	return;
    ?>
    <div id="vpc-price-container">
        <span class="vpc-price-label" style="font-weight: normal;color:#768e9d"> <?php _e( "Total:", "vpc" ); ?> </span>
        <span id="vpc-price"></span>   
    </div>
    <?php
}

function vpc_get_action_buttons_arr( $product_id ) {
    $product	 = wc_get_product( $product_id );
    $product_price	 = $product->get_price();

    $add_to_cart = array(
	"id"		 => "vpc-add-to-cart",
	"label"		 => __( "Add to cart", "vpc" ),
	"class"		 => "",
	"attributes"	 => array(
	    "data-pid"	 => $product_id,
	    "data-price"	 => $product_price,
	),
    );

    $cid	 = "";
    if ( isset( $_GET[ "cid" ] ) )
	$cid	 = intval( $_GET[ "cid" ] );

    $buttons = array(
	$add_to_cart,
    );
    return apply_filters( "vpc_action_buttons", $buttons );
}

function vpc_get_action_buttons( $product_id ) {
    if ( ! $product_id )
	return;
    $buttons = vpc_get_action_buttons_arr( $product_id );
    ob_start();
    ?>
    <div class="vpc-action-buttons">
        <div class="o-col xl-1-1">
	    <?php
	    vpc_get_quantity_container();

	    foreach ( $buttons as $button ) {
		if ( ! isset( $button[ "requires_login" ] ) )
		    $button[ "requires_login" ]	 = false;
		if ( ! isset( $button[ "visible_admin" ] ) )
		    $button[ "visible_admin" ]	 = true;
		if ( ! isset( $button[ "attributes" ] ) )
		    $button[ "attributes" ]		 = array();

		if ( ! is_user_logged_in() && $button[ "requires_login" ] )
		    continue;
		else if ( is_admin() && ! is_ajax() && ! $button[ "visible_admin" ] )
		    continue;
		// Custom attribute handling
		$custom_attributes = array();

		foreach ( $button[ 'attributes' ] as $attribute => $attribute_value ) {
		    $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
		}
		?>
		<button
		    id="<?php echo esc_attr( $button[ 'id' ] ); ?>"
		    class="<?php echo esc_attr( $button[ 'class' ] ); ?>"
			<?php echo implode( ' ', $custom_attributes ); ?>
		    >
	<?php echo esc_attr( $button[ "label" ] ); ?>
		</button>

		<?php
	    }
	    ?>
        </div>
    </div>
    <?php
    $output = ob_get_contents();
    ob_end_clean();
    return apply_filters( "vpc_action_buttons_html", $output, $product_id );
}

/**
 * Register the stylesheets for the public-facing side of the site.
 */
function vpc_enqueue_core_scripts()
{
    wp_enqueue_script('oriontip-script', VPC_URL . 'public/libs/oriontip/oriontip.js', array('jquery'), VPC_VERSION, false);
    wp_enqueue_script('oimageload', VPC_URL . 'public/js/oimageload.js', array('jquery'), VPC_VERSION, false);
    wp_enqueue_script('vpc-fabric', VPC_URL . 'public/js/fabric.min.js', array('jquery'), VPC_VERSION, false);
    // phpcs:ignore // wp_enqueue_script( 'wp-js-hooks', VPC_URL . 'public/js/wp-js-hooks.min.js', array( 'jquery' ), VPC_VERSION, false );
    wp_enqueue_script('wp-serializejson', VPC_URL . 'public/js/jquery.serializejson.min.js', array('jquery'), VPC_VERSION, false);
    wp_enqueue_script('core-js', VPC_URL . 'public/js/core-js.js', array('jquery'), VPC_VERSION, false);
	do_action('vpc_enqueue_core_scripts');
}

/**
 * Include css files
 */
function vpc_enqueue_core_styles() {
    wp_enqueue_style( 'oriontip-style', VPC_URL . 'public/libs/oriontip/oriontip.css', array(), VPC_VERSION, 'all' );
    wp_enqueue_style( 'o-flexgrid', VPC_URL . 'admin/css/flexiblegs.css', array(), VPC_VERSION, 'all' );
	do_action('vpc_enqueue_core_styles');
}

/**
 * Function to verify current admin screen.
 */
function is_vpc_admin_screen()
{
    $screen            = get_current_screen();
    $is_correct_screen = false;
    if (isset($screen->base) && isset($screen->post_type) && ('vpc-config' === $screen->post_type || 'vpc-text-component' === $screen->post_type || 'vpc-upload-component' === $screen->post_type || 'vpc-rqa-form-data' === $screen->post_type || 'ofb' === $screen->post_type || 'product' === $screen->post_type || 'shop_order' === $screen->post_type || false !== strpos($screen->base, 'vpc') || false !== strpos($screen->post_type, 'vpc'))) {
        $is_correct_screen = true;
    }
}


function vpc_get_quantity_container() {
    global $vpc_settings;
    if ( is_admin() && ! is_ajax() )
	return;
    $action_qtity_box	 = Orion_Library::get_proper_value( $vpc_settings, "hide-qty", "Yes" );
    $qty			 = 1;
    if ( isset( $_GET[ 'qty' ] ) )
	$qty			 = intval( $_GET[ 'qty' ] );
    if ( $action_qtity_box == "No" )
	$style			 = "";
    else
	$style			 = "display:none;";
    ?>
    <div id="vpc-qty-container" class="" style="<?php echo $style; ?>">
        <input type="button" value="-" class="minus">
        <input id="vpc-qty" type="number" step="1" value="<?php echo $qty; ?>" min="1">
        <input type="button" value="+" class="plus">
    </div>
    <?php
}

function vpc_get_product_root_and_variations_ids( $id ) {
    $product_id	 = 0;
    $variation_id	 = 0;
    $variation	 = array();

    $variable_product = wc_get_product( $id );
    if ( ! $variable_product )
	return false;

    if ( vpc_woocommerce_version_check() )
	$product_type	 = $variable_product->product_type;
    else
	$product_type	 = $variable_product->get_type();

    if ( $product_type == "simple" )
	$product_id = $id;
    else {
	if ( vpc_woocommerce_version_check() ) {
	    $variation	 = $variable_product->variation_data;
	    $product_id	 = $variable_product->parent->id;
	} else {
	    $variation	 = $variable_product->get_data();
	    $product_id	 = $variable_product->get_parent_id();
	}
	$variation_id = $id;
    }

    return array( "product-id" => $product_id, "variation-id" => $variation_id, "variation" => $variation );
}

function vpc_sort_options_by_group( $options ) {
    $sorted_options = array();
    foreach ( $options as $option ) {
	if ( ! isset( $sorted_options[ $option[ "group" ] ] ) )
	    $sorted_options[ $option[ "group" ] ] = array();
	array_push( $sorted_options[ $option[ "group" ] ], $option );
    }
    $merged = call_user_func_array( 'array_merge', array_values( $sorted_options ) );

    return array_merge( $merged );
}

function vpc_get_configuration_url( $product_id, $saved_config_id = false, $template_id = false ) {
    global $vpc_settings;
    $config_page_id = Orion_Library::get_proper_value( $vpc_settings, "config-page" );
    if ( ! $config_page_id )
	return false;

    $design_url = get_permalink( $config_page_id );
    if ( $product_id ) {
//                $query = parse_url($design_url, PHP_URL_QUERY);
	// Returns a string if the URL has parameters or NULL if not
	$use_pretty_url = apply_filters( "vpc_use_pretty_url", true );
	if ( get_option( 'permalink_structure' ) && $use_pretty_url ) {
	    if ( substr( $design_url, -1 ) != '/' ) {
		$design_url .= '/';
	    }
	    // $design_url.='?vpc-pid=' . $product_id;
	    $design_url	 .= 'configure/' . $product_id . '/';
	    if ( $saved_config_id )
		$design_url	 .= "?cid=$saved_config_id";
	    else if ( $template_id )
		$design_url	 .= "?tid=$template_id";
	} else {
	    $url_args		 = array( "vpc-pid" => $product_id );
	    if ( $saved_config_id )
		$url_args[ "cid" ]	 = $saved_config_id;
	    else if ( $template_id )
		$url_args[ "tid" ]	 = $template_id;;
	    $design_url		 = add_query_arg( $url_args, $design_url );
	}
    }

    return $design_url;
}

function vpc_extract_configuration_images( $saved_config, $original_config ) {
    $components_by_names	 = $original_config->get_components_by_name();
    $output			 = "";

    foreach ( $saved_config as $saved_component_name => $saved_options ) {
	$original_options = $components_by_names[ $saved_component_name ];
	if ( ! is_array( $saved_options ) ) {
	    $saved_options = array( $saved_options );
	}

	foreach ( $saved_options as $saved_option ) {
	    $original_option = Orion_Library::get_proper_value( $original_options, $saved_option );
	    $img_id		 = Orion_Library::get_proper_value( $original_option, "image" );
	    if ( $img_id ) {
		$img_url = Orion_Library::get_media_url( $img_id );
		$output	 .= "<img src='$img_url'>";
	    }
	}
    }

    return $output;
}

function vpc_get_behaviours() {
    $behaviours_arr = apply_filters( "vpc_configuration_behaviours", array(
	"radio"		 => __( "Single choice", "vpc" ),
	"checkbox"	 => __( "Multiple choices", "vpc" ),
    ) );

    return $behaviours_arr;
}

function vpc_is_configurable( $metas ) {
    return ( ! empty( $metas[ 'config-id' ] ));
}

function vpc_product_is_configurable( $id ) {
    $metas		 = get_post_meta( $id, 'vpc-config', true );
    $product	 = wc_get_product( $id );
    if ( ! $product )
	return false;
    $class_name	 = get_class( $product );
    if ( $class_name == "WC_Product_Variable" ) {
	$variations = $product->get_available_variations();
	foreach ( $variations as $variation ) {
	    $variation_id		 = $variation[ "variation_id" ];
	    $variation_metas	 = Orion_Library::get_proper_value( $metas, $variation_id, false );
	    $variation_config_id	 = $variation_metas[ 'config-id' ];
	    if ( ! empty( $variation_config_id ) ) {
		return true;
	    } else {
		return false;
	    }
	}
    } else if ( $class_name == "WC_Product_Variation" ) {
	$Parent_ID		 = get_the_ID( $product );
	$metas			 = get_post_meta( $Parent_ID, 'vpc-config', true );
	$variation_metas	 = Orion_Library::get_proper_value( $metas, $id, false );
	$variation_config_id	 = $variation_metas[ 'config-id' ];
	if ( ! empty( $variation_config_id ) ) {
	    return true;
	} else {
	    return false;
	}
    } else {
	$configs	 = Orion_Library::get_proper_value( $metas, $id, array() );
	$config_id	 = Orion_Library::get_proper_value( $configs, "config-id", false );
	if ( ! empty( $config_id ) ) {
	    return true;
	} else {
	    return false;
	}
    }
}

function vpc_get_recap_from_cart_item( $data ) {
    if ( empty( $data ) || ! is_array( $data ) )
	return array();
    // $merged_with_keys=array(
    //     'product_id', 
    //     'variation_id', 
    //     'variation', 
    //     'quantity', 
    //     'data', 
    //     'line_tax',
    //     'line_total',
    //     'line_subtotal', 
    //     'line_subtotal_tax', 
    //     'line_tax_data',
    //     'addons');
    // $output=array_diff_key($data,array_flip($merged_with_keys));
    $output	 = array();
    if ( isset( $data[ 'visual-product-configuration' ] ) && ! empty( $data[ 'visual-product-configuration' ] ) )
	$output	 = $data[ 'visual-product-configuration' ];

    return $output;
}

function vpc_merge_pictures( $images, $path = false, $url = false ) {
    $tmp_dir	 = uniqid();
    $upload_dir	 = wp_upload_dir();
    $generation_path = $upload_dir[ "basedir" ] . "/VPC";
    $generation_url	 = $upload_dir[ "baseurl" ] . "/VPC";
    if ( wp_mkdir_p( $generation_path ) ) {
	$output_file_path	 = $generation_path . "/$tmp_dir.png";
	$output_file_url	 = $generation_url . "/$tmp_dir.png";
	foreach ( $images as $imgs ) {
	    list($width, $height) = getimagesize( $imgs );
	    $img = imagecreatefrompng( $imgs );
	    imagealphablending( $img, true );
	    imagesavealpha( $img, true );
	    if ( isset( $output_img ) ) {
		imagecopy( $output_img, $img, 0, 0, 0, 0, 1000, 500 );
	    } else {
		$output_img = $img;
		imagealphablending( $output_img, true );
		imagesavealpha( $output_img, true );
		imagecopymerge( $output_img, $img, 10, 12, 0, 0, 0, 0, 100 );
	    }
	}
	imagepng( $output_img, $output_file_path );
	imagedestroy( $output_img );
	if ( $path )
	    return $output_file_path;
	if ( $url )
	    return $output_file_url;
    } else
	return false;
}

function vpc_get_price_format() {
    $currency_pos	 = get_option( 'woocommerce_currency_pos' );
    $format		 = '%s%v';

    switch ( $currency_pos ) {
	case 'left' :
	    $format	 = '%s%v';
	    break;
	case 'right' :
	    $format	 = '%v%s';
	    break;
	case 'left_space' :
	    $format	 = '%s %v';
	    break;
	case 'right_space' :
	    $format	 = '%v %s';
	    break;
	default:
	    $format	 = '%s%v';
	    break;
    }
    return $format;
}

function vpc_get_order_item_configuration( $item ) {
    if ( isset( $item[ "vpc-original-config" ] ) ) {
	if ( vpc_woocommerce_version_check() )
	    $original_config = unserialize( $item[ "vpc-original-config" ] );
	else
	    $original_config = $item[ "vpc-original-config" ];
    } else {
	if ( $item[ "variation_id" ] )
	    $product_id	 = $item[ "variation_id" ];
	else
	    $product_id	 = $item[ "product_id" ];

	$original_config_obj	 = get_product_config( $product_id );
	$original_config	 = $original_config_obj->settings;
    }

    return $original_config;
}

function vpc_array_sanitize( $arr ) {
    $newArr = array();
    foreach ( $arr as $key => $value ) {
	$newArr[ $key ] = (is_array( $value ) ? vpc_array_sanitize( $value ) : sanitize_text_field( esc_html( $value ) ));
    }
    return $newArr;
}

function vpc_woocommerce_version_check( $version = '3.0.0' ) {
    if ( function_exists( "WC" ) && ( version_compare( WC()->version, $version, "<" )) )
	return true;
    return false;
}

/**
 * load xml from url
 * @param type $url
 * @return type
 */
function vpc_load_xml_from_url( $url ) {
    if ( function_exists( 'curl_init' ) ) {
	$ch		 = curl_init( $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
	$notifier_data	 = curl_exec( $ch );
	curl_close( $ch );
    }
    if ( ! $notifier_data ) {
	$notifier_data = file_get_contents( $url );
    }
    if ( $notifier_data ) {
	if ( strpos( (string) $notifier_data, '<notifier>' ) === false ) {
	    $notifier_data = '<?xml version="1.0" encoding="UTF-8"?><notifier><latest>1.0</latest><changelog></changelog></notifier>';
	}
    }
    $xml = simplexml_load_string( $notifier_data );
    return $xml;
}

/**
 * Active modern skin license key
 * 
 * @return boolean
 */
function vpc_activate_vpc_and_all_addons_licenses() {
    $site_url	 = get_site_url();
    $licences	 = vpc_get_vpc_and_all_addons_licenses();
    foreach ( $licences as $key => $value ) {
	if ( isset( $value[ 'purchase-code' ] ) && ! empty( $value[ 'purchase-code' ] ) ) {
	    if ( ! get_option( $key . '-license-key' ) ) {
		$purchase_code	 = $value[ 'purchase-code' ];
		$url          = ORION_SERVER_API_ROUTES . '/olicenses/v1/license/?purchase-code=' . $purchase_code . '&siteurl=' . rawurlencode($site_url) . '&slug=' . $key;
		$args		 = array( 'timeout' => 60 );
		$response	 = wp_remote_get( $url, $args );
		if ( is_wp_error( $response ) ) {
		    $error_message			 = $response->get_error_message();
		    $licences[ $key . '-checking' ]	 = "Something went wrong: $error_message";
		} else {
		    if ( isset( $response[ 'body' ] ) ) {
			$answer = $response[ 'body' ];
		    }
		    if ( is_array( json_decode( $answer, true ) ) ) {
			$data					 = json_decode( $answer, true );
			$answer_key				 = $data[ 'key' ];
			update_option( $key . '-license-key', $answer_key );
			$licences[ $key ][ $key . '-checking' ]	 = 'Activation successfully completed.';
			$licences[ $key ][ $key . '-status' ]	 = true;
		    } else {
			$licences[ $key ][ $key . '-checking' ]	 = $answer;
			$licences[ $key ][ $key . '-status' ]	 = false;
		    }
		}
	    } else {
		$licences[ $key ][ $key . '-checking' ]	 = __( 'Your plugin is already active.', 'vpc' );
		$licences[ $key ][ $key . '-status' ]	 = false;
	    }
	} else {
	    $licences[ $key ][ $key . '-checking' ]	 = __( "Purchase code not found. Please, set your purchase code in the plugin's settings.", 'vpc' );
	    $licences[ $key ][ $key . '-status' ]	 = false;
	}
    }
    set_transient( 'vpc-checking', 'valid', 1 * WEEK_IN_SECONDS );
    return $licences;
}

/**
 * This function get information (name, license key, admin url) on each product (vpc and these add-ons).
 *
 * @return array $licences Array containing for each product (vpc and add-ons), the official name of the product, its license key and its admin url
 */
function vpc_get_vpc_and_all_addons_licenses() {
    global $vpc_settings;
    $vpc_settings	 = get_option( 'vpc-options' );
    $licences	 = array();
    if ( class_exists( 'vpc' ) ) {
	$start_urls = 'edit.php?post_type=vpc-config&page=vpc-manage-settings';
	if ( ( class_exists( 'vpc_msl' ) ) ) {
	    $licences[ 'vpc-modern-skin' ] = array(
		'purchase-code'	 => get_proper_value( $vpc_settings, 'purchase-code-modern-skin', '' ),
		'name'		 => 'Modern Skin',
		'url'		 => admin_url( $start_urls . '&section=vpc-msl-container' ),
	    );
	}
    }
    return $licences;
}

/**
 * Get a value by key in an array if defined
 *
 * @param array  $values Array to search into
 * @param string $search_key Searched key
 * @param mixed  $default_value Value if the key does not exist in the array
 * @return mixed
 */
function get_proper_value( $values, $search_key, $default_value = '' ) {
    if ( isset( $values[ $search_key ] ) ) {
	$default_value = $values[ $search_key ];
    }
    return $default_value;
}

/**
 * Returns the loader html according to chosen loader option
 *
 */
function vpc_get_configurator_loader() {
    global $vpc_settings;
    $content	 = '';
    $loader_option	 = get_proper_value( $vpc_settings, 'hide-loader', 'Yes' );

    if ( $loader_option == 'No' ) {
	$content = '
    <div id="vpc-loader-container">
    <div>
    <div class="loadingio-spinner-gear-e4tpyed78c8"><div class="ldio-bv4uhcnsv1h">
    <div><div></div><div></div><div></div><div></div><div></div><div></div></div>
    </div></div>
    <span>' . __( "Loading configurator...", "vpc" ) . '</span>
    </div>
    </div>
    ';
    }

    echo $content;
}

/**
 * Sort option by group
 * @param type $options
 * @return type
 */
function sort_options_by_group( $options ) {
    $sorted_options = array();
    foreach ( $options as $option ) {
	if ( ! isset( $sorted_options[ $option[ 'group' ] ] ) ) {
	    $sorted_options[ $option[ 'group' ] ] = array();
	}
	array_push( $sorted_options[ $option[ 'group' ] ], $option );
    }
    $merged = call_user_func_array( 'array_merge', array_values( $sorted_options ) );

    return array_merge( $merged );
}

/**
 * get configurator description
 * @param type $config
 * @return type
 */
function get_configurator_description( $config ) {
    return ( isset( $config[ 'config-desc' ] ) ) ? nl2br( $config[ 'config-desc' ] ) : '';
}

/**
 * get image property url
 * @param type $suspected_link
 * @param type $with_root
 * @return type
 */
function o_get_proper_image_url( $suspected_link, $with_root = true ) {
    // var_dump($suspected_link);
    if ( empty( $suspected_link ) ) {
	return $suspected_link;
    }
    $img_src = $suspected_link;
    if ( is_numeric( $suspected_link ) ) {
	$raw_img_src	 = wp_get_attachment_url( $suspected_link );
	$img_src	 = str_replace( o_get_medias_root_url( '/' ), '', $raw_img_src );
    }
    $img_src = str_replace( o_get_medias_root_url( '/' ), '', $img_src );
    // Code for bad https handling
    if ( strpos( o_get_medias_root_url( '/' ), 'https' ) === false ) {
	$https_home	 = str_replace( 'http', 'https', o_get_medias_root_url( '/' ) );
	$img_src	 = str_replace( $https_home, '', $img_src );
    }

    if ( $with_root && strpos( $img_src, 'https://' ) === false && strpos( $img_src, 'http://' ) === false )//If there is not http or https 
	$img_src = o_get_medias_root_url( "/$img_src" );
    return $img_src;
}

/**
 * get medias root url
 * @param type $path
 * @return type
 */
function o_get_medias_root_url( $path = '/' ) {
    $upload_url_path = get_option( 'upload_url_path' );
    if ( $upload_url_path ) {
	return $upload_url_path . $path;
    } else {
	return site_url( $path );
    }
}

/**
 * Apply taxes on price
 * @param type $price
 * @param type $product
 * @return type
 */
function vpc_apply_taxes_on_price_if_needed( $price, $product ) {
    $qty = 1;
    if ( class_exists( 'Woocommerce' ) ) {
	return ( 'incl' === get_option( 'woocommerce_tax_display_shop' ) && false !== $product ) ? wc_get_price_including_tax(
	$product,
 array(
	    'qty'	 => $qty,
	    'price'	 => $price,
	)
	) : $price;
    } else {
	return $price;
    }
}
/**
 * Register the stylesheets for the public-facing side of the skin.
 *
 * @param string $skin_name The skin name.
 */
function vpc_skins_enqueue_styles_scripts($skin_name)
{
    if (isset($skin_name) && ('VPC_Right_Sidebar_Skin' === $skin_name || 'VPC_Default_Skin' === $skin_name)) {
        if ('VPC_Right_Sidebar_Skin' === $skin_name) {
            wp_enqueue_style('vpc-right-sidebar-skin', VPC_URL . 'public/css/vpc-right-sidebar-skin.css', array(), VPC_VERSION, 'all');
        }
        wp_enqueue_style('vpc-default-skin', VPC_URL . 'public/css/vpc-default-skin.css', array(), VPC_VERSION, 'all');
        wp_enqueue_style('FontAwesome', VPC_URL . 'public/css/font-awesome.min.css', array(), VPC_VERSION, 'all');
        wp_enqueue_script('vpc-default-skin', VPC_URL . 'public/js/vpc-default-skin.js', array('jquery', 'vpc-public'), VPC_VERSION, false);
        wp_localize_script('vpc-default-skin', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }
	do_action('vpc_skins_enqueue_styles_scripts', $skin_name);
}

function get_allowed_tags() {
	$allowed_tags = wp_kses_allowed_html( 'post' );
	add_filter(
		'safe_style_css',
		function ( $styles ) {
			$styles[] = 'display';
			return $styles;
		}
	);

	$allowed_tags['li'] = array(
		'id'             => array(),
		'name'           => array(),
		'class'          => array(),
		'value'          => array(),
		'style'          => array(),
		'data-ttf'       => array(),
		'data-fonturl'   => array(),
		'data-fontname'  => array(),
		'data-color'     => array(),
		'data-minwidth'  => array(),
		'data-minheight' => array(),
	);

	$allowed_tags['br'] = array();

	$allowed_tags['input'] = array(
		'type'                   => array(),
		'id'                     => array(),
		'name'                   => array(),
		'style'                  => array(),
		'class'                  => array(),
		'value'                  => array(),
		'min'                    => array(),
		'max'                    => array(),
		'row_class'              => array(),
		'selected'               => array(),
		'checked'                => array(),
		'readonly'               => array(),
		'placeholder'            => array(),
		'step'                   => array(),
		'data-fonturl'           => array(),
		'data-fontname'          => array(),
		'data-minwidth'          => array(),
		'data-minheight'         => array(),
		'autocomplete'           => array(),
		'autocorrect'            => array(),
		'autocapitalize'         => array(),
		'spellcheck'             => array(),
		'pattern'                => array(),
		'required'               => array(),
		'data-validation-engine' => array(),
		'data-price'             => array(),

	);
	$allowed_tags['form'] = array(
		'accept-charset' => array(),
		'id'             => array(),
		'name'           => array(),
		'style'          => array(),
		'class'          => array(),
		'value'          => array(),
		'action'         => array(),
		'autocomplete'   => array(),
		'row_class'      => array(),
		'novalidate'     => array(),
		'method'         => array(),
		'readonly'       => array(),
		'target'         => array(),
		'data-fonturl'   => array(),
		'data-fontname'  => array(),
		'data-minwidth'  => array(),
		'data-minheight' => array(),
		'autocorrect'    => array(),
		'autocapitalize' => array(),
		'hidden'         => array(),
		'enctype'        => array(),
	);

	$allowed_tags['div'] = array(
		'id'                   => array(),
		'name'                 => array(),
		'data-id'              => array(),
		'class'                => array(),
		'row_class'            => array(),
		'role'                 => array(),
		'aria-labelledby'      => array(),
		'aria-hidden'          => array(),
		'data-fonturl'         => array(),
		'data-minwidth'        => array(),
		'data-minheight'       => array(),
		'data-tooltip-content' => array(),
		'tabindex'             => array(),
		'style'                => array(),
		'data-tooltip-title'   => array(),
		'data-placement'       => array(),
		'media'                => array(),
		'data-result'          => array(),
		'uk-modal'			   => array(),
	);
	$allowed_tags['i']   = array();

	$allowed_tags['button'] = array(
		'id'                 => array(),
		'name'               => array(),
		'class'              => array(),
		'value'              => array(),
		'data-tpl'           => array(),
		'style'              => array(),
		'data-id'            => array(),
		'data-dismiss'       => array(),
		'aria-hidden'        => array(),
		'data-editor'        => array(),
		'type'               => array(),
		'data-wp-editor-id'  => array(),
		'data-pid'           => array(),
		'data-price'         => array(),
		'data-currency-rate' => array(),
        'data-prod-id'       => array(),
		'data-pid'			=> array(),
	);

	$allowed_tags['body'] = array(
		'id'                 => array(),
		'name'               => array(),
		'class'              => array(),
		'data-gr-c-s-loaded' => array(),
	);

	$allowed_tags['a']        = array(
		'id'               => array(),
		'name'             => array(),
		'class'            => array(),
		'data-tpl'         => array(),
		'href'             => array(),
		'data-toggle'      => array(),
		'data-target'      => array(),
		'data-modalid'     => array(),
		'target'           => array(),
		'data-group'       => array(),
		'data-slide-index' => array(),
		'download'         => array(),
		'style'            => array(),
        'data-view'        => array(),
		'data-id'			=> array(),
		'uk-toggle'			=> array(),
	);
	$allowed_tags['select']   = array(
		'id'         => array(),
		'name'       => array(),
		'class'      => array(),
		'data-tpl'   => array(),
		'style'      => array(),
		'multiple'   => array(),
		'tabindex'   => array(),
		'data-rule'  => array(),
		'data-group' => array(),
	);
	$allowed_tags['optgroup'] = array(
		'id'       => array(),
		'name'     => array(),
		'class'    => array(),
		'data-tpl' => array(),
		'style'    => array(),
		'multiple' => array(),
		'tabindex' => array(),
		'label'    => array(),
	);
	$allowed_tags['option']   = array(
		'id'       => array(),
		'name'     => array(),
		'class'    => array(),
		'value'    => array(),
		'style'    => array(),
		'selected' => array(),
		'tabindex' => array(),
	);

	$allowed_tags['span'] = array(
		'id'                 => array(),
		'name'               => array(),
		'class'              => array(),
		'value'              => array(),
		'style'              => array(),
		'data-tooltip-title' => array(),
		'data-placement'     => array(),
	);

	$allowed_tags['h1']     = array(
		'id'    => array(),
		'class' => array(),
		'style' => array(),
	);
	$allowed_tags['iframe'] = array();
	$allowed_tags['h2']     = array(
		'id'    => array(),
		'class' => array(),
		'style' => array(),
	);
	$allowed_tags['h3']     = array(
		'style' => array(),
		'id'    => array(),
		'class' => array(),
	);

	$allowed_tags['link'] = array(
		'id'    => array(),
		'rel'   => array(),
		'media' => array(),
		'href'  => array(),
	);

	$allowed_tags['textarea'] = array(
		'autocomplete'   => array(),
		'autocorrect'    => array(),
		'autocapitalize' => array(),
		'spellcheck'     => array(),
		'class'          => array(),
		'rows'           => array(),
		'cols'           => array(),
		'name'           => array(),
		'id'             => array(),
		'style'          => array(),
	);

	$allowed_tags['table'] = array(
		'border'      => array(),
		'cellpadding' => array(),
		'cellspacing' => array(),
		'class'       => array(),
		'style'       => array(),
		'id'          => array(),
	);

	$allowed_tags['tr'] = array(
		'align'   => array(),
		'class'   => array(),
		'style'   => array(),
		'data-id' => array(),
	);

	$allowed_tags['td'] = array(
		'colspan' => array(),
		'class'   => array(),
		'style'   => array(),
	);

	$allowed_tags['th'] = array(
		'colspan' => array(),
		'class'   => array(),
		'style'   => array(),
	);

	$allowed_tags['img'] = array(
		'src'    => array(),
		'alt'    => array(),
		'height' => array(),
		'width'  => array(),
		'style'  => array(),
		'class'  => array(),
	);

	$allowed_tags['script'] = array(
		'src'  => array(),
		'type' => array(),
	);
	$allowed_tags['style'] = array(
		'type' => array(),
	);
	$allowed_tags['i'] = array(
		'id'    => array(),
		'class' => array(),
		'style' => array(),
	);
	return $allowed_tags;
}