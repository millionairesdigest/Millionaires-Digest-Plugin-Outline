<?php

/* Register All of the New Widgets We Created */
function add_new_millionaires_digest_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("AuthorRecentArticles");') );
	add_action('widgets_init', create_function('', 'return register_widget("AuthorRecentVideos");') );
	add_action('widgets_init', create_function('', 'return register_widget("AuthorRecentPhotos");') );
	add_action('widgets_init', create_function('', 'return register_widget("AuthorRecentMusic");') );
	add_action('widgets_init', create_function('', 'return register_widget("BuddyPress_User_Info_Widget");') );
	add_action('widgets_init', create_function('', 'return register_widget("BuddyPress_Author_Widget");') );
	add_action('widgets_init', create_function('', 'return register_widget("BP_My_Groups_Widget");') );
	add_action('widgets_init', create_function('', 'return register_widget("Recent_Profile_Visitors_Widget");') );
	add_action('widgets_init', create_function('', 'return register_widget("Total_Friends_Widget");') );
	add_action('widgets_init', create_function('', 'return register_widget("BuddyPress_Posts_Widget");') );
}
add_action( 'plugins_loaded', 'add_new_millionaires_digest_widgets', 15 );



/* Create All of the New Widgets */

/* Author Recent Articles */
class AuthorRecentArticles extends WP_Widget {
	//Constructor
	function AuthorRecentArticles() {
		parent::__construct(
			'author_recent_articles', // Base ID
			'Author Recent Articles', // Name
			array( 'description' => __( 'Display a list of more articles written by the same author if on a singlular article', 'text_domain' ) )
		);
	}
	
	//Widget Form
	function form( $instance ) {	
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$numberofposts = isset( $instance['numberofposts'] ) ? absint( $instance['numberofposts'] ) : 5;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
		$showthumbnail = isset( $instance['showthumbnail'] ) ? (bool) $instance['showthumbnail'] : false;
		$width = isset( $instance['width'] ) ?  esc_attr($instance['width'] ) : '';
		$height = isset( $instance['height'] ) ? esc_attr( $instance['height'] ) : '';
		?>
	<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'wp_widget_plugin' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
	</p>
	<p>
	<label for="<?php echo $this->get_field_id( 'numberofposts' ); ?>"><?php _e( 'Number of articles to show:', 'wp_widget_plugin' ); ?></label>
	<input  id="<?php echo $this->get_field_id( 'numberofposts' ); ?>" name="<?php echo $this->get_field_name( 'numberofposts' ); ?>" type="text"  size="3" value="<?php echo $numberofposts; ?>" />
	</p>
	<p>
	<input class="checkbox showthumbnail" id="<?php echo $this->get_field_id( 'showthumbnail' ); ?>" name="<?php echo $this->get_field_name( 'showthumbnail' ); ?>" type="checkbox" <?php checked( $showthumbnail ); ?> /><label for="<?php echo $this->get_field_id( 'showthumbnail' ); ?>"><?php _e( 'Show Thumbnail?', 'wp_widget_plugin' ); ?></label>
	</p>
    <div class="thumbnailAttr">
    <p>
		<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width: ' ); ?></label> 
		<input  size="3" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo $width; ?>" /> px
		</p>
        <p>
		<label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height: ' ); ?></label> 
		<input  size="3"  id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo $height; ?>" /> px
		</p>
        </div>
    <p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display article date?' ); ?></label></p>
	<?php	
	}

	//Widget Update
	function update( $new_instance, $old_instance ) {
		$old_instance['title'] = $new_instance['title'];		
		$old_instance['numberofposts'] = isset( $new_instance['numberofposts'] )? (int)$new_instance['numberofposts']:'';
		$old_instance['showthumbnail'] = isset( $new_instance['showthumbnail'] ) ? (bool) $new_instance['showthumbnail'] : false;
		$old_instance['width'] = isset( $new_instance['width'] ) ? $new_instance['width']:'';
		$old_instance['height'] = isset( $new_instance['height'] ) ? $new_instance['height']:'';
		$old_instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		return $old_instance;
	}

	//Widget Display
	function widget( $args, $instance ) {
		extract( $args );
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Author Recent Articles' );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$number = ( ! empty( $instance['numberofposts'] ) ) ? absint( $instance['numberofposts'] ) : 5;
		$showthumbnail = isset( $instance['showthumbnail'] ) ? $instance['showthumbnail'] : false;
		$width_image = empty( $instance['width'] ) ? '50' : apply_filters( 'widget_image_width', $instance['width'] );
        $height_image = empty( $instance['height'] ) ? '50' : apply_filters('widget_image_height', $instance['height']);
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
		
        if ( is_single() ) {
		 global $authordata, $post;
         $authors_posts = get_posts( array( 
			 'author' => $authordata->ID,
			 'orderby' => 'rand',
			 'post_type' => 'article',
			 'post__not_in' => array( $post->ID ),
			 'posts_per_page' => $number
		 ) );
			if ( count( $authors_posts ) > 0 ) :
		?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) 
			{
				$author = get_the_author_meta( 'display_name', $authordata->ID );
				$title = str_replace( '[Author]', $author, $title );
				echo $before_title . $title . $after_title; 
			}
		?>
		<ul class="author_article">
		<?php foreach ( $authors_posts as $authors_post ) { ?>
			<li>
            <?php if( $showthumbnail ) : ?>
            <div class="author_left" style="width:<?php echo $width_image; ?>px;height:<?php echo $height_image; ?>px;">           
            <?php
			if( $showthumbnail ) {
				if( has_post_thumbnail( $authors_post->ID ) ) {
				?>
                 <a href="<?php echo get_permalink( $authors_post->ID ); ?>">
                <?php
				echo get_the_post_thumbnail( $authors_post->ID, array( $width_image,$height_image ) ); ?>
                </a>
			<?php } 
				else {
				?>
				<a href="<?php echo get_permalink( $authors_post->ID ); ?>">
                <?php
				echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?>
                </a>
			<?php 
				}
			}
				?>
            </div>
            <?php endif; ?>
            <div class="author_right">
            <a href="<?php echo get_permalink( $authors_post->ID ) ; ?>">
            <?php			
            echo apply_filters( 'the_title', $authors_post->post_title, $authors_post->ID ); ?>
            </a>
			<?php if ( $show_date ) : ?>
				<br /><span class="post-date"><small><?php echo date( get_option( 'date_format' ), strtotime( $authors_post->post_date ) ); ?></small></span>
			<?php endif; ?>
            </div>
			</li>
		<?php } ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();		
		endif ;
		} // single page condition ends here
	}// widget function end here
 } // class end tag



/* Author Recent Videos */
class AuthorRecentVideos extends WP_Widget {
	//Constructor
	function AuthorRecentVideos() {
		parent::__construct(
			'author_recent_videos', // Base ID
			'Author Recent Videos', // Name
			array( 'description' => __( 'Display a list of more videos created by the same author if on a singlular video', 'text_domain' ) )
		);
	}
	
