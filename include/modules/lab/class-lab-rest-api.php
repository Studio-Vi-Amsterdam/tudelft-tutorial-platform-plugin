<?php

namespace TutorialPlatform\Modules\Lab;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Abstracts;
use TutorialPlatform\Common\Rest_Api;
use TutorialPlatform\Modules\Taxonomy\Taxonomy;
use WP_REST_Server;
use WP_REST_Request;

class Lab_Rest_Api extends Abstracts\Rest_Api {

    /**
     * Register routes
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/labs', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_labs' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/labs/preview', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_labs_preview' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/labs/single', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_single_course' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/labs/create', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_course' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/labs/create/draft', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_draft_course' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/labs/single/delete', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [ self::class, 'delete_course' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/labs/single/update', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [ self::class, 'update_course' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/labs/create/info', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_course_create_info' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );
    }

    /**
     * Get labs from tutor
     * 
     * @param WP_REST_Request $request
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_labs( WP_REST_Request $request ): mixed {

        $amount = $request->get_param( 'amount' ) ? $request->get_param( 'amount' ) : -1;
        $status = $request->get_param( 'status' ) ? $request->get_param( 'status' ) : 'publish';

        $labs = parent::get_modules( 'course', $amount, $status );

        if ( !empty( $labs['error'] ) ) {
            Rest_Api::send_error_response( $labs['error'] );
        }

        return $labs;
    }

    /**
     * Get labs preview from tutor
     * 
     * @param WP_REST_Request $request
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_labs_preview( WP_REST_Request $request ): mixed {

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
     * Get single lab for given id
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_single_lab( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $lab = parent::get_single_module( 'lab', $id, Lab::CUSTOM_FIELDS_MAPPING, true, [ 'keywords', 'teachers' ] );

        if ( !empty( $lab['error'] ) ) {
            Rest_Api::send_error_response( $lab['error'] );
        }

        return $lab;
    }

    /**
     * Create lab
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function create_lab( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['title'] ) {
            Rest_Api::send_error_response( 'no_name' );
        }

        if ( ! $data['description'] ) {
            Rest_Api::send_error_response( 'no_content' );
        }

        $status = $data['status'] ? $data['status'] : 'publish';

        $media_ids = $data['mediaIds'] ? $data['mediaIds'] : [];

        $response = parent::create_module( 'lab', $data['title'], $status, $data, Lab::CUSTOM_FIELDS_MAPPING, $media_ids );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return [
            'id' => $response
        ];
    }

    /**
     * Create draft lab
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function create_draft_lab( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['title'] && ! $data['id']) {
            Rest_Api::send_error_response( 'no_name' );
        }

        $id = 0;

        if ( $data['id'] ) {
            $id = intval( $data['id'] );
        }

        $response = parent::create_or_update_draft_module(
            'lab',
            $id,
            $data['title'],
            $data,
            Lab::CUSTOM_FIELDS_MAPPING
        );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        Rest_Api::send_success_response( $response );
    }

    /**
     * Delete lab
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function delete_lab( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $response = parent::delete_module( 'lab', $id );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }

    /**
     * Update lab
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function update_lab( WP_REST_Request $request ): mixed {
        
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

        $response = parent::update_module( 'lab', $data['id'], $data, Lab::CUSTOM_FIELDS_MAPPING );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }

    /**
     * Get lab create info
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_lab_create_info(): mixed {
        
        /**
         * Required data for creating lab
         * 
         * Study,
         * keywords,
         * teachers,
         * faculty,
         */

        $study = Taxonomy::get_academic_levels( false, false );
        $seconday_study = Taxonomy::get_academic_levels( false, true );
        $keywords = Taxonomy::get_keywords( false, true );
        $teachers = Taxonomy::get_teachers( false, true );
        $faculty = [
            'Bouwkunde'
        ];

        return [
            'study' => $study,
            'seconday_study' => $seconday_study,
            'keywords' => $keywords,
            'teachers' => $teachers,
            'faculty' => $faculty,
        ];
    }
}