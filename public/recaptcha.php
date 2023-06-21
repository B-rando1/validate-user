<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', '/languages' ) );
}

if ( ! class_exists( 'ValidateUserReCaptcha' ) ) {

	class ValidateUserReCaptcha {

		// $instance, __construct() and getInstance() are used to implement the Singleton design pattern
		// ( makes sure there's always at most one instance of the class )
		private static ValidateUserReCaptcha|null $instance = null;

		private function __construct() {
		}

		public static function getInstance(): ValidateUserReCaptcha {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		public static function setup(): void {

			$instance = self::getInstance();

			add_action( 'wp_enqueue_scripts', [ $instance, 'enqueueScripts' ] );

		}

		public function enqueueScripts(): void {

			if ( '1' === get_option( 'validate-user-use-recaptcha', '0') && 'default' === get_option( 'validate-user-form-type', 'default' ) ) {

				wp_register_script( 'verify_user_recaptcha_script', 'https://www.google.com/recaptcha/api.js#asyncload', null, null, true );
				wp_enqueue_script( 'verify_user_recaptcha_script' );

			}

		}

	}

}