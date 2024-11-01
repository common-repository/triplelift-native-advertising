<?php
include_once(dirname(__FILE__).'/../js/placement_select.php');
include_once(dirname(__FILE__).'/../js/html_placement_add.php');
include_once(dirname(__FILE__).'/../js/theme_placement_add.php');
?>
<script>
function triplelift_np_admin_add_tag(target_div) {

    triplelift_np_admin_html_placement_add(target_div);
    /*
    jQuery("#"+target_div).html(
        '<ul class="card-list">'+
            '<li><a href="#" id="triplelift_np_admin_sent_html"><strong>Tag</strong><br>&nbsp;<br><span class="card-list-text">TripleLift sent me a tag <br>(Recommended)</span></a></li>'+
            '<li><a href="#" id="triplelift_np_admin_existing_placement"><strong>Advanced</strong><br>&nbsp;<br><span class="card-list-text">Use existing placement</span></a></li>'+
            '<li><a href="#" id="triplelift_np_admin_theme_placement"><strong>Theme</strong><br>&nbsp;<br><span class="card-list-text">Plugin makes placement from theme<br>(Experimental)</span></a></li></ul>');
    
	jQuery("#triplelift_np_admin_existing_placement").click(function() {
    	triplelift_np_admin_load_publishers(target_div, false);
	});

	jQuery("#triplelift_np_admin_sent_html").click(function() {
    	triplelift_np_admin_html_placement_add(target_div);
	});

	jQuery("#triplelift_np_admin_theme_placement").click(function() {
    	triplelift_np_admin_theme_placement_add(target_div);
	});
    */
}

</script>
