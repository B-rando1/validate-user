<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', 'validate-user' ) );
}

if ( ! class_exists( 'ValidateUserCF7FormHandler' ) ) {

	class ValidateUserCF7FormHandler {

		// $instance, __construct() and getInstance() are used to implement the Singleton design pattern
		// ( makes sure there's always at most one instance of the class )
		private static ValidateUserCF7FormHandler|null $instance = null;

		private string|null $cf7StatusMessage = null;

		private function __construct() {
		}

		/**
		 * Gets the singleton instance
		 *
		 * @return ValidateUserCF7FormHandler The singleton instance
		 */
		public static function getInstance(): ValidateUserCF7FormHandler {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		/**
		 * Sets up the hooks for handling a CF7 form
		 *
		 * @return void
		 */
		public static function setup(): void {

			$instance = self::getInstance();

			add_action( 'wpcf7_skip_mail', [$instance, 'cf7SkipMail'], 10, 2 );
			add_action( 'wpcf7_before_send_mail', [$instance, 'cf7GetData'], 10, 3 );

		}

		/**
		 * Tells CF7 not to send an email for the form if it has been set as the form for validating new users
		 *
		 * @param bool $skip_mail The current value of whether to skip mail.
		 * @param object $contactForm The CF7 object for the form that was just submitted.
		 *
		 * @return bool
		 */
		public function cf7SkipMail( bool $skip_mail, object $contactForm ): bool {

			if ( 'cf7' !== get_option( 'validate-user-form-type', 'default' ) || $contactForm->id() != get_option( 'validate-user-cf7-form-id', '' ) ) {
				return $skip_mail;
			}

			return true;

		}

		/**
		 * Gets the data from a CF7 form if it has been set as the form for validating new users
		 *
		 * @param object $contactForm The CF7 object for the form that was just submitted.
		 * @param bool $abort Whether to give an error message to the form.
		 * @param object $submission The CF7 object for the form data that was just submitted.
		 *
		 * @return object
		 */
		public function cf7GetData( object $contactForm, bool &$abort, object $submission ): object {

			if ( 'cf7' !== get_option( 'validate-user-form-type', 'default' ) || $contactForm->id() != get_option( 'validate-user-cf7-form-id', '' ) ) {
				return $contactForm;
			}

			require_once( VALIDATE_USER_PATH . 'utilities/create-application.php' );
			$newApplication = new ValidateUserCreateApplication( $submission->get_posted_data() );
			$response       = $newApplication->create();

			if ( $response->is_error() ) {
				$abort = true;
			}

			// Handle response and send it back to cf7
			$this->cf7StatusMessage = $response->data;
			add_filter( 'wpcf7_display_message', [$this, 'cf7DisplayMessage'] );

			return $contactForm;

		}

		/**
		 * Overrides the CF7 message to give a custom response to the applicant.
		 *
		 * @param string $message The message to display on the front end.
		 *
		 * @return string The updated message to display on the front end.
		 */
		public function cf7DisplayMessage( string $message ): string {

			if ( null !== $this->cf7StatusMessage ) {

				$message                = $this->cf7StatusMessage;
				$this->cf7StatusMessage = null;

			}

			remove_filter( 'wpcf7_display_message', [$this, 'cf7DisplayMessage'] );

			return $message;

		}

	}

}