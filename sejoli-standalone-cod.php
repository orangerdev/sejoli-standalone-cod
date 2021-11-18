<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sejoli.co.id
 * @since             1.0.0
 * @package           Sejoli_Standalone_Cod
 *
 * @wordpress-plugin
 * Plugin Name:       Sejoli Standalone COD
 * Plugin URI:        https://sejoli.co.id
 * Description:       Provides COD Courier Services to Sejoli Premium Membership WordPress Plugin.
 * Version:           1.0.0
 * Author:            Sejoli Team
 * Author URI:        https://sejoli.co.id
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sejoli-standalone-cod
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SEJOLI_STANDALONE_COD_VERSION', '1.0.0' );
define( 'SEJOLI_STANDALONE_COD_DIR', plugin_dir_path( __FILE__ ) );
define( 'SEJOLI_STANDALONE_COD_URL', plugin_dir_url( __FILE__ ) );

if(version_compare(PHP_VERSION, '7.2.1') < 0 && !class_exists( 'WP_CLI' )) :
	add_action('admin_notices', 'sejoli_cod_error_php_message', 1);

	/**
	 * Display error message when PHP version is lower than 7.2.0
	 * Hooked via admin_notices, priority 1
	 * @return 	void
	 */
	function sejoli_cod_error_php_message() {
		?>
		<div class="notice notice-error">
			<h2>SEJOLI TIDAK BISA DIGUNAKAN DI HOSTING ANDA</h2>
			<p>
				Versi PHP anda tidak didukung oleh SEJOLI dan HARUS diupdate. Update versi PHP anda ke versi yang terbaru. <br >
				Minimal versi PHP adalah 7.2.1 dan versi PHP anda adalah <?php echo PHP_VERSION; ?>
			</p>
			<p>
				Jika anda menggunakan cpanel, anda bisa ikuti langkah ini <a href='https://www.rumahweb.com/journal/memilih-versi-php-melalui-cpanel/' target="_blank" class='button'>Update Versi PHP</a>
			</p>
			<p>
				Jika anda masih kesulitan untuk update versi PHP anda, anda bisa meminta bantuan pada CS hosting anda.
			</p>
		</div>
		<?php
	}

else :

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-sejoli-standalone-cod-activator.php
	 */
	function activate_sejoli_standalone_cod() {
		
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-standalone-cod-activator.php';
		Sejoli_Standalone_Cod_Activator::activate();
	
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-sejoli-standalone-cod-deactivator.php
	 */
	function deactivate_sejoli_standalone_cod() {
	
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-standalone-cod-deactivator.php';
		Sejoli_Standalone_Cod_Deactivator::deactivate();
	
	}

	register_activation_hook( __FILE__, 'activate_sejoli_standalone_cod' );
	register_deactivation_hook( __FILE__, 'deactivate_sejoli_standalone_cod' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-standalone-cod.php';

	/**
	 * Require vendor autoload.php
	 * @since 1.0.0
	 */
	require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_sejoli_standalone_cod() {

		$plugin = new Sejoli_Standalone_Cod();
		$plugin->run();

	}
	
	run_sejoli_standalone_cod();

endif;
