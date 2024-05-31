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

    const BLOCKS = [
        'tu-delft-text',
        'tu-delft-image',
        'tu-delft-video',
        'tu-delft-download',
        'tu-delft-info-box',
        'tu-delft-content-card',
        'tu-delft-image-text',
        'tu-delft-text-image',
        'tu-delft-video-text',
        'tu-delft-text-video',
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
         *                  'show_chapter_subtitle' => 1,
         *                  'chapter_subtitle' => Test123,
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
     * Generate Gutenberg block for given block
     * 
     * @param array $block
     * 
     * example:
     *          [
     *              'block_name' => 'text_block',
     *              'block_data' => [
     *                  'show_chapter_subtitle' => 1,
     *                  'chapter_subtitle' => Test123,
     *                  'content' => 'This is a text block',
     *              ]
     *          ]
     * 
     * @return string
     * 
     * @since 1.0.0
     */
    public static function generate_gutenberg_block(array $block) {
        if ( !$block['block_name'] || !$block['block_data'] ) {
            return;
        }
        
        if ( in_array($block['block_name'], self::BLOCKS) ) {
            $block_name = $block['block_name'];
            $block_data = $block['block_data'];

            $data = [];
            
            // mapping block data

            /**
             * Correct format
             * 
             * <!-- wp:acf/tu-delft-text {"name":"acf/tu-delft-text","data":{"show_chapter_subtitle":"0","_show_chapter_subtitle":"show_chapter_subtitle","tu-delft-text_content":"ewaeewaewaeaw wae awe awe awe awe","_tu-delft-text_content":"tu-delft-text_content"},"mode":"edit"} /-->
             */
            foreach ($block_data as $key => $value) {
                if ( $key === 'show_chapter_subtitle' || $key === 'chapter_subtitle' ) {
                    $data[$key] = $value;
                    $data["_" . $key ] = $key;
                }
                else {
                    $data[$block['block_name'] . '_' . $key] = $value;
                    $data["_" . $block['block_name'] . '_' . $key] = $block['block_name'] . '_' . $key;
                }
                
                
            }
            $block_markup = self::generate_acf_gutenberg_block($data, $block_name);
            $block_markup .= "\n";

            return $block_markup;
        }
        else {
            return;
        }
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
    private static function generate_acf_gutenberg_block( array $data, string $block_name ) {

        if ( empty( $data ) || !$block_name ) {
            return;
        }

        $attributes = [
            'name' => "acf/{$block_name}",
            'data' => $data,
            'mode' => 'edit',
        ];
        $attributes_json = wp_json_encode($attributes);
        $block_markup = sprintf('<!-- wp:acf/%s %s /-->', esc_attr($block_name), $attributes_json);

        return $block_markup;
    }

    /**
     * Convert ACF Gutenberg block to block data
     * 
     * @param string $block_markup
     * 
     * @return array
     * 
     * @since 1.0.0
     */
    public static function parse_acf_gutenberg_block( string $block_markup ) {
        if ( !$block_markup ) {
            return;
        }

        $parsed_blocks = parse_blocks($block_markup);

        $final_data = [];

        foreach ($parsed_blocks as $block) {
            if ( $block['blockName'] ) {
                $block_name = str_replace('acf/', '', $block['blockName']);
                $block_data = $block['attrs']['data'];

                // remove _ prefix from keys
                $block_data = array_filter($block_data, function($key) {
                    return strpos($key, '_') !== 0;
                }, ARRAY_FILTER_USE_KEY);

                /**
                 * remove block name from keys. For example tu-delft-text_content (key) => content (key)
                 * 
                 * For storing in database we need to differentiate between content and images but on frontend we just send "content", "image", etc
                 */
                foreach ($block_data as $key => $value) {
                    $new_key = str_replace($block_name . '_', '', $key);
                    $block_data[$new_key] = $value;
                    if ( $key !== $new_key) {
                        unset($block_data[$key]);
                    }
                }
                
                $final_data[] = [
                    'block_name' => $block_name,
                    'block_data' => $block_data,
                ];
            }
        }

        return $final_data;
    }
}