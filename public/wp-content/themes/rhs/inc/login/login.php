<?php

/*
*
* Esta Class implementa as funções necessárias para o Login e uso das reCaptcha.
* Pega a key setada no Painel do Admin (Wordpress).
* Com a Função display_recuperar_captcha() mostra na tela o reCaptcha.
*
*/
class RHSLogin extends RHSMessage {

    private static $instance;
    
    const META_KEY_LAST_LOGIN = '_last_login';

    function __construct() {

        if ( empty ( self::$instance ) ) {
            add_filter( "login_url", array( &$this, "login_url" ), 10, 3 );
            add_filter( "login_redirect", array( &$this, "login_redirect" ), 10, 3 );
            add_filter( 'wp_login_errors', array( &$this, 'check_errors' ), 10, 2 );
            add_action( 'wp_login', array( &$this, 'save_last_login'));
        }

        self::$instance = true;
    }

    static function login_url( $login_url, $redirect, $force_reauth ) {
        $login_page = home_url(RHSRewriteRules::LOGIN_URL);
        $login_url  = add_query_arg( 'redirect_to', urlencode($redirect), $login_page );

        return $login_url;
    }

    function login_redirect( $redirect_to, $requested_redirect_to, $user ) {
        if ( empty( $redirect_to ) ) {
            //TODO verificar role do usuário para enviar para a página apropriada
            $redirect_to =  esc_url(home_url());
        }

        return $redirect_to;
    }

    function login_errors( $errors, $redirect_to ) {

        $_SESSION['login_errors'] = '';
    }
    function check_errors( $errors, $redirect_to ) {

        if ( $errors instanceof WP_Error && ! empty( $errors->errors ) ) {

            if ( $errors->errors ) {

                $this->clear_messages();

                foreach ($errors->get_error_messages() as $error){
                    $this->set_messages($error, false, 'error');
                }
            }

            wp_redirect( home_url(RHSRewriteRules::LOGIN_URL) );
            exit;
        }

        return $errors;
    }
    
    function save_last_login($login) {
        global $user_ID;
        $user = get_user_by('login', $login);
        update_user_meta($user->ID, self::META_KEY_LAST_LOGIN, current_time('mysql'));
    }

}

global $RHSLogin;
$RHSLogin = new RHSLogin();
