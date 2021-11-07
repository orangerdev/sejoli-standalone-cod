<?php

namespace Sejoli_Standalone_Cod\Shipment;

use Carbon_Fields\Container;
use Carbon_Fields\Field;
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

class COD {

    /**
     * Construction
     * @since   1.2.0
     */
    public function __construct() {


    }

    /**
     * Check if district in cities
     * @since   1.2.0
     * @param   int     $district_id    District ID
     * @param   array   $cities         All City IDs
     * @return  boolean
     */
    protected function check_if_subdistrict_in_cities( int $subdistrict_id, array $cities ) {

        $is_in_cities = false;

        ob_start();
		
        require SEJOLI_STANDALONE_COD_DIR . 'json/subdistrict.json';
		$json_data = ob_get_contents();

		ob_end_clean();

		$subdistricts        = json_decode( $json_data, true );
        $key                 = array_search( $subdistrict_id, array_column( $subdistricts, 'subdistrict_id' ) );
        $current_subdistrict = $subdistricts[$key];

        if( in_array( $current_subdistrict['city_id'], $cities ) ) :
            return true;
        endif;

        return $is_in_cities;

    }

    /**
	 * Get city options
	 * @since 	1.2.0
	 * @param  	array  $options 	City options
	 * @return 	array
	 */
	public function get_city_options( $options = array() ) {

		$options = [];

		ob_start();

		require SEJOLI_STANDALONE_COD_DIR . 'json/city.json';
		$json_data = ob_get_contents();
		
        ob_end_clean();

		$subdistricts = json_decode($json_data, true);

		foreach( $subdistricts as $data ):
			$options[$data['city_id']] = $data['province'] . ' - ' . $data['type'].' '.$data['city_name'] ;
		endforeach;

		asort($options);

		return $options;

	}

    /**
     * Add JS Vars for localization
     * Hooked via sejoli/admin/js-localize-data, priority 10
     * @since   1.0.0
     * @param   array   $js_vars    Array of js vars
     * @return  array
     */
    public function set_localize_js_var( array $js_vars ) {

        $js_vars['order']['check_physical'] = add_query_arg([
                                                    'ajaxurl' => add_query_arg([
                                                        'action' => 'sejoli-order-check-if-physical'
                                                    ], admin_url('admin-ajax.php')),
                                                    'nonce' => wp_create_nonce('sejoli-order-check-if-physical')
                                                ]);

        $js_vars['get_subdistricts'] = [
            'ajaxurl' => add_query_arg([
                    'action' => 'get-subdistricts'
                ], admin_url('admin-ajax.php')
            ),
            'placeholder' => __('Ketik minimal 3 karakter', 'sejoli-standalone-cod')
        ];

        return $js_vars;

    }

    /**
     * Get subdistriction options for json
     * Hooked via action wp_ajax_get-subdistricts
     * @since  1.0.0
     * @return json
     */
    public function get_json_subdistrict_options() {

        $response = sejoli_jne_get_district_options( $_REQUEST['term'] );
        wp_send_json( $response );

    }

    /**
     * Get subdistriction options
     * Hooked via filter sejoli/shipment/subdistricts, priority 1
     * @since  1.0.0
     * @return array
     */
    public function get_subdistrict_options( $options = array() ) {

        $options = [];

        ob_start();
        
        require SEJOLI_STANDALONE_COD_DIR . 'json/subdistrict.json';
        $json_data = ob_get_contents();

        ob_end_clean();

        $subdistricts = json_decode( $json_data, true );

        foreach( $subdistricts as $data ):
            $options[$data['subdistrict_id']] = $data['province'] . ' - ' . $data['type'].' '.$data['city'] . ' - ' . $data['subdistrict_name'];
        endforeach;

        asort( $options );

        return $options;

    }

