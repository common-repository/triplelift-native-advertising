<script>
function triplelift_np_admin_html_placement_add(target_div, error_message) {
	if (typeof error_message !== 'undefined') {
		error_message = '<div class="updated" id="message"><p><strong>Error</strong>: '+error_message+'</p></div>';
	} else {
		error_message = '';
	}
	var main_text ='<form name="logout" method="post" action="options-general.php?page=triplelift_np_admin" class="fancy-form">'+
    '<p class="name">'+  
        '<label for="name">Please paste the tag here:</label>  '+
        '<input type="text" name="triplelift_np_admin_html_placement_value" id="triplelift_np_admin_html_placement_value" size=75/> '+
    '</p>'+
    '<input type="hidden" name="<?php print $this->action_field?>" value="new_html_placement">'+
    '<p class="submit">'+
        '<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Submit') ?>" />'+
    '</p>'+
    '</form>';
    jQuery("#"+target_div).html(error_message + main_text);
}


</script>
