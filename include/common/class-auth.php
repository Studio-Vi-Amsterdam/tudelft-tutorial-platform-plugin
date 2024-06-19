<?php

namespace TutorialPlatform\Common;

use TutorialPlatform\Abstracts;
use WP_REST_Response;

class Auth extends Abstracts\Rest_Api {

    // 1 day in seconds 
    private const EXPIRE_TIME = DAY_IN_SECONDS * 1;

    public function __construct() {
        
        // redirect user after login
        add_action('wp_login', [ $this, 'redirect_user_after_login' ], 1, 2);        
    }

    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/auth', [
            'methods' => 'POST',
            'callback' => [ self::class, 'auth_user' ],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * Redirect user after login
     * 
     * @param string $user_login
     * 
     * @param WP_User $user
     * 
     * @return void
     */
    public function redirect_user_after_login($user_login, $user) {
        
        $use_role = $user->roles[0];

        if ($use_role == 'administrator') {
            // admin chooses do they go to wp-admin or dashboard
        } else {
            $random_key = self::generate_random_key($user->user_login);

            $encoded_key = base64_encode($random_key);

            // save random key to user meta
            update_user_meta($user->ID, 'auth_key', $random_key);
            
            // redirect to react-app
            wp_redirect('https://tu-delft-teacher-dashboard.vercel.app/login?auth_key=' . $encoded_key);

            exit;
        }
    }

    /**
     * Validate auth token
     * 
     * @param string $token
     * 
     * @return string
     */
    public static function validate_auth_token( string $token ): string {

        $encrypted = base64_decode( $token );

        $user_login = explode( ':', $encrypted )[0];
        $token = explode( ':', $encrypted )[1];

        $user = get_user_by( 'login', $user_login );
        
        if ( ! $user ) {
            return 'auth_failed';
        }

        $saved_token = get_user_meta( $user->ID, 'auth_token', true );
        

        if ( empty( $saved_token ) ) {
            return 'auth_failed';
        }

        if ( $saved_token['token'] !== md5( $token ) ) {
            return 'auth_failed';
        }


        if ( $saved_token['expire'] < time() ) {
            return 'expired_token';
        }

        wp_set_current_user( $user->ID );

        return 'auth_success';
    }

    /**
     * Authenticate user
     * 
     * @param WP_REST_Request $request
     * 
     * @return WP_REST_Response
     */
    public static function auth_user($request) {
        
        $auth_key = $request->get_param('auth_key');

        if (!$auth_key) {
            Rest_Api::send_error_response( 'auth_failed' );
        }

        // decode key
        $auth_key = base64_decode($auth_key);

        $user_login = explode('-', $auth_key)[0];
        
        $user = get_user_by('login', $user_login);
        
        if (!$user) {
             Rest_Api::send_error_response( 'auth_failed' );
        }

        $saved_auth_key = get_user_meta($user->ID, 'auth_key', true);

        if ($saved_auth_key !== $auth_key) {
            Rest_Api::send_error_response( 'auth_failed' );
        }

        // remove key from user meta
        delete_user_meta($user->ID, 'auth_key');

        // 128 bit token
        $token = self::generate_128bit_token();

        $expire = time() + self::EXPIRE_TIME;

        // save token to user meta
        update_user_meta($user->ID, 'auth_token', [
            'token' => md5($token),
            'expire' => $expire
        ]);

        // combine user login and md5 token 
        $token = base64_encode($user_login . ':' . $token);

        // send token to user
        Rest_Api::send_success_response([
            'token' => $token,
            'expire' => $expire
        ]);
    }

    /**
     * Generate random key for user
     * 
     * @param string $user_login 
     * 
     * @return string
     */
    private static function generate_random_key($user_login) {
        $key = $user_login . '-' . md5($user_login . time());
        return $key;
    }

    /**
     * Generate token for user
     * 
     * @return string
     * 
     */
    private static function generate_128bit_token() {
        return wp_generate_password(128, false);
    }

}