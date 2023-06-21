<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', 'validate-user' ) );
}

if ( ! class_exists( 'ValidateUserShortCode' ) ) {

	class ValidateUserShortcode {

		// $instance, __construct() and getInstance() are used to implement the Singleton design pattern
		// ( makes sure there's always at most one instance of the class )
		private static ValidateUserShortcode|null $instance = null;

		private function __construct() {
		}

		/**
		 * Gets the singleton instance
		 *
		 * @return ValidateUserShortcode The singleton instance
		 */
		public static function getInstance(): ValidateUserShortcode {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		public static function setup(): void {

			$instance = self::getInstance();

			add_shortcode( 'validate_user_form', [ $instance, 'formHTML' ] );

		}

		public function formHTML(): string {

			ob_start();
			include( VALIDATE_USER_PATH . 'templates/application-form.php' );

			return ob_get_clean();

		}

	}
}