<script>

function triplelift_np_admin_select_placement(id, inv_code) {
	var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "<?php print TRIPLELIFT_NP_BASE_URL;?>");

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "<?php print $this->action_field;?>");
    hiddenField.setAttribute("value","new_html_placement");
	form.appendChild(hiddenField);

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "triplelift_np_admin_html_placement_value");
    hiddenField.setAttribute("value",'<script src="<?php print TRIPLELIFT_NP_IB;?>ttj?inv_code='+inv_code+'&member=<?php print $this->options_object['member_id']?>"><'+'/script>');
	form.appendChild(hiddenField);

    document.body.appendChild(form);
    form.submit();	    
}

function triplelift_np_admin_load_placements(id, target_div) {
    triplelift_np_admin_api.get("native_advertising/placement?publisher_id="+id, function(resp) {
        if (resp.status) {
            
            // no placements - try to get a default placement for this theme if possible
            if (resp.placements.length == 0) {
                triplelift_np_admin_new_placement(id, target_div, false);
            }
            
            // if there's one or more
            else if (resp.placements.length >= 1) {
                var html = '<a href="#" id="triplelift_np_admin_placement_back">Go back</a><br>&nbsp;<br><div class="list-table" style="width:300px;"><table><tr><td>Choose Placement</td></tr>';

                for (i=0; i<resp.placements.length;i++) {

                    html += '<tr><td><a href="#" onclick="triplelift_np_admin_select_placement('+resp.placements[i].id+', \''+resp.placements[i].inv_code+'\', false)"><span class="tab">'+resp.placements[i].inv_code+'</span></a></td></tr>';
                }
                jQuery("#"+target_div).html(html);
				jQuery("#triplelift_np_admin_placement_back").click(function() {
					triplelift_np_admin_load_publishers(target_div);	
				});
			
            }
        } else {
            unable_to_contact(target_div);
        }
    })
}
</script>
<?php
include_once(dirname(__FILE__).'/../js/publisher_select.php');
include_once(dirname(__FILE__).'/../js/new_publisher.php');
include_once(dirname(__FILE__).'/../js/new_placement.php');
include_once(dirname(__FILE__).'/../js/theme_placement_add.php');

