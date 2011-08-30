<?php
/***********************************************************************

  Copyright (C) 2002-2005  Rickard Andersson (rickard@punbb.org)

  This file is part of PunBB.

  PunBB is free software; you can redistribute it and/or modify it
  under the terms of the GNU General Public License as published
  by the Free Software Foundation; either version 2 of the License,
  or (at your option) any later version.

  PunBB is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston,
  MA  02111-1307  USA

************************************************************************


  INSTRUCTIONS

  Le script extern.php est utiliser pour inclure des informations à 
  propos de vos forums sur des pages externes aux forums et pour 
  syndiquer les discussions récentes via RSS. Le script peut afficher 
  une liste de discussions récentes (triée par messages, dates ou 
  derniers messages), une liste d’utilisateurs actifs ou une 
  collection de statistiques générales. Le script peut être appeler 
  directement par l’intermédiaire d’une URL (pour RSS), de la 
  commande inclue de PHP ou par l’utilisation du Server Side 
  Includes (SSI).

  Le comportement du script est commandé par l’intermédiaire de 
  variables fournies au script dans l’URL. Les différentes variables 
  sont : action (que faut-il afficher), show (combien de discussions 
  afficher), forum (l’ID du forum à sonder pour récupèrer les 
  discussions) et type (sortie comme HTML ou RSS). La seule variable 
  obligatoire est action. Les valeurs de possibles (par defaut) sont :

    action: 
	  active (affiche les discussions récemment actives) (HTML ou RSS)
      new (afficher les plus récentes discussions) (HTML ou RSS)
      online (afficher les utilisateurs en ligne) (HTML)
      online_full (idem, mais inclut une liste complète) (HTML)
      stats (afficher les statistiques des forums) (HTML)

    show:   N’importe qu’elle valeur, nombre entier entre 1 et 50. 
	        Cette variable est ignorées pour la sortie RSS. 15 par 
			défaut.

    fid:    Un ou plusieurs ID de forum (séparés par des virgules). 
	        Si ignorée, des discussions de tous les forums lisibles 
			par les invités seront récupérées.

    nfid:   Un ou plusieurs ID de forum (séparés par des virgules) 
	        qui seront ignorés. Ex. l'ID d'un forum de test.

    type:   RSS. Toute autre chose signifie une sortie en HTML.


  Voici quelques exemples en utilisant la fonction include() de PHP :

    Afficher les 15 discussions les plus récemment actives depuis tous les forums :
    include('http://host.com/forums/extern.php?action=active');

    Afficher les 10 discussions les plus récentes depuis les forums d’ID 5, 6 et 7 :
    include('http://host.com/forums/extern.php?action=new&show=10&fid=5,6,7');

    Afficher les utilisateurs en ligne :
    include('http://host.com/forums/extern.php?action=online');

    Afficher les utilisateurs en ligne avec une liste complète :
    include('http://host.com/forums/extern.php?action=online_full');

    Afficher les statistiques des forums :
    include('http://host.com/forums/extern.php?action=stats');


  Voici quelques exemples en utilisant SSI :

    Afficher les 5 discussions les plus récentes depuis les forums d’ID 11 et 22:
    <!--#include virtual="forums/extern.php?action=new&show=5&fid=11,22" -->

    Afficher les statistiques des forums :
    <!--#include virtual="forums/extern.php?action=stats" -->


  Et finalement quelques exemples en utilisant extern.php pour produire un fil RSS 0.91 :

    Afficher les 15 discussions les plus récemment actives :
    http://host.com/extern.php?action=active&type=RSS

    Afficher les 15 discussions les plus récemment actives depuis le forum d’ID 2:
    http://host.com/extern.php?action=active&type=RSS&fid=2

  Ci-dessous vous trouverez des variables que vous pouvez modifier pour que le 
  script se comporte selon vos besoins.

/***********************************************************************/

// Le nombre maximum de discussions qui seront affichées
$show_max_topics = 60;

// La longueur à laquelle les sujets des discussions seront tronquées (pour HTML)
$max_subject_length = 30;

/***********************************************************************/

// NE MODIFIEZ RIEN AU-DESSOUS DE CETTE LIGNE ! (à moins que vous sachiez ce que vous faites)


define('PUN_ROOT', './');
@include PUN_ROOT.'config.php';

// If PUN isn't defined, config.php is missing or corrupt
if (!defined('PUN'))
	exit('Le fichier "config.php" n\'existe pas ou est endommagé. Veuillez lancer install.php pour installer FluxBB.');


// Make sure PHP reports all errors except E_NOTICE
error_reporting(E_ALL ^ E_NOTICE);

