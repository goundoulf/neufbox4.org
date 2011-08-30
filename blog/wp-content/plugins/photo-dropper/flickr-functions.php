<?php
$flickr_post_apikey = "d087381c85f6827a767dfb49d3aaa79d";
$flickr_post_secret = "691c10b9eee99113";

function flickr_post_call($method, $params, $sign = false, $rsp_format = "php_serial") {
	
	if(!is_array($params)) $params = array();
	
	global $flickr_post_apikey;
	$call_includes = array( 'api_key'	=> $flickr_post_apikey, 
							'method'	=> $method,
							'format'	=> $rsp_format);
	
	$params = array_merge($call_includes, $params);
	
	if($sign) $params = array_merge($params, array('api_sig' => flickr_post_sig($params)));
	
	$url = "http://api.flickr.com/services/rest/?".flickr_post_encode($params);
	
    	return flickr_post_get_request($url);
    
}

function flickr_post_get_request($url) {
	if(function_exists('curl_init')) {
		$session = curl_init($url);
		curl_setopt($session, CURLOPT_HEADER, false);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($session);
		curl_close($session);
		$rsp_obj = unserialize($response);
	} else {
		$handle = fopen($url, "rb");
		$contents = '';
		while (!feof($handle)) {
			$contents .= fread($handle, 8192);
		}
		fclose($handle);
		$rsp_obj = unserialize($contents);
	}
	return $rsp_obj;
}

function flickr_post_encode($params) {
	$encoded_params = array();

	foreach ($params as $k => $v){
		$encoded_params[] = urlencode($k).'='.urlencode($v);
	}
	
	return implode('&', $encoded_params);
}

function flickr_post_sig($params) {
	ksort($params);
	
	global $flickr_post_secret;
	$api_sig = $flickr_post_secret;
	
	foreach ($params as $k => $v){
		$api_sig .= $k . $v;
	}
	
	return md5($api_sig);
}

function flickr_post_auth_url($frob, $perms) {
	global $flickr_post_apikey;	
	$params = array('api_key' => $flickr_post_apikey, 'perms' => $perms, 'frob' => $frob);
	$params = array_merge($params, array('api_sig' => flickr_post_sig($params)));	
	$url = 'http://flickr.com/services/auth/?'.flickr_post_encode($params);
	return $url;
}

function flickr_post_photo_url($photo, $size) {
	$sizes = array('square' => '_s', 'thumbnail' => '_t', 'small' => '_m', 'medium' => '', 'large' => '_b', 'original' => '_o');
	if(!isset($photo['originalformat']) && strtolower($size) == "original") $size = 'medium';
	if(($size = strtolower($size)) != 'original') {
		$url = "http://farm{$photo['farm']}.static.flickr.com/{$photo['server']}/{$photo['id']}_{$photo['secret']}{$sizes[$size]}.jpg";
	} else {
		$url = "http://farm{$photo['farm']}.static.flickr.com/{$photo['server']}/{$photo['id']}_{$photo['originalsecret']}{$sizes[$size]}.{$photo['originalformat']}";
	}
	return $url;
}

function flickr_replace($string) {
	return str_replace("&amp;amp;","&amp;",str_replace("&","&amp;",str_replace("'","\'",$string))); 
	
}

/**************************************
 *** Added 1.0.6 - Settings Manager ***
 *********** Trent Gardner ************
 **************************************/
class PDSettings {
	var $settings = array();
	
	function getID($setting) {
		global $wpdb, $flickr_post_table;
		
		$uid = $wpdb->get_var("select uid from $flickr_post_table where name='$setting' limit 1");
		return $uid;
	}
	
	function saveSetting($setting, $value) {
		global $wpdb, $flickr_post_table;
		
		$exists = $this->getID($setting);
		if(empty($exists)) {
			$sql = "INSERT INTO $flickr_post_table (name, value) VALUES ('$setting', '$value')";
		} else {
			$sql = "UPDATE $flickr_post_table SET value='$value' WHERE uid=$exists";
		}
		$this->settings[$setting] = $value;
		
		return $wpdb->query($sql);
	}
	
	function getSetting($setting) {
		global $wpdb, $flickr_post_table;
		
		if(!isset($this->settings[$setting])) {
			$value = $wpdb->get_var("select value from $flickr_post_table where name='$setting' limit 1");
			$this->settings[$setting] = $value;
		}
		
		return $this->settings[$setting];
	}
}
?>