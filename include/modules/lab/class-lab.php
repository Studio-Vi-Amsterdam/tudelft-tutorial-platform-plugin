<?php

namespace TutorialPlatform\Modules\Lab;

defined( 'ABSPATH' ) || die( "Can't access directly" );

class Lab {
    
    /**
     * Custom fields mapping
     * 
     * Key is the custom field name in the database
     * Value is the custom field name in the API response
     */
    const CUSTOM_FIELDS_MAPPING = [
    ];
    
    public function __construct() {
        // Do nothing
    }
}