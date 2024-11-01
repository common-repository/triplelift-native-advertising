<?php

class Triplelift_np_admin_api {
    
    protected $_token;
    
    function __construct() {
    }
    
    function authenticate_username($username, $password, $redirects = 10, $url_override = false) {
        $ch = curl_init();
        if ($url_override) {
	        curl_setopt($ch, CURLOPT_URL, $url_override);	        
        } else {
	        curl_setopt($ch, CURLOPT_URL, TRIPLELIFT_NP_API_URL.'login/');	        
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('username' => $username, 'password' => $password)));
        $result = curl_exec($ch);
        $auth_out = json_decode($result);
        if (isset($auth_out->status) && $auth_out->status) {
            return array('token' => $auth_out->token, 'member_id' => $auth_out->member->id);
        } else {
            return false;
        } 
        
        // deal with various security settings
		if ((!ini_get('open_basedir') && !ini_get('safe_mode')) || $redirects < 1) {
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $redirects > 0);
	        $result = curl_exec($ch);
			$auth_out = json_decode($result);
	        if (isset($auth_out->status) && $auth_out->status) {
	            return array('token' => $auth_out->token, 'member_id' => $auth_out->member->id);
	        } else {
	            return false;
	        } 
	    } else {
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	        curl_setopt($ch, CURLOPT_HEADER, true);
	        curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
	
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code == 301 || $code == 302) {
	            $header_start = strpos($result, "\r\n")+2;
	            $headers = substr($data, $header_start, strpos($data, "\r\n\r\n", $header_start)+2-$header_start);
	            if (!preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches)) {
		            $this->authenticate_username($username, $password, --$redirects);
	            } else {
	            	$this->authenticate_username($username, $password, --$redirects, $matechs[1]);
	            }

            }

	        if (!$redirects)
	            return false;

	        $auth_out = json_decode(substr($result, strpos($data, "\r\n\r\n")+4));
	        if (isset($auth_out->status) && $auth_out->status) {
	            return array('token' => $auth_out->token, 'member_id' => $auth_out->member->id);
	        } else {
	            return false;
	        } 
	    }        
    }

    function authenticate_token($token) {
        $this->_token = $token;
        $out = $this->do_get('member/');			
        if (isset($out->status) && $out->status == true) {
            return true;
        } else {
            return false;
        }
    }

    function do_get($path, $result_to_assoc = false, $redirects = 10) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, TRIPLELIFT_NP_API_URL.$path);
		if (isset($this->_token)) {
	        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Auth-token: '.$this->_token));
		}
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        
        
        // deal with various security settings
		if ((!ini_get('open_basedir') && !ini_get('safe_mode')) || $redirects < 1) {
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $redirects > 0);
	        $result = curl_exec($ch);
			return json_decode($result, $result_to_assoc);
	    } else {
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	        curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
	
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code == 301 || $code == 302) {
	            $header_start = strpos($result, "\r\n")+2;
	            $headers = substr($data, $header_start, strpos($data, "\r\n\r\n", $header_start)+2-$header_start);
	            if (!preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches)) {
		            $this->do_get($path, $result_to_assoc, --$redirects);	            
	            } else {
		            $this->do_get($matches[1], $result_to_assoc, --$redirects);		            
	            }
            }

	        if (!$redirects)
	            return array ('error' => 'Too many redirects');

	        return json_decode(substr($result, strpos($data, "\r\n\r\n")+4));
	    }

    }
    
    function do_post($path, $data_as_array, $result_to_assoc = false, $redirects = 10) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, TRIPLELIFT_NP_API_URL.$path);
		if (isset($this->_token)) {
        	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Auth-token: '.$this->_token));
		}
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_as_array));
        
		// deal with various security settings
		if ((!ini_get('open_basedir') && !ini_get('safe_mode')) || $redirects < 1) {
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $redirects > 0);
	        $result = curl_exec($ch);
			return json_decode($result, $result_to_assoc);
	    } else {
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	        curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
	
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code == 301 || $code == 302) {
	            $header_start = strpos($result, "\r\n")+2;
	            $headers = substr($data, $header_start, strpos($data, "\r\n\r\n", $header_start)+2-$header_start);
	            if (!preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!", $headers, $matches)) {
		            $this->do_post($path, $data_as_array, $result_to_assoc, --$redirects);	            
	            } else {
		            $this->do_post($matches[1], $data_as_array, $result_to_assoc, --$redirects);		            
	            }

            }

	        if (!$redirects)
	            return array ('error' => 'Too many redirects');

	        return json_decode(substr($result, strpos($data, "\r\n\r\n")+4));
	    }
        
                       
    }
    
}