    /**
     * Get subdistrict detail
     * @since   1.0.0
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

        return NULL;

    }

    /**
     * Add COD shipping product fields
     * @since   1.0.0
     * @param   array   $fields   Current product fields
     * @return  array
     */
    public function set_product_shipping_fields( $fields ) {
        
        $fields[]   = array(
            'title'     => __('Cash on Delivery (COD)', 'sejoli-standalone-cod'),
            'fields'    => array(
                Field::make( 'separator', 'sep_cod_services' , __('Cash on Delivery (COD)', 'sejoli-standalone-cod'))
                    ->set_classes('sejoli-with-help')
                    ->set_help_text('<a href="' . sejolisa_get_admin_help('shipping') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

                Field::make('checkbox', 'shipment_cod_services_active', __('Aktifkan COD', 'sejoli-standalone-cod'))
                    ->set_option_value('yes')
                    ->set_default_value(true),

                Field::make('text', 'sejoli_scod_username', __('SCOD Username', 'sejoli-standalone-cod'))
                    ->set_required(true)
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                Field::make('text', 'sejoli_scod_password', __('SCOD Password', 'sejoli-standalone-cod'))
                    ->set_required(true)
                    ->set_attribute('type', 'password')
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                Field::make('text', 'sejoli_scod_store_id', __('SCOD Store ID', 'sejoli-standalone-cod'))
                    ->set_required(true)
                    ->set_attribute('type', 'number')
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                Field::make('text', 'sejoli_scod_secret_key', __('SCOD Secret Key', 'sejoli-standalone-cod'))
                    ->set_required(true)
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                Field::make('separator', 'sep_cod_services_store_setting', __('Pengaturan Toko', 'sejoli-standalone-cod'))->set_conditional_logic(array(
                    array(
                        'field' => 'shipment_cod_services_active',
                        'value' => true
                    )
                )),
                
                Field::make('text', 'sejoli_store_name', __('Nama Toko', 'sejoli-standalone-cod'))
                    ->set_required(true)
                    ->set_default_value(get_bloginfo('name'))
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                Field::make('text', 'sejoli_store_phone', __('No. Telepon Toko', 'sejoli-standalone-cod'))
                    ->set_attribute('type', 'number')
                    ->set_required(true)
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                Field::make('text', 'shipment_cod_weight', __('Berat barang (dalam gram)', 'sejoli-standalone-cod'))
                    ->set_attribute('type', 'number')
                    ->set_attribute('min', 1000)
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                Field::make('select', 'shipment_cod_origin', __('Awal pengiriman', 'sejoli-standalone-cod'))
                    ->set_options(array($this, 'get_subdistrict_options'))
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )))
                    ->set_help_text(__('Ketik nama kecamatan untuk pengiriman', 'sejoli-standalone-cod')),
                
                Field::make('separator', 'sep_cod_services_setting', __('Pengaturan Layanan COD JNE', 'sejoli-standalone-cod'))->set_conditional_logic(array(
                    array(
                        'field' => 'shipment_cod_services_active',
                        'value' => true
                    )
                )),

                Field::make( "multiselect", "shipment_cod_jne_services", __('Layanan JNE', 'sejoli-standalone-cod') )
                    ->add_options( array(
                        'cod_jne_service_reg' => 'REG',
                        'cod_jne_service_oke' => 'OKE',
                        'cod_jne_service_jtr' => 'JTR',
                    ))
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                Field::make('text', 'shipment_cod_jne_markup_label', __('Label Biaya Markup COD JNE', 'sejoli-standalone-cod'))
                    ->set_default_value(__('Biaya COD JNE', 'sejoli-standalone-cod'))
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                Field::make('checkbox', 'shipment_cod_jne_markup_with_ongkir', __('Biaya COD JNE Termasuk ke Ongkir?', 'sejoli-standalone-cod'))
                    ->set_option_value('yes')
                    ->set_default_value(true)
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                Field::make('separator', 'sep_cod_sicepat_setting', __('Pengaturan Layanan COD SiCepat', 'sejoli-standalone-cod'))->set_conditional_logic(array(
                    array(
                        'field' => 'shipment_cod_services_active',
                        'value' => true
                    )
                )),

                Field::make( "multiselect", "shipment_cod_sicepat_services", __('Layanan SiCepat', 'sejoli-standalone-cod') )
                    ->add_options( array(
                        'cod_sicepat_service_cargo' => 'Cargo',
                        'cod_sicepat_service_best' => 'BEST',
                        'cod_sicepat_service_gokil' => 'GOKIL',
                        'cod_sicepat_service_kepo' => 'KEPO',
                        'cod_sicepat_service_halu' => 'Halu',
                        'cod_sicepat_service_reguler' => 'Regular',
                        'cod_sicepat_service_sds' => 'SDS',
                        'cod_sicepat_service_siunt' => 'SI UNTUNG',
                    ))
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                // Field::make('text', 'shipment_cod_sicepat_weight', __('Berat barang (dalam gram)', 'sejoli-standalone-cod'))
                //     ->set_attribute('type', 'number')
                //     ->set_attribute('min', 1000)
                //     ->set_conditional_logic(array(
                //         array(
                //             'field' => 'shipment_cod_services_active',
                //             'value' => true
                //         )
                //     )),

                // Field::make('select', 'shipment_cod_sicepat_origin', __('Awal pengiriman', 'sejoli-standalone-cod'))
                //     ->set_options(array($this, 'get_subdistrict_options'))
                //     ->set_conditional_logic(array(
                //         array(
                //             'field' => 'shipment_cod_services_active',
                //             'value' => true
                //         )))
                //     ->set_help_text(__('Ketik nama kecamatan untuk pengiriman', 'sejoli-standalone-cod')),

                Field::make('text', 'shipment_cod_sicepat_markup_label', __('Label Biaya Markup COD SiCepat', 'sejoli-standalone-cod'))
                    ->set_default_value(__('Biaya COD SiCepat', 'sejoli-standalone-cod'))
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),

