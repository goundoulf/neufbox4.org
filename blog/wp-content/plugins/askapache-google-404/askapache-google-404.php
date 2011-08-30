<?php
/**
 * Plugin Name: AskApache Google 404
 * Short Name: AA Google 404
 * Description: Displays unbeatable information to site visitors arriving at a non-existant page (from a bad link).  Major SEO with Google AJAX, Google 404 Helper, Related Posts, Recent Posts, etc..
 * Author: AskApache
 * Version: 4.8.2.2
 * Requires at least: 2.7
 * Tested up to: 3.1-alpha
 * Tags: google, 404, errordocument, htaccess, error, notfound, ajax, search, seo, mistyped, urls, news, videos, images, blogs, optimized, askapache, post, admin, askapache, ajax, missing, admin, template, traffic
 * Contributors: AskApache
 * WordPress URI: http://wordpress.org/extend/plugins/askapache-google-404/
 * Author URI: http://www.askapache.com/
 * Donate URI: http://www.askapache.com/donate/
 * Plugin URI: http://www.askapache.com/seo/404-google-wordpress-plugin.html
 *
 *
 * AskApache Google 404 - Intelligent SEO-Based 404 Error Handling
 * Copyright (C) 2010	AskApache.com
 *
 * This program is free software - you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.	If not, see <http://www.gnu.org/licenses/>.
 */

// exit if add_action or plugins_url functions do not exist
if (!function_exists('add_action') || !function_exists('plugins_url')) exit;

// function to replace wp_die if it doesn't exist
if (!function_exists('wp_die')) : function wp_die ($message = 'wp_die') { die($message); } endif;

// define some definitions if they already are not
!defined('WP_CONTENT_DIR') && define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
!defined('WP_PLUGIN_DIR') && define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
!defined('WP_CONTENT_URL') && define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
!defined('WP_PLUGIN_URL') && define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');


// don't load directly
!defined('ABSPATH') && exit;


if(!function_exists('set_current_screen')):

/**
 * Set the current screen object
 *
 * @since 3.0.0
 *
 * @uses $current_screen
 *
 * @param string $id Screen id, optional.
 */
function aa_set_current_screen( $id = '' ) {
	global $current_screen, $hook_suffix, $typenow, $taxnow;

	$action = '';

	if ( empty($id) ) {
		$current_screen = $hook_suffix;
		$current_screen = str_replace('.php', '', $current_screen);
		if ( preg_match('/-add|-new$/', $current_screen) )
			$action = 'add';
		$current_screen = str_replace('-new', '', $current_screen);
		$current_screen = str_replace('-add', '', $current_screen);
		$current_screen = array('id' => $current_screen, 'base' => $current_screen);
	} else {
		$id = sanitize_key($id);
		if ( false !== strpos($id, '-') ) {
			list( $id, $typenow ) = explode('-', $id, 2);
			if ( taxonomy_exists( $typenow ) ) {
				$id = 'edit-tags';
				$taxnow = $typenow;
				$typenow = '';
			}
		}
		$current_screen = array('id' => $id, 'base' => $id);
	}

	$current_screen = (object) $current_screen;

	$current_screen->action = $action;

	// Map index to dashboard
	if ( 'index' == $current_screen->base )
		$current_screen->base = 'dashboard';
	if ( 'index' == $current_screen->id )
		$current_screen->id = 'dashboard';

	if ( 'edit' == $current_screen->id ) {
		if ( empty($typenow) )
			$typenow = 'post';
		$current_screen->id .= '-' . $typenow;
		$current_screen->post_type = $typenow;
	} elseif ( 'post' == $current_screen->id ) {
		if ( empty($typenow) )
			$typenow = 'post';
		$current_screen->id = $typenow;
		$current_screen->post_type = $typenow;
	} elseif ( 'edit-tags' == $current_screen->id ) {
		if ( empty($taxnow) )
			$taxnow = 'post_tag';
		$current_screen->id = 'edit-' . $taxnow;
		$current_screen->taxonomy = $taxnow;
	}

	$current_screen = apply_filters('current_screen', $current_screen);
}

endif;





/**
 * AA_G404
 * 
 * @package   
 * @author AskApache
 * @version 2009
 * @access public
 * http://googlesystem.blogspot.com/2008/02/google-toolbar-and-404-error-pages.html
 */
class AA_G404 {
	
	var $options = array();	// an array of options and values
	var $plugin = array();	// array to hold plugin information
	var $code = array('css' => '', 'javascript' => '', 'html' => '');	// array to hold the css, javascript, and html


	/**
	 * Defined blank for loading optimization
	 */
	function AA_G404() {}
	
