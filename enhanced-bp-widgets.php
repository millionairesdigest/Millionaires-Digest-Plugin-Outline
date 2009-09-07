<?php

/*
Plugin Name: Enhanced BuddyPress Widgets
Plugin URI: http://dev.commons.gc.cuny.edu/2009/09/07/new-buddypress-plugin-enhanced-buddypress-widgets
Description: Provides enhanced versions of BuddyPress's default Groups and Members widgets
Version: 0.1
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




function members_register_enhanced_widgets() {
	register_widget("BP_Enhanced_Members_Widget");	
}

add_action( 'widgets_init', 'members_register_enhanced_widgets' );


class BP_Enhanced_Members_Widget extends WP_Widget {
	
	
	function bp_enhanced_members_widget() {
		$widget_ops = array('description' => __('Enhanced BP Widgets: Members. Use this instead of the standard BuddyPress Members widget.', 'bp-enhanced-groups-widget'));
		$this->WP_Widget('bp_enhanced_members_widget', __('Members'), $widget_ops);
		wp_enqueue_script( 'bp_core_widget_members-js', BP_PLUGIN_URL . '/bp-core/js/widget-members.js', array('jquery', 'jquery-livequery-pack') );		
		wp_enqueue_style( 'bp_core_widget_members-css', BP_PLUGIN_URL . '/bp-core/css/widget-members.css' );
	}

	function widget($args, $instance) {
		global $bp;
	    extract( $args );
		
		echo $before_widget;
		echo $before_title
		   . $widget_name 
		   . $after_title; ?>

		<?php if ( bp_has_site_members( 'type=' . $instance['member_default'] .'&max=' . $instance['max_members'] ) ) : ?>
			<div class="item-options" id="members-list-options">
				<img id="ajax-loader-members" src="<?php echo $bp->core->image_base ?>/ajax-loader.gif" height="7" alt="<?php _e( 'Loading', 'buddypress' ) ?>" style="display: none;" /> 
				<a href="<?php echo site_url() . '/' . BP_MEMBERS_SLUG ?>" id="newest-members" <?php if ($instance['member_default'] == "newest") { ?> class="selected" <?php } ?>><?php _e( 'Newest', 'buddypress' ) ?></a> | 
				<a href="<?php echo site_url() . '/' . BP_MEMBERS_SLUG ?>" id="recently-active-members" <?php if ($instance['member_default'] == "active") { ?> class="selected" <?php } ?>><?php _e( 'Active', 'buddypress' ) ?></a> | 
				<a href="<?php echo site_url() . '/' . BP_MEMBERS_SLUG ?>" id="popular-members" <?php if ($instance['member_default'] == "popular") { ?> class="selected" <?php } ?>><?php _e( 'Popular', 'buddypress' ) ?></a>
			</div>
			
			<ul id="members-list" class="item-list">
				<?php while ( bp_site_members() ) : bp_the_site_member(); ?>
					<li class="vcard">
						<div class="item-avatar">
							<a href="<?php bp_the_site_member_link() ?>"><?php bp_the_site_member_avatar() ?></a>
						</div>

						<div class="item">
							<div class="item-title fn"><a href="<?php bp_the_site_member_link() ?>" title="<?php bp_the_site_member_name() ?>"><?php bp_the_site_member_name() ?></a></div>
							<div class="item-meta"><span class="activity"><?php
							
							if ( $instance['member_default'] == 'newest') {
									echo bp_core_get_last_activity( bp_get_the_site_member_registered(), __( 'registered %s ago', 'buddypress' ) );
								}
								if ( $instance['member_default'] == 'active')
									bp_the_site_member_last_active();
								if ( $instance['member_default'] == 'popular')
									bp_the_site_member_total_friend_count();
							
							
							
							
							 ?></span></div>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>		
			<?php wp_nonce_field( 'bp_core_widget_members', '_wpnonce-members' ); ?>
			<input type="hidden" name="members_widget_max" id="members_widget_max" value="<?php echo attribute_escape( $instance['max_members'] ); ?>" />
			
		<?php else: ?>

			<div class="widget-error">
				<?php _e('No one has signed up yet!', 'buddypress') ?>
			</div>

		<?php endif; ?>
			
		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['max_members'] = strip_tags( $new_instance['max_members'] );
		$instance['member_default'] = strip_tags( $new_instance['member_default'] );

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'max_members' => 5, 'member_default' => 'active' ) );
		$max_members = strip_tags( $instance['max_members'] );
		$member_default = strip_tags( $instance['member_default'] );

		?>

		<p><label for="bp-core-widget-members-max"><?php _e('Max Members to show:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_members' ); ?>" name="<?php echo $this->get_field_name( 'max_members' ); ?>" type="text" value="<?php echo attribute_escape( $max_members ); ?>" style="width: 30%" /></label></p>

		<p><label for="bp-groups-widget-member-default"><?php _e('Default members to show:', 'buddypress'); ?> <br />
			<input id="<?php echo $this->get_field_id( 'member_default' )-newest; ?>" name="<?php echo $this->get_field_name( 'member_default' ); ?>" type="radio" value="newest" <?php if ($member_default == "newest") echo "checked"; ?> /></label> Newest<br />
		
			<input id="<?php echo $this->get_field_id( 'member_default' )-active; ?>" name="<?php echo $this->get_field_name( 'member_default' ); ?>" type="radio" value="active" <?php if ($member_default == "active") echo "checked"; ?>/></label> Recently Active<br />
			<input id="<?php echo $this->get_field_id( 'member_default' )-popular; ?>" name="<?php echo $this->get_field_name( 'member_default' ); ?>" type="radio" value="popular" <?php if ($member_default == "popular") echo "checked"; ?>/></label> Popular
		</p>
		
		
		
	<?php
	}
}











function groups_register_enhanced_widgets() {
	register_widget("BP_Enhanced_Groups_Widget");	
}

add_action( 'widgets_init', 'groups_register_enhanced_widgets' );

class BP_Enhanced_Groups_Widget extends WP_Widget {
	function bp_enhanced_groups_widget() {
		$widget_ops = array('description' => __('Enhanced BP Widgets: Groups. Use this instead of the standard BuddyPress Groups widget.', 'bp-enhanced-groups-widget'));
		$this->WP_Widget('bp_enhanced_groups_widget', __('Groups'), $widget_ops);
	
		//parent::WP_Widget( false, $name = 'Groups' );
		wp_enqueue_script( 'groups_widget_groups_list-js', BP_PLUGIN_URL . '/bp-groups/js/widget-groups.js', array('jquery', 'jquery-livequery-pack') );		
		wp_enqueue_style( 'groups_widget_members-css', BP_PLUGIN_URL . '/bp-groups/css/widget-groups.css' );		
	}

	function widget($args, $instance) {
		global $bp;
		
	    extract( $args );
		echo $before_widget;
		echo $before_title
		   . $widget_name 
		   . $after_title; ?>
		
		<?php if ( bp_has_site_groups( 'type=' . $instance['group_default'] . '&max=' . $instance['max_groups'] ) ) : ?>
			<div class="item-options" id="groups-list-options">
				<img id="ajax-loader-groups" src="<?php echo $bp->groups->image_base ?>/ajax-loader.gif" height="7" alt="<?php _e( 'Loading', 'buddypress' ) ?>" style="display: none;" /> 
				<a href="<?php echo site_url() . '/' . $bp->groups->slug ?>" id="newest-groups" <?php if ($instance['group_default'] == "newest") { ?> class="selected" <?php } ?>><?php _e("Newest", 'buddypress') ?></a> | 
				<a href="<?php echo site_url() . '/' . $bp->groups->slug ?>" id="recently-active-groups"<?php if ($instance['group_default'] == "active") { ?> class="selected" <?php } ?>><?php _e("Active", 'buddypress') ?></a> | 
				<a href="<?php echo site_url() . '/' . $bp->groups->slug ?>" id="popular-groups"<?php if ($instance['group_default'] == "popular") { ?> class="selected" <?php } ?>><?php _e("Popular", 'buddypress') ?></a>
			</div>
			
			<ul id="groups-list" class="item-list">
				<?php while ( bp_site_groups() ) : bp_the_site_group(); ?>
					<li>
						<div class="item-avatar">
							<a href="<?php bp_the_site_group_link() ?>"><?php bp_the_site_group_avatar_thumb() ?></a>
						</div>

						<div class="item">
							<div class="item-title"><a href="<?php bp_the_site_group_link() ?>" title="<?php bp_the_site_group_name() ?>"><?php bp_the_site_group_name() ?></a></div>
							<div class="item-meta"><span class="activity">
							<?php
								if ( $instance['group_default'] == 'newest') {
									echo "Created ";
									bp_the_site_group_date_created();
								}
								if ( $instance['group_default'] == 'active')
									bp_the_site_group_last_active();
								if ( $instance['group_default'] == 'popular')
									bp_the_site_group_member_count();
								
							?></span></div>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>		
			<?php wp_nonce_field( 'groups_widget_groups_list', '_wpnonce-groups' ); ?>
			<input type="hidden" name="groups_widget_max" id="groups_widget_max" value="<?php echo attribute_escape( $instance['max_groups'] ); ?>" />
			
		<?php else: ?>

			<div class="widget-error">
				<?php _e('There are no groups to display.', 'buddypress') ?>
			</div>

		<?php endif; ?>
			
		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['max_groups'] = strip_tags( $new_instance['max_groups'] );
		$instance['group_default'] = strip_tags( $new_instance['group_default'] );

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'max_groups' => 5, 'group_default' => 'active' ) );
		$max_groups = strip_tags( $instance['max_groups'] );
		$group_default = strip_tags( $instance['group_default'] );
		?>

		<p><label for="bp-groups-widget-groups-max"><?php _e('Max groups to show:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_groups' ); ?>" name="<?php echo $this->get_field_name( 'max_groups' ); ?>" type="text" value="<?php echo attribute_escape( $max_groups ); ?>" style="width: 30%" /></label></p>
		
		<p><label for="bp-groups-widget-groups-default"><?php _e('Default groups to show:', 'buddypress'); ?> <br />
			<input id="<?php echo $this->get_field_id( 'group_default' )-newest; ?>" name="<?php echo $this->get_field_name( 'group_default' ); ?>" type="radio" value="newest" <?php if ($group_default == "newest") echo "checked"; ?> /></label> Newest<br />
		
			<input id="<?php echo $this->get_field_id( 'group_default' )-active; ?>" name="<?php echo $this->get_field_name( 'group_default' ); ?>" type="radio" value="active" <?php if ($group_default == "active") echo "checked"; ?>/></label> Recently Active<br />
			<input id="<?php echo $this->get_field_id( 'group_default' )-popular; ?>" name="<?php echo $this->get_field_name( 'group_default' ); ?>" type="radio" value="popular" <?php if ($group_default == "popular") echo "checked"; ?>/></label> Popular
		</p>
		
	<?php
	}
}

?>