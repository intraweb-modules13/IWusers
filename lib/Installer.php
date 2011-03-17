<?php
/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2002, PostNuke Development Team
 * @link http://www.postnuke.com
 * @version $Id: pninit.php 22139 2007-06-01 10:57:16Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PostNuke_Value_Addons
 * @subpackage Webbox
 */

/**
 * Initialise the iw_users module creating module tables and module vars
 * @author Albert Pérez Monfort (aperezm@xtec.cat)
 * @return bool true if successful, false otherwise
 */
function iw_users_init()
{
	$dom=ZLanguage::getModuleDomain('iw_users');
	// Checks if module iw_main is installed. If not returns error
	$modid = ModUtil::getIdFromName('iw_main');
	$modinfo = ModUtil::getInfo($modid);
	if($modinfo['state'] != 3){
		return LogUtil::registerError (__('Module iw_main is needed. You have to install the iw_main module before installing it.', $dom));
	}
	// Check if the version needed is correct
	$versionNeeded = '2.0';
	if(!ModUtil::func('iw_main', 'admin', 'checkVersion', array('version' => $versionNeeded))){
		return false;
	}
	// Create module table
	if (!DBUtil::createTable('iw_users')) return false;
	if (!DBUtil::createTable('iw_users_import')) return false;
	if (!DBUtil::createTable('iw_users_aux')) return false;
	if (!DBUtil::createTable('iw_users_friends')) return false;
	// Create the index
	if (!DBUtil::createIndex('iw_uid', 'iw_users', 'uid')) return false;
	if (!DBUtil::createIndex('iw_uid', 'iw_users_import', 'uid')) return false;
	if (!DBUtil::createIndex('iw_uid', 'iw_users_aux', 'uid')) return false;
	if (!DBUtil::createIndex('iw_uid', 'iw_users_friends', 'uid')) return false;
	if (!DBUtil::createIndex('iw_fid', 'iw_users_friends', 'fid')) return false;
	//Create module vars
	ModUtil::setVar('iw_main', 'friendsSystemAvailable', 1);
	ModUtil::setVar('iw_main', 'invisibleGroupsInList', '$');
  	return true;
}

/**
 * Delete the iw_users module
 * @author Albert Pérez Monfort (aperezm@xtec.cat)
 * @return bool true if successful, false otherwise
 */
function iw_users_delete()
{
	// Delete module table
	DBUtil::dropTable('iw_users');
	DBUtil::dropTable('iw_users_import');
	DBUtil::dropTable('iw_users_aux');
	DBUtil::dropTable('iw_users_friends');
	//Create module vars
	ModUtil::delVar('iw_main', 'friendsLabel');
	ModUtil::delVar('iw_main', 'friendsSystemAvailable');
	ModUtil::delVar('iw_main', 'invisibleGroupsInList', '$');
	//Deletion successfull
	return true;
}

/**
 * Update the iw_users module
 * @author Albert Pérez Monfort (aperezm@xtec.cat)
 * @return bool true if successful, false otherwise
 */
function iw_users_upgrade($oldversion)
{
	if ($oldversion < 1.2) {
		if (!DBUtil::changeTable('iw_users')) return false;
		if (!DBUtil::changeTable('iw_users_import')) return false;
		if (!DBUtil::createTable('iw_users_friends')) return false;
		if (!DBUtil::createIndex('iw_uid', 'iw_users_friends', 'uid')) return false;
		if (!DBUtil::createIndex('iw_fid', 'iw_users_friends', 'fid')) return false;
		//Create module vars
		ModUtil::delVar('iw_main', 'friendsLabel');
		ModUtil::setVar('iw_main', 'friendsSystemAvailable', 1);
		ModUtil::setVar('iw_main', 'invisibleGroupsInList', '$');
	}
	return true;
}