	//Widget Form
	function form( $instance ) {	
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$numberofposts = isset( $instance['numberofposts'] ) ? absint( $instance['numberofposts'] ) : 5;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
		$showthumbnail = isset( $instance['showthumbnail'] ) ? (bool) $instance['showthumbnail'] : false;
		$width = isset( $instance['width'] ) ?  esc_attr( $instance['width'] ) : '';
		$height = isset( $instance['height'] ) ? esc_attr( $instance['height']) : '';
		$alternateImg = isset( $instance['alternateImg'] ) ? esc_attr( $instance['alternateImg'] ) : '';
		?>
	<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'wp_widget_plugin' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
	</p>
	<p>
	<label for="<?php echo $this->get_field_id( 'numberofposts' ); ?>"><?php _e( 'Number of videos to show:', 'wp_widget_plugin' ); ?></label>
	<input  id="<?php echo $this->get_field_id( 'numberofposts' ); ?>" name="<?php echo $this->get_field_name( 'numberofposts' ); ?>" type="text"  size="3" value="<?php echo $numberofposts; ?>" />
	</p>
	<p>
	<input class="checkbox showthumbnail" id="<?php echo $this->get_field_id( 'showthumbnail' ); ?>" name="<?php echo $this->get_field_name( 'showthumbnail' ); ?>" type="checkbox" <?php checked( $showthumbnail ); ?> /><label for="<?php echo $this->get_field_id( 'showthumbnail' ); ?>"><?php _e( 'Show Thumbnail?', 'wp_widget_plugin' ); ?></label>
	</p>
    <div class="thumbnailAttr">
    <p>
		<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width: ' ); ?></label> 
		<input  size="3" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo $width; ?>" /> px
		</p>
        <p>
		<label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height: ' ); ?></label> 
		<input  size="3"  id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo $height; ?>" /> px
		</p>
          <p>
	<label for="<?php echo $this->get_field_id( 'alternateImg' ); ?>"><?php _e( 'Alternate image url:', 'wp_widget_plugin' ); ?></label>
	<input class="widefat"  id="<?php echo $this->get_field_id( 'alternateImg' ); ?>" name="<?php echo $this->get_field_name( 'alternateImg' ); ?>" type="text"   value="<?php echo $alternateImg; ?>" />
	</p>
        </div>
    <p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display video date?' ); ?></label></p>
	<?php	
	}

	//Widget Update
	function update( $new_instance, $old_instance ) {
		$old_instance['title'] = $new_instance['title'];		
		$old_instance['numberofposts'] = isset( $new_instance['numberofposts'] ) ? (int)$new_instance['numberofposts']:'';
		$old_instance['showthumbnail'] = isset( $new_instance['showthumbnail'] ) ? (bool) $new_instance['showthumbnail'] : false;
		$old_instance['width'] = isset( $new_instance['width'] ) ? $new_instance['width']:'';
		$old_instance['height'] = isset( $new_instance['height'] ) ? $new_instance['height']:'';
		$old_instance['alternateImg'] = isset( $new_instance['alternateImg'] ) ? $new_instance['alternateImg']:'';
		$old_instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		return $old_instance;
	}

	//Widget Display
	function widget( $args, $instance ) {
		extract( $args );
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Author Recent Videos' );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$number = ( ! empty( $instance['numberofposts'] ) ) ? absint( $instance['numberofposts'] ) : 5;
		$showthumbnail = isset( $instance['showthumbnail'] ) ? $instance['showthumbnail'] : false;
		$width_image = empty( $instance['width'] ) ? '50' : apply_filters( 'widget_image_width', $instance['width'] );
        $height_image = empty( $instance['height'] ) ? '50' : apply_filters( 'widget_image_height', $instance['height'] );
		$alternateImg = !empty( $instance['alternateImg'] ) ? $instance['alternateImg']:'';
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
		
        if ( is_single() ) {
		 global $authordata, $post;
         $authors_posts = get_posts( array( 
			 'author' => $authordata->ID,
			 'post_type' => 'video',
			 'post__not_in' => array( $post->ID ),
			 'posts_per_page' => $number
		 ) );
			if ( count( $authors_posts ) > 0 ) :
		?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) 
			{
				$author = get_the_author_meta( 'display_name', $authordata->ID );
				$title = str_replace( '[Author]', $author, $title );
				echo $before_title . $title . $after_title; 
			}
		?>
		<ul class="author_video">
		<?php foreach ( $authors_posts as $authors_post ) { ?>
			<li>
            <?php if( $showthumbnail ) : ?>
            <div class="author_left" style="width:<?php echo $width_image; ?>px;height:<?php echo $height_image; ?>px;">           
            <?php
			if( $showthumbnail ) {
				if( has_post_thumbnail( $authors_post->ID ) ) {
				?>
                 <a href="<?php echo get_permalink( $authors_post->ID ) ; ?>">
                <?php
				echo get_the_post_thumbnail( $authors_post->ID, array( $width_image, $height_image ) ); ?>
                </a>
			<?php 
				}
				elseif( $alternateImg != '') {?>
				<a href="<?php echo get_permalink( $authors_post->ID ) ; ?>">
                <img src="<?php echo $alternateImg; ?>" width="<?php echo $width_image; ?>" height="<?php echo $height_image; ?>" class="wp-video-image" />
                </a>
			<?php }
			}?>
            </div>
            <?php endif; ?>
            <div class="author_right">
            <a href="<?php echo get_permalink( $authors_post->ID ) ; ?>">
            <?php			
            echo apply_filters( 'the_title', $authors_post->post_title, $authors_post->ID ); ?>
            </a>
			<?php if ( $show_date ) : ?>
				<br /><span class="post-date"><small><?php echo date( get_option( 'date_format' ), strtotime( $authors_post->post_date ) ); ?></small></span>
			<?php endif; ?>
            </div>
			</li>
		<?php } ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();		
		endif;
		} // single page condition ends here
	}// widget function end here
 } // class end tag



/* Author Recent Photos */
class AuthorRecentPhotos extends WP_Widget {
	//Constructor
	function AuthorRecentPhotos() {
		parent::__construct(
			'author_recent_photos', // Base ID
			'Author Recent Photos', // Name
			array( 'description' => __( 'Display a list of more photos taken by the same author if on a singlular photo', 'text_domain' ) )
		);
	}
	
