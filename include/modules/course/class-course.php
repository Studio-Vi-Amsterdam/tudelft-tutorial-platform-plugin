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

    const STUDIES = [
        "bachelor_1" => "Bachelor 1",
        "bachelor_2" => "Bachelor 2",
        "bachelor_3" => "Bachelor 3",
        "msc_architecture" => "MSc Architecture",
        "msc_building_technology" => "MSc Building Technology",
        "msc_urbanism" => "MSc Urbanism",
        "msc_landscape_architecture" => "MSc Landscape Architecture",
        "msc_management_in_the_built_environment" => "MSc Management in the Built Environment",
        "msc_geomatics_gima" => "MSc Geomatics (GIMA)",
    ];
    
    public function __construct() {
        // Do nothing
    }
}