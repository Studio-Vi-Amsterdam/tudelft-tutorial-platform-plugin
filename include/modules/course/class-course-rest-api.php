<?php

namespace TutorialPlatform\Modules\Course;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Abstracts;
use TutorialPlatform\Common\Rest_Api;
use WP_REST_Server;
use WP_REST_Request;

class Course_Rest_Api extends Abstracts\Rest_Api {

    /**
     * Register routes
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/courses', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_courses' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/courses/single', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_single_course' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/courses/create', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_course' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/courses/single/delete', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [ self::class, 'delete_course' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/courses/single/update', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [ self::class, 'update_course' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );
    }

    /**
     * Get courses from tutor
     * 
     * @param WP_REST_Request $request
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_courses( WP_REST_Request $request ): mixed {

        $amount = $request->get_param( 'amount' ) ? $request->get_param( 'amount' ) : -1;
        $status = $request->get_param( 'status' ) ? $request->get_param( 'status' ) : 'publish';

        $courses = parent::get_modules( 'course', $amount, $status );

        if ( !empty( $courses['error'] ) ) {
            Rest_Api::send_error_response( $courses['error'] );
        }

        return $courses;
    }

    /**
     * Get single course for given id
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_single_course( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $course = parent::get_single_module( 'course', $id, Course::CUSTOM_FIELDS_MAPPING );

        if ( !empty( $course['error'] ) ) {
            Rest_Api::send_error_response( $course['error'] );
        }

        return $course;
    }

    /**
     * Create course
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function create_course( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['title'] ) {
            Rest_Api::send_error_response( 'no_name' );
        }

        if ( ! $data['description'] ) {
            Rest_Api::send_error_response( 'no_content' );
        }

        $status = $data['status'] ? $data['status'] : 'publish';

        $response = parent::create_module( 'course', $data['title'], $status, $data, Course::CUSTOM_FIELDS_MAPPING );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }

    /**
     * Delete course
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function delete_course( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $response = parent::delete_module( 'course', $id );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }

    /**
     * Update course
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function update_course( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['id'] ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        if ( ! $data['title'] ) {
            Rest_Api::send_error_response( 'no_name' );
        }

        if ( ! $data['description'] ) {
            Rest_Api::send_error_response( 'no_content' );
        }

        $status = $data['status'] ? $data['status'] : 'publish';

        $response = parent::update_module( 'course', $data['id'], $data['title'], $status, $data, Course::CUSTOM_FIELDS_MAPPING );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }
}