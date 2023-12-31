<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', 'validate-user' ) );
}

if ( ! class_exists( 'ValidateUserSettings' ) ) {

	class ValidateUserSettings {

		// $instance, __construct() and getInstance() are used to implement the Singleton design pattern
		// ( makes sure there's always at most one instance of the class )
		private static ValidateUserSettings|null $instance = null;

		private function __construct() {
		}

		/**
		 * Gets the singleton instance
		 *
		 * @return ValidateUserSettings The singleton instance
		 */
		public static function getInstance(): ValidateUserSettings {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		/**
		 * Sets up hooks for the Validate User settings panel.
		 *
		 * @return void
		 */
		public static function setup(): void {

			$instance = self::getInstance();

			add_action( 'admin_enqueue_scripts', [$instance, 'enqueueScripts'] );

			add_action( 'admin_init', [$instance, 'createSettings'] );

			add_action( 'pre_update_option_validate-user-recaptcha-secret-key', [$instance, 'encryptOption'], 10, 2 );
			add_action( 'option_validate-user-recaptcha-secret-key', [$instance, 'decryptOption'], 10, 2 );

			add_action( 'admin_menu', [$instance, 'addOptionsPages'] );

			add_action( 'wpcf7_verify_nonce', '__return_true' );

		}

		/**
		 * Enqueues JS for the Validate User settings panel.
		 *
		 * @param string $hook The hook for the admin panel.
		 *
		 * @return void
		 */
		public function enqueueScripts( string $hook ): void {

			if ( 'toplevel_page_validate-user' === $hook ) {

				wp_register_script( 'validate_user_settings_page_script', VALIDATE_USER_URL . '/js/settings-page.js', [
					'jquery',
					'validate_user_global_script'
				] );
				wp_enqueue_script( 'validate_user_settings_page_script' );

			}

		}

		/**
		 * Registers the settings fields.
		 *
		 * @return void
		 */
		public function createSettings(): void {

			require_once( VALIDATE_USER_PATH . '/templates/email-templates.php' );

			$settings_group = 'validate-user';

			/// Form Settings
			$settings_section = 'validate-user-form-settings';

			// Add settings section
			add_settings_section(
				$settings_section,
				esc_html__( 'Form Settings', 'validate-user' ),
				'',
				$settings_group
			);

			// Register Settings
			$args     = [
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => - 1
			];
			$cf7Forms = get_posts( $args );
			$cf7Ids   = wp_list_pluck( $cf7Forms, 'post_title', 'ID' );

			$settings = [
				[
					'name'              => 'validate-user-form-title',
					'display_name'      => esc_html__( 'Form Title', 'validate-user' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => esc_html__( 'Apply to Be a User', 'validate-user' ),
					'after_form'        => esc_html__( 'Leave empty for no title.' )
				],
				[
					'name'              => 'validate-user-form-type',
					'display_name'      => esc_html__( 'Form Type', 'validate-user' ),
					'type'              => 'select',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => 'default',
					'options'           => [
						'default' => 'Default',
						'cf7'     => 'Contact Form 7'
					]
				],
				[
					'name'              => 'validate-user-cf7-form-id',
					'display_name'      => esc_html__( 'Contact Form 7 Form', 'validate-user' ),
					'type'              => 'select',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => null,
					'options'           => $cf7Ids
				]
			];

			foreach ( $settings as $setting ) {

				register_setting(
					$settings_group,
					$setting['name'],
					['sanitize_callback' => $setting['sanitize_callback']]
				);

				// Add fields to the section
				add_settings_field(
					$setting['name'],
					$setting['display_name'],
					[$this, 'inputField'],
					$settings_group,
					$settings_section,
					[
						'type'       => $setting['type'],
						'name'       => $setting['name'],
						'default'    => $setting['default'],
						'after_form' => $setting['after_form'] ?? null,
						'options'    => $setting['options'] ?? null
					]
				);

			}

			/// New Application Message
			$settings_section = 'validate-user-admin-email-settings';

			// Add settings section
			add_settings_section(
				$settings_section,
				esc_html__( 'Email Settings - New Application Message', 'validate-user' ),
				'',
				$settings_group
			);

			// Register Settings
			$settings = [
				[
					'name'              => 'validate-user-admin-email-send',
					'display_name'      => esc_html__( 'Send Admin Email', 'validate-user' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => 1
				],
				[
					'name'              => 'validate-user-admin-email-from-name',
					'display_name'      => esc_html__( 'From Name', 'validate-user' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => get_bloginfo( 'name' )
				],
				[
					'name'              => 'validate-user-admin-email-from-address',
					'display_name'      => esc_html__( 'From Address', 'validate-user' ),
					'type'              => 'email',
					'sanitize_callback' => 'sanitize_email',
					'default'           => get_bloginfo( 'admin_email' )
				],
				[
					'name'              => 'validate-user-admin-email-to-name',
					'display_name'      => esc_html__( 'To Name', 'validate-user' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => 'Site Admin'
				],
				[
					'name'              => 'validate-user-admin-email-to-address',
					'display_name'      => esc_html__( 'To Address', 'validate-user' ),
					'type'              => 'email',
					'sanitize_callback' => 'sanitize_email',
					'default'           => get_bloginfo( 'admin_email' )
				],
				[
					'name'              => 'validate-user-admin-email-message',
					'display_name'      => esc_html__( 'Message Template', 'validate-user' ),
					'type'              => 'textarea',
					'sanitize_callback' => [$this, 'validateEmailTemplate'],
					'default'           => ValidateUserEmailTemplates::adminEmailTemplate(),
					'after_form'        => esc_html__( 'Default macros:', 'validate-user' ) .
					                       ' {from_name}, {from_address}, {to_name}, {to_address}, {site_name}, {site_url}, {username}, {email}, {application_link}, {other_info}.  ' .
					                       esc_html__( 'Other macros can be used based on form fields', 'validate-user' ) . '.'
				]
			];

			foreach ( $settings as $setting ) {

				register_setting(
					$settings_group,
					$setting['name'],
					['sanitize_callback' => $setting['sanitize_callback']]
				);

				// Add fields to the section
				add_settings_field(
					$setting['name'],
					$setting['display_name'],
					[$this, 'inputField'],
					$settings_group,
					$settings_section,
					[
						'type'       => $setting['type'],
						'name'       => $setting['name'],
						'default'    => $setting['default'],
						'after_form' => $setting['after_form'] ?? null,
						'options'    => $setting['options'] ?? null
					]
				);

			}

			/// Client Messages
			$settings_section = 'validate-user-client-email-settings';

			// Add settings section
			add_settings_section(
				$settings_section,
				'Email Settings - Client Messages',
				'',
				$settings_group
			);

			// Register Settings
			$settings = [
				[
					'name'              => 'validate-user-client-email-from-name',
					'display_name'      => esc_html__( 'From Name', '/lanaguages' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => get_bloginfo( 'name' )
				],
				[
					'name'              => 'validate-user-client-email-from-address',
					'display_name'      => esc_html__( 'From Address', 'validate-user' ),
					'type'              => 'email',
					'sanitize_callback' => 'sanitize_email',
					'default'           => get_bloginfo( 'admin_email' )
				],
				[
					'name'              => 'validate-user-confirmation-email-subject',
					'display_name'      => esc_html__( 'User Confirmed - Subject Template', 'validate-user' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => 'Your User Application Has Been Approved'
				],
				[
					'name'              => 'validate-user-confirmation-email-message',
					'display_name'      => esc_html__( 'User Confirmed - Message Template', 'validate-user' ),
					'type'              => 'textarea',
					'sanitize_callback' => function ( $input ) {
						$input = $this->validateEmailTemplate( $input );
						if ( ! str_contains( $input, '{set_password_link}' ) ) {
							$input = get_option( 'validate-user-confirmation-email-message', ValidateUserEmailTemplates::confirmationEmailTemplate() );
						}

						return $input;
					},
					'default'           => ValidateUserEmailTemplates::confirmationEmailTemplate(),
					'after_form'        => esc_html__( 'Default macros:', 'validate-user' ) .
					                       ' {from_name}, {from_address}, {site_name}, {site_url}, {username}, {email}, {set_password_link}.  ' .
					                       esc_html__( 'Other macros can be used based on form fields', 'validate-user' ) . '.' .
					                       "<br>({set_password_link} " . esc_html__( 'must appear in the email)', 'validate-user' )
				],
				[
					'name'              => 'validate-user-rejection-email-send',
					'display_name'      => esc_html__( 'Send Rejection Email', 'validate-user' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => 0
				],
				[
					'name'              => 'validate-user-rejection-email-subject',
					'display_name'      => esc_html__( 'User Rejected - Subject Template', 'validate-user' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => 'Your User Application Has Been Denied'
				],
				[
					'name'              => 'validate-user-rejection-email-message',
					'display_name'      => esc_html__( 'User Rejected - Message Template', 'validate-user' ),
					'type'              => 'textarea',
					'sanitize_callback' => [$this, 'validateEmailTemplate'],
					'default'           => ValidateUserEmailTemplates::rejectionEmailTemplate(),
					'after_form'        => esc_html__( 'Default macros:', 'validate-user' ) .
					                       ' {from_name}, {from_address}, {site_name}, {site_url}, {username}, {email}.  ' .
					                       esc_html__( 'Other macros can be used based on form fields', 'validate-user' ) . '.'
				]
			];

			foreach ( $settings as $setting ) {

				register_setting(
					$settings_group,
					$setting['name'],
					['sanitize_callback' => $setting['sanitize_callback']]
				);

				// Add fields to the section
				add_settings_field(
					$setting['name'],
					$setting['display_name'],
					[$this, 'inputField'],
					$settings_group,
					$settings_section,
					[
						'type'       => $setting['type'],
						'name'       => $setting['name'],
						'default'    => $setting['default'],
						'after_form' => $setting['after_form'] ?? null,
						'options'    => $setting['options'] ?? null
					]
				);

			}

			/// reCAPTCHA Settings
			$settings_section = 'validate-user-recaptcha-settings';

			// Add settings section
			add_settings_section(
				$settings_section,
				esc_html__( 'reCAPTCHA Settings', 'validate-user' ),
				'',
				$settings_group
			);

			// Register Settings
			$settings = [
				[
					'name'              => 'validate-user-use-recaptcha',
					'display_name'      => esc_html__( 'Use reCAPTCHA', 'validate-user' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => 0,
					'after_form'        => esc_html__( 'Note: this only works if form is set to default.  For other forms reCAPTCHA will need to be set up separately.', 'validate-user' )
				],
				[
					'name'              => 'validate-user-recaptcha-public-key',
					'display_name'      => esc_html__( 'Public Key', 'validate-user' ),
					'type'              => 'text',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => '',
					'after_form'        => esc_html__( 'Must be a valid reCAPTCHA v2 site key' )
				],
				[
					'name'              => 'validate-user-recaptcha-secret-key',
					'display_name'      => esc_html__( 'Secret Key', 'validate-user' ),
					'type'              => 'password',
					'sanitize_callback' => 'sanitize_text_field',
					'default'           => ''
				]
			];

			foreach ( $settings as $setting ) {

				register_setting(
					$settings_group,
					$setting['name'],
					['sanitize_callback' => $setting['sanitize_callback']]
				);

				// Add fields to the section
				add_settings_field(
					$setting['name'],
					$setting['display_name'],
					[$this, 'inputField'],
					$settings_group,
					$settings_section,
					[
						'type'       => $setting['type'],
						'name'       => $setting['name'],
						'default'    => $setting['default'],
						'after_form' => $setting['after_form'] ?? null,
						'options'    => $setting['options'] ?? null
					]
				);

			}

		}

		/**
		 * Creates html for a settings field.
		 *
		 * @param array $args An array with information about the field to display.  Must have keys 'type', 'name',
		 *                    and 'default'.  Optional key 'after_form'.
		 *
		 * @return void
		 */
		public function inputField( array $args ): void {

			if ( $args['type'] === 'text' || $args['type'] === 'email' || $args['type'] === 'password' ) {
				echo '<input type="' . $args['type'] . '"' .
				     ' id="' . $args['name'] . '"' .
				     ' name="' . $args['name'] . '"' .
				     ' value="' . get_option( $args['name'], $args['default'] ) . '">';
			}
			else if ( $args['type'] === 'textarea' ) {
				echo '<textarea id="' . $args['name'] . '"' .
				     ' class="auto-resize"' .
				     ' name="' . $args['name'] . '">' .
				     get_option( $args['name'], $args['default'] ) . '</textarea>';
			}
			else if ( $args['type'] === 'checkbox' ) {
				echo '<input type="checkbox"' .
				     ' name="' . $args['name'] . '"' .
				     ' value="1"' .
				     ' ' . checked( get_option( $args['name'], $args['default'] ), 1, false ) . '>';
			}
			else if ( $args['type'] === 'select' ) {

				echo '<select id="' . $args['name'] . '"' .
				     ' name="' . $args['name'] . '">';

				foreach ( $args['options'] as $value => $name ) {

					$select_text = '';
					if ( $value == get_option( $args['name'], $args['default'] ) ) {
						$select_text = ' selected="selected"';
					}

					echo '<option value="' . $value . '"' . $select_text . '>' . $name . '</option>';
				}

				echo '</select>';

			}

			if ( ! array_key_exists( 'after_form', $args ) || null === $args['after_form'] ) {
				return;
			}

			echo '<p class="after-form">' . $args['after_form'] . '</p>';

		}

		/**
		 * Validates an email template, allowing a few html formatting options.
		 *
		 * @param string $input The input email message template.
		 *
		 * @return string The validated email message template.
		 */
		public function validateEmailTemplate( string $input ): string {

			return wp_kses(
				$input,
				[
					'a'      => ['href' => []],
					'br'     => [],
					'ul'     => [],
					'li'     => [],
					'strong' => []
				]
			);

		}

		/**
		 * Encrypts an option before it is saved to the database.
		 *
		 * @param string $new_value The new value of the option to save.
		 * @param string $old_value The old value of the option, which is used if encryption fails.
		 *
		 * @return string The encrypted value, or the old value if encryption fails.
		 */
		public function encryptOption( string $new_value, string $old_value ): string {

			require_once( VALIDATE_USER_PATH . 'utilities/encryption.php' );
			$encryptionObj = new ValidateUserEncryption();
			$encryptedData = $encryptionObj->encrypt( $new_value );

			if ( false === $encryptedData ) {
				return $old_value;
			}

			return $encryptedData;

		}

		/**
		 * Decrypts an encrypted option when it is retrieved from the database.
		 *
		 * @param string $value The encrypted value to decrypt.
		 * @param string $option The option name.
		 *
		 * @return string
		 */
		public function decryptOption( string $value, string $option ): string {

			require_once( VALIDATE_USER_PATH . 'utilities/encryption.php' );
			$encryptionObj = new ValidateUserEncryption();
			$decryptedData = $encryptionObj->decrypt( $value );

			if ( false === $decryptedData ) {
				return $value;
			}

			return $decryptedData;

		}

		/**
		 * Adds the settings page to the admin panel.
		 *
		 * @return void
		 */
		public function addOptionsPages(): void {

			add_menu_page(
				esc_html__( 'Validate User', 'validate-user' ),
				esc_html__( 'Validate User', 'validate-user' ),
				'manage_options',
				'validate-user',
				[$this, 'settingsHTML'],
				'dashicons-id'
			);

			add_submenu_page(
				'validate-user',
				esc_html__( 'Validate User Applications', 'validate-user' ),
				esc_html__( 'Applications', 'validate-user' ),
				'manage_options',
				'edit.php?post_type=validate-apps'
			);

		}

		/**
		 * Generates the HTML for the settings page itself.
		 *
		 * @return void
		 */
		public function settingsHTML(): void {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_redirect( get_home_url() );
				die();
			}
			?>

            <div class="wrap validate-user">
                <h1><?php esc_html_e( 'Validate User', 'validate-user' ); ?></h1>
                <h2><?php esc_html_e( 'Documentation', 'validate-user' ); ?></h2>
                <div>
                    <p><?php esc_html_e( 'View the documentation on GitHub', 'validate-user' ); ?> <a
                                href="https://github.com/B-rando1/validate-user#readme"
                                target="_blank"><?php esc_html_e( 'here', 'validate-user' ); ?>.</a></p>
                    <p><?php esc_html_e( 'If the GitHub link does not work for you, view the documentation', 'validate-user' ); ?>
                        <a href="<?php echo VALIDATE_USER_URL . 'README.md'; ?>"
                           target="_blank"><?php esc_html_e( 'here', 'validate-user' ); ?>.</a></p>
                </div>

                <form action="options.php" method="post">
					<?php settings_fields( 'validate-user' ); ?>
					<?php do_settings_sections( 'validate-user' ); ?>
                    <input name="Submit" type="submit" value="<?php esc_html_e( 'Save Changes', 'validate-user' ); ?>"
                           class="button button-primary"/>
                </form>
            </div>

			<?php

		}

	}

}