	<div class="clear"></div>

</div> <!-- Close Page -->

<hr />

<div id="footer">

	<?php $style_footer = get_k2info('style_footer'); if ($style_footer != '') { ?><p class="footerstyledwith"><?php printf(__('Styled with %s','k2_domain'), $style_footer); ?></p><?php } ?>

	<p class="footerpoweredby"><?php printf( __('Powered by %1$s and %2$s','k2_domain'),
		sprintf('<a href="http://wordpress.org/">%1$s<!-- %2$s--></a>',
			__('WordPress','k2_domain'),
			get_bloginfo('version')
		),
		sprintf('<a href="http://getk2.com/" title="%1$s">K2<!-- %2$s--></a>',
			__('Loves you like a kitten.','k2_domain'),
			get_k2info('version')
		)
	); ?></p>

	<p class="footerfeedlinks"><?php printf(__('<a href="%1$s">Entries Feed</a> and <a href="%2$s">Comments Feed</a>','k2_domain'), get_bloginfo('rss2_url'), get_bloginfo('comments_rss2_url')) ?></p>
	<!-- <?php printf(__('%d queries. %.4f seconds.','k2_domain'), $wpdb->num_queries , timer_stop()) ?> -->
</div>

<?php wp_footer(); ?>

<!-- Begin Google Analytics Tracking Code -->
<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>

<script type="text/javascript">
	var pageTracker = _gat._getTracker("UA-2496487-5");
	pageTracker._initData();
	pageTracker._trackPageview();
</script>
<!-- End Google Analytics Tracking Code -->

</body>
</html> 
