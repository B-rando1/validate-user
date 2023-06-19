jQuery( document ).ready( function ( $ ) {

    $( '.validate-user textarea' ).keydown( function( e ) {
        let $this, end, start;
        if ( e.keyCode === 9 ) {
            start = this.selectionStart;
            end = this.selectionEnd;
            $this = $( this );
            $this.val( $this.val().substring( 0, start ) + "\t" + $this.val().substring( end ) );
            this.selectionStart = this.selectionEnd = start + 1;
            return false;
        }
    });

    // Hide and display settings based on other settings
    const formTypeSelect = $( '.validate-user select[name="validate-user-form-type"]' );
    const cf7Settings = [
        'validate-user-cf7-form-id'
    ];

    validateUserShowFields( $, formTypeSelect.val() === 'cf7', cf7Settings );
    formTypeSelect.change( function () {
        validateUserShowFields( $, formTypeSelect.val() === 'cf7', cf7Settings );
    } );

    const adminEmailCheckBox = $( '.validate-user input[name="validate-user-admin-email-send"]' );
    const adminEmailSettings = [
        'validate-user-admin-email-from-name',
        'validate-user-admin-email-from-address',
        'validate-user-admin-email-to-name',
        'validate-user-admin-email-to-address',
        'validate-user-admin-email-message'
    ];

    validateUserShowFields( $, adminEmailCheckBox.is( ':checked' ), adminEmailSettings );
    adminEmailCheckBox.change( function () {

        validateUserShowFields( $, adminEmailCheckBox.is( ':checked' ), adminEmailSettings );
        $(".validate-user .auto-resize").each(function () {
            validateUserResize( this );
        } );

    } );

    const rejectionEmailCheckBox = $( '.validate-user input[name="validate-user-rejection-email-send"]' );
    const rejectionEmailSettings = [
        'validate-user-rejection-email-subject',
        'validate-user-rejection-email-message'
    ];

    validateUserShowFields( $, rejectionEmailCheckBox.is( ':checked' ), rejectionEmailSettings );
    rejectionEmailCheckBox.change( function () {

        validateUserShowFields( $, rejectionEmailCheckBox.is( ':checked' ), rejectionEmailSettings );
        $(".validate-user .auto-resize").each(function () {
            validateUserResize( this );
        } );

    } );

    const useRecaptchaCheckBox = $( '.validate-user input[name="validate-user-use-recaptcha"]' );
    const recaptchaSettings = [
        'validate-user-recaptcha-public-key',
        'validate-user-recaptcha-secret-key'
    ];

    validateUserShowFields( $, useRecaptchaCheckBox.is( ':checked' ), recaptchaSettings );
    useRecaptchaCheckBox.change( function () {
        validateUserShowFields( $, useRecaptchaCheckBox.is( ':checked' ), recaptchaSettings );
    } );

});

function validateUserShowFields( $, show, names ) {

    if ( show ) {

        $( '.validate-user table.form-table tr' ).each( function() {

            if ( names.includes( $( this ).find( 'input, textarea, select' ).attr( 'name' ) ) ) {
                $( this ).fadeIn();
            }

        } );

    }
    else {

        $( '.validate-user table.form-table tr' ).each( function() {

            if ( names.includes( $( this ).find( 'input, textarea, select' ).attr( 'name' ) ) ) {
                $( this ).fadeOut();
            }

        } );

    }

}