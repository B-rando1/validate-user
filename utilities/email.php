<?php

if ( ! class_exists( 'ValidateUserEmailUtilities' ) ) {

	class ValidateUserEmailUtilities {

		public static function getAdminMacros(): array {

			return [
				'{from_name}'    => get_option( 'validate-user-admin-email-from-name', get_bloginfo( 'name' ) ),
				'{from_address}' => get_option( 'validate-user-admin-email-from-address', get_bloginfo( 'admin_email' ) ),
				'{to_name}'      => get_option( 'validate-user-admin-email-to-name', 'Site Admin' ),
				'{to_address}'   => get_option( 'validate-user-admin-email-to-address', get_bloginfo( 'admin_email' ) ),
				'{site_name}'    => get_bloginfo( 'name' ),
				'{site_url}'     => get_home_url()
			];

		}

		public static function insertAdminMacros( string $text, $extra_macros = [] ): string {

			$macros = self::getAdminMacros();

			$macros = array_merge( $macros, $extra_macros );

			foreach ( $macros as $key => $value ) {
				$text = str_replace( $key, $value, $text );
			}

			return $text;

		}

		public static function getClientMacros(): array {

			return [
				'{from_name}'    => get_option( 'validate-user-client-email-from-name', get_bloginfo( 'name' ) ),
				'{from_address}' => get_option( 'validate-user-client-email-from-address', get_bloginfo( 'admin_email' ) ),
				'{site_name}'    => get_bloginfo( 'name' ),
				'{site_url}'     => get_home_url()
			];

		}

		public static function insertClientMacros( string $text, $extra_macros = [] ): string {

			$macros = self::getClientMacros();

			$macros = array_merge( $macros, $extra_macros );

			foreach ( $macros as $key => $value ) {
				$text = str_replace( $key, $value, $text );
			}

			return $text;

		}

		public static function formatArray( array $arr ): string {

			$message = '';
			foreach ( $arr as $key => $value ) {

				if ( gettype( $value ) === 'array' ) {
					if ( ! empty( implode( ', ', $value ) ) ) {
						$message .= esc_html( $key ) . ': ' . esc_html( implode( ', ', $value ) ) . '<br>';
					}
				}
				else {
					if ( ! empty( trim( $value ) ) ) {
						$message .= esc_html( $key ) . ': ' . esc_html( $value ) . '<br>';
					}
				}

			}

			return trim( $message );

		}

	}

}