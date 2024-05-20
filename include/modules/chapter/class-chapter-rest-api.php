<?php

namespace TutorialPlatform\Modules\Chapter;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Common\Rest_Api;
use WP_REST_Server;
use WP_REST_Request;

//class Chapter_Rest_Api implements Interface_Rest_Api
class Chapter_Rest_Api {

    /**
     * Register routes
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/chapters', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_chapters' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/chapters/single', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_single_chapter' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/chapters/create', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_chapter' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );
    }

    /**
     * Get chapters related to given id
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_chapters( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $response = Chapter::get_chapters( $id );

        if ( $response['error'] ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        Rest_Api::send_success_response( $response );
    }

    /**
     * Get single chapter for given id
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_single_chapter( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        if ( get_post_type( $id ) !== 'chapter' ) {
            Rest_Api::send_error_response( 'invalid_chapter_id' );
        }

        $chapter = get_post( $id );

        if ( ! $chapter ) {
            Rest_Api::send_error_response( 'no_chapter_found' );
        }

        $chapter = [
            'id' => $chapter->ID,
            'title' => get_the_title( $chapter->ID ),
            'content' => apply_filters( 'the_content', $chapter->post_content ),
            'belongs_to' => get_field( 'belongs_to', $chapter->ID ),
        ];

        Rest_Api::send_success_response( $chapter );
    }
}