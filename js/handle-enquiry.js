jQuery( document ).ready( function ( $ ) {

    $( "#validate-user-form" ).submit( function ( event ) {

        event.preventDefault();

        let form = $( this );
        console.log( form.serialize() );

        $.ajax( {
            type    : "POST",
            url     : testAjax.ajaxurl,
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