<?php
/**
  * BLOG:CMS: PHP/MySQL Personal Content Management System
  * http://blogcms.com/
  * http://forum.blogcms.com/
  *
  * 2003-2004, (c) Radek HULAN
  * http://hulan.cz/
  *
  * Mod by Bert Garcia for PunBB 1.2.1
  * http://nupusi.com
  *
  * Additional changes Copyright 2005 by Alex King
  * Now creates feeds for categories, forums, topics
  * http://www.alexking.org/software/
  *
  * Additional changes Copyright 2007 by Goundoulf
  * http://www.neufbox4.org
  * Now creates a more readable output in feed readers
  * Adds multilingual support, and styling support
  * And is now valid according to http://validator.w3.org/feed/
  *
  * Please see the enclosed readme.txt file for usage
  *
  * This program is free software; you can redistribute it and/or
  * modify it under the terms of the GNU General Public License
  * as published by the Free Software Foundation; either version 2
  * of the License, or (at your option) any later version.

// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// *****************************************************************

**/

// RSS plugin config
$rss_config['language']                 = 'French';
$rss_config['post_count']               = '15';
$rss_config['header_background_color']  = '#3371a3';
$rss_config['header_text_color']        = '#fff';
$rss_config['content_background_color'] = '#dedfdf';
$rss_config['content_text_color']       = '#000';
$rss_config['feed_language']            = 'fr';
$rss_config['feed_ttl']                 = '60';

define('PUN_ROOT', './');
@include PUN_ROOT . 'config.php';

// If PUN isn't defined, config.php is missing or corrupt
if (!defined('PUN'))
{
	exit('The file \'config.php\' doesn\'t exist or is corrupt. Please run install.php to install PunBB first.');
}

// Disable error reporting for uninitialized variables
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Turn off magic_quotes_runtime
set_magic_quotes_runtime(0);

// Load the functions script
require PUN_ROOT . 'include/functions.php';
require PUN_ROOT . 'include/parser.php';

// Load DB abstraction layer and try to connect
require PUN_ROOT . 'include/dblayer/common_db.php';

// Attempt to load the rss.php language file
@include PUN_ROOT . 'lang/' . $rss_config['language'] . '/rss.php';

if (!isset($lang_rss))
{
	exit('The \'' . $rss_config['language'] . '\' RSS language pack is missing.');
}

// Get the forum config
$result = $db->query('SELECT * FROM ' . $db->prefix . 'config') or error('Unable to fetch forum config', __FILE__, __LINE__, $db->error());
while ($cur_config_item = $db->fetch_row($result))
{
	$pun_config[$cur_config_item[0]] = $cur_config_item[1];
}

// Make sure we (guests) have permission to read the forums
$result = $db->query('SELECT g_read_board FROM ' . $db->prefix . 'groups WHERE g_id=3') or error('Unable to fetch group info', __FILE__, __LINE__, $db->error());
if ($db->result($result) == '0')
{
	exit('No permission');
}

// Attempt to load the common language file
@include PUN_ROOT . 'lang/' . $pun_config['o_default_lang'] . '/common.php';
if (!isset($lang_common))
{
	exit('There is no valid language pack \'' . $pun_config['o_default_lang'] . '\' installed. Please reinstall a language of that name.');
}

// Parse RSS
ob_start();

// Make feed
if (!empty($_GET['cid']))
{
	$where = '	AND c.id = \'' . intval($_GET['cid']) . '\'';
	$what = 'cid';
}
else if (!empty($_GET['fid']))
{
	$where = '	AND f.id = \'' . intval($_GET['fid']) . '\'';
	$what = 'fid';
}
else if (!empty($_GET['tid']))
{
	$where = '	AND t.id = \'' . intval($_GET['tid']) . '\'';
	$what = 'tid';
}
else
{
	$where = '';
	$what = '';
}

