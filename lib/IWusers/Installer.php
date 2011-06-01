<?php

class IWusers_Installer extends Zikula_AbstractInstaller {

    function Install() {
        // Checks if module IWmain is installed. If not returns error
        $modid = ModUtil::getIdFromName('IWmain');
        $modinfo = ModUtil::getInfo($modid);
        if ($modinfo['state'] != 3) {
            return LogUtil::registerError($this->$this->__('Module IWmain is needed. You have to install the IWmain module before installing it.'));
        }
        // Check if the version needed is correct
        $versionNeeded = '3.0.0';
        if (!ModUtil::func('IWmain', 'admin', 'checkVersion', array('version' => $versionNeeded))) {
            return false;
        }
        // Create module table
        if (!DBUtil::createTable('IWusers'))
            return false;
        if (!DBUtil::createTable('IWusers_friends'))
            return false;
        // Create the index
        if (!DBUtil::createIndex('iw_uid', 'IWusers', 'uid'))
            return false;
        if (!DBUtil::createIndex('iw_uid', 'IWusers_friends', 'uid'))
            return false;
        if (!DBUtil::createIndex('iw_fid', 'IWusers_friends', 'fid'))
            return false;
        //Create module vars
        $this->setVar('friendsSystemAvailable', 1)
                ->setVar('invisibleGroupsInList', '$')
                ->setVar('usersCanManageName', 0)
                ->setVar('allowUserChangeAvatar', '1')
                ->setVar('allowUserSetTheirSex', '0')
                ->setVar('allowUserDescribeTheirSelves', '1')
                ->setVar('avatarChangeValidationNeeded', '1')
                ->setVar('usersPictureFolder', 'photos');
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
        DBUtil::dropTable('IWusers_friends');
        //Delete module vars
        $this->delVar('friendsSystemAvailable')
                ->delVar('invisibleGroupsInList')
                ->delVar('usersCanManageName')
                ->delVar('allowUserChangeAvatar')
                ->delVar('allowUserSetTheirSex')
                ->delVar('allowUserDescribeTheirSelves')
                ->delVar('avatarChangeValidationNeeded')
                ->delVar('usersPictureFolder');

        //Deletion successfull
        return true;
    }

