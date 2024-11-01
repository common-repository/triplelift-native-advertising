<h3><?php echo  __( 'Ads have been globally disabled', 'inactive' ); ?></h3>  

<form name="activate" method="post" action="">
<input type="hidden" name="<?php print $this->action_field ?>" value="activate">
<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Show ads') ?>" />
</p>
</form>

