<?php

class Facebook_Twitter extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		add_action('wp_enqueue_scripts', array($this, 'js_widget'));
		$widget_ops = array( 
			'classname' => 'Facebook_Twitter',
			'description' => 'Widget para exibir a Pagina do Facebook e o botão Seguir do Twitter',
		);
		parent::__construct( 'facebook_twitter', 'Facebook_Twitter', $widget_ops );
	}

    public function js_widget() {
		if ( is_active_widget( false, false, $this->id_base, true ) ) {
			wp_enqueue_script('facebook_twitter', get_template_directory_uri() . '/inc/widgets/Facebook_Twitter/facebook_twitter.js', array('jquery'));
		}
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
    public function widget( $args, $instance ) { ?>
        <div class="facebook_twitter">
            <p class="facebook"></p>
            <p class="twitter"></p>
        </div>
        <?php
    }
    
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        return $instance;
    }
}

function facebook_twitter_widget() {
    register_widget('Facebook_Twitter');
}

add_action('widgets_init', 'facebook_twitter_widget');