	//Widget Form
	function form( $instance ) {	
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$numberofposts = isset( $instance['numberofposts'] ) ? absint( $instance['numberofposts'] ) : 5;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
		$showthumbnail = isset( $instance['showthumbnail'] ) ? (bool) $instance['showthumbnail'] : false;
		$width = isset( $instance['width'] ) ?  esc_attr( $instance['width'] ) : '';
		$height = isset( $instance['height'] ) ? esc_attr( $instance['height']) : '';
		$alternateImg = isset( $instance['alternateImg'] ) ? esc_attr( $instance['alternateImg'] ) : '';
		?>
	<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'wp_widget_plugin' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
	</p>
	<p>
	<label for="<?php echo $this->get_field_id( 'numberofposts' ); ?>"><?php _e( 'Number of photos to show:', 'wp_widget_plugin' ); ?></label>
	<input  id="<?php echo $this->get_field_id( 'numberofposts' ); ?>" name="<?php echo $this->get_field_name( 'numberofposts' ); ?>" type="text"  size="3" value="<?php echo $numberofposts; ?>" />
	</p>
	<p>
	<input class="checkbox showthumbnail" id="<?php echo $this->get_field_id( 'showthumbnail' ); ?>" name="<?php echo $this->get_field_name( 'showthumbnail' ); ?>" type="checkbox" <?php checked( $showthumbnail ); ?> /><label for="<?php echo $this->get_field_id( 'showthumbnail' ); ?>"><?php _e( 'Show Thumbnail?', 'wp_widget_plugin' ); ?></label>
	</p>
    <div class="thumbnailAttr">
    <p>
		<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width: ' ); ?></label> 
		<input  size="3" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo $width; ?>" /> px
		</p>
        <p>
		<label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height: ' ); ?></label> 
		<input  size="3"  id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo $height; ?>" /> px
		</p>
          <p>
	<label for="<?php echo $this->get_field_id( 'alternateImg' ); ?>"><?php _e( 'Alternate image url:', 'wp_widget_plugin' ); ?></label>
	<input class="widefat"  id="<?php echo $this->get_field_id( 'alternateImg' ); ?>" name="<?php echo $this->get_field_name( 'alternateImg' ); ?>" type="text"   value="<?php echo $alternateImg; ?>" />
	</p>
        </div>
    <p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display photo date?' ); ?></label></p>
	<?php	
	}

	//Widget Update
	function update( $new_instance, $old_instance ) {
		$old_instance['title'] = $new_instance['title'];		
		$old_instance['numberofposts'] = isset( $new_instance['numberofposts'] ) ? (int)$new_instance['numberofposts']:'';
		$old_instance['showthumbnail'] = isset( $new_instance['showthumbnail'] ) ? (bool) $new_instance['showthumbnail'] : false;
		$old_instance['width'] = isset( $new_instance['width'] ) ? $new_instance['width']:'';
		$old_instance['height'] = isset( $new_instance['height'] ) ? $new_instance['height']:'';
		$old_instance['alternateImg'] = isset( $new_instance['alternateImg'] ) ? $new_instance['alternateImg']:'';
		$old_instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		return $old_instance;
	}

	//Widget Display
	function widget( $args, $instance ) {
		extract( $args );
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Author Recent Photos' );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$number = ( ! empty( $instance['numberofposts'] ) ) ? absint( $instance['numberofposts'] ) : 5;
		$showthumbnail = isset( $instance['showthumbnail'] ) ? $instance['showthumbnail'] : false;
		$width_image = empty( $instance['width'] ) ? '50' : apply_filters( 'widget_image_width', $instance['width'] );
        $height_image = empty( $instance['height'] ) ? '50' : apply_filters( 'widget_image_height', $instance['height'] );
		$alternateImg = !empty( $instance['alternateImg'] ) ? $instance['alternateImg']:'';
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
		
        if ( is_single() ) {
		 global $authordata, $post;
         $authors_posts = get_posts( array( 
			 'author' => $authordata->ID,
			 'post_type' => 'photo',
			 'post__not_in' => array( $post->ID ),
			 'posts_per_page' => $number
		 ) );
			if ( count( $authors_posts ) > 0 ) :
		?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) 
			{
				$author = get_the_author_meta( 'display_name', $authordata->ID );
				$title = str_replace( '[Author]', $author, $title );
				echo $before_title . $title . $after_title; 
			}
		?>
		<ul class="author_photo">
		<?php foreach ( $authors_posts as $authors_post ) { ?>
			<li>
            <?php if( $showthumbnail ) : ?>
            <div class="author_left" style="width:<?php echo $width_image; ?>px;height:<?php echo $height_image; ?>px;">           
            <?php
			if( $showthumbnail ) {
				if( has_post_thumbnail( $authors_post->ID ) ) {
				?>
                 <a href="<?php echo get_permalink( $authors_post->ID ) ; ?>">
                <?php
				echo get_the_post_thumbnail( $authors_post->ID, array( $width_image, $height_image ) ); ?>
                </a>
			<?php 
				}
				elseif( $alternateImg != '') {?>
				<a href="<?php echo get_permalink( $authors_post->ID ) ; ?>">
                <img src="<?php echo $alternateImg; ?>" width="<?php echo $width_image; ?>" height="<?php echo $height_image; ?>" class="wp-photo-image" />
                </a>
			<?php }
			}?>
            </div>
            <?php endif; ?>
            <div class="author_right">
            <a href="<?php echo get_permalink( $authors_post->ID ) ; ?>">
            <?php			
            echo apply_filters( 'the_title', $authors_post->post_title, $authors_post->ID ); ?>
            </a>
			<?php if ( $show_date ) : ?>
				<br /><span class="post-date"><small><?php echo date( get_option( 'date_format' ), strtotime( $authors_post->post_date ) ); ?></small></span>
			<?php endif; ?>
            </div>
			</li>
		<?php } ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();		
		endif;
		} // single page condition ends here
	}// widget function end here
 } // class end tag



/* Author Recent Music */
class AuthorRecentMusic extends WP_Widget {
	//Constructor
	function AuthorRecentMusic() {
		parent::__construct(
			'author_recent_music', // Base ID
			'Author Recent Music', // Name
			array( 'description' => __( 'Display a list of more songs created by the same author if on a singlular song', 'text_domain' ) )
		);
	}
	
