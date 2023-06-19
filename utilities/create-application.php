<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', '/languages' ) );
}

if ( ! class_exists( 'ValidateUserCreateApplication' ) ) {

	class ValidateUserCreateApplication {

		private array $data;

		public function __construct( $data ) {
			$this->data = $data;
		}
		
		public function create(): WP_REST_Response {

			// Check for collisions with  usernames/emails in existing users/applications:
			$validCredentials = $this->areValidCredentials(
				sanitize_text_field( $this->data['username'] ),
				sanitize_email( $this->data['email'] )
			);
			if ( true !== $validCredentials ) {
				return $validCredentials;
			}

			// Create Application (Custom Post Type)
			$args = [
				'post_type'   => 'validate-apps',
				'post_title'  => sanitize_text_field( $this->data['username'] ),
				'post_status' => 'publish'
			];

			$new_post = wp_insert_post( $args );

			// Add post meta to be viewable on application page
			$username = sanitize_text_field( $this->data['username'] );
			unset( $this->data['username'] );
			add_post_meta( $new_post, 'username', $username );

			$email = sanitize_email( $this->data['email'] );
			unset( $this->data['email'] );
			add_post_meta( $new_post, 'email', $email );

			if ( isset( $this->data['message']) ) {
				if ( ! empty( trim( $this->data['message'] ) ) ) {

					$form_message = wp_kses( nl2br( $this->data['message'] ), [ 'br' => [] ] );
					add_post_meta( $new_post, 'message', $form_message );

				}
				unset( $this->data['message'] );
			}

			foreach ( $this->data as $key => $value ) {

				if ( gettype( $value ) === 'array' ) {
					if ( ! empty( implode( ', ', $value ) ) ) {
						add_post_meta( $new_post, sanitize_text_field( $key ), sanitize_text_field( implode( ', ', $value ) ) );
					}
				}
				else {
					if ( ! empty( trim( $value ) ) ) {
						add_post_meta( $new_post, sanitize_text_field( $key ), sanitize_text_field( $value ) );
					}
				}

			}

			// Send email to admin
			if ( '1' === get_option( 'validate-user-admin-email-send', '1' ) ) {

				$sent = $this->sendAdminEmail( $username, $email, $new_post, $form_message ?? null );
				if ( ! $sent ) {
					return new WP_Rest_Response( esc_html__( 'Failed to send admin message', '/languages' ), 500 );
				}

			}

			// Send a success message
			return new WP_REST_Response( esc_html__( 'Your request has been successfully sent', '/languages' ), 200 );
			
		}

		private function areValidCredentials( $username, $email ): bool|WP_REST_Response {

			// Check for empty username or email
			if ( empty( trim( $username ) ) ) {
				return new WP_REST_Response( esc_html__( 'Username field must be filled out', '/languages' ), 422 );
			}
			if ( empty( trim( $email ) ) ) {
				return new WP_REST_Response( esc_html__( 'Email field must be filled out', '/languages' ), 422 );
			}

			// Check for collisions with existing users:
			if ( false !== username_exists( $username ) ) {
				return new WP_REST_Response( esc_html__( 'That username is already taken', '/languages' ), 422 );
			}
			if ( false !== email_exists( $email ) ) {
				return new WP_REST_Response( esc_html__( 'There is already a user with that email', '/languages' ), 422 );
			}

			// Check for collisions with existing applications:
			$application_ids = get_posts([
				'fields' => 'ids',
				'numberposts' => -1,
				'post_type' => 'validate-apps'
			]);

			foreach ( $application_ids as $id ) {

				if ( strtolower( get_post_meta( $id, 'username', true ) ) === strtolower( $username ) ) {
					return new WP_REST_Response( esc_html__( 'That username is already taken', '/languages' ), 422 );
				}
				if ( strtolower( get_post_meta( $id, 'email', true ) ) === strtolower( $email ) ) {
					return new WP_REST_Response( esc_html__( 'There is already an application with that email', '/languages' ), 422 );
				}

			}

			return true;

		}

		private function sendAdminEmail( string $username, string $email, string $post_id, string $form_message=null ): bool {

			$admin_email = get_bloginfo( 'admin_email' );
			$admin_name  = get_bloginfo( 'name' );

			$from_name  = get_option( 'validate-user-admin-email-from-name', $admin_email );
			$from_email = get_option( 'validate-user-admin-email-from-address', $admin_email );
			$to_name    = get_option( 'validate-user-admin-email-to-name', 'Site Admin' );
			$to_email   = get_option( 'validate-user-admin-email-to-address', get_bloginfo( 'admin_email' ) );

			$subject   = "$admin_name - New User Application";
			$headers   = [];
			$headers[] = "From: $from_name <$from_email>";
			$headers[] = "To: $to_name <$to_email>";
			$headers[] = "Content-Type: text/html";

			$application_link = get_edit_post_link( $post_id );

			require_once( VALIDATE_USER_PATH . '/templates/email-templates.php' );
			$message = ValidateUserEmailTemplates::adminEmailTemplate();
			$message = get_option( 'validate-user-admin-email-message', $message );

			include_once( VALIDATE_USER_PATH . '/utilities/email.php' );

			$macros = [
				'{username}'         => esc_html( $username ),
				'{email}'            => esc_html( $email ),
				'{application_link}' => $application_link,
				'{other-info}'           => ValidateUserEmailUtilities::formatArray( $this->data )
			];
			if ( null !== $form_message ) {
				$macros['{message}'] = $form_message;
			}
			else {
				$macros['{message}'] = 'No message entered';
			}
			foreach ( $this->data as $key => $value ) {

				$macroKey = esc_html( $key );
				if ( gettype( $value ) === 'array' ) {
					if ( ! empty( implode( ', ', $value ) ) ) {
						$macros["{{$macroKey}}"] = esc_html( ( implode( ', ', $value ) ) );
					}
				}
				else {
					if ( ! empty( trim( $value ) ) ) {
						$macros["{{$macroKey}}"] = esc_html( $value );
					}
				}

			}

			$message = ValidateUserEmailUtilities::insertAdminMacros(
				$message,
				$macros
			);

			$message  = apply_filters( 'validate-user-admin-message', $message, $post_id );

			return wp_mail( $to_email, $subject, $message, $headers );

		}

	}

}