<?php

/**
	Triplelift_np_admin_tag_manager is the class that provides the admin functionality related to 
		to the creation and maintenance of tags and settings 
*/

class Triplelift_np_admin_tag_manager {

	public $options_object, $options_field;

	private $original_script, $modified_script, $wp_include, $wp_exclude, $include_paths, $exclude_paths, $active;
	public $error = false; 
	public $error_message = '', $updated_tag_settings;
    public $eligible_hooks = array('post', 'content', 'excerpt', 'title');
    public $eligible_intervals = array(1,2,3,4,5,6,7,8,9,10,11,12,'once');
    public $eligible_offsets = array("n/a",1,2,3,4,5);

	public function get_tag_settings_by_script($script) {
		$script = stripslashes($script);
        if (is_array($this->options_object['tags'])) {
            foreach ($this->options_object['tags'] as $curr_tag) {
                if (isset($curr_tag['script'])) {
                    if ($curr_tag['script'] == $script && (!isset($curr_tag['deleted']) || $curr_tag['deleted'] == 0)) {
                        return $curr_tag;
                    }
                }
            }
        }
		return false;
	}

	public function validate_required_fields() {
		$required_fields = array('triplelift_np_admin_original_script', 'triplelift_np_admin_script_html', 'triplelift_np_admin_include_paths', 'triplelift_np_admin_exclude_paths', 'triplelift_np_admin_hook');	
		foreach ($required_fields as $curr_field) {
			if (!isset($_POST[$curr_field])) {
				$this->error = true;
				$this->error_message = 'Invalid submission';
			}
		}
		if (!$this->error) {
			$_POST['triplelift_np_admin_script_html'] = htmlentities($_POST['triplelift_np_admin_script_html']);
			$_POST['triplelift_np_admin_original_script'] = htmlentities($_POST['triplelift_np_admin_original_script']);
		}
	}

	public function validate_post_tag_exists() {
		if (!$this->error) {
			if (!$this->tag_exists($_POST['triplelift_np_admin_original_script'])) {
				$_POST['triplelift_np_admin_original_script'] = stripslashes($_POST['triplelift_np_admin_original_script']);
				
				if (!$this->tag_exists($_POST['triplelift_np_admin_original_script'])) {
					$this->error = true;
					$this->error_message = 'Original script not found';
				}
			}
		}
	}

	public function validate_modified_tag($prev_tag = false) {
		if (!$this->error) {
			$fields = array(
				'is_home' => 0,
				'is_front_page' => 0,
				'is_404' => 0,
				'is_archive' => 0,
				'is_author' => 0,
				'is_category' => 0,
				'is_comments_popup' => 0,
				'is_search' => 0,
				'is_singular' => 0,
				'is_tag' => 0,
				'is_time' => 0,
				'is_year' => 0,
			);
			$this->wp_include = $fields;
			$this->wp_exclude = $fields;

			if (isset($_POST['triplelift_np_admin_wp_include_settings']) && is_array($_POST['triplelift_np_admin_wp_include_settings']) ) {
				foreach ($_POST['triplelift_np_admin_wp_include_settings'] as $curr_field) {
					if (isset($this->wp_include[$curr_field])) {
						$this->wp_include[$curr_field] = 1;
					}
				}
			}
			if (isset($_POST['triplelift_np_admin_wp_exclude_settings']) && is_array($_POST['triplelift_np_admin_wp_exclude_settings']) ) {
				foreach ($_POST['triplelift_np_admin_wp_exclude_settings'] as $curr_field) {
					if (isset($this->wp_exclude[$curr_field])) {
						$this->wp_exclude[$curr_field] = 1;
					}
				}
			}


			$this->original_script = $_POST['triplelift_np_admin_original_script'];
			$this->modified_script = $_POST['triplelift_np_admin_script_html'];
			$this->include_paths = $_POST['triplelift_np_admin_include_paths'];
			$this->exclude_paths = $_POST['triplelift_np_admin_exclude_paths'];
			$this->active = isset($_POST['triplelift_np_admin_active']) ? $_POST['triplelift_np_admin_active'] : false;
			$this->admin_preview = isset($_POST['triplelift_np_admin_admin_preview']) ? $_POST['triplelift_np_admin_admin_preview'] : false;
            $this->hook = $_POST['triplelift_np_admin_hook'];
            $this->interval = $_POST['triplelift_np_admin_interval'];
            $this->offset = $_POST['triplelift_np_admin_offset'];
            $this->append_prepend = isset($_POST['triplelift_np_admin_append_prepend']) ? $_POST['triplelift_np_admin_append_prepend'] : false;
            $this->tl_last_update = time();
            $this->tl_update_timestamp = time();


			if (!strpos($this->modified_script, 'script')) {
				$this->error = true;
				$this->error_message = 'The script you entered was not in a valid format. Please try again.';
			}
            if (!in_array($_POST['triplelift_np_admin_hook'], $this->eligible_hooks)) {
                $this->error = true;
                $this->erorr_message = 'Invalid hook'; 
            }
            if (!in_array($_POST['triplelift_np_admin_interval'], $this->eligible_intervals)) {
                $this->error = true;
                $this->erorr_message = 'Invalid interval'; 
            }
            if (!in_array($_POST['triplelift_np_admin_offset'], $this->eligible_offsets)) {
                $this->error = true;
                $this->erorr_message = 'Invalid offset'; 
            }
		}
	}

