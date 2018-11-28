<?php

class RHSVoteQueueWidget extends WP_Widget {

    public function __construct() {
        $widget_options = array(
            'classname' => 'vote_widget',
            'description' => 'Listagem da fila de votação',
        );
        parent::__construct( 'vote_widget', 'Fila de Votação', $widget_options );
    }

    public function widget( $args, $instance ) {
        if ( ! is_user_logged_in() ) {
            echo '';
            return;
        }
        $title = apply_filters( 'widget_title', __('Fila de votação') );

        echo $args['before_widget'] . $args['before_title'] .'<a href="'.get_home_url().'/fila-de-votacao/">'. $title .'</a>'. $args['after_title'];

        $posts = get_posts(array(
            'posts_per_page'  => !empty($instance['qtd']) ? $instance['qtd'] : 0,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'post_status'      => RHSVote::VOTING_QUEUE
        ));

        ?>
        <div class="fila-content">
            <?php foreach ($posts as $post){ ?>
            <div class="fila-votacao">
                <a href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title; ?></a>
            </div>
            <?php } ?>
        </div>
        <?php echo $args['after_widget'];
    }

    public function form( $instance ) {
        $qtd = ! empty( $instance['qtd'] ) ? $instance['qtd'] : '5';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'qtd' ); ?>">Quantidade:</label>
            <select id="<?php echo $this->get_field_id( 'qtd' ); ?>" name="<?php echo $this->get_field_name( 'qtd' ); ?>">
                <?php for($i = 0; $i < 20; $i++){ ?>
                    <?php $selected = esc_attr( $qtd ) == $i ? 'selected' : ''; ?>
                    <option <?php echo $selected; ?> ><?php echo $i; ?></option>
                <?php } ?>
            </select>
        </p><?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance[ 'qtd' ] = strip_tags( $new_instance[ 'qtd' ] );
        return $instance;
    }
}

function rhs_register_vote_queue_widget() {
    register_widget( 'RHSVoteQueueWidget' );
}
add_action( 'widgets_init', 'rhs_register_vote_queue_widget' );
