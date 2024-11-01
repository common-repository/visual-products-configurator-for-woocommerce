<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.orionorigin.com
 * @since      1.0.0
 *
 * @package    Vpc
 * @subpackage Vpc/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Vpc
 * @subpackage Vpc/admin
 * @author     ORION <help@orionorigin.com>
 */
class VPC_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Vpc_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Vpc_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style('vpc-admin', plugin_dir_url(__FILE__) . 'css/vpc-admin.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/vpc-admin.min.css', array(), $this->version, 'all');
        wp_enqueue_style("o-flexgrid", plugin_dir_url(__FILE__) . 'css/flexiblegs.css', array(), $this->version, 'all');
        wp_enqueue_style("o-ui", plugin_dir_url(__FILE__) . 'css/UI.css', array(), $this->version, 'all');
        wp_enqueue_style("o-tooltip", VPC_URL . 'public/css/tooltip.min.css', array(), $this->version, 'all');
        wp_enqueue_style("o-bs-modal-css", VPC_URL . 'admin/js/modal/modal.min.css', array(), $this->version, 'all');

        if (class_exists( 'WooCommerce' ))
            wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Vpc_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Vpc_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/vpc-admin.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script("o-admin", plugin_dir_url(__FILE__) . 'js/o-admin.min.js', array('jquery', 'jquery-ui-sortable'), $this->version, false);
        wp_localize_script("o-admin", 'home_url', array(Orion_Library::get_medias_root_url("/")));
        wp_enqueue_script("o-tooltip", VPC_URL . 'public/js/tooltip.min.js', array('jquery'), $this->version, false);
//        wp_enqueue_script("o-lazyload", VPC_URL . 'admin/js/jquery.lazyload.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('o-modal-js', VPC_URL . 'admin/js/modal/modal.min.js', array('jquery'), false, false);
        wp_enqueue_script("jquery-serializejson", VPC_URL . 'public/js/jquery.serializejson.min.js', array('jquery'), $this->version, false);