	/**
	 * Loads options named by opts array into correspondingly named class vars
	 */
	function LoadOptions($opts=array('options', 'code', 'plugin'))
	{
		foreach ($opts as $pn) $this->{$pn} = get_option("askapache_google_404_{$pn}");
	}
		
		
	
	
	/**
	 * Saves options from class vars passed in by opts array and the adsense key and api key
	 */
	function SaveOptions($opts=array('options','code','plugin'))
	{
		foreach ($opts as $pn) update_option("askapache_google_404_{$pn}", $this->{$pn});
		$this->save_keys();
	}
		
		
	
	
	/**
	 * Gets and sets the default values for the plugin options, then saves them
	 */
	function default_options()
	{
		
		// get all the plugin array data
		$this->plugin = $this->get_plugin_data();
		
		// original code that comes with plugin
		$this->code = $this->get_default_code();
		
		$ads_key = get_option('aa_google_404_adsense_key');
		$api_key = get_option('aa_google_404_api_key');
		$ana_key = get_option('aa_google_404_analytics_key');
		$mana_key = get_option('aa_google_404_mobile_analytics_key');
		
	
		foreach ( array("code", "plugin", "orig_code", "iframe_one_time") as $pn ) delete_option("askapache_google_404_{$pn}" );

		// default options
		$this->options = array(
			'api_key' 		=> ($api_key !== false && strlen($api_key) > 5) ? $api_key : '',	// ABQIAAAAGpnYzhlFfhxcnc02U1NT1hSrXMCP0pDj9HHVk8NG53Pp2_-7KxSdZ5paIt0ciL3cNLv20-kmmxlTcA
			'adsense_key' 	=> ($ads_key !== false && strlen($ads_key) > 5) ? $ads_key : '',	// pub-4356884677303281
			'analytics_key'	=> ($ana_key !== false && strlen($ana_key) > 5) ? $ana_key : '',	// UA-732153-7
			'mobile_analytics_key'	=> ($mana_key !== false && strlen($mana_key) > 5) ? $mana_key : '',	// UA-732153-7
			'analytics_url' => '"/404/?page=" + document.location.pathname + document.location.search + "&from=" + document.referrer',
			
			
			'enabled' 		=> '1',	// 404 error handling is ON by default
			'google_ajax' 	=> '1', // google ajax search results are ON by default
			'google_404' 	=> '0', // googles new 404 script is OFF by default


			'analytics_log' => '1',	// 
			'mobile_analytics_log' => '0',


			'robots_meta' 	=> '1', // adding noindex,follow robot meta tag to error pages is ON by default
			'robots_tag' => 'noindex,follow',	// the value of the robot meta on error pages
			
			'related_posts' => '1', // showing related posts on error pages is ON by default
			'related_num' 	=> 10,	// number of related posts to show
			'related_length'=> 240,	// length of related posts excerpts
			
			'recent_posts' 	=> '1', // showing recent posts on error pages is ON by default
			'recent_num' 	=> 6,	// number of recent posts to show
			
			'tag_cloud' 	=> '1', // showing a tag cloud on error pages is ON by default
			'tag_cloud_num'	=> 100,	// number tags used to create cloud
			
			'show_result_site' => '1',
			'show_result_video' => '1',
			'show_result_blogs' => '1',
			'show_result_cse' => '1',
			'show_result_image' => '1',
			'show_result_news' => '1',
			'show_result_web' => '1',
			'show_result_local' => '1',
			
			
			'404_handler' => trailingslashit(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace("\\","/",basename(dirname(__FILE__)))) . '404.php',	// the file location of 404 template
			
			'iframe_preview'=> '0', // iframe preview in admin area is OFF by default
			'iframe_url' => site_url('wordpress-google-AskApache/f2?askapache=htaccess-plugin&amp;missing-5+this-post')	// the url for the iframe preview
			
		);
		
		// Save all these variables to database
		$this->SaveOptions();
	}
		
		
	
	
	/**
	 * currently code is not saved across upgrades due to a potential security issue
	 */
	function upgrade_settings()
	{
		// initiate all vars to empty string
		$api_key=$adsense_key=$analytics_key=$code_html=$code_js=$code_css=$code=$options='';
		
		// get the oldest options from earliest version
		foreach (array('api_key','adsense_key', 'analytics_key', 'code_html', 'code_js', 'code_css') as $pn) $$pn = get_option("aa_google_404_{$pn}");
		
		// get old options if more current version
		foreach (array('code','options') as $pn) $$pn = get_option("askapache_google_404_{$pn}");
		
		// first make sure to transfer over any existing api key, as the google ajax now wont work without it
		if ($api_key !== false && strlen($api_key) > 5 ) update_option('aa_google_404_api_key', $api_key);	
		if ($adsense_key !== false && strlen($adsense_key) > 5 ) update_option('aa_google_404_adsense_key', $adsense_key);	
		if ($analytics_key !== false && strlen($analytics_key) > 5 ) update_option('aa_google_404_analytics_key', $analytics_key);
		
		if ($options !==false && is_array($options) && array_key_exists('api_key', $options) && strlen($options['api_key']) > 5 ) update_option('aa_google_404_api_key', $options['api_key']);
	}
		
		
	
	
	/**
	 * Saves the api and adsense keys
	 */
	function save_keys()
	{
		update_option('aa_google_404_api_key', $this->options['api_key']);
		update_option('aa_google_404_adsense_key', $this->options['adsense_key']);
		update_option('aa_google_404_analytics_key', $this->options['analytics_key']);
	}
		
		
	
	
	/**
	 * Gets the default code for css, html, and javascript by reading the original file in this plugins folder/f/orig.(css|javascript|html)
	 */
	function get_default_code()
	{
		foreach(array_keys($original_code = array('css' => '', 'html' => '', 'javascript' => '')) as $pn) 
			$original_code["{$pn}"] = $this->_readfile(trailingslashit(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace("\\","/",basename(dirname(__FILE__)))) . "f/orig.{$pn}");
		return $original_code;
	}
		
		
	
	
	/**
	 * Loads the options into the class vars.  
	 * Adds this plugins 'load' function to the 'load-plugin' hook.
	 * Adds this plugins 'admin_print_styles' function to the 'admin_print_styles-plugin' hook. 
	 */
	function init()
	{
		$this->LoadOptions();
		
		add_action("load-{$this->plugin['hook']}", array(&$this, 'load'));
		add_action("admin_print_styles-{$this->plugin['hook']}", create_function('', 'echo "<style type=\"text/css\">#ag4 #icon-askapache {background-image:url('.plugins_url("/f/icon-askapache.png",__FILE__).');}</style>";'));
		add_action("admin_footer-{$this->plugin['hook']}", create_function('', 'echo "<script src=\"'.plugins_url('/f/admin.js',__FILE__).'\" type=\"text/javascript\"></script>";'));
		
  }
		


