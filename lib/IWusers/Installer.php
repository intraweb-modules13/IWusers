<?php

class IWusers_Installer extends Zikula_Installer {

    function Install() {
        // Checks if module IWmain is installed. If not returns error
        $modid = ModUtil::getIdFromName('IWmain');
        $modinfo = ModUtil::getInfo($modid);
        if ($modinfo['state'] != 3) {
            return LogUtil::registerError($this->$this->__('Module IWmain is needed. You have to install the IWmain module before installing it.'));
        }
        // Check if the version needed is correct
        $versionNeeded = '2.0';
        if (!ModUtil::func('IWmain', 'admin', 'checkVersion',
                        array('version' => $versionNeeded))) {
            return false;
        }
        // Create module table
        if (!DBUtil::createTable('IWusers'))
            return false;
        if (!DBUtil::createTable('IWusers_import'))
            return false;
        if (!DBUtil::createTable('IWusers_aux'))
            return false;
        if (!DBUtil::createTable('IWusers_friends'))
            return false;
        // Create the index
        if (!DBUtil::createIndex('iw_uid', 'IWusers', 'uid'))
            return false;
        if (!DBUtil::createIndex('iw_uid', 'IWusers_import', 'uid'))
            return false;
        if (!DBUtil::createIndex('iw_uid', 'IWusers_aux', 'uid'))
            return false;
        if (!DBUtil::createIndex('iw_uid', 'IWusers_friends', 'uid'))
            return false;
        if (!DBUtil::createIndex('iw_fid', 'IWusers_friends', 'fid'))
            return false;
        //Create module vars
        ModUtil::setVar('IWmain', 'friendsSystemAvailable', 1);
        ModUtil::setVar('IWmain', 'invisibleGroupsInList', '$');
        return true;
    }

    /**
     * Delete the IWusers module
     * @author Albert Pérez Monfort (aperezm@xtec.cat)
     * @return bool true if successful, false otherwise
     */
    function Uninstall() {
        // Delete module table
        DBUtil::dropTable('IWusers');
        DBUtil::dropTable('IWusers_import');
        DBUtil::dropTable('IWusers_aux');
        DBUtil::dropTable('IWusers_friends');
        //Create module vars
        ModUtil::delVar('IWmain', 'friendsLabel');
        ModUtil::delVar('IWmain', 'friendsSystemAvailable');
        ModUtil::delVar('IWmain', 'invisibleGroupsInList', '$');
        ModUtil::delVar('IWmain', 'usersCanManageName', 0);
        //Deletion successfull
        return true;
    }

    /**
     * Update the IWusers module
     * @author Albert Pérez Monfort (aperezm@xtec.cat)
     * @return bool true if successful, false otherwise
     */
    function Upgrade($oldversion) {
        return true;
    }
}