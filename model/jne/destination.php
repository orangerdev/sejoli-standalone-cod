<?php
namespace Sejoli_Standalone_Cod\Model\JNE;

use Sejoli_Standalone_Cod\Model\Main as Eloquent;

class Destination extends Eloquent {

    /**
     * The table associated with the model without prefix.
     *
     * @var string
     */
    protected $table = 'sejolisa_shipping_jne_destination';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
	   'city_id', 'district_id', 'city_name', 'district_name', 'code'
	];

    /**
     * Define relationship with City model
     *
     * @since    1.0.0
     * @return  string
     */
    public function city() {
       
        return $this->belongsTo( 'Sejoli_Standalone_Cod\Model\City', 'city_id' );
    
    }

    /**
     * Define relationship with District model
     *
     * @since    1.0.0
     * @return  string
     */
    public function district() {
    
        return $this->belongsTo( 'Sejoli_Standalone_Cod\Model\District', 'district_id' );
    
    }

    /**
     * Get static table name with no prefix
     *
     * @since    1.0.0
     * @return  string
     */
    public function getTableName() {
    
        return $this->table;
    
    }

}
