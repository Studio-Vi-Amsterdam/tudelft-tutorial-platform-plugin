<?php

namespace TutorialPlatform\Modules\Tutorial;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Common\Rest_Api;
use TutorialPlatform\Interface\Interface_Rest_Api;
use WP_REST_Server;
use WP_REST_Request;

//class Tutorial_Rest_Api implements Interface_Rest_Api
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
}