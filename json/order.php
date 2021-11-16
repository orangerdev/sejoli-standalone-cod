<?php
namespace Sejoli_Standalone_Cod\JSON;

use \WeDevs\ORM\Eloquent\Facades\DB;
use Sejoli_Standalone_Cod\API\SCOD as API_SCOD;
use Sejoli_Standalone_Cod\API\JNE as API_JNE;
use Sejoli_Standalone_Cod\API\SiCepat as API_SICEPAT;
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
        $data                = $response['orders'];
        $checkCourierService = $data['meta_data']['shipping_data']['service'];
        $product             = sejolisa_get_product( $data['product_id'] );

        if( $product->type === "physical" ) :

            if (strpos( $checkCourierService, 'JNE' ) !== false) :

                $params = wp_parse_args( $_POST, array(
                    'invoice_number' => NULL,
                    'shipperName'    => NULL,
                    'shipperAddr1'   => NULL,
                    'shipperAddr2'   => NULL,
                    'shipperCity'    => NULL,
                    'shipperRegion'  => NULL,
                    'shipperZip'     => NULL,
                    'shipperPhone'   => NULL,
                    'receiverName'   => NULL,
                    'receiverAddr1'  => NULL,
                    'receiverAddr2'  => NULL,
                    'receiverCity'   => NULL,
                    'receiverRegion' => NULL,
                    'receiverZip'    => NULL,
                    'receiverPhone'  => NULL,
                    'qty'            => NULL,
                    'weight'         => NULL,
                    'goodsDesc'      => NULL,
                    'goodsValue'     => 1000,
                    'goodsType'      => 1,
                    'insurance'      => "N",
                    'origin'         => NULL,
                    'destination'    => NULL,
                    'service'        => NULL,
                    'codflag'        => "YES",
                    'codAmount'      => NULL,
                    'nonce'          => NULL
                ));

                if( false !== $response['valid'] ) :
                    $product_id      = $data['product_id'];
                    $user_id         = $data['user_id'];
                    $payment_gateway = $data['payment_gateway'];
                    $qty             = $data['quantity'];
                    $weight          = $data['product']->cod['cod-weight'];
                    $weight_cost     = (int) round((intval($qty) * $weight) / 1000);
                    $weight_cost     = (0 === $weight_cost) ? 1 : $weight_cost;
                    $type_product    = $data['type'];
                    $shipping_name   = $data['meta_data']['shipping_data']['service'];
                    if($shipping_name === "JNE REG") {
                        $shipping_service = "REG";
                    } elseif($shipping_name === "JNE OKE") {
                        $shipping_service = "OKE";
                    } else {
                        $shipping_service = "JTR";
                    }
                    $receiver_destination_id   = $data['meta_data']['shipping_data']['district_id'];
                    $receiver_destination_city = $this->get_subdistrict_detail($receiver_destination_id);
                    $getDestCode = DB::table( 'sejolisa_shipping_jne_destination' )
                            ->where( 'city_id', $receiver_destination_city['city_id'] )
                            ->where( 'district_id', $receiver_destination_city['subdistrict_id'] )
                            ->get();        

                    if( ! $getDestCode ) {
                        return false;
                    }

                    $receiver_destination = $getDestCode[0];

                    if( ! $receiver_destination ) {
                        return false;
                    }
                    // $receiver_destination      = JNE_Destination::where( 'city_id', $receiver_destination_city['city_id'] )->first();
                    $receiver_name       = $data['meta_data']['shipping_data']['receiver'];
                    $receiver_address    = $data['meta_data']['shipping_data']['address'];
                    $receiver_zip        = '0000';
                    $receiver_phone      = $data['meta_data']['shipping_data']['phone'];
                    $shipping_cost       = $data['meta_data']['shipping_data']['cost'];
                    $product_name        = $data['product']->post_title;
                    $product_price       = $data['product']->price;
                    $product_type        = $data['product']->type;
                    $shipper_origin_id   = $data['product']->cod['cod-origin'];
                    $shipper_origin_city = $this->get_subdistrict_detail($shipper_origin_id);
                    $getOriginCode = DB::table( 'sejolisa_shipping_jne_origin' )
                            ->where( 'city_id', $shipper_origin_city['city_id'] )
                            ->get();        

                    if( ! $getOriginCode ) {
                        return false;
                    }

                    $shipper_origin = $getOriginCode[0];

                    if( ! $shipper_origin ) {
                        return false;
                    }
                    // $shipper_origin            = JNE_Origin::where( 'city_id', $shipper_origin_city['city_id'] )->first(); 
                    $shipper_name             = carbon_get_post_meta($product_id, 'sejoli_store_name');
                    $shipper_address          = $data['meta_data']['shipping_data']['address'];
                    $shipper_zip              = '0000';
                    $shipper_phone            = carbon_get_post_meta($product_id, 'sejoli_store_phone');
                    $params['shipperName']    = $shipper_name;
                    $params['shipperAddr1']   = $shipper_address;
                    $params['shipperAddr2']   = $shipper_origin_city['subdistrict_name'];
                    $params['shipperCity']    = $shipper_origin_city['type'].' '.$shipper_origin_city['city'];
                    $params['shipperRegion']  = $shipper_origin_city['province'];
                    $params['shipperZip']     = $shipper_zip;
                    $params['shipperPhone']   = $shipper_phone;
                    $params['receiverName']   = $receiver_name;
                    $params['receiverAddr1']  = $receiver_address;
                    $params['receiverAddr2']  = $receiver_address;
                    $params['receiverCity']   = $receiver_destination_city['type'].' '.$receiver_destination_city['city'];
                    $params['receiverRegion'] = $receiver_destination_city['province'];
                    $params['receiverZip']    = $receiver_zip;
                    $params['receiverPhone']  = $receiver_phone;
                    $params['qty']            = $qty;
                    $params['weight']         = $weight_cost;
                    $params['goodsDesc']      = $product_name;
                    $params['origin']         = $shipper_origin->code;
                    $params['destination']    = $receiver_destination->code;
                    $params['service']        = $shipping_service;
                    $params['codAmount']      = $shipping_cost;
         
                endif;

                $respond = [
                    'valid'   => false,
                    'message' => NULL
                ];

                if( wp_verify_nonce( $params['nonce'], 'sejoli-order-pickup-generate-resi') ) :

                    unset( $params['nonce'] );

                    $do_update = API_JNE::set_params()->get_airwaybill( $params['invoice_number'], $params['shipperName'], $params['shipperAddr1'], $params['shipperAddr2'], $params['shipperCity'], $params['shipperRegion'], $params['shipperZip'], $params['shipperPhone'], $params['receiverName'], $params['receiverAddr1'], $params['receiverAddr2'], $params['receiverCity'], $params['receiverRegion'], $params['receiverZip'], $params['receiverPhone'], $params['qty'], $params['weight'], $params['goodsDesc'], $params['goodsValue'], $params['goodsType'], $params['insurance'], $params['origin'], $params['destination'], $params['service'], $params['codflag'], $params['codAmount'] );

                    if ( ! is_wp_error( $do_update ) ) {

                        $respond['valid']  = true;
                        $number_resi = $do_update[0]->cnote_no;

                    } else {

                        $respond['message'] = $do_update->get_error_message();
                    }

                endif;
                
                echo wp_send_json( $number_resi );

            elseif (strpos( $checkCourierService, 'SICEPAT' ) !== false) :

                $params = wp_parse_args( $_POST, array(
                    'invoice_number'        => NULL,
                    'pickup_merchant_name'  => NULL,
                    'pickup_address'        => NULL,
                    'pickup_city'           => NULL,
                    'pickup_merchant_phone' => NULL,
                    'pickup_merchant_email' => NULL,
                    'origin_code'           => NULL,
                    'delivery_type'         => NULL,
                    'parcel_category'       => NULL,
                    'parcel_content'        => NULL,
                    'parcel_qty'            => NULL,
                    'parcel_value'          => NULL,
                    'cod_value'             => NULL,
                    'total_weight'          => NULL,
                    'shipper_name'          => NULL,
                    'shipper_address'       => NULL,
                    'shipper_province'      => NULL,
                    'shipper_city'          => NULL,
                    'shipper_district'      => NULL,
                    'shipper_zip'           => NULL,
                    'shipper_phone'         => NULL,
                    'recipient_name'        => NULL,
                    'recipient_address'     => NULL,
                    'recipient_province'    => NULL,
                    'recipient_city'        => NULL,
                    'recipient_district'    => NULL,
                    'recipient_zip'         => NULL,
                    'recipient_phone'       => NULL,
                    'destination_code'      => NULL,
                    'nonce'                 => NULL
                ));

                if( false !== $response['valid'] ) :
                    $product_id      = $data['product_id'];
                    $user_id         = $data['user_id'];
                    $payment_gateway = $data['payment_gateway'];
                    $qty             = $data['quantity'];
                    $weight          = $data['product']->cod['cod-weight'];
                    $weight_cost     = (int) round((intval($qty) * $weight) / 1000);
                    $weight_cost     = (0 === $weight_cost) ? 1 : $weight_cost;
                    $type_product    = $data['type'];
                    $shipping_name   = $data['meta_data']['shipping_data']['service'];

                    if($shipping_name === "SICEPAT GOKIL") {
                        $shipping_service = "GOKIL";
                    } elseif($shipping_name === "SICEPAT SIUNT") {
                        $shipping_service = "SIUNT";
                    }

                    $receiver_destination_id   = $data['meta_data']['shipping_data']['district_id'];
                    $receiver_destination_city = $this->get_subdistrict_detail($receiver_destination_id);
                    $getDestCode = DB::table( 'sejolisa_shipping_sicepat_destination' )
                            ->where( 'city_id', $receiver_destination_city['city_id'] )
                            ->where( 'district_id', $receiver_destination_city['subdistrict_id'] )
                            ->get();        

                    if( ! $getDestCode ) {
                        return false;
                    }

                    $receiver_destination = $getDestCode[0];

                    if( ! $receiver_destination ) {
                        return false;
                    }
                    
                    $receiver_name             = $data['meta_data']['shipping_data']['receiver'];
                    $receiver_address          = $data['meta_data']['shipping_data']['address'];
                    $receiver_zip              = '0000';
                    $receiver_phone            = $data['meta_data']['shipping_data']['phone'];
                    $shipping_cost             = $data['meta_data']['shipping_data']['cost'];
                    $product_name              = $data['product']->post_title;
                    $product_price             = $data['product']->price;
                    $product_type              = $data['product']->type;
                    $shipper_origin_id         = $data['product']->cod['cod-origin'];
                    $shipper_origin_city       = $this->get_subdistrict_detail($shipper_origin_id);
                    $getOriginCode = DB::table( 'sejolisa_shipping_sicepat_origin' )
                            ->where( 'city_id', $shipper_origin_city['city_id'] )
                            ->get();        

                    if( ! $getOriginCode ) {
                        return false;
                    }

                    $shipper_origin = $getOriginCode[0];

                    if( ! $shipper_origin ) {
                        return false;
                    }
            
                    $shipper_name                    = carbon_get_post_meta($product_id, 'sejoli_store_name');
                    $shipper_address                 = $data['meta_data']['shipping_data']['address'];
                    $shipper_zip                     = '0000';
                    $shipper_phone                   = carbon_get_post_meta($product_id, 'sejoli_store_phone');
                    $params['orderID']               = $params['invoice_number'];
                    $params['pickup_merchant_name']  = $shipper_name;
                    $params['pickup_merchant_phone'] = $shipper_phone;
                    $params['pickup_merchant_email'] = carbon_get_theme_option('notification_email_from_address');
                    $params['pickup_address']        = $shipper_origin_city['subdistrict_name'];
                    $params['pickup_city']           = $shipper_origin_city['type'].' '.$shipper_origin_city['city'];
                    $params['shipper_name']          = $shipper_name;
                    $params['shipper_address']       = $shipper_address;
                    $params['shipper_district']      = $shipper_origin_city['subdistrict_name'];
                    $params['shipper_city']          = $shipper_origin_city['type'].' '.$shipper_origin_city['city'];
                    $params['shipper_province']      = $shipper_origin_city['province'];
                    $params['shipper_zip']           = $shipper_zip;
                    $params['shipper_phone']         = $shipper_phone;
                    $params['recipient_name']        = $receiver_name;
                    $params['recipient_address']     = $receiver_address;
                    $params['recipient_district']    = $receiver_address;
                    $params['recipient_city']        = $receiver_destination_city['type'].' '.$receiver_destination_city['city'];
                    $params['recipient_province']    = $receiver_destination_city['province'];
                    $params['recipient_zip']         = $receiver_zip;
                    $params['recipient_phone']       = $receiver_phone;
                    $params['parcel_qty']            = $qty;
                    $params['total_weight']          = $weight_cost;
                    $params['parcel_category']       = $product_name;
                    $params['parcel_content']        = $product_name;
                    $params['origin_code']           = $shipper_origin->origin_code;
                    $params['destination_code']      = $receiver_destination->destination_code;
                    $params['delivery_type']         = $shipping_service;
                    $params['parcel_value']          = $shipping_cost;
         
                endif;

                $respond = [
                    'valid'   => false,
                    'message' => NULL
                ];

                if( wp_verify_nonce( $params['nonce'], 'sejoli-order-pickup-generate-resi') ) :

                    unset( $params['nonce'] );

                    $do_update = API_SICEPAT::set_params()->get_airwaybill( $params );

                    if ( ! is_wp_error( $do_update ) ) {

                        $respond['valid'] = true;
                        $number_resi      = $do_update->request_number;

                    } else {

                        $respond['message'] = $do_update->get_error_message();
                    }

                endif;
                
                echo wp_send_json( $number_resi );

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
                $shipping_number        = $meta_data['shipping_data']['resi_number'];
                $trace_tracking_jne     = API_JNE::set_params()->get_tracking( $shipping_number );
                $trace_tracking_sicepat = API_SICEPAT::set_params()->get_tracking( $shipping_number );

                $tracking_pod_status_jne = ( isset($trace_tracking_jne->cnote->pod_status) ? $trace_tracking_jne->cnote->pod_status : false );
                if( false !== $tracking_pod_status_jne ) :
                    if( $tracking_pod_status_jne === "DELIVERED" ){
                        // Process updating order status
                        $status = "completed";
                        do_action('sejoli/order/update-status', [
                            'ID'     => $order_id,
                            'status' => $status
                        ]);

                        // Send update status data to API
                        $status       = "completed";
                        $api_scod     = new API_SCOD();
                        $update_order = $api_scod->post_update_order( $order_id, $status, $shipping_number );

                        if( ! is_wp_error( $update_order ) ) {
                            // Flag the action as done (to avoid repetitions on reload for example)
                            if( $order->save() ) {
                                error_log( 'Sync order success ..' );
                            }
                        }

                        $order->update_status( 'completed', 'order_note' );
                    }
                endif;

                $tracking_pod_status_sicepat = ( isset($trace_tracking_sicepat->last_status->status) ? $trace_tracking_sicepat->last_status->status : false );
                if(false !== $tracking_pod_status_sicepat) :
                    if( $tracking_pod_status_sicepat === "DELIVERED" ){
                        // Send update status data to API
                        $status       = "completed";
                        $api_scod     = new API_SCOD();
                        $update_order = $api_scod->post_update_order( $order_id, $status, $shipping_number );

                        if( ! is_wp_error( $update_order ) ) {
                            // Flag the action as done (to avoid repetitions on reload for example)
                            if( $order->save() ) {
                                error_log( 'Sync order success ..' );
                            }
                        }

                        $order->update_status( 'completed', 'order_note' );
                        
                        // Process updating order status
                        $status = "completed";
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
                } else {
                    $shipping_service = "JTR";
                }

                $getDestCode = DB::table( 'sejolisa_shipping_jne_destination' )
                        ->where( 'city_id', $receiver_destination_city['city_id'] )
                        ->where( 'district_id', $receiver_destination_city['subdistrict_id'] )
                        ->get();        

                if( ! $getDestCode ) {
                    return false;
                }

                $receiver_destination = $getDestCode[0];

                if( ! $receiver_destination ) {
                    return false;
                }

                $getOriginCode = DB::table( 'sejolisa_shipping_jne_origin' )
                        ->where( 'city_id', $shipper_origin_city['city_id'] )
                        ->get();        

                if( ! $getOriginCode ) {
                    return false;
                }

                $shipper_origin = $getOriginCode[0];

                if( ! $shipper_origin ) {
                    return false;
                }

            endif;

            if( \str_contains( strtolower( $shipping_name ), 'sicepat' ) ):

                $courier_name = 'SiCepat';
                if($shipping_name === "SICEPAT GOKIL") {
                    $shipping_service = "GOKIL";
                } elseif($shipping_name === "SICEPAT SIUNT") {
                    $shipping_service = "SIUNT";
                }

                $getOriginCode = DB::table( 'sejolisa_shipping_sicepat_origin' )
                        ->where( 'city_id', $shipper_origin_city['city_id'] )
                        ->get();        

                if( ! $getOriginCode ) {
                    return false;
                }

                $shipper_origin = $getOriginCode[0];

                if( ! $shipper_origin ) {
                    return false;
                }

                $getDestCode = DB::table( 'sejolisa_shipping_sicepat_destination' )
                        ->where( 'city_id', $receiver_destination_city['city_id'] )
                        ->where( 'district_id', $receiver_destination_city['subdistrict_id'] )
                        ->get();        

                if( ! $getDestCode ) {
                    return false;
                }

                $receiver_destination = $getDestCode[0];

                if( ! $receiver_destination ) {
                    return false;
                }

            endif;

            $receiver_name     = $order['meta_data']['shipping_data']['receiver'];
            $receiver_address  = $order['meta_data']['shipping_data']['address'];
            $receiver_district = $receiver_destination_city['subdistrict_name'];
            $receiver_city     = $receiver_destination_city['type'].' '.$receiver_destination_city['city'];
            $receiver_region   = $receiver_destination_city['province'];
            $receiver_zip      = '0000';
            $receiver_phone    = $order['meta_data']['shipping_data']['phone'];
            $shipping_cost     = $order['meta_data']['shipping_data']['cost'];
            $product_name      = $order['product']->post_title;
            $product_price     = $order['product']->price;
            $product_type      = $order['product']->type;
            $shipper_name      = carbon_get_post_meta($product_id, 'sejoli_store_name');
            $shipper_address   = $order['meta_data']['shipping_data']['address'];
            $shipper_district  = $shipper_origin_city['subdistrict_name'];
            $shipper_city      = $shipper_origin_city['type'].' '.$shipper_origin_city['city'];
            $shipper_region    = $shipper_origin_city['province'];
            $shipper_zip       = '0000';
            $shipper_phone     = carbon_get_post_meta($product_id, 'sejoli_store_phone');
            $shipper_email     = carbon_get_theme_option('notification_email_from_address');
            $store_id          = carbon_get_post_meta($product_id, 'sejoli_scod_store_id');
            $store_secret_key  = carbon_get_post_meta($product_id, 'sejoli_scod_secret_key');
            $buyer_name        = $order['user']->data->display_name;
            $buyer_email       = $order['user']->data->user_email;
            $buyer_phone       = $order['user']->data->meta->phone;
            $insurance         = "N";
            $codflag           = "YES";

            // Default params
            $order_params = array(
                'store_id'        => $store_id,
                'secret_key'      => $store_secret_key,
                'buyer_name'      => $buyer_name,
                'buyer_email'     => $buyer_email,
                'buyer_phone'     => $buyer_phone,
                'courier_name'    => $courier_name,
                'invoice_number'  => $order_id,
                'shipper_name'    => $shipper_name,
                'shipper_addr1'   => $shipper_address,
                'shipper_addr2'   => $shipper_district,
                'shipper_city'    => $shipper_city,
                'shipper_region'  => $shipper_region,
                'shipper_zip'     => $shipper_zip,
                'shipper_phone'   => $shipper_phone,
                'shipper_email'   => $shipper_email,
                'receiver_name'   => $receiver_name,
                'receiver_addr1'  => $receiver_address,
                'receiver_addr2'  => $receiver_district,
                'receiver_city'   => $receiver_city,
                'receiver_region' => $receiver_region,
                'receiver_zip'    => $receiver_zip,
                'receiver_phone'  => $receiver_phone,
                'qty'             => $qty,
                'weight'          => $weight_cost,
                'goods_desc'      => $product_name,
                'goods_category'  => $product_name,
                'goods_value'     => $qty,
                'goods_type'      => '1',
                'insurance'       => $insurance,
                'origin'          => $shipper_origin,
                'destination'     => $receiver_destination,
                'service'         => $shipping_service,
                'codflag'         => $codflag,
                'codamount'       => $order['grand_total'],
                'invoice_total'   => $order['grand_total'],
                'shipping_fee'    => $shipping_cost,
                'shipping_status' => 'pending',
                'notes'           => '',
                'order'           => $order
            );

            // Send data to API
            $api_scod     = new API_SCOD();
            $create_order = $api_scod->post_create_order( $order_params );

            if( ! is_wp_error( $create_order ) ) {
                // Flag the action as done (to avoid repetitions on reload for example)
                if( $order->save() ) {
                    error_log( 'Sync order success ..' );
                }
            } else {
                error_log( 'Sync order error .. ' );
            }
            
            error_log( 'Done processing order ID '. $order_id );
        
        else:
        
            return false;

        endif;

    }

}