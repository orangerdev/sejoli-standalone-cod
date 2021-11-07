<?php
namespace Sejoli_Standalone_Cod\Front;

use \WeDevs\ORM\Eloquent\Facades\DB;
use Sejoli_Standalone_Cod\Model\JNE\Origin as JNE_Origin;
use Sejoli_Standalone_Cod\Model\JNE\Destination as JNE_Destination;
use Sejoli_Standalone_Cod\Model\JNE\Tariff as JNE_Tariff;
use Sejoli_Standalone_Cod\API\JNE as API_JNE;
use Sejoli_Standalone_Cod\Model\SiCepat\Origin as SICEPAT_Origin;
use Sejoli_Standalone_Cod\Model\SiCepat\Destination as SICEPAT_Destination;
use Sejoli_Standalone_Cod\Model\SiCepat\Tariff as SICEPAT_Tariff;
use Sejoli_Standalone_Cod\API\SiCepat as API_SICEPAT;
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
            'shipmentNumber' => NULL,
            'nonce' 		 => NULL
        ));

        $respond  = [
            'valid'   => false,
            'message' => NULL
        ];

        if( wp_verify_nonce( $params['nonce'], 'sejoli_shipment_tracking_result') ) :

            unset( $params['nonce'] );

            $trace_tracking_jne     = API_JNE::set_params()->get_tracking( $params['shipmentNumber'] );
            $trace_tracking_sicepat = API_SICEPAT::set_params()->get_tracking( $params['shipmentNumber'] );

            if ( ! is_wp_error( $trace_tracking_jne ) || ! is_wp_error( $trace_tracking_sicepat ) ) {

                $respond['valid']  = true;

            } else {

                $respond['message'] = $trace_tracking_jne->get_error_message();
                $respond['message'] = $trace_tracking_sicepat->get_error_message();
            }

        endif;

        if(isset($trace_tracking_jne->history)):

	        $html = '<h6>'.__('Number Resi:', 'sejoli-standalone-cod').'</h6>';
	    	$html .= '<div class="shipping-number" style="font-size:26px;"><b>'.$params['shipmentNumber'].'</b></div>';

		   	$html .= '<h6>'.__('Shipping Details:', 'sejoli-standalone-cod').'</h6>';
		   	$html .= '<table style="text-align: left;">';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Courier:', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<td>JNE - '.$trace_tracking_jne->cnote->cnote_services_code.'</td>';
		   	$html .= '</tr>';
		   	foreach ($trace_tracking_jne->detail as $detail) {
			   	$html .= '<tr>';
			   		$html .= '<th>'.__('From:', 'sejoli-standalone-cod').'</th>';
			   		$html .= '<td>'.$detail->cnote_shipper_name.'</td>';
			   	$html .= '</tr>';
			   	$html .= '<tr>';
			   		$html .= '<th>'.__('Shipper City:', 'sejoli-standalone-cod').'</th>';
			   		$html .= '<td>'.$detail->cnote_shipper_city.'</td>';
			   	$html .= '</tr>';
			   	$html .= '<tr>';
			   		$html .= '<th>'.__('Shipper Address:', 'sejoli-standalone-cod').'</th>';
			   		$html .= '<td>'.$detail->cnote_shipper_addr1.' - '.$detail->cnote_shipper_addr2.'</td>';
			   	$html .= '</tr>';
			   	$html .= '<tr>';
			   		$html .= '<th>'.__('To:', 'sejoli-standalone-cod').'</th>';
			   		$html .= '<td>'.$detail->cnote_receiver_name.'</td>';
			   	$html .= '</tr>';
			   	$html .= '<tr>';
			   		$html .= '<th>'.__('Receiver City:', 'sejoli-standalone-cod').'</th>';
			   		$html .= '<td>'.$detail->cnote_receiver_city.'</td>';
			   	$html .= '</tr>';
			   	$html .= '<tr>';
			   		$html .= '<th>'.__('Receiver Address:', 'sejoli-standalone-cod').'</th>';
			   		$html .= '<td>'.$detail->cnote_receiver_addr1.' - '.$detail->cnote_receiver_addr2.'</td>';
			   	$html .= '</tr>';
		   	}
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Receiver:', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<td>'.$trace_tracking_jne->cnote->cnote_receiver_name.' - ('.$trace_tracking_jne->cnote->keterangan.')</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Last Status:', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<td>'.$trace_tracking_jne->cnote->pod_status.'</td>';
		   	$html .= '</tr>';
		   	$html .= '</table>';

	        $html .= '<h6>'.__('Tracking History:', 'sejoli-standalone-cod').'</h6>';
	   		$html .= '<table style="text-align: left;">';
	   		$html .= '<tr>';
		   		$html .= '<th>'.__('Date', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<th>'.__('Status', 'sejoli-standalone-cod').'</th>';
		   	$html .= '</tr>';	
		   	foreach ($trace_tracking_jne->history as $history) {
				$html .= '<tr>';
			   		$html .= '<td>'.$history->date.'</td>';
			   		$html .= '<td>'.$history->desc.'</td>';
			   	$html .= '</tr>';
		   	}
		   	$html .= '</table>';

		elseif(isset($trace_tracking_sicepat->track_history)):
	        
	        $html = '<h6>'.__('Number Resi:', 'sejoli-standalone-cod').'</h6>';
	    	$html .= '<div class="shipping-number" style="font-size:26px;"><b>'.$params['shipmentNumber'].'</b></div>';

		   	$html .= '<h6>'.__('Shipping Details:', 'sejoli-standalone-cod').'</h6>';
		   	$html .= '<table style="text-align: left;">';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Courier:', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<td>SICEPAT - '.$trace_tracking_sicepat->service.'</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('From:', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<td>'.$trace_tracking_sicepat->sender.'</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Shipper Address:', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<td>'.$trace_tracking_sicepat->sender_address.'</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('To:', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<td>'.$trace_tracking_sicepat->receiver_name.'</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Receiver Address:', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<td>'.$trace_tracking_sicepat->receiver_address.'</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Receiver:', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<td>'.$trace_tracking_sicepat->POD_receiver.'</td>';
		   	$html .= '</tr>';
		   	$html .= '<tr>';
		   		$html .= '<th>'.__('Last Status:', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<td>'.$trace_tracking_sicepat->last_status->status.' - '.$trace_tracking_sicepat->last_status->receiver_name.'</td>';
		   	$html .= '</tr>';
		   	$html .= '</table>';

	        $html .= '<h6>'.__('Tracking History:', 'sejoli-standalone-cod').'</h6>';
	   		$html .= '<table style="text-align: left;">';
	   		$html .= '<tr>';
		   		$html .= '<th>'.__('Date', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<th>'.__('Status', 'sejoli-standalone-cod').'</th>';
		   		$html .= '<th>'.__('Description', 'sejoli-standalone-cod').'</th>';
		   	$html .= '</tr>';	
		   	foreach ($trace_tracking_sicepat->track_history as $history) {
				$html .= '<tr>';
			   		$html .= '<td>'.$history->date_time.'</td>';
			   		$html .= '<td>'.$history->status.'</td>';
			   		$html .= '<td>'.(isset($history->city) ? $history->city : '-').'</td>';
			   	$html .= '</tr>';
		   	}
		   	$html .= '</table>';
		   	
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
	

	