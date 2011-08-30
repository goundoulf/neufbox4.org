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
	// Custom referrer check (so we can output a custom error message)
	if (!preg_match('#^'.preg_quote(str_replace('www.', '', $pun_config['o_base_url']).'/admin_options.php', '#').'#i', str_replace('www.', '', (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''))))
		message('Mauvais HTTP_REFERER. Si vous avez déplacé ces forums d\'un endroit à un autre ou si vous avez changé de nom de domaine, vous devez mettre à jour manuellement l\'URL de base dans la base de données (cherchez o_base_url dans la table config) et ensuite videz le cache en effaçant tous les fichiers .php du répertoire /cache.');

	$form = array_map('trim', $_POST['form']);

	if ($form['board_title'] == '')
		message('Vous devez saisir un titre pour vos forums.');

	// Clean default_lang
	$form['default_lang'] = preg_replace('#[\.\\\/]#', '', $form['default_lang']);

	require PUN_ROOT.'include/email.php';

	$form['admin_email'] = strtolower($form['admin_email']);
	if (!is_valid_email($form['admin_email']))
		message('L\'adresse email administrateur que vous avez saisi est incorrecte.');

	$form['webmaster_email'] = strtolower($form['webmaster_email']);
	if (!is_valid_email($form['webmaster_email']))
		message('L\'adresse email webmaster que vous avez saisi est incorrecte.');

	if ($form['mailing_list'] != '')
		$form['mailing_list'] = strtolower(preg_replace('/[\s]/', '', $form['mailing_list']));

	// Make sure base_url doesn't end with a slash
	if (substr($form['base_url'], -1) == '/')
		$form['base_url'] = substr($form['base_url'], 0, -1);

	// Clean avatars_dir
	$form['avatars_dir'] = str_replace("\0", '', $form['avatars_dir']);

	// Make sure avatars_dir doesn't end with a slash
	if (substr($form['avatars_dir'], -1) == '/')
		$form['avatars_dir'] = substr($form['avatars_dir'], 0, -1);

	if ($form['additional_navlinks'] != '')
		$form['additional_navlinks'] = trim(pun_linebreaks($form['additional_navlinks']));

	if ($form['announcement_message'] != '')
		$form['announcement_message'] = pun_linebreaks($form['announcement_message']);
	else
	{
		$form['announcement_message'] = 'Saisissez votre annonce ici.';

		if ($form['announcement'] == '1')
			$form['announcement'] = '0';
	}

	if ($form['rules_message'] != '')
		$form['rules_message'] = pun_linebreaks($form['rules_message']);
	else
	{
		$form['rules_message'] = 'Saisissez les règles d\'utilisation ici.';

		if ($form['rules'] == '1')
			$form['rules'] = '0';
	}

	if ($form['maintenance_message'] != '')
		$form['maintenance_message'] = pun_linebreaks($form['maintenance_message']);
	else
	{
		$form['maintenance_message'] = 'Les forums sont temporairement fermés pour des raisons de maintenance. Veuillez essayer à nouveau d\'ici quelques minutes.<br />\\n<br />\\n/Administrateur';

		if ($form['maintenance'] == '1')
			$form['maintenance'] = '0';
	}

	$form['timeout_visit'] = intval($form['timeout_visit']);
	$form['timeout_online'] = intval($form['timeout_online']);
	$form['redirect_delay'] = intval($form['redirect_delay']);
	$form['topic_review'] = intval($form['topic_review']);
	$form['disp_topics_default'] = intval($form['disp_topics_default']);
	$form['disp_posts_default'] = intval($form['disp_posts_default']);
	$form['indent_num_spaces'] = intval($form['indent_num_spaces']);
	$form['avatars_width'] = intval($form['avatars_width']);
	$form['avatars_height'] = intval($form['avatars_height']);
	$form['avatars_size'] = intval($form['avatars_size']);

	if ($form['timeout_online'] >= $form['timeout_visit'])
		message('La valeur de "Temps mort en ligne" doit être inférieur à la valeur de "Temps mort de visite".');

	while (list($key, $input) = @each($form))
	{
		// Only update values that have changed
		if (array_key_exists('o_'.$key, $pun_config) && $pun_config['o_'.$key] != $input)
		{
			if ($input != '' || is_int($input))
				$value = '\''.$db->escape($input).'\'';
			else
				$value = 'NULL';

			$db->query('UPDATE '.$db->prefix.'config SET conf_value='.$value.' WHERE conf_name=\'o_'.$db->escape($key).'\'') or error('Unable to update board config', __FILE__, __LINE__, $db->error());
		}
	}

	// Regenerate the config cache
	require_once PUN_ROOT.'include/cache.php';
	generate_config_cache();

	redirect('admin_options.php', 'Options modifiées. Redirection ...');
}


