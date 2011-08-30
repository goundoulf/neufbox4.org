<?php
if (get_option('k2sidebarmanager') == '1') {
	remove_action('plugins_loaded', 'wp_maybe_load_widgets', 0);

	if(is_admin()) {
		global $pagenow;
		if($pagenow == 'index.php')
			wp_maybe_load_widgets();
	}
}
?>
