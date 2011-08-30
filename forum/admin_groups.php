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


// Add/edit a group (stage 1)
if (isset($_POST['add_group']) || isset($_GET['edit_group']))
{
	if (isset($_POST['add_group']))
	{
		$base_group = intval($_POST['base_group']);

		$result = $db->query('SELECT * FROM '.$db->prefix.'groups WHERE g_id='.$base_group) or error('Unable to fetch user group info', __FILE__, __LINE__, $db->error());
		$group = $db->fetch_assoc($result);

		$mode = 'add';
	}
	else	// We are editing a group
	{
		$group_id = intval($_GET['edit_group']);
		if ($group_id < 1)
			message($lang_common['Bad request']);

		$result = $db->query('SELECT * FROM '.$db->prefix.'groups WHERE g_id='.$group_id) or error('Unable to fetch user group info', __FILE__, __LINE__, $db->error());
		if (!$db->num_rows($result))
			message($lang_common['Bad request']);

		$group = $db->fetch_assoc($result);

		$mode = 'edit';
	}


	$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Groupes';
	$required_fields = array('req_title' => 'Nom groupe');
	$focus_element = array('groups2', 'req_title');
	require PUN_ROOT.'header.php';

	generate_admin_menu('groups');

?>
	<div class="blockform">
		<h2><span>Réglages du groupe</span></h2>
		<div class="box">
			<form id="groups2" method="post" action="admin_groups.php" onsubmit="return process_form(this)">
				<p class="submittop"><input type="submit" name="add_edit_group" value=" Enregistrer " /></p>
				<div class="inform">
					<input type="hidden" name="mode" value="<?php echo $mode ?>" />
<?php if ($mode == 'edit'): ?>				<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
<?php endif; ?><?php if ($mode == 'add'): ?>				<input type="hidden" name="base_group" value="<?php echo $base_group ?>" />
<?php endif; ?>					<fieldset>
						<legend>Réglages des options et des permissions de groupe</legend>
						<div class="infldset">
							<p>Les options et permissions ci-dessous sont les permissions par défaut pour le groupe. Ces options s'appliquent s'il n'y a pas de réglages de permissions spécifiques à un forum.</p>
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Nom groupe</th>
									<td>
										<input type="text" name="req_title" size="25" maxlength="50" value="<?php if ($mode == 'edit') echo pun_htmlspecialchars($group['g_title']); ?>" tabindex="1" />
									</td>
								</tr>
								<tr>
									<th scope="row">Titre utilisateur</th>
									<td>
										<input type="text" name="user_title" size="25" maxlength="50" value="<?php echo pun_htmlspecialchars($group['g_user_title']) ?>" tabindex="2" />
										<span>Ce titre remplacera n'importe quel nom de rang que les utilisateurs de ce groupe auront atteint. Laissez vide pour utiliser le titre par défaut ou le rang.</span>
									</td>
								</tr>
<?php if ($group['g_id'] != PUN_ADMIN): ?>								<tr>
									<th scope="row">Lire forums</th>
									<td>
										<input type="radio" name="read_board" value="1"<?php if ($group['g_read_board'] == '1') echo ' checked="checked"' ?> tabindex="3" />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="read_board" value="0"<?php if ($group['g_read_board'] == '0') echo ' checked="checked"' ?> tabindex="4" />&nbsp;<strong>Non</strong>
										<span>Autoriser les utilisateurs de ce groupe à voir les forums. Ce réglage s'applique à tous les aspects des forums et ne peut être outrepassé par les permissions spécifiques aux forums. Avec cette option à non les utilisateurs de ce groupe ne pourront que se connecter / se déconnecter.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Écrire des réponses</th>
									<td>
										<input type="radio" name="post_replies" value="1"<?php if ($group['g_post_replies'] == '1') echo ' checked="checked"' ?> tabindex="5" />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="post_replies" value="0"<?php if ($group['g_post_replies'] == '0') echo ' checked="checked"' ?> tabindex="6" />&nbsp;<strong>Non</strong>
										<span>Autoriser les utilisateurs de ce groupe à écrire des réponses aux discussions.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Lancer des discussions</th>
									<td>
										<input type="radio" name="post_topics" value="1"<?php if ($group['g_post_topics'] == '1') echo ' checked="checked"' ?> tabindex="7" />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="post_topics" value="0"<?php if ($group['g_post_topics'] == '0') echo ' checked="checked"' ?> tabindex="8" />&nbsp;<strong>Non</strong>
										<span>Autoriser les utilisateurs de ce groupe à lancer des nouveaux sujets.</span>
									</td>
								</tr>
<?php if ($group['g_id'] != PUN_GUEST): ?>								<tr>
									<th scope="row">Modifier messages</th>
									<td>
										<input type="radio" name="edit_posts" value="1"<?php if ($group['g_edit_posts'] == '1') echo ' checked="checked"' ?> tabindex="11" />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="edit_posts" value="0"<?php if ($group['g_edit_posts'] == '0') echo ' checked="checked"' ?> tabindex="12" />&nbsp;<strong>Non</strong>
										<span>Autoriser les utilisateurs de ce groupe à modifier leurs propres messages.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Effacer les messages</th>
									<td>
										<input type="radio" name="delete_posts" value="1"<?php if ($group['g_delete_posts'] == '1') echo ' checked="checked"' ?> tabindex="13" />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="delete_posts" value="0"<?php if ($group['g_delete_posts'] == '0') echo ' checked="checked"' ?> tabindex="14" />&nbsp;<strong>Non</strong>
										<span>Autoriser les utilisateurs de ce groupe à effacer leurs propres messages.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Effacer les discussions</th>
									<td>
										<input type="radio" name="delete_topics" value="1"<?php if ($group['g_delete_topics'] == '1') echo ' checked="checked"' ?> tabindex="15" />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="delete_topics" value="0"<?php if ($group['g_delete_topics'] == '0') echo ' checked="checked"' ?> tabindex="16" />&nbsp;<strong>Non</strong>
										<span>Autoriser les utilisateurs de ce groupe à effacer leurs propres sujets (y compris toutes les réponses).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Titre personnalisé</th>
									<td>
										<input type="radio" name="set_title" value="1"<?php if ($group['g_set_title'] == '1') echo ' checked="checked"' ?> tabindex="17" />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="set_title" value="0"<?php if ($group['g_set_title'] == '0') echo ' checked="checked"' ?> tabindex="18" />&nbsp;<strong>Non</strong>
										<span>Autoriser les utilisateurs de ce groupe à modifier leur propre titre utilisateur.</span>
									</td>
								</tr>
<?php endif; ?>								<tr>
									<th scope="row">Utiliser la recherche</th>
									<td>
										<input type="radio" name="search" value="1"<?php if ($group['g_search'] == '1') echo ' checked="checked"' ?> tabindex="19" />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="search" value="0"<?php if ($group['g_search'] == '0') echo ' checked="checked"' ?> tabindex="20" />&nbsp;<strong>Non</strong>
										<span>Autoriser les utilisateurs de ce groupe à utiliser la fonction de recherche sur les forums.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Recherche d'utilisateur</th>
									<td>
										<input type="radio" name="search_users" value="1"<?php if ($group['g_search_users'] == '1') echo ' checked="checked"' ?> tabindex="21" />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="search_users" value="0"<?php if ($group['g_search_users'] == '0') echo ' checked="checked"' ?> tabindex="22" />&nbsp;<strong>Non</strong>
										<span>Autoriser les utilisateurs de ce groupe à utiliser	un texte libre pour rechercher dans la liste des utilisateurs.</span>
									</td>
								</tr>
<?php if ($group['g_id'] != PUN_GUEST): ?>								<tr>
									<th scope="row">Intervalle pour modifier le sujet d'une discussion</th>
									<td>
										<input type="text" name="edit_subjects_interval" size="5" maxlength="5" value="<?php echo $group['g_edit_subjects_interval'] ?>" tabindex="23" />
										<span>Nombre de secondes après que le message ait été envoyé pendant lesquelles les utilisateurs de ce groupe pourront modifier le sujet d'une discussion qu'ils viennent de lancer. Mettre à 0 pour permettre la modification du sujet des discussions sans restriction dans le temps.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Intervalle de contrôle de flood sur les messages</th>
									<td>
										<input type="text" name="post_flood" size="5" maxlength="4" value="<?php echo $group['g_post_flood'] ?>" tabindex="24" />
										<span>Nombre de secondes pendant lesquelles les utilisateurs de ce groupe devront patienter entre deux messages. Mettre à 0 pour désactiver le contrôle de flood sur les messages.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Intervalle de contrôle de flood sur les recherches</th>
									<td>
										<input type="text" name="search_flood" size="5" maxlength="4" value="<?php echo $group['g_search_flood'] ?>" tabindex="25" />
										<span>Nombre de secondes pendant lesquelles les utilisateurs de ce groupe devront patienter entre deux recherches. Mettre à 0 pour désactiver le contrôle de flood sur les recherches.</span>
									</td>
								</tr>
<?php endif; ?><?php endif; ?>							</table>
<?php if ($group['g_id'] == PUN_MOD ): ?>							<p class="warntext">Pour qu'un utilisateur de ce groupe acquière les compétences de modérateur, il devra nécessairement être désigné comme modérateur d'un ou de plusieurs forums. Ceci est possible depuis la page d'administration du profil de l'utilisateur.</p>
<?php endif; ?>						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="add_edit_group" value=" Enregistrer " tabindex="26" /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}


