<?php
namespace Sejoli_Standalone_Cod;

Class Database {
    
    static protected $table = NULL;

    /**
     * Table define
     * @return [type] [description]
     */
    static protected function table() {
        
        global $wpdb;

        $prefix = $wpdb->prefix;

        return $prefix.self::$table;
    
    }

}
