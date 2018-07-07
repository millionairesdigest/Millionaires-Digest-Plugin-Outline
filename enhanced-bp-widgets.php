<?php

/*
Plugin Name: Enhanced BuddyPress Widgets
Plugin URI: http://dev.commons.gc.cuny.edu/2009/09/07/new-buddypress-plugin-enhanced-buddypress-widgets
Description: Provides enhanced versions of BuddyPress's default Groups and Members widgets
Version: 0.2.1
Author: Boone Gorges - CUNY Academic Commons
Author URI: http://teleogistic.net
*/

/*  Copyright 2009  Boone Gorges - CUNY Academic Commons  (email : boonebgorges@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
//Use class New_Millionaires_Digest_Widgets
Class My_New_Millionaires_Digest_Widgets {
    
    private static $instance;
    private $path;
    
    private function __construct() {

        $this->path = plugin_dir_path( __FILE__ );
        $this->setup();

    }
    
    public static function get_instance() {

        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;

    }
    
    private function setup() {

        add_action( 'bp_loaded', array( $this, 'load' ) );
        add_action( 'widgets_init', array( $this, 'register_widget' ), 10 );
    }

    //Load the Widgets (Another words, list all of the file paths you created in this plugin, and add them here.)
    public function load() {
	    
	    require_once $this->path . 'enhanced-buddypress-widgets-bp-functions.php';
    }
}
My_New_Millionaires_Digest_Widgets::get_instance();
