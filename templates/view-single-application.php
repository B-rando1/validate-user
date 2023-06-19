<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', '/languages' ) );
}

/** @var $id string */
$post_meta = get_post_meta( $id );
$username = $post_meta['username'][0];
unset( $post_meta['username'] );
$email = $post_meta['email'][0];
unset( $post_meta['email'] );
if ( isset ( $post_meta['message'] ) ) {
    $message = $post_meta['message'][0];
    unset( $post_meta['message'] );
}
if ( isset( $post_meta['_edit_lock'] ) ) {
    unset( $post_meta['_edit_lock'] );
}

?>

<div class="validate-user">
    <ul>
        <li><strong><?php esc_html_e( 'Username:', '/languages' ); ?></strong> <?php echo esc_html( $username ); ?></li>
        <li><strong><?php esc_html_e( 'Email:', '/languages' ); ?></strong> <?php echo esc_html( $email ); ?></li>
        <?php if ( isset( $message ) ) { ?>
            <li class="message"><strong><?php esc_html_e( 'Message:', '/languages' ); ?></strong><br><div><?php echo wp_kses( $message, ['br' => []] ); ?></div></li>
        <?php }
        foreach ( $post_meta as $key => $value ) { ?>
            <li><strong><?php echo esc_html( ucfirst( $key ) ); ?></strong> <?php echo esc_html( $value[0] ); ?></li>
        <?php } ?>
    </ul>
    <p id="error-messages" class="error"></p>
</div>

<?php

$nonce = wp_create_nonce( 'validate_user_nonce' );

$link = admin_url( 'admin-ajax.php?action=validate_user_action&post_id="' . $id . '&nonce=' . $nonce );
echo '<a class="validate_submit" data-nonce="' . $nonce . '" data-post_id="' . $id . '" href=' . $link . '">' . esc_html__( 'Validate Application', '/languages' ) . '</a>';

$link = admin_url( 'admin-ajax.php?action=reject_user_action&post_id="' . $id . '&nonce=' . $nonce );
echo '<br><a class="reject_submit" data-nonce="' . $nonce . '" data-post_id="' . $id . '" href=' . $link . '">' . esc_html__( 'Reject Application', '/languages' ) . '</a>';