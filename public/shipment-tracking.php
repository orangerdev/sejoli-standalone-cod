<?php
namespace Sejoli_Standalone_Cod\Front;

use \WeDevs\ORM\Eloquent\Facades\DB;
use Sejoli_Standalone_Cod\API\ARVEOLI as API_ARVEOLI;
use Sejoli_Standalone_Cod\Model\JNE\Tariff as JNE_Tariff;
use Sejoli_Standalone_Cod\Model\SiCepat\Tariff as SICEPAT_Tariff;
use Illuminate\Database\Capsule\Manager as Capsule;

class ShipmentTracking {

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
	 * Display Shipment Tracking Form With Shortcode
	 * 
	 * @since    1.0.0
	 */
	public function sejoli_shipment_tracking_shortcode($atts) {
		
	  	require_once( plugin_dir_path( __FILE__ ) . 'partials/tracking-form.php' );

        return $html;
	
	}

	/**
	 * Display Shipment Tracking Result On Shortcode
	 * 
	 * @since    1.0.0
	 */
	public function sejoli_shipment_tracking_result() {

		$params = wp_parse_args( $_POST, array(
            'shipmentNumber'     => NULL,
            'shipmentExpedition' => NULL,
            'nonce' 		     => NULL
        ));

        $respond  = [
            'valid'   => false,
            'message' => NULL
        ];

        if( wp_verify_nonce( $params['nonce'], 'sejoli_shipment_tracking_result') && !empty($params['shipmentExpedition']) && !empty($params['shipmentNumber']) ) :

            unset( $params['nonce'] );
             
            $trace_tracking_arveoli = API_ARVEOLI::set_params()->get_tracking( $params['shipmentExpedition'], $params['shipmentNumber'] );

            if ( ! is_wp_error( $trace_tracking_arveoli ) ) {

                $respond['valid']  = true;

               if( isset( $trace_tracking_arveoli->cnote ) ):

			        require_once( plugin_dir_path( __FILE__ ) . 'partials/sejoli-jne-tracking.php' );

				elseif( isset( $trace_tracking_arveoli->sicepat ) && $trace_tracking_arveoli->sicepat->status->code === 200 ):

			        require_once( plugin_dir_path( __FILE__ ) . 'partials/sejoli-sicepat-tracking.php' );

				else:

					$html = '<p>'.__('No Resi Yang Anda Masukkan Tidak Ditemukan!', 'sejoli-standalone-cod').'</p>';

				endif;

            } else {

                $respond['message'] = $trace_tracking_arveoli->get_error_message();

            }

        else:

			$html = '<p>'.__('Anda Belum Memasukkan No Resi!', 'sejoli-standalone-cod').'</p>';

		endif;

        echo wp_send_json( $html );

    }

    /**
	 * Display Shipment Tracking Form Shortcode
	 * Hook via init
	 * 
	 * @since    1.0.0
	 */
	public function sejoli_init_tracking_shipment_shortcode() {

	    add_shortcode( 'sejoli_shipment_tracking', array( $this , 'sejoli_shipment_tracking_shortcode' ) );
	
	}

}
	

	