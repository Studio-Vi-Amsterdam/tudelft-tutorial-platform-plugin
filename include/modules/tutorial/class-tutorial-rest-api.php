<?php

namespace TutorialPlatform\Modules\Tutorial;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Abstracts;
use TutorialPlatform\Common\Rest_Api;
use TutorialPlatform\Modules\Course\Course;
use TutorialPlatform\Modules\Software\Software;
use TutorialPlatform\Modules\Subject\Subject;
use TutorialPlatform\Modules\Taxonomy\Taxonomy;
use WP_REST_Server;
use WP_REST_Request;

class Tutorial_Rest_Api extends Abstracts\Rest_Api {

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

        register_rest_route( Rest_Api::API_NAMESPACE, '/tutorials/all', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_all_tutorials' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/tutorials/preview', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_tutorial_preview' ],
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

        register_rest_route( Rest_Api::API_NAMESPACE, '/tutorials/create/draft', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_draft_tutorial' ],
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

        register_rest_route( Rest_Api::API_NAMESPACE, '/tutorials/single/delete', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [ self::class, 'delete_tutorial' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/tutorials/single/update', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [ self::class, 'update_tutorial' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/tutorials/create/info', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_tutorial_create_info' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );
    }

    /**
     * Get tutorials from tutor
     * 
     * @param WP_REST_Request $request
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_tutorials( WP_REST_Request $request ): mixed {

        $amount = $request->get_param( 'amount' ) ? $request->get_param( 'amount' ) : -1;
        $status = $request->get_param( 'status' ) ? $request->get_param( 'status' ) : 'publish';
        
        $tutorials = parent::get_modules( 'tutorial', $amount, $status );

        if ( !empty( $tutorials['error'] ) ) {
            Rest_Api::send_error_response( $tutorials['error'] );
        }

        return $tutorials;
    }

    /**
     * Get all tutorials
     * 
     * @param WP_REST_Request $request
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_all_tutorials( WP_REST_Request $request ): mixed {
    
        $amount = $request->get_param( 'amount' ) ? $request->get_param( 'amount' ) : -1;
        $status = $request->get_param( 'status' ) ? $request->get_param( 'status' ) : 'publish';
        
        $tutorials = parent::get_modules( 'tutorial', $amount, $status, false );

        if ( !empty( $tutorials['error'] ) ) {
            Rest_Api::send_error_response( $tutorials['error'] );
        }

        return $tutorials;
    }

    /**
     * Get tutorials preview from tutor
     * 
     * @param WP_REST_Request $request
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_tutorial_preview( WP_REST_Request $request ): mixed {

        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $link = parent::get_module_preview_link( 'tutorial', $id );

        if ( ! $link ) {
            Rest_Api::send_error_response( 'no_preview' );
        }

        return $link;
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

        $tutorial = parent::get_single_module( 'tutorial', $id, Tutorial::CUSTOM_FIELDS_MAPPING, true, [ 'keywords', 'teachers' ] );

        if ( !empty( $tutorial['error'] ) ) {
            Rest_Api::send_error_response( $tutorial['error'] );
        }

        return $tutorial;
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
        
        $data = $request->get_json_params();

        if ( ! $data['title'] ) {
            Rest_Api::send_error_response( 'no_name' );
        }

        if ( ! $data['description'] ) {
            Rest_Api::send_error_response( 'no_content' );
        }

        $status = $data['status'] ? $data['status'] : 'publish';

        $media_ids = $data['mediaIds'] ? $data['mediaIds'] : [];

        $response = parent::create_module( 'tutorial', $data['title'], $status, $data, Tutorial::CUSTOM_FIELDS_MAPPING, $media_ids );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return [
            'id' => $response
        ];
    }

    /**
     * Create draft tutorial
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     */
    public static function create_draft_tutorial( WP_REST_Request $request ) {
        
        $data = $request->get_json_params();

        if ( ! $data['title'] && ! $data['id']) {
            Rest_Api::send_error_response( 'no_name' );
        }

        $id = 0;

        if ( $data['id'] ) {
            $id = intval( $data['id'] );
        }

        $response = parent::create_or_update_draft_module(
            'tutorial',
            $id,
            $data['title'],
            $data,
            Tutorial::CUSTOM_FIELDS_MAPPING
        );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        Rest_Api::send_success_response( $response );
    }

    /**
     * Delete tutorial
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function delete_tutorial( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $response = parent::delete_module( 'tutorial', $id );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }

    /**
     * Update tutorial
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function update_tutorial( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['id'] ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $response = parent::update_module( 'tutorial', $data['id'], $data, Tutorial::CUSTOM_FIELDS_MAPPING );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }

    /**
     * Get tutorial create info
     * 
     * @since 1.0.0
     * 
     */
    public static function get_tutorial_create_info() {
        
        /**
         * Required data for creating tutorial
         * Softwares,
         * Software versions (for each software),
         * Subjects,
         * Courses,
         * keywords,
         * teachers
         * Faculties
         */

        $softwares = Software::get_softwares();
        $categories = Taxonomy::get_categories( false, false );
        $secondary_categories = Taxonomy::get_categories( false, true, true );
        $courses = Course::get_all_courses();
        $faculties = [
            'Bouwkunde'
        ];
        $keywords = Taxonomy::get_keywords(false, true);
        $teachers = Taxonomy::get_teachers(false, true);
        $defined_terms = Taxonomy::get_defined_terms();

        Rest_Api::send_success_response([
            'softwares' => $softwares,
            'subjects' => $categories,
            'secondary_subjects' => $secondary_categories,
            'courses' => $courses,
            'faculties' => $faculties,
            'keywords' => $keywords,
            'teachers' => $teachers,
            'defined_terms' => $defined_terms
       ]);
    }
}