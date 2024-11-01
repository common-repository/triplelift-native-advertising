<?php


class Triplelift_np_admin_auth {

    public $api_url = 'http://api.triplelift.com/';
    
    public $options_field = TRIPLELIFT_NP_OPTIONS_OBJECT_FIELD; 
    public $auth_token_field = 'triplelift_np_auth_token';
    public $username_field = 'triplelift_np_login_username';
    public $password_field = 'triplelift_np_login_password';
    public $logout_field = 'triplelift_np_logout';
    public $member_id, $auth_token;
    public $error_message = false;
    public $api;
    public $options_object;
    public $logged_in = false;

    private $end_function = false;

    function end() {
        if ($this->end_function) {
            $this->{$this->end_function}();
        }
    }

    function logged_in_options() {
        $this->logged_in = true;
        include(dirname(__FILE__).'/../html/logged_in.php');
        $this->end_function = 'logged_in_options_end';
    }

    function logged_in_options_end() {
        include(dirname(__FILE__).'/../html/logged_in_end.php');
    }

    function logged_out_options() {
        include(dirname(__FILE__).'/../html/logged_out.php');
    }

    function __construct() {
        $this->options_object = get_option($this->options_field);
        if (!$this->options_object) {$this->options_object = array();}
        $this->auth_token = isset($this->options_object['auth_token']) ? $this->options_object['auth_token'] : false;
        $this->api = new triplelift_np_admin_api();

        // if the user is logging out
        if (isset($_POST[$this->logout_field]) && $_POST[$this->logout_field] ) {	
            $this->options_object['auth_token'] = false;
            update_option( $this->options_field, $this->options_object );
            $this->logged_out_options();
        // if the user is logged in
        } elseif ($this->auth_token && $this->api->authenticate_token($this->auth_token)) {
            $this->logged_in_options();
        // if they're in the process of logging in
        } elseif (isset($_POST[$this->username_field]) || isset($_POST[$this->password_field])) {
            $auth = $this->api->authenticate_username($_POST[$this->username_field], $_POST[$this->password_field]);	
            if (!$auth) {
                $this->error_message = 'Invalid username / password combination';
                $this->logged_out_options();
            } else {
                $this->options_object['auth_token'] = $auth['token'];
                $this->auth_token = $auth['token'];
				$this->options_object['member_id'] = $auth['member_id'];
                update_option( $this->options_field, $this->options_object );
                $this->logged_in_options();
            }

        // if they're not logged in at all
        } else {	
            $this->logged_out_options();
        }

    }
	
	
}


