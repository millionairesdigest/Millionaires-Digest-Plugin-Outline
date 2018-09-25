<?php
/*
Plugin Name: Millionaire's Digest Birthdays List
Description: Allow the the Millionaire's Digest to keep a list of all the company members birthdays so we can display them on the frontend.
Version: 1.0.0
Author: K&L (Founder of the Millionaire's Digest)
Author URI: https://millionairedigest.com/
*/

    define( 'BW', '1.7.18' );
    require_once dirname( __FILE__ ) . '/class-birthdays-widget.php';
    require_once dirname( __FILE__ ) . '/class-birthdays-widget-installer.php';
    require_once dirname( __FILE__ ) . '/class-birthdays-widget-settings.php';  
    require_once dirname( __FILE__ ) . '/birthdays-widget-ajax-callback.php';   
    
    register_activation_hook( __FILE__ , array( 'Birthdays_Widget_Installer', 'activate' ) );
    register_deactivation_hook( __FILE__ , array( 'Birthdays_Widget_Installer', 'deactivate_multisite' ) );
    add_action( 'wpmu_new_blog', array( 'Birthdays_Widget_Installer', 'new_blog' ), 10, 6 );

    if ( is_admin() )
        $my_settings_page = new Birthdays_Widget_Settings();

    // register Birthdays_Widget widget
    function register_birthdays_widget() {
        register_widget( 'Birthdays_Widget' );
    }
    add_action( 'widgets_init', 'register_birthdays_widget' );

    // register our scripts
    function birthdays_extra_files() {
        wp_register_script( 'birthdays-script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), BW, true );
        wp_register_script( 'birthdays-calendar-js', plugins_url( 'js/bic_calendar.min.js', __FILE__ ), array( 'jquery' ), BW );
        wp_register_script( 'birthdays-bootstrap-js', plugins_url( 'js/bootstrap.min.js', __FILE__ ), array( 'jquery' ), BW );
        wp_register_script( 'datatables', plugins_url( 'js/jquery.dataTables.min.js', __FILE__ ), array( 'jquery' ), BW );
        wp_register_script( 'moment', plugins_url( 'js/moment.min.js', __FILE__ ), array( 'jquery' ), BW );
        wp_register_script( 'birthdays-table-datetime-js', plugins_url( 'js/datetime-moment.js', __FILE__ ), array( 'jquery' ), BW, true );
        
        $handle = 'datatables';
        $list = 'registered';
        if ( wp_script_is( $handle, $list ) ) {
            wp_deregister_script( $handle );
        }
        wp_register_script( 'datatables', plugins_url( 'js/jquery.dataTables.min.js', __FILE__ ), array( 'jquery' ), 'BW' );
        wp_register_style( 'jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css', array(), BW );
        wp_register_style( 'birthdays-calendar-css', plugins_url( 'css/bic_calendar.css', __FILE__ ), array(), BW );
        wp_register_style( 'birthdays-bootstrap-css', plugins_url( 'css/bootstrap.min.css', __FILE__ ), array(), BW );
        wp_register_style( 'birthdays-table-css', plugins_url( 'css/jquery.dataTables.min.css', __FILE__ ), array(), BW );
        wp_register_style( 'birthdays-css', plugins_url( 'css/birthdays-widget.css', __FILE__ ), array(), BW );
    }
    add_action( 'wp_enqueue_scripts', 'birthdays_extra_files' );
    add_action( 'login_enqueue_scripts', 'birthdays_extra_files' );
    add_action( 'admin_enqueue_scripts', 'birthdays_extra_files' );
    add_action( 'siteorigin_panel_enqueue_admin_scripts', 'birthdays_extra_files' );

    function birthdays_widget_action_links($links, $file) {
        static $this_plugin;
        if ( !$this_plugin ) {
            $this_plugin = plugin_basename( __FILE__ );
        }
        if ( $file == $this_plugin ) {
            // The "page" query string value must be equal to the slug
            // of the Settings admin page we defined earlier, which in
            // this case equals "myplugin-settings".
            $settings_link = '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=birthdays-widget">'. _( 'Settings' ) .'</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }
    add_filter( 'plugin_action_links', 'birthdays_widget_action_links', 10, 2 );

    function birthdays_widget_load_languages() {
        load_plugin_textdomain( 'birthdays-widget', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }
    add_action( 'plugins_loaded', 'birthdays_widget_load_languages' );

    $birthdays_settings = get_option( 'birthdays_settings' );
    $birthdays_settings = maybe_unserialize( $birthdays_settings );

    // Feature: User name and User birthday field in User registration form
    // If option is on, enable that feature.
    if ( $birthdays_settings[ 'register_form' ] == TRUE ) {
        add_action( 'register_form', 'birthdays_widget_register_form' );
        add_filter( 'registration_errors', 'birthdays_widget_registration_errors', 10, 3 );
        add_action( 'user_register', 'birthdays_widget_user_register', 10, 1 );
    }
    //1. Add a new form element...
    function birthdays_widget_register_form (){
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'birthdays-script' );
        wp_enqueue_style( 'jquery-style' );

        /* $birthdays_settings = get_option( 'birthdays_settings' );
        $birthdays_settings = maybe_unserialize( $birthdays_settings );
        var_dump( $birthdays_settings ); */

        $date_format_wp = get_option( 'date_format' );
        $date_format = wp_date_to_moment( $date_format_wp, 'datepicker' );
        $first_name = ( isset( $_POST['first_name'] ) ) ? $_POST['first_name']: '';
        $date = ( isset( $_POST['birthday_date'] ) ) ? $_POST['birthday_date']: ''; ?>
        <p>
            <label for="first_name"><?php _e('First Name','birthdays-widget') ?><br />
                <input type="text" name="first_name" id="first_name" class="input" 
                    value="<?php echo esc_attr( stripslashes($first_name) ); ?>" /></label>
            <label for="birthday_date"><?php _e( 'User Birthday', 'birthdays-widget' ); ?></label>
                <input  type="text" id="birthday_date" name="birthday_date" 
                    value="<?php if ( $date != '' ) echo date_i18n( $date_format_wp, strtotime( $date ) ); ?>"
                    data-date-format="<?php echo $date_format; ?>" />
        </p> <?php
    }

    //2. Add validation. No need yet
    function birthdays_widget_registration_errors ( $errors, $sanitized_user_login, $user_email ) {
        return $errors;
    }

    //3. Finally, save our extra registration user meta.
    function birthdays_widget_user_register ( $user_id ) {
        global $wpdb;
        $wp_date_format = get_option( 'date_format' );
        $birthdays_settings = get_option( 'birthdays_settings' );
        $birthdays_settings = maybe_unserialize( $birthdays_settings );
        $plugin_date_format = $birthdays_settings[ 'date_format' ];

        if ( isset( $_POST['first_name'] ) )
            update_user_meta( $user_id, 'first_name', $_POST['first_name'] );

        if ( isset( $_POST[ 'birthday_date' ] ) && empty( $_POST[ 'birthday_date' ] ) )
            return;

        $value = $_POST[ 'birthday_date' ];

        if ( $birthdays_settings[ 'date_from_profile' ] ) {
            if ( function_exists( 'bp_is_active' ) && bp_is_active('activity') ) {
                return;
            }
            //Shall now save it in the profile's correct metafield
            $tmp_date = date_create_from_format( $wp_date_format, $value );
            var_dump( $tmp_date, $plugin_date_format, $value );
            if ( $tmp_date ) {
                $value = $tmp_date->getTimestamp();
            }
            //BuddyPress has it's own registration form
            $birthday_date_meta_field = $birthdays_settings[ 'date_meta_field' ];
            update_user_meta( $user_id, $birthday_date_meta_field, date( $plugin_date_format , $value ) );
        } else if ( $birthdays_settings[ 'profile_page' ] ) {
            //Shall now save it in our database table
            $birth_user = "cs_birth_widg_" . $user_id;
            $table_name = $wpdb->prefix . 'birthdays';

            //add the new entry
            $insert_query = "INSERT INTO $table_name (name, date) VALUES (%s, %s);";
            $tmp_date = date_create_from_format( $wp_date_format, $value );
            if ( $tmp_date ) {
                $value = $tmp_date->format( 'Y-m-d' );
            }
            if ( $wpdb->query( $wpdb->prepare( $insert_query, $birth_user, $value ) ) != 1 ) {
                $error = 'Query error in User Registration. Query: ' . $insert_query;
                file_put_contents( __DIR__.'/debug.txt', ob_get_contents() );
                file_put_contents( __DIR__.'/debug.txt', $error );
            }
            $birth_id = $wpdb->insert_id;
            update_user_meta( $user_id, 'birthday_id', $birth_id, '' );
        }
    }

    // Feature: User name and User birthday field in User profile in admin section
    // If option is on, enable that feature.
    if ( $birthdays_settings[ 'profile_page' ] == TRUE ) {
		add_action( 'profile_update', 'birthdays_widget_update_profile' );
		add_action( 'edit_user_profile', 'birthdays_widget_usr_profile' );
		add_action( 'show_user_profile', 'birthdays_widget_usr_profile' );
        add_action( 'delete_user', 'birthdays_delete_user' );
    }    

	//1. Add new element to profile page, user birthday field
    function birthdays_widget_usr_profile() {
        global $wpdb;
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'birthdays-script' );
        wp_enqueue_style( 'jquery-style' );

        $date_format_wp = get_option( 'date_format' );
        $date_format = wp_date_to_moment( $date_format_wp, 'datepicker' );

        if ( isset($_GET['user_id'] ) )
            $user_id = $_GET['user_id'];
        else
            $user_id = get_current_user_id();

        $id = get_user_meta( $user_id, 'birthday_id' );
        if ( empty( $id ) ) {
			unset( $id );
            unset( $date );
        } else {
            $id = $id[ 0 ];
            $table_name = $wpdb->prefix . "birthdays";
            $query = "SELECT date FROM $table_name WHERE id = '%d' ;";
            $results = $wpdb->get_results( $wpdb->prepare( $query, $id ) );
            if ( empty( $results ) ) {
                delete_user_meta( $user_id, 'birthday_id' );
                unset( $id );
                unset( $date );
            } else {
                $date = $results[ 0 ]->date;
            }
        } ?>
            <table class="form-table">
                <tr>
                    <th><label for="birthday_date"><?php _e( 'User Birthday', 'birthdays-widget' ); ?></label></th>
                    <td><input type="text" size="18" id="birthday_date" name="birthday_date" placeholder="<?php echo date_i18n( $date_format_wp ); ?>"
                        <?php if ( isset( $date ) ) {
                                $tmp_date = date_create_from_format( 'Y-m-d', $date );
                                $date = $tmp_date->getTimestamp();
                                echo 'value="' . date_i18n( $date_format_wp, $date ) . '" data-date-format="' . $date_format . '" />';
                              } else {
                                echo 'value="" data-date-format="' . $date_format . '" />'; 
                        } ?>
                        <br /><span class="description"><?php _e( 'Please enter user\'s birthday requested by Birthdays Widget', 'birthdays-widget' ); ?></span>
						<input type="hidden" name="birthday_usr_id" value="<?php echo $user_id; ?>" />
        <?php 
        if ( isset( $id ) ) {
            echo '<input type="hidden" name="birthday_id" value="' . $id . '" />';
            echo '</td></tr><tr><th><label for="birthday_id_delete">' . __( 'Delete Birthday', 'birthdays-widget' ) . '</label></th>'
                .'<td><input type="radio" name="birthday_id_delete" value="1"> Yes</td>';
        }
        echo '      </td> 
                </tr></table>';
    }

	//2. Validate and update field in WP user structure
    function birthdays_widget_update_profile() {
        global $wpdb;
        $date_format = get_option( 'date_format' );

        if ( !isset( $_POST[ 'birthday_id' ] ) && empty( $_POST[ 'birthday_date' ] ) )
            return;

        if ( isset( $_POST[ 'birthday_id_delete' ] ) && $_POST[ 'birthday_id_delete' ] == 1 ) {
            birthdays_delete_user( $_POST[ 'birthday_usr_id' ] );
            return;
        }
        $user_id = $_POST[ 'birthday_usr_id' ];
        $value = $_POST[ 'birthday_date' ];
		//Shall now save it in our database table
        $birth_user = "cs_birth_widg_" . $user_id;
        $table_name = $wpdb->prefix . 'birthdays';

        if ( !isset( $_POST[ 'birthday_id' ] ) ) {
            //add the new entry
            $insert_query = "INSERT INTO $table_name (name, date) VALUES (%s, %s);";
            $tmp_date = date_create_from_format( $date_format, $value );
            if ( $tmp_date ) {
                $value = $tmp_date->format( 'Y-m-d' );
            }
            if ( $wpdb->query( $wpdb->prepare( $insert_query, $birth_user, $value ) ) != 1 )
                echo '<div id="message" class="error"><p>' . __( 'Query error', 'birthdays-widget' ) . '</p></div>';
            $birth_id = $wpdb->insert_id;
            update_user_meta( $user_id, 'birthday_id', $birth_id, '' );
        } else {
            //update the existing entry
            $update_query = "UPDATE $table_name SET date = %s, name = %s WHERE id = %d;";
            $tmp_date = date_create_from_format( $date_format, $value );
            if ( $tmp_date ) {
                $value = $tmp_date->format( 'Y-m-d' );
            }
            if ( $wpdb->query( $wpdb->prepare( $update_query, $value, $birth_user, $_POST[ 'birthday_id' ] ) ) != 1 ) {
                echo '<div id="message" class="error"><p>' . __( 'Query error', 'birthdays-widget' ) . '</p></div>';
            }
        }
    }
    
    //3. Remove WP user from our table upon delete of user
    function birthdays_delete_user( $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'birthdays';

        $user_birthday_id = get_user_meta( $user_id, 'birthday_id' );
        $delete_query = "DELETE FROM $table_name WHERE id = %d;";
        if ( $wpdb->query( $wpdb->prepare( $delete_query, $user_birthday_id ) ) != 1 )
            echo '<div id="message" class="error"><p>' . __( 'Query error', 'birthdays-widget' ) . '</p></div>';
    }

    // Feature: Shortcode for birthdays in pages/posts
    function birthdays_shortcode( $atts ) {
        $birthdays_settings = get_option( 'birthdays_settings' );
        $birthdays_settings = maybe_unserialize( $birthdays_settings );
        $attr = shortcode_atts( array(
            'class' => '',
            'img_width' => $birthdays_settings[ 'image_width' ],
            'template' => '0'
        ), $atts );
        if ( $attr[ 'template' ] == 'default' ) {
            $attr[ 'template' ] = 0;
        } else if ( $attr[ 'template' ] == 'list' ) {
            $attr[ 'template' ] = 1;
        } else if ( $attr[ 'template' ] == 'calendar' ) {
            $attr[ 'template' ] = 2;
        } else if ( $attr[ 'template' ] == 'upcoming' ) {
            $attr[ 'template' ] = 3;
        } else {
            $attr[ 'template' ] = 0;
        }
        $instance = array( 'class' => $attr[ 'class' ], 'img_width' => $attr[ 'img_width' ], 'template' => $attr[ 'template' ] );
        if ( $attr[ 'template' ] == 2 || $attr[ 'template' ] == 3 ) {
            $birthdays = birthdays_widget_check_for_birthdays( true );
        } else {
            $birthdays = birthdays_widget_check_for_birthdays();
        }
        if ( count( $birthdays ) >= 1 ) {
            return Birthdays_Widget::birthdays_code( $instance, $birthdays, $birthdays_settings );
        } elseif ( $birthdays_settings[ 'empty_response' ] ) {
            wp_enqueue_style ( 'birthdays-css' );
            return '<div class="birthday_error">'. $birthdays_settings[ 'empty_response_text' ] . '</div>';
        }
    }
    add_shortcode( 'birthdays', 'birthdays_shortcode' );

    // Feature: Add button for shortcode in WordPress editor
    // (thanks to: http://wordpress.stackexchange.com/questions/72394/how-to-add-a-shortcode-button-to-the-tinymce-editor)
    add_action('init', 'birthdays_shortcode_button_init');
    function birthdays_shortcode_button_init() {
          //Abort early if the user will never see TinyMCE
          if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) && get_user_option( 'rich_editing' ) == 'true' )
               return;

          //Add a callback to regiser our tinymce plugin   
          add_filter( "mce_external_plugins", "birthdays_register_tinymce_plugin" );

          // Add a callback to add our button to the TinyMCE toolbar
          add_filter( 'mce_buttons', 'birthdays_add_tinymce_button' );
    }

    //This callback registers our plug-in
    function birthdays_register_tinymce_plugin( $plugin_array ) {
        $plugin_array[ 'birthdays_button' ] = plugins_url() . '/birthdays-widget/js/shortcode.js';
        return $plugin_array;
    }

    //This callback adds our button to the toolbar
    function birthdays_add_tinymce_button( $buttons ) {
        //Add the button ID to the $button array
        $buttons[] = "birthdays_button";
        return $buttons;
    }

    //Check if there is a need to update
    function update_birthdays_widget() {
        $birthdays_settings = get_option( 'birthdays_settings' );
        $birthdays_settings = maybe_unserialize( $birthdays_settings );

        if ( !isset( $birthdays_settings[ 'version' ] ) || ( $birthdays_settings[ 'version' ] != BW ) ) {
            Birthdays_Widget_Installer::install();
            $birthdays_settings = get_option( 'birthdays_settings' );
            $birthdays_settings = maybe_unserialize( $birthdays_settings );
        }
    }
    add_action( 'plugins_loaded', 'update_birthdays_widget' );

    function cmp( $a, $b ) {
        return strcasecmp( $a->name, $b->name );
    }

    function wp_date_to_moment( $format, $type ) {
        $count = 1;
        if( $type == 'moment' ) {
            $format = str_replace( 'I', '', $format, $count );
            $format = str_replace( 'D', '', $format, $count );

            $format = str_replace( 'jS', 'Do', $format, $count );
            $format = str_replace( 'j', 'D', $format, $count );
            $format = str_replace( 'd', 'DD', $format, $count );

            $format = str_replace( 'M', 'MMM', $format, $count );
            $format = str_replace( 'm', 'MM', $format, $count );
            $format = str_replace( 'n', 'M', $format, $count );
            $format = str_replace( 'F', 'MMMM', $format, $count );

            $format = str_replace( 'Y', 'YYYY', $format, $count );
            return $format;
        } else if ( $type == 'datepicker' ) {
            //var_dump( $format );
            $format = str_replace( 'd', 'dd', $format, $count );
            $format = str_replace( 'jS', 'dd', $format, $count );
            $format = str_replace( 'j', 'dd', $format, $count );

            $format = str_replace( 'M', 'M', $format, $count );
            $format = str_replace( 'm', 'mm', $format, $count );
            $format = str_replace( 'n', 'mm', $format, $count );
            $format = str_replace( 'F', 'MM', $format, $count );

            $format = str_replace( 'Y', 'yy', $format, $count );
            //var_dump( $format );
            return $format;
        } else if ( $type == 'international_date' ) {
            //var_dump( $format );
            $format = str_replace( 'd', 'dd', $format, $count );
            $format = str_replace( 'jS', 'dd', $format, $count );
            $format = str_replace( 'j', 'dd', $format, $count );

            $format = str_replace( 'M', 'MMM', $format, $count );
            $format = str_replace( 'm', 'MM', $format, $count );
            $format = str_replace( 'n', 'M', $format, $count );
            $format = str_replace( 'F', 'MMMM', $format, $count );

            $format = str_replace( 'Y', 'yyyy', $format, $count );

            return $format;
        }
    }

    function get_international_date( $date ) {
        //Must remain WordPress date_format option to get the correct date from BuddyPress
        $format = wp_date_to_moment( get_option( 'date_format' ), "international_date" );
        if ( !class_exists( "IntlDateFormatter" ) ) {
            return 'intl';
        }
        $formatter = new IntlDateFormatter(
            get_locale(),
            IntlDateFormatter::LONG,
            IntlDateFormatter::LONG,
            'GMT',
            IntlDateFormatter::GREGORIAN,
            $format
        );
        $value = $formatter->parse( $date );
        unset( $formatter );
        return $value;
    }
    
    register_activation_hook( __FILE__, 'my_activation_func' ); 
    function my_activation_func() {
        file_put_contents( __DIR__.'/debug.txt', ob_get_contents() );
    }
    /* 
    // Scheduled Action Hook
    function birthdays_email_notifier( ) {
        $birthdays_settings = get_option( 'birthdays_settings' );
        $birthdays_settings = maybe_unserialize( $birthdays_settings );
        $birthdays = birthdays_widget_check_for_birthdays( false );
        $email_text = __( 'Hello ###USERNAME###,
         
        We at  would like to wish you a happy birthday today!
         
        Regards,
        All at ###SITENAME###
        ###SITEURL###' );

        //TODO check for filters
        //$content = apply_filters( 'new_admin_email_content', $email_text, $new_admin_email );
        $content = $email_text;
        $prefix = "cs_birth_widg_";
        foreach ( $birthdays as $row ) {
            //var_dump ( $row );
            if ( !isset( $row->email ) || empty( $row->email ) ) {
                continue;
            }
            $wp_usr = strpos( $row->name, $prefix );
            if ( $wp_usr !== false ) {
                //Get the ID from the record, which is of the format $prefixID and get the user's data
                $birth_user = get_userdata( substr( $row->name, strlen( $prefix ) ) );
                //If birthdays are enabled for WP Users, draw user's name from the corresponding meta key
                if ( $birthdays_settings[ 'meta_field_bp' ] ) {
                    $query = 'field='.$birthdays_settings[ 'meta_field' ].'&user_id='.$birth_user->id;
                    $row->name = bp_get_profile_field_data( $query );
                } else if ( $birthdays_settings[ 'profile_page' ] && !$birthdays_settings[ 'meta_field_bp' ] ) {
                    $row->name = $birth_user->{$meta_key};
                }
            }
            $email_content = str_replace( '###USERNAME###', ucfirst( $row->name ), $content );
            $email_content = str_replace( '###SITENAME###', get_site_option( 'site_name' ), $email_content );
            $email_content = str_replace( '###SITEURL###', network_home_url(), $email_content );
            //echo "<br /><br />";
            //var_dump ( $email_content );
            //echo "<br /><br />";
            wp_mail( $birth_user->email, sprintf( __( '[%s] - Happy Birtday' ), wp_specialchars_decode( get_option( 'blogname' ) ) ), $email_content );
        }
    }

    // Schedule Cron Job Event
    function birthdays_email() {
        if ( ! wp_next_scheduled( 'birthdays_email_notifier' ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'daily', 'birthdays_email_notifier' );
        }
    }
    add_action( 'wp', 'birthdays_email' ); */