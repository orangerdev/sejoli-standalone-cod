<?php

namespace SejoliSA\Payment;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Illuminate\Database\Capsule\Manager as Capsule;

final class SejoliCOD extends \SejoliSA\Payment {

    /**
     * Table name
     * @since 1.0.0
     * @var string
     */
    protected $table = 'sejolisa_cod_transaction_data';

    /**
     * Unique code
     * @since 1.0.0
     * @var float
     */
    protected $markup_price = 0.0;

    /**
     * Order price
     * @since 1.0.0
     * @var float
     */
    protected $order_price = 0.0;

    /**
     * Construction
     * @since   1.2.0
     */
    public function __construct() {

        global $wpdb;

        $this->id          = 'cod';
        $this->name        = __('COD - Cash on Delivery', 'sejoli-standalone-cod');
        $this->title       = __('COD - Cash on Delivery', 'sejoli-standalone-cod');
        $this->description = __('Transaksi via Cash on Delivery.', 'sejoli-standalone-cod');
        $this->table       = $wpdb->prefix . $this->table;

        add_action( 'admin_init', [$this, 'register_transaction_table'], 1 );
        add_filter( 'sejoli/payment/payment-options', [$this, 'add_payment_options'] );

    }

    /**
     * Register transaction table
     * @return void
     */
    public function register_transaction_table() {
        
        global $wpdb;

        if( !Capsule::schema()->hasTable( $this->table ) ):
            Capsule::schema()->create( $this->table, function( $table ){
                $table->increments('ID');
                $table->datetime('created_at');
                $table->datetime('updated_at')->default('0000-00-00 00:00:00');
                $table->integer('order_id');
                $table->integer('user_id')->nullable();
                $table->float('total', 12, 2);
                $table->integer('markup_price');
                $table->text('meta_data');
            });
        endif;

    }

    /**
     * Add payment options if cod transfer active
     * Hooked via filter sejoli/payment/payment-options
     * @since   1.0.0
     * @param   array $options
     * @return  array
     */
    public function add_payment_options($options = array()) {

        $url           = $_SERVER['HTTP_REFERER'];
        $product_id    = url_to_postid( $url );
        $is_cod_active = boolval(carbon_get_post_meta( $product_id, 'shipment_cod_services_active' ));
        $is_markup_cod_jne_active = boolval( carbon_get_post_meta( $product_id, 'shipment_cod_jne_active' ) );
        $is_markup_cod_sicepat_active = boolval( carbon_get_post_meta( $product_id, 'shipment_cod_sicepat_active' ) );
        $product       = sejolisa_get_product( $product_id );

        if( true === $is_cod_active && true === $is_markup_cod_jne_active && $product->type === "physical" || true === $is_cod_active && true === $is_markup_cod_sicepat_active && $product->type === "physical" ) :

            // Listing available payment channels from your payment gateways
            $methods = array(
                'CashOnDelivery'
            );

            $cod_name  = __( 'Cash on Delivery', 'sejoli-standalone-cod' );
            $cod_image = SEJOLI_STANDALONE_COD_URL . 'public/img/cod.png';

            foreach( $methods as $method_id ) :

                // MUST PUT ::: after payment ID
                $key = 'cod:::' . $method_id;

                switch( $method_id ) :

                    case 'CashOnDelivery' :

                        $options[$key] = [
                            'label' => $cod_name,
                            'image' => $cod_image
                        ];

                        break;

                endswitch;

            endforeach;

        endif;

        return $options;

    }

    /**
     * Display payment instruction in notification
     * @param  array    $invoice_data
     * @param  string   $recipient_type
     * @param  string   $media
     * @return string
     */
    public function display_payment_instruction( $invoice_data, $media = 'email' ) {

        if('on-hold' !== $invoice_data['order_data']['status']) :
            return;
        endif;

        $content = sejoli_get_notification_content(
                    'cod',
                    $media,
                    array(
                        'order' => $invoice_data['order_data']
                    )
                );

        return $content;

    }

    /**
     * Display simple payment instruction in notification
     * @param  array    $invoice_data
     * @param  string   $recipient_type
     * @param  string   $media
     * @return string
     */
    public function display_simple_payment_instruction( $invoice_data, $media = 'email' ) {

        if('on-hold' !== $invoice_data['order_data']['status']) :
            return;
        endif;

        $content = __('via Cash on Delivery', 'sejoli-standalone-cod');

        return $content;

    }


    /**
     * Set payment info to order datas
     * @since 1.0.0
     * @param array $order_data
     * @return array
     */
    public function set_payment_info( array $order_data ) {

        $trans_data = [
            'bank'  => __('COD - Cash on Delivery', 'sejoli-standalone-cod')
        ];

        return $trans_data;

    }

}
