<script>
    
function triplelift_np_admin_select_publisher(id, name, target_div, new_from_theme) {
    if (new_from_theme) {
        triplelift_np_admin_new_placement(id, target_div, new_from_theme);
    } else {
        jQuery("#"+target_div).html('Loading tags for '+name+'<br><img src="http://static.adpinr.com/image/2609283.gif">');
;
       triplelift_np_admin_load_placements(id, target_div);
    }
}

function triplelift_np_admin_load_publishers(target_div, new_from_theme) {
    jQuery("#"+target_div).html('Loading placements from TripleLift<br><img src="http://static.adpinr.com/image/2609283.gif">');
    
    triplelift_np_admin_api.get("native_advertising/publisher", function(resp) {
        if (resp.status) {
            
            // if there are no publishers, make a publisher
            if (resp.publishers.length == 0) {
                var outHtml = '<div class="updated" id="message"><p><strong>Error</strong>: No publisher found. '; 
                if (new_from_theme) {
                    outHtml = 'You must create a publisher before having the plugin auto-generate a placement. ';
                }
                outHtml += 'Please create one below.</p></div><div id="triplelift_np_admin_new_publisher"></div>';
                jQuery("#"+target_div).html(outHtml);
                
                triplelift_np_admin_new_publisher("triplelift_np_admin_new_publisher", new_from_theme);
                
            }
            else if (resp.publishers.length == 1) {
                triplelift_np_admin_select_publisher(resp.publishers[0].id, resp.publishers[0].name, target_div, new_from_theme);
            }	
            else if (resp.publishers.length > 1) {
                var html = '<a href="#" onclick="triplelift_np_admin_add_tag(\''+target_div+'\')">Go back</a><br>&nbsp;<br><div class="list-table" style="width:300px;"><table><tr><td>Choose Publisher</td></tr>';
                for (i=0; i<resp.publishers.length;i++) {
                    html += '<tr><td><a href="#" onclick="triplelift_np_admin_select_publisher('+resp.publishers[i].id+', \''+resp.publishers[i].name+'\', \''+target_div+'\', '+new_from_theme+')"><span class="tab">'+resp.publishers[i].name+'</span></a></td></tr>';
                }
                jQuery("#"+target_div).html(html);
            }
        } else {
            unable_to_contact(target_div);
        }
    })
}
</script>
