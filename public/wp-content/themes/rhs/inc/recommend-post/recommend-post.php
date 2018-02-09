<?php

class RHSRecommendPost extends RHSMessage {
    const RECOMMEND_POST_TO_KEY = '_rhs_recommend_post_to';
    const RECOMMEND_POST_FROM_KEY = '_rhs_recommend_post_from';

    function __construct() {
        add_action('wp_enqueue_scripts', array(&$this, 'addJS'));
        add_action('wp_ajax_show_people_to_recommend', array( $this, 'show_people_to_recommend' ) );
        add_action('wp_ajax_recommend_the_post', array( $this, 'recommend_the_post' ) );        
    }
    
    function addJS() {
        wp_enqueue_script('rhs_recommend_post', get_template_directory_uri() . '/inc/recommend-post/recommend-post.js', array('jquery'));
        wp_localize_script('rhs_recommend_post', 'recommend_post', array('ajaxurl' => admin_url('admin-ajax.php')));
    }

    /**
     * Busca de usuários para indicar post
     */
    function show_people_to_recommend() {

        $data = array('suggestions' => array());

        $users = new WP_User_Query( array(
            'search'         => '*' . esc_attr( $_POST['string'] ) . '*',
            'search_columns' => array( 'user_nicename', 'user_email' ),
            'number'         => -1,
            'orderby'        => 'display_name',
        ) );

        foreach ( $users->results as $user ) {

            $data['suggestions'][] = array(
                'data'  => $user->ID,
                'value' => $user->display_name
            );
        }

        echo json_encode( $data );
        exit;

    }

    /**
     * Envia indicação de post para usuário
     */
    function recommend_the_post() {

        $this->clear_messages();

        $current_user = wp_get_current_user();
        $user = new RHSUser(get_userdata($_POST['user_id']));
        $user_id = $_POST['user_id'];
        $post_id = $_POST['post_id'];
        
        $data['user'] = array(
            'user_id' => $user_id,
            'post_id' => $post_id,
            'recommend_from' => $current_user->ID
        );

        add_user_meta($user_id, self::RECOMMEND_POST_TO_KEY, $data['user']);
        add_user_meta($current_user->ID, self::RECOMMEND_POST_FROM_KEY, $data['user']);

        $this->set_messages($user->get_name() . ' recebeu a indicação de leitura', false, 'success');

        $data['messages'] = $this->messages();

        echo json_encode($data);
        exit;
    }
}

global $RHSRecommendPost;
$RHSRecommendPost = new RHSRecommendPost();