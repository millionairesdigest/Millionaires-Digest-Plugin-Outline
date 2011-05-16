<?php


function bp_enhanced_widgets_init() {
	add_action('widgets_init', create_function('', 'return unregister_widget("BP_Core_Members_Widget");') );
	add_action('widgets_init', create_function('', 'return register_widget("BP_Enhanced_Members_Widget");') );
	add_action('widgets_init', create_function('', 'return unregister_widget("BP_Groups_Widget");') );
	add_action('widgets_init', create_function('', 'return register_widget("BP_Enhanced_Groups_Widget");') );
}
add_action( 'plugins_loaded', 'bp_enhanced_widgets_init', 15 );





class BP_Enhanced_Members_Widget extends WP_Widget {
	
	
	function bp_enhanced_members_widget() {
		
		parent::WP_Widget( false, $name = __( 'Members', 'buddypress' ) );
		if ( is_active_widget( false, false, $this->id_base ) )
			wp_enqueue_script( 'bp_core_widget_members-js', BP_PLUGIN_URL . '/bp-core/js/widget-members.js', array('jquery') );
	}

	function widget($args, $instance) {
		global $bp;
	    extract( $args );
		
		if ( !$instance['member_default'] )
			$instance['member_default'] = 'active';
		
		echo $before_widget;
		echo $before_title
		   . $widget_name 
		   . $after_title; ?>

		<?php if ( bp_has_members( 'user_id=0&type=' . $instance['member_default'] . '&max=' . $instance['max_members'] ) ) : ?>
			<div class="item-options" id="members-list-options">
				<span class="ajax-loader" id="ajax-loader-members"></span>
				<a href="<?php echo site_url() . '/' . BP_MEMBERS_SLUG ?>" id="newest-members" <?php if ($instance['member_default'] == "newest") { ?> class="selected" <?php } ?>><?php _e( 'Newest', 'buddypress' ) ?></a> |
				<a href="<?php echo site_url() . '/' . BP_MEMBERS_SLUG ?>" id="recently-active-members" <?php if ($instance['member_default'] == "active") { ?> class="selected" <?php } ?>><?php _e( 'Active', 'buddypress' ) ?></a> |
				<a href="<?php echo site_url() . '/' . BP_MEMBERS_SLUG ?>" id="popular-members" <?php if ($instance['member_default'] == "popular") { ?> class="selected" <?php } ?>><?php _e( 'Popular', 'buddypress' ) ?></a>
			</div>
			
		<ul id="members-list" class="item-list">
				<?php while ( bp_members() ) : bp_the_member(); ?>
					<li class="vcard">
						<div class="item-avatar">
							<a href="<?php bp_member_permalink() ?>"><?php bp_member_avatar() ?></a>
						</div>

						<div class="item">
							<div class="item-title fn"><a href="<?php bp_member_permalink() ?>" title="<?php bp_member_name() ?>"><?php bp_member_name() ?></a></div>
							<div class="item-meta"><span class="activity"><?php
							
							if ( $instance['member_default'] == 'newest')
									bp_member_registered();								
							if ( $instance['member_default'] == 'active')
									bp_member_last_active();
							if ( $instance['member_default'] == 'popular')
									bp_member_total_friend_count();						
							
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



class BP_Enhanced_Groups_Widget extends WP_Widget {
	function bp_enhanced_groups_widget() {
			parent::WP_Widget( false, $name = __( 'Groups', 'buddypress' ) );

		if ( is_active_widget( false, false, $this->id_base ) )
			wp_enqueue_script( 'groups_widget_groups_list-js', BP_PLUGIN_URL . '/bp-groups/js/widget-groups.js', array('jquery') );
	}

	function widget($args, $instance) {
		global $bp;
		
	    extract( $args );
		
		echo $before_widget;
		echo $before_title
		   . $widget_name 
		   . $after_title; ?>
		
		
		<?php if ( bp_has_groups( 'type=' . $instance['group_default'] . '&max=' . $instance['max_groups'] ) ) : ?>
			<div class="item-options" id="groups-list-options">
				<span class="ajax-loader" id="ajax-loader-groups"></span>
				<a href="<?php echo site_url() . '/' . $bp->groups->slug ?>" id="newest-groups" <?php if ($instance['group_default'] == "newest") { ?> class="selected" <?php } ?>><?php _e("Newest", 'buddypress') ?></a> |
				<a href="<?php echo site_url() . '/' . $bp->groups->slug ?>" id="recently-active-groups" <?php if ($instance['group_default'] == "active") { ?> class="selected" <?php } ?>><?php _e("Active", 'buddypress') ?></a> |
				<a href="<?php echo site_url() . '/' . $bp->groups->slug ?>" id="popular-groups" <?php if ($instance['group_default'] == "popular") { ?> class="selected" <?php } ?>><?php _e("Popular", 'buddypress') ?></a>
			
			</div>
			
			<ul id="groups-list" class="item-list">
				<?php while ( bp_groups() ) : bp_the_group(); ?>
					<li>
						<div class="item-avatar">
							<a href="<?php bp_group_permalink() ?>"><?php bp_group_avatar_thumb() ?></a>
						</div>

	
						<div class="item">
							<div class="item-title"><a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_name() ?></a></div>
							<div class="item-meta"><span class="activity">
							<?php
								if ( $instance['group_default'] == 'newest') {
									echo "Created " . bp_get_group_date_created() . " ago";
								}
								if ( $instance['group_default'] == 'active')
									echo bp_get_group_last_active();
								if ( $instance['group_default'] == 'popular')
									echo bp_group_member_count();
								
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
