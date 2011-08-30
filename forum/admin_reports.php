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


if ($pun_user['g_id'] > PUN_MOD)
	message($lang_common['No permission']);


// Zap a report
if (isset($_POST['zap_id']))
{
	confirm_referrer('admin_reports.php');

	$zap_id = intval(key($_POST['zap_id']));

	$result = $db->query('SELECT zapped FROM '.$db->prefix.'reports WHERE id='.$zap_id) or error('Unable to fetch report info', __FILE__, __LINE__, $db->error());
	$zapped = $db->result($result);

	if ($zapped == '')
		$db->query('UPDATE '.$db->prefix.'reports SET zapped='.time().', zapped_by='.$pun_user['id'].' WHERE id='.$zap_id) or error('Unable to zap report', __FILE__, __LINE__, $db->error());

	redirect('admin_reports.php', 'Signalement ignoré. Redirection ...');
}


$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Signalements';
require PUN_ROOT.'header.php';

generate_admin_menu('reports');

?>
	<div class="blockform">
		<h2><span>Nouveaux signalements</span></h2>
		<div class="box">
			<form method="post" action="admin_reports.php?action=zap">
<?php

$result = $db->query('SELECT r.id, r.post_id, r.topic_id, r.forum_id, r.reported_by, r.created, r.message, t.subject, f.forum_name, u.username AS reporter FROM '.$db->prefix.'reports AS r LEFT JOIN '.$db->prefix.'topics AS t ON r.topic_id=t.id LEFT JOIN '.$db->prefix.'forums AS f ON r.forum_id=f.id LEFT JOIN '.$db->prefix.'users AS u ON r.reported_by=u.id WHERE r.zapped IS NULL ORDER BY created DESC') or error('Unable to fetch report list', __FILE__, __LINE__, $db->error());

if ($db->num_rows($result))
{
	while ($cur_report = $db->fetch_assoc($result))
	{
		$reporter = ($cur_report['reporter'] != '') ? '<a href="profile.php?id='.$cur_report['reported_by'].'">'.pun_htmlspecialchars($cur_report['reporter']).'</a>' : 'Utilisateur supprimé';
		$forum = ($cur_report['forum_name'] != '') ? '<a href="viewforum.php?id='.$cur_report['forum_id'].'">'.pun_htmlspecialchars($cur_report['forum_name']).'</a>' : 'Supprimé';
		$topic = ($cur_report['subject'] != '') ? '<a href="viewtopic.php?id='.$cur_report['topic_id'].'">'.pun_htmlspecialchars($cur_report['subject']).'</a>' : 'Supprimé';
		$post = ($cur_report['post_id'] != '') ? str_replace("\n", '<br />', pun_htmlspecialchars($cur_report['message'])) : 'Supprimé';
		$postid = ($cur_report['post_id'] != '') ? '<a href="viewtopic.php?pid='.$cur_report['post_id'].'#p'.$cur_report['post_id'].'">Post #'.$cur_report['post_id'].'</a>' : 'Supprimé';

?>
				<div class="inform">
					<fieldset>
						<legend>Signalé le <?php echo format_time($cur_report['created']) ?></legend>
						<div class="infldset">
							<table cellspacing="0">
								<tr>
									<th scope="row">Forum&nbsp;&raquo;&nbsp;Discussion&nbsp;&raquo;&nbsp;Message</th>
									<td><?php echo $forum ?>&nbsp;&raquo;&nbsp;<?php echo $topic ?>&nbsp;&raquo;&nbsp;<?php echo $postid ?></td>
								</tr>
								<tr>
									<th scope="row">Signalé par <?php echo $reporter ?><div><input type="submit" name="zap_id[<?php echo $cur_report['id'] ?>]" value=" Ignorer " /></div></th>
									<td><?php echo $post ?></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
<?php

	}
}
else
	echo "\t\t\t\t".'<p>Il n\'y a pas de nouveaux signalements.</p>'."\n";

?>
			</form>
		</div>
	</div>

	<div class="blockform block2">
		<h2><span>10 derniers signalements ignorés</span></h2>
		<div class="box">
			<div class="fakeform">
<?php

$result = $db->query('SELECT r.id, r.post_id, r.topic_id, r.forum_id, r.reported_by, r.message, r.zapped, r.zapped_by AS zapped_by_id, t.subject, f.forum_name, u.username AS reporter, u2.username AS zapped_by FROM '.$db->prefix.'reports AS r LEFT JOIN '.$db->prefix.'topics AS t ON r.topic_id=t.id LEFT JOIN '.$db->prefix.'forums AS f ON r.forum_id=f.id LEFT JOIN '.$db->prefix.'users AS u ON r.reported_by=u.id LEFT JOIN '.$db->prefix.'users AS u2 ON r.zapped_by=u2.id WHERE r.zapped IS NOT NULL ORDER BY zapped DESC LIMIT 10') or error('Impossible de retrouver la liste des signalements', __FILE__, __LINE__, $db->error());

if ($db->num_rows($result))
{
	while ($cur_report = $db->fetch_assoc($result))
	{
		$reporter = ($cur_report['reporter'] != '') ? '<a href="profile.php?id='.$cur_report['reported_by'].'">'.pun_htmlspecialchars($cur_report['reporter']).'</a>' : 'Utilisateur supprimé';
		$forum = ($cur_report['forum_name'] != '') ? '<a href="viewforum.php?id='.$cur_report['forum_id'].'">'.pun_htmlspecialchars($cur_report['forum_name']).'</a>' : 'Supprimé';
		$topic = ($cur_report['subject'] != '') ? '<a href="viewtopic.php?id='.$cur_report['topic_id'].'">'.pun_htmlspecialchars($cur_report['subject']).'</a>' : 'Supprimé';
		$post = ($cur_report['post_id'] != '') ? str_replace("\n", '<br />', pun_htmlspecialchars($cur_report['message'])) : 'Supprimé';
		$post_id = ($cur_report['post_id'] != '') ? '<a href="viewtopic.php?pid='.$cur_report['post_id'].'#p'.$cur_report['post_id'].'">Post #'.$cur_report['post_id'].'</a>' : 'Supprimé';
		$zapped_by = ($cur_report['zapped_by'] != '') ? '<a href="profile.php?id='.$cur_report['zapped_by_id'].'">'.pun_htmlspecialchars($cur_report['zapped_by']).'</a>' : 'N/A';

?>
				<div class="inform">
					<fieldset>
						<legend>Zapped <?php echo format_time($cur_report['zapped']) ?></legend>
						<div class="infldset">
							<table cellspacing="0">
								<tr>
									<th scope="row">Forum&nbsp;&raquo;&nbsp;Discussion&nbsp;&raquo;&nbsp;Message</th>
									<td><?php echo $forum ?>&nbsp;&raquo;&nbsp;<?php echo $topic ?>&nbsp;&raquo;&nbsp;<?php echo $post_id ?></td>
								</tr>
								<tr>
									<th scope="row">Signalé par <?php echo $reporter ?><div class="topspace">Ignoré par <?php echo $zapped_by ?></div></th>
									<td><?php echo $post ?></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
<?php

	}
}
else
	echo "\t\t\t\t".'<p>Il n\'y a pas de signalement ignoré.</p>'."\n";

?>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

require PUN_ROOT.'footer.php';
