<?php

namespace TutorialPlatform\Modules\Keyword;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Common\Rest_Api;
use WP_REST_Server;
use WP_REST_Request;

class Keyword_Rest_Api {

    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/keywords', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_keywords' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/keywords/create', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_keyword' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/keywords/single', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_single_keyword' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );
    }


    /**
     * Get all keywords
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     */
    public static function get_keywords( WP_REST_Request $request ): mixed {

        $hide_empty = $request->get_param( 'hide_empty' ) ? $request->get_param( 'hide_empty' ) : false;

        $args = [
            'taxonomy' => 'keywords',
            'hide_empty' => $hide_empty,
            'orderby' => 'count',
            'order' => 'DESC',

        ];

        $keywords = get_terms( $args );

        return $keywords;
    }

    /**
     * Create new keyword
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     */
    public static function create_keyword( WP_REST_Request $request ): mixed {
        $keyword = $request->get_param( 'keyword' );

        if ( empty( $keyword ) ) {
            return new \WP_Error( 'keyword_empty', 'Keyword is empty', [ 'status' => 400 ] );
        }

        $term = wp_insert_term( $keyword, 'keywords' );

        if ( is_wp_error( $term ) ) {
            return $term;
        }

        return $term;
    }

    /**
     * Get single keyword
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     */
    public static function get_single_keyword( WP_REST_Request $request ): mixed {
        $id = $request->get_param( 'id' );
        $name = $request->get_param( 'name' );

        if ( ! $id && ! $name ) {
            Rest_Api::send_error_response( 'id_or_name_required' );
        }

        if ( $id ) {
            $keyword = get_term( $id, 'keywords' );
        } else {
            $keyword = get_term_by( 'name', $name, 'keywords' );
        }

        if ( is_wp_error( $keyword ) ) {
            return $keyword;
        }

        return $keyword;
    }
}