	//Widget Form
	function form( $instance ) {	
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$numberofposts = isset( $instance['numberofposts'] ) ? absint( $instance['numberofposts'] ) : 5;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
		$showthumbnail = isset( $instance['showthumbnail'] ) ? (bool) $instance['showthumbnail'] : false;
		$width = isset( $instance['width'] ) ?  esc_attr( $instance['width'] ) : '';
		$height = isset( $instance['height'] ) ? esc_attr( $instance['height']) : '';
		$alternateImg = isset( $instance['alternateImg'] ) ? esc_attr( $instance['alternateImg'] ) : '';
		?>
	<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'wp_widget_plugin' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
	</p>
	<p>
	<label for="<?php echo $this->get_field_id( 'numberofposts' ); ?>"><?php _e( 'Number of songs to show:', 'wp_widget_plugin' ); ?></label>
	<input  id="<?php echo $this->get_field_id( 'numberofposts' ); ?>" name="<?php echo $this->get_field_name( 'numberofposts' ); ?>" type="text"  size="3" value="<?php echo $numberofposts; ?>" />
	</p>
	<p>
	<input class="checkbox showthumbnail" id="<?php echo $this->get_field_id( 'showthumbnail' ); ?>" name="<?php echo $this->get_field_name( 'showthumbnail' ); ?>" type="checkbox" <?php checked( $showthumbnail ); ?> /><label for="<?php echo $this->get_field_id( 'showthumbnail' ); ?>"><?php _e( 'Show Thumbnail?', 'wp_widget_plugin' ); ?></label>
	</p>
    <div class="thumbnailAttr">
    <p>
		<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width: ' ); ?></label> 
		<input  size="3" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo $width; ?>" /> px
		</p>
        <p>
		<label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height: ' ); ?></label> 
		<input  size="3"  id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo $height; ?>" /> px
		</p>
          <p>
	<label for="<?php echo $this->get_field_id( 'alternateImg' ); ?>"><?php _e( 'Alternate image url:', 'wp_widget_plugin' ); ?></label>
	<input class="widefat"  id="<?php echo $this->get_field_id( 'alternateImg' ); ?>" name="<?php echo $this->get_field_name( 'alternateImg' ); ?>" type="text"   value="<?php echo $alternateImg; ?>" />
	</p>
        </div>
    <p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display song date?' ); ?></label></p>
	<?php	
	}

	//Widget Update
	function update( $new_instance, $old_instance ) {
		$old_instance['title'] = $new_instance['title'];		
		$old_instance['numberofposts'] = isset( $new_instance['numberofposts'] ) ? (int)$new_instance['numberofposts']:'';
		$old_instance['showthumbnail'] = isset( $new_instance['showthumbnail'] ) ? (bool) $new_instance['showthumbnail'] : false;
		$old_instance['width'] = isset( $new_instance['width'] ) ? $new_instance['width']:'';
		$old_instance['height'] = isset( $new_instance['height'] ) ? $new_instance['height']:'';
		$old_instance['alternateImg'] = isset( $new_instance['alternateImg'] ) ? $new_instance['alternateImg']:'';
		$old_instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		return $old_instance;
	}

	//Widget Display
	function widget( $args, $instance ) {
		extract( $args );
		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Author Recent Music' );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$number = ( ! empty( $instance['numberofposts'] ) ) ? absint( $instance['numberofposts'] ) : 5;
		$showthumbnail = isset( $instance['showthumbnail'] ) ? $instance['showthumbnail'] : false;
		$width_image = empty( $instance['width'] ) ? '50' : apply_filters( 'widget_image_width', $instance['width'] );
        $height_image = empty( $instance['height'] ) ? '50' : apply_filters( 'widget_image_height', $instance['height'] );
		$alternateImg = !empty( $instance['alternateImg'] ) ? $instance['alternateImg']:'';
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
		
        if ( is_single() ) {
		 global $authordata, $post;
         $authors_posts = get_posts( array( 
			 'author' => $authordata->ID,
			 'post_type' => 'audio',
			 'post__not_in' => array( $post->ID ),
			 'posts_per_page' => $number
		 ) );
			if ( count( $authors_posts ) > 0 ) :
		?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) 
			{
				$author = get_the_author_meta( 'display_name', $authordata->ID );
				$title = str_replace( '[Author]', $author, $title );
				echo $before_title . $title . $after_title; 
			}
		?>
		<ul class="author_audio">
		<?php foreach ( $authors_posts as $authors_post ) { ?>
			<li>
            <?php if( $showthumbnail ) : ?>
            <div class="author_left" style="width:<?php echo $width_image; ?>px;height:<?php echo $height_image; ?>px;">           
            <?php
			if( $showthumbnail ) {
				if( has_post_thumbnail( $authors_post->ID ) ) {
				?>
                 <a href="<?php echo get_permalink( $authors_post->ID ) ; ?>">
                <?php
				echo get_the_post_thumbnail( $authors_post->ID, array( $width_image, $height_image ) ); ?>
                </a>
			<?php 
				}
				elseif( $alternateImg != '') {?>
				<a href="<?php echo get_permalink( $authors_post->ID ) ; ?>">
                <img src="<?php echo $alternateImg; ?>" width="<?php echo $width_image; ?>" height="<?php echo $height_image; ?>" class="wp-audio-image" />
                </a>
			<?php }
			}?>
            </div>
            <?php endif; ?>
            <div class="author_right">
            <a href="<?php echo get_permalink( $authors_post->ID ) ; ?>">
            <?php			
            echo apply_filters( 'the_title', $authors_post->post_title, $authors_post->ID ); ?>
            </a>
			<?php if ( $show_date ) : ?>
				<br /><span class="post-date"><small><?php echo date( get_option( 'date_format' ), strtotime( $authors_post->post_date ) ); ?></small></span>
			<?php endif; ?>
            </div>
			</li>
		<?php } ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();		
		endif;
		} // single page condition ends here
	}// widget function end here
 } // class end tag