	/**
	 * The load function executed by the load-plugin hook.  Passes control of request handling to the 'handle_post' function.
	 * Adds the meta-boxes and the contextual help.
	 * Enqueues the neccessary js and css files for plugin adminstration.
	 */
	function load()
	{
		global $screen,$current_screen;
		
		if(function_exists('set_current_screen')) set_current_screen($current_screen->id);
		else if(function_exists('aa_set_current_screen')) aa_set_current_screen($current_screen->id);
		
 		// parse and handle post requests to plugin
		if('POST' == $_SERVER['REQUEST_METHOD']) $this->handle_post();
		
		// add meta boxes - can someone help me figure this damn metabox situation out..  not even 2.9 is extendable enough
		//foreach(array('404 Options', 'Plugin Options', 'Google Options', 'Robot Options', 'Recent Posts Options', 'Related Posts Options', 'Popular Tag Cloud', 'CSS Code', 'HTML Code', 'Javascript Code') as $box)
		//foreach(array('Main Options', 'CSS Code', 'HTML Code', 'Javascript Code') as $box)
			//add_meta_box(preg_replace('/\W/i', '', strtolower(str_replace(' ', '_', "ag4_box_{$box}"))), "{$box}", array(&$this, "print_box"), $current_screen->id, 'normal', 'high');
			
			
		// add contextual help
		$help = '<h4>Fixing Status Headers</h4>';
		$help .= '<p>For super-advanced users, or those with access and knowledge of Apache <a href="http://www.askapache.com/htaccess/htaccess.html">.htaccess/httpd.conf files</a>';
		$help .=' you should check that your error pages are correctly returning a <a href="http://www.askapache.com/htaccess/apache-status-code-headers-errordocument.html"><code>404 Not Found</code>';
		$help .=' HTTP Header</a> and not a <code>200 OK</code> Header which appears to be the default for many WP installs, this plugin attempts to fix this using PHP, but the best way I have found';
		$help .=' is to add the following to your <a href="http://www.askapache.com/htaccess/htaccess.html">.htaccess</a> file.</p>';
		$help .= '<pre>ErrorDocument 404 /index.php?error=404'."\n".'Redirect 404 /index.php?error=404</pre>';
		$help .= '<p>You can check your headers by requesting a bad url on your site using my online tool <a href="http://www.askapache.com/online-tools/http-headers-tool/">Advanced HTTP Headers</a>.</p>';
		$help .= '<h4>Future Awesomeness</h4>';
		$help .= '<p>The goal of this plugin is to boost your sites SEO by telling search engines to ignore your error pages, with the focus on human users to increase people staying on your site and being';
		$help .= ' able to find what they were originally looking for on your site.  Because I am obsessed with fast web pages, many various speed/efficiency improvements are also on the horizon.</p>';
		$help .= '<p>Another feature that I am using with beta versions of this plugin, is tracking information for you to go over at your leisure, to fix recurring problems.  The information is collected';
		$help .= ' is the requested url that wasnt found, the referring url that contains the invalid link.</p>';
		$help .= '<p>The reason I didnt include it in this release is because for sites like AskApache with a very high volume of traffic (and thus 404 requests) this feature can create a bottleneck and ';
		$help .= 'slow down or freeze a blog if thousands of 404 errors are being requested and saved to the database.  This could also very quickly be used by malicious entities as a Denial of Service ';
		$help .= 'attack.  So I am figuring out and putting into place limits.. like once a specific requested url resulting in a not found error has been requested 100x in a day, an email is sent to the ';
		$help .= 'blog administrator.  But to prevent Email DoS and similar problems with the number and interval of emails allowed by your email provider other considerations on limits need to be examined.</p>';
		$help .= '<h5>FAST!  CACHE!  SPEED!</h5>';
		$help .= '<p>Future versions of this plugin will add this option for everyone..  Basically, there will be an option to switch to using a 100% javascript (instead of javascript + php) method of ';
		$help .= 'handling 404 errors, this will be BIG because the plugin will simply create 1 static html file named 404.html and then use .htaccess ErrorDocument to redirect all 404 errors to this ';
		$help .= 'static html file.  The downside is the only way to get stuff like related posts and recent posts would be to use ajax or to create the 404.html static file at regular intervals or for ';
		$help .= 'multiple error requests.  This will help tremendously in keeping your site and server speedy as it will reduce CPU/Memory/Disk IO/and Database Queries to almost nothing.  Stay tuned.</p>';
		$help .= '<p>One other big improvement or feature-add is to show the admin a list of error urls and allow the admin to specify the correct url that the error url should point to.  Then using mod_rewrite ';
		$help .= 'rules automatically generated by the plugin and added to .htaccess these error urls will 301 redirect to the correct urls, boosting your SEO further and also helping your visitors.  A ';
		$help .= 'big difference between this method and other redirection plugins is that it will use mod_rewrite, I would really like to avoid using php to redirect or rewrite to other urls, as this method';
		$help .= ' has a HUGE downside in terms of your site and servers speed, bandwidth usage, CPU/Memory usage, Disk Input/Output (writes/reads), security issues, Database Usage, among other problems.</p>';
		$help .= '<h5>Generating Revenue</h5>';
		$help .= '<p>Anyone smart enough to find and use this plugin deserves to earn a little income too, so I am working on integrating AdSense into the Search Results.  Currently this is very new and not ';
		$help .= 'enabled or allowed by Google in certain circumstances and just isnt a feature yet of the Google AJAX API.  At the very least I am going to add a custom search engine results for your site ';
		$help .= 'that will allow you to display relevant ads, but I am still waiting for some clarification from my Google Homeslices on whether we can use the AJAX API to display ADS on 404 error pages ';
		$help .= 'automatically based on the requested url or if that violates the Google TOS, which is something I would never condone or even get close to violating.  If not then we will have to settle ';
		$help .= 'for no ADS being displayed automatically and only being displayed if the user actually types something in the search box.  So go get your AdSense account (free) and also sign up for a ';
		$help .= 'Google CSE (custom search engine) as soon as possible.</p>';
		$help .= '<h5>Comments/Questions</h5><p><strong>Please visit <a href="http://www.askapache.com/">AskApache.com</a> or send me an email at <code>webmaster@askapache.com</code></strong></p>';
		add_contextual_help('settings_page_askapache-google-404', $help);

		// enqueue css
		wp_enqueue_style($this->plugin['pagenice'], plugins_url('/f/admin.css',__FILE__), array('dashboard','wp-admin','thickbox'), $this->plugin['version'], 'all');
		
		// enqueue js
		wp_enqueue_script('jquery');
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
		wp_enqueue_script('thickbox');
		wp_enqueue_script('codepress');
		wp_enqueue_script('jquery-ui-core');   //Used for background color animation
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script($this->plugin['pagenice'], plugins_url('/f/admin.js',__FILE__), array('jquery','postbox','thickbox','codepress'), $this->plugin['version']);
  	}
		
		
	
	
	/**
	 * The main function that lets this plugin handle errors instead of WP's builtin error handling.
	 */
	function template_redirect()
	{
		global $wp_query, $AA_G404_Handler, $wpdb;

		// return execution back to wordpress if not 404
		if (!is_404() || '1' != $this->options['enabled']) return true;
		
		// if AA_G404_Handler is not already an object, create it
		if(!is_object($AA_G404_Handler))$AA_G404_Handler = new AA_G404_Handler();
		
		// Adds the AA_G404_Handler::wp_header function to the wp_head action
		add_action('wp_head', array(&$AA_G404_Handler, 'wp_header'));
		
		// Modifies the nocache_headers filter with an anonymous function that fixes Cache-Control and Expires headers for error pages
		add_filter('nocache_headers', create_function('$a', 'return (is_404()? array("Cache-Control"=>"private, max-age=0", "Expires"=>"-1") : $a);'), 99999);
		
		// Modifies the wp_headers filter with an anonymous function that fixes Cache-Control and Expires headers for error pages
		add_filter('wp_headers', create_function('$a', 'return (is_404()? array("Cache-Control"=>"private, max-age=0", "Expires"=>"-1") : $a);'), 99999);
		
		// Modifies the title for error pages to be descriptive (in describing the error)
		add_filter('wp_title', create_function('$a', 'global $AA_G404_Handler; if(!is_object($AA_G404_Handler))$AA_G404_Handler =& new AA_G404_Handler(); return ( !is_404() ) ? $a : $AA_G404_Handler->sc . " " . $AA_G404_Handler->reason;'));
		
		// Now handle the incoming request with AA_G404_Handler::handle_it
		$AA_G404_Handler->handle_it();
		
		// Loads the 404 error template specified by the 404_handler option
		if(file_exists($this->options['404_handler'])) load_template($this->options['404_handler']);
		
		// return for the template_redirect
		return;
	}
		
		
	
	
	/**
	 * this plugin has to protect the code as it is displayed live on error pages, a prime target for malicious crackers and spammers
	 * can someone help me add the proper code to make sure everything is escaped correctly?
	 * @return
	 */
	function handle_post()
	{
		// if current user does not have administrator rights, then DIE
		if(!current_user_can('administrator')) wp_die('<strong>ERROR</strong>: Not an Admin!');
		
		// verify nonce, if not verified, then DIE
		if(isset($_POST["_{$this->plugin['nonce']}"])) wp_verify_nonce($_POST["_{$this->plugin['nonce']}"], $this->plugin['nonce']) || wp_die('<strong>ERROR</strong>: Incorrect Form Submission, please try again.');
		elseif(isset($_POST["ag4_action_reset"])) wp_verify_nonce($_POST["ag4_action_reset"], 'ag4_action_reset_nonce') || wp_die('<strong>ERROR</strong>: Incorrect Form Submission, please try again.');
		
		// resets options to default values
		if(isset($_POST["ag4_action_reset"])) return $this->default_options();
		
		// load up the current options from the database
		$this->LoadOptions();

		// process absolute integer options
		foreach (array(
					   'related_num', 
					   'related_length', 
					   'recent_num', 
					   'tag_cloud_num') as $k) $this->options[$k] = ((isset($_POST["ag4_{$k}"])) ? absint($_POST["ag4_{$k}"]) : absint($this->options[$k]));
			
			
		// process options of type string
		foreach (array(
					   'api_key', 
					   'adsense_key', 
					   'analytics_key', 
						 'mobile_analytics_key',
					   'robots_tag', 
					   '404_handler', 
					   'iframe_url') as $k)$this->options[$k] = ((isset($_POST["ag4_{$k}"])) ? $_POST["ag4_{$k}"] : $this->options[$k]);
		
		
		// process on ('1') or off ('0') options
		foreach (array(
					   'enabled', 
					   'iframe_preview', 
					   'robots_meta', 
					   'google_404', 
					   'related_posts', 
					   'recent_posts', 
					   'google_ajax', 
					   'tag_cloud', 
					   'analytics_log',
						 'mobile_analytics_log',
						 'show_result_site',
						 'show_result_video',
						 'show_result_blogs',
						 'show_result_cse',
						 'show_result_image',
						 'show_result_news',
						 'show_result_web',
						 'show_result_local') as $k)$this->options[$k] = ((!isset($_POST["ag4_{$k}"])) ? '0' : '1');
		
		// TODO: Nothing :)
		foreach (array('analytics_url') as $k)if (isset($_POST["ag4_{$k}"])) $this->options[$k] = stripslashes($_POST["ag4_{$k}"]);
	
	
		// process incoming unfiltered code
		foreach (array(
					   'css', 
					   'html', 
					   'javascript') as $k) if (isset($_POST["ag4_{$k}"])) $this->code[$k] = stripslashes($_POST["ag4_{$k}"]);


		// Save code and options arrays to database
		$this->SaveOptions();
	}
		
		
	
	
	/**
	 */
	function options_page()
	{
		global $screen,$current_screen, $wp_meta_boxes, $_wp_contextual_help, $title;

		if(!current_user_can('administrator')) wp_die('<strong>ERROR</strong>: Not an Admin!');
		
		echo '<div class="wrap" id="ag4">';
		if(function_exists('screen_icon')) screen_icon();
		echo '<h2>' . $this->plugin['plugin-name'].'</h2>';
		
		
		echo '<form action="' . admin_url($this->plugin['action']) . '" method="post" id="ag4_form">';
		
		// print form nonce
		echo '<p style="display:none;"><input type="hidden" id="_' . $this->plugin['nonce'] . '" name="_' . $this->plugin['nonce'] . '" value="' . wp_create_nonce($this->plugin['nonce']) . '" />';
		echo '<input type="hidden" name="_wp_http_referer" value="' . (esc_attr($_SERVER['REQUEST_URI'])) . '" /></p>';	
		
		
		echo '<p><a title="Preview" class="thickbox thickbox-preview" href="'.$this->options['iframe_url'].'&amp;TB_iframe=true&amp;width=1024&amp;height=600">Preview</a></p>';
		
			
			// if iframe_preview is enabled, show the preview
		if($this->options['iframe_preview'] == '1') echo '<div id="preview"><iframe src="'.$this->options['iframe_url'].'" width="99%" height="400" frameborder="0" id="preview"></iframe></div>';
	

		
		$section_names = array(
			'general' =>  'General',
			'output' =>   '404 Output Options',
			'ajax' =>    'Google Search Options',
			'tracking' =>  'Tracking/Logging',
			'css' =>    'CSS Editor',
			'js' =>    'JS Editor',
			'html' => 'HTML Editor'
		);
		echo '<div id="ag4-tabs">';
		?><ul class="hide-if-no-js"><?php foreach($section_names as $section_id => $section_name)printf('<li><a href="#section-%s">%s</a></li>', esc_attr($section_id), $section_name);?></ul><?php
		
		echo '<div id="section-general" class="ag4-section"><h3 class="hide-if-js">General</h3>';
			echo '<table class="form-table"><tbody><tr><th scope="row">Enable/Disable Plugin</th><td><fieldset><legend class="screen-reader-text"><span>Enable/Disable handling errors</span></legend>';
			echo '<label for="ag4_enabled" title="Handle Erorrs"><input type="radio"'. checked( $this->options['enabled'],'1',false).' value="1" name="ag4_enabled" id="ag4_enabled_on" /> Enable plugin to handle 404s, immediately</label><br />';
			echo '<label for="ag4_enabled" title="Turn off this plugin"><input type="radio"'. checked( $this->options['enabled'], '0', false).' value="0" name="ag4_enabled" id="ag4_enabled_off" /> Disable this plugin from handling 404s</label><br />';
			echo '</fieldset></td></tr>';
			
			echo '<tr><th scope="row">404.php Template File</th><td>';
			echo '<fieldset><legend class="screen-reader-text"><span>404.php Template File</span></legend>';
			foreach(array(trailingslashit(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . str_replace("\\","/",basename(dirname(__FILE__)))).'404.php', TEMPLATEPATH.'/404.php',  dirname(TEMPLATEPATH).'/default/404.php', 'Custom File Location') as $v=>$k) 
			{
				if(file_exists($k)) {
					echo '<label for="ag4_404_handler" title="'.$k.'"><input type="radio"'.checked($this->options['404_handler'],$k,false);
					echo ' value="'.$k.'" name="ag4_404_handler" id="ag4_404_handler_'.$v.'" /> <code>'.$k.'</code> <a href="'.admin_url("theme-editor.php?file={$k}").'">(?)</a></label><br />';
				} elseif ($k=='Custom File Location') {
					echo '<label for="ag4_404_handler" title="'.$k.'"><input type="radio"'.checked($this->options['404_handler'],$k,false);
					echo ' value="'.$k.'" name="ag4_404_handler" id="ag4_other_file" /> ';
					echo '<input type="text" value="Custom File Location" class="code" id="ag4_404_choose_file" style="min-width:35em;" name="ag4_404_choose_file" title="File Path"></label><br />';
				}
			}
			echo '</fieldset></td></tr></tbody></table>';
			echo '<p class="binfo"><strong>To use your own <a href="http://www.google.com/support/webmasters/bin/answer.py?&answer=93641">custom 404.php</a> file</strong> in your template directory:  <br />Add <code>if(function_exists("aa_google_404"))aa_google_404();</code> between get_header and get_footer and save as 404.php in your template folder.  <em>(based off your current index.php perhaps)</em><br />Also: try adding an <code>ob_start();</code> as the first command in the file, before <code>get_header();</code>, and add an <code>exit;</code> as the last command before the closing <code>?&gt;</code><br /> - See the included 404.php file for a simple working example.</p>'; 
			
	
			$this->form_field( 1, 'Show Google AJAX Search', 'google_ajax', 'Displays Google AJAX Search Results' );
			$this->form_field( 2, 'Google API Key <a href="http://code.google.com/apis/ajaxsearch/signup.html">Get One</a>', 'api_key', 'This identifies your blog to Google.' );
			echo '<p class="binfo">You need a Google API Key for this site to display the ajax results.  Go ahead and add your AdSense ID as future versions of this plugin will allow you to incorporate AdSense setups compliant with Google Guidelines.</p>';			
			
					
			$this->form_field( 1, '<a href="http://www.google.com/support/googleanalytics/bin/answer.py?hl=en&answer=86927">Track</a> <a href="http://www.google.com/support/forum/p/Google+Analytics/thread?tid=09386ba811b3e7d8&hl=en">Errors</a> with Google Analytics', 'analytics_log', 'Use Google Analytics to Track/Log Errors' );
			$this->form_field( 2, 'Google Analytics Key <small>UA-733153-7</small><a href="https://www.google.com/adsense/support/bin/answer.py?answer=45465">Get One</a>', 'analytics_key', 'The tracking ID for this site.' );
			echo '<p class="binfo"><strong>NEW</strong>:<br />Use Google Analytics to <a href="http://www.askapache.com/seo/tip-google-analytics-404-error-page.html">Track/Log Errors</a>.</p>';

			$this->form_field( '2j', 'Tracking URL for reports <a href="http://www.google.com/support/googleanalytics/bin/answer.py?answer=75129">Get One</a>', 'analytics_url', 'Lets you view errors in analytics!' );
			echo '<p class="binfo"><strong>NEW</strong>:<br />This is clever as instead of using your server and database to store 404s, which results in crazy additional server-load, this method uses javascript so google machines will do all the work.  <code>"/404.html?page=" + document.location.pathname + document.location.search + "&amp;from=" + document.referrer</code></p>';			

			
			
			$this->form_field( 2, 'Your Google AdSense Publisher ID <a href="https://www.google.com/adsense/support/bin/answer.py?answer=45465">Get One</a>', 'adsense_key', 'This tells Google who to pay.' );
			echo '<p class="binfo"><strong>COMING TO THIS PLUGIN ON NEXT UPDATE</strong>:<br />Use Google AdSense to generate revenue, using several policy-conforming and user-friendly ways allowed by Google. For the AskApache Google 404\'s next upgrade I will be adding alot of adsense/analytics features, one will be geared so users that are running this plugin on a new blog with no content can automatically generate revenue through <a href="https://www.google.com/adsense/support/bin/answer.py?hl=en&answer=105924">AdSense for domains</a>.  And of course the biggest thing I\'m adding is AdSense incorporated into the search.  Both static non-google search results through <a href="http://code.google.com/apis/afs-ads-only/">AdSense for Search Ads Only</a>, and also using the newest <a href="http://code.google.com/apis/afa/">AdSense for Ajax</a>.  Both are basically Google BETA at the moment.</p>';
			

			$this->form_field( 1, 'Add <a href="http://www.askapache.com/seo/updated-robotstxt-for-wordpress.html">robots meta</a> to prevent indexing', 'robots_meta', 'Prevent 404 pages from being indexed.' );
			$this->form_field( 2, 'Robots meta tag value <a href="http://www.askapache.com/seo/updated-robotstxt-for-wordpress.html">(?)</a>', 'robots_tag', 'Value of robots meta tag.' );
			echo '<p class="binfo">This prevents your error pages from being indexed by Google and other search engines, which saves your PageRank for your non-error pages.  Highly recommended, Google recommended.</p>';
			
			
			$this->form_field( 1, 'Display a preview of your 404 page', 'iframe_preview', 'Display a preview of your 404 page.' );
			$this->form_field( 2, 'URL of Preview', 'iframe_url', 'Url (bad) of the preview iframe.' );
			echo '<p class="binfo">Live Preview of your 404 error page.</p>';

		
		echo '</div><!--section-->';




		
		
		echo '<div class="ag4-section" id="section-ajax"><h3 class="hide-if-js">Google Search Results</h3>';
			$this->form_field( 1, 'Show Site Results', 'show_result_site', 'Display Site Results' );	
			$this->form_field( 1, 'Show Video Results', 'show_result_video', 'Display Video Results' );	
			$this->form_field( 1, 'Show Image Results', 'show_result_image', 'Display Image Results' );	
			$this->form_field( 1, 'Show Blogs Results', 'show_result_blogs', 'Display Blogs Results' );	
			$this->form_field( 1, 'Show Web Results', 'show_result_web', 'Display Web Results' );	
			$this->form_field( 1, 'Show News Results', 'show_result_news', 'Display News Results' );	
			$this->form_field( 1, 'Show CSE Results', 'show_result_cse', 'Display CSE Results' );	
			$this->form_field( 1, 'Show Local Results', 'show_result_local', 'Display Local Results' );	
		echo '</div><!--section-->';
		
		

		echo '<div class="ag4-section" id="section-output"><h3 class="hide-if-js">404 Output Options</h3>';
		
	
			$this->form_field( 1, 'Show Google 404 Helper', 'google_404', 'Displays Google New 404 Helper' );
			echo '<p class="binfo">Use Google Webmaster Tools <a href="http://www.google.com/support/webmasters/bin/answer.py?hl=en&answer=136085">404 widget</a> on the error page to automatically provide users with helpful suggestions instead of error messages. </p>';				
			
			
			$this->form_field( 1, 'Show Recent Posts', 'recent_posts', 'Displays List of Recent Posts' );
			$this->form_field( 3, 'Recent Posts # to Show', 'recent_num', 'How many recent posts to show..' );		
			echo '<p class="binfo">Shows a list of Recent Posts on your blog.</p>';
			
			
			$this->form_field( 1, 'Show Related Posts', 'related_posts', 'Displays List of Posts similar to the query' );
			$this->form_field( 3, 'Related Posts # to Show', 'related_num', 'How many related posts to show..' );
			$this->form_field( 3, 'Related Posts Excerpt Length', 'related_length', 'How many related posts to show..' );
			echo '<p class="binfo">Shows a list of single posts on your blog that are related to the keywords auto-parsed from the bad url.</p>';
	
	
			$this->form_field( 1, 'Show Popular Tag Cloud', 'tag_cloud', 'Displays Popular Tag Cloud' );	
			$this->form_field( 3, 'Tag # to Use', 'tag_cloud_num', 'How many tags to use, otherwise ALL tags..' );		
			echo '<p class="binfo">Displays a tag cloud (heatmap) from provided data. of your popular tags where each tag is displayed with a font-size showing how popular the tag is, more popular tags are larger.</p>';
		echo '</div><!--section-->';






		echo '<div class="ag4-section" id="section-tracking"><h3 class="hide-if-js">404 Tracking/Logging</h3>';
				echo '<p>Coming soon...</p>';			
			
			$this->form_field( 1, '<a href="http://code.google.com/mobile/analytics/">Track activity</a> for mobile browsers with Google Analytics', 'mobile_analytics_log', 'Use Google Analytics for Mobile Websites' );
			$this->form_field( 2, 'Google Analytics Key <small>UA-733153-7</small><a href="https://www.google.com/adsense/support/bin/answer.py?answer=45465">Get One</a>', 'mobile_analytics_key', 'The tracking ID for this site.' );
		echo '</div><!--section-->';





		echo '<div class="ag4-section" id="section-html"><h3 class="hide-if-js">HTML Editor</h3>';
		$this->form_field( 5, '', 'html','This controls the output of the plugin.  Move stuff around, change what you want, and load the default if you mess up too much.');
		echo '<p class="binfo">This lets you determine the placement and any extra html you want output by this plugin.<br /><br /><code>%error_title%</code> - replaced with the status code and error phrase - 404 Not Found<br /><code>%related_posts%</code> - replaced with your related posts html if enabled<br /><code>%tag_cloud%</code> - replaced with your tag cloud if enabled<br /><code>%recent_posts%</code> - replaced with the recent posts html if enabled<br /><code>%google_helper%</code> - replaced with the Google Fixurl Help box.</p>';
		echo '</div><!--section-->';



		echo '<div class="ag4-section" id="section-css"><h3 class="hide-if-js">CSS Editor</h3>';
		$this->form_field( 5, '', 'css','The css that controls the google ajax search results.. (and anything else on the page)' );		
		echo '<p class="binfo">Modify the css that is output (inline) on your 404 error pages.  Changes the appearance of, well, everything.</p>';
		echo '</div><!--section-->';



		echo '<div class="ag4-section" id="section-js"><h3 class="hide-if-js">Javascript Editor</h3>';
		$this->form_field( 5, '', 'javascript','The javascript that runs the <a href="http://code.google.com/apis/ajaxsearch/documentation/reference.html">google ajax search</a>.. (and anything else on the page)');		
		echo '<p class="binfo">For advanced users only.  Future versions will provide much more control over this without having to code.</p>';
		echo '</div><!--section-->';
		echo '</div><!--ag4-tabs-->';

		
		echo '<p class="submit hide-if-js"><input type="submit" class="button-primary" name="ag4_action_save" id="ag4_action_save" value="Save Changes &raquo;" />  &nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<input type="submit" class="button-primary" name="ag4_action_reset" id="ag4_action_reset" value="Revert to Defaults &raquo;" /></p>';
		echo '</form><br style="clear:both;" />';
		
		
		// just a temp solution.. will be cleaned for next release
		echo "<form style='display: none' method='post' action='" . admin_url($this->plugin['action']) . "' id='ag4_reset'><p>";
		echo "<input type='hidden' name='ag4_action_reset' id='ag4_action_reset' value='".wp_create_nonce('ag4_action_reset_nonce')."' /></p></form>";
		
		echo "<p><a title='Save Changes' href='#' class='ag4submit hide-if-no-js'><em class='aasubmit-button'>Save</em></a></p>";
		
		
		echo '<p class="hide-if-no-js">';
		echo "<a title='Reset all options including code to the default values, except for the AdSense ID and API Key' href='#' class='ag4reset'><em class='aasubmit-b'>RESET TO DEFAULTS</em></a> &nbsp;&nbsp;&nbsp;&nbsp; ";
		echo "<a title='View the Contextual Help for this plugin.' href='#contextual-help' class='contextualhl'><em class='aasubmit-b'>ADVANCED</em></a>";
		echo '</p>';

		
		
		echo '<div style="width:300px;float:left;"><p><br class="clear" /></p><h3>Articles from AskApache</h3>';
		echo '<ul><li><a href="http://www.askapache.com/seo/seo-secrets.html">SEO Secrets of AskApache.com</a></li>';
		echo '<li><a href="http://www.askapache.com/seo/seo-advanced-pagerank-indexing.html">Controlling Pagerank and Indexing</a></li>';
		echo '<li><a href="http://www.askapache.com/htaccess/htaccess.html">Ultimate .htaccess Tutorial</a></li>';
		echo '<li><a href="http://www.askapache.com/seo/updated-robotstxt-for-wordpress.html">Robots.txt Info for WordPress</a></li></ul></div>';
		echo '<div style="width:400px;float:left;"><h3>More Info from Google</h3><ul><li><a href="http://code.google.com/apis/ajaxsearch/wizards.html">AJAX Search Wizards</a></li>';
		echo '<li><a href="http://code.google.com/apis/ajaxsearch/documentation/">Developer Guide</a></li>';
		echo '<li><a href="http://code.google.com/apis/ajaxsearch/samples.html">Code Samples</a></li>';
		echo '<li><a href="http://code.google.com/apis/ajaxsearch/community-samples.html">Community Samples</a></li>';
		echo '<li><a href="http://code.google.com/support/bin/topic.py?topic=10021">Knowledge Base</a></li><li><a href="http://googleajaxsearchapi.blogspot.com/">AJAX APIs Blog</a></li>';
		echo '<li><a href="http://groups.google.com/group/Google-AJAX-Search-API">Developer Forum</a></li></ul><p><br class="clear" /></p></div>';
		
		echo '</div>';
	}
		
		
	
	

	
	
