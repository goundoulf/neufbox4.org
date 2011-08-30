<?php
/*
Plugin Name: Photo Dropper
Plugin URI: http://www.photodropper.com/wordpress-plugin/
Description: Lets you add Creative commons licensed Photos to Your Posts from Flickr. By activating this plugin you agree to be fully responsbile for adhering to Creative Commons licenses for all photos you post to your blog.
Version: 1.0.8
Author: Photodropper
Author URI: http://www.photodropper.com/wordpress-plugin/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// Added global scope declarations for variables to cater for WordPress 2.5
global $wpdb, $flickr_post_table, $flickr_post_db_version, $flickr_post_directory, $flickr_options;
$flickr_post_table = $wpdb->prefix . "flickr_post";
$flickr_post_db_version = "1.0";
$flickr_post_directory = dirname(__FILE__);

require_once($flickr_post_directory . "/flickr-functions.php");
$flickr_options = new PDSettings();

// Updated activation hook.
register_activation_hook(__FILE__, 'flickr_post_install');

function flickr_post_install() {
	global $wpdb, $flickr_post_table;
	
	if($wpdb->get_var("SHOW TABLES LIKE '$flickr_post_table'") != $flickr_post_table) {
		
		/* Create Table */
		$sql = "CREATE TABLE $flickr_post_table (
					uid mediumint(9) NOT NULL AUTO_INCREMENT,
					name VARCHAR(55) NOT NULL,
					value VARCHAR(55),
					UNIQUE KEY id (uid)
				);";
		
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);
		
		$insert = "INSERT INTO $flickr_post_table (name, value) VALUES ('commercial_only', '1')";
		$wpdb->query( $insert );
		
		$insert = "INSERT INTO $flickr_post_table (name, value) VALUES ('legacy_support', '1')";
		$wpdb->query( $insert );
	}
	
}


// Hook for adding admin menus
add_action('admin_menu', 'flickr_post_add_pages');

function flickr_post_add_pages() {
	
	wp_enqueue_script('pd-js',get_option('siteurl'). '/wp-content/plugins/photo-dropper/flickr-js.php', array('jquery'));
	add_options_page('Photo Dropper', 'Photo Dropper', 5, __FILE__, 'flickr_post_options_page');
}

