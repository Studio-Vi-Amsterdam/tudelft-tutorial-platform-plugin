<?php

namespace TutorialPlatform\Abstracts;

defined( 'ABSPATH' ) || die( "Can't access directly" );

use TutorialPlatform\Common\Gutenberg;
use TutorialPlatform\Modules\Chapter\Chapter;
class Rest_Api {

    private const MODULE_STATUS = [
        'publish',
        'draft',
        'trash',
    ];

    /**
     * Get modules from tutor
     * 
     * @param string $type Module type
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_modules( string $type, int $amount = -1, string $status = 'publish' ): mixed {

        $current_user = wp_get_current_user();

        $args = [
            'post_type' => $type,
            'author' => $current_user->ID,
            'posts_per_page' => $amount,
            'post_status' => in_array( $status, self::MODULE_STATUS ) ? $status : 'publish',
        ];

        $modules = get_posts( $args );

        if ( !$modules ) {
            return [
                'error' => "no_{$type}s_found",
            ];
        }

        $modules = array_map( function( $module ) {
            return [
                'id' => $module->ID,
                'title' => $module->post_title,
                'publish_date' => $module->post_date,
            ];
        }, $modules );

        return $modules;
    }

    /**
     * Get single module for given id
     * 
     * @param $type Module type
     * @param $id Module ID
     * 
     * @since 1.0.0
     * 
     * @param WP_REST_Request $request
     * 
     * @return mixed
     */
    public static function get_single_module( string $type, int $id, array $fields = [], bool $has_chapters = true, bool $has_keywords = true ): mixed {
        
        $current_user = wp_get_current_user();
        
        if ( get_post_type( $id ) !== $type ) {
            return [
                'error' => "no_{$type}_found",
            ];
        }

        $module = get_post( $id );

        if ( !$module ) {
            return [
                'error' => "no_{$type}_found",
            ];
        }

        if ( $module->post_author != $current_user->ID ) {
            return [
                'error' => "no_permission",
            ];
        };

        $result_array = [
            'id' => $module->ID,
            'title' => $module->post_title,
            'content' => Gutenberg::parse_acf_gutenberg_block( $module->post_content ),
            'chapters' => $has_chapters ? Chapter::get_chapters( $module->ID ) : false,
            'publish_date' => $module->post_date,
            'keywords' => $has_keywords ? get_terms( [
                'taxonomy' => 'keywords',
                'object_ids' => $module->ID,
                'fields' => 'names',
            ] ) : false,
        ];

        foreach ( $fields as $acf_field => $api_field ) {
            $result_array[ $api_field ] = get_field( $acf_field, $module->ID );
        }

        return $result_array;
    }

