<script>

function triplelift_np_admin_new_placement(id, target_div, new_from_theme) {
    triplelift_np_admin_api.get("native_advertising/placement/template?template_type=wordpress&theme_name=<?php print urlencode($this->theme).'&url='.urlencode($this->blog_host); ?>", function(resp) {
        if (resp.status) {
            if (resp.templates.length > 0) {
            	var htmlOut = '<p>';
                if (!new_from_theme) {
                    htmlOut += 'No placements exist for this publisher. You may create one from a template</p><p>';
                }
            	if (resp.templates.length == 1) {
            		htmlOut += '<b>Template found!</b>';
            	} else {
            		htmlOut += '<b>Templates found!</b>';
            	}
            	htmlOut += '<br>&nbsp;<br>Select a template below:<ul class="card-list">';
            	for (var i=0; i<resp.templates.length; i++) {
            		htmlOut += 
            			'<li id="triplelift_np_admin_template_'+i+'">'+
            				'<a href="#" onclick="triplelift_np_admin_create_placement_from_template(\''+id+'\', \''+resp.templates[i].id+'\', \''+target_div+'\');">'+
							'<strong><?php print $this->theme;?></strong>'+
							
            				'<br>&nbsp;<br><span class="card-list-text">Image: '+resp.templates[i].image_width+'x'+resp.templates[i].image_height+'<br>Tag Code: '+resp.templates[i].inv_code_type+'</span>'+
            			'</li>';	
            	}
            	htmlOut += '<ul>';
            	jQuery("#"+target_div).html(htmlOut);
            	
            } else {
                var htmlOut = '<p>';
                htmlOut += 'There are no placements for this publisher. ';
                htmlOut += 'No templates were found for your theme. Please <a href="<?php print TRIPLELIFT_NP_CONSOLE_URL;?>go?publisher_id='+id+'&token=<?php print urlencode($this->options_object['auth_token']);?>&dest=<?php print urlencode('/publisher/create');?>" target="_blank">create a template in the TripleLift console</a> or <a href="mailto:support@triplelift.com" target="_blank"">contact support</a></p>';
            	jQuery("#"+target_div).html(htmlOut);	
            }
        } else {
            unable_to_contact(target_div)
        }

    });

    jQuery("#"+target_div).html('<div class="updated" id="message"><p><strong>Error</strong>: No placement found - please create one in the <a href="<?php print TRIPLELIFT_NP_CONSOLE_URL;?>go?publisher_id='+id+'&token=<?php print urlencode($this->options_object['auth_token']);?>&dest=<?php print urlencode('/publisher/create');?>" target="_blank">TripleLift console</a></p></div>');
}

function triplelift_np_admin_create_placement_from_template(publisher_id, template_id, target_div) {
    var resp1;
    var resp2;
    var resp3;
	jQuery("#"+target_div).html('Creating placement in TripleLift (this may take a few seconds)<br><img src="http://static.adpinr.com/image/2609283.gif">');
	// get the placement details
	triplelift_np_admin_api.get("native_advertising/placement/template?template_type=wordpress&id="+template_id, function(resp1) {
		// get the publisher details
		triplelift_np_admin_api.get("native_advertising/publisher?id="+publisher_id, function(resp2) {
			if (resp1.status && resp2.status) {
				// plug all that into the placement creation
				triplelift_np_admin_api.post(
					"native_advertising/placement", 
					{
						"publisher_id": publisher_id,
					    "image_width": resp1.templates[0].image_width,
					    "image_height": resp1.templates[0].image_height,
					    "template_code": resp1.templates[0].template_code,
					    "inv_code": resp2.publisher.name.toLowerCase().replace(/\s+/g, '')+"_"+resp1.templates[0].inv_code_type,
					    "demand_enabled": "1",
					    "default_code": "",
					}, 
					// now take the newly created placement from appnexus and create a placement in wordpress
					function(resp3) {
						if (resp3.status) {
							window.location = "<?php print TRIPLELIFT_NP_BASE_URL.
								'&'.$this->action_field.'=new_placement_from_theme'.
								'&triplelift_np_admin_script='.urlencode('<script src="'.TRIPLELIFT_NP_IB.'ttj?member_id='.$this->options_object['member_id'].'&inv_code=');?>"+encodeURIComponent(resp3.placement.inv_code)+"<?php print urlencode('"></script>')?>"+
								'&triplelift_np_admin_wp_type_include='+encodeURIComponent(resp1.templates[0].wp_page_type_include)+
								'&triplelift_np_admin_wp_type_exclude='+encodeURIComponent(resp1.templates[0].wp_page_type_exclude)+
								'&triplelift_np_admin_include_path='+encodeURIComponent(resp1.templates[0].include_path)+
								'&triplelift_np_admin_exclude_path='+encodeURIComponent(resp1.templates[0].exclude_path)+
								'&triplelift_np_admin_interval='+encodeURIComponent(resp1.templates[0].post_interval)+
								'&triplelift_np_admin_hook='+encodeURIComponent(resp1.templates[0].hook)
                                

						} else {
							unable_to_contact(target_div);	
						}
					});	
			} else {
				unable_to_contact(target_div);
			}
		});
	})
	
}

</script>