// Turn off magic_quotes_runtime
set_magic_quotes_runtime(0);


// Load the functions script
require PUN_ROOT.'include/functions.php';

// Load DB abstraction layer and try to connect
require PUN_ROOT.'include/dblayer/common_db.php';

// Load cached config
@include PUN_ROOT.'cache/cache_config.php';
if (!defined('PUN_CONFIG_LOADED'))
{
	require PUN_ROOT.'include/cache.php';
	generate_config_cache();
	require PUN_ROOT.'cache/cache_config.php';
}

// Make sure we (guests) have permission to read the forums
$result = $db->query('SELECT g_read_board FROM '.$db->prefix.'groups WHERE g_id=3') or error('Unable to fetch group info', __FILE__, __LINE__, $db->error());
if ($db->result($result) == '0')
	exit('Vous n\'avez pas les permissions');


// Attempt to load the common language file
@include PUN_ROOT.'lang/'.$pun_config['o_default_lang'].'/common.php';
if (!isset($lang_common))
	exit('Il n\'y a pas de pack de langue \''.$pun_config['o_default_lang'].'\' d\'installé. Veuillez ré-installer une langue de ce nom.');

// Check if we are to display a maintenance message
if ($pun_config['o_maintenance'] && !defined('PUN_TURN_OFF_MAINT'))
	maintenance_message();

if (!isset($_GET['action']))
	exit('Aucun paramètre de fourni. Veuillez voir extern.php pour les instructions.');


//
// Converts the CDATA end sequence ]]> into ]]&gt;
//
function escape_cdata($str)
{
	return str_replace(']]>', ']]&gt;', $str);
}


//
// Show recent discussions
//
if ($_GET['action'] == 'active' || $_GET['action'] == 'new')
{
	$order_by = ($_GET['action'] == 'active') ? 't.last_post' : 't.posted';
	$forum_sql = '';

	// Was any specific forum ID's supplied?
	if (isset($_GET['fid']) && $_GET['fid'] != '')
	{
		$fids = explode(',', trim($_GET['fid']));
		$fids = array_map('intval', $fids);

		if (!empty($fids))
			$forum_sql = ' AND f.id IN('.implode(',', $fids).')';
	}

	// Any forum ID's to exclude?
	if (isset($_GET['nfid']) && $_GET['nfid'] != '')
	{
		$nfids = explode(',', trim($_GET['nfid']));
		$nfids = array_map('intval', $nfids);

		if (!empty($nfids))
			$forum_sql = ' AND f.id NOT IN('.implode(',', $nfids).')';
	}

	// Should we output this as RSS?
	if (isset($_GET['type']) && strtoupper($_GET['type']) == 'RSS')
	{
		$rss_description = ($_GET['action'] == 'active') ? $lang_common['RSS Desc Active'] : $lang_common['RSS Desc New'];
		$url_action = ($_GET['action'] == 'active') ? '&amp;action=new' : '';

		// Send XML/no cache headers
		header('Content-Type: text/xml');
		header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		// It's time for some syndication!
		echo '<?xml version="1.0" encoding="'.$lang_common['lang_encoding'].'"?>'."\r\n";
		echo '<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN" "http://my.netscape.com/publish/formats/rss-0.91.dtd">'."\r\n";
		echo '<rss version="0.91">'."\r\n";
		echo '<channel>'."\r\n";
		echo "\t".'<title>'.pun_htmlspecialchars($pun_config['o_board_title']).'</title>'."\r\n";
		echo "\t".'<link>'.$pun_config['o_base_url'].'/</link>'."\r\n";
		echo "\t".'<description>'.pun_htmlspecialchars($rss_description.' '.$pun_config['o_board_title']).'</description>'."\r\n";
		echo "\t".'<language>en-us</language>'."\r\n";

		// Fetch 15 topics
		$result = $db->query('SELECT t.id, t.poster, t.subject, t.posted, t.last_post, f.id AS fid, f.forum_name FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=3) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.moved_to IS NULL'.$forum_sql.' ORDER BY '.$order_by.' DESC LIMIT 15') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

		while ($cur_topic = $db->fetch_assoc($result))
		{
			if ($pun_config['o_censoring'] == '1')
				$cur_topic['subject'] = censor_words($cur_topic['subject']);

			echo "\t".'<item>'."\r\n";
			echo "\t\t".'<title>'.pun_htmlspecialchars($cur_topic['subject']).'</title>'."\r\n";
			echo "\t\t".'<link>'.$pun_config['o_base_url'].'/viewtopic.php?id='.$cur_topic['id'].$url_action.'</link>'."\r\n";
			echo "\t\t".'<description><![CDATA['.escape_cdata($lang_common['Forum'].': <a href="'.$pun_config['o_base_url'].'/viewforum.php?id='.$cur_topic['fid'].'">'.$cur_topic['forum_name'].'</a><br />'."\r\n".$lang_common['Author'].': '.$cur_topic['poster'].'<br />'."\r\n".$lang_common['Posted'].': '.date('r', $cur_topic['posted']).'<br />'."\r\n".$lang_common['Last post'].': '.date('r', $cur_topic['last_post'])).']]></description>'."\r\n";
			echo "\t".'</item>'."\r\n";
		}

		echo '</channel>'."\r\n";
		echo '</rss>';
	}


	// Output regular HTML
	else
	{
		$show = isset($_GET['show']) ? intval($_GET['show']) : 15;
		if ($show < 1 || $show > 50)
			$show = 15;

		// Fetch $show topics
		$result = $db->query('SELECT t.id, t.subject FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=3) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.moved_to IS NULL'.$forum_sql.' ORDER BY '.$order_by.' DESC LIMIT '.$show) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

		while ($cur_topic = $db->fetch_assoc($result))
		{
			if ($pun_config['o_censoring'] == '1')
				$cur_topic['subject'] = censor_words($cur_topic['subject']);

			if (pun_strlen($cur_topic['subject']) > $max_subject_length)
				$subject_truncated = pun_htmlspecialchars(trim(substr($cur_topic['subject'], 0, ($max_subject_length-5)))).' ...';
			else
				$subject_truncated = pun_htmlspecialchars($cur_topic['subject']);

			echo '<li><a href="'.$pun_config['o_base_url'].'/viewtopic.php?id='.$cur_topic['id'].'&amp;action=new" title="'.pun_htmlspecialchars($cur_topic['subject']).'">'.$subject_truncated.'</a></li>'."\n";
		}
	}

	return;
}


