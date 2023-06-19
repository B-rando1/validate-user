<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', '/languages' ) );
}

if ( ! class_exists( 'ValidateUserDefaultFormHandler' ) ) {

	class ValidateUserDefaultFormHandler {

		private static ValidateUserDefaultFormHandler|null $instance = null;

		private function __construct() {
		}

		public static function getInstance(): ValidateUserDefaultFormHandler {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		public static function setup(): void {

			$instance = self::getInstance();

			add_action( 'wp_enqueue_scripts', [ $instance, 'enqueueScripts' ] );
			add_action( 'rest_api_init', [ $instance, 'createRestEndpoint' ] );

		}

		public function enqueueScripts(): void {

			if ( 'default' === get_option( 'validate-user-form-type', 'default' ) ) {

				wp_register_script( 'validate_user_handle_enquiry', VALIDATE_USER_URL . '/js/handle-enquiry.js', [
					'jquery',
					'wp-i18n'
				] );
				wp_set_script_translations( 'validate_user_handle_enquiry', '/languages' );
				wp_localize_script( 'validate_user_handle_enquiry', 'testAjax', [ 'ajaxurl' => get_rest_url( null, 'v1/validate-user/submit' ) ] );
				wp_enqueue_script( 'validate_user_handle_enquiry' );

			}

		}

		public function createRestEndpoint(): void {

			if ( 'default' === get_option( 'validate-user-form-type', 'default' ) ) {

				register_rest_route( 'v1/validate-user', 'submit', [
					'methods'  => 'POST',
					'callback' => [ $this, 'handleEnquiry' ]
				] );

			}

		}

		public function handleEnquiry( $data ): WP_REST_Response {

			$params = $data->get_params();

			if ( ! wp_verify_nonce( $params['_wpnonce'], 'wp_rest' ) ) {
				return new WP_Rest_Response( esc_html__( 'Message not sent', '/languages' ), 422 );
			}

			unset( $params['_wpnonce'] );
			unset( $params['_wp_http_referer'] );

			// Check reCaptcha stuff
			if ( '1' === get_option( 'validate-user-use-recaptcha', '0' ) ) {

				if ( ! isset( $params['g-recaptcha-response'] ) ) {
					return new WP_REST_Response( esc_html__( 'Please check the reCaptcha box.', '/languages' ), 422 );
				}

				$reCAPTCHA = $params['g-recaptcha-response'];

				$secretAPIkey = get_option( 'validate-user-recaptcha-secret-key', '' );
				$user_ip      = $_SERVER['REMOTE_ADDR'];

				$verifyResponse = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretAPIkey . '&response=' . $reCAPTCHA . '&remoteip=' . $user_ip );
				$response       = json_decode( $verifyResponse );

				if ( ! $response->success ) {
					return new WP_REST_Response( esc_html__( 'Robot verification failed, please try again.', '/languages' ), 422 );
				}

			}

			unset( $params['g-recaptcha-response'] );

			require_once( VALIDATE_USER_PATH . 'utilities/create-application.php' );
			$newApplication = new ValidateUserCreateApplication( $params );
			return $newApplication->create();

		}

	}

}