    /**
     * Create module
     * 
     * @param string $type Module type
     * @param string $title Module title
     * @param string $status Module status
     * @param array $data Module data
     * 
     */
    public static function create_module( string $type, string $title, string $status, array $data = [], array $fields = [] ): bool|array {

        
        $content = '';

        if ( !empty( $data['content'] ) ) {
            /**
             * Example data:
             *   $data = [
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
            foreach ( $data['content'] as $block ) {
                $content .= Gutenberg::generate_gutenberg_block( $block );
            }
        }

        $post_data = [
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => in_array( $status, self::MODULE_STATUS ) ? $status : 'publish',
            'post_type' => $type,
        ];

        $post_id = wp_insert_post( $post_data );

        if ( !$post_id ) {
            return [
                'error' => "module_creation_failed",
            ];
        }

        if ( $data['keywords'] ) {

            $term_ids = [];

            foreach( $data['keywords'] as $keyword ) {
                if ( ! term_exists( $keyword, 'keywords' ) ) {
                    $term = wp_insert_term( $keyword, 'keywords' );
                    if ( ! is_wp_error( $term ) ) {
                        $term_ids[] = $term['term_id'];
                    }
                }
                else {
                    $term = get_term_by( 'name', $keyword, 'keywords' );
                    if ( $term ) {
                        $term_ids[] = $term->term_id;
                    }
                }
            }
            wp_set_post_terms( $post_id, $term_ids, 'keywords', true );
        }

        if ( !empty( $data['chapters'] ) ) {
            foreach ( $data['chapters'] as $chapter ) {
                $chapter_id = Chapter::create_chapter( $chapter['title'], $chapter['content'], $post_id );

                if ( $chapter_id ) {
                    $existing_chapters = get_field( 'chapters', $post_id, [] );
                    $existing_chapters[] = $chapter_id;
                    update_field( 'chapters', $existing_chapters, $post_id );
                }
            }
        }

        // Custom fields mapping
        foreach ( $fields as $acf_field => $api_field ) {
            if ( !empty( $data[ $api_field ] ) ) {
                update_field( $acf_field, $data[ $api_field ], $post_id );
            }
        }

        return true;
    }

    /**
     * Delete module by id
     * 
     * @param string $type Module type
     * @param int $id Module ID
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function delete_module( string $type, int $id ): bool|array {
        
        $current_user = wp_get_current_user();

        if ( get_post_type( $id ) !== $type ) {
            return [
                'error' => "no_{$type}_found",
            ];
        }

        $module = get_post( $id );

        if ( !$module ) {
            return [
                'error' => "no_{$type}_found",
            ];
        }

        if ( $module->post_author != $current_user->ID ) {
            return [
                'error' => "no_permission",
            ];
        };

        $chapters = get_field( 'chapters', $id, [] );

        if ( $chapters ) {
            foreach ( $chapters as $chapter_id ) {
                Chapter::delete_chapter( $chapter_id );
            }
        }

        $result = wp_delete_post( $id );

        if ( !$result ) {
            return [
                'error' => "delete_failed",
            ];
        }

        return true;
    }

    /**
     * Update module 
     * 
     * @param string $type Module type
     * @param int $id Module ID
     * @param array $data Module data
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function update_module( string $type, int $id, array $data = [], array $fields = [] ): bool|array {
        
        $current_user = wp_get_current_user();

        if ( get_post_type( $id ) !== $type ) {
            return [
                'error' => "no_{$type}_found",
            ];
        }

        $module = get_post( $id );

        if ( !$module ) {
            return [
                'error' => "no_{$type}_found",
            ];
        }

        if ( $module->post_author != $current_user->ID ) {
            return [
                'error' => "no_permission",
            ];
        };

        if ( !empty( $data['content'] ) ) {
            $content = '';

            foreach ( $data['content'] as $block ) {
                $content .= Gutenberg::generate_gutenberg_block( $block );
            }

            wp_update_post( [
                'ID' => $id,
                'post_content' => $content,
            ] );
        }

        if ( $data['keywords'] ) {

            $term_ids = [];

            foreach( $data['keywords'] as $keyword ) {
                if ( ! term_exists( $keyword, 'keywords' ) ) {
                    $term = wp_insert_term( $keyword, 'keywords' );
                    if ( ! is_wp_error( $term ) ) {
                        $term_ids[] = $term['term_id'];
                    }
                }
                else {
                    $term = get_term_by( 'name', $keyword, 'keywords' );
                    if ( $term ) {
                        $term_ids[] = $term->term_id;
                    }
                }
            }
            wp_set_post_terms( $id, $term_ids, 'keywords', true );
        }

        if ( !empty( $data['chapters'] ) ) {
            foreach ( $data['chapters'] as $chapter ) {
                $chapter_id = Chapter::create_chapter( $chapter['title'], $chapter['content'], $id );

                if ( $chapter_id ) {
                    $existing_chapters = get_field( 'chapters', $id, [] );
                    $existing_chapters[] = $chapter_id;
                    update_field( 'chapters', $existing_chapters, $id );
                }
            }
        }

        // Custom fields mapping
        foreach ( $fields as $acf_field => $api_field ) {
            if ( !empty( $data[ $api_field ] ) ) {
                update_field( $acf_field, $data[ $api_field ], $id );
            }
        }

    }
    
}