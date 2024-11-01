<h3>Plugin Details</h3>
<?php
$plugin_data = get_plugin_data( dirname(dirname(__FILE__)).'/triplelift.php' );
print '<ul>
<li><strong>Name</strong>: '.$plugin_data['Name'].'</li>
<li><strong>Version</strong>: '.$plugin_data['Version'].'</li>
<li><strong>Description</strong>: '.$plugin_data['Description'].'</li>
<li><strong>Learn More</strong>: <a href="http://www.triplelift.com/pub-info" target="_blank">Overview of native advertising</a></li>
</ul>';
?>
<br>
<!--
<h3>Settings</h3>
<form name="modify_settings" method="post" action="<?php print TRIPLELIFT_NP_BASE_URL;?>" class="fancy-form">
<input type="hidden" name="<?php print $this->action_field;?>" value="modify_general_settings">
<p><input class="tl_np_checkbox" name="triplelift_np_admin_global_debug" <?php if ($this->debug_mode) print ' checked ';?> id="triplelift_np_admin_global_debug" type="checkbox"  value="1" data-label="Enable Debug Mode"></p>
<p><input type="submit" name="Save" class="button-primary" value="Submit"></p> 
</form>
-->
<script>
jQuery().ready(function(){
      jQuery('input.tl_np_checkbox').prettyCheckable();

});
</script>
