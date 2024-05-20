<?php

namespace TutorialPlatform\Modules\Chapter;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Common\Gutenberg;

class Chapter {

    public function __construct() {
        // Do nothing
    }

    /**
     * Create chapter
     * 
     * @param string $title
     * @param array $content_blocks
     * @param int $parent_id
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function create_chapter(string $title, array $content_blocks, int $parent_id ): mixed { 
        
        if ( ! $title ) {
            return false;
        }

        $content = '';

        if ( $content_blocks ) {
            /**
             * Example data:
             * $data = [
             *      [
             *          'block_name' => 'text_block',
             *          'block_data' => [
             *             'show_chapter_subtitle' => 1,
             *             'chapter_subtitle' => Test123,
             *             'content' => 'This is a text block',
             *          ]
             *      ],
             *      ...
             * 
             * ]
             */
            foreach ( $content_blocks as $block ) {
                $content .= Gutenberg::generate_gutenberg_block( $block );
            }
        }

        $chapter_id = wp_insert_post( [
            'post_title' => $title,
            'post_content' => $content,
            'post_type' => 'chapter',
            'post_status' => 'publish',
        ] );

        if ( is_wp_error( $chapter_id ) ) {
            return false;
        }

        if ( $parent_id ) {
            update_field( 'belongs_to', $parent_id, $chapter_id );
        }

        return $chapter_id;
    }

    /**
     * Get chapters for given parent_id
     * 
     * @param int $parent_id 
     * 
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_chapters( int $parent_id ): array {
        
        if ( ! $parent_id ) {
            return [
                'error' => 'id_required'
            ];
        }

        $chapters = get_field( 'chapters', $parent_id );

        if ( ! $chapters ) {
            return [
                'error' => 'no_chapters_found'
            ];
        }

        $chapters = array_map( function( $chapter ) {
            return [
                'id' => $chapter,
                'title' => get_the_title( $chapter ),
                'permalink' => get_permalink( $chapter ),
            ];
        }, $chapters );

        return $chapters;
    }

    /**
     * Delete chapter
     * 
     * @param int $id
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function delete_chapter( int $id ): mixed {
        
        if ( ! $id ) {
            return [
                'error' => 'id_required'
            ];
        }

        $chapter = get_post( $id );

        if ( ! $chapter ) {
            return [
                'error' => 'no_chapter_found'
            ];
        }

        $result = wp_delete_post( $id );

        if ( ! $result ) {
            return [
                'error' => 'delete_failed'
            ];
        }

        return true;
    }
}