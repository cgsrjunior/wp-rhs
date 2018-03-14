<?php

class RHSRecommendPost extends RHSMessage {
    const RECOMMEND_POST_TO_KEY = '_rhs_recommend_post_to';
    const RECOMMEND_POST_FROM_KEY = '_rhs_recommend_post_from';

    function __construct() {
        add_action('wp_enqueue_scripts', array(&$this, 'addJS'));
        add_action('wp_ajax_show_people_to_recommend', array($this, 'show_people_to_recommend'));
        add_action('wp_ajax_recommend_the_post', array($this, 'recommend_the_post'));
    }
    
    function addJS() {
        wp_enqueue_script('recommend_post', get_template_directory_uri() . '/inc/recommend-post/recommend_post.js', array('jquery'));
        wp_localize_script('recommend_post', 'recommend_post', array('ajaxurl' => admin_url('admin-ajax.php')));
    }
  
    /**
     * Busca de usuários para indicar post
     */
    function show_people_to_recommend() {

        $data = array();

        $users = new WP_User_Query(array(
            'search'         => '*' . esc_attr( $_POST['query'] ) . '*',
            'search_columns' => array('display_name','user_nicename'),
            'number'         => 20,
            'orderby'        => 'display_name',
        ) );

        foreach ($users->results as $user) {
            $user_ufmun = get_user_ufmun($user->ID);
            $uf = return_value_or_dash($user_ufmun['uf']['sigla']);
            $mun = return_value_or_dash($user_ufmun['mun']['nome']);
            
            $data[] = array(
                'user_id'  => $user->ID,
                'name' => $user->display_name,
                'avatar' => get_avatar_url($user->ID, array("size"=>32)),
                'location' => $mun . ', ' . $uf,
            );
        }
        
        echo json_encode($data);
        exit;

    }

    /**
     * Envia indicação de post para usuário
     */
    function recommend_the_post() {
        $this->clear_messages();

        $current_user = wp_get_current_user();

        $users_selected = $_POST["users"];
        $temp_data = str_replace("\\", "",$users_selected);
        $users_selected = json_decode($temp_data);

        foreach($users_selected as $key=>$user) {
            $user_id = $user->user_id;
            $user = new RHSUser(get_userdata($user_id));
            if($user instanceof RHSUser) {
                $post_id = $_POST['post_id'];
                $_user_name = $user->get_name();
                $data['user'] = array(
                    'user_id' => $user_id,
                    'post_id' => $post_id,
                    'recommend_from' => $current_user->ID,
                    'value' => $_user_name,
                    'sent_name' => $current_user->display_name
                );
                $this->add_recomment_post($post_id, $user_id, $current_user, $data);
                $is_sent = true;
            } else {
                $data['msgErr'] = "Usuário não encontrado. Tente novamente mais tarde!";
                $_is_sent = false;
            }
        }

        if(!$_is_sent){
            $this->set_messages('Indicação de leitura enviada', false, 'success');
        }

        $data['messages'] = $this->messages();

        echo json_encode($data);
        exit;
    }

    function add_recomment_post($post_id, $user_id, $current_user, $data) {
        add_user_meta($user_id, self::RECOMMEND_POST_TO_KEY, $data['user']);
        $return = add_user_meta($current_user->ID, self::RECOMMEND_POST_FROM_KEY, $data['user']);
    
        if ($return)
            do_action('rhs_add_recommend_post', ['post_id' => $post_id, 'user_id' => $user_id]);

        return $return;
    }
}

add_action('init', function() {
    global $RHSRecommendPost;
    $RHSRecommendPost = new RHSRecommendPost();
});


