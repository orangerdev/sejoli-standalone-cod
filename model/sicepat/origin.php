<?php
namespace Sejoli_Standalone_Cod\Model\SiCepat;

use Sejoli_Standalone_Cod\Model\Main as Eloquent;

class Origin extends Eloquent {

    /**
     * The table associated with the model without prefix.
     *
     * @var string
     */
    protected $table = 'sejolisa_shipping_sicepat_origin';

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
	   'city_id', 'origin_code', 'origin_name'
	];

}