	public function delete_tag($script) {
		$script = stripslashes($script);
		foreach ($this->options_object['tags'] as &$curr_tag) {
			if ($curr_tag['script'] == $script && 
				(!isset($curr_tag['deleted']) || !$curr_tag['deleted'])) {

				$curr_tag['deleted'] = 1;
			}
		}
		update_option($this->options_field, $this->options_object);
	}

    public function import_tl_settings($payload, $change_tag) {
        $settings_original = json_decode($payload, true);
        if (isset($settings_original['settings'])) {
	        $settings = $settings_original['settings'];
        } else {
	        $settings = array();
        }
        $modified_tag = false;
        if (isset($settings['timestamp'])) {
            if (!isset($this->options_object['tags']) || !is_array($this->options_object['tags'])) {
                $this->options_object['tags'] = array();
            }
			foreach ($this->options_object['tags'] as &$curr_tag) {
				if ($curr_tag['script'] == $change_tag['script'] && !$curr_tag['deleted']) {
                    $modified_tag = $curr_tag;
                    $modified_keys = array(
                        'wp_page_type_include', 'wp_page_type_exclude', 'include_path', 'exclude_path',
                        'active', 'interval', 'offset','append_prepend', 
                    );
                    $modified_diff_keys = array(
                        'hook_type' => 'hook', 
                        'timestamp' => 'tl_update_timestamp',
                        'timestamp' => 'tl_last_update'
                    );
                    foreach ($modified_keys as $key) {
                        $modified_tag[$key] = $settings[$key];
                    }
                    foreach ($modified_diff_keys as $key => $key2) {
                        $modified_tag[$key2] = $settings[$key];
                    }
                    $curr_tag = $modified_tag;
                }
            }
			update_option($this->options_field, $this->options_object);
        }
        return $modified_tag;
    }

    public function get_curr_tag_from_inv_code($inv_code) {
        if (!isset($this->options_object['tags']) || !is_array($this->options_object['tags'])) {
           $this->options_object['tags'] = array();
        }
        foreach ($this->options_object['tags'] as $curr_tag) {
			if (	isset($curr_tag['active']) &&   
					( (!isset($curr_tag['deleted']) && $curr_tag['deleted'] == 0 ) || !isset($curr_tag['deleted']) ) 
				) {
	    		$inv_code_start = strpos($curr_tag['script'], 'inv_code')+9;
	    		$inv_code_end_amp = strpos($curr_tag['script'], '&', $inv_code_start);
	    		$inv_code_end_slash = strpos($curr_tag['script'], '\\', $inv_code_start);
			
	    		if ($inv_code_end_slash && $inv_code_end_slash < $inv_code_end_amp) {
	    			$inv_code_end = $inv_code_end_slash;
	    		} else {
	    			$inv_code_end = $inv_code_end_amp;
	    		}
		
	            $tag_inv_code = substr($curr_tag['script'], $inv_code_start, $inv_code_end - $inv_code_start);
	            if ($tag_inv_code == $inv_code) {
	                return $curr_tag;
	            }
	        }
        }
        return false;
    }

