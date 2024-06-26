<?php

namespace TutorialPlatform\Modules\Software;

defined( 'ABSPATH' ) || die( "Can't access directly" );

class Software {
    
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
    ];
    
    public function __construct() {
        // Do nothing
    }

    /**
     * Get all softwares and their versions
     * 
     * @since 1.0.0
     * 
     * @return mixed
     * 
     */
    public static function get_softwares() {
        $softwares = get_posts( [
            'post_type' => 'software',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ] );

        $response = [];
        foreach ( $softwares as $software ) {
            // for ID get taxonomy software-version
            $software_version = wp_get_post_terms( $software->ID, 'software-version' );
            
            $versions = [];

            foreach ( $software_version as $version ) {
                $versions[] = [
                    'id' => $version->term_id,
                    'title' => $version->name,
                ];
            }

            $response[] = [
                'id' => $software->ID,
                'title' => $software->post_title,
                'version' => $versions,
            ];
        }

        return $response;
    }
    
}