	/**
	 * Clean way to add html for form fields
	 */
	function form_field($w = 1, $title = '', $id = '', $desc = '')
	{
		echo '<div>';
		switch ($w) :
			case 1: 
				echo "<p class='c4r'><input title='{$desc}' name='ag4_{$id}' size='10' ";
				echo "type='checkbox' id='ag4_{$id}' value='{$this->options[$id]}' " . checked('1', $this->options[$id],false)." />";
				echo "<label title='{$desc}' for='ag4_{$id}'> {$title}</label><br style='clear:both;' /></p>";
			break;
			case 2:
				echo "<p class='c4r'><label title='{$desc}' for='ag4_{$id}'> {$title}:</label><br style='clear:both;' />";
				echo "<input title='{$desc}' name='ag4_{$id}' type='text' id='ag4_{$id}' value='".(isset($this->options[$id]) ? $this->options[$id] : '')."' /><br style='clear:both;' /></p>";
			break;
			case '2j':
				echo "<p class='c4r'><label title='{$desc}' for='ag4_{$id}'> {$title}:</label><br style='clear:both;' />";
				echo "<input title='{$desc}' name='ag4_{$id}' type='text' id='ag4_{$id}' value='".(isset($this->options[$id]) ? stripslashes($this->options[$id]) : '')."' /><br style='clear:both;' /></p>";
			break;
			
			case '2h':
				echo "<p class='c4r hide-if-js'><label title='{$desc}' for='ag4_{$id}'> {$title}:</label><br style='clear:both;' />";
				echo "<input title='{$desc}' name='ag4_{$id}' type='text' id='ag4_{$id}' value='".(isset($this->options[$id]) ? $this->options[$id] : '')."' /><br style='clear:both;' /></p>";
			break;
			case 3:
				echo "<p class='c4r'><input title='{$desc}' name='ag4_{$id}' style='float:left;margin-right:5px;' size='4' type='text' id='ag4_{$id}' ";
				echo "value='".(isset($this->options[$id]) ? $this->options[$id] : '')."' /><label title='{$desc}' for='ag4_{$id}'> {$title}:</label><br style='clear:both;' /></p>";
			break;
			case 5:
				echo "<div><label for='ag4_{$id}'>{$desc}<br /></label><br />{$title}</div>";
				echo "<div><textarea title='{$desc}' cols='70' rows='20' name='ag4_{$id}' id='ag4_{$id}' class='codepress {$id}'>".htmlspecialchars($this->code[$id])."</textarea></div>";
			break;
		endswitch;
		echo '</div>';
	}
		
		
	
	
	/**
	 * A souped-up function that reads the plugin file __FILE__ and based on the plugin data (commented at very top of file) creates an array of vars
	 *
	 * @return array
	 */
	function get_plugin_data()
	{
		$data = $this->_readfile(__FILE__, 1500);
		$mtx = $plugin = array();
		preg_match_all('/[^a-z0-9]+((?:[a-z0-9]{2,25})(?:\ ?[a-z0-9]{2,25})?(?:\ ?[a-z0-9]{2,25})?)\:[\s\t]*(.+)/i', $data, $mtx, PREG_SET_ORDER);
		foreach ($mtx as $m) $plugin[trim(str_replace(' ', '-', strtolower($m[1])))] = str_replace(array("\r", "\n", "\t"), '', trim($m[2]));

		$plugin['title'] = '<a href="' . $plugin['plugin-uri'] . '" title="' . __('Visit plugin homepage') . '">' . $plugin['plugin-name'] . '</a>';
		$plugin['author'] = '<a href="' . $plugin['author-uri'] . '" title="' . __('Visit author homepage') . '">' . $plugin['author'] . '</a>';
		$plugin['pb'] = preg_replace('|^' . preg_quote(WP_PLUGIN_DIR, '|') . '/|', '', __FILE__);
		$plugin['page'] = basename(__FILE__);
		$plugin['pagenice'] = rtrim($plugin['page'], '.php');
		$plugin['nonce'] = 'form_' . $plugin['pagenice'];
		$plugin['hook'] = 'settings_page_' . rtrim($plugin['page'], '.php');
		$plugin['action'] = 'options-general.php?page=' . $plugin['page'];

		if (preg_match_all('#(?:([^\W_]{1})(?:[^\W_]*?\W+)?)?#i', rtrim($plugin['page'], '.php') . '.' . $plugin['version'], $m, PREG_SET_ORDER))$plugin['op'] = '';
		foreach($m as $k) sizeof($k == 2) && $plugin['op'] .= $k[1];
		$plugin['op'] = substr($plugin['op'], 0, 3) . '_';

		return $plugin;
	}
		
		
	
	
	/**
	 * Reads a file with fopen and fread for a binary-safe read.  $f is the file and $b is how many bytes to return, useful when you dont want to read the whole file (saving mem)
	 *
	 * @return string - the content of the file or fread return
	 */
	function _readfile($f, $b = false)
	{
		$fp = NULL;
		$d = '';
		!$b && $b = @filesize($f);
		if (!($b > 0) || !file_exists($f) || !false === ($fp = @fopen($f, 'r')) || !is_resource($fp)) return false;
		if ($b > 4096) while (!feof($fp) && strlen($d) < $b)$d .= @fread($fp, 4096);
		else $d = @fread($fp, $b);
		@fclose($fp);
		return $d;
	}	
}