	public function update_tag() {
		if (!$this->error) {
            if (!isset($this->options_object['tags']) || !is_array($this->options_object['tags'])) {
                $this->options_object['tags'] = array();
            }
			foreach ($this->options_object['tags'] as &$curr_tag) {
				if ($curr_tag['script'] == $_POST['triplelift_np_admin_original_script'] && !$curr_tag['deleted']) {
					$modified_tag = array(
						'script' => $this->modified_script,
						'wp_page_type_include' => $this->wp_include,
						'wp_page_type_exclude' => $this->wp_exclude,
						'include_path' => explode(',',$this->include_paths),
						'exclude_path' => explode(',',$this->exclude_paths),
						'active' => $this->active,
						'interval' => $this->interval,
						'offset' => $this->offset,
                        'admin_preview' => $this->admin_preview,
                        'hook' => $this->hook,
                        'append_prepend' => $this->append_prepend,
                        'tl_last_update' => (isset($this->tl_last_update) ? $this->tl_last_update : 0),
                        'tl_update_timestamp' => (isset($this->tl_last_update) ? $this->tl_last_update : 0),
					);
					$this->updated_tag_settings = $modified_tag;
					$curr_tag = $modified_tag;
				}
			}  		
			update_option($this->options_field, $this->options_object);
		}
	}

	public function tag_exists($script) {
        if (!isset($this->options_object['tags']) || !is_array($this->options_object['tags'])) {
            $this->options_object['tags'] = array();
        }

		foreach ($this->options_object['tags'] as $curr_tag_exists) {  
			if ($curr_tag_exists['script'] == $script && !$curr_tag_exists['deleted']) return true;
		}
		return false;
	}

	public function add_tag_from_theme($script, $wp_page_type_include, $wp_page_type_exclude, $include_path, $exclude_path, $interval, $offset, $hook, $append_prepend = true, $tl_last_update = 0) {
		$wp_page_type_include_processed = array(
			'is_home' => 0,
			'is_front_page' => 0,
			'is_404' => 0,
			'is_archive' => 0,
			'is_author' => 0,
			'is_category' => 0,
			'is_comments_popup' => 0,
			'is_search' => 0,
			'is_singular' => 0,
			'is_tag' => 0,
			'is_time' => 0,
			'is_year' => 0,
		);
		$wp_page_type_exclude_processed = array(
			'is_home' => 0,
			'is_front_page' => 0,
			'is_404' => 0,
			'is_archive' => 0,
			'is_author' => 0,
			'is_category' => 0,
			'is_comments_popup' => 0,
			'is_search' => 0,
			'is_singular' => 0,
			'is_tag' => 0,
			'is_time' => 0,
			'is_year' => 0,
		);
		$wp_include_pieces = explode(',',$wp_page_type_include);
		$wp_exclude_pieces = explode(',',$wp_page_type_exclude);
		foreach ($wp_include_pieces as $curr_piece) {
			if (isset($wp_page_type_include_processed[$curr_piece])) $wp_page_type_include_processed[$curr_piece] = 1; 
		}
		foreach ($wp_exclude_pieces as $curr_piece) {
			if (isset($wp_page_type_exclude_processed[$curr_piece])) $wp_page_type_exclude_processed[$curr_piece] = 1; 
		}
		$script_data = array(
			'script' => $script,
			'wp_page_type_include' => $wp_page_type_include_processed,
			'wp_page_type_exclude' => $wp_page_type_exclude_processed,
			'include_path' => explode(',', $include_path),
			'exclude_path' => explode(',', $exclude_path),
			'interval' => $interval,
			'offset' => $offset,
			'active' => true,
	        'admin_preview' => false,
	        'hook' => $hook,
	        'append_prepend' => $append_prepend,
	        'tl_last_update' => $tl_last_update,
        );	
		$this->options_object['tags'][] = $script_data;
  		update_option($this->options_field, $this->options_object);
		return $script_data;
	}