/* BuddyPress User Info */
class BuddyPress_User_Info_Widget extends WP_Widget {
	public function __construct() {
		$widget_ops = array(
			'description' => __( 'Display user\'s profile fields as a widget. Note: This respects the privacy preference set by the user too.', 'bp-user-info-widget' ),
		);
		parent::__construct( false, _x( 'BuddyPress User Info', 'bp-user-info-widget' ), $widget_ops );
	}
	public function widget( $args, $instance ) {
		echo $before_widget;
		echo $before_title
		     . $instance['title']
		     . $after_title;
		self::show_blog_profile( $instance );
		echo $after_widget;
			}
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		foreach ( $new_instance as $key => $val ) {
			$instance[ $key ] = $val;//update the instance
		}
		return $instance;
	}
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title'       => __( '', 'bp-user-info-widget' )
		) );
		$title = strip_tags( $instance['title'] );
		extract( $instance, EXTR_SKIP );
		?>
		<p>
			<label for="bp-user-info-widget-title">
				<?php _e( 'Title:', 'bp-user-info-widget' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( stripslashes( $title ) ); ?>"/>
			</label>
		</p>
		<?php
		//get all xprofile fields and ask user whether to show them or not
		?>
		<h3><?php _e( 'Profile Fields Visibility', 'bp-user-info-widget' ); ?></h3>
		<table>
			<?php if ( function_exists( 'bp_has_profile' ) ) : if ( bp_has_profile() ) : while ( bp_profile_groups() ) : bp_the_profile_group(); ?>
				<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>
					<?php $fld_name = bp_get_the_profile_field_input_name();
						$fld_val        = isset( ${$fld_name} ) ? ${$fld_name} : 'no';
					?>
					<tr>
						<td>
							<label for="<?php echo $fld_name; ?>"><?php bp_the_profile_field_name() ?></label>
						</td>
						<td>
							<input type="radio" id="<?php echo $this->get_field_id( $fld_name ); ?>" name="<?php echo $this->get_field_name( $fld_name ); ?>" value="yes" <?php checked( $fld_val, 'yes' ); ?> >Show
							<input type="radio" id="<?php echo $this->get_field_id( $fld_name ); ?>" name="<?php echo $this->get_field_name( $fld_name ); ?>" value="no" <?php checked( $fld_val, 'no' ); ?>>Hide
						</td>
					</tr>
				<?php endwhile;
			endwhile;
			endif;
			endif; ?>
		</table>
		<?php
	}
	public static function get_users( $user_role = null ) {
		$bp_displayed_user_id = bp_displayed_user_id();
		return $bp_displayed_user_id;
	}
	public static function show_blog_profile( $instance ) {
		//if buddypress is not active, return
		if ( ! function_exists( 'buddypress' ) ) {
			return;
		}
		unset( $instance['title'] );//unset the title of the widget,because we will be iterating over the instance fields
		if ( bp_is_user() ) {
			$bp_displayed_user_id = array( bp_displayed_user_id() );
		}
		if ( empty( $bp_displayed_user_id ) ) {
			return;
		//Do not display the widget if profile field is empty
		}
		foreach ( $bp_displayed_user_id as $user ) {
			$user_id = $user;//["user_id"];
			$op = "<table class='bp-user-info-{$user}'>";
			//bad approach, because buddypress does not allow to fetch the field name from field key
			if ( function_exists( 'bp_has_profile' ) ) :
				if ( bp_has_profile( 'user_id=' . $user_id ) ) :
					while ( bp_profile_groups() ) : bp_the_profile_group();
						while ( bp_profile_fields() ) : bp_the_profile_field();
							$fld_name = bp_get_the_profile_field_input_name();
							if ( array_key_exists( $fld_name, $instance ) && $instance[ $fld_name ] == 'yes' ) {
								$op .= '<tr><h4 class="bp_user_info_title">' . bp_get_the_profile_field_name() . '</h4><p class="bp_user_info_data">' .xprofile_get_field_data( bp_get_the_profile_field_id(),$user_id, 'comma' ) . '</p></tr>';
							}
						endwhile;
					endwhile;
				endif;
			endif;
			$op .= "</table>";
			echo $op;
		}
	}
}


/* BuddyPress Author Widget  */
class BuddyPress_Author_Widget extends WP_Widget {
	public function __construct() {
		$widget_ops = array(
			'description' => __( 'Display user\'s profile fields on their posts. Note: This respects the privacy preference set by the user too.', 'buddypress-author-widget' ),
		);
		parent::__construct( false, _x( 'BuddyPress Author Widget', 'buddypress-author-widget' ), $widget_ops );
	}
	public function widget( $args, $instance ) {
		if ( ! millionairesdigest_get_blog_author_id() ) {
			return;
		}
		extract( $args );
		echo $args['before_widget'];
		echo $args['before_title']
		     . $instance['title']
		     . $args['after_title'];
		self::millionairesdigest_show_blog_profile( $instance );
		//Show the profile fields
		echo $args['after_widget'];
	}
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		foreach ( $new_instance as $key => $val ) {
			$instance[ $key ] = $val;
		}//Update the instance
		return $instance;
	}
	public function form( $instance ) {
	    $instance = wp_parse_args(
			(array) $instance,
			array(
				'title'       => __( '', 'buddypress-author-widget' ),
				'show_avatar' => 'no',
			)
		);
		$title = strip_tags( $instance['title'] );
		extract( $instance, EXTR_SKIP );
		?>
		<p>
			<label for="millionairesdigest-widget-title"><?php _e( 'Title:', 'buddypress-author-widget' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                       value="<?php echo esc_attr(  $title ); ?>"/>
			</label>
		</p>
		<p>
			<label for="millionairesdigest-widget-show-avatar"><?php _e( 'Show Avatar', 'buddypress-author-widget' ); ?>
				<input type="radio" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>"
                       name="<?php echo $this->get_field_name( 'show_avatar' ); ?>"
                       value="yes" <?php checked( $instance['show_avatar'], 'yes' ) ; ?> > <?php _e( 'Yes', 'buddypress-author-widget' );?>
				<input type="radio" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>"
                       name="<?php echo $this->get_field_name( 'show_avatar' ); ?>"
                       value="no" <?php checked( $instance['show_avatar'] , "no" ); ?>> <?php _e( 'No', 'buddypress-author-widget' );?>
			</label>
		</p>
		<?php
		//Get all the xprofile fields and select whether to show them or not
		?>
		<h3><?php _e( 'Profile Fields Visibility', 'buddypress-author-widget' ); ?></h3>
		<table>
			<?php if ( function_exists( 'bp_has_profile' ) ) :
		if ( bp_has_profile() ) : while ( bp_profile_groups() ) : bp_the_profile_group();
			?>
			<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>
			<?php $fld_name = bp_get_the_profile_field_input_name();
						$fld_val = isset( $instance[$fld_name] ) ? $instance[$fld_name]  : 'no';//Sets the default option
			?>
			<tr>
				<td>
					<label for="<?php echo $fld_name; ?>"><?php bp_the_profile_field_name() ?></label>
				</td>
				<td>
					<input type="radio" id="<?php echo $this->get_field_id( $fld_name ); ?>"
                                       name="<?php echo $this->get_field_name( $fld_name ); ?>"
                                       value="yes" <?php if ( $fld_val == "yes" ) {
									echo "checked='checked'";
								} ?> >Show
					<input type="radio" id="<?php echo $this->get_field_id( $fld_name ); ?>"
                                       name="<?php echo $this->get_field_name( $fld_name ); ?>"
                                       value="no" <?php if ( $fld_val != "yes" ) {
									echo "checked='checked'";
								} ?>>Hide
				</td>
			</tr>
			<?php endwhile;
		endwhile;
		endif;
		endif; ?>
		</table>
		<?php
	}
	private static function millionairesdigest_show_blog_profile( $instance ) {
		$show_avatar = $instance['show_avatar'];//We need to preserve for multi-admin
		unset( $instance['show_avatar'] );
		unset( $instance['title'] );//Unset the title of the widget,because we will be iterating over the instance fields
		$author = millionairesdigest_get_blog_author_id();
		if ( empty( $author ) ) {
			return;
		}
		$user_id = $author;
		$op = "<table class='buddypress-author-{$author}'>";
		if ( $show_avatar == 'yes' ) {
			$op .= '<p>' . bp_core_fetch_avatar( array(
				'item_id' => $user_id,
				'type' => 'thumb',
				'height' => '50',
				'width' => '50'
			) ) . '</p></p>';
		}
		//Bad approach, because buddypress does not allow to fetch the field name from field key
		if ( function_exists( 'bp_has_profile' ) ) :
			if ( bp_has_profile( 'user_id=' . $user_id ) ) :
				while ( bp_profile_groups() ) : bp_the_profile_group();
					while ( bp_profile_fields() ) : bp_the_profile_field();
						$fld_name = bp_get_the_profile_field_input_name();
						if ( array_key_exists( $fld_name, $instance ) && $instance[ $fld_name ] == 'yes' ) {
							$op .= '<tr><p class="buddypress_author_data">' . bp_get_profile_field_data( array(
								'field'   => bp_get_the_profile_field_id(),
								'user_id' => $user_id
							) ) . '</p></tr>';
						}
					endwhile;
				endwhile;
			endif;
		endif;
		$op .= '</table>';
		echo $op;
	}
}

