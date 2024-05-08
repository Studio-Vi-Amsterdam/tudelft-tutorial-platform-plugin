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

use WP_Error;


class Gutenberg {

    //TODO: merge with Theme class
    const BLOCKS = [
        'text_block',
        'image_block',
        'video_block',
        'download_block',
        'info_box_block',
        'content_card_block',
        'image_text_block',
        'text_image_block',
        'video_text_block',
        'text_video_block',
        // 'h5p_block',
    ];

    public function __construct() {

    }
    
    public static function update_gutenberg_data(array $data) {

        /**
         * data example
         * 
         * $data = [
         *      'post_id' => 1,
         *      'title' => 'Chapter 1',
         *      'content' => [
         *          [
         *             'block_name' => 'text_block',
         *             'block_data' => [
         *                  'show_chapter_subtitle' => true,
         *                  'show_chapter_title' => Test123,
         *                  'content' => 'This is a text block',
         **             ]
         *          ],
         *          [
         *            'block_name' => 'image_block',
         *             ....
         *          ]
         *      ]
         */
        
        if ( !$data['name'] ) {
            new WP_Error( 'no-name', 'No name provided', array( 'status' => 400 ) );
        }

        if ( !$data['content'] ) {
            new WP_Error( 'no-content', 'No content provided', array( 'status' => 400 ) );
        }

        if ( !function_exists('acf_register_block_type') ) {
            new WP_Error( 'no-acf', 'ACF not installed', array( 'status' => 400 ) );
        }
        
        $post_id = $data['post_id'] ? $data['post_id'] : null;

        $content = '';
        foreach ($data['content'] as $block) {
            if ( in_array($block['block_name'], self::BLOCKS) ) {
                $block_name = $block['block_name'];
                $block_data = $block['block_data'];
                $block_markup = self::generate_acf_gutenberg_block($block_data, $block_name);
                $content .= '\n' . $block_markup;
            }
        }

        $post_data = [
            'ID' => $post_id,
            'post_content' => $content,
        ];

        wp_update_post($post_data);
    }

    /**
     * Generate ACF Gutenberg block
     * 
     * @param array $data
     * @param string $block_name
     * 
     * @return string
     * 
     * @since 1.0.0
     * 
     * @see https://www.advancedcustomfields.com/resources/acf_register_block_type/
     */
    public function generate_acf_gutenberg_block( array $data, string $block_name ) {

        if ( empty( $data ) || !$block_name ) {
            return;
        }

        $attributes = [
            'name' => $block_name,
            'data' => $data,
            'mode' => 'edit',
        ];
        $attributes_json = wp_json_encode($attributes);
        $block_markup = sprintf('<!-- wp:%s %s /-->', esc_attr($block_name), $attributes_json);

        return $block_markup;
    }
}