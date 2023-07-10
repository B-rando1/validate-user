<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', 'validate-user' ) );
}

if ( ! class_exists( 'ValidateUserDefaultFormHandler' ) ) {

	class ValidateUserDefaultFormHandler {

		// $instance, __construct() and getInstance() are used to implement the Singleton design pattern
		// ( makes sure there's always at most one instance of the class )
		private static ValidateUserDefaultFormHandler|null $instance = null;

		private function __construct() {
		}

		/**
		 * Gets the singleton instance
		 *
		 * @return ValidateUserDefaultFormHandler The singleton instance
		 */
		public static function getInstance(): ValidateUserDefaultFormHandler {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		/**
		 * Sets up the hooks for handling the default form
		 *
		 * @return void
		 */
		public static function setup(): void {

			$instance = self::getInstance();

			add_action( 'wp_enqueue_scripts', [$instance, 'enqueueScripts'] );
			add_action( 'rest_api_init', [$instance, 'createRestEndpoint'] );

		}

		/**
		 * Enqueues the JS script for handling submission on the default form
		 *
		 * @return void
		 */
		public function enqueueScripts(): void {

			if ( 'default' === get_option( 'validate-user-form-type', 'default' ) ) {

				wp_register_script( 'validate_user_handle_enquiry', VALIDATE_USER_URL . '/js/handle-enquiry.js', [
					'jquery',
					'wp-i18n'
				] );
				wp_set_script_translations( 'validate_user_handle_enquiry', 'validate-user' );
				wp_localize_script( 'validate_user_handle_enquiry', 'testAjax', ['ajaxurl' => get_rest_url( null, 'v1/validate-user/submit' )] );
				wp_enqueue_script( 'validate_user_handle_enquiry' );

			}

		}

		/**
		 * Creates the REST endpoint that the default form submits its data to
		 *
		 * @return void
		 */
		public function createRestEndpoint(): void {

			if ( 'default' === get_option( 'validate-user-form-type', 'default' ) ) {

				register_rest_route( 'v1/validate-user', 'submit', [
					'methods'  => 'POST',
					'callback' => [$this, 'handleEnquiry']
				] );

			}

		}

		/**
		 * Takes form data, makes sure it is valid, and creates an application
		 *
		 * @param object $data The form data object.  Must include keys 'username' and 'email'
		 *
		 * @return WP_REST_Response
		 */
		public function handleEnquiry( object $data ): WP_REST_Response {

			$params = $data->get_params();

			if ( ! wp_verify_nonce( $params['_wpnonce'], 'wp_rest' ) ) {
				return new WP_Rest_Response( esc_html__( 'Message not sent', 'validate-user' ), 422 );
			}

			unset( $params['_wpnonce'] );
			unset( $params['_wp_http_referer'] );

			// Check reCaptcha stuff
			if ( '1' === get_option( 'validate-user-use-recaptcha', '0' ) ) {

				if ( ! isset( $params['g-recaptcha-response'] ) ) {
					return new WP_REST_Response( esc_html__( 'Please check the reCaptcha box.', 'validate-user' ), 422 );
				}

				$reCAPTCHA = $params['g-recaptcha-response'];

				$secretAPIkey = get_option( 'validate-user-recaptcha-secret-key', '' );
				$user_ip      = $_SERVER['REMOTE_ADDR'];

				$verifyResponse = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretAPIkey . '&response=' . $reCAPTCHA . '&remoteip=' . $user_ip );
				$response       = json_decode( $verifyResponse );

				if ( ! $response->success ) {
					return new WP_REST_Response( esc_html__( 'Robot verification failed, please try again.', 'validate-user' ), 422 );
				}

			}

			unset( $params['g-recaptcha-response'] );

			require_once( VALIDATE_USER_PATH . 'utilities/create-application.php' );
			$newApplication = new ValidateUserCreateApplication( $params );

			return $newApplication->create();

		}

	}

}