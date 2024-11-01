<?php
if (isset($this->heading_message) && $this->heading_message) {
print '<div class="triplelift_np_admin_display_success" id="triplelift_np_admin_success_message">'.$this->heading_message.'</div>';
}

if (isset($this->error_message) && $this->error_message) {
	print '<div class="updated" id="message"><p><strong>Error</strong>: '.$this->error_message.'</p></div>';
}
?>

<p><a href="<?php print TRIPLELIFT_NP_BASE_URL.'&tab=modify_tags'?>">See all tags</a></p>

<h4>Modify the settings for your tag below:</h4>

<div id="triplelift_np_admin_modify_tag">
	<form name="modify_placement" method="post" action="<?php print TRIPLELIFT_NP_BASE_URL;?>" class="fancy-form">
		<input type="hidden" name="<?php print $this->action_field;?>" value="modify_single_tag">		
		<input type="hidden" name="triplelift_np_admin_original_script" value="<?php print $this->tag_settings['script']; ?>">
		<input type="hidden" name="triplelift_np_admin_tl_last_update" value="<?php print (isset($this->tag_settings['tl_last_update']) ? $this->tag_settings['tl_last_update'] : 0); ?>">
        <div class="stuffbox" id="namediv">
           <h3><label for="name" style="font-size:14px">Script Tag</label></h3>
           <div class="inside">
 
                <p class="textarea">
		            <textarea rows="2" cols="75" id="triplelift_np_admin_script_html" name="triplelift_np_admin_script_html"><?php print str_replace('/textarea', '', stripslashes($this->tag_settings['script'])); ?></textarea>
                </p>
           </div>
        </div>
        <div class="stuffbox" id="namediv">
           <h3><label for="name" style="font-size:14px">General Settings</label></h3>
           <div class="inside">
        		<p><?php print $this->tag_manager->render_offset($this->tag_settings, 'triplelift_np_admin_offset');?></p>
        		<p><?php print $this->tag_manager->render_interval($this->tag_settings, 'triplelift_np_admin_interval');?></p>
        		<p><?php print $this->tag_manager->render_active($this->tag_settings, 'triplelift_np_admin_active');?></p>
            </div>
        </div>
        <p id="triplelift_np_admin_show_advanced_options"><a href="#">Show advanced options</a></p>
        <div id="triplelift_np_admin_advanced_options"  style="display: none;">
            <div class="stuffbox" id="namediv">
               <h3><label for="name" style="font-size:14px">Advanced Settings</label></h3>
                <div class="inside">
                    <p><?php print $this->tag_manager->render_append_prepend_settings($this->tag_settings, 'triplelift_np_admin_append_prepend', 'include');?></p>
                    <p><?php print $this->tag_manager->render_wp_settings($this->tag_settings, 'triplelift_np_admin_wp_include_settings[]', 'include');?></p>
                    <p><?php print $this->tag_manager->render_wp_settings($this->tag_settings, 'triplelift_np_admin_wp_exclude_settings[]', 'exclude');?></p>
                    <p><?php print $this->tag_manager->render_path_settings($this->tag_settings, 'triplelift_np_admin_include_paths', 'include');?></p>
                    <p><?php print $this->tag_manager->render_path_settings($this->tag_settings, 'triplelift_np_admin_exclude_paths', 'exclude');?></p>
                    <p><?php print $this->tag_manager->render_tag_hooks($this->tag_settings, 'triplelift_np_admin_hook');?></p>
                </div>
           </div>
        </div>
		<p><input type="submit" name="Save" class="button-primary" value="<?php esc_attr_e('Save') ?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="triplelift_np_admin_confirmation" href="<?php print TRIPLELIFT_NP_BASE_URL.'&tab=modify_tag&'.$this->action_field.'=delete_tag&tag='.urlencode($this->tag_settings['script']);?>">Delete</a></p>

	</form>
</div>

<script>
triplelift_np_admin_confirmation_links();
jQuery("#triplelift_np_admin_show_advanced_options").click(function() {
    jQuery("#triplelift_np_admin_show_advanced_options").fadeOut(300, function() {
        jQuery("#triplelift_np_admin_advanced_options").show();
        jQuery("#triplelift_np_admin_advanced_options").fadeIn(300);
    }); 
});
jQuery("#triplelift_np_admin_success_message").fadeOut(4000, function() {});
jQuery().ready(function(){
      jQuery('input.tl_np_checkbox').prettyCheckable();

});
</script>
