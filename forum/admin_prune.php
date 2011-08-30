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

define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/common_admin.php';


if ($pun_user['g_id'] > PUN_ADMIN)
	message($lang_common['No permission']);


if (isset($_GET['action']) || isset($_POST['prune']) || isset($_POST['prune_comply']))
{
	if (isset($_POST['prune_comply']))
	{
		confirm_referrer('admin_prune.php');

		$prune_from = $_POST['prune_from'];
		$prune_sticky = isset($_POST['prune_sticky']) ? '1' : '0';
		$prune_days = intval($_POST['prune_days']);
		$prune_date = ($prune_days) ? time() - ($prune_days*86400) : -1;

		@set_time_limit(0);

		if ($prune_from == 'all')
		{
			$result = $db->query('SELECT id FROM '.$db->prefix.'forums') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());
			$num_forums = $db->num_rows($result);

			for ($i = 0; $i < $num_forums; ++$i)
			{
				$fid = $db->result($result, $i);

				prune($fid, $prune_sticky, $prune_date);
				update_forum($fid);
			}
		}
		else
		{
			$prune_from = intval($prune_from);
			prune($prune_from, $prune_sticky, $prune_date);
			update_forum($prune_from);
		}

		// Locate any "orphaned redirect topics" and delete them
		$result = $db->query('SELECT t1.id FROM '.$db->prefix.'topics AS t1 LEFT JOIN '.$db->prefix.'topics AS t2 ON t1.moved_to=t2.id WHERE t2.id IS NULL AND t1.moved_to IS NOT NULL') or error('Unable to fetch redirect topics', __FILE__, __LINE__, $db->error());
		$num_orphans = $db->num_rows($result);

		if ($num_orphans)
		{
			for ($i = 0; $i < $num_orphans; ++$i)
				$orphans[] = $db->result($result, $i);

			$db->query('DELETE FROM '.$db->prefix.'topics WHERE id IN('.implode(',', $orphans).')') or error('Unable to delete redirect topics', __FILE__, __LINE__, $db->error());
		}

		redirect('admin_prune.php', 'Messages supprimés. Redirection ...');
	}


	$prune_days = $_POST['req_prune_days'];
	if (!@preg_match('#^\d+$#', $prune_days))
		message('Le nombre de jours d\'ancienneté doit être un entier positif.');

	$prune_date = time() - ($prune_days*86400);
	$prune_from = $_POST['prune_from'];

	// Concatenate together the query for counting number or topics to prune
	$sql = 'SELECT COUNT(id) FROM '.$db->prefix.'topics WHERE last_post<'.$prune_date.' AND moved_to IS NULL';

	if (!$prune_sticky)
		$sql .= ' AND sticky=\'0\'';

	if ($prune_from != 'all')
	{
		$prune_from = intval($prune_from);
		$sql .= ' AND forum_id='.$prune_from;

		// Fetch the forum name (just for cosmetic reasons)
		$result = $db->query('SELECT forum_name FROM '.$db->prefix.'forums WHERE id='.$prune_from) or error('Unable to fetch forum name', __FILE__, __LINE__, $db->error());
		$forum = '"'.pun_htmlspecialchars($db->result($result)).'"';
	}
	else
		$forum = 'tous les forums';

	$result = $db->query($sql) or error('Unable to fetch topic prune count', __FILE__, __LINE__, $db->error());
	$num_topics = $db->result($result);

	if (!$num_topics)
		message('Il n\'y a pas de sujets anciens de '.$prune_days.' jours. Diminuez la valeur de Jours d\'ancienneté et essayez à nouveau.');


	$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Élagage';
	require PUN_ROOT.'header.php';

	generate_admin_menu('prune');

?>
	<div class="blockform">
		<h2><span>Élagage des forums</span></h2>
		<div class="box">
			<form method="post" action="admin_prune.php?action=foo">
				<div class="inform">
					<input type="hidden" name="prune_days" value="<?php echo $prune_days ?>" />
					<input type="hidden" name="prune_sticky" value="<?php echo $prune_sticky ?>" />
					<input type="hidden" name="prune_from" value="<?php echo $prune_from ?>" />
					<fieldset>
						<legend>Confirmez la suppression des messages</legend>
						<div class="infldset">
							<p>Êtes-vous sûr de vouloir supprimer toutes les discussions plus anciennes de <?php echo $prune_days ?> jours de <?php echo $forum ?>? (<?php echo $num_topics ?> discussions)</p>
							<p>ATTENTION ! L'élagage des forums est irréversible, les messages seront définitivement supprimés.</p>
						</div>
					</fieldset>
				</div>
				<p><input type="submit" name="prune_comply" value=" Élaguer " /><a href="javascript:history.go(-1)">Retour</a></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}


else
{
	$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Élagage des forums';
	$required_fields = array('req_prune_days' => 'Days old');
	$focus_element = array('prune', 'req_prune_days');
	require PUN_ROOT.'header.php';

	generate_admin_menu('prune');

?>
	<div class="blockform">
		<h2><span>Élagage des forums</span></h2>
		<div class="box">
			<form id="prune" method="post" action="admin_prune.php?action=foo" onsubmit="return process_form(this)">
				<div class="inform">
				<input type="hidden" name="form_sent" value="1" />
					<fieldset>
						<legend>Supprimer les messages anciens</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Jours d'ancienneté</th>
									<td>
										<input type="text" name="req_prune_days" size="3" maxlength="3" tabindex="1" />
										<span>Le nombre de jours d'ancienneté qu'un sujet doit avoir pour être supprimé. Autrement dit, si vous saisissez 30, tous les sujets qui ne contiennent pas de messages datés de moins de 30 jours seront supprimés.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Supprimer les discussions épinglées</th>
									<td>
										<input type="radio" name="prune_sticky" value="1" tabindex="2" checked="checked" />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="prune_sticky" value="0" />&nbsp;<strong>Non</strong>
										<span>Lorsque cette option est activée les discussions épinglées seront également supprimées.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Forum</th>
									<td>
										<select name="prune_from" tabindex="3">
											<option value="all">Tous les forums</option>
<?php

	$result = $db->query('SELECT c.id AS cid, c.cat_name, f.id AS fid, f.forum_name FROM '.$db->prefix.'categories AS c INNER JOIN '.$db->prefix.'forums AS f ON c.id=f.cat_id WHERE f.redirect_url IS NULL ORDER BY c.disp_position, c.id, f.disp_position') or error('Unable to fetch category/forum list', __FILE__, __LINE__, $db->error());

	$cur_category = 0;
	while ($forum = $db->fetch_assoc($result))
	{
		if ($forum['cid'] != $cur_category)	// Are we still in the same category?
		{
			if ($cur_category)
				echo "\t\t\t\t\t\t\t\t\t\t\t".'</optgroup>'."\n";

			echo "\t\t\t\t\t\t\t\t\t\t\t".'<optgroup label="'.pun_htmlspecialchars($forum['cat_name']).'">'."\n";
			$cur_category = $forum['cid'];
		}

		echo "\t\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$forum['fid'].'">'.pun_htmlspecialchars($forum['forum_name']).'</option>'."\n";
	}

?>
											</optgroup>
										</select>
										<span>Choisissez le forum dans lequel vous voulez effectuer l'élagage.</span>
									</td>
								</tr>
							</table>
							<p class="topspace">Utilisez cette fonctionnalité avec précaution. L'élagage des forums est irréversible. Pour de meilleures performances, au cours du processus d'élagage vous devriez mettre les forums en mode maintenance.</p>
							<div class="fsetsubmit"><input type="submit" name="prune" value=" Élaguer " tabindex="5" /></div>
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
}
