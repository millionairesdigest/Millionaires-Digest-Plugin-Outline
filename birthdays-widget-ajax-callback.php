<?php 

add_action( 'wp_ajax_get_birthdays', 'birthdays_widget_callback' );
add_action( 'wp_ajax_nopriv_get_birthdays', 'birthdays_widget_callback' );

//admnin ajax
add_action( 'wp_ajax_get_birthdays_export_file', 'get_birthdays_export_file_callback' );

function birthdays_widget_check_for_birthdays( $all = false, $admin_table = false ) {
    global $wpdb, $plugin_errors;

    $table_name = $wpdb->prefix . "birthdays";
    if ( $all ) {
        $query = "SELECT * FROM $table_name ORDER BY DATE_FORMAT(date, '%m-%d');";
        $results = $wpdb->get_results( $query );
    } else {
        $query = "SELECT * FROM $table_name WHERE date LIKE '%%%s' ;";
        $results = $wpdb->get_results( $wpdb->prepare( $query, date_i18n( '-m-d' ) ) );
    }

    if ( !$admin_table ) {
        foreach ( $results as $key => $row ) {
            $tmp_date = date_create_from_format( 'Y-m-d', $row->date );
            if ( $tmp_date ) {
                $results[ $key ]->date = $tmp_date->getTimestamp();
            }
        }
    }

    $birthdays_settings = get_option( 'birthdays_settings' );
    $birthdays_settings = maybe_unserialize( $birthdays_settings );
    $date_format = $birthdays_settings[ 'date_format' ];
    $example = date_i18n( $date_format );

    //If birthdays for WordPress Users are drawn from their profile
    if ( $birthdays_settings[ 'date_from_profile' ] ) {
        $birthday_date_meta_field = $birthdays_settings[ 'date_meta_field' ];
        $users = get_users();
        foreach ( $users as $user ) {
            //If the birthday is a BuddyPress field, fetch it with bp_get_profile_field_data
            if ( $birthdays_settings[ 'date_meta_field_bp' ] && function_exists( 'bp_get_profile_field_data' ) ) {
                $query = 'field='.ucfirst( $birthdays_settings[ 'date_meta_field' ] ).'&user_id='.$user->id;
                $date = bp_get_profile_field_data( $query );
                if( empty( $date ) ) {
                    continue;
                }
                $tmp_date = date_create_from_format( $date_format, $date );
                $tmp_date2 = get_international_date( $date );
                if ( $tmp_date ) {
                    $date = $tmp_date->getTimestamp();
                } elseif ( $tmp_date2 == 'intl' ) {
                    $plugin_errors->int_library = __( 'Internationalization Functions needed, please install PHP\'s extension',  'birthdays-widget' );
                    $date = NULL;
                } elseif ( $tmp_date2 ) {
                    $date = $tmp_date2;
                } else {
                    $plugin_errors->users[] = __( 'WordPress User',  'birthdays-widget' ) . " " . $user->user_login . " (ID: ". $user->id . ") " . __( 'has wrong birthday date in BuddyPress',  'birthdays-widget' ) . ".<br />"
                    . __( ' Expected format:',  'birthdays-widget' ) . " " . $date_format . " (" .  __( 'something like',  'birthdays-widget' ) . " "
                    . $example . "), " . __( 'but',  'birthdays-widget' ) . " \"" . $date . "\" " . __( 'given',  'birthdays-widget' ) . "."
                    . __( 'Please change the plugin\'s option "Expected Date Format"', 'birthdays-widget' ) . ".";
                    $date = NULL;
                }
            } else {
                if ( isset( $user->{$birthday_date_meta_field} ) && !empty( $user->{$birthday_date_meta_field} ) ) {
                    $tmp_date = date_create_from_format( $date_format, $user->{$birthday_date_meta_field} );
                    $tmp_date2 = get_international_date( $user->{$birthday_date_meta_field} );
                    if ( $tmp_date ) {
                        $date = $tmp_date->getTimestamp();
                    } else {
                        $date = NULL;
                        $plugin_errors->users[] = __( 'WordPress User',  'birthdays-widget' ) . " " . $user->user_login . " (ID: ". $user->id . ") " . __( 'has wrong birthday date metafield',  'birthdays-widget' ) . ".<br />"
                        . __( ' Expected format:',  'birthdays-widget' ) . " " . $date_format . " (" . __( 'something like',  'birthdays-widget' ) . " "
                        . $example . "), " . __( 'but',  'birthdays-widget' ) . " \"" . $user->{$birthday_date_meta_field} . "\" " . __( 'given',  'birthdays-widget' ) . ". "
                       . __( 'Please change the plugin\'s option "Expected Date Format"', 'birthdays-widget' ) . ".";
                    }
                } else {
                    $date = NULL;
                }
            }
            if ( $date != NULL ) {
                $check_date = date( "-m-d", $date );
                //If this date exists for this user and it's his/her birthday, or if we want all birthdays
                if ( ( !$all && $check_date == date_i18n( '-m-d' ) ) || $all ) {
                    $tmp_user = new stdClass();
                    //If the user's name is a BuddyPress field, fetch it with bp_get_profile_field_data
                    if ( $birthdays_settings[ 'meta_field_bp' ] ) {
                        $query = 'field='.$birthdays_settings[ 'meta_field' ].'&user_id='.$user->id;
                        $tmp_user->name = bp_get_profile_field_data( $query );
                    } else {
                        $meta_key = $birthdays_settings[ 'meta_field' ];
                        $tmp_user->name = $user->{$meta_key};
                    }
                    $tmp_user->id = $user->id;
                    $tmp_user->email = $user->user_email;
                    if ( $admin_table ) {
                        $tmp_user->date = date_i18n( $date_format, $date );
                    } else {
                        $tmp_user->date = $date;
                    }
                    $tmp_user->wp_user = $user->id;
                    //If user's image is drawn from Gravatar
                    if ( $birthdays_settings[ 'wp_user_gravatar' ] ) {
                        $tmp_user->image = Birthdays_Widget_Settings::get_avatar_url( $tmp_user->email, 256 );
                    } else if ( $birthdays_settings[ 'photo_meta_field_enabled' ] ) {
                        if ( $birthdays_settings[ 'photo_meta_field_bp' ] ) {
                            $query = 'field='.$birthdays_settings[ 'photo_meta_field' ].'&user_id='.$user->id;
                            $tmp_user->image = bp_get_profile_field_data( $query );
                        } else {
                            $meta_key = $birthdays_settings[ 'photo_meta_field' ];
                            $tmp_user->image = $user->{$meta_key};
                        }
                    }
                    if ( $birthdays_settings[ 'user_profile_link' ] ) {
                        if ( function_exists( 'bp_core_get_userlink' ) ) {
                            $tmp_user->link = bp_core_get_userlink( $tmp_user->wp_user );
                        } else if ( function_exists( 'um_fetch_user' ) ) {
                            um_fetch_user( $tmp_user->wp_user );
                            $tmp_user->link = '<a href="' . um_user_profile_url() . '" title="' . $tmp_user->name . '" >' . $tmp_user->name . '</a>';
                            um_reset_user();
                        }
                        preg_match_all('!https?://\S+!', $tmp_user->link, $matches);
                        $url = $matches[0];
                        $url = $url[0];
                        $tmp_user->url = $url;
                    }
                    array_push( $results, $tmp_user );
                }
            }
        }
    }
    return $results;
}