$query = '
	SELECT p.id AS post_id, p.message AS post_text, p.posted AS post_date, p.poster_id AS poster_id, p.poster AS poster_name, p.topic_id AS topic_id, t.subject AS topic_name, t.forum_id AS forum_id, f.forum_name AS forum_name, c.cat_name AS cat_name
	FROM ' . $db->prefix . 'posts p
	LEFT JOIN ' . $db->prefix . 'topics t
	ON p.topic_id=t.id
	INNER JOIN ' . $db->prefix . 'forums AS f
	ON f.id=t.forum_id
	LEFT JOIN ' . $db->prefix . 'categories AS c
	ON f.cat_id = c.id
	LEFT JOIN ' . $db->prefix . 'forum_perms AS fp
	ON (
		fp.forum_id=f.id
		AND fp.group_id=3
	)
	WHERE (
		fp.read_forum IS NULL
		OR fp.read_forum=1
	)
	' . $where . '
	ORDER BY post_date DESC
	LIMIT 0,' . $rss_config['post_count'];

$result = $db->query($query) or error('Unable to fetch forum posts', __FILE__, __LINE__, $db->error());

$i = 0;
while ($cur = $db->fetch_assoc($result))
{
	if ($i == 0)
	{
		putHeader($cur, $what);
		$i++;
	}

	putPost($cur);
}
putEnd();

// Get feed into $feed
$feed = ob_get_contents();
ob_end_clean();

// Send XML/no cache headers
header('Content-Type: application/rss+xml');
header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// It's time for some syndication!
echo $feed;

//
// Convert BBCodes to their HTML equivalent
//
function do_bbcode_rss($text)
{
	global $lang_common, $pun_user;

	if (strpos($text, 'quote') !== false)
	{
		$text = str_replace('[quote]', '</p><blockquote style="margin: 5px 15px 15px; padding: 8px; border-style: solid; border-width: 1px; border-color: #ACA899 rgb(255, 255, 255) rgb(255, 255, 255) rgb(172, 168, 153); background-color: #F1F1F1;"><div class="incqbox"><p style="padding: 0;">', $text);
		$text = preg_replace('#\[quote=(&quot;|"|\'|)(.*)\\1\]#seU', '"</p><blockquote style=\"margin: 5px 15px 15px; padding: 8px; border-style: solid; border-width: 1px; border-color: #ACA899 rgb(255, 255, 255) rgb(255, 255, 255) rgb(172, 168, 153); background-color: #F1F1F1;\"><div class=\"incqbox\"><h4 style=\"font-weight: bold; font-size: 1em; margin: 0 0 10px;\">".str_replace(array(\'[\', \'\\"\'), array(\'&#91;\', \'"\'), \'$2\')." ".$lang_common[\'wrote\'].":</h4><p style=\"margin: 0;\">"', $text);
		$text = preg_replace('#\[\/quote\]\s*#', '</p></div></blockquote><p style="margin: 0;">', $text);
	}

	$pattern = array('#\[b\](.*?)\[/b\]#s',
					 '#\[i\](.*?)\[/i\]#s',
					 '#\[u\](.*?)\[/u\]#s',
					 '#\[url\]([^\[]*?)\[/url\]#e',
					 '#\[url=([^\[]*?)\](.*?)\[/url\]#e',
					 '#\[email\]([^\[]*?)\[/email\]#',
					 '#\[email=([^\[]*?)\](.*?)\[/email\]#',
					 '#\[color=([a-zA-Z]*|\#?[0-9a-fA-F]{6})](.*?)\[/color\]#s');

	$replace = array('<strong>$1</strong>',
					 '<em>$1</em>',
					 '<span class="bbu">$1</span>',
					 'handle_url_tag(\'$1\')',
					 'handle_url_tag(\'$1\', \'$2\')',
					 '<a href="mailto:$1">$1</a>',
					 '<a href="mailto:$1">$2</a>',
					 '<span style="color: $1">$2</span>');

	// This thing takes a while! :)
	$text = preg_replace($pattern, $replace, $text);

	return $text;
}

