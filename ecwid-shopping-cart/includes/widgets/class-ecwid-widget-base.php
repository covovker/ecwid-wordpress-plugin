<?php

class Ecwid_Widget_Base extends WP_Widget {

	protected $_name;
	protected $_label;
	protected $_description;

	public function __construct( ) {
		$widget_ops = array(
			'description' => __( $this->_description, 'ecwid-shopping-cart' ),
			'classname' => 'widget_ecwid_' . $this->_name
		);

		parent::__construct( $this->_name, __( $this->_label, 'ecwid-shopping-cart' ) );
	}


	public function widget( $args, $instance ) {

		$title = apply_filters( 'widget_title', empty($instance['title'] ) ? '&nbsp;' : $instance['title']);

		$output = $args['before_widget'];

		if ( $title ) {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}

		// some themes do not wrap widgets
		$output .= '<div class="' . $this->_get_widget_wrapper_class_name() . '">';
		$output .= ecwid_get_scriptjs_code();
		$output .= $this->_get_content( $args, $instance );
		$output .= '</div>';

		$output .= $args['after_widget'];

		$output = apply_filters( 'ecwid_widget_content_' . $this->_name, $output);

		echo $output;
	}

	public function update($new_instance, $old_instance){

		// TODO: find a way to properly remove this $instance = $old_instance;
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));

		$instance = apply_filters( 'ecwid_widget_update_' . $this->_name, $instance );

		return $instance;
	}

	public function form($instance){

		$code = $this->_get_form( $instance );

		apply_filters( 'ecwid_widget_form_' . $this->_name, $code, $instance );

		echo $code;
	}

	protected function _get_form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array('title'=>'') );

		$title = htmlspecialchars($instance['title']);

		$output = '<p><label for="' . $this->get_field_name('title') . '">' . __('Title:') . ' <input style="width:100%;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';

		return $output;
	}

	protected function _get_content() {
		return "";
	}

	protected function _get_widget_wrapper_class_name()
	{
		return 'ecwid-widget-' . $this->_name;
	}
}