function birthdays_widget_callback() {
    @header( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
    @header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
    @header( "Content-Type: text/html; charset=utf-8" );
    //date_default_timezone_set( 'Europe/Athens' );

    $birthdays = birthdays_widget_check_for_birthdays();

    echo count( $birthdays ) .";";
    $flag = true;
    foreach($birthdays as $row){
        if ($flag)
            $flag = false;
        else
            echo ", ";
        echo $row->name;
    }
    
    die(); 
}

function get_birthdays_export_file_callback() {
    global $wpdb;

    if( !is_admin() )
        wp_die( 'Access denied' );

    //date_default_timezone_set( 'Europe/Athens' );

    @header( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
    @header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
    @header( "Content-Type: application/octet-stream" );
    @header( "Content-Disposition: attachment; filename=\"export_birthdays_". date_i18n( get_option( 'date_format' ) ) .".csv\"" );

    $table_name = $wpdb->prefix . "birthdays";
    $results = $wpdb->get_results( "SELECT name, date, email FROM $table_name;", ARRAY_A );
    $output = fopen( "php://output", "w" );

    $birthdays_settings = get_option( 'birthdays_settings' );
    $birthdays_settings = maybe_unserialize( $birthdays_settings );
    $meta_key = $birthdays_settings[ 'meta_field' ];
    $prefix = "cs_birth_widg_";
    foreach( $results as $row ) {
        $wp_usr = strpos( $row[ 'name' ], $prefix );
        if ( $wp_usr !== false ) {
            if ( isset( $_GET[ 'wp_users' ] ) && $_GET[ 'wp_users' ] == 'yes' ) {
                $birth_user = get_userdata( substr( $row[ 'name' ], strlen( $prefix ) ) );
                $row[ 'name' ] = $birth_user->$meta_key;
                $row[ 'email' ] = $birth_user->user_email;
            } else {
                continue;
            }
        }
        fputcsv( $output, $row );
    }
    
    fclose( $output );
    
    $wpdb->__destruct();
    
    die();
}
