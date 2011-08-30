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


$action = isset($_GET['action']) ? $_GET['action'] : null;

// Check for upgrade
if ($action == 'check_upgrade')
{
	if (!ini_get('allow_url_fopen'))
		message('Impossible de vérifier les mises à jour tant que \'allow_url_fopen\' est désactivé sur ce système.');

	$latest_version = trim(@file_get_contents('http://fluxbb.org/latest_version'));
 	if (empty($latest_version))
		message('La vérification de mise à jour a échouée pour une raison inconnue.');

	if (version_compare($pun_config['o_cur_version'], $latest_version, '>='))
		message('Vous utilisez la dernière version de FluxBB.');
	else
		message('Une nouvelle version de FluxBB est disponible ! Vous pouvez télécharger cette dernière version sur <a href="http://fluxbb.org/">FluxBB.org</a>.');
}

// Check for french upgrade
if ($action == 'check_upgrade_fr')
{
	if (!ini_get('allow_url_fopen'))
		message('Impossible de vérifier les mises à jour tant que \'allow_url_fopen\' est désactivé sur ce système.');

	$latest_version = trim(@file_get_contents('http://www.fluxbb.fr/latest_version'));
 	if (empty($latest_version))
		message('La vérification de mise à jour a échouée pour une raison inconnue.');

	if (version_compare($pun_config['o_cur_version_fr'], $latest_version, '>='))
		message('Vous utilisez la dernière version de FluxBB en français.');
	else
		message('Une nouvelle version de FluxBB en français est disponible ! Vous pouvez télécharger cette dernière version sur <a href="http://www.fluxbb.fr/">FluxBB.fr</a>.');
}


// Show phpinfo() output
else if ($action == 'phpinfo' && $pun_user['g_id'] == PUN_ADMIN)
{
	// Is phpinfo() a disabled function?
	if (strpos(strtolower((string)@ini_get('disable_functions')), 'phpinfo') !== false)
		message('La fonction phpinfo() de PHP est désactivée sur ce serveur.');

	phpinfo();
	exit;
}


// Get the server load averages (if possible)
if (@file_exists('/proc/loadavg') && is_readable('/proc/loadavg'))
{
	// We use @ just in case
	$fh = @fopen('/proc/loadavg', 'r');
	$load_averages = @fread($fh, 64);
	@fclose($fh);

	$load_averages = @explode(' ', $load_averages);
	$server_load = isset($load_averages[2]) ? $load_averages[0].' '.$load_averages[1].' '.$load_averages[2] : 'Indisponible';
}
else if (!in_array(PHP_OS, array('WINNT', 'WIN32')) && preg_match('/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/i', @exec('uptime'), $load_averages))
	$server_load = $load_averages[1].' '.$load_averages[2].' '.$load_averages[3];
else
	$server_load = 'Indisponible';


// Get number of current visitors
$result = $db->query('SELECT COUNT(user_id) FROM '.$db->prefix.'online WHERE idle=0') or error('Unable to fetch online count', __FILE__, __LINE__, $db->error());
$num_online = $db->result($result);


// Get the database system version
switch ($db_type)
{
	case 'sqlite':
		$db_version = 'SQLite '.sqlite_libversion();
		break;

	default:
		$result = $db->query('SELECT VERSION()') or error('Unable to fetch version info', __FILE__, __LINE__, $db->error());
		$db_version = $db->result($result);
		break;
}


// Collect some additional info about MySQL
if ($db_type == 'mysql' || $db_type == 'mysqli')
{
	$db_version = 'MySQL '.$db_version;

	// Calculate total db size/row count
	$result = $db->query('SHOW TABLE STATUS FROM `'.$db_name.'`') or error('Unable to fetch table status', __FILE__, __LINE__, $db->error());

	$total_records = $total_size = 0;
	while ($status = $db->fetch_assoc($result))
	{
		$total_records += $status['Rows'];
		$total_size += $status['Data_length'] + $status['Index_length'];
	}

	$total_size = $total_size / 1024;

	if ($total_size > 1024)
		$total_size = round($total_size / 1024, 2).' MB';
	else
		$total_size = round($total_size, 2).' KB';
}


// See if MMCache or PHPA is loaded
if (function_exists('mmcache'))
	$php_accelerator = '<a href="http://turck-mmcache.sourceforge.net/">Turck MMCache</a>';
else if (isset($_PHPA))
	$php_accelerator = '<a href="http://www.php-accelerator.co.uk/">ionCube PHP Accelerator</a>';
else
	$php_accelerator = 'N/A';


$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / Admin';
require PUN_ROOT.'header.php';

generate_admin_menu('index');

?>
	<div class="block">
		<h2>Administration Forum</h2>
		<div id="adintro" class="box">
			<div class="inbox">
				<p>Bienvenue sur le panneau de contrôles de FluxBB. Depuis cet espace vous pouvez contrôler les points essentiels de votre forum. Selon que vous êtes un administrateur ou un modérateur vous pouvez&nbsp;:<br /><br />
					&nbsp;- organiser les catégories et les forums.<br />
					&nbsp;- régler les principales options et préférences.<br />
					&nbsp;- contrôler les permissions pour les utilisateurs et les visiteurs.<br />
					&nbsp;- voir les statistiques des IP pour les utilisateurs.<br />
					&nbsp;- bannir des utilisateurs.<br />
					&nbsp;- censurer des mots.<br />
					&nbsp;- régler les rangs des utilisateurs.<br />
					&nbsp;- élaguer les anciens messages.<br />
					&nbsp;- traiter les messages signalés.
				</p>
			</div>
		</div>

		<h2 class="block2"><span>Statistiques</span></h2>
		<div id="adstats" class="box">
			<div class="inbox">
				<dl>
					<dt>Version FluxBB</dt>
					<dd>
						FluxBB version française <?php echo $pun_config['o_cur_version_fr'] ?> basée sur FluxBB <?php echo $pun_config['o_cur_version'] ?><br />
						<a href="admin_index.php?action=check_upgrade">Vérifier la version officielle</a> - <a href="admin_index.php?action=check_upgrade_fr">Vérifier la version française</a>
					</dd>
					<dt>Exécution serveur</dt>
					<dd>
						<?php echo $server_load ?> (<?php echo $num_online ?> utilisateurs en ligne)
					</dd>
<?php if ($pun_user['g_id'] == PUN_ADMIN): ?>					<dt>Environnement</dt>
					<dd>
						Système d'exploitation&nbsp;: <?php echo PHP_OS ?><br />
						PHP&nbsp;: <?php echo phpversion() ?> - <a href="admin_index.php?action=phpinfo">Afficher infos</a><br />
						Accélérateur PHP&nbsp;: <?php echo $php_accelerator."\n" ?>
					</dd>
					<dt>Base de données</dt>
					<dd>
						<?php echo $db_version."\n" ?>
<?php if (isset($total_records) && isset($total_size)): ?>						<br />Lignes&nbsp;: <?php echo $total_records."\n" ?>
						<br />Taille&nbsp;: <?php echo $total_size."\n" ?>
<?php endif; endif; ?>					</dd>
				</dl>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

require PUN_ROOT.'footer.php';
