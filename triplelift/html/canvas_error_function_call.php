<?php
	if (isset($this->includes) && is_array($this->includes)) {
		foreach ($this->includes as $curr_include) {		
			include_once (dirname(__FILE__).'/../'.$curr_include);
		}
	}
	print $this->canvas;
?>
<script>
<?php
	print $this->function_call;
?>
</script>
