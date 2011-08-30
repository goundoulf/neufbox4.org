<?php

    /**
     * Ce fichier permet à MediaWiki d'utiliser la base de donnée
     * d'utilisateurs de PunBB plutôt que celle de MediaWiki.
     * Les utilisateurs sont donc forcés d'avoir un compte sur le
     * forum PunBB.
     *
     * Un groupe peut être interdit de login dans le wiki.
     *
     * This program is free software; you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation; either version 2 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License along
     * with this program; if not, write to the Free Software Foundation, Inc.,
     * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
     * http://www.gnu.org/copyleft/gpl.html
     *
     * @package MediaWiki
     * @subpackage Auth_PunBB
     * @original_author Nicholas Dunnaway
     * @author Jordan Bracco
     * @copyright 2006-2007 irrealia development group
     * @license http://www.gnu.org/copyleft/gpl.html
     * @link http://irrealia.org/trac/wiki/PHP/MediaBB
     * @version 1.0
     *
     */

    error_reporting(E_ALL); // Debug

    // First check if class has already been defined.
    if (!class_exists('AuthPlugin')) {

        /**
         * Auth Plugin
         *
         */
        require_once("includes/AuthPlugin.php");

    } // End: if (!class_exists('AuthPlugin')) {

    /**
     * Handles the Authentication with the PunBB database.
     *
     */
    class Auth_Pun extends AuthPlugin {

		// constants for error reporting
		const DEBUG_INFO    = 1;
		const DEBUG_WARNING = 2;
		const DEBUG_ERROR   = 3;

        /**
         * Add a user to the external authentication database.
         * Return true if successful.
         *
         * NOTE: We are not allowed to add users to punBB from the
         * wiki so this always returns false.
         *
         * @param User $user
         * @param string $password
         * @return bool
         * @access public
         */
        function addUser( $user, $password ) {
            return false;
        } // End: addUser()

        /**
         * Check if a username+password pair is a valid login.
         * The name will be normalized to MediaWiki's requirements, so
         * you might need to munge it (for instance, for lowercase initial
         * letters).
         *
         * @param string $username
         * @param string $password
         * @return bool
         * @access public
         * @todo Check if the password is being changed when it contains a slash or an escape char.
         */
        function authenticate($username, $password) {
        	global $gstrMySQLVersion, $wgPun_UserTB;

            // Connect to the database.
            $fresMySQLConnection = $this->connect();

            // Clean $username and force lowercase username.
            $username = htmlentities(strtolower($username), ENT_QUOTES, 'UTF-8');
            $username = str_replace('&#039;', '\\\'', $username); // Allow apostrophes (Escape them though)

            // Check MySQLVersion
            if ($gstrMySQLVersion >= 4.1) {
                // Check Database for username and password.
                $fstrMySQLQuery = 'SELECT `username`, `password`
                                   FROM `' . $wgPun_UserTB . '`
                                   WHERE `username` = CONVERT( _utf8 \'' . $username . '\' USING latin1 )
                                   COLLATE latin1_swedish_ci
                                   LIMIT 1';
            } else {
                // Check Database for username and password.
                $fstrMySQLQuery = 'SELECT `username`, `password`
                                   FROM `' . $wgPun_UserTB . '`
                                   WHERE `username` = \'' . $username . '\'
                                   LIMIT 1';
            } // End: if ($GLOBALS['gstrMySQLVersion'] >= 4.1)

            // Query Database.
            $fresMySQLResult = mysql_query($fstrMySQLQuery) //<-
                or die('Unable to view external table: ' . mysql_error());

            while($faryMySQLResult = mysql_fetch_array($fresMySQLResult)){
                // Check if password submited matches the PunBB password.
                // Also check if user is a member of the punbb group (ban access)

				$this->printDebug( 'Password entered: ' . $password, self::DEBUG_INFO );
				$this->printDebug( 'Hash of password entered: ' . $this->pwd_hash($password), self::DEBUG_INFO );
				$this->printDebug( 'Hash of password from db: ' . $faryMySQLResult['password'], self::DEBUG_INFO );

				$this->printDebug( 'isMemberOfWikiGroup returned ' . ($this->isMemberOfWikiGroup($username) ? 'true' : 'false'), self::DEBUG_INFO );

                if ($this->pwd_hash($password) == $faryMySQLResult['password'] && $this->isMemberOfWikiGroup($username)) {
                	$this->printDebug( 'authenticate() returned true', self::DEBUG_INFO );
                    return true;
                } // End: if ($this->pwd_hash($password) == $faryMySQLResult['password'] && $this->isMemberOfWikiGroup($username)
            } // End: while($faryMySQLResult = mysql_fetch_array($fresMySQLResult))
            $this->printDebug( 'authenticate() returned false', self::DEBUG_INFO );
            return false;
        } // End: authenticate()


		/**
		 * HACK puntal.fr
		 * PunBB n'utilise pas forcément md5
		 */
		function pwd_hash( $str ) {
				if (function_exists('sha1'))    // Only in PHP 4.3.0+
						return sha1($str);
				else if (function_exists('mhash'))      // Only if Mhash library is loaded
						return bin2hex(mhash(MHASH_SHA1, $str));
				else
						return md5($str);
		} // End: pwd_hash()

        /**
         * Return true if the wiki should create a new local account automatically
         * when asked to login a user who doesn't exist locally but does in the
         * external auth database.
         *
         * If you don't automatically create accounts, you must still create
         * accounts in some way. It's not possible to authenticate without
         * a local account.
         *
         * This is just a question, and shouldn't perform any actions.
         *
         * NOTE: I have set this to true to allow the wiki to create accounts.
         *       Without an account in the wiki database a user will never be
         *       able to login and use the wiki. I think the password does not
         *       matter as long as authenticate() returns true.
         *
         * @return bool
         * @access public
         */
        function autoCreate() {
            return true;
        } // End: autoCreate()

        /**
         * Check to see if external accounts can be created.
         * Return true if external accounts can be created.
         *
         * NOTE: We are not allowed to add users to punBB from the
         * wiki so this always returns false.
         *
         * @return bool
         * @access public
         */
        function canCreateAccounts() {
            return false;
        } // End: canCreateAccounts()

		/**
		 * Can users change their passwords?
		 *
		 * @return bool
		 */
		function allowPasswordChange() {
			return false;
		} // End: allowPasswordChange()

        /**
         * Connect to the database. All of these settings are from the
         * LocalSettings.php file. This assumes that the PunBB uses the same
         * database/server as the wiki.
         *
         * {@source }
         * @return resource
         */
        function connect() {
        	global $wgPun_UseExtDatabase, $wgPun_MySQL_Host, $wgPun_MySQL_Database;
        	global $wgPun_MySQL_Username, $wgPun_MySQL_Password;
        	global $wgDBserver, $wgDBuser, $wgDBpassword, $wgDBname;

            // Check if the punBB tables are in a different database then the Wiki.
            if ($wgPun_UseExtDatabase == true) {

                // Connect to database.
                $fresMySQLConnection = mysql_connect($wgPun_MySQL_Host,  //<-
                                                     $wgPun_MySQL_Username, //<-
                                                     $wgPun_MySQL_Password) //<-
                    or die('Unable to connect to external database: ' . mysql_error());

                // Select Database
                mysql_select_db($wgPun_MySQL_Database) //<-
                                or die('Unable to open external database (' . //<-
                                $wgPun_MySQL_Database . ') ' . mysql_error());

            } else {

                // Connect to database.
                $fresMySQLConnection = mysql_connect($wgDBserver, $wgDBuser, //<-
                    $wgDBpassword) or die('Unable to connect to external database: ' .  //<-
                    mysql_error());

                // Select Database: This assumes the wiki and phpbb are in the same database.
                mysql_select_db($wgDBname) or die('Unable to open external database (' . //<-
                                                          $wgDBname . ') ' . mysql_error());
            } // End: if ($wgPun_UseExtDatabase == true)

            $GLOBALS['gstrMySQLVersion'] = substr(mysql_get_server_info(), 0, 3); // Get the mysql version.

            return $fresMySQLConnection;
        } // End: connect

        /**
         * If you want to munge the case of an account name before the final
         * check, now is your chance.
         */
        function getCanonicalName( $username ) {
            // Connect to the database.
            $fresMySQLConnection = $this->connect();

            // Clean $username and force lowercase username.
            $username = htmlentities(strtolower($username), ENT_QUOTES, 'UTF-8');
            $username = str_replace('&#039;', '\\\'', $username); // Allow apostrophes (Escape them though)

            // Check MySQLVersion
            if ($GLOBALS['gstrMySQLVersion'] >= 4.1) {

                // Check Database for username. We will return the correct casing of the name.
                $fstrMySQLQuery = 'SELECT `username`
                                   FROM `' . $GLOBALS['wgPun_UserTB'] . '`
                                   WHERE `username` = CONVERT( _utf8 \'' . $username . '\' USING latin1 )
                                   COLLATE latin1_swedish_ci
                                   LIMIT 1';
            } else {

                // Check Database for username. We will return the correct casing of the name.
                $fstrMySQLQuery = 'SELECT `username`
                                   FROM `' . $GLOBALS['wgPun_UserTB'] . '`
                                   WHERE `username` = \'' . $username . '\'
                                   LIMIT 1';
            } // End: if ($GLOBALS['gstrMySQLVersion'] >= 4.1)

            // Query Database.
            $fresMySQLResult = mysql_query($fstrMySQLQuery) //<-
                or die('Unable to view external table: ' . mysql_error());

            while($faryMySQLResult = mysql_fetch_assoc($fresMySQLResult)){
                return ucfirst($faryMySQLResult['username']);
            } // End: while($faryMySQLResult = mysql_fetch_array($fresMySQLResult))
        } // End: getCanonicalName()

        /**
         * When creating a user account, optionally fill in preferences and such.
         * For instance, you might pull the email address or real name from the
         * external user database.
         *
         * The User object is passed by reference so it can be modified; don't
         * forget the & on your function declaration.
         *
         * NOTE: This gets the email address from punBB for the wiki account.
         *
         * @param User $user
         * @access public
         */
        function initUser(&$user) {

            // Connect to the database.
            $fresMySQLConnection = $this->connect();

            // Clean $username and force lowercase username.
            $username = htmlentities(strtolower($user->mName), ENT_QUOTES, 'UTF-8');
            $username = str_replace('&#039;', '\\\'', $username); // Allow apostrophes (Escape them though)

            // Check MySQLVersion
            if ($GLOBALS['gstrMySQLVersion'] >= 4.1) {
                // Check Database for username and email address.
                $fstrMySQLQuery = 'SELECT `username`, `email`
                                   FROM `' . $GLOBALS['wgPun_UserTB'] . '`
                                   WHERE `username` = CONVERT( _utf8 \'' . $username . '\' USING latin1 )
                                   COLLATE latin1_swedish_ci
                                   LIMIT 1';
            } else {
                // Check Database for username and email address.
                $fstrMySQLQuery = 'SELECT `username`, `email`
                                   FROM `' . $GLOBALS['wgPun_UserTB'] . '`
                                   WHERE `username` = \'' . $username . '\'
                                   LIMIT 1';
            } // End: if ($GLOBALS['gstrMySQLVersion'] >= 4.1)

            // Query Database.
            $fresMySQLResult = mysql_query($fstrMySQLQuery) //<-
                or die('Unable to view external table: ' . mysql_error());

            while($faryMySQLResult = mysql_fetch_array($fresMySQLResult)){
                $user->mEmail       = $faryMySQLResult['email']; // Set Email Address.
                $user->mRealName    = $username;  // Set Real Name.
            } // End: while($faryMySQLResult = mysql_fetch_array($fresMySQLResult))

        } // End: initUser()

        /**
         * Checks if the user is a member of the PHPBB group called wiki.
         *
         * @param string $username
         * @access public
         * @return bool
         * @todo Remove 2nd connection to database. For function isMemberOfWikiGroup()
         *
         */
        function isMemberOfWikiGroup($username){

            // In LocalSettings.php you can control if being a member of a wiki
            // is required or not.
            if (isset($GLOBALS['wgPun_NUseWikiGroup']) && $GLOBALS['wgPun_NUseWikiGroup'] === true){
                return true;
            } // End: isset($GLOBALS['wgPun_NUseWikiGroup']) && $GLOBALS['wgPun_NUseWikiGroup'] === true

            // Connect to the database.
            $fresMySQLConnection = $this->connect();

            /**
             *  This is a great query. It takes the username and gets the userid. Then
             *  it gets the group_id number of the the Wiki group. Last it checks if the
             *  userid and groupid are matched up. (The user is in the wiki group.)
             *
             *  Last it returns TRUE or FALSE on if the user is in the wiki group.
             */

            // Get UserId
            mysql_query('SELECT @userId := `id` FROM `' . $GLOBALS['wgPun_UserTB'] . //<-
                        '` WHERE `username` = \'' . $username . '\';') //<-
                        or die('Unable to get userID: ' . mysql_error());

            // Get WikiId
            mysql_query('SELECT @wikiId := `g_id` FROM `' . $GLOBALS['wgPun_GroupsTB'] . //<-
                        '` WHERE `g_title` = \'' . $GLOBALS['wgPun_WikiGroupName'] . '\';') //<-
                        or die('Unable to get wikiID: ' . mysql_error());

            // Check UserId and WikiId
            mysql_query('SELECT @isThere := COUNT( * ) FROM `' . $GLOBALS['wgPun_User_GroupTB'] . //<-
                        '` WHERE `id` = @userId AND `group_id` = @wikiId;') //<-
                        or die('Unable to get validate user group: ' . mysql_error());

            // Return Result.
            $fstrMySQLQuery = 'SELECT IF(@isThere > 0, \'true\', \'false\') AS `result`;';

            // Query Database.
            $fresMySQLResult = mysql_query($fstrMySQLQuery) //<-
                or die('Unable to view external table: ' . mysql_error());

            // Check for a true or false response.
            while($faryMySQLResult = mysql_fetch_array($fresMySQLResult)){
                if ($faryMySQLResult['result'] == 'true') {
                    return true; // User is in Wiki group.
                } else {
                    return false; // User is not in Wiki group.
                } // End: ($faryMySQLResult['result'] == 'true')
            } // End: while($faryMySQLResult = mysql_fetch_array($fresMySQLResult))

        } // End: isMemberOfWikiGroup()

        /**
         * Modify options in the login template.
         *
         * NOTE: Turned off some Template stuff here. Anyone who knows where
         * to find all the template options please let me know. I was only able
         * to find a few.
         *
         * @param UserLoginTemplate $template
         * @access public
         */
        function modifyUITemplate( &$template ) {
            $template->set('usedomain',   false); // We do not want a domain name.
            $template->set('create',      false); // Remove option to create new accounts from the wiki.
            $template->set('useemail',    false); // Disable the mail new password box.
        } // End: modifyUITemplate()

        /**
         * Set the domain this plugin is supposed to use when authenticating.
         *
         * NOTE: We do not use this.
         *
         * @param string $domain
         * @access public
         */
        function setDomain( $domain ) {
            $this->domain = $domain;
        } // End: setDomain()

        /**
         * Set the given password in the authentication database.
         * Return true if successful.
         *
         * NOTE: We only allow the user to change their password via punBB.
         *
         * @param string $password
         * @return bool
         * @access public
         */
        function setPassword( $password ) {
            return false;
        } // End: setPassword()

        /**
         * Return true to prevent logins that don't authenticate here from being
         * checked against the local database's password fields.
         *
         * This is just a question, and shouldn't perform any actions.
         *
         * Note: This forces a user to pass Authentication with the above
         *       function authenticate(). So if a user changes their PHPBB
         *       password, their old one will not work to log into the wiki.
         *       Wiki does not have a way to update it's password when PHPBB
         *       does. This however does not matter.
         *
         * @return bool
         * @access public
         */
        function strict() {
            return true;
        } // End: strict()

        /**
         * When a user logs in, optionally fill in preferences and such.
         * For instance, you might pull the email address or real name from the
         * external user database.
         *
         * The User object is passed by reference so it can be modified; don't
         * forget the & on your function declaration.
         *
         * NOTE: Not useing right now.
         *
         * @param User $user
         * @access public
         */
        function updateUser( &$user ) {
            return true;
        } // End: updateUser()

        /**
         * Check whether there exists a user account with the given name.
         * The name will be normalized to MediaWiki's requirements, so
         * you might need to munge it (for instance, for lowercase initial
         * letters).
         *
         * NOTE: MediaWiki checks its database for the username. If it has
         *       no record of the username it then asks. "Is this really a
         *       valid username?" If not then MediaWiki fails Authentication.
         *
         * @param string $username
         * @return bool
         * @access public
         * @todo write this function.
         */
        function userExists($username) {

            // Connect to the database.
            $fresMySQLConnection = $this->connect();

            // Clean $username and force lowercase username.
            $username = htmlentities(strtolower($username), ENT_QUOTES, 'UTF-8');
            $username = str_replace('&#039;', '\\\'', $username); // Allow apostrophes (Escape them though)

            // Check MySQLVersion
            if ($GLOBALS['gstrMySQLVersion'] >= 4.1) {
                // Check Database for username.
                $fstrMySQLQuery = 'SELECT `username`
                                   FROM `' . $GLOBALS['wgPun_UserTB'] . '`
                                   WHERE `username` = CONVERT( _utf8 \'' . $username . '\' USING latin1 )
                                   COLLATE latin1_swedish_ci
                                   LIMIT 1';
            } else {
                // Check Database for username.
                $fstrMySQLQuery = 'SELECT `username`
                                   FROM `' . $GLOBALS['wgPun_UserTB'] . '`
                                   WHERE `username` = \'' . $username . '\'
                                   LIMIT 1';
            } // End: if ($GLOBALS['gstrMySQLVersion'] >= 4.1)

            // Query Database.
            $fresMySQLResult = mysql_query($fstrMySQLQuery) //<-
                or die('Unable to view external table: ' . mysql_error());

            while($faryMySQLResult = mysql_fetch_array($fresMySQLResult)){
            	$this->printDebug( htmlentities(strtolower($username), ENT_QUOTES, 'UTF-8') . ' : ' . htmlentities(strtolower($faryMySQLResult['username']), ENT_QUOTES, 'UTF-8'), self::DEBUG_INFO );

                // Double check match.
                if (htmlentities(strtolower($username), ENT_QUOTES, 'UTF-8') == htmlentities(strtolower($faryMySQLResult['username']), ENT_QUOTES, 'UTF-8')){
                    return true; // Pass
                } // End: if ($fresMySQLResult['username'] == $username)
            } // End: while($faryMySQLResult = mysql_fetch_array($fresMySQLResult))
            return false; // Fail
        } // End: userExists()

        /**
         * Update user information in the external authentication database.
         * Return true if successful.
         *
         * @param User $user
         * @return bool
         * @access public
         */
        function updateExternalDB( $user ) {
            return true;
        } // End: updateExternalDB()

        /**
         * Check to see if the specific domain is a valid domain.
         *
         * @param string $domain
         * @return bool
         * @access public
         */
        function validDomain( $domain ) {
            return true;
        } // End: validDomain()

		/**
		 * Prints debugging information. $debugText is what you want to print, $debugVal
		 * is the level at which you want to print the information.
		 *
		 * @param string $debugText
		 * @param string $debugVal
		 * @access private
		 */
		function printDebug( $debugText, $debugVal ) {
			global $wgPun_UserTB;

			if ( $wgPun_UserTB > $debugVal ) {
				echo $debugText . "<br>";
			}
		} // End: printDebug()

    } // End: class Auth_Pun

?>
