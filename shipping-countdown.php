<?php
/**
 * Plugin Name: Shipping countdown
 * Description: Countdown until next shipping
 * Version: 1.0
 * Author: Martin Kollerup
 * Author URI: https://github.com/martinkolle/wp-shipping-countdown/
 * License: GNU/GPL v.2 or later
 */

add_action( 'widgets_init', 'shipping_countdown' );

function shipping_countdown() {

	load_plugin_textdomain('shipping_countdown', false, dirname(plugin_basename(__FILE__)).'/languages/');
	register_widget( 'shipping_countdown' );
}

class shipping_countdown extends WP_Widget {

	static $title;
	static $hours;
	static $minutes;
	static $append = false;

	function shipping_countdown() {
		$widget_ops = array( 'classname' => 'shipping_countdown', 'description' => __('Countdown until next shipping', 'shipping_countdown'));
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'shipping_countdown-widget' );
		$this->WP_Widget( 'shipping_countdown-widget', __('Shipping countdown', 'shipping_countdown'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );

		//Our variables from the widget settings.
		$title 		= apply_filters('widget_title', $instance['title'] );
		$hours 		= isset($instance['hours']) ? $instance['hours'] : '16';
		$minutes 	= isset($instance['minutes']) ? $instance['minutes'] : '00';
		$append 	= isset($instance['append']) ? $instance['append'] : false;

		//used for the init
		self::$title 	= $title;
		self::$hours 	= $hours;
		self::$minutes 	= $minutes;
		self::$append 	= $append;

		wp_enqueue_script("shipping-countdown", plugins_url('js/countdown.min.js',__FILE__),array("jquery"), '1.0', true);
		wp_enqueue_style("shipping-countdown", plugins_url('css/countdown.min.css',__FILE__));
		add_action('wp_footer',array($this,'shipping_countdown_init'));

		if(!$append){
			echo $before_widget;
			
			// Display the widget title 
			if ($title){
				echo $before_title . $title . $after_title;
			}

			echo '<div id="shipping_countdown"></div><p id="shipping_countdown_note"></p>';

			echo $after_widget;
		}
	}

	/**
	* Update the widget settings
	* @return array
	*/
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags to remove HTML
		$instance['title'] 		= strip_tags($new_instance['title']);
		$instance['hours'] 		= strip_tags($new_instance['hours']);
		$instance['minutes'] 	= strip_tags($new_instance['minutes']);
		$instance['append'] 	= strip_tags($new_instance['append']);

		return $instance;
	}

	/**
	* Edit form for widget
	* @author Martin Kollerup
	*/
	
	function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array( 'title' => __('Next shipping at', 'shipping_countdown'), 'hours' => 16, 'minutes' => 00, 'append' =>"");
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'shipping_countdown'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'hours' ); ?>"><?php _e('Hours:', 'shipping_countdown'); ?></label>
			<input id="<?php echo $this->get_field_id( 'hours' ); ?>" placeholder="Hours" class="widefat" name="<?php echo $this->get_field_name( 'hours' ); ?>" value="<?php echo $instance['hours']; ?>" style="width:50%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'minutes' ); ?>"><?php _e('Minutes:', 'shipping_countdown'); ?></label>
			<input id="<?php echo $this->get_field_id( 'minutes' ); ?>" placeholder="Minutes" class="widefat" name="<?php echo $this->get_field_name( 'minutes' ); ?>" value="<?php echo $instance['minutes']; ?>" style="width:50%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'append' ); ?>"><?php _e('Append to:', 'shipping_countdown'); ?></label>
			<input id="<?php echo $this->get_field_id( 'append' ); ?>" placeholder="#main-nav #top" class="widefat" name="<?php echo $this->get_field_name( 'append' ); ?>" value="<?php echo $instance['append']; ?>" style="width:100%;" />
		</p>
		<p class="description"><?php _e('Only use "Append to" if you know how to use it. <a href="http://api.jquery.com/append/" target="_blank">More info</a>', 'shipping_countdown'); ?></p>
	<?php
	}

	/**
	* Append javascript to header
	* @author Martin Kollerup
	*/

	function shipping_countdown_init(){

		$append = (self::$append) ? 'jQuery("'.self::$append.'").append(\'<div id="countdown"><span id="forsend">'.self::$title.'</span> </div>\');' : "";

		echo '<script type="text/javascript">jQuery(function(){
			'.$append.'
			var curTime = new Date();
			//date string for today
			ts = new Date(curTime.getFullYear(), curTime.getMonth(), curTime.getDate(), '.self::$hours.', '.self::$minutes.')
			//past 16:00 --> count until tomorrow. 
			if((new Date()) > ts){
				var curTime = new Date();
				ts = new Date(curTime.getFullYear(), curTime.getMonth(), curTime.getDate() + 1, '.self::$hours.', '.self::$minutes.')
			}
			jQuery("#shipping_countdown").countdown({
				timestamp	: ts,
				callback	: function(days, hours, minutes, seconds){
					//note.html(message);
				}
			});
		});</script>';
	}
}