function flickr_post_options_page() {
	global $wpdb, $flickr_post_table, $flickr_options;	
	if(!empty($_REQUEST['action'])) {
		switch (intval($_REQUEST['action'])) {
			case 'save':
				// Save Settings
				if(!isset($_REQUEST['fper_page']) || !is_numeric($_REQUEST['fper_page']) || intval($_REQUEST['fper_page']) <= 0) $_REQUEST['fper_page'] = 5;
				$flickr_options->saveSetting('per_page', $_REQUEST['fper_page']);
				
				if(!isset($_REQUEST['commercial_only'])) $_REQUEST['commercial_only'] = 0;
				$flickr_options->saveSetting('commercial_only', $_REQUEST['commercial_only']);
				
				if(!isset($_REQUEST['sortbyinteresting'])) $_REQUEST['sortbyinteresting'] = 0;
				$flickr_options->saveSetting('sortbyinteresting', $_REQUEST['sortbyinteresting']);
				
				$flickr_options->saveSetting('htmlBefore', addslashes($_REQUEST['htmlBefore']));
				$flickr_options->saveSetting('htmlAfter', addslashes($_REQUEST['htmlAfter']));
				$flickr_options->saveSetting('legacy_support', $_REQUEST['flegacy_support']);
			break;
			
		}
	}
	?>
	<div class="wrap">	
		
		<?php if($_REQUEST['action'] == 'save') : ?>
					
			<div id="message" class="updated fade">
				<p><strong>Options Saved!</strong></p>
			</div>
		
		<?php endif; ?>
	
		<div align="left">
			<h3>Optional Settings</h3>
			
			<?php 
			// Load Settings
			if(!isset($_REQUEST['fper_page']) || !is_numeric($_REQUEST['fper_page']) || intval($_REQUEST['fper_page']) <= 0) $_REQUEST['fper_page'] = 5;			
			$exists = $flickr_options->getSetting('per_page');
			if(!empty($exists)) $_REQUEST['fper_page'] = $exists;	
						
			if(!isset($_REQUEST['commercial_only'])) $_REQUEST['commercial_only'] = 0; 
			$exists = $flickr_options->getSetting('commercial_only');
			if(!empty($exists)) $_REQUEST['commercial_only'] = $exists;				
			
			if(!isset($_REQUEST['sortbyinteresting'])) $_REQUEST['sortbyinteresting'] = 0; 
			$exists = $flickr_options->getSetting('sortbyinteresting');
			if(!empty($exists)) $_REQUEST['sortbyinteresting'] = $exists;
			
			$exists = $flickr_options->getSetting('htmlBefore');
			if(!empty($exists)) $_REQUEST['htmlBefore'] = stripslashes($exists);
			
			$exists = $flickr_options->getSetting('htmlAfter');
			if(!empty($exists)) $_REQUEST['htmlAfter'] = stripslashes($exists);
			
			$_REQUEST['flegacy_support'] = $flickr_options->getSetting('legacy_support');
			?>
			<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			<!-- action=3 - Update Options -->
			<input type="hidden" name="action" value="save" />
			
			<strong>General</strong>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="flegacy_support">Use classic interface: </label>
						</th>
						<td>
							<input type="checkbox" name="flegacy_support" id="flegacy_support" value="1" <?php if($_REQUEST['flegacy_support'] == '1') echo 'checked="checked" '; ?>/>
							(If checked, the Photo Dropper interface will be located under the post and not as a media button)
						</td>
					</tr>
				</tbody>
			</table>
			
			<br /><br /><strong>Pages</strong>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="fper_page">Images per page: </label>
						</th>
						<td>
							<input type="text" name="fper_page" id="fper_page" value="<?php echo $_REQUEST['fper_page']; ?>" style="padding: 3px; width: 50px;" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="commercial_only">Show only photos that can be used commercially: </label>
						</th>
						<td>
							<input name="commercial_only" type="checkbox" id="commercial_only" value="1" <?php if($_REQUEST['commercial_only'] == '1') echo 'checked="checked" '; ?>/> (Check this box if your blog is a commercial blog.)
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="sortbyinteresting">Sort photos by "most interesting" </label>
						</th>
						<td>
							<input name="sortbyinteresting" type="checkbox" id="sortbyinteresting" value="1" <?php if($_REQUEST['sortbyinteresting'] == '1') echo 'checked="checked" '; ?>/>
						</td>
					</tr>
				</tbody>
			</table>
			
			<br /><br /><strong>Code Wrapping</strong>
			<!-- Added for version 1.0.4 - Trent Gardner -->
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="htmlBefore">Insert Before: </label>
						</th>
						<td>
							<textarea name="htmlBefore" id="htmlBefore" style="width: 300px; height: 100px;"><?php echo $_REQUEST['htmlBefore']; ?></textarea>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="htmlAfter">Insert After: </label>
						</th>
						<td>
							<textarea name="htmlAfter" id="htmlAfter" style="width: 300px; height: 100px;"><?php echo $_REQUEST['htmlAfter']; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
			
			<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Save Settings') ?> &raquo;" style="font-size: 1.5em;" />
			</p>
			</form>
		</div>
		
	</div>	
<?php	
}

add_action('edit_page_form','flickr_post_insert_widget');
add_action('edit_form_advanced','flickr_post_insert_widget');

function flickr_post_insert_widget() {
	global $flickr_options;
	$legacy = $flickr_options->getSetting('legacy_support');
	if($legacy == 1) :
?>
	<div id="flickr-insert-widget" style="border: 1px solid #cccccc;" class="postbox">
		<h3 class="dbx-handle">Photo Dropper Browse Photos</h3>
		<div id="flickr-content" class="inside">
			<div id="flickr-nav" style="padding: 5px; padding-left: 13px;">
				<label>Keyword: 
				<input type="text" name="filter" id="flickr-filter" value="<?php echo $_REQUEST['filter']; ?>" onkeypress="return keyHit(event);"/>
				</label>
				<input type="hidden" name="fpage" id="flickr-page" value="<?php echo $_REQUEST['fpage']; ?>" />
				<input type="hidden" name="fold_filter" id="flickr-old-filter" value="<?php echo $_REQUEST['filter']; ?>" />
				<?php $sizes = array("square", 'thumbnail', 'small', 'medium', 'original'); ?>
				
				<input type="submit" name="button" value="Search" onclick="return wppd_performFilter('wppd-ajax')" class="button" />
				<div style="width: 100%; height: 1%; clear: both;"></div>
			</div>	
			<div id="wppd-ajax" style="padding: 5px;"></div>
		</div>	
	</div>
	<div style="clear: both;">&nbsp;</div>
<?php
	endif;
}