/**
 * AA_G404_Handler
 * 
 * @package   
 * @author 
 * @copyright Produke
 * @version 2009
 * @access public
 */
class AA_G404_Handler {
	var $reason = '';
	var $uri = '';
	var $sc;
	var $msg = '';

	var $ASC = array(
		400 => "Your browser sent a request that this server could not understand.",
		401 => "This server could not verify that you are authorized to access the document requested.",
		402 => '',
		403 => "You don't have permission to access %U% on this server.",
		404 => "We couldn't find <acronym title='%U%'>that uri</acronym> on our server, though it's most certainly not your fault.",
		405 => "The requested method %M% is not allowed for the URL %U%.",
		406 => "An appropriate representation of the requested resource %U% could not be found on this server.",
		407 => "An appropriate representation of the requested resource %U% could not be found on this server.",
		408 => "Server timeout waiting for the HTTP request from the client.",
		409 => '',
		410 => "The requested resource %U% is no longer available on this server and there is no forwarding address. Please remove all references to this resource.",
		411 => "A request of the requested method GET requires a valid Content-length.",
		412 => "The precondition on the request for the URL %U% evaluated to false.",
		413 => "The requested resource %U% does not allow request data with GET requests, or the amount of data provided in the request exceeds the capacity limit.",
		414 => "The requested URL's length exceeds the capacity limit for this server.",
		415 => "The supplied request data is not in a format acceptable for processing by this resource.",
		416 => 'Requested Range Not Satisfiable',
		417 => "The expectation given in the Expect request-header field could not be met by this server. The client sent <code>Expect:</code>",
		422 => "The server understands the media type of the request entity, but was unable to process the contained instructions.",
		423 => "The requested resource is currently locked. The lock must be released or proper identification given before the method can be applied.",
		424 => "The method could not be performed on the resource because the requested action depended on another action and that other action failed.",
		425 => '',
		426 => "The requested resource can only be retrieved using SSL. Either upgrade your client, or try requesting the page using https://",
		500 => '',
		501 => "%M% to %U% not supported.",
		502 => "The proxy server received an invalid response from an upstream server.",
		503 => "The server is temporarily unable to service your request due to maintenance downtime or capacity problems. Please try again later.",
		504 => "The proxy server did not receive a timely response from the upstream server.",
		505 => '',
		506 => "A variant for the requested resource <code>%U%</code> is itself a negotiable resource. This indicates a configuration error.",
		507 => "The method could not be performed.	There is insufficient free space left in your storage allocation.",
		510 => "A mandatory extension policy in the request is not accepted by the server for this resource."
		);
		
		
	
	
	/**
	 */
	function AA_G404_Handler(){}	
	
