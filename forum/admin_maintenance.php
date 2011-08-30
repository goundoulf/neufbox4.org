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

************************************************************************/


// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);
// Tell common.php that we don't want output buffering
define('PUN_DISABLE_BUFFERING', 1);

define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/common_admin.php';


if ($pun_user['g_id'] > PUN_ADMIN)
	message($lang_common['No permission']);


if (isset($_GET['i_per_page']) && isset($_GET['i_start_at']))
{
	$per_page = intval($_GET['i_per_page']);
	$start_at = intval($_GET['i_start_at']);
	if ($per_page < 1 || $start_at < 1)
		message($lang_common['Bad request']);

	@set_time_limit(0);

	// If this is the first cycle of posts we empty the search index before we proceed
	if (isset($_GET['i_empty_index']))
	{
		// This is the only potentially "dangerous" thing we can do here, so we check the referer
		confirm_referrer('admin_maintenance.php');

		$truncate_sql = ($db_type != 'sqlite' && $db_type != 'pgsql') ? 'TRUNCATE TABLE ' : 'DELETE FROM ';
		$db->query($truncate_sql.$db->prefix.'search_matches') or error('Unable to empty search index match table', __FILE__, __LINE__, $db->error());
		$db->query($truncate_sql.$db->prefix.'search_words') or error('Unable to empty search index words table', __FILE__, __LINE__, $db->error());

		// Reset the sequence for the search words (not needed for SQLite)
		switch ($db_type)
		{
			case 'mysql':
			case 'mysqli':
				$result = $db->query('ALTER TABLE '.$db->prefix.'search_words auto_increment=1') or error('Unable to update table auto_increment', __FILE__, __LINE__, $db->error());
				break;

			case 'pgsql';
				$result = $db->query('SELECT setval(\''.$db->prefix.'search_words_id_seq\', 1, false)') or error('Unable to update sequence', __FILE__, __LINE__, $db->error());
		}
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo pun_htmlspecialchars($pun_config['o_board_title']) ?> / Reconstruction des index de recherches ...</title>
<style type="text/css">
body {
	font: 10px Verdana, Arial, Helvetica, sans-serif;
	color: #333333;
	background-color: #FFFFFF
}
</style>
</head>
<body>

Reconstruction des index ... C'est peut être le bon moment pour aller prendre un café :-)<br /><br />

<?php

	require PUN_ROOT.'include/search_idx.php';

	// Fetch posts to process
	$result = $db->query('SELECT id FROM '.$db->prefix.'topics WHERE id>='.$start_at.' ORDER BY id LIMIT '.$per_page) or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());
	$topics = array();
	while ($cur_topic = $db->fetch_row($result))
		$topics[] = $cur_topic[0];

	$result = $db->query('SELECT topic_id, id, message FROM '.$db->prefix.'posts WHERE topic_id IN ('.implode(',', $topics).') ORDER BY topic_id') or error('Unable to fetch topic/post info', __FILE__, __LINE__, $db->error());

	$cur_topic = 0;
	while ($cur_post = $db->fetch_row($result))
	{
		if ($cur_post[0] <> $cur_topic)
		{
			// Fetch subject and ID of first post in topic
			$result2 = $db->query('SELECT p.id, t.subject, MIN(p.posted) AS first FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id WHERE t.id='.$cur_post[0].' GROUP BY p.id, t.subject ORDER BY first LIMIT 1') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
			list($first_post, $subject) = $db->fetch_row($result2);

			$cur_topic = $cur_post[0];
		}

		echo 'Traitement du message <strong>'.$cur_post[1].'</strong> de la discussion <strong>'.$cur_post[0].'</strong><br />'."\n";

		if ($cur_post[1] == $first_post)	// This is the "topic post" so we have to index the subject as well
			update_search_index('post', $cur_post[1], $cur_post[2], $subject);
		else
			update_search_index('post', $cur_post[1], $cur_post[2]);
	}

	// Check if there is more work to do
	$result = $db->query('SELECT id FROM '.$db->prefix.'topics WHERE id>'.$cur_topic.' ORDER BY id ASC LIMIT 1') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());

	$query_str = ($db->num_rows($result)) ? '?i_per_page='.$per_page.'&i_start_at='.$db->result($result) : '';

	$db->end_transaction();
	$db->close();

	exit('<script type="text/javascript">window.location="admin_maintenance.php'.$query_str.'"</script><br />La redirection javaScript a échouée. <a href="admin_maintenance.php'.$query_str.'">Cliquez ici</a> pour continuer.');
}


// Get the first post ID from the db
$result = $db->query('SELECT id FROM '.$db->prefix.'topics ORDER BY id LIMIT 1') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
if ($db->num_rows($result))
	$first_id = $db->result($result);

$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Maintenance';
require PUN_ROOT.'header.php';

generate_admin_menu('maintenance');

?>
	<div class="blockform">
		<h2><span>Maintenance des Forums</span></h2>
		<div class="box">
			<form method="get" action="admin_maintenance.php">
				<div class="inform">
					<fieldset>
						<legend>Reconstruction des index de recherches</legend>
						<div class="infldset">
							<p>Si vous avez ajouté, modifié ou supprimé manuellement des messages dans la base de données ou si vous avez des problèmes avec la  recherche vous devriez reconstruire les index de recherche (supprime les mots inutiles). Pour de meilleures performances, pendant la reconstruction des index, vous devriez mettre vos forums en mode maintenance. <strong>La reconstruction des index de recherches peut prendre beaucoup de temps et augmenter considérablement la charge serveur au cours du processus de reconstruction&nbsp;!</strong></p>
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Discussions par cycle</th>
									<td>
										<input type="text" name="i_per_page" size="7" maxlength="7" value="100" tabindex="1" />
										<span>Le nombre de discussions à traiter par cycle. Si vous saisissez 100, une centaine de discussions sera traitée et ensuite la page sera actualisée. Cela permet d'éviter que le script n'atteigne le temps limite d'exécution pendant le processus de reconstruction.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">ID de la discussion de départ</th>
									<td>
										<input type="text" name="i_start_at" size="7" maxlength="7" value="<?php echo (isset($first_id)) ? $first_id : 0 ?>" tabindex="2" />
										<span>L'ID de discussion de laquelle vous souhaitez lancer la reconstruction. La valeur par défaut est le premier ID disponible dans la base de données. Normalement vous ne devriez pas avoir à changer ceci.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Vider index</th>
									<td class="inputadmin">
										<span><input type="checkbox" name="i_empty_index" value="1" tabindex="3" checked="checked" />&nbsp;&nbsp;Cochez cette option si vous souhaitez que les index de recherches soient vidés avant la reconstruction (voir ci-dessous).</span>
									</td>
								</tr>
							</table>
							<p class="topspace">Lorsque le processus sera terminé vous serez redirigé sur cette page. Il est fortement recommandé que Javascript soit activé sur votre navigateur pour effectuer la reconstruction (pour une redirection automatique lorsqu'un cycle est achevé). Si vous êtes obligé d'abandonner le processus de reconstruction, notez l'ID du dernier sujet traité et saisissez cet ID+1 dans le champ "ID de la discussion de départ" quand/si vous reprennez le processus  ("Vider index" ne doit pas être coché).</p>
							<div class="fsetsubmit"><input type="submit" name="rebuild_index" value=" Reconstruire index " tabindex="4" /></div>
						</div>
					</fieldset>
				</div>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

require PUN_ROOT.'footer.php';