//Set string translation for js scripts
        $string_translations = array(
            "reverse_cb_label" => __("Enable reverse rule", 'vpc'),
            "group_conditions_relation" => __("Conditions relationship", "vpc"),
        );
        wp_localize_script($this->plugin_name, 'string_translations', array($string_translations));
    }


    public function get_vpc_screen_layout_columns($columns) {
        $columns['vpc-config'] = 1;
        return $columns;
    }

    public function get_vpc_config_screen_layout() {
        return 1;
    }

    public function metabox_order($order) 
    {
        if(!is_array($order)) 
            $order = array();
        $order["advanced"] = "vpc-config-preview-box,vpc-config-settings-box,vpc-config-conditional-rules-box,submitdiv";
        return $order;
    }

    /**
     * Builds all the plugin menu and submenu
     */
    public function get_menu() {
        $parent_slug = "edit.php?post_type=vpc-config";
        add_submenu_page($parent_slug, __('Settings', 'vpc'), __('Settings', 'vpc'), 'manage_product_terms', 'vpc-manage-settings', array($this, 'get_vpc_settings_page'));
        add_submenu_page($parent_slug, __('Getting Started', 'vpc'), __('Getting Started', 'vpc'), 'manage_product_terms', 'vpc-getting-started', array($this, 'get_vpc_getting_started_page_new'));
        add_submenu_page($parent_slug, __('Get support', 'vpc' ), __( 'Get support', 'vpc' ), 'manage_product_terms', 'wad-submit-a-ticket', array($this, "redirect_to_support"));

        //add_submenu_page($parent_slug, __('Add-ons', 'vpc'), __('Add-ons & Support', 'vpc'), 'manage_product_terms', 'vpc-manage-add-ons', array($this, 'get_vpc_addons_page'));
    }
    
    /**
     * Redirect to the ticket support for send a issue.
     *
     * @return void
     */
    public function redirect_to_support(){
        wp_redirect( 'https://orionorigin.com/contact/?utm_source=VPC%20free&utm_medium=get%support%20submenu&utm_campaign=client-site');
        exit();
    }


    public function get_vpc_settings_page() {
        if ( isset($_REQUEST['vpc-options_nonce'] ) &&  wp_verify_nonce($_REQUEST['vpc-options_nonce'], 'vpc-options_nonce' ) ) 
        {
            $posts_datas  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            if ((isset($posts_datas["vpc-options"]) && !empty($posts_datas["vpc-options"])) && current_user_can('manage_product_terms')) {
                $options=vpc_array_sanitize($posts_datas["vpc-options"]);
                if(is_array($options)){
                    $esc_vpc_options=array();
                    $vpc_options=$options;
                    foreach($vpc_options as $key=>$vpc_option)
                        $esc_vpc_options[$key]=sanitize_text_field(esc_html($vpc_option));
                    update_option("vpc-options", $esc_vpc_options);
                }
                $vpc        = new Vpc();
                $vpc_public = new VPC_Public($vpc->get_plugin_name(), $vpc->get_version());
                $vpc_public->init_globals();
                $license_activation_result = vpc_activate_vpc_and_all_addons_licenses();
                ?>
                <div id="activation-success-notice" class="notice notice-info is-dismissible" style="display:block;">
                    <?php
                    foreach ($license_activation_result as $key => $value) {
                        if ('Activation successfully completed.' !== $value[$key . '-checking']) {
                    ?>
                            <div><strong> <?php esc_html_e('Visual products configurator ', 'vpc'); ?> <?php echo esc_html($value['name']); ?> </strong> : <?php echo esc_html($value[$key . '-checking']); ?></div>
                    <?php
                        }
                    }
                    ?>
                </div>
                <?php
                global $wp_rewrite;
                $wp_rewrite->flush_rules(); 
            }
	} 
        ?>
        <div class="wrap cf">
            <h1><?php _e("Visual Products Configurator Settings", "vpc"); ?></h1>
            <form method="POST" action="" class="mg-top">
                <div class="postbox" id="vpc-options-container">
                    <?php
                    $begin = array(
                        'type' => 'sectionbegin',
                        'id' => 'vpc-options-container',
                        'table' => 'options',
                    );
                    $args = array(
                        "post_type" => "page",
                        "nopaging" => true,
                    );
                    $pages = get_posts($args);
                    $pages_ids = array();
                    foreach ($pages as $page) {
                        $pages_ids[$page->ID] = $page->post_title;
                    }
                    $configuration_page = array(
                        'title' => __('Configuration page', 'vpc'),
                        'name' => 'vpc-options[config-page]',
                        'type' => 'select',
                        'options' => $pages_ids,
                        'default' => '',
                        'class' => 'chosen_select_nostd',
                        'desc' => __('Page where all products are configured.', 'vpc'),
                    );


                    $cart_actions_arr = array(
                        "none" => __("None", "vpc"),
                        "refresh" => __("Refresh", "vpc"),
                        "redirect" => __("Redirect to cart page", "vpc"),
                        "redirect_to_product_page" => __("Redirect to product page", "vpc"),
                    );
                    
                    $hide_qty_box = array(
                        'title' => __('Hide quantity box', 'vpc'),
                        'name' => 'vpc-options[hide-qty]',
                        'type' => 'radio',
                        'options' => array("Yes" => "Yes", "No" => "No"),
                        'default' => 'Yes',
                        //'class' => 'chosen_select_nostd',
                        'desc' => __('Hide quantity box on configurator page?', 'vpc'),
                    );
                    
                 

                    $action_in_cart = array(
                        'title' => __('Action after addition to cart', 'vpc'),
                        'name' => 'vpc-options[action-after-add-to-cart]',
                        'type' => 'select',
                        'options' => $cart_actions_arr,
                        'default' => '',
                        'class' => 'chosen_select_nostd',
                        'desc' => __('What should happen once the customer adds the configured product to the cart.', 'vpc'),
                    );

                    $end = array('type' => 'sectionend');
                    $settings = apply_filters("vpc_global_settings", array(
                        $begin,
                        $configuration_page,
                        $hide_qty_box,
                        $action_in_cart,
                        $end
                    ));
                    echo Orion_Library::o_admin_fields($settings);
                    ?>
                </div>
                <input type="hidden" name="vpc-options_nonce" value="<?php echo wp_create_nonce( 'vpc-options_nonce' ); ?>" />
                <input type="submit" class="button button-primary button-large" value="Save">
            </form>
        </div>
        <?php
    }

    public function get_vpc_addons_page() {
    ?>
        <div class="wrap cf"></div>
    <?php
    }
    
    public function get_vpc_getting_started_page() {
    ?>
         <div class="wrap cf"> 
            <h1 class="">
                <?php _e("About Visual Products Configurator", "vpc"); ?>
            </h1>
            <div class="vpc-getting-started">
                <div class="postbox vpc-gs-half" id="vpc-presentation">
                    <div class="vpc-addon-section-title-container vpc-addon-section-description">
                        <h2 class="vpc-addon-section-title"><?php _e("Visual Products Configurator", "vpc"); ?></h2>
                        <p class="vpc-addon-section-subtitle">
                            <?php _e("A smart and flexible extension <br> which lets you setup any customizable <br> product your customers can configure <br> visually prior to purchase.", "vpc");?>
                        </p>
                        <a class="button" href="https://www.woocommerceproductconfigurator.com/"><?php _e("From: $60", "vpc"); ?></a>
                    </div>
                </div>
                <div class="postbox vpc-gs-half" id="youtube-video-container">
                    <div class="videos_youtube">
                        <iframe src="https://www.youtube.com/embed/2auCs0EBqjE?list=PLC9GLMXokPgXW3mYmXYJc-QstNGgF173d" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>

            <div class="vpc-getting-started">
                <div class="vpc-getting-started-body">
                    <h2 class="vpc-addon-section-title"><?php _e("Pro version features", "vpc"); ?></h2>
                    <span class="vpc-addon-section-title"><?php _e("From services to content, there’s no limit to what you can sell with Visual Product Configurator", "vpc"); ?></span>

                    <div class="feature">
                        <img src="<?php echo VPC_URL; ?>/admin/images/logic.png">
                        <h3 class="vpc-addon-section-title"><?php _e("Conditional logic", "vpc"); ?></h3>
                        <span class="vpc-addon-section-title"><?php _e("Allows you to automatically show or hide some options or components based on the customer selection.", "vpc"); ?></span>
                    </div>

                    <div class="feature">
                        <img src="<?php echo VPC_URL; ?>/admin/images/multiple.png">
                        <h3 class="vpc-addon-section-title"><?php _e("Multiple options selection", "vpc"); ?></h3>
                        <span class="vpc-addon-section-title"><?php _e("Allows the selection of multiple options within the same component.", "vpc"); ?></span>
                    </div>

                    <div class="feature">
                        <img src="<?php echo VPC_URL; ?>/admin/images/linked.png">
                        <h3 class="vpc-addon-section-title"><?php _e("Linked products", "vpc"); ?></h3>
                        <span class="vpc-addon-section-title"><?php _e("Allows you to link existing products to an option in order to trigger everything related to the linked products once the order is made.", "vpc"); ?></span>
                    </div>

                    <div class="feature">
                        <img src="<?php echo VPC_URL; ?>/admin/images/priority.png">
                        <h3 class="vpc-addon-section-title"><?php _e("Priority support", "vpc"); ?></h3>
                        <span class="vpc-addon-section-title"><?php _e("Get help from our support team within the next two hours after submitting your ticket..", "vpc"); ?></span>
                    </div>


                </div>
            </div>

            <div class="clearfix"></div>

            <div class="vpc-getting-started pubs">
                <div class="pub-theme">
                    <img src="<?php echo VPC_URL; ?>/admin/images/gp.png">
                    
                    <div>
                        <h2 class="vpc-addon-section-title"><?php _e("Grand Popo is the official shop theme for Visual Product Configurator", "vpc"); ?></h2>
                        
                        <p class="vpc-addon-section-subtitle">
                            <?php _e("Grand-Popo is a powerful ecommerce solution for creating large scale online stores, complete with advanced e-commerce marketing & up selling solutions for WordPress. 
                                Perfect for any electronic store, drones shop, fashion & clothing megastore, food markets and any other WordPress shop you can think of.", "vpc");?>
                        </p>

                        <div>
                            <a class="button" href="http://demos.orionorigin.com/grand-popo/01/free-trial/?utm_source=VPC%20Free&utm_medium=cpc&utm_campaign=Grand-Popo&utm_content=Getting%20Started"><?php _e("Get free version", "vpc"); ?></a>
                            
                            <a class="button" href="http://demos.orionorigin.com/grand-popo/?utm_source=VPC%20Free&utm_medium=cpc&utm_campaign=Grand-Popo&utm_content=Getting%20Started"><?php _e("Live preview", "vpc"); ?></a>
                        </div>
                    </div>
                </div>

                <div class="pub-plugin">
                    <div class="vpc-addon-section-title-container vpc-addon-section-description">
                        <h2 class="vpc-addon-section-title"><?php _e("Woocommerce All Discounts", "vpc"); ?></h2>
                        <p class="vpc-addon-section-subtitle">
                            <?php _e("Woocommerce All Discounts is a groundbreaking extension that helps you manage bulk
                                or wholesale pricing, customers roles or groups <br>
                                based offers, or....", "vpc");?>
                        </p>

                        <a class="button" href="https://www.orionorigin.com/plugins/woocommerce-all-discounts/?utm_source=VPC%20Free&utm_medium=cpc&utm_campaign=Woocommerce%20All%20Discounts&utm_content=Getting%20Started"><?php _e("From: $32", "vpc"); ?></a>
                    </div>
                </div>
            </div>

            <a href="https://wordpress.org/support/plugin/visual-products-configurator-for-woocommerce/reviews/#new-post">
                <span class="rating">
                    <?php _e("If you like <span>Visual Product Configurator</span> please leave us a <img src='" . VPC_URL . "/admin/images/rating.png'> rating. A huge thanks in advance!", "vpc"); ?>
                </span>
            </a>
        </div>
    <?php
    }
    
    /**
     * Redirects the plugin to the about page after the activation
     */
    function vpc_redirect() {
        if (get_option('vpc_do_activation_redirect', false)) {
            delete_option('vpc_do_activation_redirect');
            if (class_exists('WooCommerce')) 
                wp_redirect(admin_url('edit.php?post_type=vpc-config&page=vpc-getting-started'));
            else
                wp_redirect(admin_url('edit.php?post_type=vpc-config&page=vpc-manage-settings'));
        }
    }
    
    function get_product_columns($defaults) {
        $defaults['configuration'] = __('Configurable', 'vpc');
        return $defaults;
    }

    function get_products_columns_values($column_name, $id) {
        if ($column_name === 'configuration') {
            $is_configurable = vpc_product_is_configurable($id);
            if ($is_configurable)
                _e("Yes", "vpc");
            else
                _e("No", "vpc");
        }
    }

    public function get_max_input_vars_php_ini() {
        $total_max_normal = ini_get('max_input_vars');
        $msg = __("Your max input var is <strong>$total_max_normal</strong> but this page contains <strong>{nb}</strong> fields. You may experience a lost of data after saving. In order to fix this issue, please increase <strong>the max_input_vars</strong> value in your php.ini file.", "vpc");
        ?> 
        <script type="text/javascript">
            var o_max_input_vars = <?php echo $total_max_normal; ?>;
            var o_max_input_msg = "<?php echo $msg; ?>";
        </script>         
        <?php
    }
    
    /**
      *  getting started page
      */


      public function get_vpc_getting_started_page_new() {
        
        ?>
        <h1 class="">
          <?php _e("About Visual Products Configurator", "vpc"); ?>
        </h1>
        <?php
        $active_tab = isset( $_GET[ 'section' ] ) ? $_GET[ 'section' ] : 'vpc-getting-started';
        ?>
        <div class="wrap woocommerce wc_addons_wrap">
          <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-getting-started' ) ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-getting-started' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Browse our extensions', 'vpc' ); ?></a>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-getting-started&section=vpc-tutorials' ) ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-tutorials' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Videos tutorials', 'vpc' ); ?></a>
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpc-config&page=vpc-getting-started&section=vpc-about-orion' ) ); ?>" class="nav-tab <?php echo $active_tab == 'vpc-about-orion' ? 'nav-tab-active' : ''; ?>"><?php _e( 'About us', 'vpc' ); ?></a>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=vpc-config&page=vpc-getting-started&section=vpc-support')); ?>" class="nav-tab <?php echo 'vpc-support' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Support', 'vpc'); ?></a>
          </nav>
          <div class="vpc-getting-started addons-featured">
            <?php
            if( $active_tab == 'vpc-getting-started' ) {
              ?>
					<div class="vpc-addons">
						<div class="vpc-getting-started-title">
							<h3>Add more features to your product configurator to create the sales machine</h3>
						</div>
						<div class="addons-banner-block-items">
							<div class="addons-banner-block-item vpc-addon">
								<div class="addons-banner-block-item-icon">
									<img class="addons-img" src="<?php echo esc_attr(VPC_URL); ?>/admin/images/addons/custom_text.svg" alt="Custom Text addon" />
								</div>
								<div class="addons-banner-block-item-content">
									<h3>Custom Text</h3>
									<p>Allows the customer to add a custom text with a custom color and font to the preview area which will be sent with his order.</p>
									<div class="vpc-addons-buttons">
										<a class="addons-button addons-button-solid" href="https://orionorigin.com/docs/visual-products-configurator/custom-text/" target="_blank">
											Documentation
										</a>
										<a class="addons-button addons-button-solid live-preview" href="https://www.orionorigin.com/product/custom-text-add-on/" target="_blank">
											View all add-ons
										</a>
										<!--<a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-custom-text-add-on/21098606?s_rank=4?ref=orionorigin" target="_blank">
						$25
					</a>-->
									</div>
								</div>
							</div>

							<div class="addons-banner-block-item vpc-addon">
								<div class="addons-banner-block-item-icon">
									<img src="<?php echo esc_attr(VPC_URL); ?>/admin/images/addons/multiple_view.svg" alt="multi views addon" />
								</div>
								<div class="addons-banner-block-item-content">
									<h3>Multiple Views</h3>
									<p>Allow the customer to see his custom product under multiple views and angles, which are configured by the shop manager.</p>

									<div class="vpc-addons-buttons">
										<a class="addons-button addons-button-solid" href="https://orionorigin.com/docs/visual-products-configurator/multiple-views/" target="_blank">
											Documentation
										</a>
										<a class="addons-button addons-button-solid live-preview" href="https://www.orionorigin.com/product/multiple-views-add-on/" target="_blank">
											View all add-ons
										</a>
										<!--<a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-multiple-views-addon/21098558?s_rank=5?ref=orionorigin" target="_blank">
						$28
					</a>-->

									</div>
								</div>
							</div>

							<div class="addons-banner-block-item vpc-addon">
								<div class="addons-banner-block-item-icon">
									<img src="<?php echo esc_attr(VPC_URL); ?>/admin/images/addons/save_for_later.svg" alt="Save for Later addon" />
								</div>
								<div class="addons-banner-block-item-content">
									<h3>Save For Later</h3>
									<p>Gives the users the possibility to save their personalized products for future usage in their account.</p>
									<div class="vpc-addons-buttons">
										<a class="addons-button addons-button-solid" href="https://orionorigin.com/docs/visual-products-configurator/save-for-later/" target="_blank">
											Documentation
										</a>
										<a class="addons-button addons-button-solid live-preview" href="https://www.orionorigin.com/product/save-for-later-add-on/" target="_blank">
											View all add-ons
										</a>
										<!--<a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-save-for-later-addon/21098722?s_rank=1?ref=orionorigin" target="_blank">
						$25
					</a>-->
									</div>
								</div>
							</div>

							<div class="addons-banner-block-item vpc-addon">
								<div class="addons-banner-block-item-icon">
									<img src="<?php echo esc_attr(VPC_URL); ?>/admin/images/addons/request_a_quote.svg" alt="Request a quote addon" />
								</div>
								<div class="addons-banner-block-item-content">
									<h3>Request A Quote</h3>
									<p>Allows the customer to request a quote about a customized product and purchase later if needed.</p>
									<div class="vpc-addons-buttons">
										<a class="addons-button addons-button-solid" href="https://orionorigin.com/docs/visual-products-configurator/request-a-quote/" target="_blank">
											Documentation
										</a>
										<a class="addons-button addons-button-solid live-preview" href="https://www.orionorigin.com/product/request-a-quote-add-on/" target="_blank">
											View all add-ons
										</a>
										<!--<a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-products-configurator-request-a-quote-addon/21098694?s_rank=2?ref=orionorigin" target="_blank">
						$25
					</a>-->
									</div>
								</div>
							</div>

							<div class="addons-banner-block-item vpc-addon">
								<div class="addons-banner-block-item-icon">
									<img src="<?php echo esc_attr(VPC_URL); ?>/admin/images/addons/upload_image.svg" alt="Upload image addon" />
								</div>
								<div class="addons-banner-block-item-content">
									<h3>Custom Image Upload</h3>
									<p>Allows the customer to upload one or multiple pictures on his custom product which will show up on the preview area.</p>

									<div class="vpc-addons-buttons">
										<a class="addons-button addons-button-solid" href="https://orionorigin.com/docs/visual-products-configurator/custom-image-upload/" target="_blank">
											Documentation
										</a>
										<a class="addons-button addons-button-solid live-preview" href="https://www.orionorigin.com/product/custom-upload-add-on/" target="_blank">
											View all add-ons
										</a>
										<!--<a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-upload-image/21098653?s_rank=3?ref=orionorigin" target="_blank">
						$28
					</a>-->
									</div>
								</div>
							</div>
							<div class="addons-banner-block-item vpc-addon">
								<div class="addons-banner-block-item-icon">
									<img src="<?php echo esc_attr(VPC_URL); ?>/admin/images/addons/save_preview.svg" alt="Save preview addon" />
								</div>
								<div class="addons-banner-block-item-content">
									<h3>Save preview</h3>
									<p>Allow your customers to download the flattened image of their designs for use outside the product builder.</p>

									<div class="vpc-addons-buttons">
										<a class="addons-button addons-button-solid" href="https://orionorigin.com/docs/visual-products-configurator/save-preview/" target="_blank">
											Documentation
										</a>
										<a class="addons-button addons-button-solid live-preview" href="https://www.orionorigin.com/product/visual-product-configurator-save-configuration-image-add-on/" target="_blank">
											View all add-ons
										</a>
										<!--<a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-save-preview-addon/21881361?s_rank=1" target="_blank">
						$25
					</a>-->
									</div>
								</div>
							</div>
							<div class="addons-banner-block-item vpc-addon">
								<div class="addons-banner-block-item-icon">
									<img src="<?php echo esc_attr(VPC_URL); ?>/admin/images/addons/form_builder.svg" alt="Form builder addon" />
								</div>
								<div class="addons-banner-block-item-content">
									<h3>Form Builder</h3>
									<p>A form builder designed to work as add-on for ORION extensions only, not as an independant form builder plugin.</p>

									<div class="vpc-addons-buttons">
										<a class="addons-button addons-button-solid" href="https://orionorigin.com/docs/visual-products-configurator/form-builder/" target="_blank">
											Documentation
										</a>
										<a class="addons-button addons-button-solid live-preview" href="https://www.orionorigin.com/product/form-builder-add-on/" target="_blank">
											View all add-ons
										</a>
										<!--<a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-form-builder-addon/21872047?s_rank=1" target="_blank">
						$28
					</a>-->
									</div>
								</div>
							</div>
							<div class="addons-banner-block-item vpc-addon">
								<div class="addons-banner-block-item-icon">
									<img src="<?php echo esc_attr(VPC_URL); ?>/admin/images/addons/social_sharing.svg" alt="Social Share addon" />
								</div>
								<div class="addons-banner-block-item-content">
									<h3>Social Share</h3>
									<p>Allows your customers to share their configured products to facebook; twitter, pinterest, google,whatsapp and by mail.</p>

									<div class="vpc-addons-buttons">
										<a class="addons-button addons-button-solid" href="https://orionorigin.com/docs/visual-products-configurator/social-share/" target="_blank">
											Documentation
										</a>
										<a class="addons-button addons-button-solid live-preview" href="https://www.orionorigin.com/product/social-share-add-on/" target="_blank">
											View all add-ons
										</a>
										<!--<a class="addons-button addons-button-solid" href="https://codecanyon.net/item/visual-product-configurator-social-sharing-addon/22094775?s_rank=1">
						$25
					</a>-->
									</div>
								</div>
							</div>
						</div>
					</div>
					<br><br>

					<div class="vpc-addons">
						<div class="vpc-getting-started-title">
							<h3>Make your configurator more beautiful than ever using new layouts</h3>
						</div>

						<div class="addons-banner-block-items">
							<div class="addons-banner-block-item vpc-addon">
								<div class="addons-banner-block-item-icon">
									<img class="addons-img" src="<?php echo esc_attr(VPC_URL); ?>/admin/images/addons/lom-nava.svg" alt="Lom-nava skin" />
								</div>
								<div class="addons-banner-block-item-content">
									<h3>Lom-Nava Skin</h3>
									<p>A beautiful mutliple steps skin that will instantly enhance the look and feel of your configurator.</p>
									<div class="vpc-addons-buttons">
										<a class="addons-button addons-button-solid" href="https://orionorigin.com/docs/visual-products-configurator/skins/" target="_blank">
											Documentation
										</a>
										<a class="addons-button addons-button-solid live-preview" href="https://www.orionorigin.com/product/lom-nava-skin/" target="_blank">
											View all add-ons
										</a>
										<!--<a class="addons-button addons-button-solid" href="https://codecanyon.net/item/lom-nava-skin-for-visual-product-configurator/21124537?s_rank=3" target="_blank">
						$25
					</a>-->
									</div>
								</div>
							</div>
							<div class="addons-banner-block-item vpc-addon">
								<div class="addons-banner-block-item-icon">
									<img class="addons-img" src="<?php echo esc_attr(VPC_URL); ?>/admin/images/addons/ouando.svg" alt="Ouando skin" />
								</div>
								<div class="addons-banner-block-item-content">
									<h3>Ouando Skin</h3>
									<p>A beautiful slideshows skin that will instantly reveal and complete the look and feel of your configurator.</p>
									<div class="vpc-addons-buttons">
										<a class="addons-button addons-button-solid" href="https://orionorigin.com/docs/visual-products-configurator/skins/" target="_blank">
											Documentation
										</a>
										<a class="addons-button addons-button-solid live-preview" href="https://www.orionorigin.com/product/ouando-skin/" target="_blank">
											View all add-ons
										</a>
										<!--<a class="addons-button addons-button-solid" href="https://codecanyon.net/user/orionorigin/portfolio" target="_blank">
						$28
					</a>-->
									</div>
								</div>
							</div>
							<div class="addons-banner-block-item vpc-addon">
								<div class="addons-banner-block-item-icon">
									<img class="addons-img" src="<?php echo esc_attr(VPC_URL); ?>/admin/images/addons/modern.svg" alt="Modern skin" />
								</div>
								<div class="addons-banner-block-item-content">
									<h3>Modern Skin</h3>
									<p>The new default skin that will instantly reveal and complete the look and feel of your configurator.</p>
									<div class="vpc-addons-buttons">
										<a class="addons-button addons-button-solid" href="https://orionorigin.com/docs/visual-products-configurator/skins/" target="_blank">
											Documentation
										</a>
										<a class="addons-button addons-button-solid live-preview" href="https://www.orionorigin.com/product/modern-skin/" target="_blank">
											View all add-ons
										</a>
										<!--<a class="addons-button addons-button-solid" href="https://codecanyon.net/user/orionorigin/portfolio" target="_blank">
						$28
					</a>-->
									</div>
								</div>
							</div>
						</div>
					</div>
<?php

}

