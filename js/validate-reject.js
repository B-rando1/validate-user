/**
 * @var validate_user_application_args
 */

jQuery( document ).ready( function ( $ ) {

    $( ".validate_submit" ).click( function ( event ) {

        event.preventDefault();

        let post_id = $( this ).attr( "data-post_id" );
        let nonce   = $( this ).attr( "data-nonce" );

        $.ajax( {
            type     : "post",
            dataType : "json",
            url      : validate_user_application_args.ajaxurl,
            data     : {
                action  : "validate_user_action",
                post_id : post_id,
                nonce   : nonce,
            },
            success  : function ( response ) {

                if ( response.errors.length !== 0 ) {
                    response.errors.forEach( function ( error ) {
                        $( '#error-messages' ).append( error + '<br>' );
                    } );
                }
                else {
                    window.location.replace( response.url );
                }
            },
            error    : function () {
                $( '#error-messages' ).html( validate_user_application_args.ajax_error_message );
            },
        } );

    } );

    $( ".reject_submit" ).click( function ( event ) {

        event.preventDefault();

        let post_id = $( this ).attr( "data-post_id" );
        let nonce   = $( this ).attr( "data-nonce" );

        $.ajax( {
            type     : "post",
            dataType : 'json',
            url      : testAjax.ajaxurl,
            data     : {
                action  : "reject_user_action",
                post_id : post_id,
                nonce   : nonce,
            },
            success  : function ( response ) {

                if ( response.errors.length !== 0 ) {
                    response.errors.forEach( function ( error ) {
                        $( '#error-messages' ).append( error + '<br>' );
                    } );
                }
                else {
                    window.location.replace( response.url );
                }

            },
            error    : function () {
                $( '#error-messages' ).html( validate_user_application_args.ajax_error_message );
            },
        } );

    } );

} );