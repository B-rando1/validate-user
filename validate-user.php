<?php

/**
 * Plugin Name: Validate User
 * Description: A WordPress plugin for facilitating new user applications when admin validation is required. I made it to grow my WordPress plugin development skills. Note that I don't currently have plans to maintain it, so use it at your own risk.
 * Plugin URI: https://github.com/B-rando1/validate-user
 * Author: Brandon Bosman
 * Author URI: https://github.com/B-rando1
 * Version: 0.1
 * Update URI: localhost:8888/wordpress
 * Requires at least: 5.9.0
 * Tested up to: 6.2.2
 * Requires PHP: 8.1
 * Text Domain: validate-user
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', 'validate-user' ) );
}

if ( ! class_exists( 'ValidateUser' ) ) {

	class ValidateUser {

		// $instance, __construct() and getInstance() are used to implement the Singleton design pattern
		// ( makes sure there's always at most one instance of the class )
		private static ValidateUser|null $instance = null;

		private function __construct() {
		}

		/**
		 * Gets the singleton instance
		 *
		 * @return ValidateUser The singleton instance
		 */
		public static function getInstance(): ValidateUser {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		/**
		 * Includes the main plugin files and enqueues global js and css
		 *
		 * @return void
		 */
		public static function setup(): void {

			$instance = self::getInstance();

			define( 'VALIDATE_USER_PATH', plugin_dir_path( __FILE__ ) );
			define( 'VALIDATE_USER_URL', plugin_dir_url( __FILE__ ) );

			// Make sure all the main classes exist
			require_once( VALIDATE_USER_PATH . 'public/shortcode.php' );
			require_once( VALIDATE_USER_PATH . 'public/gutenberg-block.php' );
			require_once( VALIDATE_USER_PATH . 'public/classic-widget.php' );
			require_once( VALIDATE_USER_PATH . 'public/recaptcha.php' );

			require_once( VALIDATE_USER_PATH . 'public/default-form-handler.php' );
			require_once( VALIDATE_USER_PATH . 'public/cf7-form-handler.php' );

			require_once( VALIDATE_USER_PATH . 'admin/applications.php' );
			require_once( VALIDATE_USER_PATH . 'admin/user-editor.php' );
			require_once( VALIDATE_USER_PATH . 'admin/settings.php' );

			// Run the setup method for all the main classes
			ValidateUserShortcode::setup();
			ValidateUserGutenbergBlock::setup();
			ValidateUserReCaptcha::setup();

			ValidateUserDefaultFormHandler::setup();
			ValidateUserCF7FormHandler::setup();

			ValidateUserApplications::setup();
			ValidateUserUserEditor::setup();
			ValidateUserSettings::setup();

			add_action( 'admin_enqueue_scripts', [$instance, 'enqueueGlobalScripts'] );
			add_action( 'wp_enqueue_scripts', [$instance, 'enqueueGlobalScripts'] );
			add_filter( 'clean_url', [$instance, 'addAsyncToScript'], 11, 1 );
		}

		/**
		 * Adds any JS and CSS that always appear everywhere on the site
		 *
		 * @return void
		 */
		public function enqueueGlobalScripts(): void {

			wp_register_style( 'validate_user_shortcode_css', VALIDATE_USER_URL . '/css/style.css' );
			wp_enqueue_style( 'validate_user_shortcode_css' );

			wp_register_script( 'validate_user_global_script', VALIDATE_USER_URL . '/js/global.js', ['jquery'] );
			wp_enqueue_script( 'validate_user_global_script' );

		}

		/**
		 * Adds an async attribute to any scripts with #asyncload at the end
		 *
		 * @param string $url The script url that is being added
		 *
		 * @return string
		 */
		public function addAsyncToScript( string $url ): string {

			if ( ! str_contains( $url, '#asyncload' ) ) {
				return $url;
			}
			if ( is_admin() ) {
				return str_replace( '#asyncload', '', $url );
			}

			return str_replace( '#asyncload', '', $url ) . "' async='async";

		}

	}

}

ValidateUser::setup();