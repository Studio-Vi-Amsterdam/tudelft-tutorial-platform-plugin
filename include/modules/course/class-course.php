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
        "useful_links_title" => "useful_links_title",
        "featured_image" => "featured_image",
        "course_code" => "course_code",
        "study" => "study",
        "secondary_study" => "secondary_study",
        "faculty" => "faculty",
    ];
    
    public function __construct() {
        // Do nothing
    }

    /**
     * Get all Courses
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_all_courses() {

        $courses = get_posts( [
            'post_type' => 'course',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ] );

        $response = [];
        foreach ( $courses as $course ) {
            $response[] = [
                'id' => $course->ID,
                'title' => $course->post_title,
            ];
        }

        return $response;
    }
}