<?php
add_action( 'rest_api_init', 'sejolisa_add_callback_url_endpoint' );

function sejolisa_add_callback_url_endpoint() {

    register_rest_route(
        'scod-webhook/v1/', // Namespace
        'update-order-callback', // Endpoint
        array(
            'methods'  => 'POST',
            'callback' => 'sejolisa_update_order_callback'
        )
    );

}

function sejolisa_update_order_callback( $request_data ) {

    global $wpdb;

    $data = array();
    
    $parameters = $request_data->get_params();
    
    $order_id        = isset( $parameters['order_id'] ) ? $parameters['order_id'] :'';
    $shipment_number = isset( $parameters['shipment_number'] ) ? $parameters['shipment_number'] :'';
    
    if ( $order_id && $shipment_number ) {
        
        $data['status'] = 'OK';
    
        $data['received_data'] = array(
            'order_id'        => $order_id,
            'shipment_number' => $shipment_number,
        );

        $orderID    = $data['received_data']['order_id'];
        $numberResi = $data['received_data']['shipment_number'];

        $response = sejolisa_update_order_meta_data(
        $orderID,
        [
            'shipping_data' => [
                'resi_number' => $numberResi
            ]
        ]);

        $status = "shipping";
        do_action('sejoli/order/update-status', [
            'ID'     => $orderID,
            'status' => $status
        ]);
        
        $data['message'] = 'You have reached the server';
        
    } else {
        
        $data['status']  = 'Failed';
        $data['message'] = 'Parameters Missing!';
        
    }
    
    return $data;

}