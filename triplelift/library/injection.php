<?php

class Triplelift_np_injection {

	public $options_field = TRIPLELIFT_NP_OPTIONS_OBJECT_FIELD, $options_object, $tags;
    public $post_count = 0, $eligible_tags;

	public $debug = false, $global_debug = false, $settings_debug;
	public $debug_output = array();
	private $title_count = 0, $excerpt_count = 0, $content_count = 0, $the_post_count = 0;
	private $debug_title_count = 0, $debug_excerpt_count = 0, $debug_content_count = 0, $debug_the_post_count = 0;

	// array of the wordpress page types
    public $wp_fields =  array(
        'is_home',
        'is_front_page',
        'is_404',
        'is_archive',
        'is_author',
        'is_category',
        'is_comments_popup',
        'is_search',
        'is_singular',
        'is_tag',
        'is_time',
        'is_year',
    );


    function __construct() {
    	// load the plugin options
        $this->options_object = get_option($this->options_field);
		$this->tags = array();
		// either load the non-deleted tags that have been saved in the plugin into a tags array
		// or load whatever was forced in the querystring

		// make sure the user passed in the correct dongle in the QS (don't expose debug generally)
		if (
			isset($_GET['tripleliftTagDebug']) ||
			isset($_GET['tripleliftDebug']) ||
			isset($_GET['tripleliftGlobalDebug']) 
		) {
			$dongle_validated = false;
			if (isset($_GET['dongle'])) {
				$validate_dongle = @file_get_contents(TRIPLELIFT_NP_API_URL.'open/wordpress/validate_debug_dongle?dongle='.$_GET['dongle']);
				if ($validate_dongle) {
					$decoded = json_decode($validate_dongle);	
					if (isset($decoded->valid_dongle) && $decoded->valid_dongle) {
						$dongle_validated = true;
					}
				} 
			} 
			
			if (!$dongle_validated) {
				$_GET['tripleliftTagDebug'] = false;
				$_GET['tripleliftDebug'] = false;
				$_GET['tripleliftGlobalDebug'] = false;
			}
		}

		// if we're in tag debug, take the appropriate vals from the querystring (or use their defaults to avoid shenanigans)
        if (isset($_GET['tripleliftTagDebug']) && $_GET['tripleliftTagDebug']) {
	        $curr_tag = array();
	        $curr_tag['active'] = 1;
	        $curr_tag['offset'] = $this->get_querystring_or_default('offset', 'n/a');
	        $curr_tag['interval'] = $this->get_querystring_or_default('interval', 3);
	        $curr_tag['append_prepend'] = $this->get_querystring_or_default('append_prepend', true);
	        $curr_tag['wp_page_type_include'] = array();
	        $curr_tag['wp_page_type_exclude'] = array();
	        foreach ($this->wp_fields as $curr_field) {
		    	$curr_tag['wp_page_type_include'][$curr_field] = $this->get_querystring_or_default('include_'.$curr_field, true);
		    	$curr_tag['wp_page_type_exclude'][$curr_field] = $this->get_querystring_or_default('exclude_'.$curr_field, true);
	        }
	        $curr_tag['hook'] = $this->get_querystring_or_default('hook_type', 'excerpt');
	        $curr_tag['include_path'] = $this->get_querystring_or_default('include_path', false) ? explode(',',$this->get_querystring_or_default('include_path', array())) : array();
	        $curr_tag['exclude_path'] = $this->get_querystring_or_default('exclude_path', false) ? explode(',',$this->get_querystring_or_default('exclude_path', array())) : array();
	        $curr_tag['script'] = addslashes(htmlentities('<script src="http://ib.3lift.com/ttj?inv_code='.$this->get_querystring_or_default('script', '').'"></script>' ));
	        $this->tags[] = $curr_tag;
	        
        }
        // otherwise load in the saved tags
        elseif (is_array($this->options_object['tags'])) {
    		foreach ($this->options_object['tags'] as $curr_elt) {
	    		if (!isset($curr_elt['deleted']) || !$curr_elt['deleted']) {
		    		$this->tags[] = $curr_elt;
			    }
    		}
        }
        // if we are in tripleliftDebug, set the debug_output object to have the relevant parametersea
		if ((isset($_GET['tripleliftDebug']) && $_GET['tripleliftDebug']) || (isset($_GET['tripleliftTagDebug']) && $_GET['tripleliftTagDebug'])) {
			$this->debug = true;
			$this->debug_output['options_object'] = $this->options_object;
			$this->debug_output['time_started'] = date("Y-m-d H:i:s");
			$this->debug_output['theme'] = get_current_theme();
			$this->debug_output['blog_title'] = get_bloginfo('name');
			$this->debug_output['url'] = get_bloginfo('url');
			$this->debug_output['charset'] = get_bloginfo('charset');
			$this->debug_output['wpversion'] = get_bloginfo('version');
			$this->debug_output['all_tags'] = $this->tags;
			$this->debug_output['tag_debug_log'] = array();
			$this->debug_output['post_debug_log'] = array();
			$this->debug_output['path'] = parse_url(get_bloginfo( 'url' ), PHP_URL_PATH);
			add_action( 'wp_footer' , array('triplelift_np_injection', 'debug_output' ) );
		}
		// if we're doing the global debug, set up some styles for the hover stuff
		if (isset($_GET['tripleliftGlobalDebug']) && $_GET['tripleliftGlobalDebug'] ) {
			$this->global_debug = true;
			print '<style>	
			a.tl-global-debug-tooltip {outline:none; }
			a.tl-global-debug-tooltip strong {line-height:30px;}
			a.tl-global-debug-tooltip:hover {text-decoration:none;} 
			a.tl-global-debug-tooltip span {
			    z-index:10;display:none; padding:14px 20px;
			    margin-top:-30px; margin-left:28px;
			    width:240px; line-height:16px;
			}
			a.tl-global-debug-tooltip:hover span{
			    display:inline; position:absolute; color:#111;
			    border:1px solid #DCA; background:#fffAF0;}
			.callout {z-index:20;position:absolute;top:30px;border:0;left:-12px;}
			    
			/*CSS3 extras*/
			a.tl-global-debug-tooltip span
			{
			    border-radius:4px;
			    -moz-border-radius: 4px;
			    -webkit-border-radius: 4px;
			        
			    -moz-box-shadow: 5px 5px 8px #CCC;
			    -webkit-box-shadow: 5px 5px 8px #CCC;
			    box-shadow: 5px 5px 8px #CCC;
			}
			</style>';
		}
        $this->find_eligible_tags();
		add_action ( 'init' , array('triplelift_np_injection', 'inject_init' ) );

    }	

	// creates an array of the tags that are eligible to be served in this particular page
	// this array is iterated through in the title, excerpt and content hooks 
    function find_eligible_tags() {
        $this->eligible_tags = array();
        $path = parse_url(get_bloginfo( 'url' ), PHP_URL_PATH);

	    if (is_array($this->tags)) {	
            foreach ($this->tags as $curr_tag) {
                $ineligible = false;
                $path_match = false;
	    		if ($this->debug) {
		    		$curr_tag_debug = array();
				    $curr_tag_debug['is_admin'] = is_admin();
    				$curr_tag_debug['active'] = $curr_tag['active'];
	    			$curr_tag_debug['tag'] = $curr_tag;
				
		    	}
		
                if ($curr_tag['active'])  {
                	if ($this->debug) {$curr_tag_debug['branch_1'] = true;}
                    if (!$ineligible && !$to_add) {
                    	
                    	if ($this->debug) {$curr_tag_debug['branch_2'] = true;}
                        if (is_array($curr_tag['exclude_path']) && count($curr_tag['exclude_path'])) {
                    	
                        	if ($this->debug) {$curr_tag_debug['branch_3'] = true;}
                            if (is_array($curr_tag['exclude_path'])) {
                                foreach ($curr_tag['exclude_path'] as $curr_path) {
                                    if (strlen($curr_path) && strpos($path,$curr_path) !== false) {
                                            
                                        if ($this->debug) {$curr_tag_debug['branch_4'] = true;}
                                        $ineligible = true;
                                        break;
                                    }
                                }
                            }
                    }
                    if (is_array($curr_tag['include_path']) && count($curr_tag['include_path'])) {
                    		
                    	if ($this->debug) {$curr_tag_debug['branch_5'] = true;}
                        foreach ($curr_tag['include_path'] as $curr_path) {
                            if (strlen($curr_path) && strpos($path,$curr_path) !== false) {
                            		
                            	if ($this->debug) {$curr_tag_debug['branch_6'] = true;}
                                $path_match = true;
                                break;
                            }
                        }
                    }
                }

				if ($this->debug) {$curr_tag_debug['post_ineligible'] = $ineligible;}	
                if (!$ineligible) {
                	$curr_tag_debug['eligible_tag'] = true;
                	$this->eligible_tags[] = array(
	                	'script' => $curr_tag['script'], 
	                	'hook' => $curr_tag['hook'], 
	                	'path_match' => $path_match, 
	                	'interval' => $curr_tag['interval'], 
	                	'offset' => (isset($curr_tag['offset']) ? $curr_tag['offset'] : 'n/a'), 
	                	'wp_page_type_exclude' => $curr_tag['wp_page_type_exclude'], 
	                	'wp_page_type_include' => $curr_tag['wp_page_type_include'],
	                );
				}
            }
           	if ($this->debug) {
           		array_push($this->debug_output['tag_debug_log'], $curr_tag_debug);
       			$this->debug_output['eligible_tags'] = $this->eligible_tags;
			}
			}
        }
    }

	function debug_output() {
		global $triplelift_np_injection_register;
		print '<script>';
		print 'console.groupCollapsed("Global Details");';
		if (is_array($triplelift_np_injection_register->debug_output)) {
			foreach ($triplelift_np_injection_register->debug_output as $debug_key => $debug_val) {
				switch ($debug_key) {
					case 'all_tags':
					case 'options_object':
					case 'tag_debug_log':
					case 'post_debug_log':
					case 'eligible_tags':
					break;
					default:
						if (is_array($debug_val)) 
							$triplelift_np_injection_register->debug_output_array($debug_key, $debug_val);	
						else 
							print 'console.log("'.$debug_key.': '.str_replace(array("\r", "\n"), '', addslashes(htmlspecialchars($debug_val))).'");
							';	
					break;
				}
			}			
		}
		print 'console.groupEnd();';
		if (is_array($triplelift_np_injection_register->debug_output['tag_debug_log'])) {			
			$triplelift_np_injection_register->debug_tag_output_array('Tag Debug Log', $triplelift_np_injection_register->debug_output['tag_debug_log']);	
		}
		if (is_array($triplelift_np_injection_register->debug_output['post_debug_log'])) {
			$triplelift_np_injection_register->debug_post_output_array('Post Debug Log', $triplelift_np_injection_register->debug_output['post_debug_log']);	
		}
		print '</script>';
	}

	function debug_post_output_array($key, $val) {
		global $triplelift_np_injection_register;
		
		if (isset($val['hook_type'])) {
			$curr_count = 0;
			if ($hook_type == 'title') {				
				$curr_count = $triplelift_np_injection_register->debug_title_count++;
			} elseif ($hook_type == 'excerpt') {
				$curr_count = $triplelift_np_injection_register->debug_excerpt_count++;				
			} elseif ($hook_type == 'post') {
				$curr_count = $triplelift_np_injection_register->debug_the_post_count++;				
			} else {
				$curr_count = $triplelift_np_injection_register->debug_content_count++;				
			}
			
			$curr_content = '';
			if (isset($val['content'])) {
				if (is_array($val['content'])) {
					$triplelift_np_injection_register->debug_tag_output_array('Content', $val['content']);	
				} elseif (is_object($val['content'])) {
					$triplelift_np_injection_register->debug_tag_output_array('Content', (array) $val['content']);	
				} else {
					$curr_content = str_replace(array("\r", "\n"), '', addslashes(substr($val['content'], 0, 50)));
					if (strlen($val['content']) > 50) {
						$curr_content .= ' ...';
					}
					
				}
			}
			$injected = '';
			if (isset($val['eligible_tag_debug_log']) && is_array($val['eligible_tag_debug_log'])) {
				foreach ($val['eligible_tag_debug_log'] as $curr_debug) {
					if (isset($curr_debug['tag']['injected']) && $curr_debug['tag']['inject']) {
						$injected = ' --INJECTED-- ';
					}
				}
			}
			print 'console.groupCollapsed("'.$key.$injected.', ('.$val['hook_type'].' '.$curr_count.') '.$curr_content.'");';
		} else
			print 'console.groupCollapsed("'.$key.'");
		';

		foreach ($val as $sub_key => $sub_val) {
			if (is_array($sub_val)) 
				$triplelift_np_injection_register->debug_post_output_array($sub_key, $sub_val);
			elseif (is_object($sub_val))
				$triplelift_np_injection_register->debug_post_output_array($sub_key, (array) $sub_val);
			else 
				print 'console.log("'.$sub_key.': '.str_replace(array("\r", "\n"), '', addslashes(htmlspecialchars($sub_val))).'");
				';	
		}						
		print 'console.groupEnd();
		';
	}

	function debug_tag_output_array($key, $val) {
		global $triplelift_np_injection_register;
		if (isset($val['tag']['script'])) {

			print 'console.groupCollapsed("'.$triplelift_np_injection_register->extract_inv_code($val['tag']['script']).'");';
		} else
			print 'console.groupCollapsed("'.$key.'");
		';
		foreach ($val as $sub_key => $sub_val) {
			if (is_array($sub_val)) 
				$triplelift_np_injection_register->debug_tag_output_array($sub_key, $sub_val);
			else 
				print 'console.log("'.$sub_key.': '.str_replace(array("\r", "\n"), '', addslashes(htmlspecialchars($sub_val))).'");
				';	
		}						
		print 'console.groupEnd();
		';
	}



	function debug_output_array($key, $val, $open = false) {
		global $triplelift_np_injection_register;
		if ($open)
			print 'console.group("'.$key.'");';
		else
			print 'console.groupCollapsed("'.$key.'");
		';
		foreach ($val as $sub_key => $sub_val) {
			if (is_array($sub_val)) 
				$triplelift_np_injection_register->debug_output_array($sub_key, $sub_val);
			else 
				print 'console.log("'.$sub_key.': '.str_replace(array("\r", "\n"), '', addslashes(htmlspecialchars($sub_val))).'");
				';	
		}						
		print 'console.groupEnd();
		';
	}

	function extract_inv_code($input) {
		$inv_code_start = strpos($input, 'inv_code')+9;
		$inv_code_end_amp = strpos($input, '&', $inv_code_start);
		$inv_code_end_slash = strpos($input, '\\', $inv_code_start);
	
		if ($inv_code_end_slash && $inv_code_end_slash < $inv_code_end_amp) {
			$inv_code_end = $inv_code_end_slash;
		} else {
			$inv_code_end = $inv_code_end_amp;
		}

        return substr($input, $inv_code_start, $inv_code_end - $inv_code_start);
	}

    function inject_native_ad($content, $hook_type) {
        // assumes php 5.3+        
        global $triplelift_np_injection_register;
        $injection =& $triplelift_np_injection_register;
    	$curr_count = 0;
    	
		if ($hook_type == 'title') {				
			$curr_count = $triplelift_np_injection_register->title_count++;
		} elseif ($hook_type == 'excerpt') {
			$curr_count = $triplelift_np_injection_register->excerpt_count++;				
		} elseif ($hook_type == 'post') {
			$curr_count = $triplelift_np_injection_register->the_post_count++;				
		} else {
			$curr_count = $triplelift_np_injection_register->content_count++;				
		}

        if ($injection->global_debug) {
	        $img = '<img src="http://img-4.3lift.com/?url=http%3A%2F%2Fimages.adpinr.com%2F2947107.png" width=20 height=20 style="width:20px;height:20px">';
			
	        $prepend = '<a href="#" class="tl-global-debug-tooltip">'.$img.'
	        	<span>
		        	'.$hook_type.' - '.$curr_count.' (prepend)
				</span>
		    </a>';

			$append = '<a href="#" class="tl-global-debug-tooltip">'.$img.'
	        	<span>
		        	'.$hook_type.' - '.$curr_count.' (append)
				</span>
		    </a>';

	        if ($hook_type == 'post') {
		        print $prepend;
	        } else {
				return $prepend . $content . $append;		        
	        }

        }
		if ($injection->debug) {
			$curr_post_debug = array();
			$curr_post_debug['content'] = $content;
			$curr_post_debug['hook_type'] = $hook_type;
			$curr_post_debug['page_post_type'] = array(
				'is_home' => is_home(),
				'is_front_page' => is_front_page(),
				'is_404' => is_404(),
				'is_archive' => is_archive(),
				'is_author' => is_author(),
				'is_category' => is_category(),
				'is_comments_popup' => is_comments_popup(),
				'is_search' => is_search(),
				'is_singular' => is_singular(),
				'is_tag' => is_tag(),
				'is_time' => is_time(),
				'is_year' => is_year()
			);
			$curr_post_debug['eligible_tag_debug_log'] = array();
			$curr_eligible_tag = array();
		}
        if (!is_array($injection->eligible_tags)) {
            $injection->eligible_tags = array();
        }
        foreach ($injection->eligible_tags as &$curr_tag_elt) {
        	$curr_tag = $curr_tag_elt;
            $include_match = false;
            $ineligible = false;
			if ($injection->debug) {
				$curr_eligible_tag = array();
				$curr_eligible_tag['tag'] = $curr_tag;	
			}
            if ($curr_tag['hook'] == $hook_type) {
            	if ($curr_tag['interval'] == 'once' && isset($curr_tag_elt['injected']) && $curr_tag_elt['injected']) {
            		$curr_eligible_tag['previously-injected-interval-once'] = true;
            		array_push($curr_post_debug['eligible_tag_debug_log'], $curr_eligible_tag);
            		array_push($injection->debug_output['post_debug_log'], $curr_post_debug);
	            	return $content;	
            	}
            	if ($injection->debug) {$curr_eligible_tag['branch_1'] = true;}
                if (!is_array($injection->wp_fields)) {
                    $injection->wp_fields = array();
                }
                foreach ($injection->wp_fields as $curr_field) {
                	if ($injection->debug) {$curr_eligible_tag[$curr_field] = $curr_field();}
                    if ($curr_tag['wp_page_type_exclude'][$curr_field] && $curr_field()) {
                    	
						if ($injection->debug) {$curr_eligible_tag['branch_2'] = true;}
                        $ineligible = true;
                        break;
                    }
                    if ($curr_tag['wp_page_type_include'][$curr_field] && $curr_field()) {
                    	
						if ($injection->debug) {$curr_eligible_tag['branch_3'] = true;}
                        $include_match = true;
                        break;
                    }
                }

                if (!$ineligible && ($include_match || $curr_tag['path_match'])) {
             	    if ($curr_tag['interval'] == 'once' && isset($curr_tag_elt['injected']) && $curr_tag_elt['injected']) {
                		$curr_eligible_tag['previously-injected-interval-once'] = true;
                		array_push($curr_post_debug['eligible_tag_debug_log'], $curr_eligible_tag);
                		array_push($injection->debug_output['post_debug_log'], $curr_post_debug);
	                	return $content;	
            	    }
               	
					if ($injection->debug) {$curr_eligible_tag['branch_4'] = true;}
                    // we got a match
                    // are we doing offset stuff
                    if (isset($curr_tag['offset']) && $curr_tag['offset'] != 'n/a') {
                    	
						if ($injection->debug) {$curr_eligible_tag['branch_5'] = true;}
						if ($curr_count <= $curr_tag['offset']) {
							
							if ($injection->debug) {$curr_eligible_tag['branch_6'] = true;}
							if ($curr_count == $curr_tag['offset']) {
								
								if ($injection->debug) {$curr_eligible_tag['branch_7'] = true;} 

								if ($injection->debug) {
		                        	$curr_tag_elt['injected'] = true;
		                        	$curr_eligible_tag['injected'] = true;
									array_push($curr_post_debug['eligible_tag_debug_log'], $curr_eligible_tag);
									array_push($injection->debug_output['post_debug_log'], $curr_post_debug);
								}


                                if (isset($curr_tag['append_prepend'])) {
                                	if ($hook_type == 'post') {
										print html_entity_decode(stripslashes($curr_tag['script']));                       	
                                	} else {
	                                	return (
	                                        $curr_tag['append_prepend'] ?
		                                        $content.html_entity_decode(stripslashes($curr_tag['script'])) :
		                                        html_entity_decode(stripslashes($curr_tag['script'])).$content
	                                   );	
                                	}
                                    
                                } else {
                                	if ($hook_type == 'post') {
										print html_entity_decode(stripslashes($curr_tag['script']));                       	
                                	} else {
		                                return $content.html_entity_decode(stripslashes($curr_tag['script']));	                                	
                                	} 
                                }


		                    }	
						} else {
							
							if ($injection->debug) {$curr_eligible_tag['branch_8'] = true;}
		                    if (
		                    	($curr_tag['interval'] != 'once' && ($curr_count - $curr_tag['offset']) % $curr_tag['interval'] == 0 && $curr_count > 0) ||
								($curr_tag['interval'] == 'once' &&  ($curr_count - $curr_tag['offset']) == 0 && $curr_count > 0)
							)
		                      {
		                    	
								if ($injection->debug) {$curr_eligible_tag['branch_9'] = true;} 
		                        $curr_count++;
		                        if ($injection->debug) {
		                        	$curr_tag_elt['injected'] = true;
		                        	$curr_eligible_tag['injected'] = true;
									array_push($curr_post_debug['eligible_tag_debug_log'], $curr_eligible_tag);
									array_push($injection->debug_output['post_debug_log'], $curr_post_debug);
								}
                                if (isset($curr_tag['append_prepend'])) {
                                	if ($hook_type == 'post') {
	                                	print html_entity_decode(stripslashes($curr_tag['script']));                       	
                                	} else {
	                                    return (
	                                        $curr_tag['append_prepend'] ?
		                                        $content.html_entity_decode(stripslashes($curr_tag['script'])) :
		                                        html_entity_decode(stripslashes($curr_tag['script'])).$content
	                                   );
	                                	
                                	}
                                } else {
                                	if ($hook_type == 'post') {
	                                	print html_entity_decode(stripslashes($curr_tag['script']));                       	
                                	} else {
		                                return $content.html_entity_decode(stripslashes($curr_tag['script']));	
                                	}
                                
                                }

		                    }
							
						}
                    } else {
                    	
						if ($injection->debug) {$curr_eligible_tag['branch_10'] = true;}
	                    if (
	                    	($curr_tag['interval'] != 'once' && $curr_count % $curr_tag['interval'] == 0 && $curr_count > 0) || 
	                    	($curr_tag['interval'] == 'once' && ($curr_count - $curr_tag['offset']) == 0 && $curr_count > 0)	
							) {
	                    	
							if ($injection->debug) {$curr_eligible_tag['branch_11'] = true;} 

	                        if ($injection->debug) {
	                        	$curr_tag_elt['injected'] = true;
	                        	$curr_eligible_tag['injected'] = true;
								array_push($curr_post_debug['eligible_tag_debug_log'], $curr_eligible_tag);
								array_push($injection->debug_output['post_debug_log'], $curr_post_debug);
							}
                            if (isset($curr_tag['append_prepend'])) {
                            	if ($hook_type == 'post') {
                                	print html_entity_decode(stripslashes($curr_tag['script']));                       	
                            	} else {
	                            	return (
		                                $curr_tag['append_prepend'] ?
		                                    $content.html_entity_decode(stripslashes($curr_tag['script'])) :
		                                    html_entity_decode(stripslashes($curr_tag['script'])).$content
		                           );	
                            	}
                                
                            } else {
                            	if ($hook_type == 'post') {
                                	print html_entity_decode(stripslashes($curr_tag['script']));                       	
                            	} else {
		                            return $content.html_entity_decode(stripslashes($curr_tag['script']));
		                        }
                            }
	                    }
                    }
                    
                    break;
                }

            }
			if ($injection->debug) {
				array_push($curr_post_debug['eligible_tag_debug_log'], $curr_eligible_tag);
			}
        }
        if ($injection->debug) {
    		array_push($injection->debug_output['post_debug_log'], $curr_post_debug);
        }
        $curr_tag_elt['injected'] = false;
        return $content;
    }
    
    private function get_querystring_or_default($key, $default) {
	    if (isset($_GET[$key])) {
		    return $_GET[$key];
	    } else {
		    return $default;
	    }
    }

    function inject_native_ad_content($content) {
        $hook_type = 'content';
        return Triplelift_np_injection::inject_native_ad($content, $hook_type); 
    }

    function inject_native_ad_excerpt($content) {
        $hook_type = 'excerpt';
        return Triplelift_np_injection::inject_native_ad($content, $hook_type); 
    }

	function inject_native_ad_title($content) {
        $hook_type = 'title';
        return Triplelift_np_injection::inject_native_ad($content, $hook_type); 
    }

	function inject_native_ad_post($content) {
        $hook_type = 'post';
        return Triplelift_np_injection::inject_native_ad($content, $hook_type); 
    }

    function inject_init() {
        add_action("the_content" , array('triplelift_np_injection', 'inject_native_ad_content' ));
        add_action("the_excerpt" , array('triplelift_np_injection', 'inject_native_ad_excerpt' ));
        add_action("the_title"   , array('triplelift_np_injection', 'inject_native_ad_title' ));
        add_action("the_post"    , array('triplelift_np_injection', 'inject_native_ad_post' ));
    }

}
