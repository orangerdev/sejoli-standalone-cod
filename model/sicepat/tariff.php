<?php
namespace Sejoli_Standalone_Cod\Model\SiCepat;

use Sejoli_Standalone_Cod\Model\Main as Eloquent;

class Tariff extends Eloquent {

    /**
     * The table associated with the model without prefix.
     *
     * @var string
     */
    protected $table = 'sejolisa_shipping_sicepat_tariff';

    /**
     * The label for this class.
     *
     * @var string
     */
    protected $label = 'SICEPAT';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
	   'sicepat_origin_id', 'sicepat_destination_id', 'tariff_data'
	];

    public static function get_available_services() {
      
        return self::$available_services;
    
    }

    /**
     * Set tariff data.
     *
     * @param  string  $value
     * @return void
     */
    public function setTariffDataAttribute( $value ) {
       
        $this->attributes['tariff_data'] = serialize( $value );
    
    }

    /**
     * Get tariff data.
     *
     * @param  string  $value
     * @return string
     */
    public function getTariffDataAttribute( $value ) {
        
        return unserialize( $value );

    }

    /**
     * Get tariff label.
     *
     * @param   $rate array of service information
     * @return  string
     */
    public function getLabel( $rate ) {

        $label = array();

        if( $this->label ) {
            $label[] = $this->label;
        }

        if( $rate->service ) {
            $label[] = $rate->service;
        }

        $label = implode( " - ", $label );

        if( $rate->etd ) {

            $label .= ' (';

                $label .= $rate->etd;

            $label .= ')';
        }

        return $label;

    }

    /**
     * Generate Rate ID for shipping methods.
     *
     * @param   $rate rate object
     * @param   $prefix
     * @return  string
     */
    public function getRateID( $prefix, $rate ) {

        $ids       = array( $prefix , $this->label );
        $code      = $rate->service;
        $separator = "_";

        $lessthan_pattern       = "/</i";
        $lessthan_placeholder   = "lt";
        $biggerthan_pattern     = "/>/i";
        $biggerthan_placeholder = "bt";

        if( preg_match( $biggerthan_pattern, $code ) ) {
            $code = preg_replace( $biggerthan_pattern, $biggerthan_placeholder, $code );
        } elseif( preg_match( $lessthan_pattern, $code ) ) {
            $code = preg_replace( $lessthan_pattern, $lessthan_placeholder, $code );
        }

        $ids[] = $code;

        return mb_strtolower( \implode( $separator, $ids ) );

    }

}
