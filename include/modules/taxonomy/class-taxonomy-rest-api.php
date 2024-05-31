<?php

namespace TutorialPlatform\Modules\Taxonomy;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Common\Rest_Api;
use WP_REST_Server;
use WP_REST_Request;

class Taxonomy_Rest_Api {

    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/keywords', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_keywords' ],
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

        register_rest_route( Rest_Api::API_NAMESPACE, '/keywords/create', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_keyword' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );


        register_rest_route( Rest_Api::API_NAMESPACE, '/teachers', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_teachers' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/teachers/single', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_single_teacher' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/teachers/create', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_teacher' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/software-version', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_software_version' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/software-version/single', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_single_software_version' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/software-version/create', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_software_version' ],
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
     * 
     * @return mixed
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
     * Get all teachers
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_teachers( WP_REST_Request $request ): mixed {

        $hide_empty = $request->get_param( 'hide_empty' ) ? $request->get_param( 'hide_empty' ) : false;

        $args = [
            'taxonomy' => 'teachers',
            'hide_empty' => $hide_empty,
            'orderby' => 'count',
            'order' => 'DESC',

        ];

        $teachers = get_terms( $args );

        return $teachers;
    }

    /**
     * Get all software versions
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_software_version( WP_REST_Request $request ): mixed {

        $hide_empty = $request->get_param( 'hide_empty' ) ? $request->get_param( 'hide_empty' ) : false;

        $args = [
            'taxonomy' => 'software-version',
            'hide_empty' => $hide_empty,
            'orderby' => 'count',
            'order' => 'DESC',

        ];

        $software_versions = get_terms( $args );

        return $software_versions;
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
     * Create new teacher
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     */
    public static function create_teacher( WP_REST_Request $request ): mixed {
        $teacher = $request->get_param( 'teacher' );

        if ( empty( $teacher ) ) {
            return new \WP_Error( 'teacher_empty', 'Teacher is empty', [ 'status' => 400 ] );
        }

        $term = wp_insert_term( $teacher, 'teachers' );

        if ( is_wp_error( $term ) ) {
            return $term;
        }

        return $term;
    }

    /**
     * Create new software version
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     */
    public static function create_software_version( WP_REST_Request $request ): mixed {
        $software_version = $request->get_param( 'software_version' );

        if ( empty( $software_version ) ) {
            return new \WP_Error( 'software_version_empty', 'Software version is empty', [ 'status' => 400 ] );
        }

        $term = wp_insert_term( $software_version, 'software-version' );

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

    /**
     * Get single teacher
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     */
    public static function get_single_teacher( WP_REST_Request $request ): mixed {
        $id = $request->get_param( 'id' );
        $name = $request->get_param( 'name' );

        if ( ! $id && ! $name ) {
            Rest_Api::send_error_response( 'id_or_name_required' );
        }

        if ( $id ) {
            $teacher = get_term( $id, 'teachers' );
        } else {
            $teacher = get_term_by( 'name', $name, 'teachers' );
        }

        if ( is_wp_error( $teacher ) ) {
            return $teacher;
        }

        return $teacher;
    }

    /**
     * Get single software version
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     */
    public static function get_single_software_version( WP_REST_Request $request ): mixed {
        $id = $request->get_param( 'id' );
        $name = $request->get_param( 'name' );

        if ( ! $id && ! $name ) {
            Rest_Api::send_error_response( 'id_or_name_required' );
        }

        if ( $id ) {
            $software_version = get_term( $id, 'software-version' );
        } else {
            $software_version = get_term_by( 'name', $name, 'software-version' );
        }

        if ( is_wp_error( $software_version ) ) {
            return $software_version;
        }

        return $software_version;
    }
}