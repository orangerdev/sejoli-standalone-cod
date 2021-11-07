<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_Standalone_Cod
 * @subpackage Sejoli_Standalone_Cod/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Sejoli_Standalone_Cod
 * @subpackage Sejoli_Standalone_Cod/includes
 * @author     Sejoli Team <admin@sejoli.co.id>
 */
class Sejoli_Standalone_Cod {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sejoli_Standalone_Cod_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SEJOLI_STANDALONE_COD_VERSION' ) ) {
			$this->version = SEJOLI_STANDALONE_COD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'sejoli-standalone-cod';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_json_hooks();
		$this->define_shipment_hooks();
		$this->define_payment_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sejoli_Standalone_Cod_Loader. Orchestrates the hooks of the plugin.
	 * - Sejoli_Standalone_Cod_i18n. Defines internationalization functionality.
	 * - Sejoli_Standalone_Cod_Admin. Defines all hooks for the admin area.
	 * - Sejoli_Standalone_Cod_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once SEJOLI_STANDALONE_COD_DIR . 'includes/class-sejoli-standalone-cod-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once SEJOLI_STANDALONE_COD_DIR . 'includes/class-sejoli-standalone-cod-i18n.php';

		/**
		 * The class responsible for integrating with database
		 * @var [type]
		 */
		require_once SEJOLI_STANDALONE_COD_DIR . 'includes/class-sejoli-standalone-cod-database.php';

		/**
		 * The class responsible for creating database tables.
		 */
		require_once SEJOLI_STANDALONE_COD_DIR . 'database/main.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'database/indonesia/state.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'database/indonesia/city.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'database/indonesia/district.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'database/jne/origin.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'database/jne/destination.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'database/jne/tariff.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'database/sicepat/origin.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'database/sicepat/destination.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'database/sicepat/tariff.php';

		/**
		 * The class responsible for database seed.
		 */
		require_once SEJOLI_STANDALONE_COD_DIR . 'database/indonesia/seed.php';

		/**
		 * The class responsible for database models.
		 */
		require_once SEJOLI_STANDALONE_COD_DIR . 'model/main.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'model/state.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'model/city.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'model/district.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'model/jne/origin.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'model/jne/destination.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'model/jne/tariff.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'model/sicepat/origin.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'model/sicepat/destination.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'model/sicepat/tariff.php';

		/**
		 * The class responsible for defining API related functions.
		 */
		require_once SEJOLI_STANDALONE_COD_DIR . 'includes/class-sejoli-standalone-cod-api.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'includes/class-sejoli-standalone-cod-order-webhook.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'api/class-sejoli-standalone-cod-jne.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'api/class-sejoli-standalone-cod-sicepat.php';

		/**
		 * The class responsible for defining all actions that work for json functions
		 */
		require_once SEJOLI_STANDALONE_COD_DIR . 'json/main.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'json/order.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once SEJOLI_STANDALONE_COD_DIR . 'admin/class-sejoli-standalone-cod-admin.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'admin/shipment.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'admin/payment.php';

		/**
		 * The class responsible for defining all actions that work for shipment functions
		 */
		require_once SEJOLI_STANDALONE_COD_DIR . 'shipments/cod.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once SEJOLI_STANDALONE_COD_DIR . 'public/class-sejoli-standalone-cod-public.php';
		require_once SEJOLI_STANDALONE_COD_DIR . 'public/shipment-tracking.php';

		$this->loader = new Sejoli_Standalone_Cod_Loader();

		Sejoli_Standalone_Cod\DBIntegration::connection();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sejoli_Standalone_Cod_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Sejoli_Standalone_Cod_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$admin = new Sejoli_Standalone_Cod\Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

		$shipment = new Sejoli_Standalone_Cod\Admin\Shipment( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/admin/js-localize-data',		 	      $shipment, 'set_localize_js_var', 10);
		$this->loader->add_action( 'carbon_fields_theme_options_container_saved', $shipment, 'delete_cache_data', 10);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$public = new Sejoli_Standalone_Cod\Front( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );

		$tracking = new Sejoli_Standalone_Cod\Front\ShipmentTracking( $this->get_plugin_name(), $this->get_version() );
		// Shipment Tracking
		$this->loader->add_action( 'init', $tracking, 'sejoli_init_tracking_shipment_shortcode' );
		$this->loader->add_action( 'wp_ajax_nopriv_sejoli_shipment_tracking_result', $tracking, 'sejoli_shipment_tracking_result' );
        $this->loader->add_action( 'wp_ajax_sejoli_shipment_tracking_result', $tracking, 'sejoli_shipment_tracking_result' );

	}

	/**
	 * Register all of the hooks related to json request
	 *
	 * @since 	 1.0.0
	 * @access 	 private
	 */
	private function define_json_hooks() {

		$order = new Sejoli_Standalone_Cod\JSON\Order();
		$this->loader->add_action( 'wp_ajax_sejoli-order-pickup-generate-resi',	$order, 'generate_pickup_resi', 1);

		// Setting Cron Jobs Update Status Completed based on Shipping Status is Delivered
		$this->loader->add_filter( 'cron_schedules', $order, 'sejoli_update_status_cron_schedules' );
		$this->loader->add_action( 'admin_init', $order, 'schedule_update_order_to_complete_based_on_shipment_status' );
		$this->loader->add_action( 'update_status_order_to_completed', $order, 'update_status_order_to_completed_based_on_shipment_status' );

	}

	/**
	 * Register all of the hooks related to shipment request
	 *
	 * @since 	 1.0.0
	 * @access 	 private
	 */
	private function define_shipment_hooks() {

		$cod = new Sejoli_Standalone_Cod\Shipment\COD();

		$this->loader->add_filter( 'sejoli/shipment/options',  $cod, 'set_shipping_jne_options',        10, 2);
		$this->loader->add_filter( 'sejoli/shipment/options',  $cod, 'set_shipping_sicepat_options',        10, 2);
        $this->loader->add_filter( 'sejoli/product/fields',    $cod, 'set_product_shipping_fields', 36);
        $this->loader->add_action( 'sejoli/product/meta-data', $cod, 'setup_product_cod_meta',      10, 2);

	}

	/**
	 * Register all of the hooks related to payment request
	 *
	 * @since 	 1.0.0
	 * @access 	 private
	 */
	private function define_payment_hooks() {

		$payment = new Sejoli_Standalone_Cod\Admin\Payment( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter('sejoli/payment/available-libraries',	$payment, 'register_libraries', 10);

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since   1.0.0
	 */
	public function run() {

		$this->loader->run();
	
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		
		return $this->plugin_name;
	
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Sejoli_Standalone_Cod_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
	
		return $this->loader;
	
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
	
		return $this->version;
	
	}

}