//
// Show users online
//
else if ($_GET['action'] == 'online' || $_GET['action'] == 'online_full')
{
	// Load the index.php language file
	require PUN_ROOT.'lang/'.$pun_config['o_default_lang'].'/index.php';
	
	// Fetch users online info and generate strings for output
	$num_guests = $num_users = 0;
	$users = array();
	$result = $db->query('SELECT user_id, ident FROM '.$db->prefix.'online WHERE idle=0 ORDER BY ident', true) or error('Unable to fetch online list', __FILE__, __LINE__, $db->error());

	while ($pun_user_online = $db->fetch_assoc($result))
	{
		if ($pun_user_online['user_id'] > 1)
		{
			$users[] = '<a href="'.$pun_config['o_base_url'].'/profile.php?id='.$pun_user_online['user_id'].'">'.pun_htmlspecialchars($pun_user_online['ident']).'</a>';
			++$num_users;
		}
		else
			++$num_guests;
	}

	echo $lang_index['Guests online'].': '.$num_guests.'<br />';

	if ($_GET['action'] == 'online_full')
		echo $lang_index['Users online'].': '.implode(', ', $users).'<br />';
	else
		echo $lang_index['Users online'].': '.$num_users.'<br />';

	return;
}


//
// Show board statistics
//
else if ($_GET['action'] == 'stats')
{
	// Load the index.php language file
	require PUN_ROOT.'lang/'.$pun_config['o_default_lang'].'/index.php';

	// Collect some statistics from the database
	$result = $db->query('SELECT COUNT(id)-1 FROM '.$db->prefix.'users') or error('Unable to fetch total user count', __FILE__, __LINE__, $db->error());
	$stats['total_users'] = $db->result($result);

	$result = $db->query('SELECT id, username FROM '.$db->prefix.'users ORDER BY registered DESC LIMIT 1') or error('Unable to fetch newest registered user', __FILE__, __LINE__, $db->error());
	$stats['last_user'] = $db->fetch_assoc($result);

	$result = $db->query('SELECT SUM(num_topics), SUM(num_posts) FROM '.$db->prefix.'forums') or error('Unable to fetch topic/post count', __FILE__, __LINE__, $db->error());
	list($stats['total_topics'], $stats['total_posts']) = $db->fetch_row($result);

	echo $lang_index['No of users'].': '.$stats['total_users'].'<br />';
	echo $lang_index['Newest user'].': <a href="'.$pun_config['o_base_url'].'/profile.php?id='.$stats['last_user']['id'].'">'.pun_htmlspecialchars($stats['last_user']['username']).'</a><br />';
	echo $lang_index['No of topics'].': '.$stats['total_topics'].'<br />';
	echo $lang_index['No of posts'].': '.$stats['total_posts'];

	return;
}


else
	exit('Bad request');
