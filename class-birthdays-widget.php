<?php
/**
 * Adds Birthdays_Widget widget.
 */
class Birthdays_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'birthdays_widget', // Base ID
            __('Millionaire\'s Digest Birthdays'), // Name
            array( 'description' => __( 'Display the current and upcoming members who\'s birthday it is as a widget.', 'birthdays-widget' ), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        $birthdays_settings = get_option( 'birthdays_settings' );
        $birthdays_settings = maybe_unserialize( $birthdays_settings );
        wp_enqueue_style ( 'birthdays-css' );
        if ( $instance[ 'template' ] == 2 || $instance[ 'template' ] == 3 ) {
            $birthdays = birthdays_widget_check_for_birthdays( true );
        } else {
            $birthdays = birthdays_widget_check_for_birthdays();
        }
        if ( count( $birthdays ) >= 1 ) {
            $title = apply_filters( 'widget_title', $instance[ 'title' ] );
            echo $args[ 'before_widget' ];
            if ( ! empty( $title ) )
                echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];
            echo self::birthdays_code( $instance, $birthdays, $birthdays_settings );
            /* TODO make again ajax support?
                wp_enqueue_script('birthdays-widget-script', plugins_url('script.js', __FILE__ ), array('jquery'));
                wp_localize_script('birthdays-widget-script', 'ratingsL10n', array( 'admin_ajax_url' => admin_url('admin-ajax.php')));
            */
            echo $args[ 'after_widget' ];
        } elseif ( $birthdays_settings[ 'empty_response' ] ) {
            $title = apply_filters( 'widget_title', $instance[ 'title' ] );
            echo $args[ 'before_widget' ];
            if ( ! empty( $title ) )
                echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];
            echo '<div class="birthday_error">'. $birthdays_settings[ 'empty_response_text' ] . '</div>';
            echo $args[ 'after_widget' ];
        }
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $birth_widg = get_option( 'birthdays_widget_settings' );
        $birth_widg = maybe_unserialize( $birth_widg );
        $instance = wp_parse_args( (array) $instance, $birth_widg );
        if ( !isset( $instance[ 'title' ] ) )
            $instance[ 'title' ] = "Birthdays Widget";
        if ( !isset( $instance[ 'template' ] ) )
            $instance[ 'template' ] = 0;
        ?>
        <p><fieldset class="basic-grey">
            <legend><?php _e( 'Settings', 'birthdays-widget' ); ?>:</legend>
            <label>
                <span><?php _e( 'Title', 'birthdays-widget' ); ?></span>
                <input  id="<?php echo $this->get_field_id( 'title' ); ?>" 
                        name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" 
                        value="<?php empty( $instance[ 'title' ] ) ? '' : esc_attr_e( $instance[ 'title' ] ) ; ?>" />
            </label>
            <label>
                <span><?php _e( 'Template', 'birthdays-widget' ); ?></span>
                <select id="<?php echo $this->get_field_id( 'template' ); ?>" 
                        name="<?php echo $this->get_field_name( 'template' ); ?>">
                    <option value="0" <?php if ( $instance[ 'template' ] == 0 ) echo "selected='selected'"; ?>><?php _e( 'Default', 'birthdays-widget' ); ?></option>
                    <option value="1" <?php if ( $instance[ 'template' ] == 1 ) echo "selected='selected'"; ?>><?php _e( 'List', 'birthdays-widget' ); ?></option>
                    <option value="2" <?php if ( $instance[ 'template' ] == 2 ) echo "selected='selected'"; ?>><?php _e( 'Calendar', 'birthdays-widget' ); ?></option>
                    <option value="3" <?php if ( $instance[ 'template' ] == 3 ) echo "selected='selected'"; ?>><?php _e( 'Upcoming', 'birthdays-widget' ); ?></option>
                </select>
            </label>
        </fieldset></p>
        <?php
    }

    public static function organize_days( $filtered ) {
        $days_organized = array();
        foreach ( $filtered as $user_birt ) {
            $tmp = date( 'Y-m-d', $user_birt->date );
            if( !isset( $user_birt->date ) )
                var_dump( $user_birt );
            $user_birt->tmp = substr( $tmp, 5 );
            if ( !isset ( $days_organized[ $user_birt->tmp ] ) ) {
                $days_organized[ $user_birt->tmp ] = array();
            }
            $days_organized[ $user_birt->tmp ][] = $user_birt;
        }
        return $days_organized;
    }

    public static function birthdays_code( $instance, $birthdays = NULL, $birthdays_settings ) {
        $html = "";
		$html_tmp = "";
        if ( isset( $instance[ 'img_width' ] ) ) {
            $birthdays_settings[ 'image_width' ] = $instance[ 'img_width' ];
        }
        if ( !isset( $instance[ 'class' ] ) ) {
            $instance[ 'class' ] = '';
        }
        if ( !isset( $instance[ 'template' ] ) ) {
            $instance[ 'template' ] = 0;
        }
        if ( $birthdays_settings[ 'tooltip' ] ) {
            $html_tmp = "birthdays-tooltip-enabled";
        }
        $html .= "<div class=\"birthdays-widget {$instance[ 'class' ]} {$html_tmp}\">";
            if ( $birthdays_settings[ 'image_enabled' ] ) {
                if ( is_numeric( $birthdays_settings[ 'image_url' ] ) ) {
                    $default_image_src = wp_get_attachment_image_src( $birthdays_settings[ 'image_url' ], 'medium' );
                    $default_image_src = $default_image_src[ 0 ];
                } else {
                    $default_image_src = $birthdays_settings[ 'image_url' ];
                }
                $html .= "<img style=\"width: {$birthdays_settings[ 'image_width' ]}\" 
                    src=\"$default_image_src\" alt=\"birthday_cake\" class=\"aligncenter birthday_wish_image\" />";
            }
            if ( $birthdays_settings[ 'user_image_enabled' ] ) {
                if ( is_numeric( $birthdays_settings[ 'user_image_url' ] ) ) {
                    $default_user_image_src = wp_get_attachment_image_src( $birthdays_settings[ 'user_image_url' ], 'medium' );
                    $default_user_image_src = $default_user_image_src[ 0 ];
                } else {
                    $default_user_image_src = $birthdays_settings[ 'user_image_url' ];
                }
            }
            if ( !$birthdays_settings[ 'wish_disabled' ] ) {
                $html .= "<div class=\"birthday_wish\">{$birthdays_settings[ 'wish' ]}</div>";
            }
            /*
             * For each user that has birthday today, if his name is
             * in the cs_birth_widg_# format (which means he is a WP User),
             * show his name if and only if the option to 
             * save Users' birthdays in our table is enabled.
             */
            $meta_key = $birthdays_settings[ 'meta_field' ];
            $photo_meta_key = $birthdays_settings[ 'photo_meta_field' ];
            $prefix = "cs_birth_widg_";
            $filtered = array();
            $year = true;

            $filtered_first = array();
            if ( function_exists( 'friends_get_friend_user_ids' ) && $birthdays_settings['bdpress_friends_only'] ) {
                $friends = friends_get_friend_user_ids( get_current_user_id() );
                foreach ( $birthdays as $user ) {
                    //var_dump( $user->id );
                    //If user not in friend list 
                    if ( !in_array( $user->id, $friends ) ) {
                        continue;
                    }
                    array_push( $filtered_first, $user );
                }
            } else {
                $filtered_first = $birthdays;
            }
            
			foreach ( $filtered_first as $row ) {
                //Check if this is record represents a WordPress user
                $wp_usr = strpos( $row->name, $prefix );

                if ( $wp_usr !== false ) {
                    //If birthdays are disabled for WP Users, or birthday date is drown from WP Profile, skip the record
                    if ( ( $birthdays_settings[ 'profile_page' ] == 0 && $birthdays_settings[ 'date_from_profile' ] == 0 ) 
                        || $birthdays_settings[ 'date_from_profile' ] ) {
                        continue;
                    }
                    //Get the ID from the record, which is of the format $prefixID and get the user's data
                    $birth_user = get_userdata( substr( $row->name, strlen( $prefix ) ) );
                    //If user's image is drawn from Gravatar
                    if ( $birthdays_settings[ 'wp_user_gravatar' ] ) {
                        if ( $instance[ 'template' ] == 2 ) {
                            $row->image = Birthdays_Widget_Settings::get_avatar_url( $birth_user->user_email, 96 );
                        } else {
                            $row->image = Birthdays_Widget_Settings::get_avatar_url( $birth_user->user_email, 256 );
                        }
                    }
                    //If birthdays are enabled for WP Users, draw user's name from the corresponding meta key
                    if ( $birthdays_settings[ 'meta_field_bp' ] ) {
                        $query = 'field='.$birthdays_settings[ 'meta_field' ].'&user_id='.$birth_user->id;
                        $row->name = bp_get_profile_field_data( $query );
                    } else if ( $birthdays_settings[ 'profile_page' ] && !$birthdays_settings[ 'meta_field_bp' ] ) {
                        $row->name = $birth_user->{$meta_key};
                    }
                }

                if ( $birthdays_settings[ 'photo_meta_field_enabled' ] && $wp_usr !== false && !$birthdays_settings[ 'wp_user_gravatar' ] ) {
                    if ( $birthdays_settings[ 'photo_meta_field_bp' ] ) {
                        $query = 'field='.$photo_meta_key.'&user_id='.$birth_user->id;
                        $row->image = bp_get_profile_field_data( $query );
                    } else {
                        $row->image = $birth_user->{$photo_meta_key};
                    }
                }
                if ( isset( $row->image ) && ( is_numeric( $row->image ) || $row->image == NULL ) ) {
                    if ( $instance[ 'template' ] == 2 ) {
                        $row->image = wp_get_attachment_image_src( $row->image, array( 150, 150 ) );
                    } else {
                        $row->image = wp_get_attachment_image_src( $row->image, 'medium' );
                    }
                    $row->image = $row->image[ 0 ];
                }
                //If user has no image, set the default
                if ( ( !isset( $row->image ) || empty( $row->image ) ) && $birthdays_settings[ 'user_image_enabled' ] ) {
                    $row->image = $default_user_image_src;
                }
                array_push( $filtered, $row );
            }
            //var_dump( $filtered );
            switch ( $instance[ 'template' ] ) {
                case 0:
                    wp_enqueue_script( 'jquery-ui-tooltip' );
                    wp_enqueue_script( 'birthdays-script' );
                    wp_enqueue_style ( 'jquery-style' );
                    $flag = false;
                    uasort( $filtered, "cmp" );
                    foreach ( $filtered as $row ) {
                        $html .= '<div class="birthday_element birthday_name">';
                        if ( $flag && $birthdays_settings[ 'comma' ] ) {
                            $html .= ', ';
                        } else {
                            $flag = true;
                        }
                        if ( isset( $row->link ) ) {
                            $html .= $row->link;
                        } else {
                            $html .= $row->name;
                        }
                        $age = date( "Y" ) - date( "Y", $row->date );
                        $tmp_class = "";
                        if ( $birthdays_settings[ 'user_image_enabled' ] ) {
                            $tmp_class = 'class="user_image_enabled"';
                        }
                        $html .= '<a href="' . $row->image . '" target="_blank" ' . $tmp_class . ' ';
                        if( $birthdays_settings[ 'user_verbiage' ] ) {
                            $html .= 'data-age="' . $age . ' ' . $birthdays_settings[ 'user_verbiage_text' ] . '" ';
                        }
                        $html .= '></a></div>';
                    }
                    break;
                //List Mode
                case 1:
                    $html .= '<ul class="birthday_list">';
                    uasort( $filtered, "cmp" );
                        foreach ( $filtered as $row ) {
                            $html .= "<li class=\"birthday_name\">";
                            if ( $birthdays_settings[ 'user_image_enabled' ] ) {
                                $tmp = "<img style=\"width:{$birthdays_settings[ 'list_image_width' ]}\" 
                                    src=\"{$row->image}\" class=\"birthday_list_image\" />";
                                if ( isset( $row->link ) ) {
                                    $html .= '<a href="' . $row->url . '" >' . $tmp . '</a>';
                                } else {
                                    $html .= $tmp;
                                }
                            }
                            if ( isset( $row->link ) ) {
                                $html .= $row->link;
                            } else {
                                $html .= $row->name;
                            }
                            if( $birthdays_settings[ 'user_verbiage' ] ) {
                                $age = date( "Y" ) - date( "Y", $row->date );
                                $html .= '<span class="birthday_age"> ' . $age . ' ' . $birthdays_settings[ 'user_verbiage_text' ] . '</span>';
                            }
                            $html .= "</li>";
                        }
                    $html .= '</ul>';
                    break;
                //Calendar Mode
                case 2:
                    if ( defined( 'CALENDAR' ) ) {
                        $html .= "<span class=\"description\">" . __( 'Only one calendar template is available per page. Please check your widget and shortcode options.', 'birthdays-widget' ) . "</span>";
                        break;
                    }
                    define( 'CALENDAR' , true );
                    //uasort( $filtered, "cmp" );
                    $days_organized = self::organize_days( $filtered );
                    wp_enqueue_style( 'birthdays-bootstrap-css' );
                    wp_enqueue_style( 'birthdays-calendar-css' );
                    wp_enqueue_script( 'birthdays-bootstrap-js' );
                    wp_enqueue_script( 'birthdays-calendar-js' );
                    global $wp_locale;
                    $months = array();
                    for( $i = 1; $i <= 12; $i++ ) {
                        $months[] = $wp_locale->get_month( $i );
                    }
                    $week_days = array();
                    for( $i = 0; $i <= 6; $i++ ) {
                        $week_days[] = $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( $i ) );
                    }
                    $week_days[] = array_shift( $week_days );
                    if ( get_locale() == 'el' ) {
                        for( $i = 0; $i <= 11; $i++ ) {
                            $months[ $i ] = mb_strcut( $months[ $i ], 0, strlen( $months[ $i ] ) - 1 );
                            $months[ $i ] .= "ς";
                        }
                    }
                    //var_dump ( $days_organized );
                    $months = implode( '", "', $months );
                    $months = '[ "'.$months.'" ]';
                    $week_days = implode( '", "', $week_days );
                    $week_days = '[ "'.$week_days.'" ]';
                    $html .= '<script>
                        jQuery( document ).ready( function() {
                            var monthNames = ' . $months . ';
                            var dayNames = ' . $week_days . ';
                            var events = [ ';
                                $flag = false;
                                foreach ( $days_organized as $day ) {
                                    $html .= '{ date: "' . date( 'j/n', $day[ 0 ]->date ) . '/' . date( 'Y' ) . '",';
                                    $html .= 'title: \'' . $birthdays_settings[ 'wish' ] . '\',';
                                    if ( date( 'm-d', $day[ 0 ]->date ) == date( 'm-d' ) ) {
                                        $color = $birthdays_settings[ 'color_current_day' ];
                                    } else if ( $flag && $birthdays_settings[ 'second_color' ] ) {
                                        $color = $birthdays_settings[ 'color_two' ];
                                        $flag = false;
                                    } else {
                                        $color = $birthdays_settings[ 'color_one' ];
                                        $flag = true;
                                    }
                                    $html .= ' color: "' . $color . '",';
                                    $html .= ' content: \''; 
                                    $comma = false;
                                    foreach ( $day as $user ) {
                                        if ( $birthdays_settings[ 'user_image_enabled' ] ) {
                                            $tmp = '<img src="' . $user->image . '" width="150" />';
                                            if( isset( $user->link ) ) {
                                                $html .= '<a href="' . $user->url . '" >' . $tmp . '</a>';
                                            } else {
                                                $html .= $tmp;
                                            }
                                        }
                                        $html .= '<div class="birthday_center birthday_name';
                                        if( isset( $user->link ) ) {
                                            $html .= ' birthday_name_link">' . $user->link;
                                        } else {
                                            $html .= '">' . $user->name;
                                        }
                                        if( $birthdays_settings[ 'user_verbiage' ] ) {
                                            $age = date( "Y" ) - date( "Y", $user->date );
                                            $html .= '<span class="birthday_age"> ' . $age . ' ' . $birthdays_settings[ 'user_verbiage_text' ] . '</span>';
                                            if( $user->name == "Demo" ){
                                                ;//var_dump( $age , $user, date( "Y", $user->date ), date( 'Y-d-m', $user->date ) );die();
                                            }
                                        }
                                        $html .= '</div>';
                                    }
                                    $html .= '\' }, ';
                                }
                            $html .= ' ];';
                            $html .= "
                                jQuery( '#birthday_calendar' ).bic_calendar( {
                                    events: events,
                                    dayNames: dayNames,
                                    monthNames: monthNames,
                                    showDays: true,
                                    displayMonthController: true,
                                    displayYearController: false
                                } );
                            ";

                            $html .= "jQuery( '#bic_calendar_'+'";
                            $html .= date( 'd_m_Y' );
                            $html .= "' ).addClass( 'selection' ); ";
                        $html .= '} );';
                    $html .= '</script>';
                    $html .= '<div id="birthday_calendar"></div>';
                    break;
                //Upcoming Mode
                case 3:
                    wp_enqueue_script( 'jquery-ui-tooltip' );
                    wp_enqueue_script( 'birthdays-script' );
                    wp_enqueue_style ( 'jquery-style' );
                    $days_organized = self::organize_days( $filtered );
                    //TODO get current day in format MM-DD
                    $today_key = date( 'm-d' );
                    $upcoming_days = $birthdays_settings[ 'upcoming_days_birthdays' ];
                    $consecutive_days = $birthdays_settings[ 'upcoming_consecutive_days' ];
                    $upcoming_mode = $birthdays_settings[ 'upcoming_mode' ];
                    /* If today is not in the array, add the key and sort the array again */
                    if ( ! array_key_exists( $today_key, $days_organized ) ) {
                        $days_organized[ $today_key ] = array();
                        ksort( $days_organized );
                    }
                    /* Find the current day in the array, then iterate to it */
                    $offset = array_search( $today_key, array_keys( $days_organized ) );
                    for ( $i = 0; $i < $offset; $i++ ) {
                        next( $days_organized );
                    }
                    //var_dump( $days_organized );
                    /* Now show the number of days user desires */
                    $final_days = array();
                    $not_empty = false;
                    if ( $upcoming_mode ) {
                        $today = DateTime::createFromFormat( 'm-d', $today_key );
                        for ( $i = 0; $i < $consecutive_days; $i++ ) {
                            $today->add( new DateInterval( 'P1D' ) );
                            $tmp_day = $today->format( 'm-d' );
                            if ( ! array_key_exists( $tmp_day, $days_organized ) ) {
                                $days_organized[ $tmp_day ] = array();
                            }
                        }
                        ksort( $days_organized );
                        $offset = array_search( $today_key, array_keys( $days_organized ) );
                        for ( $i = 0; $i < $offset; $i++ ) {
                            next( $days_organized );
                        }
                        $tmp_count = $offset;
                        $total = count( $days_organized );
                        for ( $i = 0; $i < $consecutive_days; $i++ ) {
                            $final_days[] = current( $days_organized );
                            next( $days_organized );
                            $tmp_count++;
                            if ( current( $days_organized ) == false && ( $tmp_count == $total ) ) {
                                reset( $days_organized );
                                $tmp_count = 0;
                            }
                        }
                    } else {
                        $tmp_count = $offset;
                        $total = count( $days_organized );
                        for ( $i = 0; $i < $upcoming_days; $i++ ) {
                            if( ( current( $days_organized ) == false ) && ( $tmp_count == $total ) ) {
                                reset( $days_organized );
                                $tmp_count = 0;
                            } elseif( ( current( $days_organized ) == false ) && $i == 0 ) {
                                $tmp_count++;
                                if ( $tmp_count == $total ) {
                                    reset( $days_organized );
                                    $final_days[] = current( $days_organized );
                                    next( $days_organized );
                                    $tmp_count = 1;
                                    continue;
                                }
                                next( $days_organized );
                            }
                            if ( $tmp_count == $offset && $i != 0 ) {
                                break;
                            }
                            $final_days[] = current( $days_organized );
                            next( $days_organized );
                            $tmp_count++;
                        }
                    }
                    foreach( $final_days as $day ) {
                        if ( !empty( $day ) ) {
                            $not_empty = true;
                            break;
                        }
                    }
                    if ( !$not_empty ) {
                        if ( $birthdays_settings[ 'empty_response' ] ) {
                            $html = '<div class="birthday_error">';
                            $html .= $birthdays_settings[ 'empty_response_text' ];
                            $html .= '</div>';
                            return $html;
                        } else {
                            return;
                        }
                    } else {
                        $year_passed = false;
                        $format = get_option( 'date_format' );
                        $format = str_replace( 'Y', '', $format );
                        $today = new DateTime( "today" );
                        $year = date( 'Y' );
                        foreach ( $final_days as $day ) {
                            if ( !$day )
                                continue;
                            uasort( $day, "cmp" );
                            $html_date = date_i18n( $format, $day[ 0 ]->date );
                            $html_date = self::remove_empty( $html_date );
                            $date1 = new DateTime( date( 'j-m', $day[ 0 ]->date ). '-' .$year );
                            if ( $birthdays_settings[ 'upcoming_year_seperate' ] && !$year_passed ) {
                                if ( $date1 < $today && $date1 != $today ) {
                                    $tmp = $year + 1;
                                    $html .= '<div class="birthday_year" >'. __( 'Year' ) . ' ' . $tmp . '</div>';
                                    $year_passed = true;
                                }
                            }
                            if ( $birthdays_settings[ 'upcoming_year' ] ) {
                                if ( $date1 < $today ) {
                                    $year += 1;
                                }
                                $html_date .= '&nbsp;<span class="birthday_upcoming_year">' . $year . '</span>';
                            }
                            $html .= '<div class="birthday_date" >' . $html_date . '</div>';
                            $flag = false;
                            foreach ( $day as $row ) {
                                $html .= '<div class="birthday_element birthday_name">';
                                if ( $flag && $birthdays_settings[ 'comma' ] ) {
                                    $html .= ', ';
                                } else {
                                    $flag = true;
                                }
                                if ( isset( $row->link ) ) {
                                    $html .= $row->link;
                                } else {
                                    $html .= $row->name;
                                }
                                $age = date( "Y" ) - date( "Y", $row->date );
                                if ( $birthdays_settings[ 'user_image_enabled' ] ) {
                                    $html .= '<a href="' . $row->image . '" target="_blank" class="user_image_enabled" ';
                                } else {
                                    $html .= '<a href="#" target="_blank" ';
                                }
                                if( $birthdays_settings[ 'user_verbiage' ] ) {
                                    $html .= 'data-age="' . $age . ' ' . $birthdays_settings[ 'user_verbiage_text' ] . '" ';
                                }
                                $html .= '></a></div>';
                            }
                        }
                    }
                break;
            }
        $html .= '</div>';
        //birthdays_email_notifier();
        return $html;
    }

    function remove_empty( $date ) {
        $empty_value = true;
        $length = strlen( $date ) - 1;
        while( $empty_value ) {
            if( !is_numeric( $date[ $length ] ) ) {
                $date = substr( $date, 0, -1 );
                $length--;
            } else {
                $empty_value = false;
            }
        }
        return $date;
    }
    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
        $instance[ 'template' ] = ( $new_instance[ 'template' ] ) ? strip_tags( $new_instance[ 'template' ] ) : 0;
        return $instance;
    }

} // class Birthdays_Widget
