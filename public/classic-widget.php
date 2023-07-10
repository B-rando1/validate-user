<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access Denied', 'validate-user' ) );
}

if ( ! class_exists( 'ValidateUserClassicWidget' ) ) {

	class ValidateUserClassicWidget extends WP_Widget {


		public function __construct(  ) {

			$widget_ops = [
				'classname' => 'validate-user-classic-widget'
			];
			parent::__construct( 'validate-user-classic-widget', esc_html__( 'New User Form', 'validate-user' ), $widget_ops );

		}

		/**
		 * The main display function for the widget
		 *
		 * @param $args
		 * @param $instance
		 *
		 * @return void
		 */
		public function widget( $args, $instance ): void {

			echo $args['before_widget'];

			include( VALIDATE_USER_PATH . 'templates/application-form.php' );

			echo $args['after_widget'];

		}

		public function form( $instance ) {
			// No options...
		}

		public function update( $new_instance, $old_instance ) {
			// Nothing to update...
		}

	}

	add_action( 'widgets_init', function () {
		register_widget( 'ValidateUserClassicWidget' );
	} );

}