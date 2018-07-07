<?php

/* Register All of the New Widgets We Created */
function add_new_millionaires_digest_widgets() {
	add_action('widgets_init', create_function('', 'eturn register_widget("AuthorRecentArticles");') );
	add_action('widgets_init', create_function('', 'return register_widget("AuthorRecentVideos");') );
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
				$title = str_replace( '[author]', $author, $title );
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
				$title = str_replace( '[author]', $author, $title );
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

?>
