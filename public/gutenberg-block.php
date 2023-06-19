<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', '/languages' ) );
}

if ( ! class_exists( 'ValidateUserGutenbergBlock' ) ) {

	class ValidateUserGutenbergBlock {

		private static ValidateUserGutenbergBlock|null $instance = null;

		private function __construct() {
		}

		public static function getInstance(): ValidateUserGutenbergBlock {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		public static function setup(): void {

			$instance = self::getInstance();

			add_action( 'init', [$instance, 'registerBlock'] );

		}

		public function registerBlock(): void {

			// automatically load dependencies and version
			$asset_file = include( VALIDATE_USER_PATH . 'build/index.asset.php' );

			$script_handle = 'validate-user-block-script';

			wp_register_script(
				$script_handle,
				VALIDATE_USER_URL . 'build/index.js',
				$asset_file['dependencies'],
				$asset_file['version']
			);

			if ( '1' === get_option( 'validate-user-use-recaptcha', '0' ) ) {
				$public_key = get_option( 'validate-user-recaptcha-public-key', '' );
			}
			else {
				$public_key = '';
			}

			wp_localize_script( $script_handle, 'args',
				[ 'wp_nonce' => wp_create_nonce( 'wp_rest' ),
				  'recaptcha_public_key' => $public_key]
			);

			register_block_type( 'validate-user/gutenberg-block', [
				'editor_script' => $script_handle,
				'render_callback' => [ $this, 'blockHTML' ]
			] );

		}

		public function blockHTML(): string {

			ob_start();
			include( VALIDATE_USER_PATH . 'templates/application-form.php' );
			return ob_get_clean();

		}

	}

}