<?php
if (isset($this->heading_message) && $this->heading_message) {
print '<div class="triplelift_np_admin_display_success" id="triplelift_np_admin_success_message">'.$this->heading_message.'</div>';
}

if (isset($this->error_message) && $this->error_message) {
	print '<div class="updated" id="message"><p><strong>Error</strong>: '.$this->error_message.'</p></div>';
}
?>

<?php
if (count($this->tags) ==  0) {
	print '<br><div class="updated" id="message"><p><strong>Error:</strong> You have no tags associated with your WordPress account. Please click <a href="options-general.php?page=triplelift_np_admin&tab=new_tag">create new tag</a> above to begin.</p></div>';
} else {
	if (isset($this->flash_message) && $this->flash_message) {
		print '<br><div class="updated" id="message"><p>'.$this->flash_message.'</p></div>';
	}
	if (isset($this->error_message) && $this->error_message) {
		print '<br><div class="updated" id="message"><p><strong>Error:</strong> '.$this->error_message.'</p></div>';
	}
	$tags = array_reverse($this->tag_manager->get_tags());
    if (count($tags) == 0) {
         print '<h4>No tags. Create a new tag by clicking on the tab "Create New Tag" above</h4>';
    } else {
         print '<h4>Click on a tag below to edit</h4>';
	?>
    <table class="widefat fixed comments" cellspacing="0">
        <thead>
            <tr>
                <th scope="col" id="placement" class="manage-column " style=""><a onclick="return false"><span>Placement</span></a></th>
                <th scope="col" id="action" class="manage-column " style=""><a onclick="return false"><span>Action</span></a></th>
                <th scope="col" id="status" class="manage-column " style=""><a onclick="return false"><span>Status</span></a></th>
                
            </tr>
       </thead>
        <tfoot>
            <tr>
                <th scope="col" id="placement" class="manage-column " style=""><a onclick="return false"><span>Placement</span></a></th>
                <th scope="col" id="action" class="manage-column " style=""><a onclick="return false"><span>Action</span></a></th>
                <th scope="col" id="status" class="manage-column " style=""><a onclick="return false"><span>Status</span></a></th>
                
            </tr>
       </tfoot>

        <tbody>
        <?php
        $i=1;
        foreach ($tags as $curr_tag) {
    		$inv_code_start = strpos($curr_tag['script'], 'inv_code')+9;
    		$inv_code_end_amp = strpos($curr_tag['script'], '&', $inv_code_start);
    		$inv_code_end_slash = strpos($curr_tag['script'], '\\', $inv_code_start);
		
    		if ($inv_code_end_slash && $inv_code_end_slash < $inv_code_end_amp) {
    			$inv_code_end = $inv_code_end_slash;
    		} else {
    			$inv_code_end = $inv_code_end_amp;
    		}
    
            $inv_code = substr($curr_tag['script'], $inv_code_start, $inv_code_end - $inv_code_start);	
            $tl_contents = @file_get_contents(TRIPLELIFT_NP_API_URL.'open/wordpress/settings?inv_code='.urlencode($inv_code)); 

            $tl_updated = false;
            $tl_just_updated = false;
            if ($tl_contents) {
                $payload_original = json_decode($tl_contents, true);
                $payload = $payload_original['settings'];
                if (!isset($curr_tag['tl_last_update'])) {
                    $tl_updated = true;
                } elseif ($curr_tag['tl_last_update'] < $payload['timestamp']) {
                    $tl_updated = true;
                }
            }
            if (isset($this->tag_tl_update) && $this->tag_tl_update) {
                if (isset($_GET['tag']) && ($_GET['tag'] == $curr_tag['script'] || stripslashes($_GET['tag']) == $curr_tag['script'])) {
                    $tl_just_updated = true;
                    $tl_updated = false;
                }
            }
            print '<tr id="comment-'.$i.'" class=" comment '.($i%2==0 ? 'even' : 'odd').' thread-'.($i%2==0 ? 'even' : 'odd').' depth-1 approved">
            <td class="'.($tl_just_updated ? 'just-updated-row' : '').' '.($tl_updated ? 'update-row' : '').' '.($tl_updated ? 'update-cell' : '').' '.($tl_just_updated ? 'just-updated-cell' : '').'" >'.substr($curr_tag['script'], $inv_code_start, $inv_code_end - $inv_code_start).'</td> 
            <td class="'.($tl_updated ? 'update-cell' : '').' '.($tl_just_updated ? 'just-updated-cell' : '').'"> <a href="'.TRIPLELIFT_NP_BASE_URL.'&tab=modify_tags&'.$this->action_field.'=modify_single_tag_start&tag='.urlencode($curr_tag['script']).'">Edit</a></td> 
            <td class="'.($tl_updated ? 'update-cell' : '').' '.($tl_just_updated ? 'just-updated-cell' : '').'"> '.($curr_tag['active'] ? 'Active' : 'Inactive').'</td> 
    
            
            </tr>';
            if ($tl_updated) {
                print '<tr>
                <td colspan="3" class="'.($tl_updated ? 'update-row' : '').' '.($tl_updated ? 'update-cell' : '').' colspanchange">
                <div class="update-row-message">TripleLift has updated your settings. You may <a href="'.TRIPLELIFT_NP_BASE_URL.'&tab=modify_tags&'.$this->action_field.'=tl_update_tag&tag='.urlencode($curr_tag['script']).'">update now</a>.</div></td></tr>';
            } elseif ($tl_just_updated) {
                print '<tr>
                <td colspan="3" class="'.($tl_just_updated ? 'just-updated-row' : '').' '.($tl_just_updated ? 'just-updated-cell' : '').' colspanchange">
                <div class="just-updated-row-message"><b>Your settings have been updated</b></div></td></tr>';

            }
            $i++;
        }
        ?>
</tbody>

</table>
<script>
jQuery("#triplelift_np_admin_success_message").fadeOut(4000, function() {});
</script>
<?php 
    }
}
