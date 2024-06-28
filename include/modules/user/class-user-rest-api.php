<?php

namespace TutorialPlatform\Modules\User;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Common\Rest_Api;
use WP_REST_Server;
use WP_REST_Request;
use WP_Error;

//class User_Rest_Api implements Interface_Rest_Api
class User_Rest_Api {
    
    /**
     * Register routes
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/user', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_user' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );
    }
    
    /**
     * Get user data
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return array|WP_Error
     */
    public static function get_user( WP_REST_Request $request ): array|WP_Error {
        
        $user = wp_get_current_user();
        
        if ( ! $user->exists() ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You are not allowed to access this resource', 'tutorial-platform' ), [ 'status' => 401 ] );
        }
        
        $response = [
            'username' => $user->user_login,
            'email' => $user->user_email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ];
        
        return $response;
    }
}