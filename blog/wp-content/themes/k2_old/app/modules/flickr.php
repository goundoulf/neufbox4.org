<?php

function flickr_sidebar_module($args) {
	extract($args);

	echo($before_module . $before_title . $title . $after_title);
	?>
		<a href="http://flickr.com/services/feeds/photos_public.gne?id=<?php echo get_option('flickrRSS_flickrid'); ?>&amp;format=rss_200" title="<?php _e('RSS Feed for flickr','k2_domain'); ?>" class="feedlink"><span><?php _e('RSS','k2_domain'); ?></span></a>

		<div>
			<?php get_flickrRSS(); ?>
		</div>
	<?php
	echo($after_module);
}

if(function_exists('get_flickrRSS')) {
	register_sidebar_module('Flickr', 'flickr_sidebar_module', 'sb-flickr');
}

?>
