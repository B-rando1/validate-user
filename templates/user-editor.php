<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', 'validate-user' ) );
}

/** @var array $user_meta */
?>

<h2>Additional Information</h2>
<table class="validate-user form-table">
	<?php foreach ( $user_meta as $key => $value ) {
		if ( ! str_starts_with( $key, 'validate-user-' ) ) {
			continue;
		} ?>

        <tr>
            <th>
                <label for="<?php echo esc_html( $key ); ?>"><?php echo esc_html( ucfirst( substr( $key, 14 ) ) ); ?></label>
            </th>
            <td>
                <input type="text" name="<?php echo esc_html( $key ); ?>" id="<?php echo esc_html( $key ); ?>"
                       value="<?php echo esc_html( $value[0] ); ?>" class="regular-text"/>
            </td>
        </tr>

	<?php } ?>
</table>

<h2>Add Information</h2>
<table class="validate-user form-table">
    <tr>
        <th>
            <label for="validate-user-add-user-key"><?php esc_html_e( 'New Key', 'validate-user' ); ?></label>
        </th>
        <td>
            <input type="text" name="validate-user-add-user-key" id="validate-user-add-user-key" value=""
                   class="regular-text"/>
        </td>
        <th>
            <label for="validate-user-add-user-value"
                   class="alignright"><?php esc_html_e( 'New Value', 'validate-user' ); ?></label>
        </th>
        <td>
            <input type="text" name="validate-user-add-user-value" id="validate-user-add-user-value" value=""
                   class="regular-text"/>
        </td>
    </tr>
</table>
