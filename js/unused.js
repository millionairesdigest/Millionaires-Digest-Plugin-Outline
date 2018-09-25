jQuery.post(ratingsL10n.admin_ajax_url, { 'action' : 'get_birthdays' }, function( data ){
    jQuery( '#birthday' ).html( showNames( data ) );
});

function showNames(data){
    var a = data.split(";", 100);
    var ret = "";
    if ( a[0] != 0 ){
        ret = "<span style=\"color: red; font-weight: bold; margin: 5px auto 5px auto; text-align: center;\">" +
                    "<img style=\"display: block;\" src=\"<?php echo plugins_url( '/images/birthday_cake.png' , __FILE__ ); ?>\" alt=\"birthday_cake\" width=\"100\" height=\"100\"/>" +
                    "<?php _e( 'Happy Birthday', 'birthdays-widget' ); ?> " +
                "</span>" + a[1] + "!!";
    }
    return ret;
}