	/**
	 */
	function handle_it()
	{
		global $AA_G404, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
		if (is_array($wp_query->query_vars)) extract($wp_query->query_vars, EXTR_SKIP);

		// reset AA_G404_Handler vars
		$this->uri = $this->sc = $this->msg = $this->reason = '';
		$this->uri = esc_attr(stripslashes($_SERVER['REQUEST_URI']));
		$this->sc = absint((isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] != 200) ? $_SERVER['REDIRECT_STATUS'] : (!isset($_REQUEST['error'])) ? 404 : $_REQUEST['error']);

		if ('HTTP/1.1' != $_SERVER["SERVER_PROTOCOL"] && 'HTTP/1.0' != $_SERVER["SERVER_PROTOCOL"] && $_SERVER["SERVER_PROTOCOL"] = 'HTTP/1.0') $this->sc = 505;
		$this->reason = get_status_header_desc($this->sc);
		if ($this->sc == 402 || $this->sc == 409 || $this->sc == 425 || $this->sc == 500 || $this->sc == 505) $this->msg = 'The server encountered an internal error or misconfiguration and was unable to complete your request.';
		else $this->msg = (array_key_exists($this->sc, $this->ASC) !== false) ? str_replace(array('%U%', '%M%'), array($this->uri, $_SERVER['REQUEST_METHOD']), $this->ASC["{$this->sc}"]) : 'Error';

