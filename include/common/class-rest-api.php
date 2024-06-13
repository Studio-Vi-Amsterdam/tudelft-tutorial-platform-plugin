<?php
/**
 * The REST API class.
 * 
 * @since 1.0.0
 * 
 * @package TutorialPlatform\Common
 *
 */
namespace TutorialPlatform\Common;

use TutorialPlatform\Modules\Chapter\Chapter_Rest_Api;
use TutorialPlatform\Modules\Course\Course;
use TutorialPlatform\Modules\Course\Course_Rest_Api;
use TutorialPlatform\Modules\Media\Media_Rest_Api;
use TutorialPlatform\Modules\Taxonomy\Taxonomy_Rest_Api;
use TutorialPlatform\Modules\Software\Software_Rest_Api;
use TutorialPlatform\Modules\Tutorial\Tutorial_Rest_Api;
use TutorialPlatform\Modules\Subject\Subject_Rest_Api;
use WP_Error;
use WP_REST_Request;

defined( 'ABSPATH' ) || die( "Can't access directly" );

class Rest_Api {

    private $allowed_origins = [];

    const API_NAMESPACE = 'tutorial-platform/v1';

    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
        add_action( 'init', [ $this, 'cors_header' ] );
    }

    /**
     * Add CORS headers
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function cors_header(): void {

        // Get the HTTP Origin
        $origin = get_http_origin() ? : ( isset( $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : $_SERVER['HTTP_HOST'] );

        // Clean the origin URL
        $origin_domain = str_replace( 'www.', '', $origin );
        $origin_domain = str_replace( 'http://', '', $origin_domain );
        $origin_domain = str_replace( 'https://', '', $origin_domain );

        // Check if the origin domain is in our whitelist
        // if ( ! in_array( $origin_domain, $this->allowed_origins ) ) {
        //     return;
        // }
        
        // TODO: instead of * allow all origins from $this->allowed_origins
        header( 'Access-Control-Allow-Origin: *' );

        // Allow the following HTTP methods
        header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");

        // Allow the following headers to be included in the request
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        // Allow credentials (cookies, HTTP authentication, etc.)
        header("Access-Control-Allow-Credentials: true");

        // Set the maximum amount of time the client is allowed to cache the preflight request
        header("Access-Control-Max-Age: 3600");

        // Check if it's an OPTIONS request (preflight), and if so, exit early with a 200 OK response
        if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
            header("HTTP/1.1 200 OK");
            exit();
        }
    }

    /**
     * Register routes
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function register_routes(): void {
        Auth::register_routes();
        Chapter_Rest_Api::register_routes();
        Course_Rest_Api::register_routes();
        Tutorial_Rest_Api::register_routes();
        Software_Rest_Api::register_routes();
        Taxonomy_Rest_Api::register_routes();
        Subject_Rest_Api::register_routes();
        Media_Rest_Api::register_routes();
    }

    /**
     * Check if user is allowed to access the API
     * 
     * @param WP_REST_Request $request
     * 
     * @since 1.0.0
     * 
     * @return bool|WP_Error
     */
    public static function is_user_allowed( WP_REST_Request $request ): bool|WP_Error {
        $token = $request->get_header( 'Authorization' );

        if ( empty( $token ) ) {
            return false;
        }

        switch ( Auth::validate_auth_token($token) ) {
            case 'auth_success':
                return true;
            case 'auth_failed':
                return new WP_Error( 'auth_failed', 'Authentication failed', ['status' => 401] );
                break;
            case 'expired_token':
                return new WP_Error( 'expired_token', 'Token expired', ['status' => 401] );
                break;
        }

        return false;
    }

    /**
     * Send success response for the API
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public static function send_success_response( $data = [] ): void {
        wp_send_json_success( $data );
    }
    
    /**
     * Send success response notifcation for the API
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public static function send_success_notification( string $code ): void {
        $success = self::get_success_mapping()[ $code ];
        $success = !empty( $success ) ? $success : self::get_success_mapping()[ 'unknown' ];
        // set status and message
        $status = $success['status'];
        wp_send_json_success( $success['message'], $status);
    }
        

    /**
     * Send error response for the API
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public static function send_error_response( string $error_code, string $message = '' ): void {
        // get error mapping for error code
        $error = self::get_error_mapping()[ $error_code ];
        $error = !empty( $error ) ? $error : self::get_error_mapping()[ 'unknown' ];
        // set status and message
        $status = $error['status'];
        $message = !empty( $message ) ? $error['message'] . ': ' . $message : $error['message'];
        wp_send_json_error( $message, $status );
    }

    /**
     * Map of error codes to HTTP status codes and messages.
     * 
     * @since 1.0.0
     * 
     * @return array
     */
    private static function get_error_mapping(): array {
        return [
            'unauthorized' => [
                'status' => 401,
                'message' => __( 'Unauthorized', 'tutorial-platform' ),
            ],
            'unknown' => [
                'status' => 500,
                'message' => __( 'Unknown error', 'digitale-gruendung' ),
            ],
            'id_required' => [
                'status' => 400,
                'message' => __( 'ID is required', 'tutorial-platform' ),
            ],
            'no_chapters_found' => [
                'status' => 404,
                'message' => __( 'No chapters found', 'tutorial-platform' ),
            ],
            'no_chapter_found' => [
                'status' => 404,
                'message' => __( 'No chapter found', 'tutorial-platform' ),
            ],
            'invalid_chapter_id' => [
                'status' => 400,
                'message' => __( 'Invalid chapter ID', 'tutorial-platform' ),
            ],
            'no_tutorials_found' => [
                'status' => 404,
                'message' => __( 'No tutorials found', 'tutorial-platform' ),
            ],
            'no_tutorial_found' => [
                'status' => 404,
                'message' => __( 'No tutorials found', 'tutorial-platform' ),
            ],
            'no_name' => [
                'status' => 400,
                'message' => __( 'No name provided', 'tutorial-platform' ),
            ],
            'no_content' => [
                'status' => 400,
                'message' => __( 'No content provided', 'tutorial-platform' ),
            ],
            'no_courses_found' => [
                'status' => 404,
                'message' => __( 'No courses found', 'tutorial-platform' ),
            ],
            'no_course_found' => [
                'status' => 404,
                'message' => __( 'No course found', 'tutorial-platform' ),
            ],
            'no_subjects_found' => [
                'status' => 404,
                'message' => __( 'No subjects found', 'tutorial-platform' ),
            ],
            'no_softwares_found' => [
                'status' => 404,
                'message' => __( 'No software found', 'tutorial-platform' ),
            ],
            'delete_failed' => [
                'status' => 500,
                'message' => __( 'Failed to delete', 'tutorial-platform' ),
            ],
            'id_or_name_required' => [
                'status' => 400,
                'message' => __( 'ID or name is required', 'tutorial-platform' ),
            ],
            'no_permission' => [
                'status' => 403,
                'message' => __( 'No permission for this action', 'tutorial-platform' ),
            ],
            'teacher_not_found' => [
                'status' => 404,
                'message' => __( 'Teacher not found', 'tutorial-platform' ),
            ],
            'teacher_empty' => [
                'status' => 400,
                'message' => __( 'Teacher is empty', 'tutorial-platform' ),
            ],
            'media_upload_failed' => [
                'status' => 400,
                'message' => __( 'Media upload failed', 'tutorial-platform' ),
            ],
            'no_preview' => [
                'status' => 404,
                'message' => __( 'No preview found', 'tutorial-platform' ),
            ],
            'auth_failed' => [
                'status' => 401,
                'message' => __( 'Authentication failed', 'tutorial-platform' ),
            ],
        ];
    }

    /**
     * Map of success codes to HTTP status codes and messages.
     * 
     * @since 1.0.0
     * 
     * @return array
     */
    private static function get_success_mapping(): array {
        return [
            'ok' => [
                'status' => 200,
                'message' => __( 'OK', 'tutorial-platform' ),
            ],
            'created' => [
                'status' => 201,
                'message' => __( 'Created', 'tutorial-platform' ),
            ],
            'no_content' => [
                'status' => 204,
                'message' => __( 'No Content', 'tutorial-platform' ),
            ],
        ];
    }
}