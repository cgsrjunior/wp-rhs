<?php

class RHSFollow {
    const FOLLOW_KEY = '_rhs_follow';
    const FOLLOWED_KEY = '_rhs_followed';
    const FOLLOWED_POSTS_KEY = '_rhs_follow_post';
    const USERS_PER_PAGES = 10;

    function __construct() {
        add_action('wp_enqueue_scripts', array(&$this, 'addJS'));
        add_action('rhs_author_header_actions', array(&$this, 'show_header_follow_box'));
        add_action('wp_ajax_rhs_follow', array(&$this, 'ajax_callback'));
        add_action('rhs_total_follows', array(&$this, 'show_total_follows'),10,2);
    }

    function addJS() {
        wp_enqueue_script('rhs_follow', get_template_directory_uri() . '/inc/follow/follow.js', array('jquery'));
        wp_localize_script('rhs_follow', 'follow', array('ajaxurl' => admin_url('admin-ajax.php')));
    }

    /**
     * Show button to follow or unfollow user
     * 
     * @param int $author_id The author that we want to check and see if he/she is followed by the user
     * @return mixed it returns button to follow or unfoloww user and check if user is current author, for this case this button must be hide
     */
    function show_header_follow_box($author_id) {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            if ($user_id == $author_id) {
                return;
            }
            
            $isFollowing = $this->does_user_follow_author($author_id);

            $button_html = "<button class='btn btn-default follow-btn' data-author_id='". $author_id ."'>";
            $button_html .= ($isFollowing) ? "Deixar de Seguir" : "Seguir";
            $button_html .= "</button>";
            echo $button_html;
        }
    }

    function ajax_callback() {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $author_id = $_POST['author_id'];
            if (is_numeric($author_id)) {
                echo json_encode($this->toggle_follow($author_id, $current_user->ID));
            }
        }
        exit;
    }

    /**
     * Check if anuser follows an author (another user)
     *
     * @param int $author_id The author that we want to check and see if he/she is followed by the user
     * @param int $user_id (optional) The ID of the user you want to check if he/she follows the author
     * @return bool true if user follows author, false if dont
     */
    function does_user_follow_author($author_id, $user_id = null) {
        if (is_null($user_id)) {
            $current_user = wp_get_current_user();
            if (!$current_user)
                return false;
            $user_id = $current_user->ID;
        }
        $follows = $this->get_user_follows($user_id);
        return in_array($author_id, $follows);
    }

    /**
     * Toggle function to check if user follow author and return params for use in other functions
     * 
     * @param int $author_id The author id to check if user follow author by they id
     * @param int $user_id The user id to check if user follow author by they id
     * @return int return '1' if already follow and '2' if not, if not correspond some condition it will return 'false'
     */
    function toggle_follow($author_id, $user_id) {
        if ($this->does_user_follow_author($author_id, $user_id)) {
            if (false !== $this->remove_follow($author_id, $user_id))
                return 1;
        } else {
            if (false !== $this->add_follow($author_id, $user_id))
                return 2;
        }
        return false;
    }

    /**
     * Return meta user specific for user id to show followers of specific user
     * 
     * @param int $user_id Current user id to get meta keys
     * @return mixed Will be an array if user_id is not specified or if third param is false (is false in default). Will be value of meta_value field if third value is true. 
     */
    function get_user_followers($user_id) {
        return get_user_meta($user_id, self::FOLLOWED_KEY);
    }
    
    /**
     * Get total count of user according meta key
     * 
     * @param int $user_id The user id of author needed to count
     * @param string $meta_key The meta who needed to count
     * @return string Number of follows scoped by user and meta (FOLLOW_KEY, FOLLOWED_KEY)
     */
    function get_total_follows($user_id, $meta_key) {
        global $wpdb;
        $total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(umeta_id) FROM $wpdb->usermeta 
            WHERE user_id = %d AND meta_key = %s", 
            $user_id, $meta_key));
        return $total;
    }

    function get_followed_posts($user_id, $meta_key)
    {
        global $wpdb;
        $followed_posts_id = get_user_meta($user_id, $meta_key);

        $sql = "SELECT object_id, datetime FROM ".$wpdb->prefix . "notifications WHERE user_id = $user_id ORDER BY datetime asc";
        $follow_date = $wpdb->get_results($sql, ARRAY_A);
        foreach ($follow_date as $fd)
        {
            $datetime = new DateTime($fd['datetime']);
            $recent_follow [$fd['object_id']] = $datetime->format('d/m/Y');

        }

        if (!empty($followed_posts_id)) {
            $posts = [];
            foreach ($followed_posts_id as $id)
            {
                $author_id = get_post_field("post_author", $id);
                $author_name = "";
                $author_data = get_user_by("id", $author_id);

                if ($author_data instanceof WP_User) {
                    $author_name = $author_data->data->display_name;
                }

                $posts[$id]['post_title'] = get_the_title($id);
                $posts[$id]['permalink'] = get_permalink($id);
                $posts[$id]['author'] = $author_name;
                $posts[$id]['author_link'] = get_author_posts_url($author_id);
                $posts[$id]['follow_date'] = $recent_follow[$id];
            }
        } else {
            return false;
        }

        $posts = array_reverse($posts, true);
        return $posts;
    }


    /**
     * Show total of user follow or followed
     *
     * @param int $user_id The user id of author needed to count
     * @param string $meta_key The meta to count
     * @return string Number of follows scoped by user and meta (FOLLOW_KEY, FOLLOWED_KEY)
     */
    function show_total_follows($user_id, $meta_key) {
        echo $this->get_total_follows($user_id, $meta_key);
    }

    /**
     * Return meta user specific for user id to show follows of specific user
     * 
     * @param int $user_id 
     * @return mixed Will be an array if user_id is not specified or if third param is false (is false in default). Will be value of meta_value field if third value is true. 
     */
    function get_user_follows($user_id) {
        return get_user_meta($user_id, self::FOLLOW_KEY);
    }

    /**
     * This function have two actions:
     * 1) Add user meta to identify user who follow an author, meta_key in this case is called by '_rhs_follow'
     * 2) Add user meta to identify author followed by user, meta_key in this case is called by '_rhs_followed'
     *  
     * @param int $author_id The author id to check and add new usermeta
     * @param int $user_id The user id to check and add new usermeta
     * @return int/bool If user dont follows author it returns true with primary key id (umeta_id), false if already follow
     * @see rhs_add_user_meta_unique function declared on functions.php
     */
    function add_follow($author_id, $user_id) {
        rhs_add_user_meta_unique($user_id, self::FOLLOW_KEY, $author_id);
        $return = rhs_add_user_meta_unique($author_id, self::FOLLOWED_KEY, $user_id);
        if ($return)
            do_action('rhs_add_user_follow_author', ['user_id' => $user_id, 'author_id' => $author_id]);

        return $return;
        // muito difícil acontecer um erro só em um dos metadados, então parece seguro retornar só o retorno da segunda chamada
    }

    /**
     * This function have two actions:
     * 1) Removes user meta to identify user who follow an author, meta_key in this case is called by '_rhs_follow'
     * 2) Removes user meta to identify author followed by user, meta_key in this case is called by '_rhs_followed'
     * 
     * @param int $author_id The author id to check and remove usermeta
     * @param int $user_id The user id to check and remove usermeta
     * @return bool true if action is completed, removing relation between user_id, meta_key and meta_value, false if dont
     * @see delete_user_meta on wordpress documentation
     */
    function remove_follow($author_id, $user_id) {
        delete_user_meta($user_id, self::FOLLOW_KEY, $author_id);
        $return = delete_user_meta($author_id, self::FOLLOWED_KEY, $user_id);
        if ($return)
            do_action('rhs_delete_user_follow_author', ['user_id' => $user_id, 'author_id' => $author_id]);
        return $return;
        // muito difícil acontecer um erro só em um dos metadados, então parece seguro retornar só o retorno da segunda chamada
    }

    /**
     * Function to return follow list with pagination
     * 
     * @param int $author_id The author id of current page
     * @param string $meta The meta key (FOLLOW_KEY or FOLLOWED_KEY)
     * @param int $paged Param to get current page according rewrite rules (rhs_paged)
     * @return mixed Return class with params
     */
    function get_follows_list($author_id, $meta, $paged) {
        $users_per_page = self::USERS_PER_PAGES;
        $offset = $users_per_page * ($paged - 1);
        $args = array(
            'meta_key'  => $meta,
            'meta_value'=> $author_id,
            'orderby'   => 'display_name',
            'order'     => 'ASC',
            'paged'     => $paged,
            'number'    => $users_per_page,
            'offset'    => $offset
        );
        $author_query = new WP_User_Query($args);
        return $author_query;
    }

    /**
     * Show pagination 
     * 
     * @param string $meta The meta key (FOLLOW_KEY or FOLLOWED_KEY) 
     * @param int $paged Param to get current page according rewrite rules (rhs_paged)
     * @return mixed Return html with paginate links
     */
    function show_follow_pagination($meta, $paged) {
        $author = get_queried_object();
        $author_query = $this->get_follows_list($author->ID, $meta, $paged);
        
        $total_pages = 1;
        $total_pages = ceil($author_query->total_users / self::USERS_PER_PAGES);

        $big = 999999999;
        $content = paginate_links( array(
            'base'         => str_replace($big, '%#%', get_pagenum_link($big)),
            'format'       => 'page/%#%',
            'prev_text'    => __('&laquo; Anterior'),
            'next_text'    => __('Próxima &raquo;'), 
            'total'        => $total_pages,
            'current'      => $paged,
            'end_size'     => 1,
            'type'         => 'array',
            'mid_size'     => 8,
            'prev_next'    => true,
        ));
        
        if (is_array($content)) {
            $current_page = (get_query_var('rhs_paged') == 0) ? 1 : get_query_var('rhs_paged');
            echo '<ul class="pagination">';
            foreach ($content as $i => $page) {
                echo "<li>$page</li>";
            }
            echo '</ul>';
        }
    }

}

add_action('init', function() {
    global $RHSFollow;
    $RHSFollow = new RHSFollow();
});



