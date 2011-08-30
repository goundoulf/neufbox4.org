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


// Show IP statistics for a certain user ID
if (isset($_GET['ip_stats']))
{
	$ip_stats = intval($_GET['ip_stats']);
	if ($ip_stats < 1)
		message($lang_common['Bad request']);


	$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Utilisateurs';
	require PUN_ROOT.'header.php';

?>
<div class="linkst">
	<div class="inbox">
		<div><a href="javascript:history.go(-1)">Retour</a></div>
	</div>
</div>

<div id="users1" class="blocktable">
	<h2><span>Utilisateurs</span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col">Adresse <acronym title="Internet Protocol" lang="en">IP</acronym></th>
					<th class="tc2" scope="col">Dernière visite</th>
					<th class="tc3" scope="col">Occurences</th>
					<th class="tcr" scope="col">Action</th>
				</tr>
			</thead>
			<tbody>
<?php

	$result = $db->query('SELECT poster_ip, MAX(posted) AS last_used, COUNT(id) AS used_times FROM '.$db->prefix.'posts WHERE poster_id='.$ip_stats.' GROUP BY poster_ip ORDER BY last_used DESC') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
		while ($cur_ip = $db->fetch_assoc($result))
		{

?>
				<tr>
					<td class="tcl"><a href="moderate.php?get_host=<?php echo $cur_ip['poster_ip'] ?>"><?php echo $cur_ip['poster_ip'] ?></a></td>
					<td class="tc2"><?php echo format_time($cur_ip['last_used']) ?></td>
					<td class="tc3"><?php echo $cur_ip['used_times'] ?></td>
					<td class="tcr"><a href="admin_users.php?show_users=<?php echo $cur_ip['poster_ip'] ?>">Trouver plus d'utilisateur pour cette <acronym title="Internet Protocol" lang="en">IP</acronym></a></td>
				</tr>
<?php

		}
	}
	else
		echo "\t\t\t\t".'<tr><td class="tcl" colspan="4">Il n\'y a actuellement aucun message de cet utilisateur dans les forums.</td></tr>'."\n";

?>
			</tbody>
			</table>
		</div>
	</div>
</div>

<div class="linksb">
	<div class="inbox">
		<div><a href="javascript:history.go(-1)">Retour</a></div>
	</div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}


if (isset($_GET['show_users']))
{
	$ip = $_GET['show_users'];

	if (!@preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $ip))
		message('L\'adresse IP soumise n\'est pas correctement formée.');


	$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Utilisateurs';
	require PUN_ROOT.'header.php';

?>
<div class="linkst">
	<div class="inbox">
		<div><a href="javascript:history.go(-1)">Retour</a></div>
	</div>
</div>

<div id="users2" class="blocktable">
	<h2><span>Utilisateurs</span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col">Nom d'utilisateur</th>
					<th class="tc2" scope="col">E-mail</th>
					<th class="tc3" scope="col">Titre/Statut</th>
					<th class="tc4" scope="col">Messages</th>
					<th class="tc5" scope="col">Note admin</th>
					<th class="tcr" scope="col">Actions</th>
				</tr>
			</thead>
			<tbody>
<?php

	$result = $db->query('SELECT DISTINCT poster_id, poster FROM '.$db->prefix.'posts WHERE poster_ip=\''.$db->escape($ip).'\' ORDER BY poster DESC') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	$num_posts = $db->num_rows($result);

	if ($num_posts)
	{
		// Loop through users and print out some info
		for ($i = 0; $i < $num_posts; ++$i)
		{
			list($poster_id, $poster) = $db->fetch_row($result);

			$result2 = $db->query('SELECT u.id, u.username, u.email, u.title, u.num_posts, u.admin_note, g.g_id, g.g_user_title FROM '.$db->prefix.'users AS u INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id>1 AND u.id='.$poster_id) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());

			if (($user_data = $db->fetch_assoc($result2)))
			{
				$user_title = get_title($user_data);

				$actions = '<a href="admin_users.php?ip_stats='.$user_data['id'].'">Voir stats IP</a> - <a href="recherche?action=show_user&amp;user_id='.$user_data['id'].'">Afficher messages</a>';

?>
				<tr>
					<td class="tcl"><?php echo '<a href="profile.php?id='.$user_data['id'].'">'.pun_htmlspecialchars($user_data['username']).'</a>' ?></td>
					<td class="tc2"><a href="mailto:<?php echo $user_data['email'] ?>"><?php echo $user_data['email'] ?></a></td>
					<td class="tc3"><?php echo $user_title ?></td>
					<td class="tc4"><?php echo $user_data['num_posts'] ?></td>
					<td class="tc5"><?php echo ($user_data['admin_note'] != '') ? $user_data['admin_note'] : '&nbsp;' ?></td>
					<td class="tcr"><?php echo $actions ?></td>
				</tr>
<?php

			}
			else
			{

?>
				<tr>
					<td class="tcl"><?php echo pun_htmlspecialchars($poster) ?></td>
					<td class="tc2">&nbsp;</td>
					<td class="tc3">Invité</td>
					<td class="tc4">&nbsp;</td>
					<td class="tc5">&nbsp;</td>
					<td class="tcr">&nbsp;</td>
				</tr>
<?php

			}
		}
	}
	else
		echo "\t\t\t\t".'<tr><td class="tcl" colspan="6">L\'adresse IP soumise est introuvable dans la base de données.</td></tr>'."\n";