function parse_message_rss($text, $hide_smilies)
{
	global $pun_config, $lang_common, $pun_user;

	if ($pun_config['o_censoring'] == '1')
		$text = censor_words($text);

	// Convert applicable characters to HTML entities
	$text = pun_htmlspecialchars($text);

	// If the message contains a code tag we have to split it up (text within [code][/code] shouldn't be touched)
	if (strpos($text, '[code]') !== false && strpos($text, '[/code]') !== false)
	{
		list($inside, $outside) = split_text($text, '[code]', '[/code]');
		$outside = array_map('ltrim', $outside);
		$text = implode('<">', $outside);
	}

	if ($pun_config['o_make_links'] == '1')
		$text = do_clickable($text);

	if ($pun_config['o_smilies'] == '1' && $pun_user['show_smilies'] == '1' && $hide_smilies == '0')
		$text = do_smilies($text);

	if ($pun_config['p_message_bbcode'] == '1' && strpos($text, '[') !== false && strpos($text, ']') !== false)
	{
		$text = do_bbcode_rss($text);

		if ($pun_config['p_message_img_tag'] == '1')
		{
			$text = preg_replace('#\[img\]((ht|f)tps?://)([^\s<"]*?)\[/img\]#e', 'handle_img_tag(\'$1$3\')', $text);
		}
	}

	// Deal with newlines, tabs and multiple spaces
	$pattern = array("\n", "\t", '  ', '  ');
	$replace = array('<br />', '&nbsp; &nbsp; ', '&nbsp; ', ' &nbsp;');
	$text = str_replace($pattern, $replace, $text);

	// If we split up the message before we have to concatenate it together again (code tags)
	if (isset($inside))
	{
		$outside = explode('<">', $text);
		$text = '';

		$num_tokens = count($outside);

		for ($i = 0; $i < $num_tokens; ++$i)
		{
			$text .= $outside[$i];
			if (isset($inside[$i]))
			{
				$num_lines = ((substr_count($inside[$i], "\n")) + 3) * 1.5;
				$height_str = ($num_lines > 35) ? '35em' : $num_lines.'em';
				$text .= '</p><div class="codebox" style="margin: 5px 15px 15px; padding: 8px; border-style: solid; border-width: 1px; border-color: #ACA899 rgb(255, 255, 255) rgb(255, 255, 255) rgb(172, 168, 153); background-color: #F1F1F1;"><div class="incqbox" style="padding: 0; margin: 0;"><h4 style="font-weight: bold; font-size: 1em; margin: 0 0 10px;">'.$lang_common['Code'].':</h4><div class="scrollbox" style="height: '.$height_str.'"><pre style="font-size: 1.2em; font-family: monaco,courier,monospace; margin: 0;">'.$inside[$i].'</pre></div></div></div><p style="margin: 0;">';
			}
		}
	}

	// Add paragraph tag around post, but make sure there are no empty paragraphs
	$text = str_replace('<p style="margin: 0;"></p>', '', '<p style="margin: 0;">'.$text.'</p>');

	return $text;
}

// Taken from MediaWiki
function encodeXml($string)
{
	$string = str_replace("\r\n", "\n", $string);
	$string = preg_replace('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', '', $string);

	return htmlspecialchars($string);
}

