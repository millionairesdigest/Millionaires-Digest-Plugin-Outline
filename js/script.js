jQuery( document ).ready( function() {
    var master_table;

    jQuery( '.delete_link' ).click( function() {
        var tmp = jQuery( '#delete-msg' ).html();
        return confirm( tmp );
    } );
    if ( jQuery( '#birthday_date' ).length >= 1 ) {
        jQuery( '#birthday_date' ).datepicker( {
            changeMonth: true,
            changeYear: true,
			yearRange: "-90:+0",
            maxDate: "+0D",
            "dateFormat" : jQuery( '#birthday_date' ).data( 'date-format' )
        } );
        jQuery( '#ui-datepicker-div' ).hide();
    }
    if ( jQuery( '#birthday_table' ).length >= 1 ) {
        jQuery.fn.dataTable.moment( jQuery( '#birthday_table' ).data( 'date-format' ) );
        master_table = jQuery( '#birthday_table' ).DataTable( {
            stateSave: true,
            "lengthMenu": [ 15, 30, 100 ],
            "columnDefs": [ 
                { "orderable": false, "targets": [3, 4] }
            ],
            "stripeClasses": [ 'alternate', '' ],
            "processing": true,
            "deferRender": true
        } );
        jQuery( document ).tooltip( {
            items: ".list-image",
            content: function() {
                var element = jQuery( 'a', this );
                if ( element.length >= 1 ) {
                    return '<img src="'+element.attr( 'href' )+'" alt="User\'s Image" />';
                }
            },
            show: {
                effect: "slideDown",
                delay: 250
            },
            open: function (event, ui) {
                ui.tooltip.addClass( 'birthday-list-tooltip' );
            }
        } );
        jQuery( '.show_today' ).click( function() {
            if ( jQuery( this ).hasClass( 'button-primary focus' ) ) {
                master_table.columns(2).search('').draw();
            } else {
                var today = jQuery.datepicker.formatDate( jQuery( '#birthday_table' ).data( 'date-format-noyear' ), new Date() );
                master_table.columns(2).search( today ).draw();
            }
            jQuery( this ).toggleClass( 'button-primary focus' );
        } );
        jQuery( '.show_wp_users' ).click( function() {
            if ( jQuery( this ).hasClass( 'button-primary focus' ) ) {
                master_table.columns(5).search('').draw();
            } else {
                master_table.columns(5).search('WP').draw();
            }
            jQuery( this ).toggleClass( 'button-primary focus' );
        } );
    }

    if ( jQuery( '#birthday_date_format' ).length >= 1 ) {
        var ajaxurl = '/wp/wp-admin/admin-ajax.php';
        jQuery("input[name='birthdays_date_format']").change( function() {
            var format = jQuery(this);
            format.siblings( '.spinner' ).addClass( 'is-active' );
            jQuery.post(ajaxurl, {
                    action: 'date_format_custom' == format.attr('name') ? 'date_format' : 'time_format',
                    date : format.val()
                }, function(d) { format.siblings( '.spinner' ).removeClass( 'is-active' ); format.siblings('.example').text(d); } );
        });

        jQuery("input[name='birthdays_date_format']").click(function(){
            console.log( '1' );
            if ( "date_format_custom_radio" != jQuery(this).attr("id") ) {
                console.log( '2' );
                jQuery( "#date_format_custom" ).val( jQuery( this ).val() ).siblings( '.example' ).text( jQuery( this ).parent( 'label' ).text() );
            }
        });
        jQuery("#date_format_custom").focus(function(){
            jQuery( '#date_format_custom_radio' ).prop( 'checked', true );
        });
    }

    if ( jQuery( '.bw-image' ).length >= 1 ) {
        // Uploading files
        var file_frame;
        jQuery( '.upload_image_button' ).live( 'click', function( event ) {
            event.preventDefault();
            // If the media frame already exists, reopen it.
            if ( file_frame ) {
                file_frame.url_input = jQuery( this ).attr( 'data-input' );
                file_frame.open();
                return;
            }
            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Please select an image:',
                button: {
                    text: jQuery( this ).data( 'uploader_button_text' ),
                },
                multiple: false  // Set to true to allow multiple files to be selected
            });
            file_frame.url_input = jQuery( this ).attr( 'data-url-input' );
            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();
                // Do something with attachment.id and/or attachment.url here
                jQuery( '#'+file_frame.url_input ).val( attachment.id );
                jQuery( '#'+file_frame.url_input+'_preview' ).attr( 'src', attachment.url );
            });
            // Finally, open the modal
            file_frame.open();
        } );
        jQuery( '.default-image' ).click( function() {
            var deflt = jQuery( this ).attr( 'data-default-image' );
            var url_input = jQuery( this ).attr( 'data-url-input' );
            jQuery( '#'+url_input ).val( deflt );
            jQuery( '#'+url_input+'_preview' ).attr( 'src', deflt );
            //jQuery( this ).siblings( '.bw-image' ).val(  );
        } );
        jQuery( '.disable-image' ).click( function() {
            var element = jQuery( this );
            var flag = jQuery( this ).siblings( '.default-image' ).prop( 'disabled' );
            var disabled = jQuery( '#disabled_txt' ).html();
            var enabled = jQuery( '#enabled_txt' ).html();
            if ( flag ) {
                jQuery( this ).siblings( '.default-image' ).prop( 'disabled', false );
                jQuery( this ).siblings( '.bw-image' ).prop( 'disabled', false );
                jQuery( this ).siblings( '.select-image' ).prop( 'disabled', false );
                jQuery( this ).siblings( '.disable-img' ).val( '1' );
                element.val( disabled );
            } else {
                jQuery( this ).siblings( '.bw-image' ).prop( 'disabled', true );
                jQuery( this ).siblings( '.default-image' ).prop( 'disabled', true );
                jQuery( this ).siblings( '.select-image' ).prop( 'disabled', true );
                jQuery( this ).siblings( '.disable-img' ).val( '0' );
                element.val( enabled );
            }
        } );
    }
    if ( jQuery( '.color_field' ).length >= 1 ) {
        jQuery( document ).ready( function($) {
            $( '.color_field' ).wpColorPicker();
        } );
    }
    jQuery( '#second_color' ).change( function() {
        jQuery( '.birthdays_hidden' ).toggleClass( 'hidden' );
    } );
    jQuery( '#wp_users_export' ).click( function() {
        var elem = jQuery( '#birthdays-export-button' );
        
        if( jQuery( this ).prop( 'checked' ) ) {
            elem.attr( 'href', elem.attr( 'href' ) + '&wp_users=yes' );
        } else {
            elem.attr( 'href', elem.attr( 'data-orig-link' ) );
        }
    } );
    jQuery( '.opt_item' ).click( function() {
        /* Unselect all other items */
        jQuery( '.opt_item' ).removeClass( 'opt_item_selected' );
        /* Handle the select element for birthday date meta field */
        var slc = jQuery( this ).find( 'select[name="birthdays_date_meta_field"]' );                    
        /*
         * If the select element is not inside the current option item,
         * then disable the select item otherwise enable it
        */
        if ( slc.length == 0 )
            jQuery( 'select[name="birthdays_date_meta_field"]' ).prop( 'disabled', true );
        else 
            slc.prop( 'disabled', false );
        /* Make the item selected */
        jQuery( this ).addClass( 'opt_item_selected' );
        /* Select current radio button */
        var elm = jQuery( this ).find( 'input:first' );
        elm.prop( 'checked', true );
    } );
    jQuery( '.nav-tab-wrapper > a' ).click( function() {
        jQuery( '.fade' ).hide();
        jQuery( '.nav-tab-wrapper > a' ).removeClass( 'nav-tab-active' );
        jQuery( this ).addClass( 'nav-tab-active' );
        jQuery( '.table' ).addClass( 'ui-tabs-hide' );
        var item_clicked = jQuery( this ).attr( 'href' );
        jQuery( item_clicked ).removeClass( 'ui-tabs-hide' );
        return false;
    } );

    if ( jQuery( '.birthdays-widget.birthdays-tooltip-enabled' ).length >= 1 ) {
        jQuery( document ).tooltip( {
            items: ".birthday_element",
            content: function() {
                var element = jQuery( 'a', this );
                if ( element.length == 2 ) {
                    element = element.next();
                }
                if ( element.length >= 1 ) {
                    if ( element.hasClass( 'user_image_enabled' ) ) {
                        var str = '<img src="'+element.attr( 'href' )+'" alt="User\'s Image" /><br />';
                    } else {
                        var str = '';
                    }
                    if ( element.attr( 'data-age' ) )
                        str += '<span class="birthday_age" >'+element.attr( 'data-age' )+'</span>';
                    return str;
                }
            },
            show: {
                effect: "slideDown",
                delay: 250
            },
            open: function (event, ui) {
                ui.tooltip.addClass( 'birthday-list-tooltip' );
            }
        } );
    }
} );