// Add/edit a group (stage 2)
else if (isset($_POST['add_edit_group']))
{
	confirm_referrer('admin_groups.php');

	// Is this the admin group? (special rules apply)
	$is_admin_group = (isset($_POST['group_id']) && $_POST['group_id'] == PUN_ADMIN) ? true : false;

	$title = trim($_POST['req_title']);
	$user_title = trim($_POST['user_title']);
	$read_board = isset($_POST['read_board']) ? intval($_POST['read_board']) : '1';
	$post_replies = isset($_POST['post_replies']) ? intval($_POST['post_replies']) : '1';
	$post_topics = isset($_POST['post_topics']) ? intval($_POST['post_topics']) : '1';
	$edit_posts = isset($_POST['edit_posts']) ? intval($_POST['edit_posts']) : ($is_admin_group) ? '1' : '0';
	$delete_posts = isset($_POST['delete_posts']) ? intval($_POST['delete_posts']) : ($is_admin_group) ? '1' : '0';
	$delete_topics = isset($_POST['delete_topics']) ? intval($_POST['delete_topics']) : ($is_admin_group) ? '1' : '0';
	$set_title = isset($_POST['set_title']) ? intval($_POST['set_title']) : ($is_admin_group) ? '1' : '0';
	$search = isset($_POST['search']) ? intval($_POST['search']) : '1';
	$search_users = isset($_POST['search_users']) ? intval($_POST['search_users']) : '1';
	$edit_subjects_interval = isset($_POST['edit_subjects_interval']) ? intval($_POST['edit_subjects_interval']) : '0';
	$post_flood = isset($_POST['post_flood']) ? intval($_POST['post_flood']) : '0';
	$search_flood = isset($_POST['search_flood']) ? intval($_POST['search_flood']) : '0';

	if ($title == '')
		message('Vous devez saisir un nom de groupe.');

	$user_title = ($user_title != '') ? '\''.$db->escape($user_title).'\'' : 'NULL';

	if ($_POST['mode'] == 'add')
	{
		$result = $db->query('SELECT 1 FROM '.$db->prefix.'groups WHERE g_title=\''.$db->escape($title).'\'') or error('Unable to check group title collision', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
			message('Il existe déjà un groupe nommé \''.pun_htmlspecialchars($title).'\'.');

		$db->query('INSERT INTO '.$db->prefix.'groups (g_title, g_user_title, g_read_board, g_post_replies, g_post_topics, g_edit_posts, g_delete_posts, g_delete_topics, g_set_title, g_search, g_search_users, g_edit_subjects_interval, g_post_flood, g_search_flood) VALUES(\''.$db->escape($title).'\', '.$user_title.', '.$read_board.', '.$post_replies.', '.$post_topics.', '.$edit_posts.', '.$delete_posts.', '.$delete_topics.', '.$set_title.', '.$search.', '.$search_users.', '.$edit_subjects_interval.', '.$post_flood.', '.$search_flood.')') or error('Unable to add group', __FILE__, __LINE__, $db->error());
		$new_group_id = $db->insert_id();

		// Now lets copy the forum specific permissions from the group which this group is based on
		$result = $db->query('SELECT forum_id, read_forum, post_replies, post_topics FROM '.$db->prefix.'forum_perms WHERE group_id='.intval($_POST['base_group'])) or error('Unable to fetch group forum permission list', __FILE__, __LINE__, $db->error());
		while ($cur_forum_perm = $db->fetch_assoc($result))
			$db->query('INSERT INTO '.$db->prefix.'forum_perms (group_id, forum_id, read_forum, post_replies, post_topics) VALUES('.$new_group_id.', '.$cur_forum_perm['forum_id'].', '.$cur_forum_perm['read_forum'].', '.$cur_forum_perm['post_replies'].', '.$cur_forum_perm['post_topics'].')') or error('Unable to insert group forum permissions', __FILE__, __LINE__, $db->error());
	}
	else
	{
		$result = $db->query('SELECT 1 FROM '.$db->prefix.'groups WHERE g_title=\''.$db->escape($title).'\' AND g_id!='.intval($_POST['group_id'])) or error('Unable to check group title collision', __FILE__, __LINE__, $db->error());
		if ($db->num_rows($result))
			message('Il existe déjà un groupe nommé \''.pun_htmlspecialchars($title).'\'.');

		$db->query('UPDATE '.$db->prefix.'groups SET g_title=\''.$db->escape($title).'\', g_user_title='.$user_title.', g_read_board='.$read_board.', g_post_replies='.$post_replies.', g_post_topics='.$post_topics.', g_edit_posts='.$edit_posts.', g_delete_posts='.$delete_posts.', g_delete_topics='.$delete_topics.', g_set_title='.$set_title.', g_search='.$search.', g_search_users='.$search_users.', g_edit_subjects_interval='.$edit_subjects_interval.', g_post_flood='.$post_flood.', g_search_flood='.$search_flood.' WHERE g_id='.intval($_POST['group_id'])) or error('Unable to update group', __FILE__, __LINE__, $db->error());
	}

	// Regenerate the quickjump cache
	require_once PUN_ROOT.'include/cache.php';
	generate_quickjump_cache();

	redirect('admin_groups.php', 'Groupe '.(($_POST['mode'] == 'edit') ? 'modifié' : 'ajouté').'. Redirection ...');
}


// Set default group
else if (isset($_POST['set_default_group']))
{
	confirm_referrer('admin_groups.php');

	$group_id = intval($_POST['default_group']);
	if ($group_id < 4)
		message($lang_common['Bad request']);

	$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$group_id.' WHERE conf_name=\'o_default_user_group\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());

	// Regenerate the config cache
	require_once PUN_ROOT.'include/cache.php';
	generate_config_cache();

	redirect('admin_groups.php', 'Groupe par défaut réglé. Redirection ...');
}


// Remove a group
else if (isset($_GET['del_group']))
{
	confirm_referrer('admin_groups.php');

	$group_id = intval($_GET['del_group']);
	if ($group_id < 5)
		message($lang_common['Bad request']);

	// Make sure we don't remove the default group
	if ($group_id == $pun_config['o_default_user_group'])
		message('Le groupe par défaut ne peut être supprimé. Vous devez d\'abord définir un groupe par défaut différent.');


	// Check if this group has any members
	$result = $db->query('SELECT g.g_title, COUNT(u.id) FROM '.$db->prefix.'groups AS g INNER JOIN '.$db->prefix.'users AS u ON g.g_id=u.group_id WHERE g.g_id='.$group_id.' GROUP BY g.g_id, g_title') or error('Unable to fetch group info', __FILE__, __LINE__, $db->error());

	// If the group doesn't have any members or if we've already selected a group to move the members to
	if (!$db->num_rows($result) || isset($_POST['del_group']))
	{
		if (isset($_POST['del_group']))
		{
			$move_to_group = intval($_POST['move_to_group']);
			$db->query('UPDATE '.$db->prefix.'users SET group_id='.$move_to_group.' WHERE group_id='.$group_id) or error('Unable to move users into group', __FILE__, __LINE__, $db->error());
		}

		// Delete the group and any forum specific permissions
		$db->query('DELETE FROM '.$db->prefix.'groups WHERE g_id='.$group_id) or error('Unable to delete group', __FILE__, __LINE__, $db->error());
		$db->query('DELETE FROM '.$db->prefix.'forum_perms WHERE group_id='.$group_id) or error('Unable to delete group forum permissions', __FILE__, __LINE__, $db->error());

		// Regenerate the quickjump cache
		require_once PUN_ROOT.'include/cache.php';
		generate_quickjump_cache();

		redirect('admin_groups.php', 'Groupe supprimé. Redirection ...');
	}


	list($group_title, $group_members) = $db->fetch_row($result);

	$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Groupes';
	require PUN_ROOT.'header.php';

	generate_admin_menu('groups');

?>
	<div class="blockform">
		<h2><span>Supprimer groupe</span></h2>
		<div class="box">
			<form id="groups" method="post" action="admin_groups.php?del_group=<?php echo $group_id ?>">
				<div class="inform">
					<fieldset>
						<legend>Déplacer les utilisateur de ce groupe</legend>
						<div class="infldset">
							<p>Il y a actuellement <?php echo $group_members ?> membres dans le groupe "<?php echo pun_htmlspecialchars($group_title) ?>". Veuillez sélectionner un groupe dans lequel ces utilisateurs seront déplacés.</p>
							<label>Déplacer les utilisateurs dans
							<select name="move_to_group">
<?php

	$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups WHERE g_id!='.PUN_GUEST.' AND g_id!='.$group_id.' ORDER BY g_title') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

	while ($cur_group = $db->fetch_assoc($result))
	{
		if ($cur_group['g_id'] == PUN_MEMBER)	// Pre-select the pre-defined Members group
			echo "\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'" selected="selected">'.pun_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
		else
			echo "\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'">'.pun_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
	}

?>
							</select>
							</br></label>
						</div>
					</fieldset>
				</div>
				<p><input type="submit" name="del_group" value=" Supprimer groupe " /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

	require PUN_ROOT.'footer.php';
}


$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Groupes';
require PUN_ROOT.'header.php';

generate_admin_menu('groups');

?>
	<div class="blockform">
		<h2><span>Ajouter/régler groupes</span></h2>
		<div class="box">
			<form id="groups" method="post" action="admin_groups.php?action=foo">
				<div class="inform">
					<fieldset>
						<legend>Ajouter un groupe</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Nouveau groupe basé sur le groupe<div><input type="submit" name="add_group" value=" Ajouter " tabindex="2" /></div></th>
									<td>
										<select id="base_group" name="base_group" tabindex="1">
<?php

$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups WHERE g_id>'.PUN_GUEST.' ORDER BY g_title') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

while ($cur_group = $db->fetch_assoc($result))
{
	if ($cur_group['g_id'] == $pun_config['o_default_user_group'])
		echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'" selected="selected">'.pun_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
	else
		echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'">'.pun_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
}

?>
										</select>
										<span>Choisissez un groupe d'utilisateurs duquel le nouveau groupe héritera les propriétés. La page suivante vous permettra d'affiner ces réglages.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Définir le groupe par défaut</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Groupe par défaut<div><input type="submit" name="set_default_group" value=" Enregistrer " tabindex="4" /></div></th>
									<td>
										<select id="default_group" name="default_group" tabindex="3">
<?php

$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups WHERE g_id>'.PUN_GUEST.' ORDER BY g_title') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

while ($cur_group = $db->fetch_assoc($result))
{
	if ($cur_group['g_id'] == $pun_config['o_default_user_group'])
		echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'" selected="selected">'.pun_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
	else
		echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$cur_group['g_id'].'">'.pun_htmlspecialchars($cur_group['g_title']).'</option>'."\n";
}

?>
										</select>
										<span>Choisissez le groupe que vous voulez définir par défaut. C'est à dire le groupe où les utilisateurs seront placés quand ils s'inscriront. Pour des raisons de sécurité, par défaut les utilisateurs ne peuvent être mis ni dans le groupe modérateur ni dans le groupe administrateur.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
			</form>
		</div>

		<h2 class="block2"><span>Groupes existants</span></h2>
		<div class="box">
			<div class="fakeform">
				<div class="inform">
					<fieldset>
						<legend>Modifier/supprimer les groupes</legend>
						<div class="infldset">
							<p>Les groupes pré-définis Invités, Administrateurs, Modérateurs et Membres ne peuvent être supprimés. Ils peuvent par contre être modifiés. Mais suivant le groupe certaines options ne sont pas disponibles (<abbr title="exemple">ex.</abbr> la permission <em>modifier messages</em> pour les invités). Les Administrateurs ont toujours toutes les permisssions.</p>
							<table cellspacing="0">
<?php

$result = $db->query('SELECT g_id, g_title FROM '.$db->prefix.'groups ORDER BY g_id') or error('Unable to fetch user group list', __FILE__, __LINE__, $db->error());

while ($cur_group = $db->fetch_assoc($result))
	echo "\t\t\t\t\t\t\t\t".'<tr><th scope="row"><a href="admin_groups.php?edit_group='.$cur_group['g_id'].'">Modifier</a>'.(($cur_group['g_id'] > PUN_MEMBER) ? ' - <a href="admin_groups.php?del_group='.$cur_group['g_id'].'">Supprimer</a>' : '').'</th><td>'.pun_htmlspecialchars($cur_group['g_title']).'</td></tr>'."\n";

?>
							</table>
						</div>
					</fieldset>
				</div>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

require PUN_ROOT.'footer.php';