//Last but not least, get the author ID for the current page/post
function millionairesdigest_get_blog_author_id() {
	//If this is a single user blog, admin will not need this widgets as Bp profile for blogs will do that
	$author_id = null;
	if ( in_the_loop() ) {
		//inside post loop
		$author_id = get_the_author_meta('ID');
	} elseif ( is_singular() && ! is_buddypress() ) {
		global $wp_the_query;
		$author_id = $wp_the_query->posts[0]->post_author;
	} elseif ( is_author() ) {
		global $wp_the_query;
		$author_id = $wp_the_query->get_queried_object_id();
	}
	return $author_id;
}



/* BuddyPress My Groups */
class BP_My_Groups_Widget extends WP_Widget {
	
	function bp_my_groups_widget() {
		parent::__construct(
			'buddypress', // Base ID
			'BuddyPress My Groups', // Name
			array( 'description' => __( 'Display the groups of the currently displayed BuddyPress user if on or viewing their profile.', 'text_domain' ) )
		);
	}

	function widget($args, $instance) {
		global $bp;

	    extract( $args );
		$title = apply_filters('widget_title', empty($instance['title'])?__('My Groups','buddypress'):$instance['title']);

		echo $before_widget;
		echo $before_title
		   . $title
		   . $after_title; ?>

		<?php if ( bp_has_groups( 'type=active&max=5&user_id=' . $bp->displayed_user->id )) : ?>

			<ul class="my-groups-list item-list">
				<?php while ( bp_groups() ) : bp_the_group(); ?>
					<li>
						<div class="item-avatar">
							<a href="<?php bp_group_permalink() ?>"><?php bp_group_avatar_thumb() ?></a>
						</div>

						<div class="item">
							<div class="item-title"><a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_name() ?></a></div>
							<div class="item-meta"><span class="activity"><?php bp_group_member_count() ?></span></div>
						</div>
					</li>

				<?php endwhile; ?>
			</ul>
			<?php wp_nonce_field( 'groups_widget_groups_list', '_wpnonce-groups' ); ?>
			<input type="hidden" name="groups_widget_max" id="groups_widget_max" value="<?php echo  esc_attr( $instance['title'] ); ?>" />

		<?php else: ?>
			
			<div class="widget-error">
				<?php if( $bp->displayed_user->id && bp_is_my_profile() ) {
			   		printf( __( "You have not joined any groups yet.", 'buddypress' ) );
				} else {
			  		printf( __( "%s has not joined any groups.", 'buddypress' ), bp_get_user_firstname( bp_get_displayed_user_fullname() ) );
				} ?> 
			</div>

		<?php endif; ?>

		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = esc_attr( $instance['title'] );
		?>

		<p><label><?php _e('Title:','buddypress'); ?></label><input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
	<?php
	}
}



/* BuddyPress Extended Widget Options */
function bpew_load(){
    // display our own fields
    add_action('in_widget_form', 'bpew_extend_form', 10, 3);
    
    // save our new things
    add_filter('widget_update_callback', 'bpew_extend_update', 10, 4);
    
    // display content if needed
    add_filter('widget_display_callback', 'bpew_extend_display', 10, 3);
}
add_action('bp_init', 'bpew_load');

/* Handlers */
function bpew_extend_form($class, $return, $instance) {
    echo '<hr /><p>'.__('Display the widget if it satisfies one or more of the BuddyPress-specific options below:','bpew').'</p>';
    if(!isset($instance['bp_component_type']))
        $instance['bp_component_type'] = '';
    if(!isset($instance['bp_component_ids']))
        $instance['bp_component_ids'] = '';
    echo '<p>
           <input '.checked($instance['bp_component_type'], '', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value=""/> '.__('Do not apply', 'bpew').'<br />
            <input '.checked($instance['bp_component_type'], 'member_typea', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value="member_typea"/> '.__('Member Type: User', 'bpew').'<br />
            <input '.checked($instance['bp_component_type'], 'member_typeb', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value="member_typeb"/> '.__('Member Type: Brand', 'bpew').'<br />
            <input '.checked($instance['bp_component_type'], 'member_typec', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value="member_typec"/> '.__('Member Type: Famous Person', 'bpew').'<br />
            <input '.checked($instance['bp_component_type'], 'member_typed', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value="member_typed"/> '.__('Member Type: Organization', 'bpew').'<br />
            <input '.checked($instance['bp_component_type'], 'member_typee', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value="member_typee"/> '.__('Member Type: Millonaires Digest', 'bpew').'<br />
            <input '.checked($instance['bp_component_type'], 'member_typef', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value="member_typef"/> '.__('Member Type: Government', 'bpew').'<br />
           <input '.checked($instance['bp_component_type'], 'members', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value="members"/> '.__('Members (Single)', 'bpew').'<br />
		   <input '.checked($instance['bp_component_type'], 'my_profile', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value="my_profile"/> '.__('My Profile', 'bpew').'<br />
           <input '.checked($instance['bp_component_type'], 'members_dir', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value="members_dir"/> '.__('Members Directory', 'bpew').'<br />
           <input '.checked($instance['bp_component_type'], 'groups', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value="groups"/> '.__('Groups (Single)', 'bpew').'<br />
           <input '.checked($instance['bp_component_type'], 'groups_dir', false).' type="radio" name="'.$class->get_field_name('bp_component_type').'" value="groups_dir"/> '.__('Groups Directory', 'bpew').'
       </p>';
    echo '<p>
           <label id="'.$class->get_field_id('bp_component_ids').'">'.__('IDs','bpew').':</label>
           <input id="'.$class->get_field_id('bp_component_ids').'" type="text" name="'.$class->get_field_name('bp_component_ids').'" value="'.$instance['bp_component_ids'].'"/><br />
           <span class="description">'.__('Use commas to separate; No spaces.','bpew').'</span>
       </p>';  
    add_action('bpew_extend_form', $class, $return, $instance);
    return $return;
}

function bpew_extend_update($instance, $new_instance, $old_instance, $this) {
    $new_instance = apply_filters('bpew_extend_update', $new_instance, $old_instance, $instance, $this);
    return $new_instance;
}

