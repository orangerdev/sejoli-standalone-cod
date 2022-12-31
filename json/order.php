<?php
namespace Sejoli_Standalone_Cod\JSON;

use \WeDevs\ORM\Eloquent\Facades\DB;
use Sejoli_Standalone_Cod\API\SCOD as API_SCOD;
use Sejoli_Standalone_Cod\API\ARVEOLI as API_ARVEOLI;
use Illuminate\Database\Capsule\Manager as Capsule;

Class Order extends \Sejoli_Standalone_Cod\JSON {

    /**
     * Construction
     */
    public function __construct() {

    }

    /**
     * Set user options
     * @since   1.0.0
     * @return  json
     */
    public function set_for_options() {

    }

    /**
     * Set table data
     * Hooked via action wp_ajax_sejoli-order-table, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function set_for_table() {

    }
     
    /**
     * Get subdistrict detail
     * @since   1.2.0
     * @since   1.5.0       Add conditional to check if subdistrict_id is 0
     * @param   integer     $subdistrict_id     District ID
     * @return  array|null  District detail
     */
    public function get_subdistrict_detail( $subdistrict_id ) {

        if( 0 !== intval( $subdistrict_id ) ) :

            ob_start();

            require SEJOLI_STANDALONE_COD_DIR . 'json/subdistrict.json';
            $json_data = ob_get_contents();
            
            ob_end_clean();

            $subdistricts        = json_decode( $json_data, true );
            $key                 = array_search( $subdistrict_id, array_column( $subdistricts, 'subdistrict_id' ) );
            $current_subdistrict = $subdistricts[$key];

            return $current_subdistrict;

        endif;

        return  NULL;

    }

    /**
     * Get origin detail
     * @since   1.0.0
     * @param   integer     $subdistrict_id     District ID
     * @return  array|null  District detail
     */
    public function get_origin( $expedition, $city ) {

        if( $city && $expedition === 'jne' ) :

            ob_start();

            require SEJOLI_STANDALONE_COD_DIR . 'json/json_origin_JNE.json';
            $json_data = ob_get_contents();
            
            ob_end_clean();

            $origins = json_decode( $json_data, true );
            $current_origin = array();

            foreach( $origins as $data ):
                if( \str_contains( strtolower( $city ), strtolower( $data['originname'] ) ) ) {
                    $current_origin = $data;

                    break;
                } else {
                    $current_origin = null;
                }
            endforeach;

            return $current_origin;

        endif;

        if( $city && $expedition === 'sicepat' ) :

            ob_start();

            require SEJOLI_STANDALONE_COD_DIR . 'json/json_origin_sicepat.json';
            $json_data = ob_get_contents();
            
            ob_end_clean();

            $origins = json_decode( $json_data, true );
            $current_origin = array();

            foreach( $origins as $data ):
                if( \str_contains( strtolower( $city ), strtolower( $data['origin_name'] ) ) ) {
                    $current_origin = $data;

                    break;
                } else {
                    $current_origin = null;
                }
            endforeach;

            return $current_origin;

        endif;

        return NULL;

    }

    /**
     * Get destination detail
     * @since   1.0.0
     * @param   integer     $subdistrict_id     District ID
     * @return  array|null  District detail
     */
    public function get_destination( $expedition, $district ) {

        if( $district && $expedition === 'jne' ) :

            ob_start();

            require SEJOLI_STANDALONE_COD_DIR . 'json/json_dest_jne.json';
            $json_data = ob_get_contents();
            
            ob_end_clean();

            $destinations   = json_decode( $json_data, true );

            foreach( $destinations as $data ):
                if( \str_contains( strtolower( $district ), strtolower( $data['district_name'] ) ) ) {
                    $current_destination = $data;

                    break;
                } else {
                    $current_destination = null;
                }
            endforeach;

            return $current_destination;

        endif;

        if( $district && $expedition === 'sicepat' ) :

            ob_start();

            require SEJOLI_STANDALONE_COD_DIR . 'json/json_dest_sicepat.json';
            $json_data = ob_get_contents();
            
            ob_end_clean();

            $destinations   = json_decode( $json_data, true );

            foreach( $destinations as $data ):
                if( \str_contains( strtolower( $district ), strtolower( $data['subdistrict'] ) ) ) {
                    $current_destination = $data;

                    break;
                } else {
                    $current_destination = null;
                }
            endforeach;

            return $current_destination;

        endif;

        return NULL;

    }

    /**
     * Process Pickup Generate Resi COD JNE
     * Hooked via wp_ajax_sejoli-order-pickup-generate-resi, priority 1
     * @since   1.0.0
     * @return  json
     */
    public function generate_pickup_resi() {

        $gettingID = wp_parse_args( $_POST, array(
            'invoice_number' => NULL
        ));

        $response            = sejolisa_get_order( ['ID' => $gettingID['invoice_number']] );
        $order               = $response['orders'];
        $checkCourierService = $order['meta_data']['shipping_data']['service'];
        $product             = sejolisa_get_product( $order['product_id'] );

        if( $product->type === "physical" ) :

            $params = wp_parse_args( $_POST, array(
                'invoice_number'      => NULL,
                'orderID'             => NULL,
                'orderDate'           => NULL,
                'expedition'          => NULL,
                'shipperName'         => NULL,
                'shipperPhone'        => NULL,
                'shipperAddress'      => NULL,
                'shipperCity'         => NULL,
                'shipperProvince'     => NULL,
                'shipperDistrict'     => NULL,
                'shipperZip'          => NULL,
                'receiverName'        => NULL,
                'receiverPhone'       => NULL,
                'receiverAddress'     => NULL,
                'receiverEmail'       => NULL,
                'receiverCity'        => NULL,
                'receiverZip'         => NULL,
                'receiverProvince'    => NULL,
                'receiverDistrict'    => NULL,
                'receiverSubdistrict' => NULL,
                'origin'              => NULL,
                'destination'         => NULL,
                'branch'              => NULL,
                'service'             => NULL,
                'weight'              => NULL,
                'qty'                 => NULL,
                'description'         => NULL,
                'category'            => NULL,
                'packageAmount'       => NULL,
                'insurance'           => NULL,
                'note'                => NULL,
                'codflag'             => NULL,
                'codAmount'           => NULL,
                'shippingPrice'       => NULL,
                'nonce'               => NULL
            ));

            if( false !== $response['valid'] ) :
                $product_id      = $order['product_id'];
                $user_id         = $order['user_id'];
                $payment_gateway = $order['payment_gateway'];
                $qty             = $order['quantity'];
                $weight          = $order['product']->cod['cod-weight'];
                $weight_cost     = (int) round((intval($qty) * $weight) / 1000);
                $weight_cost     = (0 === $weight_cost) ? 1 : $weight_cost;
                $type_product    = $order['type'];
                $shipping_name   = $order['meta_data']['shipping_data']['service'];

                if (strpos( $checkCourierService, 'JNE' ) !== false) :
                   
                    if($shipping_name === "JNE REG") {
                        $shipping_service = "REG";
                    } elseif($shipping_name === "JNE OKE") {
                        $shipping_service = "OKE";
                    } elseif($shipping_name === "JNE YES") {
                        $shipping_service = "YES";
                    } else {
                        $shipping_service = "JTR";
                    }
                    
                    $receiver_destination_id   = $order['meta_data']['shipping_data']['district_id'];
                    $receiver_destination_city = $this->get_subdistrict_detail($receiver_destination_id);
                    $getDestCode               = $this->get_destination( $expedition = 'jne', $receiver_destination_city['subdistrict_name'] );           

                    if( ! $getDestCode ) {
                        return false;
                    }

                    $receiver_destination = $getDestCode['tariff_code'];

                    if( ! $receiver_destination ) {
                        return false;
                    }
                    
                    $shipper_origin_id   = $order['product']->cod['cod-origin'];
                    $shipper_origin_city = $this->get_subdistrict_detail($shipper_origin_id);
                    $getOriginCode       = $this->get_origin( $expedition = 'jne', $shipper_origin_city['city'] );    

                    if( ! $getOriginCode ) {
                        return false;
                    }

                    $shipper_origin = $getOriginCode['origincode'];

                    if( ! $shipper_origin ) {
                        return false;
                    }

                    $expedition = "jne";

                    $branch = $getOriginCode['branchcode'];

                elseif (strpos( $checkCourierService, 'SICEPAT' ) !== false) :

                    if($shipping_name === "SICEPAT GOKIL") {
                        $shipping_service = "GOKIL";
                    } elseif($shipping_name === "SICEPAT SIUNT") {
                        $shipping_service = "SIUNT";
                    } elseif($shipping_name === "SICEPAT BEST") {
                        $shipping_service = "BEST";
                    } elseif($shipping_name === "SICEPAT CARGO") {
                        $shipping_service = "CARGO";
                    } elseif($shipping_name === "SICEPAT KEPO") {
                        $shipping_service = "KEPO";
                    } elseif($shipping_name === "SICEPAT HALU") {
                        $shipping_service = "HALU";
                    } elseif($shipping_name === "SICEPAT SDS") {
                        $shipping_service = "SDS";
                    } else {
                        $shipping_service = "REGULAR";
                    }

                    $receiver_destination_id   = $order['meta_data']['shipping_data']['district_id'];
                    $receiver_destination_city = $this->get_subdistrict_detail($receiver_destination_id);
                    $getDestCode               = $this->get_destination( $expedition = 'sicepat', $receiver_destination_city['subdistrict_name'] );     

                    if( ! $getDestCode ) {
                        return false;
                    }

                    $receiver_destination = $getDestCode['destination_code'];

                    if( ! $receiver_destination ) {
                        return false;
                    }

                    $shipper_origin_id   = $order['product']->cod['cod-origin'];
                    $shipper_origin_city = $this->get_subdistrict_detail($shipper_origin_id);
                    $getOriginCode       = $this->get_origin( $expedition = 'sicepat', $shipper_origin_city['city'] );    

                    if( ! $getOriginCode ) {
                        return false;
                    }

                    $shipper_origin = $getOriginCode['origin_code'];

                    if( ! $shipper_origin ) {
                        return false;
                    }

                    $expedition = "sicepat";

                    $branch = $getOriginCode['origin_code'];

                endif;

                $receiver_name       = $order['meta_data']['shipping_data']['receiver'];
                $receiver_address    = $order['meta_data']['shipping_data']['address'];
                $receiver_zip        = $order['meta_data']['shipping_data']['postal_code'];
                $receiver_email      = '';
                $receiver_phone      = $order['meta_data']['shipping_data']['phone'];
                $shipping_cost       = $order['meta_data']['shipping_data']['cost'];
                $getGapMarkup        = ($order['grand_total'] - $shipping_cost) * 0.04;
                $markup_price        = $order['meta_data']['markup_price'];
                if($markup_price > 0){
                    $cod_fee       = $markup_price;
                    $shipping_fee  = $shipping_cost;
                    $packageAmount = ($order['grand_total'] - $order['meta_data']['shipping_data']['cost']) - $markup_price;
                } else {
                    $cod_fee       = $getGapMarkup;
                    $shipping_fee  = $shipping_cost - $getGapMarkup;
                    $packageAmount = ($order['grand_total'] - $order['meta_data']['shipping_data']['cost']);
                }
                $product_name        = $order['product']->post_title;
                $product_price       = $order['product']->price;
                $product_type        = $order['product']->type;
                
                $shipper_name    = carbon_get_post_meta($product_id, 'sejoli_store_name');
                $shipper_address = $order['meta_data']['shipping_data']['address'];
                $shipper_district  = $shipper_origin_city['subdistrict_name'];
                $shipper_city      = $shipper_origin_city['type'].' '.$shipper_origin_city['city'];
                $shipper_province  = $shipper_origin_city['province'];
                $shipper_zip     = carbon_get_post_meta($product_id, 'sejoli_store_postal_code');
                $shipper_phone   = carbon_get_post_meta($product_id, 'sejoli_store_phone');
                $insurance       = "0";
                if($order['payment_gateway'] === 'cod') {
                    $codflag = "1";
                } else {
                    $codflag = "0";
                }
                $note            = 'Mohon Segera Diproses...';

                $params['orderID']             = $params['invoice_number'];
                $params['orderDate']           = $order['created_at'];
                $params['shipperName']         = $shipper_name;
                $params['expedition']          = $expedition;
                $params['shipperPhone']        = $shipper_phone;
                $params['shipperAddress']      = $shipper_address;
                $params['shipperCity']         = $shipper_city;
                $params['shipperProvince']     = $shipper_province;
                $params['shipperDistrict']     = $shipper_district;
                $params['shipperZip']          = $shipper_zip;
                $params['receiverName']        = $receiver_name;
                $params['receiverPhone']       = $receiver_phone;
                $params['receiverAddress']     = $receiver_address;
                $params['receiverEmail']       = $receiver_email; //
                $params['receiverCity']        = $receiver_destination_city['city'];
                $params['receiverZip']         = $receiver_zip;
                $params['receiverProvince']    = $receiver_destination_city['province'];
                $params['receiverDistrict']    = $receiver_destination_city['subdistrict_name'];
                $params['receiverSubdistrict'] = $receiver_destination_city['subdistrict_name'];
                if (strpos( $checkCourierService, 'JNE' ) !== false) {
                    $params['origin']          = $shipper_origin;
                    $params['destination']     = $receiver_destination;
                } elseif (strpos( $checkCourierService, 'SICEPAT' ) !== false) {
                    $params['origin']          = $shipper_origin;
                    $params['destination']     = $receiver_destination;
                }
                $params['branch']              = $branch;
                $params['service']             = $shipping_service;
                $params['weight']              = $weight_cost;
                $params['qty']                 = $qty;
                $params['category']            = $product_name;
                $params['description']         = $product_name;
                $params['packageAmount']       = $packageAmount;
                $params['insurance']           = $insurance;
                $params['note']                = $note;
                $params['codflag']             = $codflag;
                if($order['payment_gateway'] === 'cod') {
                    $params['codAmount']       = $order['grand_total'];
                } else {
                    $params['codAmount']       = 0;
                }
                $params['shippingPrice']       = $shipping_fee;
     
            endif;

            $respond = [
                'valid'   => false,
                'message' => NULL
            ];

            if( wp_verify_nonce( $params['nonce'], 'sejoli-order-pickup-generate-resi') ) :

                unset( $params['nonce'] );

                $do_update = API_ARVEOLI::set_params()->get_airwaybill( $params );

                if ( ! is_wp_error( $do_update ) ) {

                    $respond['valid']  = true;
                    $number_resi = $do_update->no_resi;

                    $status = "on-the-way";

                    $order_params = array(
                        'invoice_number'  => $params['orderID'],
                        'shipping_status' => $status,
                        'shipping_number' => $number_resi
                    );

                    // Send data to API
                    $api_scod     = new API_SCOD();
                    $update_order = $api_scod->post_update_order( $order_params );
                    
                    if( ! is_wp_error( $update_order ) ) {
                        // Flag the action as done (to avoid repetitions on reload for example)
                        // $order->update_meta_data( '_sync_order_action_scod_done', true );
                        error_log( 'Sync order success ..' );
                    } else {
                        error_log( 'Sync order error .. ' );
                    }

                    echo wp_send_json( $number_resi );

                } else {

                    $respond['message'] = $do_update->get_error_message();
                }

            endif;     

        endif;

    }

    /**
     * Create Updating Status Order to Completed Based on Shipment Status is Delivered Cron Job
     *
     * @since 1.0.0
     */
    public function sejoli_update_status_cron_schedules( $schedules ) {

        $schedules['order_status_to_completed'] = array(
            'interval' => 300, 
            'display'  => 'Update Status Order to Completed Once every 5 minutes'
        );

        return $schedules;

    }

    /**
     * Set Schedule Event for Updating Status Order to Complete Based on Shipping Status is Delivered Cron Job
     *
     * @since 1.0.0
     */
    public function schedule_update_order_to_complete_based_on_shipment_status() {

        // Schedule an action if it's not already scheduled
        if ( ! wp_next_scheduled( 'update_status_order_to_completed' ) ) {
            wp_schedule_event( time(), 'order_status_to_completed', 'update_status_order_to_completed' );
        }

    }

    /**
     * Create Updating Status Order to Complete Based on Shipping Status is Delivered Functiona
     *
     * @since    1.0.0
     */
    public function update_status_order_to_completed_based_on_shipment_status() {

        global $wpdb;
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}sejolisa_orders WHERE status = 'shipping'");
        
        if( $results > 0 ) {

            // Loop through each order post object
            foreach( $results as $result ){

                $order_id               = $result->ID; // The Order ID
                $meta_data              = unserialize($result->meta_data);
                $shipping_number        = (isset($meta_data['shipping_data']['resi_number'])) ? $meta_data['shipping_data']['resi_number'] : '';
                
                $trace_tracking_arveoli_jne = API_ARVEOLI::set_params()->get_tracking( 'jne', $shipping_number );

                $trace_tracking_arveoli_sicepat = API_ARVEOLI::set_params()->get_tracking( 'sicepat', $shipping_number );

                $tracking_pod_status_jne = ( isset($trace_tracking_arveoli_jne->cnote->pod_status) ? $trace_tracking_arveoli_jne->cnote->pod_status : false );
                if( false !== $tracking_pod_status_jne ) :
                    if( $tracking_pod_status_jne === "DELIVERED" ){
                        // Process updating order status
                        $status = "completed";

                        $order_params = array(
                            'invoice_number'  => $order_id,
                            'shipping_status' => $status,
                            'shipping_number' => $shipping_number
                        );

                        // Send data to API
                        $api_scod     = new API_SCOD();
                        $update_order = $api_scod->post_update_order( $order_params );
                        
                        if( ! is_wp_error( $update_order ) ) {
                            // Flag the action as done (to avoid repetitions on reload for example)
                            // $order->update_meta_data( '_sync_order_action_scod_done', true );
                            if( $order->save() ) {
                                error_log( 'Sync order success ..' );
                            }
                        } else {
                            error_log( 'Sync order error .. ' );
                        }
                        
                        do_action('sejoli/order/update-status', [
                            'ID'     => $order_id,
                            'status' => $status
                        ]);
                    }
                endif;

                $tracking_pod_status_sicepat = ( isset($trace_tracking_arveoli_sicepat->sicepat->result->last_status->status) ? $trace_tracking_arveoli_sicepat->sicepat->result->last_status->status : false );
                if(false !== $tracking_pod_status_sicepat) :
                    if( $tracking_pod_status_sicepat === "DELIVERED" ){
                        // Process updating order status
                        $status = "completed";

                        $order_params = array(
                            'invoice_number'  => $order_id,
                            'shipping_status' => $status,
                            'shipping_number' => $shipping_number
                        );

                        // Send data to API
                        $api_scod     = new API_SCOD();
                        $update_order = $api_scod->post_update_order( $order_params );
                        
                        if( ! is_wp_error( $update_order ) ) {
                            // Flag the action as done (to avoid repetitions on reload for example)
                            // $order->update_meta_data( '_sync_order_action_scod_done', true );
                            if( $order->save() ) {
                                error_log( 'Sync order success ..' );
                            }
                        } else {
                            error_log( 'Sync order error .. ' );
                        }
                        
                        do_action('sejoli/order/update-status', [
                            'ID'     => $order_id,
                            'status' => $status
                        ]);
                    }
                endif;
            }

        }

    }

    /**
     * WooCommerce action to send newly created order data to API.
     * Hook action via sejoli/thank-you/render
     *
     * @since    1.0.0
     */
    public function send_order_data_to_api( array $order ) {
        
        if ( ! $order ) return;

        $is_cod_active = boolval( carbon_get_post_meta( $order['product_id'], 'shipment_cod_services_active' ) );
        $product       = sejolisa_get_product( $order['product_id'] );

        if( false !== $is_cod_active && $product->type === "physical" ) :

            $order_id                  = $order['ID'];
            $product_id                = intval( $order['product_id'] );
            $user_id                   = $order['user_id'];
            $payment_gateway           = $order['payment_gateway'];
            $qty                       = $order['quantity'];
            $weight                    = $order['product']->cod['cod-weight'];
            $weight_cost               = (int) round((intval($qty) * $weight) / 1000);
            $weight_cost               = (0 === $weight_cost) ? 1 : $weight_cost;
            $type_product              = $order['type'];
            $shipping_name             = $order['meta_data']['shipping_data']['service'];
            $receiver_destination_id   = $order['meta_data']['shipping_data']['district_id'];
            $receiver_destination_city = $this->get_subdistrict_detail($receiver_destination_id);
            $shipper_origin_id         = $order['product']->cod['cod-origin'];
            $shipper_origin_city       = $this->get_subdistrict_detail($shipper_origin_id);
            
            if( \str_contains( strtolower( $shipping_name ), 'jne' ) ):

                $courier_name = 'jne';
                if($shipping_name === "JNE REG") {
                    $shipping_service = "REG";
                } elseif($shipping_name === "JNE OKE") {
                    $shipping_service = "OKE";
                } elseif($shipping_name === "JNE YES") {
                    $shipping_service = "YES";
                } else {
                    $shipping_service = "JTR";
                }  

                $getDestCode = $this->get_destination( $expedition = 'jne', $receiver_destination_city['subdistrict_name'] );    

                if( ! $getDestCode ) {
                    return false;
                }

                $receiver_destination = $getDestCode['tariff_code'];

                if( ! $receiver_destination ) {
                    return false;
                }

                $getOriginCode = $this->get_origin( $expedition = 'jne', $shipper_origin_city['city'] );

                if( ! $getOriginCode ) {
                    return false;
                }

                $shipper_origin = $getOriginCode['origincode'];   

                if( ! $shipper_origin ) {
                    return false;
                }

                $expedition = 'jne';

                $branch = $getOriginCode['branchcode'];

            endif;

            if( \str_contains( strtolower( $shipping_name ), 'sicepat' ) ):

                $courier_name = 'SiCepat';
                if($shipping_name === "SICEPAT GOKIL") {
                    $shipping_service = "GOKIL";
                } elseif($shipping_name === "SICEPAT SIUNT") {
                    $shipping_service = "SIUNT";
                } elseif($shipping_name === "SICEPAT BEST") {
                    $shipping_service = "BEST";
                } elseif($shipping_name === "SICEPAT CARGO") {
                    $shipping_service = "CARGO";
                } elseif($shipping_name === "SICEPAT KEPO") {
                    $shipping_service = "KEPO";
                } elseif($shipping_name === "SICEPAT HALU") {
                    $shipping_service = "HALU";
                } elseif($shipping_name === "SICEPAT SDS") {
                    $shipping_service = "SDS";
                } else {
                    $shipping_service = "REGULAR";
                }

                $getOriginCode = $this->get_origin( $expedition = 'sicepat', $shipper_origin_city['city'] );

                if( ! $getOriginCode ) {
                    return false;
                }

                $shipper_origin = $getOriginCode['origin_code'];   

                if( ! $shipper_origin ) {
                    return false;
                }

                $getDestCode = $this->get_destination( $expedition = 'sicepat', $receiver_destination_city['subdistrict_name'] );     

                if( ! $getDestCode ) {
                    return false;
                }

                $receiver_destination = $getDestCode['destination_code'];

                if( ! $receiver_destination ) {
                    return false;
                }

                $expedition = 'sicepat';

                $branch = $getOriginCode['origin_code'];

            endif;

            $receiver_name     = $order['meta_data']['shipping_data']['receiver'];
            $receiver_address  = $order['meta_data']['shipping_data']['address'];
            $receiver_district = $receiver_destination_city['subdistrict_name'];
            $receiver_city     = $receiver_destination_city['type'].' '.$receiver_destination_city['city'];
            $receiver_province = $receiver_destination_city['province'];
            $receiver_zip      = $order['meta_data']['shipping_data']['postal_code'];
            $receiver_phone    = $order['meta_data']['shipping_data']['phone'];
            $receiver_email    = '';
            $shipping_cost     = $order['meta_data']['shipping_data']['cost'];
            $getGapMarkup      = ($order['grand_total'] - $shipping_cost) * 0.04;
            $markup_price      = $order['meta_data']['markup_price'];
            if($order['payment_gateway'] === 'cod') {
                $codflag   = "1";
                $codamount = $order['grand_total'];
                if($markup_price > 0){
                    $cod_fee       = $markup_price;
                    $shipping_fee  = $shipping_cost;
                    $packageAmount = ($order['grand_total'] - $order['meta_data']['shipping_data']['cost']) - $markup_price;
                } else {
                    $cod_fee       = $getGapMarkup;
                    $shipping_fee  = $shipping_cost - $getGapMarkup;
                    $packageAmount = ($order['grand_total'] - $order['meta_data']['shipping_data']['cost']);
                }
            } else {
                $codflag   = "0";
                $codamount = 0;
                if($markup_price > 0){
                    $cod_fee       = 0;
                    $shipping_fee  = $shipping_cost;
                    $packageAmount = ($order['grand_total'] - $order['meta_data']['shipping_data']['cost']) - $markup_price;
                } else {
                    $cod_fee       = 0;
                    $shipping_fee  = $shipping_cost;
                    $packageAmount = ($order['grand_total'] - $order['meta_data']['shipping_data']['cost']);
                }
            }
            $product_name      = $order['product']->post_title;
            $product_price     = $order['product']->price;
            $product_type      = $order['product']->type;
            $shipper_name      = carbon_get_post_meta($product_id, 'sejoli_store_name');
            $shipper_address   = $order['meta_data']['shipping_data']['address'];
            $shipper_district  = $shipper_origin_city['subdistrict_name'];
            $shipper_city      = $shipper_origin_city['type'].' '.$shipper_origin_city['city'];
            $shipper_province  = $shipper_origin_city['province'];
            $shipper_zip       = carbon_get_post_meta($product_id, 'sejoli_store_postal_code');
            $shipper_phone     = carbon_get_post_meta($product_id, 'sejoli_store_phone');
            $shipper_email     = carbon_get_theme_option('notification_email_from_address');
            $store_id          = carbon_get_post_meta($product_id, 'sejoli_scod_store_id');
            $store_secret_key  = carbon_get_post_meta($product_id, 'sejoli_scod_secret_key');
            $buyer_name        = $order['user']->data->display_name;
            $buyer_email       = $order['user']->data->user_email;
            $buyer_phone       = $order['user']->data->meta->phone;
            $insurance         = "0";

            // Default params            
            $order_params = array(
                'store_id'             => $store_id,
                'secret_key'           => $store_secret_key,
                'buyer_name'           => $buyer_name,
                'buyer_email'          => $buyer_email,
                'buyer_phone'          => $buyer_phone,
                'courier_name'         => $courier_name,
                'invoice_number'       => $order_id,
                'shipper_name'         => $shipper_name,
                'shipper_address'      => $shipper_address,
                'shipper_province'     => $shipper_province,
                'shipper_city'         => $shipper_city,
                'shipper_district'     => $shipper_district,
                'shipper_subdistrict'  => $shipper_district,
                'shipper_zip'          => $shipper_zip,
                'shipper_phone'        => $shipper_phone,
                'shipper_email'        => $shipper_email,
                'receiver_name'        => $receiver_name,
                'receiver_address'     => $receiver_address,
                'receiver_province'    => $receiver_province,
                'receiver_city'        => $receiver_destination_city['city'],
                'receiver_district'    => $receiver_district,
                'receiver_subdistrict' => $receiver_district,
                'receiver_zip'         => $receiver_zip,
                'receiver_phone'       => $receiver_phone,
                'receiver_email'       => $receiver_email,
                'qty'                  => $qty,
                'weight'               => $weight_cost,
                'goods_desc'           => $product_name,
                'goods_category'       => $product_name,
                'goods_value'          => $packageAmount,
                'goods_type'           => '1',
                'insurance'            => $insurance,
                'expedition'           => $expedition,
                'origin'               => $shipper_origin,
                'destination'          => $receiver_destination,
                'branch'               => $branch,
                'service'              => $shipping_service,
                'codflag'              => $codflag,
                'codamount'            => $codamount,
                'invoice_total'        => $order['grand_total'],
                'shipping_fee'         => $shipping_fee,
                'cod_fee'              => $cod_fee,
                'shipping_status'      => 'pending',
                'notes'                => '',
                'order'                => $order
            );


            // Send data to API
            $api_scod     = new API_SCOD();
            $create_order = $api_scod->post_create_order( $order_params );

            if( ! is_wp_error( $create_order ) ) {
                // Flag the action as done (to avoid repetitions on reload for example)
                error_log( 'Sync order success ..' );
            } else {
                error_log( 'Sync order error .. ' );
            }
            
            error_log( 'Done processing order ID '. $order_id );
        
        else:
        
            return false;

        endif;

    }

}