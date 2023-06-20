<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', '/languages' ) );
}

if ( ! class_exists( 'ValidateUserSettings' ) ) {

    class ValidateUserSettings {

	    private static ValidateUserSettings|null $instance = null;

	    private function __construct() {
	    }

	    public static function getInstance(): ValidateUserSettings {

		    if ( null === self::$instance ) {
			    self::$instance = new self();
		    }

		    return self::$instance;

	    }

        public static function setup(): void {

            $instance = self::getInstance();

            add_action( 'admin_enqueue_scripts', [$instance, 'enqueueScripts' ] );

            add_action( 'admin_init', [$instance, 'createSettings'] );

	        add_action( 'pre_update_option_validate-user-recaptcha-secret-key', [$instance, 'encryptOption'], 10, 2 );
            add_action( 'option_validate-user-recaptcha-secret-key', [$instance, 'decryptOption'], 10, 2 );

            add_action( 'admin_menu', [$instance, 'addOptionsPages'] );

            add_action( 'wpcf7_verify_nonce', '__return_true' );

        }

        public function enqueueScripts( $hook ): void {

            if ( 'toplevel_page_validate-user' === $hook ) {

	            wp_register_script( 'validate_user_settings_page_script', VALIDATE_USER_URL . '/js/settings-page.js', [ 'jquery', 'validate_user_global_script' ] );
	            wp_enqueue_script( 'validate_user_settings_page_script' );

            }

        }

        // Register Settings
        public function createSettings(): void {

            require_once( VALIDATE_USER_PATH . '/templates/email-templates.php' );

            $settings_group = 'validate-user';

	        /// Form Settings
	        $settings_section = 'validate-user-form-settings';

	        // Add settings section
	        add_settings_section(
		        $settings_section,
		        esc_html__( 'Form Settings', '/languages' ),
		        '',
		        $settings_group
	        );

	        // Register Settings
            $args = [
                'post_type' => 'wpcf7_contact_form',
                'posts_per_page' => -1
            ];
            $cf7Forms = get_posts( $args );
            $cf7Ids = wp_list_pluck( $cf7Forms, 'post_title', 'ID' );

	        $settings = [
                [
                    'name' => 'validate-user-form-title',
                    'display_name' => esc_html__( 'Form Title', '/languages' ),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => esc_html__( 'Apply to Be a User', '/languages' ),
                    'after_form' => esc_html__( 'Leave empty for no title.' )
                ],
		        [
			        'name' => 'validate-user-form-type',
			        'display_name' => esc_html__( 'Form Type', '/languages' ),
			        'type' => 'select',
			        'sanitize_callback' => 'sanitize_text_field',
			        'default' => 'default',
			        'options' => [
                            'default' => 'Default',
                            'cf7' => 'Contact Form 7'
                    ]
		        ],
                [
                    'name' => 'validate-user-cf7-form-id',
                    'display_name' => esc_html__( 'Contact Form 7 Form', '/languages' ),
                    'type' => 'select',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => null,
                    'options' => $cf7Ids
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
			        [ $this, 'inputField' ],
			        $settings_group,
			        $settings_section,
			        [
				        'type' => $setting['type'],
				        'name' => $setting['name'],
				        'default' => $setting['default'],
				        'after_form' => $setting['after_form'] ?? null,
				        'options' => $setting['options'] ?? null
			        ]
		        );

	        }

            /// New Application Message
            $settings_section = 'validate-user-admin-email-settings';

            // Add settings section
            add_settings_section(
                $settings_section,
                esc_html__( 'Email Settings - New Application Message', '/languages' ),
                '',
                $settings_group
            );

            // Register Settings
            $settings = [
                [
                    'name' => 'validate-user-admin-email-send',
                    'display_name' => esc_html__( 'Send Admin Email', '/languages' ),
                    'type' => 'checkbox',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => 1
                ],
                [
                    'name' => 'validate-user-admin-email-from-name',
                    'display_name' => esc_html__( 'From Name', '/languages' ),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => get_bloginfo( 'name' )
                ],
                [
                    'name' => 'validate-user-admin-email-from-address',
                    'display_name' => esc_html__( 'From Address', '/languages' ),
                    'type' => 'email',
                    'sanitize_callback' => 'sanitize_email',
                    'default' => get_bloginfo( 'admin_email' )
                ],
                [
                    'name' => 'validate-user-admin-email-to-name',
                    'display_name' => esc_html__( 'To Name', '/languages' ),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => 'Site Admin'
                ],
                [
                    'name' => 'validate-user-admin-email-to-address',
                    'display_name' => esc_html__( 'To Address', '/languages' ),
                    'type' => 'email',
                    'sanitize_callback' => 'sanitize_email',
                    'default' => get_bloginfo( 'admin_email' )
                ],
                [
                    'name' => 'validate-user-admin-email-message',
                    'display_name' => esc_html__( 'Message Template', '/languages' ),
                    'type' => 'textarea',
                    'sanitize_callback' => [$this, 'validateEmailTemplate'],
                    'default' => ValidateUserEmailTemplates::adminEmailTemplate(),
                    'after_form' => esc_html__( 'Default macros:', '/languages' ) .
                                    ' {from_name}, {from_address}, {to_name}, {to_address}, {site_name}, {site_url}, {username}, {email}, {application_link}, {other_info}.  ' .
                                    esc_html__( 'Other macros can be used based on form fields', '/languages' ) . '.'
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
                    [ $this, 'inputField' ],
                    $settings_group,
                    $settings_section,
	                [
		                'type' => $setting['type'],
		                'name' => $setting['name'],
		                'default' => $setting['default'],
		                'after_form' => $setting['after_form'] ?? null,
		                'options' => $setting['options'] ?? null
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
                    'name' => 'validate-user-client-email-from-name',
                    'display_name' => esc_html__( 'From Name', '/lanaguages' ),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => get_bloginfo( 'name' )
                ],
                [
                    'name' => 'validate-user-client-email-from-address',
                    'display_name' => esc_html__( 'From Address', '/languages' ),
                    'type' => 'email',
                    'sanitize_callback' => 'sanitize_email',
                    'default' => get_bloginfo( 'admin_email' )
                ],
                [
                    'name' => 'validate-user-confirmation-email-subject',
                    'display_name' => esc_html__( 'User Confirmed - Subject Template', '/languages' ),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => 'Your User Application Has Been Approved'
                ],
                [
                    'name' => 'validate-user-confirmation-email-message',
                    'display_name' => esc_html__( 'User Confirmed - Message Template', '/languages' ),
                    'type' => 'textarea',
                    'sanitize_callback' => function ( $input ) {
                        $input = $this->validateEmailTemplate( $input );
                        if ( ! str_contains( $input, '{set_password_link}' ) ) {
                            $input = get_option( 'validate-user-confirmation-email-message', ValidateUserEmailTemplates::confirmationEmailTemplate() );
                        }
                        return $input;
                    },
                    'default' => ValidateUserEmailTemplates::confirmationEmailTemplate(),
                    'after_form' => esc_html__( 'Default macros:', '/languages' ) .
                                    ' {from_name}, {from_address}, {site_name}, {site_url}, {username}, {email}, {set_password_link}.  ' .
                                    esc_html__( 'Other macros can be used based on form fields', '/languages' ) . '.' .
                                    "<br>({set_password_link} " . esc_html__( 'must appear in the email)', '/languages' )
                ],
                [
                    'name' => 'validate-user-rejection-email-send',
                    'display_name' => esc_html__( 'Send Rejection Email', '/languages' ),
                    'type' => 'checkbox',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => 0
                ],
                [
                    'name' => 'validate-user-rejection-email-subject',
                    'display_name' => esc_html__( 'User Rejected - Subject Template', '/languages' ),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => 'Your User Application Has Been Denied'
                ],
                [
                    'name' => 'validate-user-rejection-email-message',
                    'display_name' => esc_html__( 'User Rejected - Message Template', '/languages' ),
                    'type' => 'textarea',
                    'sanitize_callback' => [$this, 'validateEmailTemplate'],
                    'default' => ValidateUserEmailTemplates::rejectionEmailTemplate(),
                    'after_form' => esc_html__( 'Default macros:', '/languages' ) .
                                    ' {from_name}, {from_address}, {site_name}, {site_url}, {username}, {email}.  ' .
                                    esc_html__( 'Other macros can be used based on form fields', '/languages' ) . '.'
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
                    [ $this, 'inputField' ],
                    $settings_group,
                    $settings_section,
                    [
                        'type' => $setting['type'],
                        'name' => $setting['name'],
                        'default' => $setting['default'],
                        'after_form' => $setting['after_form'] ?? null,
                        'options' => $setting['options'] ?? null
                    ]
                );

            }

	        /// reCAPTCHA Settings
	        $settings_section = 'validate-user-recaptcha-settings';

	        // Add settings section
	        add_settings_section(
		        $settings_section,
		        esc_html__( 'reCAPTCHA Settings', '/languages' ),
		        '',
		        $settings_group
	        );

	        // Register Settings
	        $settings = [
		        [
			        'name' => 'validate-user-use-recaptcha',
			        'display_name' => esc_html__( 'Use reCAPTCHA', '/languages' ),
			        'type' => 'checkbox',
			        'sanitize_callback' => 'sanitize_text_field',
			        'default' => 0,
                    'after_form' => esc_html__( 'Note: this only works if form is set to default.  For other forms reCAPTCHA will need to be set up separately.', '/languages' )
		        ],
		        [
			        'name' => 'validate-user-recaptcha-public-key',
			        'display_name' => esc_html__( 'Public Key', '/languages' ),
			        'type' => 'text',
			        'sanitize_callback' => 'sanitize_text_field',
			        'default' => '',
                    'after_form' => esc_html__( 'Must be a valid reCAPTCHA v2 site key' )
		        ],
		        [
			        'name' => 'validate-user-recaptcha-secret-key',
			        'display_name' => esc_html__( 'Secret Key', '/languages' ),
			        'type' => 'password',
			        'sanitize_callback' => 'sanitize_text_field',
			        'default' => ''
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
			        [ $this, 'inputField' ],
			        $settings_group,
			        $settings_section,
			        [
				        'type' => $setting['type'],
				        'name' => $setting['name'],
				        'default' => $setting['default'],
				        'after_form' => $setting['after_form'] ?? null,
                        'options' => $setting['options'] ?? null
			        ]
		        );

	        }

        }

        public function inputField( $args ): void {

            if ( $args['type'] === 'text' || $args['type'] === 'email' || $args['type'] === 'password') {
                echo '<input type="' . $args['type'] . '"' .
                     ' id="' . $args['name'] . '"' .
                     ' name="' . $args['name'] . '"' .
                     ' value="' . get_option( $args['name'], $args['default'] ) . '">';
            }
            else if ($args['type'] === 'textarea' ) {
                echo '<textarea id="' . $args['name'] . '"' .
                     ' class="auto-resize"' .
                     ' name="' . $args['name'] . '">' .
                     get_option( $args['name'], $args['default'] ) . '</textarea>';
            }
            else if ( $args['type'] === 'checkbox' ) {
                echo '<input type="checkbox"' .
                     ' name="' . $args['name'] .'"' .
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

        public function validateEmailTemplate( string $input ): string {

            return wp_kses(
                $input,
                [
                    'a' => ['href' => []],
                    'br' => [],
                    'ul' => [],
                    'li' => [],
                    'strong' => []
                ]
            );

        }

        public function encryptOption( string $new_value, string $old_value ): string {

            require_once( VALIDATE_USER_PATH . 'utilities/encryption.php' );
            $encryptionObj = new ValidateUserEncryption();
            $encryptedData = $encryptionObj->encrypt( $new_value );

            if ( false === $encryptedData ) {
                return $old_value;
            }
            return $encryptedData;

        }

        public function decryptOption( string $value, string $option ): string {

	        require_once( VALIDATE_USER_PATH . 'utilities/encryption.php' );
	        $encryptionObj = new ValidateUserEncryption();
	        $decryptedData = $encryptionObj->decrypt( $value );

	        if ( false === $decryptedData ) {
		        return $value;
	        }
	        return $decryptedData;

        }

        // Page Navigation
        public function addOptionsPages(): void {

            add_menu_page(
                esc_html__( 'Validate User', '/languages' ),
                esc_html__( 'Validate User', '/languages' ),
                'manage_options',
                'validate-user',
                [$this, 'settingsHTML'],
                'dashicons-id'
            );

            add_submenu_page(
                'validate-user',
                esc_html__( 'Validate User Applications', '/languages' ),
                esc_html__( 'Applications', '/languages' ),
                'manage_options',
                'edit.php?post_type=validate-apps'
            );

        }

        // The Page Itself
        public function settingsHTML(): void {

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_redirect( get_home_url() );
                die();
            }
            ?>

            <div class="wrap validate-user">
                <h1><?php esc_html_e( 'Validate User', '/languages' ); ?></h1>
                <h2><?php esc_html_e( 'Documentation', '/languages' ); ?></h2>
                <div>
                    <p><?php esc_html_e( 'View the documentation on GitHub', '/languages' ); ?> <a href="https://github.com/B-rando1/validate-user#readme" target="_blank"><?php esc_html_e( 'here', '/languages' ); ?>.</a></p>
                    <p><?php esc_html_e( 'If the GitHub link does not work for you, view the documentation', '/languages' ); ?> <a href="<?php echo VALIDATE_USER_URL . 'README.md'; ?>" target="_blank"><?php esc_html_e( 'here', '/languages' ); ?>.</a></p>
                </div>

                <form action="options.php" method="post">
                    <?php settings_fields( 'validate-user' ); ?>
                    <?php do_settings_sections( 'validate-user' ); ?>
                    <input name="Submit" type="submit" value="<?php esc_html_e( 'Save Changes', '/languages' ); ?>" class="button button-primary" />
                </form>
            </div>

            <?php

        }

    }

}