function bpew_extend_display($instance, $this, $args) {
    if(empty($instance['bp_component_type']))
        return $instance;
    global $bp;
    $user_id = bp_displayed_user_id();
    // Display on profile pages with the "User" member type
    if($instance['bp_component_type'] == 'member_typea' && bp_displayed_user_id() && bp_has_member_type( $user_id, 'user' )
    && in_array(bp_has_member_type(), explode(',', $instance['bp_component_ids']))){
        return $instance;
    }
    // Display on profile pages with the "Brand" member type
    if($instance['bp_component_type'] == 'member_typeb' && bp_displayed_user_id() && bp_has_member_type( $user_id, 'brand' )
    && in_array(bp_has_member_type(), explode(',', $instance['bp_component_ids']))){
        return $instance;
    }
    // Display on profile pages with the "Famous Person" member type
    if($instance['bp_component_type'] == 'member_typec' && bp_displayed_user_id() && bp_has_member_type( $user_id, 'famous-person' )
    && in_array(bp_has_member_type(), explode(',', $instance['bp_component_ids']))){
        return $instance;
    }
    // Display on profile pages with the "Organization" member type
    if($instance['bp_component_type'] == 'member_typed' && bp_displayed_user_id() && bp_has_member_type( $user_id, 'organization' )
    && in_array(bp_has_member_type(), explode(',', $instance['bp_component_ids']))){
        return $instance;
    }
    // Display on profile pages with the "Millionaire's Digest" member type
    if($instance['bp_component_type'] == 'member_typee' && bp_displayed_user_id() && bp_has_member_type( $user_id, 'millionaires-digest' )
    && in_array(bp_has_member_type(), explode(',', $instance['bp_component_ids']))){
        return $instance;
    }
    // Display on profile pages with the "Government" member type
    if($instance['bp_component_type'] == 'member_typef' && bp_displayed_user_id() && bp_has_member_type( $user_id, 'government' )
    && in_array(bp_has_member_type(), explode(',', $instance['bp_component_ids']))){
        return $instance;
    }
	// Display on logged-in user's profile only
	if($instance['bp_component_type'] == 'my_profile' && is_user_logged_in() && bp_is_my_profile()
    && explode(',', $instance['bp_component_ids'])){
        return $instance;
    }

    // Display on specific profile pages
    if($instance['bp_component_type'] == 'members' && bp_displayed_user_id()
    && in_array(bp_displayed_user_id(), explode(',', $instance['bp_component_ids']))){
        return $instance;
    }
    if($instance['bp_component_type'] == 'members_dir' && bp_is_directory() && bp_current_component() == BP_MEMBERS_SLUG){
        return $instance;
    }
    // Display on groups pages only
    $group_id = $bp->groups->current_group->id;
    if($instance['bp_component_type'] == 'groups' && !empty($group_id)
    && in_array($group_id, explode(',', str_replace(' ', '', trim($instance['bp_component_ids']))))){
        return $instance;
    }
    if($instance['bp_component_type'] == 'groups_dir' && bp_is_directory() && bp_current_component() == BP_GROUPS_SLUG){
        return $instance;
    }
    return false;
}
add_action('bp_init', 'bpew_load');



/* BuddyPress Recent Profile Visitors */
class Recent_Profile_Visitors_Widget extends WP_Widget {
	function __construct() {
		parent::__construct(
			// Base ID of your widget
			'recent_profile_visitors_widget',
			// Widget name will appear in UI
			__('BuddyPress Recent Profile Visitors', 'recent_profile_visitors_widget_domain'), 
			// Widget description
			array( 'description' => __( 'Display the recent profile visitors of the current loggedin user', 'recent_profile_visitors_widget_domain' ), 
				 ) );
	}
	
// Creating widget front-end
public function widget( $args, $instance ) {
	//Hide the widget if user has turned recording off or if the user does not have one of the following member types
	$user_id = bp_loggedin_user_id();
	if ( ! is_super_admin() && ! bp_has_member_type( $user_id, 'user' ) && ! bp_has_member_type( $user_id, 'millionaires-digest' ) )
			return;
	if ( ! visitors_is_active_visitor_recording( $user_id ) )
			return;
	$title = apply_filters( 'widget_title', $instance['title'] );
	// Before and after widget arguments are defined by themes
	echo $args['before_widget'];
	if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];
	// Run the code and display the output
	echo do_shortcode( '[bp-visitors-recent-visitors]', 'recent_profile_visitors_widget_domain' );
	echo $args['after_widget'];
}

// Widget Backend 
public function form( $instance ) {
	if ( isset( $instance[ 'title' ] ) ) {
		$title = $instance[ 'title' ];
	}
	else {
		$title = __( 'Recent Profile Visitors', 'recent_profile_visitors_widget_domain' );
	}
// Widget admin form
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php 
}
     
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
	$instance = array();
	$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
	return $instance;
	}
} // Class recent_profile_visitors_widget ends here



/* BuddyPress Total Friends Count */
class Total_Friends_Widget extends WP_Widget {
  public function __construct() {
      $widget_ops = array('classname' => 'Total_Friends_Widget', 'description' => 'Display the total number of friends a displayed user has on their profile as a widget.' );
      $this->WP_Widget('Total_Friends_Widget', 'BuddyPress Total Friends Count', $widget_ops);
  }
	function widget($args, $instance) {
		// PART 1: Extracting the arguments + getting the values
		extract($args, EXTR_SKIP);
		$title = empty( $instance['title'] ) ? ' ' : apply_filters('widget_title', $instance['title']);
		$subject_single = __( 'person' , 'Total_Friends_Widget' );
		$subject_plural = __( 'people' , 'Total_Friends_Widget' );
		$count = friends_get_friend_count_for_user( bp_displayed_user_id() );
		// Before widget code, if any
		echo (isset($before_widget)?$before_widget:'');
		// PART 2: The title and the text output
		if (!empty($title) )
			echo $before_title . $title . $after_title;
		if( $count == 0 ) {
			echo '<p/>' . $count . '&nbsp' . $subject_plural . '<p/>';
		}
		if( $count == 1 ) {
			echo '<p/>' . $count . '&nbsp' . $subject_single . '<p/>';
		}
		if( $count > 1 ) {
			echo '<p/>' . $count['followers'] . '&nbsp' . $subject_plural . '<p/>';
		}
		// After widget code, if any
		echo (isset($after_widget)?$after_widget:'');
	}
	
  public function form( $instance ) {
     // PART 1: Extract the data from the instance variable
     $instance = wp_parse_args( (array) $instance, array( 'title' => 'Total Friends' ) );
     $title = $instance['title'];
     // PART 2-3: Display the fields
     ?>
     <!-- PART 2: Widget Title field START -->
     <p>
      <label for="<?php echo $this->get_field_id('title'); ?>">Title:
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
               name="<?php echo $this->get_field_name('title'); ?>" type="text"
               value="<?php echo attribute_escape($title); ?>" />
      </label>
      </p>
      <!-- Widget Title field END -->
     <?php
  }
	
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }
}



