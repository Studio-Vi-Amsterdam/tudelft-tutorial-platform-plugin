<?php

namespace TutorialPlatform\Modules\Course;

defined( 'ABSPATH' ) || die( "Can't access directly" );

class Course {
    
    /**
     * Custom fields mapping
     * 
     * Key is the custom field name in the database
     * Value is the custom field name in the API response
     */
    const CUSTOM_FIELDS_MAPPING = [
        "description" => "description",
        "useful_links" => "useful_links",
        "featured_image" => "featured_image",
        "course_code" => "course_code",
        "study" => "study",
        "faculty" => "faculty",
    ];
    
    public function __construct() {
        // Do nothing
    }
}