?>
			</tbody>
			</table>
		</div>
	</div>
</div>

<div class="linksb">
	<div class="inbox">
		<div><a href="javascript:history.go(-1)">Retour</a></div>
	</div>
</div>
<?php
	require PUN_ROOT.'footer.php';
}


else if (isset($_POST['find_user']))
{
	$form = $_POST['form'];
	$form['username'] = $_POST['username'];

	// trim() all elements in $form
	$form = array_map('trim', $form);
	$conditions = array();

	$posts_greater = trim($_POST['posts_greater']);
	$posts_less = trim($_POST['posts_less']);
	$last_post_after = trim($_POST['last_post_after']);
	$last_post_before = trim($_POST['last_post_before']);
	$registered_after = trim($_POST['registered_after']);
	$registered_before = trim($_POST['registered_before']);
	$order_by = $_POST['order_by'];
	$direction = $_POST['direction'];
	$user_group = $_POST['user_group'];

	if (preg_match('/[^0-9]/', $posts_greater.$posts_less))
		message('Vous avez saisi une donnée non-numérique dans un champ qui en requière une.');

	// Try to convert date/time to timestamps
	if ($last_post_after != '')
		$last_post_after = strtotime($last_post_after);
	if ($last_post_before != '')
		$last_post_before = strtotime($last_post_before);
	if ($registered_after != '')
		$registered_after = strtotime($registered_after);
	if ($registered_before != '')
		$registered_before = strtotime($registered_before);

	if ($last_post_after == -1 || $last_post_before == -1 || $registered_after == -1 || $registered_before == -1)
		message('Vous avez saisi une date/heure invalide.');

	if ($last_post_after != '')
		$conditions[] = 'u.last_post>'.$last_post_after;
	if ($last_post_before != '')
		$conditions[] = 'u.last_post<'.$last_post_before;
	if ($registered_after != '')
		$conditions[] = 'u.registered>'.$registered_after;
	if ($registered_before != '')
		$conditions[] = 'u.registered<'.$registered_before;

	$like_command = ($db_type == 'pgsql') ? 'ILIKE' : 'LIKE';
	while (list($key, $input) = @each($form))
	{
		if ($input != '' && in_array($key, array('username', 'email', 'title', 'realname', 'url', 'jabber', 'icq', 'msn', 'aim', 'yahoo', 'location', 'signature', 'admin_note')))
			$conditions[] = 'u.'.$db->escape($key).' '.$like_command.' \''.$db->escape(str_replace('*', '%', $input)).'\'';
	}

	if ($posts_greater != '')
		$conditions[] = 'u.num_posts>'.$posts_greater;
	if ($posts_less != '')
		$conditions[] = 'u.num_posts<'.$posts_less;

	if ($user_group != 'all')
		$conditions[] = 'u.group_id='.intval($user_group);

	if (empty($conditions))
		message('Vous n\'avez saisi aucun critères de recherche.');


	$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Utilisateurs';
	require PUN_ROOT.'header.php';

?>
<div class="linkst">
	<div class="inbox">
		<div><a href="javascript:history.go(-1)">Retour</a></div>
	</div>
</div>

<div id="users2" class="blocktable">
	<h2><span>Utilisateurs</span></h2>
	<div class="box">
		<div class="inbox">
			<table cellspacing="0">
			<thead>
				<tr>
					<th class="tcl" scope="col">Nom d'utilisateur</th>
					<th class="tc2" scope="col">E-mail</th>
					<th class="tc3" scope="col">Titre/Status</th>
					<th class="tc4" scope="col">Messages</th>
					<th class="tc5" scope="col">Note admin</th>
					<th class="tcr" scope="col">Actions</th>
				</tr>
			</thead>
			<tbody>
<?php

	$result = $db->query('SELECT u.id, u.username, u.email, u.title, u.num_posts, u.admin_note, g.g_id, g.g_user_title FROM '.$db->prefix.'users AS u LEFT JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id WHERE u.id>1 AND '.implode(' AND ', $conditions).' ORDER BY '.$db->escape($order_by).' '.$db->escape($direction)) or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
	if ($db->num_rows($result))
	{
		while ($user_data = $db->fetch_assoc($result))
		{
			$user_title = get_title($user_data);

			// This script is a special case in that we want to display "Not verified" for non-verified users
			if (($user_data['g_id'] == '' || $user_data['g_id'] == PUN_UNVERIFIED) && $user_title != $lang_common['Banned'])
				$user_title = '<span class="warntext">Not verified</span>';

			$actions = '<a href="admin_users.php?ip_stats='.$user_data['id'].'">Voir stats IP</a> - <a href="recherche?action=show_user&amp;user_id='.$user_data['id'].'">Afficher messages</a>';

?>
				<tr>
					<td class="tcl"><?php echo '<a href="profile.php?id='.$user_data['id'].'">'.pun_htmlspecialchars($user_data['username']).'</a>' ?></td>
					<td class="tc2"><a href="mailto:<?php echo $user_data['email'] ?>"><?php echo $user_data['email'] ?></a></td>
					<td class="tc3"><?php echo $user_title ?></td>
					<td class="tc4"><?php echo $user_data['num_posts'] ?></td>
					<td class="tc5"><?php echo ($user_data['admin_note'] != '') ? $user_data['admin_note'] : '&nbsp;' ?></td>
					<td class="tcr"><?php echo $actions ?></td>
				</tr>
<?php

		}
	}
	else
		echo "\t\t\t\t".'<tr><td class="tcl" colspan="6">Aucun résultat.</td></tr>'."\n";

?>
			</tbody>
			</table>
		</div>
	</div>
</div>

<div class="linksb">
	<div class="inbox">
		<div><a href="javascript:history.go(-1)">Retour</a></div>
	</div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}


