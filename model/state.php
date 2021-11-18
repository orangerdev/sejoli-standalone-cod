<?php
namespace Sejoli_Standalone_Cod\Model;

use Sejoli_Standalone_Cod\Model\Main as Eloquent;

class State extends Eloquent {

    /**
     * The table associated with the model without prefix.
     *
     * @var string
     */
    protected $table = 'sejolisa_shipping_state';

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
	   'name', 'code'
	];

    /**
     * Define relationship with City model
     *
     * @since    1.0.0
     * @return  string
     */
	public function cities() {

		return $this->hasMany( 'Sejoli_Standalone_Cod\Model\City', 'state_id' );
	
    }
    
}
