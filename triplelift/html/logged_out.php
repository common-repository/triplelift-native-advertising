<div class="wrap">

<h2><?php echo  __( 'TripleLift Native Advertising Settings', 'logged-out' ); ?></h2>
<h3><?php echo  __( 'You must login to begin', 'logged-out' ); ?></h3>

<form name="login" method="post" action="">

<?php
if ($this->error_message) {
	?>
	<div class="updated" id="message"><p><strong>Error</strong> <?php echo($this->error_message);?></p></div>
	<?php
}
?>
<p><?php _e("Username:", 'username' ); ?> 
<input type="text" name="<?php echo $this->username_field; ?>" value="<?php echo $opt_val; ?>" size="20">
</p>

<p><?php _e("Password:", 'password' ); ?> 
<input type="password" name="<?php echo $this->password_field; ?>" value="<?php echo $opt_val; ?>" size="20">
</p>

<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Login') ?>" />
</p>

<hr />

<p>
<a href="mailto:info@triplelift.com" target="_blank">Contact us</a> if you don't have a TripleLift account or to get more information. If you have forgotten your account information or are otherwise having difficulty logging in, please <a href="mailto:support@triplelift.com" target="_blank">contact support</a> 
</p>
</form>
</div>

