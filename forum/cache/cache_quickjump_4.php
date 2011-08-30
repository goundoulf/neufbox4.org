<?php

if (!defined('PUN')) exit;
define('PUN_QJ_LOADED', 1);

?>				<form id="qjump" method="get" action="viewforum.php">
					<div><label><?php echo $lang_common['Jump to'] ?>

					<br /><select name="id" onchange="window.location=('viewforum.php?id='+this.options[this.selectedIndex].value)">
						<optgroup label="Communauté">
							<option value="7"<?php echo ($forum_id == 7) ? ' selected="selected"' : '' ?>>Présentation</option>
							<option value="4"<?php echo ($forum_id == 4) ? ' selected="selected"' : '' ?>>Discussions générales</option>
							<option value="2"<?php echo ($forum_id == 2) ? ' selected="selected"' : '' ?>>neufbox 4 - Firmware officiel</option>
							<option value="12"<?php echo ($forum_id == 12) ? ' selected="selected"' : '' ?>>neufbox 4 - Firmware OpenWrt</option>
							<option value="15"<?php echo ($forum_id == 15) ? ' selected="selected"' : '' ?>>neufbox 4 - Firmware modifié</option>
							<option value="17"<?php echo ($forum_id == 17) ? ' selected="selected"' : '' ?>>neufbox 6</option>
							<option value="16"<?php echo ($forum_id == 16) ? ' selected="selected"' : '' ?>>NoBox &amp; auto-hébergement</option>
							<option value="3"<?php echo ($forum_id == 3) ? ' selected="selected"' : '' ?>>Hardware</option>
							<option value="14"<?php echo ($forum_id == 14) ? ' selected="selected"' : '' ?>>OpenAdsl &amp; OpenVoip</option>
							<option value="9"<?php echo ($forum_id == 9) ? ' selected="selected"' : '' ?>>Espace détente</option>
							<option value="10"<?php echo ($forum_id == 10) ? ' selected="selected"' : '' ?>>English</option>
						</optgroup>
						<optgroup label="Portail">
							<option value="5"<?php echo ($forum_id == 5) ? ' selected="selected"' : '' ?>>Les news du blog</option>
							<option value="6"<?php echo ($forum_id == 6) ? ' selected="selected"' : '' ?>>Les articles du wiki</option>
					</optgroup>
					</select>
					<input type="submit" value="<?php echo $lang_common['Go'] ?>" accesskey="g" />
					</label></div>
				</form>
