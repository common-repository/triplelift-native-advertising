<?php

/**
	Triplelift_np_admin_register is the class that provides the base hooks for the admin functionality
*/
class Triplelift_np_admin_register {

    function __construct($invalid_version = false) {
        if (!$invalid_version) {
            add_action( 'admin_menu', array('triplelift_np_admin_register', 'register_options_page' ));
            add_filter('plugin_action_links', array('triplelift_np_admin_register', 'link_hook'), 10, 2); // digits = priority, num args
            
	 	    wp_register_style( 'triplelift_np_admin_style', plugins_url('stylesheet.css', __FILE__) );
        } else {
            add_action( 'admin_menu', array('triplelift_np_admin_register', 'register_invalid_php_version_page' ));
        }
    }	

    function link_hook($links, $file) {
        $link = "<a href='options-general.php?page=triplelift_np_admin'>" . __("Settings") . "</a>";
        array_unshift($links, $link);
        
        return $links;
    }

    function register_options_page() {
        add_options_page( 'TripleLift Native Advertising', 'TripleLift Native Advertising', 'manage_options', 'triplelift_np_admin', array('triplelift_np_admin_register', 'initialize_option_screen' ));
    }

    function register_invalid_php_version_page() {
        add_options_page( 'TripleLift Native Advertising', 'TripleLift Native Advertising', 'manage_options', 'triplelift_np_admin', array('triplelift_np_admin_register', 'invalid_php_version' ));
    }


    function invalid_php_version() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        wp_die( __( 'This plugin requires PHP 5.3 or above. You are using '.phpversion() ) );
    }

    function initialize_option_screen() {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

		wp_register_style( 'triplelift_np_admin_stylesheet', plugins_url('/../css/triplelift_np_admin.css', __FILE__ ) );
		wp_enqueue_style( 'triplelift_np_admin_stylesheet' );

        $global_includes = array(
            'js/common.php', 
        );

        foreach ($global_includes as $curr) {
            include(dirname(__FILE__).'/../'.$curr);
        }

        $auth = new Triplelift_np_admin_auth();
        if ($auth->logged_in) {
            $router = new Triplelift_np_admin_router($auth->options_object);
            $router->route();
        }
        $auth->end();
    }

	function install_plugin() {
		$plugin_data = get_plugin_data( dirname(__FILE__).'/../triplelift.php' );
        $theme = wp_get_theme();
		$api = new Triplelift_np_admin_api();
		$out = $api->do_post("/native_advertising/wp/new_install", array(
			'theme_name' 	=> $theme->Name,
			'host' 			=> get_bloginfo('url'),
			'admin_email' 	=> get_bloginfo('admin_email'),
			'wp_version' 	=> get_bloginfo('version'),
			'plugin_version' => $plugin_data['Version'],  	
		));
	}
	
	function uninstall_plugin() {
		@file_get_contents(TRIPLELIFT_NP_API_URL.'open/wordpress/action?url='.get_bloginfo('url').'&action=uninstall&value='); 
	}	
}