add_action('admin_head','flickr_post_styles');

function flickr_post_styles() {
	if(stristr($_SERVER['REQUEST_URI'], 'post.php') === false && stristr($_SERVER['REQUEST_URI'], 'page.php') === false && stristr($_SERVER['REQUEST_URI'], 'post-new.php') === false && stristr($_SERVER['REQUEST_URI'], 'page-new.php') === false) return;
?>
	<style type="text/css">
		#flickr-browse {
			max-height: 300px !important;
		}
	</style>
	
<?php
}

/**************************************
 ***** Added 1.0.6 - Media Panel ******
 *********** Trent Gardner ************
 **************************************/
class PDMediaPanel {
	
	function PDMediaPanel() {
		add_action('media_buttons', array($this, 'addMediaButton'), 20);
		add_action('media_upload_flickr', array($this, 'media_upload_flickr'));
        add_action('admin_head_media_upload_flickr_form', array($this, 'addMediaHeader'));
	}
	
	function addMediaButton() {
		global $post_ID, $temp_ID;
        $uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
        $media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";
		
        $flickr_upload_iframe_src = apply_filters('media_flickr_iframe_src', "$media_upload_iframe_src&amp;type=flickr");
        $flickr_title = 'Add Photo via Photo Dropper';
        
        $link_markup = "<a href=\"{$flickr_upload_iframe_src}&amp;tab=flickr&amp;TB_iframe=false&amp;height=300&amp;width=640\" class=\"thickbox\" title=\"$flickr_title\"><img src=\"".get_option('siteurl')."/wp-content/plugins/photo-dropper/images/flickr-media.gif\" alt=\"$flickr_title\" /></a>\n";
    	
        echo $link_markup;
	}
	
	function media_upload_flickr() {
		wp_iframe('media_upload_flickr_form');
	}
    
    function addMediaHeader() { 
    	
    	wp_admin_css('css/media');
    	?>
    	
    	<script type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/photo-dropper/flickr-js.php"></script>
    	
    <?php }
    
    function modifyMediaTab($tabs) {
        return array(
            'flickr' =>  'Photo Dropper'
        );
    }
	
}

global $pd_media_panel;
$pd_media_panel = new PDMediaPanel();

function media_upload_flickr_form() {
	global $wpdb, $type, $tab, $post_mime_types, $pd_media_panel;
	
	add_filter('media_upload_tabs', array($pd_media_panel, 'modifyMediaTab'));
	?>
	
	<div id="media-upload-header">
		<?php media_upload_header(); ?>
	</div>
	
	<div id="flickr-content" class="inside">
		<div id="flickr-nav" style="padding: 5px; padding-left: 13px;">
			<label>Keyword: 
			<input type="text" name="filter" id="flickr-filter" value="<?php echo $_REQUEST['filter']; ?>" onkeypress="return keyHit(event);" style="padding: 3px; margin: 1px;"/>
			</label>
			<input type="hidden" name="fpage" id="flickr-page" value="<?php echo $_REQUEST['fpage']; ?>" />
			<input type="hidden" name="fold_filter" id="flickr-old-filter" value="<?php echo $_REQUEST['filter']; ?>" />
			<?php $sizes = array("square", 'thumbnail', 'small', 'medium', 'original'); ?>
			
			<input type="submit" name="button" value="Search" onclick="return wppd_performFilter('wppd-ajax')" class="button" />
			<div style="width: 100%; height: 1%; clear: both;"></div>
		</div>	
		<div id="wppd-ajax" style="padding: 5px;"></div>
	</div>
	
	<?php
}
?>