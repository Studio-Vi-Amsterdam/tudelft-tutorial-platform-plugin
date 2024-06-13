<?php
/**
 * Plugin Name:         TuDelft Tutorial Platform API Connector
 * Plugin URI:          https://github.com/Studio-Vi-Amsterdam/tudelft-tutorial-platform-plugin
 * Description:         A plugin that serves TuDelft Teacher's Tutorial Platform
 * Version:             1.0.0
 * Author:              Aljosa K.
 * Author URI:          https://viamsterdam.com/
 * Requires PHP:        7.3
 * Requires at least:   5.8
 */

namespace TutorialPlatform;

use TutorialPlatform\Common\Gutenberg;
use TutorialPlatform\Common\Rest_Api;
use TutorialPlatform\Common\Auth;
use TutorialPlatform\Modules\Chapter\Chapter;
use TutorialPlatform\Modules\Chapter\Chapter_Rest_Api;
use TutorialPlatform\Modules\Tutorial\Tutorial;
use TutorialPlatform\Modules\Tutorial\Tutorial_Rest_Api;
use TutorialPlatform\Modules\Media\Media;
use TutorialPlatform\Modules\Media\Media_Rest_Api;
use TutorialPlatform\Modules\Subject\Subject;
use TutorialPlatform\Modules\Subject\Subject_Rest_Api;

defined( 'ABSPATH' ) || die( "Can't access directly" );

// require_once __DIR__ . '/include/class-api.php';

if ( ! class_exists( 'TutorialPlatform' ) ) {
    class TutorialPlatform {

        /**
		 * The plugin version number.
		 *
		 * @var string
		 */
		public $version = '1.0.0';


        public function __construct() {
            // Do nothing
        }

        /**
		 * Sets up the TutorialPlatform plugin.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function initialize() {
            // Define constants.
			define( 'TUTORIAL_PLATFORM_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'TUTORIAL_PLATFORM', true );
            $this->define( 'TUTORIAL_PLATFORM_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'TUTORIAL_PLATFORM_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'TUTORIAL_PLATFORM_VERSION', $this->version );

			add_action( 'init', [$this, 'load' ], 1, 0 );
        }

        /**
		 * Defines a constant if doesnt already exist.
		 *
		 * @since   1.0.0
		 *
		 * @param   string $name The constant name.
		 * @param   mixed  $value The constant value.
		 * @return  void
		 */
		public function define( $name, $value = true ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Load Plugin classes
		 * 
		 * @return void
		 * 
		 * @since 1.0.0
		 */
		public function load(): void {

			$this->load_classes();
			$this->init_classes();
		}

		/**
		 * Load plugin classes
		 * 
		 * @since 1.0.0
		 * 
		 * @return void
		 */
		public function load_classes(): void {

			// include autoload for modules
			require_once TUTORIAL_PLATFORM_PATH . 'lib/class-autoload.php';
			
			$autoload = new Autoload();
			$autoload->register();

			/**
			 * Load classes shared between modules
			 */       
			$autoload->addNamespace( 
				'TutorialPlatform\Common', 
				TUTORIAL_PLATFORM_PATH . '/include/common' 
			);

			/**
			 * Load modules
			 */
			$autoload->addNamespace( 
				'TutorialPlatform\Modules', 
				TUTORIAL_PLATFORM_PATH . '/include/modules'
			);

			/**
			 * Load abstract classes
			 */
			$autoload->addNamespace(
				'TutorialPlatform\Abstracts',
				TUTORIAL_PLATFORM_PATH . '/include/abstracts'
			);
		}

		/**
		 * Init plugin classes
		 * 
		 * @since 1.0.0
		 * 
		 * @return void
		 */
		public function init_classes(): void {
			
			// Init common classes
			new Rest_Api();
			new Gutenberg();
			new Auth();

			// Init modules
			new Chapter_Rest_Api();
			new Chapter();
			new Tutorial_Rest_Api();
			new Tutorial();
			new Subject_Rest_Api();
			new Subject();
			new Media_Rest_Api();
			new Media();
		}


    }

	/**
	 * Returns the main instance of TutorialPlatform.
	 * 
	 * @since 1.0.0
	 * 
	 */
    function TutorialPlatform() {
		global $tutorialPlatform;

		// Instantiate only once.
		if ( ! isset( $tutorialPlatform ) ) {
			$tutorialPlatform = new TutorialPlatform();
			$tutorialPlatform->initialize();
		}
		return $tutorialPlatform;
	}

	// Instantiate.
	TutorialPlatform();
}