	public function add_tag($script) {
        $curr_tags = $this->get_tags();
		if (count($curr_tags) == 0) {
			$wp_page_type_include = array(
				'is_home' => 1,
				'is_front_page' => 1,
				'is_404' => 0,
				'is_archive' => 0,
				'is_author' => 0,
				'is_category' => 0,
				'is_comments_popup' => 0,
				'is_search' => 0,
				'is_singular' => 0,
				'is_tag' => 0,
				'is_time' => 0,
				'is_year' => 0,
			);
			$wp_page_type_exclude = array(
				'is_home' => 0,
				'is_front_page' => 0,
				'is_404' => 1,
				'is_archive' => 0,
				'is_author' => 0,
				'is_category' => 0,
				'is_comments_popup' => 1,
				'is_search' => 1,
				'is_singular' => 1,
				'is_tag' => 1,
				'is_time' => 1,
				'is_year' => 1,
			);

		} else {
			$wp_page_type_include = array(
				'is_home' => 0,
				'is_front_page' => 0,
				'is_404' => 0,
				'is_archive' => 0,
				'is_author' => 0,
				'is_category' => 0,
				'is_comments_popup' => 0,
				'is_search' => 0,
				'is_singular' => 0,
				'is_tag' => 0,
				'is_time' => 0,
				'is_year' => 0,
			);
			$wp_page_type_exclude = array(
				'is_home' => 0,
				'is_front_page' => 0,
				'is_404' => 0,
				'is_archive' => 0,
				'is_author' => 0,
				'is_category' => 0,
				'is_comments_popup' => 0,
				'is_search' => 0,
				'is_singular' => 0,
				'is_tag' => 0,
				'is_time' => 0,
				'is_year' => 0,
			);

		}
		$script_data = array(
			'script' => $script,
			'wp_page_type_include' => $wp_page_type_include,
			'wp_page_type_exclude' => $wp_page_type_exclude,
			'include_path' => array(),
			'exclude_path' => array(),
			'interval' => 6,
			'offset' => 4,
			'active' => true,
            'admin_preview' => false,
            'hook' => 'post',
            'append_prepend' => true,
            'tl_last_update' => 0,
		);

		$this->options_object['tags'][] = $script_data;
  		update_option($this->options_field, $this->options_object);
		return $script_data;
	}

	public function field_name_map($field_name) {
		switch ($field_name) {
			case 'is_front_page':		return 'Front Page';
			case 'is_404':				return '404 Error Pages';
			case 'is_archive': 			return 'Archive Pages';
			case 'is_author': 			return 'Author Pages';
			case 'is_category': 		return 'Category Pages';
			case 'is_comments_popup': 	return 'Comments Popup Pages';
			case 'is_search':		 	return 'Search Pages';
			case 'is_singular':		 	return 'Singular Post Pages';
			case 'is_tag':			 	return 'Tag Pages';
			case 'is_time':			 	return 'Time Pages';
			case 'is_year':			 	return 'Year Pages';
			case 'is_home':			 	return 'Home Page';
			case 'is_front_page':		return 'Front Page';
		}
	
	}

	public function render_wp_settings($script_data, $input_name, $include_exclude) {
		if ($include_exclude == 'include') {
			$return_str = '<b>Always include</b> the following types of pages: <br>';
			$data = $script_data['wp_page_type_include'];
		} else {
			$return_str = '<b>Always exclude</b> the following types of pages: <br>';
			$data = $script_data['wp_page_type_exclude'];
		}
		$count = 1;
        if (is_array($data)) {
            foreach ($data as $field => $val) {
                if ($val) $checked = ' checked '; else $checked = ' ';
                $return_str .= '<input class="tl_np_checkbox" name="'.$input_name.'" id="'.$input_name.'" type="checkbox" '.$checked.' value="'.$field.'" data-label="'.$this->field_name_map($field).'"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                if ($count % 4 == 0) $return_str .= '<br>';
                $count++;
            }
        }
		return $return_str;
	}

