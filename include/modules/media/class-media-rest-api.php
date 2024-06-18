<?php

namespace TutorialPlatform\Modules\Media;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Common\Rest_Api;
use WP_REST_Server;
use WP_REST_Request;
use TuDelft\SurfShareKit\Inc\SurfShareKit;
use WP_Query;

//class Media_Rest_Api implements Interface_Rest_Api
class Media_Rest_Api {

    /**
     * Register routes
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public static function register_routes() {
        register_rest_route( Rest_Api::API_NAMESPACE, '/media', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_media' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        // total amount of media
        register_rest_route( Rest_Api::API_NAMESPACE, '/media/total', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'get_total_media' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        // upload media item
        register_rest_route( Rest_Api::API_NAMESPACE, '/media/upload', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [ self::class, 'upload_media' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );

        // search media items
        register_rest_route( Rest_Api::API_NAMESPACE, '/media/search', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [ self::class, 'search_media' ],
            'permission_callback' => function( $request ) {
                return Rest_Api::is_user_allowed( $request );
            },
        ] );
    }

    /**
     * Get all media items
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_media( WP_REST_Request $request ): mixed {

        $amount = $request->get_param( 'amount' ) ?? 12;
        $page = $request->get_param( 'page' ) ?? 1;

        // $data = SurfShareKit::get_items();

        // get items from  media library

        $args = [
            'post_type' => 'attachment',
            'posts_per_page' => $amount,
            'paged' => $page,
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        $query = new WP_Query( $args );

        $items = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();

                $items[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'description' => get_the_content(),
                    'url' => wp_get_attachment_url( get_the_ID() ),
                    'tags' => wp_get_post_tags( get_the_ID(), [ 'fields' => 'names' ] ),
                ];
            }
        }


        return $items;
    }

    /**
     * Get total amount of media items
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return int
     */
    public static function get_total_media( WP_REST_Request $request ): int {
        // get total items from media library

        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
        ];

        $query = new WP_Query( $args );

        return $query->found_posts;

    }

    /**
     * Upload media item
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function upload_media( WP_REST_Request $request ): mixed {

        $file = $request->get_file_params();

        $title = $request->get_param( 'title' ) ?? '';

        if ( empty( $file ) ) {
            return Rest_Api::send_error_response( 'media_upload_failed', 'No file uploaded' );
        }

        $file = reset( $file );

        $response = SurfShareKit::upload_media( $file, $title );

        if ( is_wp_error( $response ) ) {
            return Rest_Api::send_error_response( 'media_upload_failed', $response->get_error_message() );
        }

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $upload = wp_handle_upload($file, array('test_form' => false));

        if ($upload && !isset($upload['error'])) {
            $filename = $upload['file'];
            $filetype = wp_check_filetype($filename, null);
            $wp_upload_dir = wp_upload_dir();

            $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . basename($filename),
                'post_mime_type' => $filetype['type'],
                'post_title'     => sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $filename);

            // Generate attachment metadata and update database record.
            $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
            wp_update_attachment_metadata($attach_id, $attach_data);

            update_post_meta($attach_id, 'surfsharekit_id', $response->id );

            return Rest_Api::send_success_response( [
                'id' => $attach_id,
                'title' => $title,
                'url' => wp_get_attachment_url( $attach_id )
            ] );
        }
    }

    /**
     * Search media items
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function search_media( WP_REST_Request $request ): mixed {

        $search_term = $request->get_param( 'term' ) ?? '';

        // get items from media library

        $args = [
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'orderby' => 'date',
            'order' => 'DESC',
            's' => $search_term,
        ];

        $query = new WP_Query( $args );

        $items = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();

                $items[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'description' => get_the_content(),
                    'url' => wp_get_attachment_url( get_the_ID() ),
                    'tags' => wp_get_post_tags( get_the_ID(), [ 'fields' => 'names' ] ),
                ];
            }
        }

        return $items;
    }
}