$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin / Options';
$form_name = 'update_options';
require PUN_ROOT.'header.php';

generate_admin_menu('options');

?>
	<div class="blockform">
		<h2><span>Options</span></h2>
		<div class="box">
			<form method="post" action="admin_options.php?action=foo">
				<p class="submittop"><input type="submit" name="save" value=" Enregistrer " /></p>
				<div class="inform">
				<input type="hidden" name="form_sent" value="1" />
					<fieldset>
						<legend>Essentiel</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Titre forums</th>
									<td>
										<input type="text" name="form[board_title]" size="50" maxlength="255" value="<?php echo pun_htmlspecialchars($pun_config['o_board_title']) ?>" />
										<span>Le titre de ces forums (affiché en haut de chaques pages). Ce champ ne peut <strong>pas</strong> contenir d'<acronym title="HyperText Markup Language" lang="en">HTML</acronym>.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Description forums</th>
									<td>
										<input type="text" name="form[board_desc]" size="50" maxlength="255" value="<?php echo pun_htmlspecialchars($pun_config['o_board_desc']) ?>" />
										<span>Une courte description de vos forums (affiché en haut de chaques pages). Ce champ peut contenir du <acronym title="HyperText Markup Language" lang="en">HTML</acronym>.</span>
									</td>
								</tr>
								<tr>
									<th scope="row"><acronym title="Uniform Resource Locator" lang="en">URL</acronym> de base</th>
									<td>
										<input type="text" name="form[base_url]" size="50" maxlength="100" value="<?php echo $pun_config['o_base_url'] ?>" />
										<span>L'<acronym title="Uniform Resource Locator" lang="en">URL</acronym> de ces forums sans slash à la fin (ex :  http://www.mon-domaine.com/forums). Ce champ <strong>doit</strong> être correct pour que toutes les fonctions administrateurs et modérateurs soient opérationnelles. Si vous obtenez une erreur "Bad referer", il est probablement incorrect.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Fuseau horaire serveur</th>
									<td>
										<select name="form[server_timezone]">
											<option value="-12"<?php if ($pun_config['o_server_timezone'] == -12 ) echo ' selected="selected"' ?>>-12</option>
											<option value="-11"<?php if ($pun_config['o_server_timezone'] == -11) echo ' selected="selected"' ?>>-11</option>
											<option value="-10"<?php if ($pun_config['o_server_timezone'] == -10) echo ' selected="selected"' ?>>-10</option>
											<option value="-9.5"<?php if ($pun_config['o_server_timezone'] == -9.5) echo ' selected="selected"' ?>>-09.5</option>
											<option value="-9"<?php if ($pun_config['o_server_timezone'] == -9 ) echo ' selected="selected"' ?>>-09</option>
											<option value="-8.5"<?php if ($pun_config['o_server_timezone'] == -8.5) echo ' selected="selected"' ?>>-08.5</option>
											<option value="-8"<?php if ($pun_config['o_server_timezone'] == -8 ) echo ' selected="selected"' ?>>-08 PST</option>
											<option value="-7"<?php if ($pun_config['o_server_timezone'] == -7 ) echo ' selected="selected"' ?>>-07 MST</option>
											<option value="-6"<?php if ($pun_config['o_server_timezone'] == -6 ) echo ' selected="selected"' ?>>-06 CST</option>
											<option value="-5"<?php if ($pun_config['o_server_timezone'] == -5 ) echo ' selected="selected"' ?>>-05 EST</option>
											<option value="-4"<?php if ($pun_config['o_server_timezone'] == -4 ) echo ' selected="selected"' ?>>-04 AST</option>
											<option value="-3.5"<?php if ($pun_config['o_server_timezone'] == -3.5) echo ' selected="selected"' ?>>-03.5</option>
											<option value="-3"<?php if ($pun_config['o_server_timezone'] == -3 ) echo ' selected="selected"' ?>>-03 ADT</option>
											<option value="-2"<?php if ($pun_config['o_server_timezone'] == -2 ) echo ' selected="selected"' ?>>-02</option>
											<option value="-1"<?php if ($pun_config['o_server_timezone'] == -1) echo ' selected="selected"' ?>>-01</option>
											<option value="0"<?php if ($pun_config['o_server_timezone'] == 0) echo ' selected="selected"' ?>>00 GMT</option>
											<option value="1"<?php if ($pun_config['o_server_timezone'] == 1) echo ' selected="selected"' ?>>+01 CET</option>
											<option value="2"<?php if ($pun_config['o_server_timezone'] == 2 ) echo ' selected="selected"' ?>>+02</option>
											<option value="3"<?php if ($pun_config['o_server_timezone'] == 3 ) echo ' selected="selected"' ?>>+03</option>
											<option value="3.5"<?php if ($pun_config['o_server_timezone'] == 3.5) echo ' selected="selected"' ?>>+03.5</option>
											<option value="4"<?php if ($pun_config['o_server_timezone'] == 4 ) echo ' selected="selected"' ?>>+04</option>
											<option value="4.5"<?php if ($pun_config['o_server_timezone'] == 4.5) echo ' selected="selected"' ?>>+04.5</option>
											<option value="5"<?php if ($pun_config['o_server_timezone'] == 5 ) echo ' selected="selected"' ?>>+05</option>
											<option value="5.5"<?php if ($pun_config['o_server_timezone'] == 5.5) echo ' selected="selected"' ?>>+05.5</option>
											<option value="6"<?php if ($pun_config['o_server_timezone'] == 6 ) echo ' selected="selected"' ?>>+06</option>
											<option value="6.5"<?php if ($pun_config['o_server_timezone'] == 6.5) echo ' selected="selected"' ?>>+06.5</option>
											<option value="7"<?php if ($pun_config['o_server_timezone'] == 7 ) echo ' selected="selected"' ?>>+07</option>
											<option value="8"<?php if ($pun_config['o_server_timezone'] == 8 ) echo ' selected="selected"' ?>>+08</option>
											<option value="9"<?php if ($pun_config['o_server_timezone'] == 9 ) echo ' selected="selected"' ?>>+09</option>
											<option value="9.5"<?php if ($pun_config['o_server_timezone'] == 9.5) echo ' selected="selected"' ?>>+09.5</option>
											<option value="10"<?php if ($pun_config['o_server_timezone'] == 10) echo ' selected="selected"' ?>>+10</option>
											<option value="10.5"<?php if ($pun_config['o_server_timezone'] == 10.5) echo ' selected="selected"' ?>>+10.5</option>
											<option value="11"<?php if ($pun_config['o_server_timezone'] == 11) echo ' selected="selected"' ?>>+11</option>
											<option value="11.5"<?php if ($pun_config['o_server_timezone'] == 11.5) echo ' selected="selected"' ?>>+11.5</option>
											<option value="12"<?php if ($pun_config['o_server_timezone'] == 12 ) echo ' selected="selected"' ?>>+12</option>
											<option value="13"<?php if ($pun_config['o_server_timezone'] == 13 ) echo ' selected="selected"' ?>>+13</option>
										</select>
										<span>Le fuseau horaire du serveur où est installé FluxBB.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Langage par défaut</th>
									<td>
										<select name="form[default_lang]">
