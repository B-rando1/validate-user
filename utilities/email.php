<?php

use JetBrains\PhpStorm\ArrayShape;

if ( ! class_exists( 'ValidateUserEmailUtilities' ) ) {

	class ValidateUserEmailUtilities {

		/**
		 * Gets the default macros for an email to the admin.
		 *
		 * @return array The macros, with keys as the macro and values as the text to replace the macro with.
		 */
		#[ArrayShape( [
			'{from_name}'    => "string",
            '{from_address}' => "string",
            '{to_name}'      => "string",
            '{to_address}'   => "string",
			'{site_name}'    => "string",
            '{site_url}'     => "string"
		] )] public static function getAdminMacros(): array {

			return [
				'{from_name}'    => get_option( 'validate-user-admin-email-from-name', get_bloginfo( 'name' ) ),
				'{from_address}' => get_option( 'validate-user-admin-email-from-address', get_bloginfo( 'admin_email' ) ),
				'{to_name}'      => get_option( 'validate-user-admin-email-to-name', 'Site Admin' ),
				'{to_address}'   => get_option( 'validate-user-admin-email-to-address', get_bloginfo( 'admin_email' ) ),
				'{site_name}'    => get_bloginfo( 'name' ),
				'{site_url}'     => get_home_url()
			];

		}

		/**
		 * Replaces macros with their replacement in a message to be sent to the admin.
		 *
		 * @param string $text The original message.
		 * @param array $extra_macros Any custom macros to look for as well.
		 *
		 * @return string The updated message.
		 */
		public static function insertAdminMacros( string $text, array $extra_macros = [] ): string {

			$macros = self::getAdminMacros();

			$macros = array_merge( $macros, $extra_macros );

			foreach ( $macros as $key => $value ) {
				$text = str_replace( $key, $value, $text );
			}

			return $text;

		}

		/**
		 * Gets the default macros for an email to a client.
		 *
		 * @return array The macros, with keys as the macro and values as the text to replace the macro with.
		 */
		#[ArrayShape( [
			'{from_name}'    => "string",
            '{from_address}' => "string",
            '{site_name}'    => "string",
            '{site_url}'     => "string"
		] )] public static function getClientMacros(): array {

			return [
				'{from_name}'    => get_option( 'validate-user-client-email-from-name', get_bloginfo( 'name' ) ),
				'{from_address}' => get_option( 'validate-user-client-email-from-address', get_bloginfo( 'admin_email' ) ),
				'{site_name}'    => get_bloginfo( 'name' ),
				'{site_url}'     => get_home_url()
			];

		}

		/**
		 * Replaces macros with their replacement in a message to be sent to a client.
		 *
		 * @param string $text The original message.
		 * @param array $extra_macros Any custom macros to look for as well.
		 *
		 * @return string The updated message.
		 */
		public static function insertClientMacros( string $text, array $extra_macros = [] ): string {

			$macros = self::getClientMacros();

			$macros = array_merge( $macros, $extra_macros );

			foreach ( $macros as $key => $value ) {
				$text = str_replace( $key, $value, $text );
			}

			return $text;

		}

		/**
		 * Represents an array as an html formatted string.
		 *
		 * @param array $arr The array to format as a string.
		 *
		 * @return string The array, formatted as a string.
		 */
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