<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( esc_html__( 'Access Denied', '/languages' ) );
}

?>

<div class="validate-user">

    <?php
    $form_title = esc_html( get_option( 'validate-user-form-title', esc_html__( 'Apply to Be a User', '/languages' ) ) );
    if ( ! empty( trim( $form_title ) ) ) {
        echo '<h2>' . $form_title . '</h2>';
    }
    ?>

    <?php if ( 'default' === get_option( 'validate-user-form-type', 'default' ) ) { ?>

        <div id="form-success" class="form-success"></div>
        <div id="form-error" class="form-error"></div>

        <form id="validate-user-form">

            <?php wp_nonce_field( 'wp_rest' ); ?>

            <label for="username"><?php esc_html_e( 'Username:', '/languages' ); ?></label><br>
            <input type="text" id="username" name="username" required><br><br>

            <label for="email"><?php esc_html_e( 'Email:', 'languages' ); ?></label><br>
            <input type="email" id="email" name="email" required><br><br>

            <label for="message"><?php esc_html_e( 'Message:', '/languages' ); ?></label><br>
            <textarea id="message" class="auto-resize" name="message"></textarea>

            <?php
            if ( '1' === get_option( 'validate-user-use-recaptcha', '0' ) ) :
                $public_key = get_option( 'validate-user-recaptcha-public-key', '' ); ?>
                <div class="g-recaptcha" data-sitekey="<?php echo $public_key; ?>"></div>
            <?php endif; ?>

            <button type="submit"><?php esc_html_e( 'Submit Request', '/languages' ); ?></button>

        </form>

    <?php }
    else if ( 'cf7' === get_option( 'validate-user-form-type', 'default' ) ) {
        echo do_shortcode( '[contact-form-7 id="' . get_option( 'validate-user-cf7-form-id', '' ) . '"]' );
    } ?>

</div>