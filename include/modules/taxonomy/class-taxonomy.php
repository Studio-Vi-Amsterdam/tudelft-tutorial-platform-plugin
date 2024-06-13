<?php

namespace TutorialPlatform\Modules\Taxonomy;

defined( 'ABSPATH' ) || die( "Can't access directly" );

class Taxonomy {

    /**
     * Get all keywords
     * 
     * @param bool $hide_empty Hide empty keywords default is false
     * @param bool $minimal Minimal response default is false
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_keywords( $hide_empty = false, $minimal = false ): mixed {
        $args = [
            'taxonomy' => 'keywords',
            'hide_empty' => $hide_empty,
            'orderby' => 'count',
            'order' => 'DESC',
        ];

        $keywords = get_terms( $args );

        // If minimal is true then return only id and title
        if ( $minimal ) {

            $response = [];

            foreach ( $keywords as $keyword ) {
                $response[] = [
                    'id' => $keyword->term_id,
                    'title' => $keyword->name,
                ];
            }

            return $response;
        }

        return $keywords;
    }

    /**
     * Get all teachers
     * 
     * @param bool $hide_empty Hide empty keywords default is false
     * @param bool $minimal Minimal response default is false
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_teachers( $hide_empty = false, $minimal = false ): mixed {

        $args = [
            'taxonomy' => 'teachers',
            'hide_empty' => $hide_empty,
            'orderby' => 'count',
            'order' => 'DESC',

        ];

        $teachers = get_terms( $args );

        // If minimal is true then return only id and title
        if ( $minimal ) {

            $response = [];

            foreach ( $teachers as $teacher ) {
                $response[] = [
                    'id' => $teacher->term_id,
                    'title' => $teacher->name,
                ];
            }

            return $response;
        }

        return $teachers;
    }

    /**
     * Get all software versions
     * 
     * @param bool $hide_empty Hide empty keywords default is false
     * @param bool $minimal Minimal response default is false
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_software_versions( $hide_empty = false, $minimal = false ): mixed {

        $args = [
            'taxonomy' => 'software-version',
            'hide_empty' => $hide_empty,
            'orderby' => 'count',
            'order' => 'DESC',

        ];

        $software_versions = get_terms( $args );

        // If minimal is true then return only id and title
        if ( $minimal ) {

            $response = [];

            foreach ( $software_versions as $software_version ) {
                $response[] = [
                    'id' => $software_version->term_id,
                    'title' => $software_version->name,
                ];
            }

            return $response;
        }

        return $software_versions;
    }

    /**
     * Get all categories
     * 
     * @param bool $hide_empty Hide empty keywords default is false
     * @param bool $minimal Minimal response default is false
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_categories( $hide_empty = false, $minimal = false ): mixed {

        $args = [
            'taxonomy' => 'category',
            'hide_empty' => $hide_empty,
            'orderby' => 'count',
            'order' => 'DESC',
        ];

        $categories = get_terms( $args );

        // filter out uncategorized
        $categories = array_filter( $categories, function( $category ) {
            return $category->term_id !== 1;
        } );
        // If minimal is true then return only id and title
        if ( $minimal ) {

            $response = [];

            foreach ( $categories as $category ) {
                $response[] = [
                    'id' => $category->term_id,
                    'title' => $category->name,
                ];
            }

            return $response;
        }

        return $categories;
    }

    /**
     * Get all academic levels
     * 
     * @param bool $hide_empty Hide empty keywords default is false
     * @param bool $children_only Return only children default is false
     * 
     * @since 1.0.0
     * 
     * @return mixed
     */
    public static function get_academic_levels( $hide_empty = false, $children_only = false ): mixed {

        $academic_levels = get_terms( [
            'taxonomy' => 'academic-level',
            'hide_empty' => $hide_empty,
            'exclude' => '1',
        ] );

        // If children_only is true then return only children
        if ( $children_only ) {

            $response = [];

            foreach ( $academic_levels as $academic_level ) {
                if ( $academic_level->parent !== 0 ) {
                    $response[] = [
                        'id' => $academic_level->term_id,
                        'title' => $academic_level->name,
                    ];
                }
            }

            return $response;
        }

        return $academic_levels;
    }
    
}