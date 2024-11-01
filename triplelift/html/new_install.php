<?php
include(dirname(__FILE__).'/../js/add_tag.php');
?>
<h3><?php echo  __( 'Welcome!', 'welcome' ); ?></h3>  
<p>Thank you for installing the TripleLift Native Advertising Plugin. Please note that this serves primarily as an interface between WordPress and TripleLift, and is not meant to replace your TripleLift account. You can use this plugin to ensure that the ad tags work seamlessly with your theme.</p>

<p>With this plugin, you can do one of the following:<ul><li>generate tags that natively </li><li>traffic tags that TripleLift sends you in your theme</li><li>create new tags to traffic</li></p>

<p>All of the traffic through the native ads placed on your site by this plugin is automatically linked to your TripleLift account, which you can access at any time at <a href="http://console.triplelift.com" target="_unknown">console.triplelift.com</a></p>

<div id="triplelift_np_admin_tags">

</div>

<script>
triplelift_np_admin_add_tag("triplelift_np_admin_tags");
</script>

