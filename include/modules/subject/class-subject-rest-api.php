<?php

namespace TutorialPlatform\Modules\Subject;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Abstracts;
use TutorialPlatform\Common\Rest_Api;
use TutorialPlatform\Modules\Taxonomy\Taxonomy;
use WP_REST_Server;
use WP_REST_Request;

class Subject_Rest_Api extends Abstracts\Rest_Api {
    
    /**
    * Register routes
    * 
    * @since 1.0.0
    * 
    * @return void
    */
    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/subjects', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_subjects' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/subjects/preview', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => [ self::class, 'get_subject_preview' ],
        'permission_callback' => function( $request ) {
            return Rest_Api::is_user_allowed( $request );
        },
    ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/subjects/single', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_single_subject' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/subjects/create', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_subject' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/subjects/create/draft', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_draft_subject' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/subjects/single/delete', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [ self::class, 'delete_subject' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/subjects/single/update', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [ self::class, 'update_subject' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/subjects/create/info', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [ self::class, 'get_subject_create_info' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

    }
    
    /**
     * Get subjects from tutor
     * 
     * @param WP_REST_Request $request
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_subjects( WP_REST_Request $request ): mixed {

        $amount = $request->get_param( 'amount' ) ? $request->get_param( 'amount' ) : -1;
        $status = $request->get_param( 'status' ) ? $request->get_param( 'status' ) : 'publish';

        $subjects = parent::get_modules( 'subject', $amount, $status );

        if ( !empty( $subjects['error'] ) ) {
            Rest_Api::send_error_response( $subjects['error'] );
        }

        return $subjects;
    }

    /**
     * Get subject preview
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_subject_preview( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $link = parent::get_module_preview_link( 'subject', $id );

        if ( ! $link ) {
            Rest_Api::send_error_response( 'no_preview' );
        }

        return $link;
    }

    /**
     * Get single subject for given id
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_single_subject( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $subject = parent::get_single_module( 'subject', $id, Subject::CUSTOM_FIELDS_MAPPING, true, [ 'category' ] );

        if ( !empty( $subject['error'] ) ) {
            Rest_Api::send_error_response( $subject['error'] );
        }

        return $subject;
    }

    /**
     * Create subject
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function create_subject( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['title'] ) {
            Rest_Api::send_error_response( 'no_name' );
        }

        if ( ! $data['description'] ) {
            Rest_Api::send_error_response( 'no_content' );
        }

        $status = $data['status'] ? $data['status'] : 'publish';

        $response = parent::create_module( 'subject', $data['title'], $status, $data, Subject::CUSTOM_FIELDS_MAPPING );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return [
            'id' => $response
        ];
    }

    /**
     * Create draft subject
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function create_draft_subject( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['title'] && ! $data['id']) {
            Rest_Api::send_error_response( 'no_name' );
        }

        $id = 0;

        if ( $data['id'] ) {
            $id = intval( $data['id'] );
        }

        $response = parent::create_or_update_draft_module(
            'subject',
            $id,
            $data['title'],
            $data,
            Subject::CUSTOM_FIELDS_MAPPING
        );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        Rest_Api::send_success_response( $response );
    }

    /**
     * Delete subject
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function delete_subject( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $response = parent::delete_module( 'subject', $id );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }

    /**
     * Update subject
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function update_subject( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['id'] ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $status = $data['status'] ? $data['status'] : 'publish';

        $response = parent::update_module( 'subject', $data['id'], $data['title'], $status, $data, Subject::CUSTOM_FIELDS_MAPPING );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }

    /**
     * Get subject create info
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_subject_create_info(): mixed {
        
        /**
         *  Required data for creating subject
         * 
         *  categories
         */ 

        $categories = Taxonomy::get_categories( false, true );

        return [
            'categories' => $categories,
        ];
    }
}    