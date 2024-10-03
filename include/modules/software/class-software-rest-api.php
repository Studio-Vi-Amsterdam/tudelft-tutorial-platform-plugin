<?php

namespace TutorialPlatform\Modules\Software;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Abstracts;
use TutorialPlatform\Common\Rest_Api;
use TutorialPlatform\Modules\Taxonomy\Taxonomy;
use WP_REST_Server;
use WP_REST_Request;

class Software_Rest_Api extends Abstracts\Rest_Api {

    /**
     * Register routes
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/softwares', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_softwares' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/softwares/preview', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_software_preview' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/softwares/single', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_single_software' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/softwares/create', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_software' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/softwares/create/draft', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'create_draft_software' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/softwares/single/delete', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [ self::class, 'delete_software' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/softwares/single/update', [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => [ self::class, 'update_software' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        register_rest_route( Rest_Api::API_NAMESPACE, '/softwares/create/info', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_software_create_info' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );
    }

    /**
     * Get softwares from tutor
     * 
     * @param WP_REST_Request $request
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_softwares( WP_REST_Request $request ): mixed {

        $amount = $request->get_param( 'amount' ) ? $request->get_param( 'amount' ) : -1;
        $status = $request->get_param( 'status' ) ? $request->get_param( 'status' ) : 'publish';

        $softwares = parent::get_modules( 'software', $amount, $status );

        if ( !empty( $softwares['error'] ) ) {
            Rest_Api::send_error_response( $softwares['error'] );
        }

        return $softwares;
    }

    /**
     * Get software preview
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_software_preview( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $link = parent::get_module_preview_link( 'software', $id );

        if ( ! $link ) {
            Rest_Api::send_error_response( 'no_preview' );
        }

        return $link;
    }

    /**
     * Get single software for given id
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_single_software( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $software = parent::get_single_module( 'software', $id, Software ::CUSTOM_FIELDS_MAPPING, true, [ 'keywords', 'software-version' ] );

        if ( !empty( $software['error'] ) ) {
            Rest_Api::send_error_response( $software['error'] );
        }

        return $software;
    }

    /**
     * Create software
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function create_software( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['title'] ) {
            Rest_Api::send_error_response( 'no_name' );
        }

        if ( ! $data['description'] ) {
            Rest_Api::send_error_response( 'no_content' );
        }

        $status = $data['status'] ? $data['status'] : 'publish';

        $media_ids = $data['mediaIds'] ? $data['mediaIds'] : [];

        $response = parent::create_module( 'software', $data['title'], $status, $data, Software::CUSTOM_FIELDS_MAPPING, $media_ids );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return [
            'id' => $response
        ];
    }

    /**
     * Create draft software
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function create_draft_software( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['title'] && ! $data['id']) {
            Rest_Api::send_error_response( 'no_name' );
        }

        $id = 0;

        if ( $data['id'] ) {
            $id = intval( $data['id'] );
        }

        $response = parent::create_or_update_draft_module(
            'software',
            $id,
            $data['title'],
            $data,
            Software::CUSTOM_FIELDS_MAPPING
        );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        Rest_Api::send_success_response( $response );
    }

    /**
     * Delete software
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function delete_software( WP_REST_Request $request ): mixed {
        
        $id = $request->get_param( 'id' );

        if ( ! $id ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $response = parent::delete_module( 'software', $id );

        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }

    /**
     * Update software
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function update_software( WP_REST_Request $request ): mixed {
        
        $data = $request->get_json_params();

        if ( ! $data['id'] ) {
            Rest_Api::send_error_response( 'id_required' );
        }

        $response = parent::update_module( 'software', $data['id'], $data, Software::CUSTOM_FIELDS_MAPPING );
        
        if ( !empty($response['error']) ) {
            Rest_Api::send_error_response( $response['error'] );
        }

        return true;
    }

    /**
     * Get software create info
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_software_create_info(): mixed {
        
        /**
         *  Required data for creating software
         * 
         *  keywords,
         *  software versions,
         */ 

        $keywords = Taxonomy::get_keywords( false, true );
        $software_versions = Taxonomy::get_software_versions( false, true );
        $defined_terms = Taxonomy::get_defined_terms();

        return [
            'keywords' => $keywords,
            'software_versions' => $software_versions,
            'defined_terms' => $defined_terms
        ];
    }
}