function putHeader($cur, $what)
{
	global $lang_common, $lang_rss, $pun_config, $rss_config;

	switch ($what)
	{
		case 'cid':
			$what = $lang_rss['Last messages in category'] . ' ' . $cur['cat_name'];
			break;
		case 'fid':
			$what = $lang_rss['Last messages in forum'] . ' ' . $cur['forum_name'];
			break;
		case 'tid':
			$what = $lang_rss['Last messages in topic'] . ' ' . $cur['topic_name'];
			break;
		default:
			$what = $lang_rss['Last messages'];
			break;
	}

	echo '<?xml version="1.0" encoding="' . $lang_common['lang_encoding'] . '" ?>' . "\n";
	echo '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n\n";

	echo "\t" . '<channel>' . "\n\n";

	echo "\t\t" . '<title>' . encodeXml($pun_config['o_board_title']) . '</title>' . "\n";
	echo "\t\t" . '<link>' . encodeXml($pun_config['o_base_url']) . '</link>' . "\n";
	echo "\t\t" . '<description>' . encodeXml($what) . '</description>' . "\n";
	echo "\t\t" . '<language>' . encodeXml($rss_config['feed_language']) . '</language>' . "\n";
	echo "\t\t" . '<docs>' . encodeXml('http://www.rssboard.org/rss-specification') . '</docs>' . "\n";
	echo "\t\t" . '<ttl>' . encodeXml($rss_config['feed_ttl']) . '</ttl>' . "\n";
	echo "\t\t" . '<atom:link href="' . encodeXml($pun_config['o_base_url'] . '/rss.php') . '" rel="self" type="application/rss+xml" />' . "\n\n";
}

function putPost($cur)
{
	global $lang_rss, $pun_config, $rss_config;

	$link_topic = $pun_config['o_base_url'] . '/viewtopic.php?pid=' . strval($cur['post_id']) . '#p' . strval($cur['post_id']);
	$link_poster = $pun_config['o_base_url'] . '/profile.php?id=' . strval($cur['poster_id']);
	$link_forum = $pun_config['o_base_url'] . '/viewforum.php?id=' . strval($cur['forum_id']);
	$link_comment = $pun_config['o_base_url'] . '/post.php?tid=' . strval($cur['forum_id']);

	echo "\t\t" . '<item>' . "\n";
	echo "\t\t\t" . '<title>' . encodeXml($cur['topic_name']) . '</title>' . "\n";
	echo "\t\t\t" . '<link>' . encodeXml($link_topic) . '</link>' . "\n";

	$data = '<div style="border: 1px solid ' . $rss_config['header_background_color'] . '; background-color: ' . $rss_config['content_background_color'] . ';">';
	$data .= '<div style="background-color: ' . $rss_config['header_background_color'] . '; color: ' . $rss_config['header_text_color'] . '; padding: 4px 8px; margin: 0;">';
	$data .= $lang_rss['Posted by'] . ' ' . '<strong><a href="' . $link_poster . '" style="color: ' . $rss_config['header_text_color'] . ';">' . $cur['poster_name'] . '</a></strong>';
	$data .= ' ' . $lang_rss['in'] . ' ' . '<strong><a href="' . $link_forum . '" style="color: ' . $rss_config['header_text_color'] . ';">' . $cur['forum_name'] . '</a></strong>';
	$data .= ' - <strong><a href="' . $link_topic . '" style="color: ' . $rss_config['header_text_color'] . ';">' . $cur['topic_name'] . '</a></strong>';
	$data .= '</div>';
	$data .= '<div style="color: ' . $rss_config['content_text_color'] . '; padding: 8px;">';
	$data .= parse_message_rss($cur['post_text'], 0);
	$data .= '</div>';
	$data .= '</div>';
	echo "\t\t\t" . '<description>' . encodeXml($data) . '</description>' . "\n";

	echo "\t\t\t" . '<dc:creator>' . encodeXml($cur['poster_name']) . '</dc:creator>' . "\n";
	echo "\t\t\t" . '<comments>' . encodeXml($link_comment) . '</comments>' . "\n";
	echo "\t\t\t" . '<guid isPermaLink="true">' . encodeXml($link_topic) . '</guid>' . "\n";
	echo "\t\t\t" . '<pubDate>' . encodeXml(strval(date('r', $cur['post_date']))) . '</pubDate>' . "\n";
	echo "\t\t" . '</item>' . "\n\n";
}

function putEnd()
{
	echo "\t" . '</channel>' . "\n\n";

	echo '</rss>' . "\n";
}

?>