<?php

		$languages = array();
		$d = dir(PUN_ROOT.'lang');
		while (($entry = $d->read()) !== false)
		{
			if ($entry != '.' && $entry != '..' && is_dir(PUN_ROOT.'lang/'.$entry) && file_exists(PUN_ROOT.'lang/'.$entry.'/common.php'))
				$languages[] = $entry;
		}
		$d->close();

		@natsort($languages);

		while (list(, $temp) = @each($languages))
		{
			if ($pun_config['o_default_lang'] == $temp)
				echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'" selected="selected">'.$temp.'</option>'."\n";
			else
				echo "\t\t\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.$temp.'</option>'."\n";
		}

?>
										</select>
										<span>Choisissez la langue par défaut qui sera utilisée si l'utilisateur est un invité ou pour les utilisateurs qui n'auront pas changé de langage dans leur profil. Si vous supprimez un pack de langage vous devrez mettre à jour ce réglage.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Style par défaut</th>
									<td>
										<select name="form[default_style]">
<?php

		$styles = array();
		$d = dir(PUN_ROOT.'style');
		while (($entry = $d->read()) !== false)
		{
			if (substr($entry, strlen($entry)-4) == '.css')
				$styles[] = substr($entry, 0, strlen($entry)-4);
		}
		$d->close();

		@natsort($styles);

		while (list(, $temp) = @each($styles))
		{
			if ($pun_config['o_default_style'] == $temp)
				echo "\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'" selected="selected">'.str_replace('_', ' ', $temp).'</option>'."\n";
			else
				echo "\t\t\t\t\t\t\t\t\t".'<option value="'.$temp.'">'.str_replace('_', ' ', $temp).'</option>'."\n";
		}

