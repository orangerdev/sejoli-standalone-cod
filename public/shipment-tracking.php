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

			        $html = '<h6>'.__('Number Resi:', 'sejoli-standalone-cod').'</h6>';
			    	$html .= '<div class="shipping-number" style="font-size:26px;"><b>'.$params['shipmentNumber'].'</b></div>';

				   	$html .= '<h6>'.__('Shipping Details:', 'sejoli-standalone-cod').'</h6>';
				   	$html .= '<table style="text-align: left;">';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Courier:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>JNE - '.$trace_tracking_arveoli->cnote->cnote_services_code.'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Total Price:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.sejolisa_price_format( $trace_tracking_arveoli->cnote->cnote_amount ).'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Weight:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->cnote->cnote_weight.' kg</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Send Date:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.date_i18n( 'F d, Y H:i:s', strtotime( $trace_tracking_arveoli->cnote->cnote_date ) ).'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('From:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->detail[0]->cnote_shipper_name.'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Shipper Address:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->detail[0]->cnote_shipper_addr1. ' ' .$trace_tracking_arveoli->detail[0]->cnote_shipper_addr2.'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('To:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->cnote->cnote_receiver_name.'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Receiver Address:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->cnote->city_name.'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Receiver:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->cnote->cnote_pod_receiver.' - '.date_i18n( 'F d, Y H:i:s', strtotime( $trace_tracking_arveoli->cnote->cnote_pod_date ) ).'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Last Status:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->cnote->last_status.'</td>';
				   	$html .= '</tr>';
				   	$html .= '</table>';

			        $html .= '<h6>'.__('Tracking History:', 'sejoli-standalone-cod').'</h6>';
			   		$html .= '<table style="text-align: left;">';
			   		$html .= '<tr>';
				   		$html .= '<th>'.__('Date', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<th colspan="2">'.__('Description', 'sejoli-standalone-cod').'</th>';
				   	$html .= '</tr>';	
				   	foreach ($trace_tracking_arveoli->history as $history) {
						$html .= '<tr>';
					   		$html .= '<td>'.date_i18n( 'F d, Y H:i:s', strtotime( $history->date ) ).'</td>';
					   		$html .= '<td colspan="2">'.$history->desc.'</td>';
					   	$html .= '</tr>';
				   	}
				   	$html .= '</table>';

				elseif( isset( $trace_tracking_arveoli->sicepat ) && $trace_tracking_arveoli->sicepat->status->code === 200 ):

			        $html = '<h6>'.__('Number Resi:', 'sejoli-standalone-cod').'</h6>';
			    	$html .= '<div class="shipping-number" style="font-size:26px;"><b>'.$params['shipmentNumber'].'</b></div>';

				   	$html .= '<h6>'.__('Shipping Details:', 'sejoli-standalone-cod').'</h6>';
				   	$html .= '<table style="text-align: left;">';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Courier:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>SICEPAT - '.$trace_tracking_arveoli->sicepat->result->service.'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Total Price:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.sejolisa_price_format( $trace_tracking_arveoli->sicepat->result->totalprice ).'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Weight:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->sicepat->result->weight.' kg</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Send Date:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.date_i18n( 'F d, Y H:i:s', strtotime( $trace_tracking_arveoli->sicepat->result->send_date ) ).'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('From:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->sicepat->result->sender.'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Shipper Address:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->sicepat->result->sender_address.'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('To:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->sicepat->result->receiver_name.'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Receiver Address:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->sicepat->result->receiver_address.'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Receiver:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->sicepat->result->POD_receiver.' - '.date_i18n( 'F d, Y H:i:s', strtotime( $trace_tracking_arveoli->sicepat->result->POD_receiver_time ) ).'</td>';
				   	$html .= '</tr>';
				   	$html .= '<tr>';
				   		$html .= '<th>'.__('Last Status:', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<td>'.$trace_tracking_arveoli->sicepat->result->last_status->status.' - '.$trace_tracking_arveoli->sicepat->result->last_status->receiver_name.'</td>';
				   	$html .= '</tr>';
				   	$html .= '</table>';

			        $html .= '<h6>'.__('Tracking History:', 'sejoli-standalone-cod').'</h6>';
			   		$html .= '<table style="text-align: left;">';
			   		$html .= '<tr>';
				   		$html .= '<th>'.__('Date', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<th>'.__('Status', 'sejoli-standalone-cod').'</th>';
				   		$html .= '<th>'.__('Description', 'sejoli-standalone-cod').'</th>';
				   	$html .= '</tr>';	
				   	foreach ($trace_tracking_arveoli->sicepat->result->track_history as $history) {
						$html .= '<tr>';
					   		$html .= '<td>'.date_i18n( 'F d, Y H:i:s', strtotime( $history->date_time ) ).'</td>';
					   		$html .= '<td>'.$history->status.'</td>';
					   		$html .= '<td>'.(isset($history->city) ? $history->city : '-').'</td>';
					   	$html .= '</tr>';
				   	}
				   	$html .= '</table>';

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
	

	