/* BuddyPress User's Posts */
class BuddyPress_Posts_Widget extends WP_Widget {
	/* Widget setup */
	function __construct() {
		$widget_ops = array( 
			'description' => __( 'Display the posts written or created by the displayed BuddyPress user who\'s profile we are currently on.', 'millionaires_digest_widget' ) 
		);
		parent::__construct( 'millionaires_digest_buddypress_posts', __('BuddyPress User\'s Posts','millionaires_digest_widget'), $widget_ops );
	}

	/* Display the widget */
	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );

		$title = apply_filters( 'widget_title', $instance['title'] );
		$limit = $instance['limit'];
		$length = (int)( $instance['length'] );
		$thumb = isset($instance['thumb']) ? $instance['thumb'] : '';
		$excerpt = isset($instance['excerpt']) ? $instance['excerpt'] : '' ;
		$cat = $instance['cat'];
		$post_type = $instance['post_type'];

		global $authordata, $post;
		$args = array(
            'numberposts' => $limit,
            'cat' => $cat,
            'post_type' => $post_type,
			'suppress_filters' => false
		);

		$millionaires_digest_recent_posts = get_posts( array(
			'author' => bp_displayed_user_id(), 
            'numberposts' => $limit,
            'cat' => $cat,
            'post_type' => $post_type,
			'suppress_filters' => false
		) );

		if ( ! empty( $millionaires_digest_recent_posts ) ) {
			echo $before_widget;
			if ( ! empty( $title ) ) {
				echo $before_title . $title . $after_title;
			}
			?>
			<div>
				<ul class='news-widget-wrap'>
					<?php foreach ( $millionaires_digest_recent_posts as $post ) : setup_postdata( $post ); ?>
						<li class="news-content">
							<a class="news-link" href="<?php the_permalink(); ?>">
								<?php if ( $thumb == 1 ) : /* Display author image */ ?>
									<span class="news-thumb"><?php echo get_avatar( get_the_author_meta( 'ID' ), 40 ); ?></span>
									<span class="news-headline"><?php the_title(); ?>
										<small class="news-time"><?php echo get_the_date(); ?></small></span>
									<?php if ( $excerpt == 1 ) { ?>
										<span class="news-excerpt"><?php echo millionaires_digest_excerpt( $length, false ); ?></span>
									<?php } ?>
								<?php elseif ( $thumb == 2 ) : /* Display post thumbnail */ ?>
									<?php
									$img_url = get_the_post_thumbnail_url();
									if ( $img_url != '' ) {
										$image = aq_resize( $img_url, 44, 44, true, true, true );
										if ( ! $image ) {
											$image = $img_url;
										}
										$html_img = '<img src="' . $image . '" alt="" title="">';
									} else {
										$html_img = '';
									}
									?>
									<span class="news-thumb"><?php echo $html_img; ?></span>
									<span class="news-headline"><?php the_title(); ?>
										<small class="news-time"><?php echo get_the_date(); ?></small></span>
									<?php if ( $excerpt == 1 ) { ?>
										<span class="news-excerpt"><?php echo millionaires_digest_excerpt( $length, false ); ?></span>
									<?php } ?>
								<?php else : ?>
									<span><?php the_title(); ?>
										<small class="news-time"><?php echo get_the_date(); ?></small></span>
									<?php if ( $excerpt == 1 ) { ?>
										<span class="news-excerpt"><?php echo millionaires_digest_excerpt( $length, false ); ?></span>
									<?php } ?>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach;
					wp_reset_postdata(); ?>
				</ul>
			</div>
			<?php
			echo $after_widget;
		}
	}
	
	/* Update widget */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = esc_attr( $new_instance['title'] );
		$instance['limit'] = $new_instance['limit'];
		$instance['length'] = (int)( $new_instance['length'] );
		$instance['thumb'] = $new_instance['thumb'];
		$instance['excerpt'] = $new_instance['excerpt'];
		$instance['cat'] = $new_instance['cat'];
		$instance['post_type'] = $new_instance['post_type'];
		return $instance;
	}
	
	/* Widget setting */
	function form( $instance ) {
        $defaults = array(
            'title' => '',
            'limit' => 5,
            'length' => 100,
            'thumb' => true,
            'excerpt' => '',
            'cat' => '',
            'post_type' => '',
            'date' => true,
        );
		$instance = wp_parse_args( (array) $instance, $defaults );
		$title = esc_attr( $instance['title'] );
		$limit = $instance['limit'];
		$length = (int)($instance['length']);
		$thumb = $instance['thumb'];
		$excerpt = $instance['excerpt'];
		$cat = $instance['cat'];
		$post_type = $instance['post_type'];
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'millionaires_digest_widget' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php _e( 'Limit:', 'millionaires_digest_widget' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'limit' ); ?>" id="<?php echo $this->get_field_id( 'limit' ); ?>">
				<?php for ( $i=1; $i<=20; $i++ ) { ?>
					<option <?php selected( $limit, $i ) ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php } ?>
			</select>
		</p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'excerpt' ) ); ?>"><?php _e( 'Display excerpt?', 'millionaires_digest_widget' ); ?></label>
            <select id="<?php echo $this->get_field_id( 'excerpt' ); ?>" name="<?php echo $this->get_field_name( 'excerpt' ); ?>">
                <option value="">No</option>
                <option <?php selected( '1', $excerpt ); ?> value="1">Yes</option>
            </select>&nbsp;
        </p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'length' ) ); ?>"><?php _e( 'Excerpt length(characters):', 'millionaires_digest_widget' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'length' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'length' ) ); ?>" type="text" value="<?php echo $length; ?>" />
		</p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'thumb' ) ); ?>"><?php _e( 'Display Thumbnail?', 'millionaires_digest_widget' ); ?></label>
            <select id="<?php echo $this->get_field_id( 'thumb' ); ?>" name="<?php echo $this->get_field_name( 'thumb' ); ?>">
                <option value="">No</option>
                <option <?php selected( '1', $thumb ); ?> value="1">Author Image</option>
                <option <?php selected( '2', $thumb ); ?> value="2">Featured Image</option>
            </select>&nbsp;
        </p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'cat' ) ); ?>"><?php _e( 'Show from category: ' , 'millionaires_digest_widget' ); ?></label>
			<?php wp_dropdown_categories( array( 'name' => $this->get_field_name( 'cat' ), 'show_option_all' => __( 'All categories' , 'millionaires_digest_widget' ), 'hide_empty' => 1, 'hierarchical' => 1, 'selected' => $cat ) ); ?>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>"><?php _e( 'Choose the Post Type: ' , 'millionaires_digest_widget' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>">
				<?php foreach ( get_post_types( '', 'objects' ) as $post_type ) { ?>
					<option value="<?php echo esc_attr( $post_type->name ); ?>" <?php selected( $instance['post_type'], $post_type->name ); ?>><?php echo esc_html( $post_type->labels->singular_name ); ?></option>
				<?php } ?>
			</select>
		</p>
	<?php
	}
}



?>