?>
										</select>
										<span>Séléctionnez le style par défaut qui sera utilisé par les visiteurs ou les utilisateurs qui n'ont pas changés de style dans leur profil.</span></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Dates, heures et limites de temps</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Format heure</th>
									<td>
										<input type="text" name="form[time_format]" size="25" maxlength="25" value="<?php echo pun_htmlspecialchars($pun_config['o_time_format']) ?>" />
										<span>[Format actuel : <?php echo date($pun_config['o_time_format']) ?>]&nbsp;Voir <a href="http://www.php.net/manual/fr/function.date.php">ici</a> pour les options de formatage.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Date format</th>
									<td>
										<input type="text" name="form[date_format]" size="25" maxlength="25" value="<?php echo pun_htmlspecialchars($pun_config['o_date_format']) ?>" />
										<span>[Format actuel : <?php echo date($pun_config['o_date_format']) ?>]&nbsp;Voir <a href="http://www.php.net/manual/fr/function.date.php">ici</a> pour les options de formatage.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Temps mort de visite</th>
									<td>
										<input type="text" name="form[timeout_visit]" size="5" maxlength="5" value="<?php echo $pun_config['o_timeout_visit'] ?>" />
										<span>Nombre de secondes qu'un utilisateur devra rester inactif avant que les données de sa dernière visite soient mises à jours (affecte principalement les indicateurs de nouveaux messages).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Temps mort en ligne</th>
									<td>
										<input type="text" name="form[timeout_online]" size="5" maxlength="5" value="<?php echo $pun_config['o_timeout_online'] ?>" />
										<span>Nombre de secondes qu'un utilisateur devra rester inactif avant qu'il ne soit supprimé de la liste des utilisateurs en ligne.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Temps de redirection</th>
									<td>
										<input type="text" name="form[redirect_delay]" size="3" maxlength="3" value="<?php echo $pun_config['o_redirect_delay'] ?>" />
										<span>Nombre de secondes à patienter avant d'être redirigé. Si ce champ est réglé à 0, aucune page de redirection ne sera affichée mais cela n'est pas recommandé.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Afficher</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Numéro de version</th>
									<td>
										<input type="radio" name="form[show_version]" value="1"<?php if ($pun_config['o_show_version'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[show_version]" value="0"<?php if ($pun_config['o_show_version'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Affiche le numéro de version de FluxBB en bas de page.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Infos utilisateurs dans les messages</th>
									<td>
										<input type="radio" name="form[show_user_info]" value="1"<?php if ($pun_config['o_show_user_info'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[show_user_info]" value="0"<?php if ($pun_config['o_show_user_info'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Affiche des informations sur les utilisateurs sous leur nom d'utilisateur lorsque l'on affiche un sujet. Affiche le lieu, la date d'inscription, le nombre de message et les liens de contact (e-mail et site web).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Nombre de messages par utilisateur</th>
									<td>
										<input type="radio" name="form[show_post_count]" value="1"<?php if ($pun_config['o_show_post_count'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[show_post_count]" value="0"<?php if ($pun_config['o_show_post_count'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Affiche le nombre de messages qu'un utilisateur a écrit (sur la page d'un sujet, son profil et la liste utilisateurs).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Émoticônes</th>
									<td>
										<input type="radio" name="form[smilies]" value="1"<?php if ($pun_config['o_smilies'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[smilies]" value="0"<?php if ($pun_config['o_smilies'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Convertir les émoticônes en petites images.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Émoticônes dans les signatures</th>
									<td>
										<input type="radio" name="form[smilies_sig]" value="1"<?php if ($pun_config['o_smilies_sig'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[smilies_sig]" value="0"<?php if ($pun_config['o_smilies_sig'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Convertir les émoticônes en petites images dans les signatures.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Créer des liens cliquables</th>
									<td>
										<input type="radio" name="form[make_links]" value="1"<?php if ($pun_config['o_make_links'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[make_links]" value="0"<?php if ($pun_config['o_make_links'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Lorsque cette fonction est activée, FluxBB détecte automatiquement lors de l'envoi d'un nouveau message toutes les <acronym title="Uniform Resource Locator" lang="en">URL</acronym> qu'il contient et créer des liens cliquables.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Résumés des discussions</th>
									<td>
										<input type="text" name="form[topic_review]" size="3" maxlength="3" value="<?php echo $pun_config['o_topic_review'] ?>" />
										<span>Nombre maximum  de messages à afficher quand on écrit une réponse. 0 pour désactiver.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Discussions par page</th>
									<td>
										<input type="text" name="form[disp_topics_default]" size="3" maxlength="3" value="<?php echo $pun_config['o_disp_topics_default'] ?>" />
										<span>Le nombre de discussions par défaut à afficher sur la page d'un forum. Les utilisateurs inscrits peuvent personnaliser cette option.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Messages par page</th>
									<td>
										<input type="text" name="form[disp_posts_default]" size="3" maxlength="3" value="<?php echo $pun_config['o_disp_posts_default'] ?>" />
										<span>Le nombre de messages par défaut à afficher sur la page d'une discussion. Les utilisateurs inscrits peuvent personnaliser cette option.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Taille du retrait</th>
									<td>
										<input type="text" name="form[indent_num_spaces]" size="3" maxlength="3" value="<?php echo $pun_config['o_indent_num_spaces'] ?>" />
										<span>Si réglée à 8, une tabulation usuelle sera utilisée pour afficher du texte avec les balises [code][/code]. Sinon c'est le nombre d'espaces qui sera utilisé pour mettre en retrait le texte.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Fonctionnalités</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Réponse rapide</th>
									<td>
										<input type="radio" name="form[quickpost]" value="1"<?php if ($pun_config['o_quickpost'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[quickpost]" value="0"<?php if ($pun_config['o_quickpost'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Lorsque cette option est activée, FluxBB ajoute un formulaire de réponse au bas des discussions. Ceci permet aux utilisateurs d'écrire des réponses directement depuis l'écran de lecture des discussions.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Utilisateurs en ligne</th>
									<td>
										<input type="radio" name="form[users_online]" value="1"<?php if ($pun_config['o_users_online'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[users_online]" value="0"<?php if ($pun_config['o_users_online'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Affiche sur la page d'accueil des forums des informations concernant les utilisateurs qui sont en ligne lors de l'affichage de la page.</span>
									</td>
								</tr>
								<tr>
									<th scope="row"><a name="censoring">Mots à censurer</a></th>
									<td>
										<input type="radio" name="form[censoring]" value="1"<?php if ($pun_config['o_censoring'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[censoring]" value="0"<?php if ($pun_config['o_censoring'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Activez cette option pour censurer des mots prédéfinis. Pour plus d'information, voir la page d'administration des <a href="admin_censoring.php">Mots censurés</a>.</span>
									</td>
								</tr>
								<tr>
									<th scope="row"><a name="ranks">Rangs utilisateurs</a></th>
									<td>
										<input type="radio" name="form[ranks]" value="1"<?php if ($pun_config['o_ranks'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[ranks]" value="0"<?php if ($pun_config['o_ranks'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Activez cette option pour utiliser les rangs utilisateurs. Pour plus d'information, voir la page d'administration des <a href="admin_ranks.php">Rangs utilisateurs</a>.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Utilisateur a écrit récemment</th>
									<td>
										<input type="radio" name="form[show_dot]" value="1"<?php if ($pun_config['o_show_dot'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[show_dot]" value="0"<?php if ($pun_config['o_show_dot'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Cette fonctionnalité permet d'afficher un point devant les sujets dans le cas où l'utilisateur connecté aurait récemment écrit dans les sujets. Désactivez si vous constatez d'importantes charges serveur.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Saut rapide</th>
									<td>
										<input type="radio" name="form[quickjump]" value="1"<?php if ($pun_config['o_quickjump'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[quickjump]" value="0"<?php if ($pun_config['o_quickjump'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Active le menu déroulant de saut rapide (saut de forum en forum).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Compression GZip</th>
									<td>
										<input type="radio" name="form[gzip]" value="1"<?php if ($pun_config['o_gzip'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[gzip]" value="0"<?php if ($pun_config['o_gzip'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Si activée, FluxBB compressera la sortie envoyée aux navigateurs. Ceci devrait réduire la consommation de bande passante mais utilisera un peu plus le <acronym title="Central Processing Unit" lang="en">CPU</acronym> (processeur système). Cette option nécessite que <acronym title="PHP: Hypertext Preprocessor" lang="en">PHP</acronym> soit configuré avec zlib (--with-zlib). Note : Si vous avez déjà un des modules Apache mod_gzip ou mod_deflate d'installé et de configuré pour compresser les scripts <acronym title="PHP: Hypertext Preprocessor" lang="en">PHP</acronym>, vous pouvez alors désactiver cette option.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Recherche dans tous les forums</th>
									<td>
										<input type="radio" name="form[search_all_forums]" value="1"<?php if ($pun_config['o_search_all_forums'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[search_all_forums]" value="0"<?php if ($pun_config['o_search_all_forums'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Lorsque cette option est désactivée, les recherches ne peuvent êtres effectuées que sur un forum à la fois. Désactivez si la charge serveur est élevée à cause d'un nombre trop important de recherches.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Items de menu additionnels</th>
									<td>
										<textarea name="form[additional_navlinks]" rows="3" cols="55"><?php echo pun_htmlspecialchars($pun_config['o_additional_navlinks']) ?></textarea>
										<span>En entrant des liens hypertext <acronym title="HyperText Markup Language" lang="en">HTML</acronym> dans cette boite de saisie, autant d'items seront ajoutés au menu de navigation en haut de toutes les pages. Le format pour ajouter un nouveau lien est   X = &lt;a href="URL"&gt;LIEN&lt;/a&gt; où X est la position à laquelle le lien devra être inséré (<abbr title="exemple">ex.</abbr> 0 pour insérer au début et 2 pour insérer après la «&nbsp;liste des membres&nbsp;»). Séparez chaque entrée par un retour à la ligne.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Signalements</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Méthode de signalement</th>
									<td>
										<input type="radio" name="form[report_method]" value="0"<?php if ($pun_config['o_report_method'] == '0') echo ' checked="checked"' ?> />&nbsp;Interne&nbsp;&nbsp;&nbsp;<input type="radio" name="form[report_method]" value="1"<?php if ($pun_config['o_report_method'] == '1') echo ' checked="checked"' ?> />&nbsp;E-mail&nbsp;&nbsp;&nbsp;<input type="radio" name="form[report_method]" value="2"<?php if ($pun_config['o_report_method'] == '2') echo ' checked="checked"' ?> />&nbsp;Les deux
										<span>Choisissez la méthode pour être avertis de nouveaux signalements. Pour la méthode par e-mail la liste d'adresses ci-dessous sera utilisée.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Signalement nouvelle inscription</th>
									<td>
										<input type="radio" name="form[regs_report]" value="1"<?php if ($pun_config['o_regs_report'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[regs_report]" value="0"<?php if ($pun_config['o_regs_report'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Si activée, FluxBB enverra un e-mail à chacune des adresses de la liste ci-dessous à chaque nouvelle inscription sur les forums.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Liste d'adresses e-mail</th>
									<td>
										<textarea name="form[mailing_list]" rows="5" cols="55"><?php echo pun_htmlspecialchars($pun_config['o_mailing_list']) ?></textarea>
										<span>Utilisez une virgule pour séparer les adresses. Les adresses de cette liste sont les destinataires des signalements envoyés par e-mail.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Avatars</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Utiliser les avatars</th>
									<td>
										<input type="radio" name="form[avatars]" value="1"<?php if ($pun_config['o_avatars'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[avatars]" value="0"<?php if ($pun_config['o_avatars'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Si cette option est activée, les utilisateurs pourront envoyer sur le serveur un avatar qui sera affiché sous leur titre/rang.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Répertoire avatars</th>
									<td>
										<input type="text" name="form[avatars_dir]" size="35" maxlength="50" value="<?php echo pun_htmlspecialchars($pun_config['o_avatars_dir']) ?>" />
										<span>Le dossier où les avatars seront envoyés (chemin relatif au dossier racine de FluxBB). <acronym title="PHP: Hypertext Preprocessor" lang="en">PHP</acronym> doit avoir les droits en écriture dans ce répertoire.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Largeur maximum</th>
									<td>
										<input type="text" name="form[avatars_width]" size="5" maxlength="5" value="<?php echo $pun_config['o_avatars_width'] ?>" />
										<span>La largeur maximum admise (exprimée en pixels) pour les avatars (60 est recommandé).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Hauteur maximum</th>
									<td>
										<input type="text" name="form[avatars_height]" size="5" maxlength="5" value="<?php echo $pun_config['o_avatars_height'] ?>" />
										<span>La hauteur maximum admise (exprimée en pixels) pour les avatars (60 est recommandé).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Taille maximum</th>
									<td>
										<input type="text" name="form[avatars_size]" size="6" maxlength="6" value="<?php echo $pun_config['o_avatars_size'] ?>" />
										<span>La taille maximum admise (exprimée en octets) pour les avatars (10240 est recommandé).</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>E-mail</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">E-mail administrateur</th>
									<td>
										<input type="text" name="form[admin_email]" size="50" maxlength="50" value="<?php echo $pun_config['o_admin_email'] ?>" />
										<span>L'adresse e-mail de l'administrateur des forums.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">E-mail webmaster</th>
									<td>
										<input type="text" name="form[webmaster_email]" size="50" maxlength="50" value="<?php echo $pun_config['o_webmaster_email'] ?>" />
										<span>Ceci est l'adresse qui sera utilisée par tous les messages envoyés par les forums.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Abonnements</th>
									<td>
										<input type="radio" name="form[subscriptions]" value="1"<?php if ($pun_config['o_subscriptions'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[subscriptions]" value="0"<?php if ($pun_config['o_subscriptions'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Activer l'abonnement des utilisateurs aux discussions  (réception d'un e-mail lors d'une réponse).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Adresse serveur <acronym title="Simple Mail Transfer Protocol" lang="en">SMTP</acronym></th>
									<td>
										<input type="text" name="form[smtp_host]" size="30" maxlength="100" value="<?php echo pun_htmlspecialchars($pun_config['o_smtp_host']) ?>" />
										<span>L'adresse d'un serveur <acronym title="Simple Mail Transfer Protocol" lang="en">SMTP</acronym> externe pour envoyer des e-mails. Vous pouvez spécifier un numéro de port si le serveur <acronym title="Simple Mail Transfer Protocol" lang="en">SMTP</acronym> n'utilise pas le port par défaut 25 (<abbr title="exemple">ex.</abbr> smtp.monhote.com:3580). Laissez vide pour utiliser le programme local d'envoi d'e-mails.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Nom d'utilisateur <acronym title="Simple Mail Transfer Protocol" lang="en">SMTP</acronym></th>
									<td>
										<input type="text" name="form[smtp_user]" size="25" maxlength="50" value="<?php echo pun_htmlspecialchars($pun_config['o_smtp_user']) ?>" />
										<span>Nom d'utilisateur du serveur <acronym title="Simple Mail Transfer Protocol" lang="en">SMTP</acronym>. Saisissez un nom d'utilisateur seulement si cela est nécessaire pour le serveur <acronym title="Simple Mail Transfer Protocol" lang="en">SMTP</acronym> (la plupart des serveurs <strong>ne demande pas</strong> d'authentification).</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Mot de passe <acronym title="Simple Mail Transfer Protocol" lang="en">SMTP</acronym></th>
									<td>
										<input type="text" name="form[smtp_pass]" size="25" maxlength="50" value="<?php echo pun_htmlspecialchars($pun_config['o_smtp_pass']) ?>" />
										<span>Le mot de passe pour le serveur <acronym title="Simple Mail Transfer Protocol" lang="en">SMTP</acronym>. Saisissez un mot de passe seulement si cela est nécessaire pour le serveur <acronym title="Simple Mail Transfer Protocol" lang="en">SMTP</acronym> (la plupart des serveurs <strong>ne demande pas</strong> d'authentification).</span>
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
									<th scope="row">Autoriser les nouvelles inscriptions</th>
									<td>
										<input type="radio" name="form[regs_allow]" value="1"<?php if ($pun_config['o_regs_allow'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[regs_allow]" value="0"<?php if ($pun_config['o_regs_allow'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Permet d'accepter ou non les nouvelles inscriptions aux forums. Désactivez seulement en cas de circonstances spéciales.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Vérification des inscriptions</th>
									<td>
										<input type="radio" name="form[regs_verify]" value="1"<?php if ($pun_config['o_regs_verify'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[regs_verify]" value="0"<?php if ($pun_config['o_regs_verify'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Si cette option est activée, lors de leur inscription les utilisateurs recevront par e-mail un mot de passe aléatoire. Ils pourront alors se connecter et, si ils le souhaitent, changer le mot de passe depuis leur profil. Cette option nécessite que les utilisateurs valident leur adresse e-mail si jamais ils veulent la changer depuis leur profil. C'est une bonne méthode pour limiter les inscriptions abusives et pour être sûr que les utilisateurs ont une adresse e-mail valide dans leur profil.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Activer règles d'utilisation</th>
									<td>
										<input type="radio" name="form[rules]" value="1"<?php if ($pun_config['o_rules'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[rules]" value="0"<?php if ($pun_config['o_rules'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Lorsque cette option est activée, les utilisateurs devront accepter les règles d'utilisation lors de leur inscription (saisissez le texte ci-dessous). Les règles d'utilisation seront également consultables depuis un lien situé dans la barre principale en haut de chaque pages.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Règles d'utilisation</th>
									<td>
										<textarea name="form[rules_message]" rows="10" cols="55"><?php echo pun_htmlspecialchars($pun_config['o_rules_message']) ?></textarea>
										<span>Ici vous pouvez saisir toutes les règles d'utilisation ou toutes autres informations que les utilisateurs devront lire et accepter lors de leur inscription. Si vous activez les règles d'utilisation ci-dessus vous devez saisir quelque chose ici, sinon elles seront désactivées. Ce texte n'est pas analysé comme un message des forums et peut contenir du <acronym title="HyperText Markup Language" lang="en">HTML</acronym>.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Annonce</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row">Afficher annonce</th>
									<td>
										<input type="radio" name="form[announcement]" value="1"<?php if ($pun_config['o_announcement'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[announcement]" value="0"<?php if ($pun_config['o_announcement'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Activez cette option pour afficher sur les forums le texte ci-dessous.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Message de l'annonce</th>
									<td>
										<textarea name="form[announcement_message]" rows="5" cols="55"><?php echo pun_htmlspecialchars($pun_config['o_announcement_message']) ?></textarea>
										<span>Ce texte n'est pas analysé comme un message des forums et peut contenir du <acronym title="HyperText Markup Language" lang="en">HTML</acronym>.</span>
									</td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
				<div class="inform">
					<fieldset>
						<legend>Maintenance</legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><a name="maintenance">Mode maintenance</a></th>
									<td>
										<input type="radio" name="form[maintenance]" value="1"<?php if ($pun_config['o_maintenance'] == '1') echo ' checked="checked"' ?> />&nbsp;<strong>Oui</strong>&nbsp;&nbsp;&nbsp;<input type="radio" name="form[maintenance]" value="0"<?php if ($pun_config['o_maintenance'] == '0') echo ' checked="checked"' ?> />&nbsp;<strong>Non</strong>
										<span>Lorsque cette fonctionnalité est activée, les forums ne seront disponibles que pour les administrateurs. Ceci peut être utilisé si les forums doivent être fermés temporairement pour maintenance. ATTENTION ! Ne vous déconnectez pas lorsque les forums sont en mode maintenance. Vous ne pourrez pas vous reconnecter.</span>
									</td>
								</tr>
								<tr>
									<th scope="row">Message de maintenance</th>
									<td>
										<textarea name="form[maintenance_message]" rows="5" cols="55"><?php echo pun_htmlspecialchars($pun_config['o_maintenance_message']) ?></textarea>
										<span>Le message qui sera affiché aux utilisateurs lorsque les forums sont en mode maintenance. Si laissé vide, le message par défaut sera utilisé. Ce texte n'est pas analysé comme un message des forums et peut contenir du <acronym title="HyperText Markup Language" lang="en">HTML</acronym>.</span>
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
