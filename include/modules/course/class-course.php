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
        "course_code" => "course_code",
        "study" => "study",
        "featured_image" => "featured_image",
        "faculty" => "faculty",
    ];
    
    public function __construct() {
        // Do nothing
    }
}