<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', '/languages' ) );
}

if ( ! class_exists( 'ValidateUserCF7FormHandler' ) ) {

	class ValidateUserCF7FormHandler {

		// $instance, __construct() and getInstance() are used to implement the Singleton design pattern
		// ( makes sure there's always at most one instance of the class )
		private static ValidateUserCF7FormHandler|null $instance = null;

		private string|null $cf7StatusMessage = null;

		private function __construct() {
		}

		public static function getInstance(): ValidateUserCF7FormHandler {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		public static function setup(): void {

			$instance = self::getInstance();

			add_action( 'wpcf7_skip_mail', [ $instance, 'cf7SkipMail' ], 10, 2 );
			add_action( 'wpcf7_before_send_mail', [ $instance, 'cf7GetData' ], 10, 3 );

		}

		public function cf7SkipMail( $skip_mail, $contactForm ) {

			if ( 'cf7' !== get_option( 'validate-user-form-type', 'default' ) || $contactForm->id() != get_option( 'validate-user-cf7-form-id', '' ) ) {
				return $skip_mail;
			}

			return true;

		}

		public function cf7GetData( $contactForm, &$abort, $submission ): mixed {

			if ( 'cf7' !== get_option( 'validate-user-form-type', 'default' ) || $contactForm->id() != get_option( 'validate-user-cf7-form-id', '' ) ) {
				return $contactForm;
			}

			require_once( VALIDATE_USER_PATH . 'utilities/create-application.php' );
			$newApplication = new ValidateUserCreateApplication( $submission->get_posted_data() );
			$response = $newApplication->create();

			if ( $response->is_error() ) {
				$abort = true;
			}

			// Handle response and send it back to cf7
			$this->cf7StatusMessage = $response->data;
			add_filter( 'wpcf7_display_message', [ $this, 'cf7DisplayMessage' ] );

			return $contactForm;

		}

		public function cf7DisplayMessage( string $message ): string {

			if ( null !== $this->cf7StatusMessage ) {

				$message                = $this->cf7StatusMessage;
				$this->cf7StatusMessage = null;

			}

			remove_filter( 'wpcf7_display_message', [ $this, 'cf7DisplayMessage' ] );

			return $message;

		}

	}

}