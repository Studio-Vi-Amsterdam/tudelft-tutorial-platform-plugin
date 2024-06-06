<?php

namespace TutorialPlatform\Modules\Media;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Common\Rest_Api;
use WP_REST_Server;
use WP_REST_Request;
use TutorialPlatform\Common\Gutenberg;
use TuDelft\SurfShareKit\Inc\SurfShareKit;

//class Media_Rest_Api implements Interface_Rest_Api
class Media_Rest_Api {

    /**
     * Register routes
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/media', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_media' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        // total amount of media
        register_rest_route( Rest_Api::API_NAMESPACE, '/media/total', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_total_media' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        // upload media item
        register_rest_route( Rest_Api::API_NAMESPACE, '/media/upload', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'upload_media' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        // search media items
        register_rest_route( Rest_Api::API_NAMESPACE, '/media/search', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'search_media' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );
    }

    /**
     * Get all media items
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_media( WP_REST_Request $request ): mixed {

        $amount = $request->get_param( 'amount' ) ?? 12;
        $page = $request->get_param( 'page' ) ?? 1;

        $data = SurfShareKit::get_items();

        $items = array_slice( $data['items'], ( $page - 1 ) * $amount, $amount );

        return $items;
    }

    /**
     * Get total amount of media items
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return int
     */
    public static function get_total_media( WP_REST_Request $request ): int {
        $data = SurfShareKit::get_items();

        return count( $data['items'] );
    }

    /**
     * Upload media item
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function upload_media( WP_REST_Request $request ): mixed {

        $file = $request->get_file_params();

        if ( empty( $file ) ) {
            return Rest_Api::send_error_response( 'media_upload_failed', 'No file uploaded' );
        }

        $file = reset( $file );

        $response = SurfShareKit::upload_media( $file );

        if ( is_wp_error( $response ) ) {
            return Rest_Api::send_error_response( 'media_upload_failed', $response->get_error_message() );
        }

        return Rest_Api::send_success_response( $response );
    }

    /**
     * Search media items
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function search_media( WP_REST_Request $request ): mixed {

        error_reporting( E_ALL );
        ini_set( 'display_errors', 1 );
        $search_term = $request->get_param( 'term' ) ?? '';

        $data = SurfShareKit::get_items();

        // filter items
        $items = array_filter( $data['items'], function( $item ) use ( $search_term ) {

            $found = false;
            
            if ( isset( $item['attributes'] ) ) {
                if ( 
                    isset( $item['attributes']['title'] ) && 
                    stripos( 
                        strtolower( $item['attributes']['title'] ), strtolower( $search_term ) 
                    ) !== false 
                ) {
                    $found = true;
                }
                else if ( 
                    isset( $item['attributes']['description'] ) && 
                    stripos( 
                        strtolower( $item['attributes']['description'] ), strtolower( $search_term ) 
                    ) !== false 
                ) {
                    $found = true;
                }
                else if ( 
                    isset( $item['attributes']['tags'] ) && 
                    in_array( 
                        strtolower( $search_term ), $item['attributes']['tags'] ) 
                ) {
                    $found = true;
                }
            }

            return $found;
            
        } );

        return $items;
    }
}