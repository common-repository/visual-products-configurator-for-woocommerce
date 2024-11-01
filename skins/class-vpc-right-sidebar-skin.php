<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 *
 * @package    Vpc
 * @subpackage Vpc/skins
 */

/**
 * Description of class-vpc-default-skin
 *
 * @author HL
 */
class VPC_Right_Sidebar_Skin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $product    The product data.
     */
    public $product;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $product_id    The product id.
     */
    public $product_id;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $settings   The plugin settings.
     */
    public $settings;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $config    Configuration datas.
     */
    public $config;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $product_id     $product_id.
     * @param      string $config    Configuration datas.
     */
    public function __construct( $product_id = false, $config = false ) {
	if ( $product_id ) {
	    if ( vpc_woocommerce_version_check() ) {
		$this->product = new WC_Product( $product_id );
	    } else {
		$this->product = wc_get_product( $product_id );
	    }
	    $this->product_id	 = $product_id;
	    $this->config		 = get_product_config( $product_id );
	} elseif ( $config ) {
	    $this->config = new VPC_Config( $config );
	}
    }

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      array $config_to_load    Configurator datas.
     */
    public function display( $config_to_load = array() ) {
	$this->enqueue_styles_scripts();
	ob_start();

	if ( ! $this->config || empty( $this->config ) ) {
	    return __( 'No valid configuration has been linked to this product. Please visit your WooCommerce products and link one by selecting a configuration in the product edit screen.', 'vpc' );
	}
	$skin_name = get_class( $this );

	$config = $this->config->settings;

	$options_style		 = '';
	$components_aspect	 = Orion_Library::get_proper_value( $config, 'components-aspect', 'closed' );
	if ( 'closed' === $components_aspect ) {
	    $options_style = 'display: none';
	}
	$product_id = '';
	if ( class_exists( 'Woocommerce' ) ) {
	    if ( vpc_woocommerce_version_check() ) {
		$product_id = $this->product->id;
	    } else {
		$product_id = $this->product->get_id();
	    }
	    do_action( 'vpc_before_container', $config, $product_id, $this->config->id );
	}
	?>
	<div id="vpc-container" class="o-wrap <?php echo esc_attr( $skin_name ); ?>" data-curr="<?php echo get_woocommerce_currency_symbol(); ?>">
	    <div class="col xl-2-3 lg-2-3 md-1-1 sm-1-1">
		<?php
		vpc_get_price_container( $this->product->get_id() );
		$preview_html	 = '<div id="vpc-preview"></div>';
		$preview_html	 = apply_filters( 'vpc_preview_container', $preview_html, $product_id, $this->config->id );
		echo $preview_html;
		?>
		<?php do_action( 'vpc_after_preview_area', $config, $this->product->get_id(), $this->config->id ); 
		?>
		<div class="made-with-vpc"><a href="https://www.orionorigin.com/product/visual-product-configurator-for-woocommerce"><?php _e('Made with vpc','vpc'); ?></a></div>
	    </div>
	    <div class="col xl-1-3 lg-1-3 md-1-1 sm-1-1" id="vpc-components">
		<?php
		do_action( 'vpc_before_components', $config, $product_id );
		if ( isset( $config[ 'components' ] ) ) {
		    foreach ( $config[ 'components' ] as $component_index => $component ) {
			$this->get_components_block( $component, $options_style, $config, $config_to_load );
		    }
		}

		do_action( 'vpc_after_components', $config, $product_id, $config_to_load );
		?>
	    </div>
	    <div>
		<?php do_action( 'vpc_container_end', $config ); ?>
	    </div>
	</div>
	<div class="o-left-offset-2-3">
	    <?php echo vpc_get_action_buttons( $this->product_id ); ?>
	</div>       
	<div id="debug"></div>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
    }

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      array  $component        A component's datas.
     * @param      string $options_style    Options's container style.
     * @param      array  $config_to_load   A configuration old selected options datas.
     */
    private function get_components_block( $component, $options_style, $config_to_load = array() ) {
	$skin_name	 = get_class( $this );
	$c_icon		 = '';
	$options	 = Orion_Library::get_proper_value( $component, 'options', array() );
	if ( $options && count( $options ) > 0 ) {
	    $options = vpc_sort_options_by_group( $options );
	}
	$component_id	 = 'component_' . sanitize_title( str_replace( ' ', '', $component[ 'cname' ] ) );
	$component_id	 = Orion_Library::get_proper_value( $component, 'component_id', $component_id );

	// We make sure we have an usable behaviour.
	$handlable_behaviours = vpc_get_behaviours();

	$component[ 'behaviour' ] = Orion_Library::get_proper_value( $component, 'behaviour', 'radio' );
	if ( $component[ 'cimage' ] ) {
	    $c_icon = "<img src='" . Orion_Library::o_get_proper_image_url( $component[ 'cimage' ] ) . "'>";
	}
	$components_attributes_string = apply_filters( 'vpc_component_attributes', "data-component_id = '$component_id'", $this->product_id, $component );
	?>
	<div id = '<?php echo esc_attr( $component_id ); ?>' class="vpc-component" <?php echo esc_attr( $components_attributes_string ); ?>>

	    <div class="vpc-component-header">
		<?php
		echo "$c_icon<span style='display: inline-block;'><span>" . $component[ "cname" ] . "</span>";
		?>

		<span class="vpc-selected txt"><?php _e( 'none', 'vpc' ); ?></span></span>
		<span class="vpc-selected-icon"><img width="24" src="" alt="..."></span>

	    </div>
	    <div class="vpc-options" style="<?php echo esc_attr( $options_style ); ?>">
		<?php
		do_action( 'vpc_' . $component[ 'behaviour' ] . '_begin', $component, $skin_name );

		$current_group = '';
		if ( ! is_array( $options ) || empty( $options ) ) {
		    esc_html_e( 'No option detected for the component. You need at least one option per component.', 'vpc' );
		} else {
		    foreach ( $options as $option_index => $option ) {
			if ( ( $option[ 'group' ] !== $current_group ) || ( 0 === $option_index ) ) {
			    if ( 0 !== $option_index ) {
				echo '</div>';
			    }
			    echo "<div class='vpc-group'><div class='vpc-group-name'>" . esc_html( $option[ 'group' ] ) . '</div>';
			}
			$current_group = $option[ 'group' ];

			$o_image = Orion_Library::o_get_proper_image_url( $option[ 'image' ] );

			$o_icon	 = Orion_Library::get_proper_value( $option, 'icon_url' );
			$o_icon	 = Orion_Library::o_get_proper_image_url( $option[ 'icon' ] );
			$o_name	 = $component[ 'cname' ];

			$checked = '';
			if ( $config_to_load && isset( $config_to_load[ $component[ 'cname' ] ] ) ) {
			    $saved_options = $config_to_load[ $component[ 'cname' ] ];
			    if ( ( is_array( $saved_options ) && in_array( $option[ 'name' ], $saved_options ) ) || ( $option[ 'name' ] === $saved_options ) ) {
				$checked = "checked='checked'";
			    }
			} elseif ( isset( $option[ 'default' ] ) && '1' === $option[ 'default' ] ) {
			    $checked = "checked='checked' data-default='1'";
			}
			$price		 = Orion_Library::get_proper_value( $option, 'price', 0 );
			$linked_product	 = Orion_Library::get_proper_value( $option, 'product', false );
			if ( $linked_product ) {
			    $product = new WC_Product( $linked_product );
			    if ( ! $product->is_purchasable() || ( $product->managing_stock() && ! $product->is_in_stock() ) ) {
				if ( count( $options ) - 1 === $option_index ) {
				    echo '</div>';
				}
				continue;
			    }
			    $price = $product->get_price();
			}

			if ( apply_filters( 'vpc_option_visibility', 1, $option ) !== 1 ) {
			    if ( count( $options ) - 1 === $option_index ) {
				echo '</div>';
			    }
			    continue;
			}

			$formated_price_raw	 = wc_price( $price );
			$formated_price		 = wp_strip_all_tags( $formated_price_raw );
			$option_id		 = 'component_' . sanitize_title( str_replace( ' ', '', $component[ 'cname' ] ) ) . '_group_' . sanitize_title( str_replace( ' ', '', $option[ 'group' ] ) ) . '_option_' . sanitize_title( str_replace( ' ', '', $option[ 'name' ] ) );
			$option_id		 = Orion_Library::get_proper_value( $option, 'option_id', $option_id );

			switch ( $component[ 'behaviour' ] ) {
			    case 'radio':
			    case 'checkbox':
				$input_type = 'radio';
				if ( 'checkbox' === $component[ 'behaviour' ] ) {
				    $o_name		 .= '[]';
				    $input_type	 = 'checkbox';
				}

				$tooltip = $option[ 'name' ];

				if ( $price ) {
				    $tooltip .= " +$formated_price";
				}
				if ( ! empty( $option[ 'desc' ] ) ) {
				    $tooltip .= ' (' . $option[ 'desc' ] . ')';
				}
				$label_id	 = "cb$option_id";
				$customs_datas	 = apply_filters( 'vpc_options_customs_datas', '', $option );
				?>
				<div class="vpc-single-option-wrap" data-oid="<?php echo esc_attr( $option_id ); ?>" >
				    <input id="<?php echo esc_attr( $option_id ); ?>" type="<?php echo esc_attr( $input_type ); ?>" name="<?php echo esc_attr( $o_name ); ?>" value="<?php echo esc_attr( $option[ 'name' ] ); ?>" data-img="<?php echo esc_attr( $o_image ); ?>" data-icon="<?php echo esc_attr( $o_icon ); ?>" data-price="<?php echo esc_attr( $price ); ?>" data-product="" data-oid="<?php echo esc_attr( $option_id ); ?>" <?php echo esc_attr( $checked ) . ' ' . esc_attr( $customs_datas ); ?>>
				    <label id="<?php echo esc_attr( $label_id ); ?>" for="<?php echo esc_attr( $option_id ); ?>" data-o-title="<?php echo esc_attr( $tooltip ); ?>" class="custom"></label>
				    <style>
					#<?php echo esc_attr( $label_id ); ?>:before
					{
					    background-image: url("<?php echo esc_attr( $o_icon ); ?>");
					}
				    </style>
				</div>
				<?php
				break;
			    default:
				do_action( 'vpc_' . $component[ 'behaviour' ], $option, $o_image, $price, $option_id, $component, $skin_name, $config_to_load );
				break;
			}
			if ( count( $options ) - 1 === $option_index ) {
			    echo '</div>';
			}
			$current_group = $option[ 'group' ];
		    }
		}
		do_action( 'vpc_' . $component[ 'behaviour' ] . '_end', $component, $this->config, $skin_name );
		?>
	    </div>
	</div>
	<?php
    }

    /**
     * Register the stylesheets and script for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles_scripts() {
	if ( is_admin() ) {
	    vpc_enqueue_core_scripts();
	}
	wp_enqueue_style( 'vpc-default-skin', VPC_URL . 'public/css/vpc-default-skin.css', array(), VPC_VERSION, 'all' );
	wp_enqueue_style( 'o-flexgrid', VPC_URL . 'admin/css/flexiblegs.css', array(), VPC_VERSION, 'all' );
	wp_enqueue_style( 'FontAwesome', VPC_URL . 'public/css/font-awesome.min.css', array(), VPC_VERSION, 'all' );
	wp_enqueue_style( 'o-tooltip', VPC_URL . 'public/css/tooltip.min.css', array(), VPC_VERSION, 'all' );
	wp_enqueue_style( 'vpc-right-sidebar-skin', VPC_URL . 'public/css/vpc-right-sidebar-skin.css', array(), VPC_VERSION, 'all' );

	wp_enqueue_script( 'o-tooltip', VPC_URL . 'public/js/tooltip.min.js', array( 'jquery' ), VPC_VERSION, false );
	wp_enqueue_script( 'vpc-default-skin', VPC_URL . 'public/js/vpc-default-skin.min.js', array( 'jquery','vpc-public' ), VPC_VERSION, false );
	wp_localize_script( 'vpc-default-skin', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script( 'o-serializejson', VPC_URL . 'public/js/jquery.serializejson.min.js', array( 'jquery' ), VPC_VERSION, false );
    }
}