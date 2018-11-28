<?php

// SUBSTITUA  Carrossel pelo slug do metabox

class CarrosselMetabox {

    protected static $metabox_config = array(
        'Carrossel', // slug do metabox
        'Carrossel', // título do metabox
        'post', // array('post','page','etc'), // post types
        'side' // onde colocar o metabox
    );

    static function init() {
        add_action('add_meta_boxes', array(__CLASS__, 'addMetaBox'));
        add_action('save_post', array(__CLASS__, 'savePost'));
    }

    static function addMetaBox() {
        add_meta_box(
            self::$metabox_config[0],
            self::$metabox_config[1],
            array(__CLASS__, 'metabox'), 
            self::$metabox_config[2],
            self::$metabox_config[3]
            
        );
    }

    
    static function metabox(){
        global $post;
        
        wp_nonce_field( 'save_'.__CLASS__, __CLASS__.'_noncename' );
        
        $_home = get_post_meta($post->ID, "_home", true);
        $highlighted = $_home >= 1 ?  "checked" : "";
        
        ?>
        <input type="checkbox" id="carrossel_<?php echo $post->ID; ?>" name="RHS_Carrossel" <?php echo $highlighted; ?> value="1">
        <label> Adicionar post ao Carrossel </label>
        <br/>
        <br/>
        <label>Posição</label>
        
        <select name="RHS_Carrossel_order">
            <?php for($x=1; $x<=10; $x++): ?>
                <option value="<?php echo $x; ?>" <?php echo selected($_home, $x); ?> ><?php echo $x; ?></option>
            <?php endfor; ?>
        </select>
        
        <?php
    }

    static function savePost($post_id) {
        // verify if this is an auto save routine. 
        // If it is our form has not been submitted, so we dont want to do anything
        if (!isset($_POST['post_type']) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) )
            return;

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times

        if (isset($_POST[__CLASS__.'_noncename']) && !wp_verify_nonce($_POST[__CLASS__.'_noncename'], 'save_'.__CLASS__))
            return;


        // Check permissions
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id))
                return;
        }
        else {
            if (!current_user_can('edit_post', $post_id))
                return;
        }

        // OK, we're authenticated: we need to find and save the data
        if(isset($_POST['RHS_Carrossel'])){
            
            if ($_POST['RHS_Carrossel'] == 1)
                $current = get_post_meta($post_id, "_home", true);
                if (!$current) $current = 100;
                Carrossel::move_post_order($post_id, $current, $_POST['RHS_Carrossel_order']);
            
                
        } else {
            delete_post_meta($post_id, '_home');
        }
    }

    
}


CarrosselMetabox::init();
