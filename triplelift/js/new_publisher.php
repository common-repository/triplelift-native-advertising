<script>
function triplelift_np_admin_new_publisher(target_div, new_from_theme) {
    jQuery("#"+target_div).html('<form><p>Publisher Name: <input type="text" id="triplelift_np_admin_new_publisher_name" value="<?php print $this->blog_host . ' ('. $this->theme . ')';?>" id=""></p><p><input type="button" onclick="triplelift_np_admin_new_publisher_submit("'+target_div+'", '+new_from_theme+')" value="Create Publisher"></p></form>');
}

function triplelift_np_admin_new_publisher_submit(target_div, new_from_theme) {
    triplelift_np_admin_api.post("native_advertising/publisher", {name: $("#triplelift_np_admin_new_publisher_name").val()}, function(resp) {
        if (resp.status) {
            triplelift_np_admin_load_publishers(target_div, new_from_theme);
        } else {
            unable_to_contact(target_div)   
        }
    });
}
</script>
