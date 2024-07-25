<?php

namespace TutorialPlatform\Modules\Tutorial;

defined( 'ABSPATH' ) || die( "Can't access directly" );

class Tutorial {

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
        "primary_software" => "primary_software",
        "software_version" => "software_version",
        "primary_subject" => "primary_subject",
        "secondary_subject" => "secondary_subject",
        "level" => "level",
        "featured_image" => "featured_image",
        "faculty" => "faculty",
        "course" => "course",
    ];

    public function __construct() {
        // Do nothing
    }
}