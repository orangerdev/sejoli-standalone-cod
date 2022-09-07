<?php

/**
 * Fired during plugin activation
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_Standalone_Cod
 * @subpackage Sejoli_Standalone_Cod/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sejoli_Standalone_Cod
 * @subpackage Sejoli_Standalone_Cod/includes
 * @author     Sejoli Team <admin@sejoli.co.id>
 */
class Sejoli_Standalone_Cod_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		Sejoli_Standalone_Cod\Database\State::create_table();
		Sejoli_Standalone_Cod\Database\City::create_table();
		Sejoli_Standalone_Cod\Database\District::create_table();

		Sejoli_Standalone_Cod\Database\JNE\Tariff::create_table();
		Sejoli_Standalone_Cod\Database\SiCepat\Tariff::create_table();
		
	}

}