if( $active_tab == 'vpc-tutorials' ) {
  ?>
  <div class="vpc-tutorials">
    <div class="postbox" id="youtube-video-container">
      <div class="videos_youtube">
        <!--<iframe src="https://www.youtube.com/embed/2auCs0EBqjE?list=PLC9GLMXokPgXW3mYmXYJc-QstNGgF173d" frameborder="0" allowfullscreen></iframe>-->
        <iframe width="1440" height="480" src="https://www.youtube.com/embed/kvq9yD2IKX0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>


      </div>
    </div>
  </div>
  <?php
}

if( $active_tab == 'vpc-about-orion' ) {
  ?>
 <div>
						<h3>Our other plugins</h3>
					</div>

					<div class="vpc-about-us pubs">
						<div class="pub-plugin vpc-gs-half vpc-block">
							<div class="vpc-addon-section-title-container vpc-addon-section-description">
								<h2 class="vpc-addon-section-title"><?php esc_html_e('Woocommerce Product Designer', 'vpc'); ?></h2>
								<p class="vpc-addon-section-subtitle">
									<?php esc_html_e('A powerful web to print solution which helps your customers design or customize logos, shirts, business cards and any prints before the order.', 'vpc'); ?>
								</p>

								<a class="button" href="https://designersuiteforwp.com/products/woocommerce-product-designer/" target="_blank"><?php esc_html_e('From: $61', 'vpc'); ?></a>
							</div>
						</div>

						<div class="pub-plugin vpc-gs-half wad-block">
							<div class="vpc-addon-section-title-container vpc-addon-section-description">
								<h2 class="vpc-addon-section-title"><?php esc_html_e('Conditional Discounts for WooCommerce', 'vpc'); ?></h2>
								<p class="vpc-addon-section-subtitle">
									<?php
									esc_html_e(
										'Conditional Discounts for WooCommerce is a groundbreaking extension <br> that helps you manage bulk
						or wholesale pricing, customers roles or groups based offers, or....',
										'vpc'
									);
									?>
								</p>

								<a class="button" href="https://discountsuiteforwp.com/" target="_blank"><?php esc_html_e('From: $60', 'vpc'); ?></a>
							</div>
						</div>

					</div>

					<div class="clearfix"></div>
					<br><br><br>

					<div class="vpc-about-us pubs">
						<div class="pub-plugin vpc-gs-half wpd-block" style="background:url('<?php echo esc_url(VPC_URL); ?>/admin/images/Kandi.png')">
							<div class="vpc-addon-section-title-container vpc-addon-section-description">
								<a class="button" href="https://designersuiteforwp.com/kandi-custom-phone-case-designer/features/" target="_blank"><?php esc_html_e('From: $99', 'vpc'); ?></a>
							</div>
						</div>
						<div class="pub-plugin vpc-gs-half wpd-block" style="background:url('<?php echo esc_url(VPC_URL); ?>/admin/images/Nati.png')">
							<div class="vpc-addon-section-title-container vpc-addon-section-description">
								<a class="button" href="https://designersuiteforwp.com/nati-custom-lettering-designer/features/" target="_blank"><?php esc_html_e('From: $99', 'vpc'); ?></a>
							</div>
						</div>
					</div>

					<div class="clearfix"></div>
					<br><br><br>

					<div class="vpc-about-us pubs">
						<div class="pub-plugin vpc-gs-half wpd-block" style="background:url('<?php echo esc_attr(VPC_URL); ?>/admin/images/Ouidah.png')">
							<div class="vpc-addon-section-title-container vpc-addon-section-description">
								<a class="button" href="https://designersuiteforwp.com/ouidah-woocommerce-product-designer/features/" target="_blank"><?php esc_html_e('From: $61', 'vpc'); ?></a>
							</div>
						</div>
						<div class="pub-plugin vpc-gs-half wpd-block" style="background:url('<?php echo esc_attr(VPC_URL); ?>/admin/images/Seme.png')">
							<div class="vpc-addon-section-title-container vpc-addon-section-description">
								<a class="button" href="https://designersuiteforwp.com/seme-custom-signs-designer/features/" target="_blank"><?php esc_html_e('From: $99', 'vpc'); ?></a>
							</div>
						</div>
					</div>

					<div class="clearfix"></div>
					<br><br><br>

  <?php

}
if ('vpc-support' === $active_tab) {
    ?>
        <div class="vpc-getting-started-title">
            <h4>Can’t seem to solve the issue using our detailed <a href="https://orionorigin.com/docs/visual-products-configurator/">documentation</a> ? Let us help you. : <a href="https://www.orionorigin.com/contact/">here</a>
        </div>
    <?php
    }
    ?>


?>

<!---->
<div class="rating-block">
  <a href="https://wordpress.org/support/plugin/visual-products-configurator-for-woocommerce/reviews/#new-post">
    <span class="rating">
      <?php _e("If you like <span>Visual Product Configurator</span> please leave us a <img src='" . VPC_URL . "/admin/images/rating.png'> rating. A huge thanks in advance!", "vpc"); ?>
    </span>
  </a>
</div>

</div> <!--End first container-->

</div> <!--End global getting-started-page container-->

<?php

}

/**
	 * Woocommerce notice
	 */
	public function require_woocommerce_notice()
	{
		if (!class_exists('WooCommerce')) {
		?>
			<div class="notice vpc-notice-error">
				<p><b>Visual Product Configurator: </b><?php esc_html_e('WooCommerce is not installed on your website. You will not be able to use the features of the plugin.', 'vpc'); ?> <a class="button" href="<?php echo esc_url(admin_url()) . 'plugins.php'; ?>"><?php esc_html_e('Go to plugins page', 'vpc'); ?></a></p>

			</div>
			<?php
			return;
		}
	}
}
