<?php

namespace TutorialPlatform\Modules\Subject;

defined( 'ABSPATH' ) || die( "Can't access directly" );

class Subject {
    
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