<?php

namespace TutorialPlatform\Modules\Tutorial;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Common\Rest_Api;
use WP_REST_Server;
use WP_REST_Request;

class Tutorial_Rest_Api {

    /**
     * Register routes
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/tutorials', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_tutorials' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/tutorials/single', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_single_tutorial' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/tutorials/create', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'create_tutorial_data' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/tutorials/create', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_tutorial' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );
    }

    /**
     * Get tutorials from tutor
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_tutorials( WP_REST_Request $request ): mixed {
        
        $current_user = wp_get_current_user();

        $args = [
            'post_type' => 'tutorial',
            'author' => $current_user->ID,
            'posts_per_page' => -1,
        ];

        $tutorials = get_posts( $args );

        if ( ! $tutorials ) {
            Rest_Api::send_error_response( 'no_tutorials_found' );
        }

        $tutorials = array_map( function( $tutorial ) {
            return [
                'id' => $tutorial->ID,
                'title' => $tutorial->post_title,
                'content' => $tutorial->post_content,
                'publish_date' => $tutorial->post_date,
                // TODO: Add last modified date
            ];
        }, $tutorials );

        return $tutorials;
    }

    /**
     * Get single tutorial for given id
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_single_tutorial( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $tutorial = get_post( $id );

        if ( ! $tutorial ) {
            Rest_Api::send_error_response( 'no_tutorial_found' );
        }

        $tutorial = [
            'id' => $tutorial->ID,
            'title' => $tutorial->post_title,
            'content' => $tutorial->post_content,
            'publish_date' => $tutorial->post_date,
        ];
    }

    /**
     * Create tutorial data
     * 
     * Returns the data required to create a tutorial
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function create_tutorial_data( WP_REST_Request $request ): mixed {

        $custom_post_types = [
            'subject',
            'course',
            'software',
        ];

        $data = [];

        $user_id = get_current_user_id();

        foreach ( $custom_post_types as $post_type ) {
            $posts = get_posts( [
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'author' => $user_id,
                'post_status' => 'publish',
                'orderby' => 'title',
                'order' => 'ASC',
            ] );

            // we only need id and title
            $posts = array_map( function( $post ) {
                return [
                    'id' => $post->ID,
                    'title' => $post->post_title,
                ];
            }, $posts );

            $data[ $post_type ] = $posts;
        }

        $software_versions = get_terms( [
            'taxonomy' => 'software-version',
            'hide_empty' => false,
        ] );

        $data['software_versions'] = array_map( function( $version ) {
            return [
                'id' => $version->term_id,
                'title' => $version->name,
            ];
        }, $software_versions );       

        return $data;
    }

    /**
     * Create tutorial
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function create_tutorial( WP_REST_Request $request ): mixed {
        
        /**
         * string $title
         * string $description
         * array $description_blocks
         * array $chapters
         * string $useful_links
         * int $subject_id
         * int $course_id
         * int $software_id
         * int $software_version_id
         * array $keywords
         */
        $data = $request->get_json_params();

        if ( ! $data['title'] ) {
            Rest_Api::send_error_response( 'no_name' );
        }

        if ( ! $data['description'] ) {
            Rest_Api::send_error_response( 'no_content' );
        }

        // create post
        $post_data = [
            'post_title' => $data['title'],
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'tutorial',
        ];

        $post_id = wp_insert_post( $post_data );

        if ( $data['description'] ) {
            update_field( 'description', $data['description'], $post_id );
        }

        if ( $data['useful_links'] ) {
            update_field( 'useful_links', $data['useful_links'], $post_id );
        }

        if ( $data['subject_id'] ) {
            update_field( 'subject', $data['subject_id'], $post_id );
        }

        if ( $data['course_id'] ) {
            update_field( 'course', $data['course_id'], $post_id );
        }

        if ( $data['software_id'] ) {
            update_field( 'software', $data['software_id'], $post_id );
        }

        if ( $data['software_version_id'] ) {
            update_field( 'software_version', $data['software_version_id'], $post_id );
        }
    }
}