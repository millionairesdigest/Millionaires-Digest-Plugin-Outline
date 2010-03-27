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


function enhanced_buddypress_widgets_init() {
	require( dirname( __FILE__ ) . '/enhanced-buddypress-widgets-bp-functions.php' );
}
add_action( 'bp_init', 'enhanced_buddypress_widgets_init' );
?>