                Field::make('checkbox', 'shipment_cod_sicepat_markup_with_ongkir', __('Biaya COD SiCepat Termasuk ke Ongkir?', 'sejoli-standalone-cod'))
                    ->set_option_value('yes')
                    ->set_default_value(true)
                    ->set_conditional_logic(array(
                        array(
                            'field' => 'shipment_cod_services_active',
                            'value' => true
                        )
                    )),
            )
        );

        return $fields;

    }

    /**
     * Generate options for sicepat services dropdown
     *
     * @since 1.0.0
     *
     * @return array
     */
    private function get_jne_services( int $product_id ) {

        $services = array();

        $jne_services = carbon_get_post_meta( $product_id, 'shipment_cod_jne_services' );

        foreach ( $jne_services as $jne_service ) {

            if( $jne_service === 'cod_jne_service_reg' ) {
                $services[] = 'REG19';
            }

            if( $jne_service === 'cod_jne_service_oke' ) {
                $services[] = 'OKE19';
            }

            if( $jne_service === 'cod_jne_service_jtr' ) {
                $codes    = array( 'JTR18', 'JTR250', 'JTR<150', 'JTR>250' );
                $services = array_merge( $services, $codes );
            }

        }

        return $services;

    }

    /**
     * Generate options for sicepat services dropdown
     *
     * @since 1.0.0
     *
     * @return array
     */
    private function get_sicepat_services( int $product_id ) {

        $services = array();

        $sicepat_services = carbon_get_post_meta( $product_id, 'shipment_cod_sicepat_services' );

        foreach ( $sicepat_services as $sicepat_service ) {

            if( $sicepat_service === 'cod_sicepat_service_cargo' ) {
                $services[] = 'Cargo';
            }

            if( $sicepat_service === 'cod_sicepat_service_best' ) {
                $services[] = 'BEST';
            }

            if( $sicepat_service === 'cod_sicepat_service_gokil' ) {
                $services[] = 'GOKIL';
            }

            if( $sicepat_service === 'cod_sicepat_service_kepo' ) {
                $servvices[] = 'KEPO';
            }

            if( $sicepat_service === 'cod_sicepat_service_halu' ) {
                $services[] = 'HALU';
            }

            if( $sicepat_service === 'cod_sicepat_service_reguler' ) {
                $services[] = 'REG';
            }

            if( $sicepat_service === 'cod_sicepat_service_sds' ) {
                $services[] = 'SDS';
            }

            if( $sicepat_service === 'cod_sicepat_service_siunt' ) {
                $services[] = 'SIUNT';
            }

        }

        return $services;

    }

    /**
     * Add shipping data to product meta
     * Hooked via filter sejoli/product/meta-data, priority 100
     * @since   1.0.0
     * @param   WP_Post     $product
     * @param   int         $product_id
     * @return  WP_Post
     */
    public function setup_product_cod_meta( \WP_Post $product, int $product_id ) {

        $product->cod = [
            'cod-active' => boolval( carbon_get_post_meta( $product_id, 'shipment_cod_services_active' ) ),
            'cod-weight' => intval( carbon_get_post_meta( $product_id, 'shipment_cod_weight' ) ),
            'cod-origin' => carbon_get_post_meta( $product_id, 'shipment_cod_origin' ),
        ];

        return $product;

    }

    /**
     * Get tariff jne object
     *
     * @since   1.0.0
     *
     * @param   $origin         origin object to find
     * @param   $destination    destination object to find
     *
     * @return  (Object|false)  returns an object on true, or false if fail
     */
    private function get_tariff_info( $origin, $destination, $weight ) {

        $get_tariff = JNE_Tariff::where( 'jne_origin_id', $origin->ID )
                        ->where( 'jne_destination_id', $destination->ID )
                        ->first();

        if( ! $get_tariff ) {
        
            $req_tariff_data = API_JNE::set_params()->get_tariff( $origin->code, $destination->code, $weight );
            // $req_tariff_data = API_JNE::set_params()->get_tariff( 'CGK10000', 'BDO10000', $weight );
            if( is_wp_error( $req_tariff_data ) ) {
                return false;
            }

            $get_tariff                     = new JNE_Tariff();
            $get_tariff->jne_origin_id      = $origin->ID;
            $get_tariff->jne_destination_id = $destination->ID;
            $get_tariff->tariff_data        = $req_tariff_data;

            if( ! $get_tariff->save() ) {
                return false;
            }
        
        }

        return $get_tariff;

    }

    /**
     * Get tariff sicepat object
     *
     * @since   1.0.0
     *
     * @param   $origin         origin object to find
     * @param   $destination    destination object to find
     *
     * @return  (Object|false)  returns an object on true, or false if fail
     */
    private function get_sicepat_tariff_info( $origin, $destination ) {
        
        $get_tariff = SICEPAT_Tariff::where( 'sicepat_origin_id', $origin->ID )
                        ->where( 'sicepat_destination_id', $destination->ID )
                        ->first();

        if( ! $get_tariff ) {
        
            $req_tariff_data = API_SICEPAT::set_params()->get_tariff( $origin->origin_code, $destination->destination_code );
            
            if( is_wp_error( $req_tariff_data ) ) {
                return false;
            }

            $get_tariff                         = new SICEPAT_Tariff();
            $get_tariff->sicepat_origin_id      = $origin->ID;
            $get_tariff->sicepat_destination_id = $destination->ID;
            $get_tariff->tariff_data            = $req_tariff_data;

            if( ! $get_tariff->save() ) {
                return false;
            }
        
        }

        return $get_tariff;
    
    }

    /**
     * Set JNE COD shipping options
     * @since   1.2.0
     * @param   array $shipping_options     Current shipping options
     * @param   array $post_data            Post data options
     * @return  array
     */
    public function set_shipping_jne_options( $shipping_options, array $post_data ) {

        $product_id    = intval( $post_data['product_id'] );
        $is_cod_active = boolval( carbon_get_post_meta( $product_id, 'shipment_cod_services_active' ) );

        if(false !== $is_cod_active) :

            $cod_origin           = carbon_get_post_meta( $product_id, 'shipment_cod_origin');
            $cod_origin_city      = $this->get_subdistrict_detail( $cod_origin );
            // $get_origin           = JNE_Origin::where( 'name', $cod_origin_city['city'] )->first();

            $getOriginCode = DB::table( 'sejolisa_shipping_jne_origin' )
                    ->where( 'city_id', $cod_origin_city['city_id'] )
                    ->get();        

            if( ! $getOriginCode ) {
                return false;
            }

            $origin = $getOriginCode[0];

            if( ! $origin ) {
                return false;
            }

            $cod_destination_city = $this->get_subdistrict_detail( $post_data['district_id'] );

            $getDestCode = DB::table( 'sejolisa_shipping_jne_destination' )
                    ->where( 'city_id', $cod_destination_city['city_id'] )
                    ->where( 'district_id', $cod_destination_city['subdistrict_id'] )
                    ->get();        

            if( ! $getDestCode ) {
                return false;
            }

            $destination = $getDestCode[0];

            if( ! $destination ) {
                return false;
            }

            // $get_destination      = JNE_Destination::where( 'district_name', $cod_destination_city['subdistrict_name'] )->first(); 
            // $is_cod_locally = boolval( carbon_get_post_meta( $product_id, 'shipment_cod_jne_cover' ) );
            $add_options    = true;
            $fee_title      = '';
            $product        = sejolisa_get_product( $post_data['product_id'] );
            $product_weight = intval( $product->shipping['weight'] );
            $weight_cost    = (int) round( ( intval( $post_data['quantity'] ) * $product_weight ) / 1000 );
            $weight_cost    = ( 0 === $weight_cost ) ? 1 : $weight_cost;
            $tariff         = $this->get_tariff_info( $origin, $destination, $weight_cost );

            // if( true === $is_cod_locally ) :
                
            //     $city_cover  = carbon_get_post_meta( $product_id, 'shipment_cod_jne_city');
            //     $district_id = intval( $post_data['district_id'] );
            //     $add_options = $this->check_if_subdistrict_in_cities( $district_id, $city_cover );
            
            // endif;

            if( true === $add_options ) :

                if( ! $tariff ) {
                    return false;
                }

                if( is_array( $tariff->tariff_data ) && count( $tariff->tariff_data ) > 0 ) {

                    foreach ( $tariff->tariff_data as $rate ) {

                        if( \in_array( $rate->service_code, $this->get_jne_services($product_id) ) ) {
                            
                            $price = $rate->price * $weight_cost;

                            if($rate->service_display === 'OKE'){
                                $cod_title = 'JNE '.$rate->service_display. __(' (Ongkos Kirim Ekonomis)', 'sejoli-standalone-cod');
                                $key_title = 'JNE '.$rate->service_display;
                                $fee_title = ' - ' . sejolisa_price_format($price). ', (COD - estimasi 2-3 Hari)';
                            }
                            elseif($rate->service_display === 'REG'){
                                $cod_title = 'JNE '.$rate->service_display. __(' (Layanan Reguler)', 'sejoli-standalone-cod');
                                $key_title = 'JNE '.$rate->service_display;
                                $fee_title = ' - ' . sejolisa_price_format($price). ', (COD - estimasi 1-2 Hari)';
                            }
                            else{
                                $cod_title = 'JNE '.$rate->service_display. __(' (Layanan Pengiriman Truk)', 'sejoli-standalone-cod');
                                $key_title = 'JNE '.$rate->service_display;
                                $fee_title = ' - ' . sejolisa_price_format($price). ', (COD - estimasi 3-4 Hari)';
                            }
                            
                            $key_options                    = 'COD:::'.$key_title.':::' . sanitize_title($price);
                            $shipping_options[$key_options] = $cod_title . $fee_title;

                        }
                    }
                }

            endif;

        endif;

        return $shipping_options;

    }

    /**
     * Set SiCepat COD shipping options
     * @since   1.2.0
     * @param   array $shipping_options     Current shipping options
     * @param   array $post_data            Post data options
     * @return  array
     */
    public function set_shipping_sicepat_options( $shipping_options, array $post_data ) {

        $product_id    = intval( $post_data['product_id'] );
        $is_cod_active = boolval( carbon_get_post_meta( $product_id, 'shipment_cod_services_active' ) );

        if(false !== $is_cod_active) :

            $cod_origin      = carbon_get_post_meta( $product_id, 'shipment_cod_origin' );
            $cod_origin_city = $this->get_subdistrict_detail( $cod_origin );

            $getOriginCode = DB::table( 'sejolisa_shipping_sicepat_origin' )
                    ->where( 'city_id', $cod_origin_city['city_id'] )
                    ->get();     

            if( ! $getOriginCode ) {
                return false;
            }

            $origin = $getOriginCode[0];

            if( ! $origin ) {
                return false;
            }

            $cod_destination_city = $this->get_subdistrict_detail( $post_data['district_id'] );

            $getDestCode = DB::table( 'sejolisa_shipping_sicepat_destination' )
                    ->where( 'city_id', $cod_destination_city['city_id'] )
                    ->where( 'district_id', $cod_destination_city['subdistrict_id'] )
                    ->get();        

            if( ! $getDestCode ) {
                return false;
            }

            $destination = $getDestCode[0];

            if( ! $destination ) {
                return false;
            }

            // $is_cod_locally = boolval( carbon_get_post_meta( $product_id, 'shipment_cod_jne_cover' ) );
            $add_options    = true;
            $fee_title      = '';
            $product        = sejolisa_get_product( $post_data['product_id'] );
            $product_weight = intval( $product->shipping['weight'] );
            $weight_cost    = (int) round( ( intval( $post_data['quantity'] ) * $product_weight ) / 1000 );
            $weight_cost    = ( 0 === $weight_cost ) ? 1 : $weight_cost;
            $tariff         = $this->get_sicepat_tariff_info( $origin, $destination, $weight_cost );
            // if( true === $is_cod_locally ) :
                
            //     $city_cover  = carbon_get_post_meta( $product_id, 'shipment_cod_jne_city');
            //     $district_id = intval( $post_data['district_id'] );
            //     $add_options = $this->check_if_subdistrict_in_cities( $district_id, $city_cover );
            
            // endif;

            if( true === $add_options ) :

                if( ! $tariff ) {
                    return false;
                }

                if( is_array( $tariff->tariff_data ) && count( $tariff->tariff_data ) > 0 ) {

                    foreach ( $tariff->tariff_data as $rate ) {
                        if( \in_array( $rate->service, $this->get_sicepat_services($product_id) ) ) {
                            error_log(print_r($rate, true));
                            
                            $price = $rate->tariff * $weight_cost;

                            if($rate->service === 'SIUNT'){
                                $cod_title = 'SICEPAT '.$rate->service.' (' .$rate->description.')';
                                $key_title = 'SICEPAT '.$rate->service;
                                $fee_title = ' - ' . sejolisa_price_format($price). ', (COD - estimasi 1-2 Hari)';
                            }
                            elseif($rate->service === 'GOKIL'){
                                $cod_title = 'SICEPAT '.$rate->service.' (' .$rate->description.')';
                                $key_title = 'SICEPAT '.$rate->service;
                                $fee_title = ' - ' . sejolisa_price_format($price). ', (COD - estimasi 2-3 Hari)';
                            }
                            
                            $key_options                    = 'COD:::'.$key_title.':::' . sanitize_title($price);
                            $shipping_options[$key_options] = $cod_title . $fee_title;

                        }
                    }
                }

            endif;

        endif;

        return $shipping_options;

    }

}