    /**
     * Update the IWusers module
     * @author Albert Pérez Monfort (aperezm@xtec.cat)
     * @author Jaume Fernàndez Valiente (jfern343@xtec.cat)
     * @return bool true if successful, false otherwise
     */
    function Upgrade($oldversion) {

        $prefix = $GLOBALS['ZConfig']['System']['prefix'];

        //Delete unneeded tables
        DBUtil::dropTable('iw_users_aux');
        DBUtil::dropTable('iw_users_import');

        //Rename tables

        if (!DBUtil::renameTable('iw_users', 'IWusers'))
            return false;
        if (!DBUtil::renameTable('iw_users_friends', 'IWusers_friends'))
            return false;

        // Add new columns
        $c = "ALTER TABLE `{$prefix}_IWusers` ADD `iw_avatar` VARCHAR(50) NOT NULL";
        if (!DBUtil::executeSQL($c))
            return false;
        $c = "ALTER TABLE `{$prefix}_IWusers` ADD `iw_newavatar` VARCHAR(50) NOT NULL";
        if (!DBUtil::executeSQL($c))
            return false;
        $c = "ALTER TABLE `{$prefix}_IWusers` ADD `iw_sex` TINYINT( 4 ) NOT NULL DEFAULT '0'";
        if (!DBUtil::executeSQL($c))
            return false;

        // Delete unneded columns
        $c = array();
        $c[] = "ALTER TABLE `{$prefix}_IWusers` DROP `iw_mobile` ";
        $c[] = "ALTER TABLE `{$prefix}_IWusers` DROP `iw_fix` ";
        $c[] = "ALTER TABLE `{$prefix}_IWusers` DROP `iw_parentsName` ";
        $c[] = "ALTER TABLE `{$prefix}_IWusers` DROP `iw_address` ";
        $c[] = "ALTER TABLE `{$prefix}_IWusers` DROP `iw_postal` ";
        $c[] = "ALTER TABLE `{$prefix}_IWusers` DROP `iw_identifyCard` ";
        $c[] = "ALTER TABLE `{$prefix}_IWusers` DROP `iw_refUser` ";
        $c[] = "ALTER TABLE `{$prefix}_IWusers` DROP `iw_sendSMS` ";
        $c[] = "ALTER TABLE `{$prefix}_IWusers` DROP `iw_active` ";
        $c[] = "ALTER TABLE `{$prefix}_IWusers` DROP `iw_parentsEMail` ";
        foreach ($c as $sql) {
            DBUtil::executeSQL($sql);
        }

        // Modify column names
        $c = array();
        $c = "ALTER TABLE `{$prefix}_IWusers` CHANGE `zk_obj_status` `pn_obj_status` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT \'A\'";
        $c = "ALTER TABLE `{$prefix}_IWusers` CHANGE `zk_cr_date` `pn_cr_date` DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:00\'";
        $c = "ALTER TABLE `{$prefix}_IWusers` CHANGE `zk_cr_uid` `pn_cr_uid` INT(11) NOT NULL DEFAULT \'0\'";
        $c = "ALTER TABLE `{$prefix}_IWusers` CHANGE `zk_lu_date` `pn_lu_date` DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:00\'";
        $c = "ALTER TABLE `{$prefix}_IWusers` CHANGE `zk_lu_uid` `pn_lu_uid` INT(11) NOT NULL DEFAULT \'0\'";

        $c = "ALTER TABLE `{$prefix}_IWusers_friends` CHANGE `zk_obj_status` `pn_obj_status` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT \'A\'";
        $c = "ALTER TABLE `{$prefix}_IWusers_friends` CHANGE `zk_cr_date` `pn_cr_date` DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:00\'";
        $c = "ALTER TABLE `{$prefix}_IWusers_friends` CHANGE `zk_cr_uid` `pn_cr_uid` INT(11) NOT NULL DEFAULT \'0\'";
        $c = "ALTER TABLE `{$prefix}_IWusers_friends` CHANGE `zk_lu_date` `pn_lu_date` DATETIME NOT NULL DEFAULT \'1970-01-01 00:00:00\'";
        $c = "ALTER TABLE `{$prefix}_IWusers_friends` CHANGE `zk_lu_uid` `pn_lu_uid` INT(11) NOT NULL DEFAULT \'0\'";
        foreach ($c as $sql) {
            if (!DBUtil::executeSQL($sql))
                return false;
        }

        // Update module_vars table
        // Update the name (keeps old var value)
        $c = "UPDATE {$prefix}_module_vars SET z_modname = 'IWforms' WHERE z_bkey = 'iw_forms'";
        if (!DBUtil::executeSQL($c)) {
            return false;
        }

        //Array de noms
        $oldVarsNames = DBUtil::selectFieldArray("module_vars", 'name', "`z_modname` = 'IWforms'", '', false, '');

        $newVarsNames = Array('friendsSystemAvailable', 'invisibleGroupsInList', 'usersCanManageName',
            'allowUserChangeAvatar', 'allowUserSetTheirSex', 'allowUserDescribeTheirSelves', 
            'avatarChangeValidationNeeded', 'usersPictureFolder');
             
        $newVars = Array('friendsSystemAvailable' => 1,
            'invisibleGroupsInList' => '$',
            'usersCanManageName' => 0,
            'allowUserChangeAvatar' => '1',
            'allowUserSetTheirSex', '0',
            'allowUserDescribeTheirSelves' => '1',
            'avatarChangeValidationNeeded' => '1',
            'usersPictureFolder' => 'photos');

        // Delete unneeded vars
        $del = array_diff($oldVarsNames, $newVarsNames);
        foreach ($del as $i) {
            $this->delVar($i);
        }

        // Add new vars
        $add = array_diff($newVarsNames, $oldVarsNames);
        foreach ($add as $i) {
            $this->setVar($i, $newVars[$i]);
        }
        
        
        return true;
    }

}