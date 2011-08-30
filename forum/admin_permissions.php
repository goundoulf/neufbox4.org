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


if (isset($_POST['form_sent']))
{
	confirm_referrer('admin_permissions.php');

	$form = array_map('intval', $_POST['form']);

	while (list($key, $input) = @each($form))
	{
		// Only update values that have changed
		if (array_key_exists('p_'.$key, $pun_config) && $pun_config['p_'.$key] != $input)
			$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$input.' WHERE conf_name=\'p_'.$db->escape($key).'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
	}

	// Regenerate the config cache
	require_once PUN_ROOT.'include/cache.php';
	generate_config_cache();

	redirect('admin_permissions.php', 'Permissions modifiées. Redirection ...');
}


$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Permissions';
require PUN_ROOT.'header.php';
generate_admin_menu('permissions');

?>
	<div class="blockform">
		<h2><span>Permissions</span></h2>
		<div class="box">
			<form method="post" action="admin_permissions.php">
				<p class="submittop"><input type="submit" name="save" value=" Enregistrer " /></p>
				<div class="inform">
				<input type="hidden" name="form_sent" value="1" />
					<fieldset>
						<legend>Écriture</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">BBCode</th>
									<td>
										<input type="radio" name="form[message_bbcode]" value="1"<?php if ($pun_config['p_message_bbcode'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[message_bbcode]" value="0"<?php if ($pun_config['p_message_bbcode'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser l'utilisation du BBCode dans les messages (recommandé).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Balise Image</th>
									<td>
										<input type="radio" name="form[message_img_tag]" value="1"<?php if ($pun_config['p_message_img_tag'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[message_img_tag]" value="0"<?php if ($pun_config['p_message_img_tag'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser l'utilisation de la balise BBCode [img][/img] dans les messages.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Message tout en majuscules</th>
									<td>
										<input type="radio" name="form[message_all_caps]" value="1"<?php if ($pun_config['p_message_all_caps'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[message_all_caps]" value="0"<?php if ($pun_config['p_message_all_caps'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser les messages qui ne contiennent que des lettres en majuscules.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Sujet tout en majuscules</th>
									<td>
										<input type="radio" name="form[subject_all_caps]" value="1"<?php if ($pun_config['p_subject_all_caps'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[subject_all_caps]" value="0"<?php if ($pun_config['p_subject_all_caps'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser les sujets qui ne contiennent que des lettres en majuscules.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">E-mail invité obligatoire</th>
									<td>
										<input type="radio" name="form[force_guest_email]" value="1"<?php if ($pun_config['p_force_guest_email'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[force_guest_email]" value="0"<?php if ($pun_config['p_force_guest_email'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Obliger les invités à donner une adresse e-mail pour écrire un message.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Signatures</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">BBCode dans les signatures</th>
									<td>
										<input type="radio" name="form[sig_bbcode]" value="1"<?php if ($pun_config['p_sig_bbcode'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[sig_bbcode]" value="0"<?php if ($pun_config['p_sig_bbcode'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser l'utilisation du BBCodes dans les signatures des utilisateurs.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Balise image dans les signatures</th>
									<td>
										<input type="radio" name="form[sig_img_tag]" value="1"<?php if ($pun_config['p_sig_img_tag'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[sig_img_tag]" value="0"<?php if ($pun_config['p_sig_img_tag'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser l'utilisation de la balise BBCode [img][/img] dans la signature des utilisateurs (non recommandé).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Signature tout en majuscules</th>
									<td>
										<input type="radio" name="form[sig_all_caps]" value="1"<?php if ($pun_config['p_sig_all_caps'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[sig_all_caps]" value="0"<?php if ($pun_config['p_sig_all_caps'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser les signatures qui ne contiennent que des lettres en majuscules.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Longueur maximum des signatures</th>
									<td>
										<input type="text" name="form[sig_length]" size="5" maxlength="5" value="<?php echo $pun_config['p_sig_length'] ?>" />
										<span>Le nombre maximum de caractères qu'une signature d'utilisateur puisse contenir.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Nombre maximum de lignes dans les signatures</th>
									<td>
										<input type="text" name="form[sig_lines]" size="3" maxlength="3" value="<?php echo $pun_config['p_sig_lines'] ?>" />
										<span>Le nombre maximum de lignes qu'une signature d'utilisateur puisse contenir.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Modérateurs</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Modifier les profils utilisateurs</th>
									<td>
										<input type="radio" name="form[mod_edit_users]" value="1"<?php if ($pun_config['p_mod_edit_users'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[mod_edit_users]" value="0"<?php if ($pun_config['p_mod_edit_users'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser les modérateurs à modifier les profils des utilisateurs.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Renommer utilisateurs</th>
									<td>
										<input type="radio" name="form[mod_rename_users]" value="1"<?php if ($pun_config['p_mod_rename_users'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[mod_rename_users]" value="0"<?php if ($pun_config['p_mod_rename_users'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser les modérateurs à renommer les utilisateurs. A l'exception des autres modérateurs et des administrateurs.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Changer les mots de passe des utilisateurs</th>
									<td>
										<input type="radio" name="form[mod_change_passwords]" value="1"<?php if ($pun_config['p_mod_change_passwords'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[mod_change_passwords]" value="0"<?php if ($pun_config['p_mod_change_passwords'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser les modérateurs à changer les mots de passe des utilisateurs. A l'exception des autres modérateurs et des administrateurs.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Bannir utilisateurs</th>
									<td>
										<input type="radio" name="form[mod_ban_users]" value="1"<?php if ($pun_config['p_mod_ban_users'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[mod_ban_users]" value="0"<?php if ($pun_config['p_mod_ban_users'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser les modérateurs à bannir les utilisateurs (et modifier/supprimer les bannissements en cours).</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Inscriptions</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Autoriser les adresses e-mail bannies</th>
									<td>
										<input type="radio" name="form[allow_banned_email]" value="1"<?php if ($pun_config['p_allow_banned_email'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[allow_banned_email]" value="0"<?php if ($pun_config['p_allow_banned_email'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser les utilisateurs à s'inscrire (ou changer d'adresse) avec une adresse/un domaine e-mail bannis. Si vous laissez cette option à son réglage par défaut (oui) ceci sera possible mais une alerte par e-mail sera envoyée à la liste d'adresses e-mail des forums (une manière efficace pour détecter les inscriptions multiples).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Autoriser les adresses e-mail en double</th>
									<td>
										<input type="radio" name="form[allow_dupe_email]" value="1"<?php if ($pun_config['p_allow_dupe_email'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[allow_dupe_email]" value="0"<?php if ($pun_config['p_allow_dupe_email'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Autoriser les utilisateurs à s'inscrire avec une adresse e-mail déjà utilisée par un autre nom d'utilisateur. Si autorisé une alerte e-mail sera envoyée à la liste d'adresses e-mail des forums lorsqu'un doublon sera détecté.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<p class="submitend"><input type="submit" name="save" value=" Enregistrer " /></p>
			</form>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

require PUN_ROOT.'footer.php';
