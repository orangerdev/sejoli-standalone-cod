<?php
namespace Sejoli_Standalone_Cod\Database\SiCepat;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Class that responsible to database-functions for City data
 * @since   1.0.0
 */
Class Origin extends \Sejoli_Standalone_Cod\Database {
    
    /**
     * Table name
     * @since   1.0.0
     */
    static protected $table = 'sejolisa_shipping_sicepat_origin';

    /**
     * Create table if not exists
     * @return void
     */
    static public function create_table() {

        parent::$table = self::$table;

        if( ! Capsule::schema()->hasTable( self::table() ) ):

            Capsule::schema()->create( self::table(), function( $table ){

                $table->increments ('ID');
                $table->integer    ('city_id')->nullable();
                $table->string     ('origin_code');
                $table->string     ('origin_name');

            });

        endif;

    }

}
