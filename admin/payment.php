<?php
namespace Sejoli_Standalone_Cod\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Payment {

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
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version 	   = $version;

	}

	/**
	 * Register shipment libraries
	 * Hooked via action plugins_loaded, priority 100
	 * @since 	1.2.0
	 * @return 	void
	 */
	public function register_libraries( array $libraries ) {

		require_once( SEJOLI_STANDALONE_COD_DIR . 'payments/cod.php');
		$libraries['cod'] = new \SejoliSA\Payment\SejoliCOD;

		// error_log(print_r("COD", true));
		// error_log(print_r($libraries, true));
		return $libraries;

	}

}
