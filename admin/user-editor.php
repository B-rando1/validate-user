<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( esc_html__( 'Access Denied', '/languages' ) );
}

if ( ! class_exists( 'ValidateUserEditor' ) ) {

	class ValidateUserUserEditor {

		private static ValidateUserUserEditor|null $instance = null;

		private function __construct() {
		}

		public static function getInstance(): ValidateUserUserEditor {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		public static function setup(): void {

			$instance = self::getInstance();

			add_action( 'show_user_profile', [ $instance, 'displayUserMeta' ] );
			add_action( 'edit_user_profile', [ $instance, 'displayUserMeta' ] );
			add_action( 'user_new_form', [ $instance, 'createUserBusiness' ] );

			add_action( 'personal_options_update', [ $instance, 'saveUserBusiness' ] );
			add_action( 'edit_user_profile_update', [ $instance, 'saveUserBusiness' ] );

		}

		public function displayUserMeta( $profile_user ): void {

			$user_meta = get_user_meta( $profile_user->ID );

			include( VALIDATE_USER_PATH . 'templates/user-editor.php' );

		}

		public function createUserBusiness( $type ): void {

			$business = '';
			include( VALIDATE_USER_PATH . 'templates/user-editor.php' );

		}

		public function saveUserBusiness( $user_id ): void {

			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				return;
			}

			foreach ( $_REQUEST as $key => $value ) {

				if ( ! str_starts_with( $key, 'validate-user-' ) ) {
					continue;
				}

				// Adding new information
				if ( $key === 'validate-user-add-user-key' ) {
					continue;
				}
				if ( $key === 'validate-user-add-user-value' ) {
					$key = 'validate-user-' . $_REQUEST['validate-user-add-user-key'];
				}

				// Deleting existing information
				if ( empty( trim( $value ) ) ) {

					if ( metadata_exists( 'user', $user_id, sanitize_text_field( $key ) ) ) {
						delete_user_meta( $user_id, $key );
					}
					continue;

				}

				update_user_meta( $user_id, sanitize_text_field( $key ), sanitize_text_field( $value ) );

			}

		}

	}

}