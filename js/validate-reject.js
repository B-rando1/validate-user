const { __ } = wp.i18n;

jQuery(document).ready( function($) {

    $(".validate_submit").click( function(event) {

        event.preventDefault();

        let post_id = $(this).attr("data-post_id");
        let nonce = $(this).attr("data-nonce");

        $.ajax({
            type : "post",
            dataType : "json",
            url : testAjax.ajaxurl,
            data : {
                action : "validate_user_action",
                post_id : post_id,
                nonce : nonce
            },
            success : function(response) {

                if (response.errors.length !== 0) {
                    response.errors.forEach( function (error) {
                        $('#error-messages').append(error + '<br>');
                    });
                }
                else {
                    window.location.replace(response.url);
                }
            },
            error : function(response) {
                $('#error-messages').html( __( 'Ajax Error', '/languages' ) );
            }
        });

    });

    $(".reject_submit").click( function(event) {

        event.preventDefault();

        let post_id = $(this).attr("data-post_id");
        let nonce = $(this).attr("data-nonce");

        $.ajax({
            type : "post",
            dataType : 'json',
            url : testAjax.ajaxurl,
            data : {
                action : "reject_user_action",
                post_id : post_id,
                nonce : nonce
            },
            success : function(response) {

                if (response.errors.length !== 0) {
                    response.errors.forEach( function (error) {
                        $('#error-messages').append(error + '<br>');
                    });
                }
                else {
                    window.location.replace(response.url);
                }

            },
            error : function(response) {
                $('#error-messages').html( __( 'Ajax Error', '/languages' ) );
            }
        });

    });

});