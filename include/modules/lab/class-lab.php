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
        "description" => "description",
        "useful_links" => "useful_links",
        "useful_links_title" => "useful_links_title",
        "featured_image" => "featured_image",
    ];
    
    public function __construct() {
        // Do nothing
    }
}