		if ($this->sc == 400 || $this->sc == 403 || $this->sc == 405 || floor($this->sc / 100) == 5) {
			if ($this->sc == 405) @header('Allow: GET,HEAD,POST,OPTIONS,TRACE', 1, 405);
			echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>{$this->sc} {$this->reason}</title>\n<h1>{$this->reason}</h1>\n<p>{$this->msg}<br />\n</p>\n</body></html>";
			return false;
		}

		@header("{$_SERVER['SERVER_PROTOCOL']} {$this->sc} {$this->reason}", 1, $this->sc);
		@header("Status: {$this->sc} {$this->reason}", 1, $this->sc);
		@header("Connection: close", 1);
	}
		
		
	
	
	/**
	 */
	function output()
	{
		global $AA_G404;
		
		// if aa_google_404 function called from within template but plugin not enabled, ditch
		if('1' != $AA_G404->options['enabled']) return '';
		
		$google_helper = (($AA_G404->options['google_404'] == '1') ? '<script type="text/javascript" src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>' . "\n" : '');
		$recent = (($AA_G404->options['recent_posts'] == '1') ? '<ul>' . wp_get_archives(array('echo' => false, 'type' => 'postbypost', 'limit' => absint($AA_G404->options['recent_num']))) . '</ul>' : '');
		$related = (($AA_G404->options['related_posts'] == '1') ? $this->related_posts((int)$AA_G404->options['related_num'], (int)$AA_G404->options['related_length']) : '');
		$tag_cloud = (($AA_G404->options['tag_cloud'] == '1') ? '<p>' . wp_tag_cloud(array('echo' => false)) . '</p>' : '');
		$sr = array(
			'%error_title%' => $this->sc . ' ' . $this->reason,
			'%related_posts%' => $related,
			'%recent_posts%' => $recent,
			'%google_helper%' => $google_helper,
			'%tag_cloud%' => $tag_cloud,
			);
		
		if ($AA_G404->options['google_ajax'] == '1') echo str_replace(array_keys($sr), array_values($sr), $AA_G404->code['html']);
		if ($AA_G404->options['mobile_analytics_log'] == '1')echo $this->mobile_tracker_image();
	}
		
		
	
	
	/**
	 */
	function wp_header()
	{
		if (!is_404()) return;
		global $AA_G404;
		
		if ($AA_G404->options['analytics_log'] == '1') : ?>
		<script type="text/javascript">
		//<![CDATA[
		var _gaq=_gaq||[];_gaq.push(['_setAccount','<?php echo $AA_G404->options["analytics_key"];?>']);_gaq.push(['_trackPageview',<?php echo $AA_G404->options['analytics_url'];?>]);(function(){var ga=document.createElement('script');ga.type='text/javascript';ga.async=true;ga.src=('https:'==document.location.protocol?'https://ssl':'http://www')+'.google-analytics.com/ga.js';var s=document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ga,s);})();
    //]]>
    </script>		
    <?php endif;
		
		if ($AA_G404->options['google_ajax'] == '1') {
			if ($AA_G404->options['show_result_site'] != '1')$AA_G404->code['javascript']=str_replace('this.rCT.addSearcher(gSearchSite);','//this.rCT.addSearcher(gSearchSite);',$AA_G404->code['javascript']);
			if ($AA_G404->options['show_result_video'] != '1')$AA_G404->code['javascript']=str_replace('this.lCT.addSearcher(gSearchVideo, sOPT);','//this.lCT.addSearcher(gSearchVideo, sOPT);',$AA_G404->code['javascript']);
			if ($AA_G404->options['show_result_image'] != '1')$AA_G404->code['javascript']=str_replace('this.lCT.addSearcher(gSearchImage, sOPT);','//this.lCT.addSearcher(gSearchImage, sOPT);',$AA_G404->code['javascript']);
			if ($AA_G404->options['show_result_blogs'] != '1')$AA_G404->code['javascript']=str_replace('this.rCT.addSearcher(gSearchBlog);','//this.rCT.addSearcher(gSearchBlog);',$AA_G404->code['javascript']);
			if ($AA_G404->options['show_result_web'] != '1')$AA_G404->code['javascript']=str_replace('this.rCT.addSearcher(gSearchWeb);','//this.rCT.addSearcher(gSearchWeb);',$AA_G404->code['javascript']);
			if ($AA_G404->options['show_result_news'] != '1')	$AA_G404->code['javascript']=str_replace('this.rCT.addSearcher(gSearchNews);','//this.rCT.addSearcher(gSearchNews);',$AA_G404->code['javascript']);
			if ($AA_G404->options['show_result_cse'] != '1') $AA_G404->code['javascript']=str_replace('this.rCT.addSearcher(gSearchCSE);','//this.rCT.addSearcher(gSearchCSE);',$AA_G404->code['javascript']);
			if ($AA_G404->options['show_result_local'] != '1')$AA_G404->code['javascript']=str_replace('this.rCT.addSearcher(gSearchLocal);','//this.rCT.addSearcher(gSearchLocal);',$AA_G404->code['javascript']);
		}
		
		printf('%9$s%1$s<style type="text/css">%11$s</style><script src="%8$s" type="text/javascript"></script>' .
			'<script type="text/javascript">//<![CDATA[%1$svar aa_LABEL="%2$s";var aa_MYSITE="%3$s";var aa_XX="%4$s";' .
			'var aa_BGLABEL="%5$s";var GOOG_FIXURL_LANG="%6$s";var GOOG_FIXURL_SITE="%7$s";%10$s%1$s//]]></script>%1$s%9$s',
			"\n",
			get_option('blogname'),
			str_replace('www.', '', $_SERVER['HTTP_HOST']),
			$this->get_keywords('|', 6),
			' ',//'OR allinurl:' . $this->get_keywords(' ', 2),
			get_bloginfo('language'),
			get_bloginfo('wpurl'),
			'http://www.google.com/jsapi?key=' . $AA_G404->options['api_key'],
			"<!-- Google 404 Plugin by www.AskApache.com -->",
			str_replace('}', "};", $AA_G404->code['javascript']),
			preg_replace(array('/\0+/','/(\\\\0)+/','/\s\s+/',"/(\r\n|\n|\r)/",'/\/\*(.*?)\*\//','/(:|,|;) /','# +{#','#{ +#','#} +#','# +}#','#;}#','#,+#','# +,#'),array('','',' ',"\n",'',"\\1",'{','{','}','}','}',',',','), $AA_G404->code['css'])
			);


		if($AA_G404->options['robots_meta'] == '1') echo "\n" . '<meta name="robots" content="'.$AA_G404->options['robots_tag'].'" />' . "\n";
	}
		
		
	
	function mobile_tracker_image()
	{
		global $AA_G404;
		
		return '.<img src="'.str_replace("&", "&amp;",'ga.php?utmac=' . $AA_G404->options["mobile_analytics_key"] . "&utmn=" . rand(0, 0x7fffffff) . "&utmr=" . (empty($_SERVER["HTTP_REFERER"]) ? '-' : urlencode($_SERVER["HTTP_REFERER"])). (!empty($_SERVER["REQUEST_URI"]) ? "&utmp=" . urlencode($_SERVER["REQUEST_URI"]) : '') . '&guid=ON').'" height="1" width="1" />';
  }
	
	
	/**
	 */
	function get_keywords($sep, $num = 6)
	{
		$comp_words = $found_words = array();

		$n = preg_match_all("/[\w]{3,15}/", strtolower(html_entity_decode(strip_tags($_SERVER['REQUEST_URI'], ' ' . $_SERVER['QUERY_STRING']))), $found_words);
		if ($n < 1) return $_SERVER['HTTP_HOST'];

		foreach (array_unique((array )$found_words[0]) as $key => $aa_word) $comp_words[] = $aa_word;
		if (sizeof((array )$comp_words) > 0) {
			if (sizeof($comp_words) > $num) array_splice($comp_words, $num + 1);

			return ((sizeof($comp_words) > 0) ? trim(implode($sep, $comp_words)) : $_SERVER['HTTP_HOST']);
		}
	}
		
		
	
	
	/**
	 */
	function related_posts($limit = 15, $l = 120)
	{
		global $wpdb;
		$terms = $rr = $out = '';
		$terms = $this->get_keywords(' ');
		if (strlen($terms) < 3) return;
		$sql = "SELECT ID, post_title, post_content, MATCH (post_title, post_content) AGAINST ('{$terms}') AS `score` FROM {$wpdb->posts} WHERE MATCH (post_title, post_content) AGAINST ('{$terms}') " .
		"AND post_type = 'post' AND post_status = 'publish' AND post_password = '' AND post_date < '" . current_time('mysql') . "' ORDER BY score DESC LIMIT {$limit}";

		$results = $wpdb->get_results($wpdb->prepare($sql));

		if ($results) {
		foreach ($results as $r) {
				$out .= sprintf('%4$s<h4><a href="%1$s" title="%2$s">%2$s</a></h4>%4$s<blockquote cite="%1$s">%4$s<p>%3$s...</p>%4$s</blockquote>%4$s',
					get_permalink($r->ID),
					esc_attr(stripslashes(apply_filters('the_title', $r->post_title))),
					substr(wp_trim_excerpt(stripslashes(strip_tags($r->post_content))), 0, $l),
					"\n");
			}
		}

		return $out;
	}
}







