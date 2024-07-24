<?php

namespace TutorialPlatform\Modules\User;

defined( 'ABSPATH' ) || die( "Can't access directly" );

class User {

    function __construct() {
        add_action( 'admin_init', [ $this, 'add_custom_preview_capability' ] );
    }

    /**
     * Add custom preview capability
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function add_custom_preview_capability() {
        $role = get_role( 'subscriber' );
        $role->add_cap( 'edit_posts' );
    }
}