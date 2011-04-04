<?php

class IWusers_Controller_Admin extends Zikula_AbstractController {

    protected function postInitialize() {
        // Set caching to false by default.
        $this->view->setCaching(false);
    }

    /**
     * Show the list of users
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	The values for the current view
     * @return:	The list of users
     */
    public function main($args) {

        $inici = FormUtil::getPassedValue('inici', isset($args['inici']) ? $args['inici'] : null, 'REQUEST');
        $filtre = FormUtil::getPassedValue('filtre', isset($args['filtre']) ? $args['filtre'] : null, 'REQUEST');
        $campfiltre = FormUtil::getPassedValue('campfiltre', isset($args['campfiltre']) ? $args['campfiltre'] : null, 'POST');
        $numitems = FormUtil::getPassedValue('numitems', isset($args['numitems']) ? $args['numitems'] : null, 'POST');
        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }
        $usersArray = array();
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        if ($inici == null) {
            $inici = ModUtil::func('IWmain', 'user', 'userInitVar',
                            array('sv' => $sv,
                                'module' => 'IWusers',
                                'name' => 'inici',
                                'default' => '0',
                                'lifetime' => '1000'));
        } else {
            ModUtil::func('IWmain', 'user', 'userSetVar',
                            array('sv' => $sv,
                                'module' => 'IWusers',
                                'name' => 'inici',
                                'value' => $inici,
                                'lifetime' => '1000'));
        }
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        if ($filtre == null) {
            $filtre = ModUtil::func('IWmain', 'user', 'userInitVar',
                            array('sv' => $sv,
                                'module' => 'IWusers',
                                'name' => 'filtre',
                                'default' => '0',
                                'lifetime' => '1000'));
        } else {
            ModUtil::func('IWmain', 'user', 'userSetVar',
                            array('sv' => $sv,
                                'module' => 'IWusers',
                                'name' => 'filtre',
                                'value' => $filtre,
                                'lifetime' => '1000'));
        }
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        if ($campfiltre == null || $campfiltre == '') {
            $campfiltre = ModUtil::func('IWmain', 'user', 'userInitVar',
                            array('sv' => $sv,
                                'module' => 'IWusers',
                                'name' => 'campfiltre',
                                'default' => 'l',
                                'lifetime' => '1000'));
        } else {
            ModUtil::func('IWmain', 'user', 'userSetVar',
                            array('sv' => $sv,
                                'module' => 'IWusers',
                                'name' => 'campfiltre',
                                'value' => $campfiltre,
                                'lifetime' => '1000'));
        }
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        if ($numitems == null || $numitems == '') {
            $numitems = ModUtil::func('IWmain', 'user', 'userInitVar',
                            array('sv' => $sv,
                                'module' => 'IWusers',
                                'name' => 'numitems',
                                'default' => '20',
                                'lifetime' => '1000'));
        } else {
            ModUtil::func('IWmain', 'user', 'userSetVar',
                            array('sv' => $sv,
                                'module' => 'IWusers',
                                'name' => 'numitems',
                                'value' => $numitems,
                                'lifetime' => '1000'));
        }
        // Get all users in database
        $allUsers = ModUtil::apiFunc('IWusers', 'user', 'getAllUsers');
        // Get all users info
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersUname = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'l',
                            'sv' => $sv));
        // Get all the groups information
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $groupsInfo = ModUtil::func('IWmain', 'user', 'getAllGroupsInfo',
                        array('sv' => $sv));
        // Create the users that are in users table but are not in IWusers table
        $notExist = array_diff_key($usersUname, $allUsers);
        foreach ($notExist as $key => $value) {
            ModUtil::apiFunc('IWusers', 'admin', 'create',
                            array('uid' => $key));
        }
        // Count the users for the criteria
        $usersNumber = ModUtil::apiFunc('IWusers', 'user', 'countUsers',
                        array('campfiltre' => $campfiltre,
                            'filtre' => $filtre));
        // Get all users needed
        $users = ModUtil::apiFunc('IWusers', 'user', 'getAll',
                        array('campfiltre' => $campfiltre,
                            'filtre' => $filtre,
                            'inici' => $inici,
                            'numitems' => $numitems));
        $usersList = '$$';
        foreach ($users as $user) {
            $usersList .= $user['uid'] . '$$';
        }
        // Get all users mails
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersMail = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'e',
                            'sv' => $sv,
                            'list' => $usersList));
        $folder = ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'usersPictureFolder');
        // Check all users disponibility in extra database. If user doesn't exists create it
        foreach ($users as $user) {
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $groups = ModUtil::func('IWmain', 'user', 'getAllUserGroups',
                            array('sv' => $sv,
                                'uid' => $user['uid']));
            $userGroups = array();
            foreach ($groups as $group) {
                if ($group['id']) {
                    array_push($userGroups, array('id' => $group['id'],
                        'name' => $groupsInfo[$group['id']]));
                }
            }
            //get the user small photo
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $photo_s = ModUtil::func('IWmain', 'user', 'getUserPicture',
                            array('uname' => $usersUname[$user['uid']] . '_s',
                                'sv' => $sv));
            //if the small photo not exists, check if the normal size foto exists. In this case create the small one
            if ($photo_s == '') {
                $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                $photo = ModUtil::func('IWmain', 'user', 'getUserPicture',
                                array('uname' => $usersUname[$user['uid']],
                                    'sv' => $sv));
                if ($photo != '' && is_writable($folder)) {
                    //create the small photo and take it
                    $file_extension = strtolower(substr(strrchr($photo, "."), 1));
                    $e = ModUtil::func('IWmain', 'user', 'thumb',
                                    array('imgSource' => $folder . '/' . $usersUname[$user['uid']] . '.' . $file_extension,
                                        'imgDest' => $folder . '/' . $usersUname[$user['uid']] . '_s.' . $file_extension,
                                        'new_width' => 30));
                    $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                    $photo_s = ModUtil::func('IWmain', 'user', 'getUserPicture',
                                    array('uname' => $usersUname[$user['uid']] . '_s',
                                        'sv' => $sv));
                }
            }
            $usersArray[] = array('uid' => $user['uid'],
                'uname' => $usersUname[$user['uid']],
                'email' => $usersMail[$user['uid']],
                'nom' => $user['nom'],
                'cognom1' => $user['cognom1'],
                'cognom2' => $user['cognom2'],
                'photo' => $photo_s,
                'groups' => $userGroups);
        }
        // Create output object
        $view = Zikula_View::getInstance('IWusers', false);
        $leters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $pager = ModUtil::func('IWusers', 'admin', 'pager',
                        array('inici' => $inici,
                            'rpp' => $numitems,
                            'usersNumber' => $usersNumber,
                            'urltemplate' => 'index.php?module=IWusers&type=admin&func=main&inici=%%'));
        $numitems_MS = array(array('id' => '10',
                'name' => '10'),
            array('id' => '20',
                'name' => '20'),
            array('id' => '30',
                'name' => '30'),
            array('id' => '40',
                'name' => '40'),
            array('id' => '50',
                'name' => '50'),
            array('id' => '60',
                'name' => '60'),
            array('id' => '80',
                'name' => '80'),
            array('id' => '100',
                'name' => '100'));
        $camps = array('l' => $this->__('User name'),
            'n' => $this->__('Name'),
            'c1' => $this->__('1st surname'),
            'c2' => $this->__('2nd surname'));
        $campsfiltre_MS = array(array('id' => 'l',
                'name' => $camps['l']),
            array('id' => 'n',
                'name' => $camps['n']),
            array('id' => 'c1',
                'name' => $camps['c1']),
            array('id' => 'c2',
                'name' => $camps['c2']));
        return $this->view->assign('pager', $pager)
                ->assign('leters', $leters)
                ->assign('numitems_MS', $numitems_MS)
                ->assign('campsfiltre_MS', $campsfiltre_MS)
                ->assign('inici', $inici)
                ->assign('filtre', $filtre)
                ->assign('campfiltre', $campfiltre)
                ->assign('numitems', $numitems)
                ->assign('users', $usersArray)
                ->assign('usersNumber', $usersNumber)
                ->fetch('IWusers_admin_main.htm');
    }

    /**
     * Edit the list of users
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	The identities of the users to edit
     * @return:	The list of the edited users
     */
    public function edit($args) {

        $userId = FormUtil::getPassedValue('userId', isset($args) ? $args : null, 'POST');
        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }
        if ($userId == null) {
            LogUtil::registerError($this->__('No users have chosen'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }
        // get users registers
        $users = ModUtil::apiFunc('IWusers', 'user', 'get',
                        array('multi' => $userId));
        if ($users == false) {
            LogUtil::registerError($this->__('No users found'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }
        $usersList = '$$';
        foreach ($users as $user) {
            $usersList .= $user['uid'] . '$$';
        }
        // Get all users info
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersNames = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'l',
                            'sv' => $sv,
                            'list' => $usersList));
        foreach ($users as $user) {
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $photo = ModUtil::func('IWmain', 'user', 'getUserPicture',
                            array('uname' => $usersNames[$user['uid']],
                                'sv' => $sv));
            $usersArray [] = array('uname' => $user['uid'],
                'uid' => $user['uid'],
                'nom' => $user['nom'],
                'photo' => $photo,
                'cognom1' => $user['cognom1'],
                'cognom2' => $user['cognom2']);
        }

        $canChangeAvatar = true;
        //Check if the users picture folder exists
        if (!file_exists(ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'usersPictureFolder')) || ModUtil::getVar('IWmain', 'usersPictureFolder') == '') {
            $canChangeAvatar = false;
        } else {
            if (!is_writeable(ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'usersPictureFolder')))
                $canChangeAvatar = false;
        }

        return $this->view->assign('users', $usersArray)
                ->assign('canChangeAvatar', $canChangeAvatar)
                ->assign('usersNames', $usersNames)
                ->fetch('IWusers_admin_edit.htm');
    }

    /**
     * Update the users values
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	The users values
     * @return:	Return user to main page
     */
    public function update($args) {

        $uid = FormUtil::getPassedValue('uid', isset($args) ? $args : null, 'POST');
        $nom = FormUtil::getPassedValue('nom', isset($args) ? $args : null, 'POST');
        $cognom1 = FormUtil::getPassedValue('cognom1', isset($args) ? $args : null, 'POST');
        $cognom2 = FormUtil::getPassedValue('cognom2', isset($args) ? $args : null, 'POST');
        $deleteAvatar = FormUtil::getPassedValue('deleteAvatar', isset($args) ? $args : null, 'POST');

        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        $this->checkCsrfToken();

        $error = false;
        if (!ModUtil::apiFunc('IWusers', 'admin', 'updateUser',
                        array('uid' => $uid,
                            'nom' => $nom,
                            'cognom1' => $cognom1,
                            'cognom2' => $cognom2))) {
            LogUtil::registerError($this->__('There was some mistake while editing'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        $usersList = '$$';
        foreach ($uid as $u) {
            $usersList .= $u . '$$';
        }

        // Get all users info
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersNames = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'l',
                            'sv' => $sv,
                            'list' => $usersList));

        $folder = ModUtil::getVar('IWmain', 'tempFolder');

        //update avatars
        foreach ($uid as $u) {
            if ($deleteAvatar[$u] != 1) {
                $user = 'avatar_' . $u;
                $nom_fitxer = '';
                $fileName = $_FILES['avatar_' . $u]['name'];
                $file_extension = strtolower(substr(strrchr($fileName, "."), 1));
                if ($fileName != '' && ($file_extension == 'png' || $file_extension == 'gif' || $file_extension == 'jpg')) {
                    // update the attached file to the server
                    $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                    $update = ModUtil::func('IWmain', 'user', 'updateFile',
                                    array('sv' => $sv,
                                        'folder' => $folder,
                                        'fileRealName' => $_FILES['avatar_' . $u]['name'],
                                        'fileNameTemp' => $_FILES['avatar_' . $u]['tmp_name'],
                                        'size' => $_FILES['avatar_' . $u]['size'],
                                        'allow' => '|' . $file_extension,
                                        'fileName' => $usersNames[$u] . '.' . $file_extension));

                    //the function returns the error string if the update fails and and empty string if success
                    if ($update['msg'] != '') {
                        LogUtil::registerError($update['msg'] . ' ' . $this->__('Probably the note have been sent without the attached file', $dom));
                        $nom_fitxer = '';
                    } else {
                        $nom_fitxer = $update['fileName'];
                    }
                }

                //if the file has uploaded
                if ($nom_fitxer != '') {
                    for ($i = 0; $i < 2; $i++) {
                        $fileAvatarName = $usersNames[$u];
                        $userFileName = ($i == 0) ? $fileAvatarName . '.' . $file_extension : $fileAvatarName . '_s.' . $file_extension;
                        $new_width = ($i == 0) ? 90 : 30;
                        //source and destination
                        $imgSource = ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder') . '/' . $nom_fitxer;
                        $imgDest = ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'usersPictureFolder') . '/' . $userFileName;

                        //if success $errorMsg = ''
                        $errorMsg = ModUtil::func('IWmain', 'user', 'thumb',
                                        array('imgSource' => $imgSource,
                                            'imgDest' => $imgDest,
                                            'new_width' => $new_width,
                                            'deleteOthers' => 1));
                    }

                    //delete the avatar file in temporal folder
                    unlink(ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder') . '/' . $nom_fitxer);
                }
            } else {
                ModUtil::func('IWmain', 'user', 'deleteAvatar',
                                array('avatarName' => $usersNames[$u],
                                    'extensions' => array('jpg', 'png', 'gif')));
                ModUtil::func('IWmain', 'user', 'deleteAvatar',
                                array('avatarName' => $usersNames[$u] . '_s',
                                    'extensions' => array('jpg', 'png', 'gif')));
            }
        }

        LogUtil::registerStatus($this->__('The records have been published successfully'));
        return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
    }

    /**
     * Create a pager for the users admin main page
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	args   Array with the apameters of the current page
     * @return:	Return the pager
     */
    public function pager($args) {

        $inici = FormUtil::getPassedValue('inici', isset($args['inici']) ? $args['inici'] : null, 'POST');
        $rpp = FormUtil::getPassedValue('rpp', isset($args['rpp']) ? $args['rpp'] : null, 'POST');
        $usersNumber = FormUtil::getPassedValue('usersNumber', isset($args['usersNumber']) ? $args['usersNumber'] : null, 'POST');
        $urltemplate = FormUtil::getPassedValue('urltemplate', isset($args['urltemplate']) ? $args['urltemplate'] : null, 'POST');

        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        // Quick check to ensure that we have work to do
        if ($usersNumber <= $rpp) {
            return;
        }

        if (!isset($inici) || empty($inici)) {
            $inici = 0;
        }

        if (!isset($rpp) || empty($rpp)) {
            $rpp = 10;
        }

        // Show startnum link
        if ($inici != 0) {
            $url = preg_replace('/%%/', 0, $urltemplate);
            $text = '<a href="' . $url . '"><<</a> | ';
        } else {
            $text = '<< | ';
        }
        $items[] = array('text' => $text);

        // Show following items
        $pagenum = 1;

        for ($curnum = 0; $curnum <= $usersNumber - 1; $curnum += $rpp) {
            if (($inici < $curnum) || ($inici > ($curnum + $rpp - 1))) {
                //mod by marsu - use sliding window for pagelinks
                if ((($pagenum % 10) == 0) // link if page is multiple of 10
                        || ($pagenum == 1) // link first page
                        || (($curnum > ($inici - 4 * $rpp)) //link -3 and +3 pages
                        && ($curnum < ($inici + 4 * $rpp)))
                ) {
                    // Not on this page - show link
                    $url = preg_replace('/%%/', $curnum, $urltemplate);
                    $text = '<a href="' . $url . '">' . $pagenum . '</a> | ';
                    $items[] = array('text' => $text);
                }
                //end mod by marsu
            } else {
                // On this page - show text
                $text = $pagenum . ' | ';
                $items[] = array('text' => $text);
            }
            $pagenum++;
        }

        if (($curnum >= $rpp + 1) && ($inici < $curnum - $rpp)) {
            $url = preg_replace('/%%/', $curnum - $rpp, $urltemplate);
            $text = '<a href="' . $url . '">>></a>';
        } else
            $text = '>>';
        $items[] = array('text' => $text);

        $this->view->assign('items', $items)
                ->fetch('IWusers_admin_pager.htm');
    }

    /**
     * Show the main configurable parameters needed to configurate the module IWusers
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @return:	The form with needed to change the parameters
     */
    public function config() {

        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }
        $friendsSystemAvailable = ModUtil::getVar('IWusers', 'friendsSystemAvailable');
        $invisibleGroupsInList = ModUtil::getVar('IWusers', 'invisibleGroupsInList');
        $usersCanManageName = ModUtil::getVar('IWusers', 'usersCanManageName');
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $groups = ModUtil::func('IWmain', 'user', 'getAllGroups',
                        array('sv' => $sv));
        foreach ($groups as $group) {
            $checked = false;
            if (strpos($invisibleGroupsInList, '$' . $group['id'] . '$') != false) {
                $checked = true;
            }
            $groupsArray[] = array('id' => $group['id'],
                'name' => $group['name'],
                'checked' => $checked);
        }
        return $this->view->assign('friendsSystemAvailable', $friendsSystemAvailable)
                ->assign('invisibleGroupsInList', $invisibleGroupsInList)
                ->assign('usersCanManageName', $usersCanManageName)
                ->assign('groupsArray', $groupsArray)
                ->fetch('IWusers_admin_config.htm');
    }

    /**
     * Update the module configuration
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	Configuration values
     * @return:	The form with needed to change the parameters
     */
    public function updateConf($args) {
        $friendsSystemAvailable = FormUtil::getPassedValue('friendsSystemAvailable', isset($args['friendsSystemAvailable']) ? $args['friendsSystemAvailable'] : 0, 'POST');
        $groups = FormUtil::getPassedValue('groups', isset($args['groups']) ? $args['groups'] : null, 'POST');
        $usersCanManageName = FormUtil::getPassedValue('usersCanManageName', isset($args['usersCanManageName']) ? $args['usersCanManageName'] : null, 'POST');
        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }
        $this->checkCsrfToken();
        $groupsString = '$';
        foreach ($groups as $group) {
            $groupsString .= '$' . $group . '$';
        }
        $this->setVar('IWusers', 'friendsSystemAvailable', $friendsSystemAvailable)
                ->setVar('IWusers', 'invisibleGroupsInList', $groupsString)
                ->setVar('IWusers', 'usersCanManageName', $usersCanManageName);
        LogUtil::registerStatus($this->__('The configuration has changed'));
        return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
    }

}