if (!function_exists('aa_google_404')) 
{
	/**
	 */
	function aa_google_404()
	{
		global $AA_G404_Handler, $AA_G404;
		if (!is_object($AA_G404))$AA_G404 = new AA_G404();
		if (!is_object($AA_G404_Handler))$AA_G404_Handler = new AA_G404_Handler();
		$AA_G404_Handler->output();
	}
}

$AA_G404 = new AA_G404();
add_action('init', array(&$AA_G404, 'init'));
add_action('template_redirect', array(&$AA_G404, 'template_redirect'),200);
		
		
	
	
	
	
	
	
	
/**
 * 
 *
 * @return
 */
function aa_google_404_fulltext()
{
	global $wpdb, $AA_G404;
	$wpdb->hide_errors();
	
	$wpdb->query('ALTER TABLE '.$wpdb->posts.' ENGINE = MYISAM;');
	$wpdb->query('ALTER TABLE '.$wpdb->posts.' DROP INDEX post_related');
	$wpdb->query('ALTER TABLE '.$wpdb->posts.' ADD FULLTEXT post_related ( post_title , post_content )');
	$wpdb->show_errors();
	
	if(!is_object($AA_G404))$AA_G404=new AA_G404();
	$AA_G404->upgrade_settings();$AA_G404->default_options();
}
		
		
	
	
if (is_admin()) :
	register_activation_hook(__FILE__, 'aa_google_404_fulltext');
	
	
	//add_filter('screen_meta_screen',
		//create_function('$a', 'if($a == "askapachegoogle")return "settings_page_askapache-google-404";else return $a;'));
	/*add_filter('screen_settings',
		create_function('$a', 'if($a == "settings_page_askapache-google-404")return "settings_page_askapache-google-404";else return $a;'));
	add_filter('screen_layout_columns',
		create_function('$a', 'if(!array_key_exists("settings_page_askapache-google-404",$a))return array_merge($a,array("settings_page_askapache-google-404"=>2));else return $a;'));
	*/
	
	
	add_action('admin_menu',
		create_function('', 'global $AA_G404; if(!is_object($AA_G404))$AA_G404=new AA_G404(); add_options_page( "AskApache Google 404", "AA Google 404", "administrator", "askapache-google-404.php", array(&$AA_G404,"options_page"));'));

	add_filter('plugin_action_links_askapache-google-404/askapache-google-404.php',
		create_function('$l', 'return array_merge(array("<a href=\"options-general.php?page=askapache-google-404.php\">Settings</a>"), $l);'));

	add_action('deactivate_askapache-google-404/askapache-google-404.php',
		create_function('', 'foreach ( array("code", "plugin", "orig_code", "iframe_one_time") as $pn ) delete_option("askapache_google_404_{$pn}" );'));

	add_action('admin_footer-settings_page_askapache-google-404',
		create_function('','$g="";$g.="\n<script type=\"text/javascript\">\nvar codepress_path=\"'.includes_url("js/codepress/").'\";jQuery(\"#ag4_form\").submit(function(){\n";foreach(array("html","css","javascript") as $k)$g.="if (jQuery(\"#ag4_{$k}_cp\").length)jQuery(\"#ag4_{$k}_cp\").val(ag4_{$k}.getCode()).removeAttr(\"disabled\");";$g.="});\n</script>\n";echo $g;'));

endif;

?>