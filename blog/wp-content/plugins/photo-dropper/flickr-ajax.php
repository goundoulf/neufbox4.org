<?php
	require_once(dirname(__FILE__) . "/flickr-functions.php");
	require_once("../../../wp-config.php");
	require_once("../../../wp-includes/wp-db.php");
	header('Cache-Control: no-cache');
	header('Pragma: no-cache');
	
	$flickr_post_table = $wpdb->prefix . "flickr_post";

	$page = (isset($_REQUEST['fpage']) && !empty($_REQUEST['fpage'])) ? $_REQUEST['fpage'] : '1';
		
		
	$exists = $wpdb->get_var("SELECT value FROM $flickr_post_table WHERE name='per_page'");
	if(!empty($exists)) 
		$per_page = $exists;
	else
		$per_page = 5;
	$exists = $wpdb->get_var("SELECT value FROM $flickr_post_table WHERE name='sortbyinteresting'");
	if(!empty($exists)&&$exists==1) 
		$sortbyinteresting = 'interestingness-desc';
	else
		$sortbyinteresting = 'date-posted-desc';
		
	$exists = $wpdb->get_var("SELECT value FROM $flickr_post_table WHERE name='commercial_only'");
	if(!empty($exists)&&$exists==1) 
		$commercial_only = 1;
	else
		$commercial_only = 0;	
	$licences = flickr_post_call('flickr.photos.licenses.getInfo',array());
	
	$temp = array();
	for($i = 1; $i < count($licences['licenses']['license']); $i++) {
				array_push($temp,$i);
	}
	$licence_search = implode(',',$temp);
	
	$licences = $licences['licenses']['license'];
	
	$size = (isset($_REQUEST['size']) && !empty($_REQUEST['size'])) ? $_REQUEST['size'] : "thumbnail";
	
	$params = array('api_key'=>$flickr_post_apikey, 'extras' => 'license,o_dims');
	
	if(isset($_REQUEST['filter']) && !empty($_REQUEST['filter'])) {
			$aTags = split (" ",$_REQUEST['filter']);
			$sTag = implode(',',$aTags);
			
			$params = array_merge($params,array('tags' => $sTag,'tag_mode' => 'all'));
			$flickr_function = 'flickr.photos.search';
	} else {
			$params = array_merge($params, array('license' => $licence_search));
			$flickr_function = 'flickr.photos.getRecent'; 
	}
	
	$params = array_merge($params, array('sort' => $sortbyinteresting)); 
	
	if($commercial_only)
		$params = array_merge($params, array('license' => '4,5,6'));
	else
		$params = array_merge($params, array('license' => $licence_search));
	
	$params = array_merge($params,array('per_page' => $per_page, 'page' => $page));
	$photos = flickr_post_call($flickr_function, $params, true);
	$pages = $photos['photos']['pages'];
	?>
		
		<div id="flickr-browse" style="overflow: auto;">
			
			<?php foreach ($photos['photos']['photo'] as $photo) {
				$owner = flickr_post_call('flickr.people.getInfo',array('user_id' => $photo['owner']));
				
			?>
			<?php
			if($size=="small"){
			?>
			<div id="flickr-<?php echo $photo['id']; ?>" style="float:left;padding: 3px;height:260px;width:240px;">
			<?php
			}else if($size=="medium"){
			?>
			<div id="flickr-<?php echo $photo['id']; ?>" style="float:left;padding: 3px;height:520px;width:500px;">
			<?php
			}else if($size=="original"){
			?>
			<div id="flickr-<?php echo $photo['id']; ?>" style="float:left;padding: 3px;">
			<?php
			}else{
			?>
			<div id="flickr-<?php echo $photo['id']; ?>" style="float:left;padding: 3px;height:140px;width:110px;">
			<?php
			}
			?>
					<?php
					$licence_url = "Creative Commons||http://www.photodropper.com/creative-commons/";
					foreach($licences as $license) {
						if($license['id'] == $photo['license']) {
							$licence_url = "{$license['name']}||{$license['url']}";
						}
					}
					?>	
					<img src="<?php echo flickr_post_photo_url($photo,$size); ?>" id="image-<?php echo $photo['id']; ?>" alt="<?php flickr_replace($photo['title']);?>" />								
					<br /><a href='http://www.flickr.com/photos/<?php echo "{$photo['owner']}/{$photo['id']}/"; ?>' target='_blank'><img src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/photo-dropper/images/newwin.gif" alt="go to Flickr page" /></a>	
					<small><a href='javascript:void(0);' title='Insert small size picture' onclick="return wppd_insertImage('<?php echo flickr_post_photo_url($photo,'thumbnail'); ?>','<?php echo flickr_replace($photo['title']);?>','<?php echo $photo['owner']; ?>','<?php echo $photo['id']; ?>','<?php echo flickr_replace($photo['ownername']);?>','<?php echo $owner['person']['username']['_content'];?>','<?php echo $licence_url;?>');">S</a></small>
					<small><a href='javascript:void(0);' title='Insert medium size picture' onclick="return wppd_insertImage('<?php echo flickr_post_photo_url($photo,'small'); ?>','<?php echo flickr_replace($photo['title']);?>','<?php echo $photo['owner']; ?>','<?php echo $photo['id']; ?>','<?php echo flickr_replace($photo['ownername']);?>','<?php echo $owner['person']['username']['_content'];?>','<?php echo $licence_url;?>');">M</a></small>
					<small><a href='javascript:void(0);' title='Insert large size picture' onclick="return wppd_insertImage('<?php echo flickr_post_photo_url($photo,'medium'); ?>','<?php echo flickr_replace($photo['title']);?>','<?php echo $photo['owner']; ?>','<?php echo $photo['id']; ?>','<?php echo flickr_replace($photo['ownername']);?>','<?php echo $owner['person']['username']['_content'];?>','<?php echo $licence_url;?>');">L</a></small>
					<input type="hidden" name="width-<?php echo $photo['id']; ?>" id="width-<?php echo $photo['id']; ?>" value="<?php echo $photo['o_width'] / $photo['o_height']; ?>" />
			</div>
			<?php } ?>
		</div>
		<div style="clear: both;">&nbsp;</div>
		<div id="flickr-page">
				<?php if($page > 1) :?>				
				<a href="#?filter=<?php echo $_REQUEST['filter']; ?>&amp;fpage=1&amp;size=<?php echo $size;?>" title="&laquo; First Page" onclick="return getPage(this,'wppd-ajax')">First</a>&nbsp;
				<a href="#?filter=<?php echo $_REQUEST['filter']; ?>&amp;fpage=<?php echo $page - 1; ?>&amp;size=<?php echo $size;?>" title="&lsaquo; Previous Page" onclick="return getPage(this,'wppd-ajax')">Previous</a>&nbsp;
				
				<?php endif; ?>
				<?php if($page < $pages) :?>
				
				&nbsp;<a href="#?filter=<?php echo $_REQUEST['filter']; ?>&amp;fpage=<?php echo $page + 1; ?>&amp;size=<?php echo $size;?>" title="Next Page &rsaquo;" onclick="return getPage(this,'wppd-ajax')">Next</a>
				&nbsp;<a href="#?filter=<?php echo $_REQUEST['filter']; ?>&amp;fpage=<?php echo $pages; ?>&amp;size=<?php echo $size;?>" title="Last Page &raquo;" onclick="return getPage(this,'wppd-ajax')">Last</a>
				
				<?php endif; ?>
		</div>
		<div style="clear: both; ">&nbsp;</div>
<?php
?>
