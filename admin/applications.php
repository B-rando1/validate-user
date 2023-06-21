<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', 'validate-user' ) );
}

if ( ! class_exists( 'ValidateUserApplications' ) ) {

	class ValidateUserApplications {

		private static ValidateUserApplications|null $instance = null;

		private function __construct() {
		}

		/**
		 * Gets the singleton instance
		 *
		 * @return ValidateUserApplications The singleton instance
		 */
		public static function getInstance(): ValidateUserApplications {

			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;

		}

		public static function setup(): void {

			$instance = self::getInstance();

			add_action( 'admin_enqueue_scripts', [ $instance, 'enqueueScripts' ] );

			add_action( 'init', [ $instance, 'registerCustomPostType' ] );
			add_action( 'admin_head', [ $instance, 'applicationsActive' ] );

			add_action( 'after_setup_theme', [ $instance, 'addRole' ] );

			add_action( 'add_meta_boxes', [ $instance, 'createMetaBox' ] );
			add_action( 'wp_ajax_validate_user_action', [ $instance, 'createValidUser' ] );
			add_action( 'wp_ajax_reject_user_action', [ $instance, 'rejectUser' ] );
			add_action( 'admin_menu', [ $instance, 'hidePublishBox' ] );

			add_filter( 'manage_validate-apps_posts_columns', [ $instance, 'applicationsColumns' ] );
			add_action( 'manage_validate-apps_posts_custom_column', [ $instance, 'fillApplicationsColumn' ], 10, 2 );
			add_filter( 'post_row_actions', [ $instance, 'rowActions' ], 10, 2 );

		}

		public function enqueueScripts( $hook ): void {

			global $post;

			if ( 'post.php' === $hook && 'validate-apps' === $post->post_type ) {
				wp_register_script( 'validate_reject_script', VALIDATE_USER_URL . '/js/validate-reject.js', [
					'jquery',
					'wp-i18n'
				] );
				wp_set_script_translations( 'validate_reject_script', 'validate-user' );
				wp_localize_script( 'validate_reject_script', 'testAjax', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
				wp_enqueue_script( 'validate_reject_script' );
			}

		}

		// Custom Post Type stuff
		public function registerCustomPostType(): void {

			register_post_type(
				'validate-apps',
				[
					'public'             => true,
					'has_archive'        => true,
					'publicly_queryable' => false,
					'labels'             => [
						'name'          => esc_html__( 'Validate User Applications', 'validate-user' ),
						'singular_name' => esc_html__( 'Validate User Application', 'validate-user' ),
						'edit_item'     => esc_html__( 'View Application', 'validate-user' )
					],
					'supports'           => false,
					'capability_type'    => 'post',
					'capabilities'       => [ 'create_posts' => false ],
					'map_meta_cap'       => true,
					'show_in_menu'       => false
				]
			);

		}

		public function applicationsActive(): void {

			global $parent_file, $post_type;

			if ( $post_type === 'validate-apps' ) {
				$parent_file = 'validate-user';
			}

		}

		// Add New Role
		public function addRole(): void {

			if ( wp_roles()->is_role( 'validated_user' ) ) {
				return;
			}

			add_role( 'validated_user', 'Validated User', get_role( 'subscriber' )->capabilities );

		}

		// Single Application Page
		public function createMetaBox(): void {
			add_meta_box( 'validate-user-applications', esc_html__( 'Validate User Application', 'validate-user' ), [
				$this,
				'display_application'
			], 'validate-apps' );
		}

		public function display_application(): void {

			$id = get_the_id();
			include_once( VALIDATE_USER_PATH . 'templates/view-single-application.php' );

		}

		public function createValidUser(): void {

			check_ajax_referer( 'validate_user_nonce', 'nonce' );

			$response           = [];
			$response['errors'] = [];

			// Create the user with random password
			$post_id = $_REQUEST['post_id'];

			$post_meta = get_post_meta( $post_id );

			$username = $post_meta['username'][0];
			unset( $post_meta['username'] );
			$email = $post_meta['email'][0];
			unset( $post_meta['email'] );

			if ( isset ( $post_meta['message'] ) ) {
				unset( $post_meta['message'] );
			}
			if ( isset( $post_meta['_edit_lock'] ) ) {
				unset( $post_meta['_edit_lock'] );
			}

			$password = wp_generate_password(); // Temporary password, not sent to the user

			$user_id = wp_create_user( $username, $password, $email );

			if ( gettype( $user_id ) !== "integer" ) {
				$response['errors'][] = esc_html__( 'Failed to create new user', 'validate-user' ) . ': ' . $user_id->get_error_code();
			}
			else {

				$user_obj = get_user_by( 'ID', $user_id );
				$user_obj->set_role( 'validated_user' );

				foreach ( $post_meta as $key => $value ) {
					update_user_meta( $user_id, "validate-user-$key", $value[0] );
				}

				// Send confirmation with password reset
				$from_name  = get_option( 'validate-user-client-email-from-name', get_bloginfo( 'name' ) );
				$from_email = get_option( 'validate-user-client-email-from-address', get_bloginfo( 'admin_email' ) );

				$reset_password_key = get_password_reset_key( $user_obj );
				$user_login = $user_obj->user_login;
				$reset_password_link = network_site_url( "wp-login.php?action=rp&key=$reset_password_key&login=" . rawurldecode( $user_login ), 'login' );

				$headers   = [];
				$headers[] = "From: $from_name <$from_email>";
				$headers[] = "To: $username <$email>";
				$headers[] = "Content-Type: text/html";

				require_once( VALIDATE_USER_PATH . '/templates/email-templates.php' );
				require_once( VALIDATE_USER_PATH . '/utilities/email.php' );

				$subject = get_option( 'validate-user-confirmation-email-subject', esc_html__( 'Your User Application Has Been Approved', 'validate-user' ) );
				$message = get_option( 'validate-user-confirmation-email-message', ValidateUserEmailTemplates::confirmationEmailTemplate() );

				$macros = [
					'{username}' => esc_html( $username ),
					'{email}'    => esc_html( $email ),
					'{set_password_link}' => $reset_password_link,
					'{other-info}' => ValidateUserEmailUtilities::formatArray( $post_meta )
				];
				foreach( $post_meta as $key => $value ) {
					$macro_key = esc_html( $key );
					$macros["{{$macro_key}}"] = esc_html( $value[0] );
				}

				$message = ValidateUserEmailUtilities::insertClientMacros( $message, $macros );
				$subject = ValidateUserEmailUtilities::insertClientMacros( $subject, $macros );

				$message = apply_filters( 'validate-user-confirmation-email-message', $message, $post_id );

				$sent = wp_mail( $email, $subject, $message, $headers );
				if ( ! $sent ) {
					$response['errors'][] = esc_html__( 'Failed to send email', 'validate-user' );
				}

			}

			if ( empty( $response['errors'] ) ) {

				// Delete the application
				wp_delete_post( $post_id );

				// Prepare to redirect
				$admin_url       = admin_url( 'edit.php?post_type=validate-apps' );
				$response['url'] = $admin_url;

			}

			echo json_encode( $response );

			die();

		}

		public function rejectUser(): void {

			check_ajax_referer( 'validate_user_nonce', 'nonce' );

			$response = [];
			$response['errors'] = [];

			$post_id = $_REQUEST['post_id'];

			// Send Rejection Email?
			if ( '1' === get_option( 'validate-user-rejection-email-send', '0' ) ) {

				$post_meta = get_post_meta( $post_id );
				$username = $post_meta['username'][0];
				unset( $post_meta['username'] );
				$email = $post_meta['email'][0];
				unset( $post_meta['email'] );

				if ( isset ( $post_meta['message'] ) ) {
					unset( $post_meta['message'] );
				}
				if ( isset( $post_meta['_edit_lock'] ) ) {
					unset( $post_meta['_edit_lock'] );
				}

				$from_name  = get_option( 'validate-user-client-email-from-name', get_bloginfo( 'name' ) );
				$from_email = get_option( 'validate-user-client-email-from-address', get_bloginfo( 'admin_email' ) );

				$headers   = [];
				$headers[] = "From: $from_name <$from_email>";
				$headers[] = "To: $username <$email>";
				$headers[] = "Content-Type: text/html";

				require_once( VALIDATE_USER_PATH . '/templates/email-templates.php' );
				require_once( VALIDATE_USER_PATH . '/utilities/email.php' );

				$subject = get_option( 'validate-user-rejection-email-subject', esc_html__( 'Your User Application Has Been Denied', 'validate-user' ) );
				$message = get_option( 'validate-user-rejection-email-message', ValidateUserEmailTemplates::rejectionEmailTemplate() );

				$macros = [
					'{username}' => esc_html( $username ),
					'{email}'    => esc_html( $email ),
					'{other-info}' => ValidateUserEmailUtilities::formatArray( $post_meta )
				];
				foreach( $post_meta as $key => $value ) {
					$macro_key = esc_html( $key );
					$macros["{{$macro_key}}"] = esc_html( $value[0] );
				}

				$message = ValidateUserEmailUtilities::insertClientMacros( $message, $macros );
				$subject = ValidateUserEmailUtilities::insertClientMacros( $subject, $macros );

				$message = apply_filters( 'validate-user-rejection-email-message', $message, $post_id );

				$sent = wp_mail( $email, $subject, $message, $headers );
				if ( ! $sent ) {
					$response['errors'][] = esc_html__( 'Failed to send email', 'validate-user' );
				}

			}

			if ( empty( $response['errors'] ) ) {

				// Delete the request and redirect to Validate User Applications page
				wp_delete_post( $post_id );
				$response['url'] = admin_url( 'edit.php?post_type=validate-apps' );

			}

			echo json_encode ( $response );

			die();

		}

		public function hidePublishBox(): void {
			remove_meta_box( 'submitdiv', 'validate-apps', 'side' );
		}

		// All Applications Page
		public function applicationsColumns( array $columns ): array {

			return [
				'cb'       => $columns['cb'],
				'username' => esc_html__( 'Username', 'validate-user' ),
				'email'    => esc_html__( 'Email', 'validate-user' )
			];

		}

		public function fillApplicationsColumn( string $column, int $post_id ): void {
			echo esc_html( get_post_meta( $post_id, $column, true ) );
		}

		public function rowActions( $actions, WP_Post $post ) {

			if ( $post->post_type !== 'validate-apps' ) {
				return $actions;
			}

			return [
				'<a href="' . get_edit_post_link( $post ) . '">View Application</a>'
			];

		}

	}

}