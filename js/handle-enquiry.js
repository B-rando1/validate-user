/**
 * @var validate_user_enquiry_args
 */
/**
 * @var validate_user_enquiry_args.ajaxurl
 */
/**
 * @var grecaptcha
 */

jQuery( document ).ready( function ( $ ) {

    $( "#validate-user-form" ).submit( function ( event ) {

        event.preventDefault();

        let form = $( this );

        $.ajax( {
            type    : "POST",
            url     : validate_user_enquiry_args.ajaxurl,
            data    : form.serialize(),
            success : function ( response ) {

                form.hide();
                $( "#form-success" ).html( response ).fadeIn();
                $( "#form-error" ).hide();

            },
            error   : function ( requestObject, error, errorThrown ) {
                $( "#form-error" ).html( requestObject.responseText.slice( 1, - 1 ) ).fadeIn();
                grecaptcha.reset();
            },
        } );

    } );

} );