<?php  
/* 
Plugin Name: TripleLift Native Advertising 
Plugin URI: http://www.triplelift.com/ 
Version: 1.6.6
Author: Triple Lift, Inc. 
Description: TripleLift enables integrated native advertising that fits beautifully without the layout of your site
*/  

define('PRODUCTION_DISTRIBUTION', true);

if(version_compare(PHP_VERSION, '5.3') >= 0) {

    define('TRIPLELIFT_NP_BASE_FILE', 'triplelift/triplelift.php');
    define('TRIPLELIFT_NP_BASE_URL', 'options-general.php?page=triplelift_np_admin');
    define('TRIPLELIFT_NP_OPTIONS_OBJECT_FIELD', 'triplelift_np_data');

    if (PRODUCTION_DISTRIBUTION) {
        define('TRIPLELIFT_NP_API_URL', 'http://api.triplelift.com/');
	    define('TRIPLELIFT_NP_CONSOLE_URL', 'http://console.triplelift.com/');
	    define('TRIPLELIFT_NP_IB', 'http://ib.3lift.com/');
	    define('TRIPLELIFT_NP_WP_SETTINGS_URL', 'http://mgmt.triplelift.net/wp_settings/');
    } else {
        define('TRIPLELIFT_NP_API_URL', 'http://sand-api.triplelift.net/');
	    define('TRIPLELIFT_NP_CONSOLE_URL', 'http://sand-console.triplelift.net/');
	    define('TRIPLELIFT_NP_IB', 'http://sand-ib.3lift.com/');
	    define('TRIPLELIFT_NP_WP_SETTINGS_URL', 'http://mgmt-sand.triplelift.net/wp_settings/');
    }

    $libraries = array(
        'api', 
        'injection', 
        'auth',
        'router',
        'tag_manager',
        'admin_register'
    );

	foreach ($libraries as $curr_library) {
        include (dirname(__FILE__).'/library/'.$curr_library.'.php');
    }

    $triplelift_np_admin_register = new Triplelift_np_admin_register();
    $triplelift_np_injection_register = new Triplelift_np_injection();


    register_activation_hook( __FILE__, array( 'Triplelift_np_admin_register', 'install_plugin' ) );
    register_deactivation_hook( __FILE__, array( 'Triplelift_np_admin_register', 'uninstall_plugin' ) );
} else {

	$libraries = array(
		'admin_register'
	);

	foreach ($libraries as $curr_library) {
        include (dirname(__FILE__).'/library/'.$curr_library.'.php');
    }


    $triplelift_np_admin_register = new Triplelift_np_admin_register(true);
}