else
{
	$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Utilisateurs';
	$focus_element = array('find_user', 'username');
	require PUN_ROOT.'header.php';

	generate_admin_menu('users');

?>
	<div class="blockform">
		<h2><span>Recherche d'utilisateur</span></h2>
		<div class="box">
			<form id="find_user" method="post" action="admin_users.php?action=find_user">
				<p class="submittop"><input type="submit" name="find_user" value=" Rechercher " tabindex="1" /></p>
				<div class="inform">
					<fieldset>
						<legend>Saisissez vos critères de recherche</legend>
						<div class="infldset">
							<p>Recherche d'utilisateur dans la base de données. Vous pouvez saisir un ou plusieurs termes à rechercher. Utilisez le caractère astérisque (*) comme joker.</p>
							<table  class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Nom d'utilisateur</th>
									<td><input type="text" name="username" size="25" maxlength="25" tabindex="2" /></td>
								</tr>
								<tr>
									<th scope="row">Adresse e-mail</th>
									<td><input type="text" name="form[email]" size="30" maxlength="50" tabindex="3" /></td>
								</tr>
								<tr>
									<th scope="row">Titre</th>
									<td><input type="text" name="form[title]" size="30" maxlength="50" tabindex="4" /></td>
								</tr>
								<tr>
									<th scope="row">Nom réel</th>
									<td><input type="text" name="form[realname]" size="30" maxlength="40" tabindex="5" /></td>
								</tr>
								<tr>
									<th scope="row">Site web</th>
									<td><input type="text" name="form[url]" size="35" maxlength="100" tabindex="6" /></td>
								</tr>
								<tr>
									<th scope="row">ICQ</th>
									<td><input type="text" name="form[icq]" size="12" maxlength="12" tabindex="7" /></td>
								</tr>
								<tr>
									<th scope="row">MSN Messenger</th>
									<td><input type="text" name="form[msn]" size="30" maxlength="50" tabindex="8" /></td>
								</tr>
								<tr>
									<th scope="row">AOL IM</th>
									<td><input type="text" name="form[aim]" size="20" maxlength="20" tabindex="9" /></td>
								</tr>
								<tr>
									<th scope="row">Yahoo! Messenger</th>
									<td><input type="text" name="form[yahoo]" size="20" maxlength="20" tabindex="10" /></td>
								</tr>
								<tr>
									<th scope="row">Lieu</th>
									<td><input type="text" name="form[location]" size="30" maxlength="30" tabindex="11" /></td>
								</tr>
								<tr>
									<th scope="row">Signature</th>
									<td><input type="text" name="form[signature]" size="35" maxlength="512" tabindex="12" /></td>
								</tr>
								<tr>
									<th scope="row">Note admin</th>
									<td><input type="text" name="form[admin_note]" size="30" maxlength="30" tabindex="13" /></td>
								</tr>
								<tr>
									<th scope="row">Nombre de messages supérieur à</th>
									<td><input type="text" name="posts_greater" size="5" maxlength="8" tabindex="14" /></td>
								</tr>
								<tr>
									<th scope="row">Nombre de messages inférieur à</th>
									<td><input type="text" name="posts_less" size="5" maxlength="8" tabindex="15" /></td>
								</tr>
								<tr>
									<th scope="row">Le dernier message est après le</th>
									<td><input type="text" name="last_post_after" size="24" maxlength="19" tabindex="16" />
									<span>(yyyy-mm-dd hh:mm:ss)</span></td>
								</tr>
								<tr>
									<th scope="row">Le dernier message est avant le</th>
									<td><input type="text" name="last_post_before" size="24" maxlength="19" tabindex="17" />
									<span>(yyyy-mm-dd hh:mm:ss)</span></td>
								</tr>
								<tr>
									<th scope="row">Inscrit après le</th>
									<td><input type="text" name="registered_after" size="24" maxlength="19" tabindex="18" />
									<span>(yyyy-mm-dd hh:mm:ss)</span></td>
								</tr>
								<tr>
									<th scope="row">Inscrit avant le</th>
									<td><input type="text" name="registered_before" size="24" maxlength="19" tabindex="19" />
									<span>(yyyy-mm-dd hh:mm:ss)</span></td>
								</tr>
								<tr>
									<th scope="row">Trier par</th>
									<td>
										<select name="order_by" tabindex="20">
											<option value="username" selected="selected">Nom d'utilisateur</option>
											<option value="email">e-mail</option>
											<option value="num_posts">messages</option>
											<option value="last_post">dernier message</option>
											<option value="registered">inscriptions</option>
										</select>&nbsp;&nbsp;&nbsp;<select name="direction" tabindex="21">
											<option value="ASC" selected="selected">croissant</option>
											<option value="DESC">décroissant</option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">Groupe utilisateurs</th>
									<td>
										<select name="user_group" tabindex="22">
												<option value="all" selected="selected">Tous les groupes</option>
<?php

	$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups WHERE g_id!='.PUN_GUEST.' ORDER BY g_title') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

	while ($cur_group = $db->fetch_assoc($result))
		echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'">'.pun_htmlspecialchars($cur_group['g_title']).'</option>'."\n";

?>
										</select>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="find_user" value=" Rechercher " tabindex="23" /></p>
			</form>
		</div>

		<h2 class="block2"><span>Recherche <acronym title="Internet Protocol" lang="en">IP</acronym></span></h2>
		<div class="box">
			<form method="get" action="admin_users.php">
				<div class="inform">
					<fieldset>
						<legend>Saisissez une adresse <acronym title="Internet Protocol" lang="en">IP</acronym> à rechercher</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Adresse <acronym title="Internet Protocol" lang="en">IP</acronym><div><input type="submit" value=" Trouver " tabindex="25" /></div></th>
									<td><input type="text" name="show_users" size="18" maxlength="15" tabindex="24" />
									<span>L'adresse <acronym title="Internet Protocol" lang="en">IP</acronym> à rechercher dans la base de données.</span></td>
								</tr>
							</table>
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