	public function render_path_settings($script_data, $input_name, $include_exclude) {
		if ($include_exclude == 'include') {
			$return_str = '<b>Otherwise include</b> the following page paths (comma separated, * is a wildcard): <br>';
			$data = $script_data['include_path'];
		} else {
			$return_str = '<b>Otherwise exclude</b> the following page paths (comma separated, * is a wildcard): <br>';
			$data = $script_data['exclude_path'];
		}
			
		$return_str .= '<input type="text" size=50 id="'.$input_name.'" name="'.$input_name.'" value="'.implode(',', $data).'">';
		return $return_str;
	}

	public function render_active($script_data, $input_name) {
		$checked = $script_data['active'] ? ' checked ' : ' ';
		return '<input class="tl_np_checkbox" name="'.$input_name.'" id="'.$input_name.'" type="checkbox" '.$checked.' data-label="Tag currently active">';
	}

	public function render_append_prepend_settings($script_data, $input_name) {
        if (!isset($script_data['append_prepend'])) {
            $script_data['append_prepend'] = true;
        }
		$checked = $script_data['append_prepend'] ? ' checked ' : ' ';
		return '<input class="tl_np_checkbox" name="'.$input_name.'" id="'.$input_name.'" type="checkbox" '.$checked.' data-label="Append tag (uncheck to prepend)">';
	}



	public function render_admin_only_preview($script_data, $input_name) {
		$checked = $script_data['admin_preview'] ? ' checked ' : ' ';
		return 'Test mode - only show to admin users (uncheck to show to all): <br><input name="'.$input_name.'" id="'.$input_name.'" type="checkbox" '.$checked.' style="width:4px;"> Enabled';
	}

	public function render_interval($script_data, $input_name) {
        $return_str = '<b>Interval</b> between posts (ignored on single pages): <select id="'.$input_name.'" name="'.$input_name.'">';
        if (is_array($this->eligible_intervals)) {
            foreach ($this->eligible_intervals as $curr_interval) {
                if ($script_data['interval'] == $curr_interval) $selected = ' selected ';
                else $selected = ' ';
                $return_str .= '<option value="'.$curr_interval.'" '.$selected.'>'.$curr_interval.'</option>';
            }
        }
        return $return_str .= '</select>';
	}

	public function render_offset($script_data, $input_name) {
        $return_str = '<b>Offset</b> for first post: <select id="'.$input_name.'" name="'.$input_name.'">';
        if (!isset($script_data['offset'])) {$script_data['offset'] = 'n/a';}
        if (is_array($this->eligible_offsets)) {
            foreach ($this->eligible_offsets as $curr_offset) {
                if ($script_data['offset'] == $curr_offset) $selected = ' selected ';
                else $selected = ' ';
                $return_str .= '<option value="'.$curr_offset.'" '.$selected.'>'.($curr_offset == "n/a" ? "No offset" : $curr_offset).'</option>';
            }
        }
        return $return_str .= '</select>';
	}


    public function render_tag_hooks($script_data, $input_name) {
        $return_str = 'Hook for tag: <select id="'.$input_name.'" name="'.$input_name.'">';
        if (is_array($this->eligible_hooks)) {
            foreach ($this->eligible_hooks as $curr_hook) {
                if ($script_data['hook'] == $curr_hook) $selected = ' selected ';
                else $selected = ' ';
                $return_str .= '<option value="'.$curr_hook.'" '.$selected.'>'.$curr_hook.'</option>';
            }
        }
        return $return_str .= '</select>';
    }

	public function get_tags() {
		$tags = array();
        if (!isset($this->options_object['tags']) || !is_array($this->options_object['tags'])) {
            $this->options_object['tags'] = array();
        }

		foreach ($this->options_object['tags'] as $curr_elt) {
			if (!isset($curr_elt['deleted']) || !$curr_elt['deleted']) {
				$tags[] = $curr_elt;
			}
		}
		return $tags;
	}

	public function  __construct($options_object, $options_field) {
		$this->options_object = $options_object;
		$this->options_field = $options_field;
	}

}
