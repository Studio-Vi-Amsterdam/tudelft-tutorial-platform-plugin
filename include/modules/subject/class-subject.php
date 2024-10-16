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
        "description" => "description",
        "useful_links" => "useful_links",
        "useful_links_title" => "useful_links_title",
        "category" => "category",
        "secondary_category" => "secondary_category",
        "featured_image" => "featured_image",
    ];
    
    public function __construct() {
        // Do nothing
    }

    /**
     * Get all user subjects
     * 
     * @since 1.0.0
     * 
     * @return mixed
     * 
     */
    public static function get_user_subjects() {

        $subjects = get_posts( [
            'post_type' => 'subject',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'author' => get_current_user_id(),
        ] );

        $response = [];
        foreach ( $subjects as $subject ) {
            $response[] = [
                'id' => $subject->ID,
                'title' => $subject->post_title,
            ];
        }

        return $response;
    }

    /**
     * Get all subjects
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_all_subjects() {

        $subjects = get_posts( [
            'post_type' => 'subject',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ] );

        $response = [];
        foreach ( $subjects as $subject ) {
            $response[] = [
                'id' => $subject->ID,
                'title' => $subject->post_title,
            ];
        }

        return $response;
    }
}