jQuery( document ).ready( function ( $ ) {
    $( ".validate-user .auto-resize" ).each( function () {

        this.style.height = "auto";
        this.style.height = this.scrollHeight + 10 + "px";

        $( this ).on( "input", function () {
            validateUserResize( this );
        } );
    } );
} );

function validateUserResize ( element ) {

    element.style.height = "auto";
    element.style.height = element.scrollHeight + 10 + "px";

}