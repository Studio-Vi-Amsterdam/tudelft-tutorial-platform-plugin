<?php

namespace TutorialPlatform\Modules\Course;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Abstracts;
use TutorialPlatform\Common\Rest_Api;
use TutorialPlatform\Modules\Taxonomy\Taxonomy;
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

        register_rest_route( Rest_Api::API_NAMESPACE, '/courses/all', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_all_courses' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/courses/preview', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_courses_preview' ],
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

        register_rest_route( Rest_Api::API_NAMESPACE, '/courses/create/draft', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_draft_course' ],
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

        register_rest_route( Rest_Api::API_NAMESPACE, '/courses/create/info', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_course_create_info' ],
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
     * Get all courses
     * 
     * @param WP_REST_Request $request
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_all_courses( WP_REST_Request $request ): mixed {
    
        $amount = $request->get_param( 'amount' ) ? $request->get_param( 'amount' ) : -1;
        $status = $request->get_param( 'status' ) ? $request->get_param( 'status' ) : 'publish';
        
        $courses = parent::get_modules( 'tutorial', $amount, $status, false );

        if ( !empty( $courses['error'] ) ) {
            Rest_Api::send_error_response( $courses['error'] );
        }

        return $courses;
    }

    /**
     * Get courses preview from tutor
     * 
     * @param WP_REST_Request $request
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_courses_preview( WP_REST_Request $request ): mixed {

        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $link = parent::get_module_preview_link( 'course', $id );

        if ( ! $link ) {
            Rest_Api::send_error_response( 'no_preview' );
        }

        return $link;
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

        $course = parent::get_single_module( 'course', $id, Course::CUSTOM_FIELDS_MAPPING, true, [ 'keywords', 'teachers' ] );

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

        $media_ids = $data['mediaIds'] ? $data['mediaIds'] : [];

        $response = parent::create_module( 'course', $data['title'], $status, $data, Course::CUSTOM_FIELDS_MAPPING, $media_ids );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return [
            'id' => $response
        ];
    }

    /**
     * Create draft course
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function create_draft_course( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['title'] && ! $data['id']) {
            Rest_Api::send_error_response( 'no_name' );
        }

        $id = 0;

        if ( $data['id'] ) {
            $id = intval( $data['id'] );
        }

        $response = parent::create_or_update_draft_module(
            'course',
            $id,
            $data['title'],
            $data,
            Course::CUSTOM_FIELDS_MAPPING
        );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        Rest_Api::send_success_response( $response );
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

        $response = parent::update_module( 'course', $data['id'], $data, Course::CUSTOM_FIELDS_MAPPING );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }

    /**
     * Get course create info
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_course_create_info(): mixed {
        
        /**
         * Required data for creating course
         * 
         * Study,
         * keywords,
         * teachers,
         * faculty,
         */

        $study = Taxonomy::get_academic_levels( false, false );
        $secondary_study = Taxonomy::get_academic_levels( false, true );
        $keywords = Taxonomy::get_keywords( false, true );
        $teachers = Taxonomy::get_teachers( false, true );
        $faculty = [
            'Bouwkunde'
        ];

        return [
            'study' => $study,
            'secondary_study' => $secondary_study,
            'keywords' => $keywords,
            'teachers' => $teachers,
            'faculty' => $faculty,
        ];
    }
}