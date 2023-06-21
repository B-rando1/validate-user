<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', 'validate-user' ) );
}

if ( ! class_exists( 'ValidateUserEncryption' ) ) {

	class ValidateUserEncryption {

		private string $key;
		private string $salt;

		public function __construct() {
			$this->key = $this->getDefaultKey();
			$this->salt = $this->getDefaultSalt();
		}

		public function encrypt( string $value ): string|bool {

			if ( ! extension_loaded( 'openssl' ) ) {
				return $value;
			}

			$method = 'aes-256-xts';
			$ivlen = openssl_cipher_iv_length( $method );
			$iv = openssl_random_pseudo_bytes( $ivlen );

			$raw_value = openssl_encrypt( $value . $this->salt, $method, $this->key, 0, $iv );
			if ( ! $raw_value ) {
				return false;
			}

			return base64_encode( $iv . $raw_value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		}

		public function decrypt( string $raw_value ): string|bool {

			if ( ! extension_loaded( 'openssl' ) ) {
				return $raw_value;
			}

			$raw_value = base64_decode( $raw_value, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

			$method = 'aes-256-xts';
			$ivlen = openssl_cipher_iv_length( $method );

			$iv = substr( $raw_value, 0, $ivlen );
			$raw_value = substr( $raw_value, $ivlen );

			$value = openssl_decrypt( $raw_value, $method, $this->key, 0, $iv );
			if ( ! $value || ! str_ends_with( $value, $this->salt ) ) {
				return false;
			}

			return substr( $value, 0, - strlen( $this->salt ) );

		}

		private function getDefaultKey(): string {

			if ( defined( 'LOGGED_IN_KEY' ) && '' !== LOGGED_IN_KEY ) {
				return LOGGED_IN_KEY;
			}

			// If this is reached on a live site, it is a serious security problem.
			return 'this-is-a-fallback-key-but-not-secure';

		}

		private function getDefaultSalt(): string {

			if ( defined( 'LOGGED_IN_SALT' ) && '' !== LOGGED_IN_SALT ) {
				return LOGGED_IN_SALT;
			}

			// If this is reached on a live site, it is a serious security problem.
			return 'this-is-a-fallback-salt-but-not-secure';

		}

	}

}