<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die( esc_html__( 'Access Denied', '/languages' ) );
}

// Delete Options
$options = [
	'validate-user-admin-email-send',
	'validate-user-admin-email-from-name',
	'validate-user-admin-email-from-address',
	'validate-user-admin-email-to-name',
	'validate-user-admin-email-to-address',
	'validate-user-admin-email-message',

	'validate-user-client-email-from-name',
	'validate-user-client-email-from-address',
	'validate-user-confirmation-email-subject',
	'validate-user-confirmation-email-message',
	'validate-user-rejection-email-send',
	'validate-user-rejection-email-subject',
	'validate-user-rejection-email-message',

	'validate-user-use-recaptcha',
	'validate-user-recaptcha-public-key',
	'validate-user-recaptcha-secret-key'
];

foreach ( $options as $option ) {
	delete_option( $option );
}

// Delete Applications Post Type
$application_ids = get_posts([
	'fields' => 'ids',
	'numberposts' => -1,
	'post_type' => 'validate-apps'
]);

foreach ( $application_ids as $id ) {
	wp_delete_post( $id );
}

// Delete User Meta and change role
$users = get_users();
foreach ( $users as $user ) {
	delete_user_meta( $user->ID, 'validate-user-business');

	$roles = $user->roles;
	if ( in_array( 'validated_user', $roles, true ) ) {
		$user->remove_role( 'validated_user' );
		$user->add_role( 'subscriber' );
	}
}

// Remove custom role
remove_role( 'validated_user' );