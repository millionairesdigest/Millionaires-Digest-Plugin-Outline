<?php
    class Birthdays_Widget_Installer{
        
        static function install() {
            global $wpdb;
            $table_name = $wpdb->prefix . "birthdays"; 
            //dbDelta is responsible to alter the table if necessary
            $sql = "CREATE TABLE `$table_name` (
                      id int(11) NOT NULL AUTO_INCREMENT,
                      name text NOT NULL,
                      date date NOT NULL,
                      email varchar(200) DEFAULT NULL,
                      image varchar(500) DEFAULT NULL,
                      UNIQUE KEY id (id)
                    ) DEFAULT CHARSET=utf8;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            //add some default options
            $birthdays_settings = array(
                'widget_installed' => '1',
                'register_form' => '0',
                'profile_page' => '0',
                'user_profile_link' => '0',
                'date_from_profile' => '0',
                'wp_user_gravatar' => '0',
                'meta_field' => 'display_name',
                'meta_field_bp' => '0',
                'date_format' => 'Y-m-d',
                'comma' => '1',
                'bdpress_friends_only' => '0',
                'user_data' => '2',
                'date_meta_field' => '',
                'date_meta_field_bp' => '0',
                'photo_meta_field' => '',
                'photo_meta_field_bp' => '0',
                'photo_meta_field_enabled' => '0',
                'image_url' => plugins_url( '/images/birthday_cake.png' , __FILE__ ),
                'image_width' => '55%',
                'list_image_width' => '20%',
                'image_enabled' => '1',
                'user_image_url' => plugins_url( '/images/default_user.png' , __FILE__ ),
                'user_image_enabled' => '1',
                'user_verbiage' => '1',
                'user_verbiage_text' => 'years old',
                'upcoming_mode' => '0',
                'upcoming_days_birthdays' => '3',
                'upcoming_consecutive_days' => '3',
                'wish' => __( 'Happy Birthday', 'birthdays-widget' ),
                'wish_disabled' => 0,
                'empty_response' => 1,
                'empty_response_text' => __( 'No records for these days', 'birthdays-widget' ),
                'upcoming_year' => 0,
                'upcoming_year_seperate' => 0,
                'tooltip' => 1,
                'color_current_day' => '#FF8000',
                'color_one' => '#BE1E2D',
                'second_color' => '0',
                'color_two' => '#cc7722',
                'roles' => array( 'Administrator' => 'administrator' )
                );
            //if the plugin was installed, do not lose previous settings
            if ( !( $tmp = get_option( 'birthdays_settings' ) ) ) {
                $birthdays_settings[ 'version' ] = BW;
                $birthdays_settings = maybe_serialize( $birthdays_settings );
                add_option( 'birthdays_settings', $birthdays_settings );
            } else {
                $old_birthdays_settings = maybe_unserialize( $tmp );
                $birthdays_settings = array_merge( $birthdays_settings, $old_birthdays_settings );
                $birthdays_settings[ 'version' ] = BW;
                $birthdays_settings = maybe_serialize( $birthdays_settings );
                update_option( 'birthdays_settings', $birthdays_settings );
            }
            $birthdays_settings = maybe_unserialize( $birthdays_settings );
            foreach( $birthdays_settings[ 'roles' ] as $key => $role ) {
                $tmp = get_role( $role );
                if( $tmp != NULL ) {
                    $tmp->add_cap( 'birthdays_list' ); 
                }
            }
            return;
        }

        static function unistall() {
            $birthdays_settings = get_option( 'birthdays_settings' );
            foreach( $birthdays_settings[ 'roles' ] as $role ) {
                $role = get_role( $role[ 0 ] );
                $role->remove_cap( 'birthdays_list' ); 
            }
            //delete plugin's options
            delete_option( 'birthdays_settings' );

            //delete all of our user meta
            $users = get_users( array( 'fields' => 'id' ) );
            foreach ( $users as $id ) {
                delete_user_meta( $id, 'birthday_id' );
            }

            //drop a custom db table
            global $wpdb;
            $table_name = $wpdb->prefix . "birthdays";
            $sql = "DROP TABLE IF EXISTS `$table_name`;" ;
            $wpdb->query( $sql );
        }

        static function activate() {
            if ( ! current_user_can ( 'activate_plugins' ) )
                return "You cannot activate it";

            return Birthdays_Widget_Installer::install();
        }

        static function deactivate() {
            if ( ! get_option( 'birthdays_settings' ) ) {
                $new = array();
                $new[ 'meta_field' ] = get_option( 'birthdays_meta_field' );
                if ( $new[ 'meta_field' ] == false )
                    $new[ 'meta_field' ] = 'display_name';
                $new[ 'date_from_profile' ] = get_option( 'birthdays_date_from_profile' );
                if ( $new[ 'date_from_profile' ] == false )
                    $new[ 'date_from_profile' ] = '2';
                $new[ 'date_meta_field' ] = get_option( 'birthdays_date_meta_field' );
                if ( $new[ 'date_meta_field' ] == false )
                    $new[ 'date_meta_field' ] = '';
                $new[ 'wish' ] = get_option( 'birthdays_wish' );
                if ( $new[ 'wish' ] == false )
                    $new[ 'wish' ] = __( 'Happy Birthday', 'birthdays-widget' );

                $birthdays_settings = array(
                    'widget_installed' => get_option( 'Birthdays_Widget_Installed' ),
                    'register_form' => get_option( 'birthdays_register_form' ),
                    'profile_page' => get_option( 'birthdays_profile_page' ),
                    'meta_field' => $new[ 'meta_field' ],
                    'comma' => '1',
                    'user_data' => $new[ 'date_from_profile' ],
                    'date_meta_field' => $new[ 'date_meta_field' ],
                    'image_url' => get_option( 'birthdays_widget_image' ),
                    'image_width' => get_option( 'birthdays_widget_image_width' ),
                    'image_enabled' => get_option( 'birthdays_widget_img' ),
                    'wish' => $new[ 'wish' ],
                    'roles' => get_option( 'birthdays_widget_roles' )
                    );
                $birthdays_settings = maybe_serialize( $birthdays_settings );
                add_option( 'birthdays_settings', $birthdays_settings );
                
                delete_option( 'Birthdays_Widget_Installed' );
                delete_option( 'birthdays_meta_field' );
                delete_option( 'birthdays_date_from_profile' );
                delete_option( 'birthdays_date_meta_field' );
                delete_option( 'birthdays_register_form' );
                delete_option( 'birthdays_profile_page' );
                delete_option( 'birthdays_widget_image' );
                delete_option( 'birthdays_widget_image_width' );
                delete_option( 'birthdays_widget_img' );
                delete_option( 'birthdays_wish' );
                delete_option( 'birthdays_widget_roles' );
            }
            $birthdays_settings = get_option( 'birthdays_settings' );
            $birthdays_settings = maybe_unserialize( $birthdays_settings );
            if ( ! isset( $birthdays_settings[ 'comma' ] ) ) {
                $birthdays_settings[ 'comma' ] = 1;
                update_option( 'birthdays_settings', $birthdays_settings );
            }
        }
    }
