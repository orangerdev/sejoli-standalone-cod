<?php
namespace Sejoli_Standalone_Cod\Database\JNE;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Class that responsible to database-functions for City data
 * @since   1.0.0
 */
Class Destination extends \Sejoli_Standalone_Cod\Database {
    
    /**
     * Table name
     * @since   1.0.0
     */
    static protected $table = 'sejolisa_shipping_jne_destination';

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
                $table->integer    ('district_id')->nullable();
                $table->string     ('city_name');
                $table->string     ('district_name');
                $table->string     ('code');

            });

        endif;

    }

}
