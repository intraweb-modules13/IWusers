<?php

class IWusers_Controller_Admin extends Zikula_Controller {

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
            return LogUtil::registerPermissionError();
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
        $view->assign('pager', $pager);
        $view->assign('leters', $leters);
        $view->assign('numitems_MS', $numitems_MS);
        $view->assign('campsfiltre_MS', $campsfiltre_MS);
        $view->assign('inici', $inici);
        $view->assign('filtre', $filtre);
        $view->assign('campfiltre', $campfiltre);
        $view->assign('numitems', $numitems);
        $view->assign('users', $usersArray);
        $view->assign('usersNumber', $usersNumber);
        return $view->fetch('IWusers_admin_main.htm');
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
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
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

        //Check if the temp folder exists
        if (!file_exists(ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder')) || ModUtil::getVar('IWmain', 'tempFolder') == '') {
            $canChangeAvatar = false;
        } else {
            if (!is_writeable(ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder')))
                $canChangeAvatar = false;
        }

        // Create output object
        $view = Zikula_View::getInstance('IWusers', false);

        $view->assign('users', $usersArray);
        $view->assign('canChangeAvatar', $canChangeAvatar);
        $view->assign('usersNames', $usersNames);

        return $view->fetch('IWusers_admin_edit.htm');
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
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('IWusers', 'admin', 'main'));
        }

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
     * Create a new user form
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @return:	Show the form to create a new user
     */
    public function newUser() {

        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }

        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $groups = ModUtil::func('IWmain', 'user', 'getAllGroups',
                        array('sv' => $sv));

        $defaultgroup = ModUtil::getVar('Groups', 'defaultgroup');
        $canChangeAvatar = true;
        //Check if the users picture folder exists
        if (!file_exists(ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'usersPictureFolder')) || ModUtil::getVar('IWmain', 'usersPictureFolder') == '') {
            $canChangeAvatar = false;
        } else {
            if (!is_writeable(ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'usersPictureFolder')))
                $canChangeAvatar = false;
        }

        // Create output object
        $view = Zikula_View::getInstance('IWusers', false);
        $view->assign('groups', $groups);
        $view->assign('defaultgroup', $defaultgroup);


        return $view->fetch('IWusers_admin_new.htm');
    }

    /**
     * Create a new user in database
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	User information received from the form
     * @return:	Show the form to create a new user
     */
    public function create($args) {

        $nom = FormUtil::getPassedValue('nom', isset($args['nom']) ? $args['nom'] : null, 'POST');
        $cognom1 = FormUtil::getPassedValue('cognom1', isset($args['cognom1']) ? $args['cognom1'] : null, 'POST');
        $cognom2 = FormUtil::getPassedValue('cognom2', isset($args['cognom2']) ? $args['cognom2'] : null, 'POST');
        $uname = FormUtil::getPassedValue('uname', isset($args['uname']) ? $args['uname'] : null, 'POST');
        $pass = FormUtil::getPassedValue('pass', isset($args['pass']) ? $args['pass'] : null, 'POST');
        $group = FormUtil::getPassedValue('group', isset($args['group']) ? $args['group'] : null, 'POST');
        $email = FormUtil::getPassedValue('email', isset($args['email']) ? $args['email'] : null, 'POST');

        $fileName = $_FILES['avatar']['name'];

        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('IWusers', 'admin', 'main'));
        }

        //Needed arguments
        if ($uname == null) {
            LogUtil::registerError($this->__('You do not have written the name of user'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        //Needed arguments
        if ($pass == null) {
            LogUtil::registerError($this->__('You do not have written the password of the user'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        //Needed arguments
        if ($email == null) {
            LogUtil::registerError($this->__('You do not have written the e-mail to the user'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        //Needed arguments
        if ($group == null || $group == 0) {
            LogUtil::registerError($this->__('You have not selected the initial group of the user'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        // Check if uname exists
        // Get all users uname
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersUname = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'l',
                            'sv' => $sv,
                            'list' => $uname . '$$'));

        if (in_array($uname, $usersUname)) {
            LogUtil::registerError($this->__('Username') . ' <strong>' . $uname . '</strong> ' . $this->__('already exists. You have to choose another.'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        //Check for password
        $minpass = ModUtil::getVar('Users', 'minpass');
        if (empty($pass) || strlen($pass) < $minpass) {
            return LogUtil::registerError($this->__('The password chosen by the user is too short. The minimum character must have the password is') . ' ' . $minpass);
        }

        // Create user in users table
        $user = ModUtil::apiFunc('IWusers', 'admin', 'createUser',
                        array('uname' => $uname,
                            'pass' => $pass,
                            'nom' => $nom,
                            'cognom1' => $cognom1,
                            'cognom2' => $cognom2));
        if ($user == false) {
            LogUtil::registerError($this->__('There was an error in the creation of the user.'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        $folder = ModUtil::getVar('IWmain', 'tempFolder');

        //update avatars
        $file_extension = strtolower(substr(strrchr($fileName, "."), 1));
        if ($fileName != '' && ($file_extension == 'png' || $file_extension == 'gif' || $file_extension == 'jpg')) {
            // update the attached file to the server
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $update = ModUtil::func('IWmain', 'user', 'updateFile',
                            array('sv' => $sv,
                                'folder' => $folder,
                                'file' => $_FILES['avatar'],
                                'fileName' => $uname . '.' . $file_extension));

            //the function returns the error string if the update fails and and empty string if success
            if ($update['msg'] != '') {
                LogUtil::registerError($update['msg'] . ' ' . $this->__('User without avatar'));
                $nom_fitxer = '';
            } else {
                $nom_fitxer = $update['fileName'];
            }
        }

        //if the file has uploaded
        if ($nom_fitxer != '') {
            for ($i = 0; $i < 2; $i++) {
                $fileAvatarName = $uname;
                $userFileName = ($i == 0) ? $fileAvatarName . '.' . $file_extension : $fileAvatarName . '_s.' . $file_extension;
                $new_width = ($i == 0) ? 90 : 30;
                //source and destination
                $imgSource = ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder') . '/' . $nom_fitxer;
                $imgDest = ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'usersPictureFolder') . '/' . $userFileName;

                //if success $errorMsg = ''
                $errorMsg = ModUtil::func('IWmain', 'user', 'thumb',
                                array('imgSource' => $imgSource,
                                    'imgDest' => $imgDest,
                                    'new_width' => $new_width));
            }
            //delete the avatar file in temporal folder
            unlink(ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder') . '/' . $nom_fitxer);
        }

        // Create user in IWusers table
        $userData = ModUtil::apiFunc('IWusers', 'admin', 'create',
                        array('uid' => $user,
                            'nom' => $nom,
                            'cognom1' => $cognom1,
                            'cognom2' => $cognom2));

        if ($userData == false) {
            LogUtil::registerError($this->__('The user has been created but there was an error in the entry of personal information. Create this user and makes it the right information.'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        // Insert user into initial group
        $userInitGroup = ModUtil::apiFunc('IWusers', 'admin', 'addUserToGroup',
                        array('uid' => $user,
                            'gid' => $group));

        if ($userInitGroup == false) {
            LogUtil::registerError($this->__('The user has been created but there was an error in the allocation of the initial group of the user. Put this user in a group/s that you think appropriate  from the management module group.'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        LogUtil::registerStatus($this->__('We have created a user new'));

        return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
    }

    /**
     * Delete the users selected
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	The identities of the users to delete
     * @return:	The list of the users that are going to be deleted
     */
    public function delete($args) {

        $userId = FormUtil::getPassedValue('userId', isset($args) ? $args : null, 'POST');
        $uid = FormUtil::getPassedValue('uid', isset($args) ? $args : null, 'POST');
        $uname = FormUtil::getPassedValue('uname', isset($args) ? $args : null, 'POST');
        $confirmation = FormUtil::getPassedValue('confirmation', isset($args['confirmation']) ? $args['confirmation'] : null, 'POST');

        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }

        if ($userId == null && $uid == null) {
            LogUtil::registerError($this->__('No users have chosen'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        if (!$confirmation) {
            // get users registers
            $users = ModUtil::apiFunc('IWusers', 'user', 'get',
                            array('multi' => $userId));
            if ($users == false) {
                LogUtil::registerError($this->__('No users found'));
                return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
            }

            // Get all users info
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $usersUname = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                            array('info' => 'l',
                                'sv' => $sv,
                                'fromArray' => $users));

            foreach ($users as $user) {
                $usersArray [] = array('uname' => $usersUname[$user['uid']],
                    'uid' => $user['uid'],
                    'nom' => $user['nom'],
                    'cognom1' => $user['cognom1'],
                    'cognom2' => $user['cognom2']);
            }

            // Create output object
            $view = Zikula_View::getInstance('IWusers', false);

            $view->assign('users', $usersArray);
            $view->assign('userId', $userId);

            return $view->fetch('IWusers_admin_delete.htm');
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('IWusers', 'admin', 'main'));
        }

        //Delete users avatars
        foreach ($uid as $u) {
            $userVars = UserUtil::getVars($u);
            ModUtil::func('IWmain', 'user', 'deleteAvatar',
                            array('avatarName' => $userVars['uname'],
                                'extensions' => array('jpg',
                                    'png',
                                    'gif')));
            ModUtil::func('IWmain', 'user', 'deleteAvatar',
                            array('avatarName' => $userVars['uname'] . '_s',
                                'extensions' => array('jpg',
                                    'png',
                                    'gif')));
        }
        //Delete multiple users from IWusers table
        $deleteIWUser = ModUtil::apiFunc('IWusers', 'admin', 'deleteIWUser',
                        array('uid' => $uid));
        if (!$deleteIWUser) {
            LogUtil::registerError($this->__('There was an error and failed to remove the information from the user'));
        }

        //Delete multiple users from group_membership table
        if ($deleteIWUser) {
            $deleteUserGroups = ModUtil::apiFunc('IWusers', 'admin', 'deleteUserGroups',
                            array('uid' => $uid));
            if (!$deleteUserGroups) {
                LogUtil::registerError($this->__('There was an error deleting user groups. The user could not be removed'));
            }
        }

        //Delete multiple users from users table
        if ($deleteUserGroups) {
            $deleteUsers = ModUtil::apiFunc('IWusers', 'admin', 'deleteUsers',
                            array('uid' => $uid));
            if (!$deleteUsers) {
                LogUtil::registerError($this->__('There was an error deleting users from the database'));
            }
        }

        LogUtil::registerStatus($this->__('The user or users have been deleted'));

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
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $view = Zikula_View::getInstance('IWusers', false);


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

        $view->assign('items', $items);
        return $view->fetch('IWusers_admin_pager.htm');
    }

    /**
     * Create a CSV file with all the uses data
     * @author:     Albert Pï¿œrez Monfort (aperezm@xtec.cat)
     * @param:	args   Array with the apameters of the current page
     * @return:	Return the pager
     */
    public function export($args) {

        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get all users in database
        $allUsers = ModUtil::apiFunc('IWusers', 'user', 'getAllUsers');
        if ($allUsers == false) {
            LogUtil::registerError($this->__('No users found'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        // Get all users info
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersUname = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'l',
                            'sv' => $sv));

        // Get all users mails
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersMail = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'e',
                            'sv' => $sv));

        $file = ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder') . '/export' . date('dmY') . '.csv';
        $f = fopen($file, 'w');

        //Posem la fila inicial del fitxer
        fwrite($f, '#,id,nom,cognom1,cognom2,nom_u,email,contrasenya,grup' . "\r\n");
        $i = 0;
        foreach ($allUsers as $user) {
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $groups = ModUtil::func('IWmain', 'user', 'getAllUserGroups',
                            array('sv' => $sv,
                                'uid' => $user['uid']));
            $group1 = '';
            foreach ($groups as $group) {
                $group1 .= $group['id'] . '|';
            }
            $group1 = substr($group1, 0, strlen($group1) - 1);
            $i++;
            $userId = ($user['id'] == '') ? $usersUname[$user['uid']] : $user['id'];
            fwrite($f, $i . ',"' . $userId . '","' . $user['nom'] . '","' . $user['cognom1'] . '","' . $user['cognom2'] . '","' . $usersUname[$user['uid']] . '","' . $usersMail[$user['uid']] . '","",' . $group1 . "\r\n");
        }
        fclose($f);
        //Check that file has been created correctly
        if (!is_file($file)) {
            LogUtil::registerError($this->__('An error occurred while creating the data file. Checks from the global configuration of the module (IWmain), the temporary directory exists.'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }

        //Gather relevent info about file
        $len = filesize($file);
        $filename = basename($file);
        $file_extension = strtolower(substr(strrchr($filename, "."), 1));
        $ctype = "CSV/CSV";

        //Begin writing headers
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");

        //Use the switch-generated Content-Type
        header("Content-Type: $ctype");

        //Force the download
        $header = "Content-Disposition: attachment; filename=" . $filename . ";";
        header($header);
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . $len);
        @readfile($file);

        //Delete file from servar folder
        unlink($file);
        exit;
    }

    /**
     * Import users from a CSV file
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	args   Array with the apameters of the users and process
     * @return:	Return to admin main pager
     */
    public function import($args) {
        die('es necessita import');
        /*
          $pas = FormUtil::getPassedValue('pas', isset($args['pas']) ? $args['pas'] : null, 'REQUEST');
          $subpas = FormUtil::getPassedValue('subpas', isset($args['subpas']) ? $args['subpas'] : null, 'REQUEST');
          $fitxer = FormUtil::getPassedValue('fitxer', isset($args['fitxer']) ? $args['fitxer'] : null, 'POST');
          $saga = FormUtil::getPassedValue('saga', isset($args['saga']) ? $args['saga'] : null, 'POST');
          $tria = FormUtil::getPassedValue('tria', isset($args['tria']) ? $args['tria'] : null, 'POST');
          $suids = FormUtil::getPassedValue('suids', isset($args['suids']) ? $args['suids'] : null, 'POST');
          $suidi = FormUtil::getPassedValue('suidi', isset($args['suidi']) ? $args['suidi'] : null, 'POST');
          $login = FormUtil::getPassedValue('login', isset($args['login']) ? $args['login'] : null, 'POST');
          $email = FormUtil::getPassedValue('email', isset($args['email']) ? $args['email'] : null, 'POST');
          $contrasenya = FormUtil::getPassedValue('contrasenya', isset($args['contrasenya']) ? $args['contrasenya'] : null, 'POST');
          $associa = FormUtil::getPassedValue('associa', isset($args['associa']) ? $args['associa'] : null, 'POST');
          $submit = FormUtil::getPassedValue('submit', isset($args['submit']) ? $args['submit'] : null, 'POST');
          $idintra = FormUtil::getPassedValue('idintra', isset($args['idintra']) ? $args['idintra'] : null, 'POST');
          $quins = FormUtil::getPassedValue('quins', isset($args['quins']) ? $args['quins'] : null, 'REQUEST');
          $taula = FormUtil::getPassedValue('taula', isset($args['taula']) ? $args['taula'] : null, 'REQUEST');

          // Security check
          if (!SecurityUtil::checkPermission('IWusers::', '::', ACCESS_ADMIN)) {
          return LogUtil::registerPermissionError();
          }

          $sortida = & new pnHTML();

          if ($pas > 0 && $pas != 3) {
          // Confirm authorisation code
          if (!SecurityUtil::confirmAuthKey()) {
          return LogUtil::registerAuthidError(ModUtil::url('IWusers', 'admin', 'main'));
          }
          }

          // Create output object
          $view = Zikula_View::getInstance('IWusers', false);

          //Camps fets servir en la sincronitzaciÃ³ de les taules
          $camps_esperats_xml = array('#',
          'id',
          'nom',
          'cognom1',
          'cognom2',
          'email');
          $camps_esperats_csv = array('#',
          'id',
          'nom',
          'cognom1',
          'cognom2',
          'nom_u',
          'email',
          'contrasenya',
          'grup');
          $camps_esperats = array('#',
          'id',
          'nom',
          'cognom1',
          'cognom2',
          'nom_u',
          'email',
          'contrasenya',
          'grup');

          if ($pas == '') {
          $pas = 1;
          }

          $view->assign('step', $pas);

          switch ($pas) {
          case 1:
          $potpassar = true;
          break;
          case 2:
          //Check values from step 1
          //Check if file exists
          $potpassar = true;
          if (empty($_FILES['fitxer']['name'])) {
          $view->assign('noFile', true);
          $pas = 0;
          break;
          }

          //Update the file to temp folder
          $len_fitxer = $_FILES['fitxer']['size'];
          $nom_fitxer = $_FILES['fitxer']['name'];
          $extensio_fitxer = strtolower(substr(strrchr($nom_fitxer, "."), 1));
          $view->assign('fileName', $nom_fitxer);

          //check if file characteristics are correct
          if ($extensio_fitxer != 'csv' || $len_fitxer == 0) {
          $view->assign('incorrectFile', true);
          $pas = 0;
          break;
          } else {
          if (!move_uploaded_file($_FILES['fitxer']['tmp_name'], ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder') . $nom_fitxer)) {
          //Error during the file update process
          $view->assign('updateFileError', true);
          $pas = 0;
          break;
          }
          }

          $view->assign('goOn2', true);

          //Reading the file from the server
          //Parser the file and inclode records in saga array
          $fp = fopen(ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder') . $nom_fitxer, 'r');

          $fila1 = fgets($fp);
          $fila1 = trim(str_replace('"', '', $fila1));
          $columnes[1] = explode(',', $fila1);
          $camps_esperats = $camps_esperats_csv;

          $i = 0;
          foreach ($columnes[1] as $columna) {
          if ($camps_esperats[$i] == $columna) {
          $ok = true;
          } else {
          $ok = false;
          $error = true;
          }
          $rows[] = array('column' => $columna,
          'ok' => $ok);
          $i++;
          }

          $view->assign('rows', $rows);
          $view->assign('fields', $camps_esperats);
          $view->assign('error', $error);

          if ($error) {
          $pas = 0;
          break;
          }

          if (count($camps_esperats) != $i && $i > 6) {
          while (count($camps_esperats) != $i) {
          $notPresentFields[] = array('value' => $camps_esperats[$i]);
          $i++;
          }
          $view->assign('error1', true);
          $view->assign('notPresentFields', $notPresentFields);
          $pas = 0;
          break;
          }

          //Prepare tables to make possible the sincronitation
          //Truncate the auxiliar tables saga and users_aux
          $lid = ModUtil::apiFunc('IWusers', 'admin', 'truncateTables');
          if ($lid == false) {
          $view->assign('error2', true);
          $pas = 0;
          break;
          }

          //copiem la taula d'usuaris amb totes les dades de la taula usuaris i posem les dades a zero
          $lid = ModUtil::apiFunc('IWusers', 'admin', 'copyTables');
          if ($lid == false) {
          $view->assign('error2', true);
          $pas = 0;
          break;
          }

          if ($extensio_fitxer == 'xml') {
          //Procedim a la cÃ rrega de les dades a la matriu saga
          $i = 0;
          preg_match_all("/<columna>(.*?)<\/columna>/", $data, $registres);

          foreach ($registres[1] as $registre) {
          //Memoritzem les dades trobades
          switch ($i % 5) {
          case 0; //ref
          $ref = $registre;
          if (!is_numeric($registre)) {
          $error = true;
          $error_text = 0;
          }
          break;
          case 1; //id
          $id = $registre;
          break;
          case 2; //nom
          $nom = $registre;
          break;
          case 3; //cognom1
          $cognom1 = $registre;
          break;
          case 4; //cognom2
          $cognom2 = $registre;
          //Entrem el registre a la base de dades de saga
          $lid = ModUtil::apiFunc('IWusers', 'admin', 'createImport', array('accio' => 0,
          'id' => $id,
          'nom' => $nom,
          'cognom1' => $cognom1,
          'cognom2' => $cognom2));
          if ($lid == false) {
          $error = true;
          $error_text = 1;
          }
          break;
          }
          $i++;
          if ($error) {
          break;
          }
          }
          $num_registres = $i / count($columnes[1]);
          } else {
          while (!feof($fp)) {
          $valors = trim(fgets($fp));
          if (strlen($valors > 0)) {
          $valors = trim(str_replace('"', '', $valors));
          $valors = explode(",", $valors);
          if (!is_numeric($valors[0])) {
          $error = true;
          $error_text = 0;
          }
          $lid = ModUtil::apiFunc('IWusers', 'admin', 'createImport', array('accio' => 0,
          'id' => $valors[1],
          'nom' => $valors[2],
          'cognom1' => $valors[3],
          'cognom2' => $valors[4],
          'nom_u' => $valors[5],
          'email' => $valors[6],
          'contrasenya' => $valors[7],
          'grup' => $valors[8]));
          if ($lid == false) {
          $error = true;
          $error_text = 1;
          }
          $num_registres++;
          }
          }
          }

          //comprova que tots els registres estiguin complets
          if ($num_registres != round($num_registres) && !$error) {
          $error_text = 0;
          $error = true;
          }

          //Tanquem el fitxer d'exportaciÃ³
          fclose($fp);
          //Esborra el fitxer del servidor
          unlink(ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder') . $nom_fitxer);

          //Fem una classificaciÃ³ dels registres
          //Valors de la 1a. classificaciÃ³: 	 Els registres que estan a les dues taules i sÃ³n idÃšntics els esborra directament de les dues taules
          //									 0->els registres estan en una taula i no a l'altre
          //									 1->estan a les dues taules perÃ² no sÃ³n idÃšntics

          $primer_filtre = ModUtil::apiFunc('IWusers', 'admin', 'primer_filtre');
          if ($primer_filtre == false) {
          $error = true;
          $error_text = 2;
          }

          if ($error) {
          $view->assign('error3', true);
          $view->assign('error_text', $error_text);
          $pas = 0;
          break;
          }

          //LES DADES S'HAN CARREGAT CORRECTAMENT A LA BASE DE DADES DE SAGA
          //Mostrem el nombre de registres trobats
          $view->assign('goOn21', true);
          $view->assign('numRecords', $num_registres);
          break;
          case 3:
          //Comprovem si s'han resolt tots els conflictes per poder passar al pas segÃŒent
          //per fer-ho mirem els registres de la taula de SAGA que tenen el valor d'acciÃ³=0
          //NomÃ©s serÃ  possible passar al pas segÃŒent quan tots els registres tinguin el valor acciÃ³ diferent de 0
          $conflictes = ModUtil::apiFunc('IWusers', 'admin', 'conflictes');
          if ($conflictes == false) {
          $error = true;
          $error_text = 1;
          } else {
          if (($conflictes['intranet'] + $conflictes['saga'] + $conflictes['ordres'] + $conflictes['diferents']) == 0) {
          $potpassar = true;
          }
          }

          // Get all users info
          $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
          $usersUname = ModUtil::func('IWmain', 'user', 'getAllUsersInfo', array('info' => 'l',
          'sv' => $sv));
          switch ($subpas) {
          case 1:
          //Mirem si cal fer alguna acciÃ³ degut a iteracions anteriors
          if (!empty($tria)) {
          //ConfirmaciÃ³ del codi d'autoritzaciÃ³.
          if (!SecurityUtil::confirmAuthKey()) {
          SessionUtil::setVar('errormsg', $this->__('Invalid \'authkey\':  this probably means that you pressed the \'Back\' button, or that the page \'authkey\' expired. Please refresh the page and try again.'));
          return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
          }
          //Procedim a gestionar l'acciÃ³ i a fer els canvis oportunta a les bases de dades
          $i = 0;
          foreach ($tria as $tria1) {
          $valor1 = -2;
          $valor2 = -2;
          if ($tria[$i] == 1) {
          $valor1 = 2;
          }
          $accio = ModUtil::apiFunc('IWusers', 'admin', 'modificaaccio',
          array('taula' => 1,
          'valor' => $valor1,
          'suid' => $suids[$i]));
          $accio = ModUtil::apiFunc('IWusers', 'admin', 'modificaaccio',
          array('taula' => 2,
          'valor' => $valor2,
          'suid' => $suidi[$i]));
          $i++;
          }
          //Tornem a fer un recompte dels registres que queden per gestionar
          $conflictes = ModUtil::apiFunc('IWusers', 'admin', 'conflictes');
          }
          $sortida->FormHidden('pas', 3);
          $sortida->FormHidden('subpas', 1);
          $sortida->FormHidden('authid', SecurityUtil::generateAuthKey());
          $sortida->text($this->__('Managing users who are on both tables, but have differences in data'));
          $sortida->linebreak(1);

          //Buquem els usuaris que tenen un 1 a les dues taules
          $usuaris = ModUtil::apiFunc('IWusers', 'admin', 'get_gestio',
          array('subpas' => 1,
          'inici' => 0,
          'numitems' => 20));

          if ($usuaris == false) {
          $sortida->linebreak(1);
          $sortida->boldtext($this->__('We found no users to manage in this group.'));
          $sortida->linebreak(1);
          $no_hi_ha = true;
          }
          if (!$no_hi_ha) {
          //Possibles opcions en la tria de les dades
          $tria_MS = array(array('id' => '1',
          'name' => $this->__('Import file')),
          array('id' => '2',
          'name' => $this->__('the intranet')));
          $sortida->text($this->__('The users are in the following two tables. In each case, select the most appropriate'));
          $sortida->linebreak(2);
          foreach ($usuaris as $usuari) {
          $sortida->FormHidden('suids[]', $usuari['suids']);
          $sortida->FormHidden('suidi[]', $usuari['suidi']);
          $sortida->Tablestart($usersUname[$usuari['uid']], '', '1', '200');
          $sortida->Tablerowstart();
          $sortida->Tablecolstart();
          $sortida->Boldtext(' ');
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('Information import file'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('Information intranet'));
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $sortida->Tablerowstart();
          $sortida->Tablecolstart('', 'left');
          $sortida->Boldtext($camps_esperats[5]);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['noms'] == '') {
          $usuari['noms'] = '---';
          }
          $sortida->text($usuari['login']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['nomi'] == '') {
          $usuari['nomi'] = '---';
          }
          $sortida->text($usersUname[$usuari['uid']]);
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $sortida->Tablerowstart();
          $sortida->Tablecolstart('', 'left');
          $sortida->Boldtext($camps_esperats[2]);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['noms'] == '') {
          $usuari['noms'] = '---';
          }
          $sortida->text($usuari['noms']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['nomi'] == '') {
          $usuari['nomi'] = '---';
          }
          $sortida->text($usuari['nomi']);
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $sortida->Tablerowstart();
          $sortida->Tablecolstart('', 'left');
          $sortida->Boldtext($camps_esperats[3]);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['cognom1s'] == '') {
          $usuari['cognom1s'] = '---';
          }
          $sortida->text($usuari['cognom1s']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['cognom1i'] == '') {
          $usuari['cognom1i'] = '---';
          }
          $sortida->text($usuari['cognom1i']);
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $sortida->Tablerowstart();
          $sortida->Tablecolstart('', 'left');
          $sortida->Boldtext($camps_esperats[4]);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['cognom2s'] == '') {
          $usuari['cognom2s'] = '---';
          }
          $sortida->text($usuari['cognom2s']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['cognom2i'] == '') {
          $usuari['cognom2i'] = '---';
          }
          $sortida->text($usuari['cognom2i']);
          $sortida->Tablecolend();
          $sortida->Tablerowend();

          //Busca els grups als quals pertany l'usuari
          $quins_grups = '';
          $grups = ModUtil::apiFunc('IWusers', 'user', 'quins_grups', array('uid' => $usuari['uid']));
          foreach ($grups as $grup) {
          $quins_grups .= $grup['pn_name'] . '<br>';
          }

          if ($quins_grups == '') {
          $quins_grups = '---';
          }

          $sortida->Tablerowstart();
          $sortida->Tablecolstart('2', 'left', 'top');
          $sortida->Boldtext($this->__('Group/s'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->SetInputMode(_PNH_VERBATIMINPUT);
          $sortida->text($quins_grups);
          $sortida->SetInputMode(_PNH_PARSEINPUT);
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $sortida->Tablerowstart();
          $sortida->Tablecolstart('3', 'left');
          $sortida->Boldtext($this->__('I would have data'));
          $sortida->formselectmultiple('tria[]', $tria_MS);
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $sortida->Tableend();
          $sortida->linebreak(2);
          }
          }
          if ($conflictes['diferents'] > 0) {
          $sortida->text($this->__('Are') . $conflictes['diferents'] . $this->__('manage records in this section '));
          $sortida->linebreak(2);
          $sortida->formsubmit($this->__('Next >>'));
          $sortida->linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0))), $this->__('Back to the menu of options'));
          } else {
          //Enviem a l'usuari al menÃº de gestiÃ³ de les dades
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0)));
          $sortida->linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0))), $this->__('Back to the menu of options'));
          }
          break;
          case 2:
          //Mirem si cal fer alguna acciÃ³ degut a iteracions anteriors
          if (!empty($suids)) {
          //ConfirmaciÃ³ del codi d'autoritzaciÃ³.
          if (!SecurityUtil::confirmAuthKey()) {
          SessionUtil::setVar('errormsg', $this->__('Invalid \'authkey\':  this probably means that you pressed the \'Back\' button, or that the page \'authkey\' expired. Please refresh the page and try again.'));
          return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
          }

          //Procedim a gestionar l'acciÃ³ i a fer els canvis oportunta a les bases de dades
          $i = 0;
          foreach ($tria as $tria1) {
          switch ($tria[$i]) {
          case 1:
          $group = '';
          $grup = FormUtil::getPassedValue('grup_' . $suids[$i], isset($args['grup_' . $suids[$i]]) ? $args['grup_' . $suids[$i]] : null, 'POST');
          foreach ($grup as $g) {
          $group .= $g . '|';
          }


          $valor = 3;
          $modificasaga = ModUtil::apiFunc('IWusers', 'admin', 'modificasaga', array('valor' => 3,
          'suid' => $suids[$i],
          'login' => $login[$i],
          'email' => $email[$i],
          'grup' => $group,
          'contrasenya' => $contrasenya[$i]));
          break;
          case 2:
          $modificasaga = ModUtil::apiFunc('IWusers', 'admin', 'modificasaga', array('valor' => 4,
          'suid' => $suids[$i],
          'uid' => $associa[$i]));

          $accio = ModUtil::apiFunc('IWusers', 'admin', 'modificaaccio', array('taula' => 2,
          'valor' => (-4),
          'uid' => $associa[$i],
          'suid' => $suids[$i]));
          break;
          case 3:
          $accio = ModUtil::apiFunc('IWusers', 'admin', 'modificaaccio', array('taula' => 1,
          'valor' => (-3),
          'suid' => $suids[$i]));
          break;
          }
          $i++;
          }
          //Tornem a fer un recompte dels registres que queden per gestionar
          $conflictes = ModUtil::apiFunc('IWusers', 'admin', 'conflictes');
          }

          $sortida->FormHidden('pas', 3);
          $sortida->FormHidden('subpas', 2);
          $sortida->FormHidden('authid', SecurityUtil::generateAuthKey());
          $sortida->text($this->__('Managing users who are on the table and not import to the intranet'));
          $sortida->linebreak(1);

          //Buquem els usuaris que tenen un 3 a la taula de saga
          $usuaris = ModUtil::apiFunc('IWusers', 'admin', 'get_gestio', array('subpas' => 2,
          'inici' => 0,
          'numitems' => 20));
          if ($usuaris == false) {
          $sortida->linebreak(1);
          $sortida->boldtext($this->__('We found no users to manage in this group.'));
          $sortida->linebreak(1);
          $no_hi_ha = true;
          }

          if (!$no_hi_ha) {
          //Possibles opcions en la tria de les dades
          //Agafem els usuaris que estan a la Intranet perÃ³ no a SAGA i els preparem per un camp MS
          $registres = ModUtil::apiFunc('IWusers', 'admin', 'get_gestio', array('subpas' => 3,
          'inici' => 0,
          'numitems' => 9999999999));

          // Get all users info
          $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
          $usersFullName = ModUtil::func('IWmain', 'user', 'getAllUsersInfo', array('info' => 'ncc',
          'sv' => $sv));

          foreach ($registres as $registre) {
          $associa_MS[] = array('id' => $registre['uid'],
          'name' => $usersFullName[$registre['uid']]);
          }

          $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
          $grups_MS = ModUtil::func('IWmain', 'user', 'getAllGroups', array('sv' => $sv));

          //Get the identity for the default group configurated in the intranet
          foreach ($grups_MS as $group) {
          $defaultgroup = ModUtil::getVar('Groups', 'defaultgroup');
          if ($defaultgroup == $group['name']) {
          $defaultGroup_id = $group['id'];
          }
          }

          $sortida->text($this->__('The users are only following the import table of users. In each case, select the most appropriate'));
          $sortida->linebreak(2);
          $sortida->Tablestart('', '', '1');
          if (count($associa_MS) > 0) {
          $sortida->Tablerowstart();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('Name'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('1st surname'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('2nd surname'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('Option'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('For the "Add new user" select a user name, a password and a group'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('For the "Link to the user", choose which user you want to associate'));
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $tria_MS = array(array('id' => '1',
          'name' => $this->__('Creates the user')),
          array('id' => '2',
          'name' => $this->__('Attached to the user')),
          array('id' => '3',
          'name' => $this->__('Do not make any action')));
          } else {
          $sortida->Tablerowstart();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('Name'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('1st surname'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('2nd surname'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('Option'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('For the "Add new user" select a user name, a password and a group'));
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $tria_MS = array(array('id' => '1',
          'name' => $this->__('Creates the user')),
          array('id' => '3',
          'name' => $this->__('Do not make any action')));
          }
          foreach ($usuaris as $usuari) {
          $sortida->FormHidden('suids[]', $usuari['suids']);
          $sortida->Tablerowstart();
          $sortida->Tablecolstart();
          if ($usuari['noms'] == '') {
          $usuari['noms'] = '---';
          }
          $sortida->text($usuari['noms']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['cognom1s'] == '') {
          $usuari['cognom1s'] = '---';
          }
          $sortida->text($usuari['cognom1s']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['cognom2s'] == '') {
          $usuari['cognom2s'] = '---';
          }
          $sortida->text($usuari['cognom2s']);
          $sortida->Tablecolstart();
          $sortida->Formselectmultiple('tria[]', $tria_MS, 0);
          $sortida->Tablecolend();
          $sortida->Tablecolstart('', 'center');
          if ($usuari['login'] == "") {
          $login = ModUtil::func('IWusers', 'admin', 'posanom', array('nom' => $usuari['noms'],
          'cognom1' => $usuari['cognom1s']));
          } else {
          $login = $usuari['login'];
          }
          $sortida->Formtext('login[]', $login, 12, 25);
          $sortida->linebreak(1);
          $sortida->Formtext('email[]', $usuari['email'], 12, 25);
          $sortida->linebreak(1);

          //$sortida->Formselectmultiple('grup[]',$grups_MS,0,0,$usuari['grup']);
          $userGroupsArray = explode('|', $usuari['grup']);
          $sortida->SetInputMode(_PNH_VERBATIMINPUT);
          $sortida->text('<select name="grup_' . $usuari['suids'] . '[]" size="2" multiple="multiple">');

          foreach ($grups_MS as $oneGroup) {
          $selected = (in_array($oneGroup['id'], $userGroupsArray) || $usuari['grup'] == '' && $oneGroup['id'] == $defaultGroup_id) ? "selected" : "";
          $sortida->text('<option ' . $selected . ' value="' . $oneGroup['id'] . '">' . $oneGroup['name'] . "</option>");
          }

          $sortida->text("</select>");
          $sortida->SetInputMode(_PNH_PARSEINPUT);

          $sortida->linebreak(1);
          if ($usuari['contrasenya'] == "") {
          $pass = strtolower(substr($usuari['noms'], 0, 1) . substr($usuari['cognom1s'], 0, 1)) . mt_rand(1000, 9999);
          $pass = ModUtil::func('IWusers', 'admin', 'posanom', array('nom' => '',
          'cognom1' => $pass));
          } else {
          $pass = $usuari['contrasenya'];
          }
          $sortida->Formtext('contrasenya[]', $pass, 12, 12);
          $sortida->Tablecolend();
          if (count($associa_MS) > 0) {
          $sortida->Tablecolstart();
          $sortida->Formselectmultiple('associa[]', $associa_MS, 0);
          $sortida->Tablecolend();
          }
          $sortida->Tablerowend();
          }
          $sortida->Tableend();
          }
          if ($conflictes['saganointranet'] > 0) {
          $sortida->text($this->__('Are') . $conflictes['saganointranet'] . $this->__('manage records in this section '));
          $sortida->linebreak(2);
          $sortida->formsubmit($this->__('Next >>'));
          $sortida->linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0))), $this->__('Back to the menu of options'));
          } else {
          //Enviem a l'usuari al menï¿œ de gestiï¿œ de les dades
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0)));
          $sortida->linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0))), $this->__('Back to the menu of options'));
          }
          break;
          case 3:
          //Mirem si cal fer alguna acciï¿œ degut a iteracions anteriors
          if (!empty($submit)) {
          //Confirmaciï¿œ del codi d'autoritzaciï¿œ.
          if (!SecurityUtil::confirmAuthKey()) {
          SessionUtil::setVar('errormsg', $this->__('Invalid \'authkey\':  this probably means that you pressed the \'Back\' button, or that the page \'authkey\' expired. Please refresh the page and try again.'));
          return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
          }
          //Procedim a gestionar l'acciï¿œ i a fer els canvis oportunta a les bases de dades

          $i = 0;
          $valor = ($submit == $this->__('Delete user of the intranet')) ? '5' : '-5';
          foreach ($idintra as $suid0) {
          $accio = ModUtil::apiFunc('IWusers', 'admin', 'modificaaccio', array('taula' => 2,
          'valor' => $valor,
          'suid' => $idintra[$i]));
          $i++;
          }
          //Tornem a fer un recompte dels registres que queden per gestionar
          $conflictes = ModUtil::apiFunc('IWusers', 'admin', 'conflictes');
          }


          $sortida->FormHidden('pas', 3);
          $sortida->FormHidden('subpas', 3);
          $sortida->FormHidden('authid', SecurityUtil::generateAuthKey());
          $sortida->text($this->__('Managing users who are on the table on the intranet and not in the table to import'));
          $sortida->linebreak(1);
          //Buquem els usuaris que tenen un 1 a les dues taules
          $usuaris = ModUtil::apiFunc('IWusers', 'admin', 'get_gestio', array('subpas' => 3,
          'inici' => 0,
          'numitems' => 20));
          if ($usuaris == false) {
          $sortida->linebreak(1);
          $sortida->boldtext($this->__('We found no users to manage in this group.'));
          $sortida->linebreak(1);
          $no_hi_ha = true;
          }

          if (!$no_hi_ha) {
          //Possibles opcions en la tria de les dades
          $sortida->text($this->__('The users are only following in the table on the intranet. In each case, select the most appropriate'));
          $sortida->linebreak(2);

          $sortida->Tablestart('', array($this->__('Username'),
          $this->__('Name'),
          $this->__('1st surname'),
          $this->__('2nd surname'),
          $this->__('Option')), '1');
          foreach ($usuaris as $usuari) {
          $sortida->FormHidden('suidi[]', $usuari['suidi']);
          $sortida->Tablerowstart();
          $sortida->Tablecolstart();
          $sortida->text($usersUname[$usuari['uid']]);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['noms'] == '') {
          $usuari['noms'] = '---';
          }
          $sortida->text($usuari['noms']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['cognom1s'] == '') {
          $usuari['cognom1s'] = '---';
          }
          $sortida->text($usuari['cognom1s']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          if ($usuari['cognom2s'] == '') {
          $usuari['cognom2s'] = '---';
          }
          $sortida->text($usuari['cognom2s']);
          $sortida->Tablecolstart();
          $sortida->FormCheckbox('idintra[]', 0, $usuari['suidi']);
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          }

          $sortida->Tablerowstart();
          $sortida->TableColStart('10', 'right', 'top');
          $sortida->SetInputMode(_PNH_VERBATIMINPUT);
          $sortida->text("<a onclick=\"setCheckboxes(true,'idintra[]')\" style='cursor:pointer; cursor:hand'>" . $this->__('Flag') . "</a>/<a onclick=\"setCheckboxes(false,'idintra[]')\" style='cursor:pointer; cursor:hand'>" . $this->__('Unflag') . "</a> tots ");
          $sortida->SetInputMode(_PNH_PARSEINPUT);
          $sortida->formsubmit($this->__('Delete user of the intranet'), '', 'submit');
          $sortida->text(' ');
          $sortida->formsubmit($this->__('Do not make any action'), '', 'submit');
          $sortida->TableRowEnd();
          $sortida->Tablerowend();
          $sortida->Tableend();
          }
          if ($conflictes['intranetnosaga'] > 0) {
          $sortida->text($this->__('Are') . $conflictes['intranetnosaga'] . $this->__('manage records in this section '));
          $sortida->linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0))), $this->__('Back to the menu of options'));
          } else {
          //Enviem a l'usuari al menï¿œ de gestiï¿œ de les dades
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0)));
          $sortida->linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0))), $this->__('Back to the menu of options'));
          }
          break;
          case 4:
          //Mirem si cal fer alguna acciÃ³ degut a iteracions anteriors
          if (!empty($submit)) {
          if (!SecurityUtil::confirmAuthKey()) {
          return LogUtil::registerAuthidError(ModUtil::url('IWusers', 'admin', 'main'));
          }
          $i = 0;

          // Get all users info
          $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
          $usersUname = ModUtil::func('IWmain', 'user', 'getAllUsersInfo', array('info' => 'l',
          'sv' => $sv));

          foreach ($suids as $a) {
          //Agafem els registres per cadascuna de les accions que cal portar a terme
          if ($suids[$i] != '') {
          $registre = ModUtil::apiFunc('IWusers', 'admin', 'get_dades', array('taula' => 1,
          'suid' => $suids[$i]));

          if ($registre == false) {
          SessionUtil::setVar('errormsg', $this->__('An error occurred at the time of carrying out the orders'));
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 4)));
          return false;
          }
          switch ($registre['accio']) {
          case 2:
          //Modifiquem les dades de la Intranet per les de SAGA on hi hagi les mateixes id
          $lid = ModUtil::apiFunc('IWusers', 'admin', 'modifica', array('id' => $registre['id'],
          'nom' => $registre['nom'],
          'cognom1' => $registre['cognom1'],
          'cognom2' => $registre['cognom2'],
          'origen' => 'saga'));
          if ($lid) {
          ModUtil::apiFunc('IWusers', 'admin', 'esborra_registre', array('taula' => 1,
          'suid' => $suids[$i]));
          } else {
          SessionUtil::setVar('errormsg', $this->__('An error occurred at the time of carrying out the orders'));
          }
          break;
          case 3:
          //Donem d'alta a l'usuari a la Intranet amb la Id de SAGA
          //Comprovem que s'han complimentat les dades requerides
          if (empty($registre['login'])) {
          $sortida->Title(_USUARIS);
          $sortida->text($registre['nom'] . ' ' . $registre['cognom1'] . ' ' . $registre['cognom2']);
          $sortida->Linebreak(2);
          $sortida->Text($this->__('You have not provided the user name. You\'ll need to discard the order.'));
          $sortida->Linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 4))), $this->__('Back to the table of execution.'));
          $output = $sortida->GetOutput();

          $view->assign('output', $output);
          return $view->fetch('IWusers_admin_import.htm');
          }

          if (empty($registre['contrasenya'])) {
          $sortida->Title(_USUARIS);
          $sortida->text($registre['nom'] . ' ' . $registre['cognom1'] . ' ' . $registre['cognom2']);
          $sortida->Linebreak(2);
          $sortida->Text($this->__('You have not provided the password for this user. You\'ll need to discard the first order of the list.'));
          $sortida->Linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 4))), $this->__('Back to the table of execution.'));

          $output = $sortida->GetOutput();

          $view->assign('output', $output);
          return $view->fetch('IWusers_admin_import.htm');
          }

          if (in_array($registre['login'], $usersUname)) {
          $sortida->Linebreak(1);
          $sortida->text($registre['login'] . '->' . $registre['nom'] . ' ' . $registre['cognom1'] . ' ' . $registre['cognom2']);
          $sortida->Linebreak(2);
          $sortida->Text($this->__('The username is invalid because it already exists. You\'ll need to discard the first order of the list'));
          $sortida->Linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 4))), $this->__('Back to the table of execution.'));

          $output = $sortida->GetOutput();

          $view->assign('output', $output);
          return $view->fetch('IWusers_admin_import.htm');
          }

          //El nom d'usuari no existeix i el creem
          $lid = ModUtil::apiFunc('IWusers', 'admin', 'createUser', array('uname' => $registre['login'],
          'email' => $registre['email'],
          'pass' => $registre['contrasenya'],
          'nom' => $registre['nom'],
          'cognom1' => $registre['cognom1'],
          'cognom2' => $registre['cognom2']));

          if ($lid == false) {
          SessionUtil::setVar('statusmsg', $this->__('There was an error to register a new user.'));
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 4)));
          return false;
          }

          //Entrem les dades de l'usuari
          $lid1 = ModUtil::apiFunc('IWusers', 'admin', 'create', array('id' => $registre['id'],
          'uid' => $lid,
          'nom' => $registre['nom'],
          'cognom1' => $registre['cognom1'],
          'cognom2' => $registre['cognom2']));

          if ($lid1 == false) {
          SessionUtil::setVar('statusmsg', $this->__('We have created the user, but there was an error in entering the personal information.'));
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 4)));
          return false;
          }

          //Posem l'usuari dins del grup escollit
          $lid2 = ModUtil::apiFunc('IWusers', 'admin', 'addUserToGroup', array('uid' => $lid,
          'gid' => $registre['grup']));

          if ($lid2 == false) {
          SessionUtil::setVar('statusmsg', $this->__('We have created the user, but there was a failure to assign it to the group'));
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 4)));
          return false;
          }

          if ($lid1) {
          //Li poso el valor -7 per no tenir-lo mï¿œs en compte en la sincronitzaciï¿œ i reconeixer-lo posteriorment
          ModUtil::apiFunc('IWusers', 'admin', 'modificaaccio', array('taula' => 1,
          'valor' => -7,
          'suid' => $suids[$i]));
          } else {
          SessionUtil::setVar('errormsg', $this->__('An error occurred at the time of carrying out the orders'));
          }
          break;
          case 4:
          //Modifiquem les dades de la Intranet per les de SAGA incloent-hi la Id on tinguem la uid d'usuari rebuda
          //Modifiquem les dades de la Intranet per les de SAGA on hi hagi les mateixes id
          $lid = ModUtil::apiFunc('IWusers', 'admin', 'modifica', array('id' => $registre['id'],
          'uid' => $registre['uid'],
          'nom' => $registre['nom'],
          'cognom1' => $registre['cognom1'],
          'cognom2' => $registre['cognom2']));
          if ($lid) {
          ModUtil::apiFunc('IWusers', 'admin', 'esborra_registre', array('taula' => 1,
          'suid' => $suids[$i]));
          } else {
          SessionUtil::setVar('errormsg', $this->__('An error occurred at the time of carrying out the orders'));
          }
          break;
          default:
          //Ens assegurem de que s'esborren tots els registres amb valors negatius ja que la resta de casos ja estan contemplats
          ModUtil::apiFunc('IWusers', 'admin', 'esborra_registre', array('taula' => 1,
          'suid' => $suids[$i]));
          break;
          }
          }
          if ($suidi[$i] != '') {
          $registre = ModUtil::apiFunc('IWusers', 'admin', 'get_dades', array('taula' => 2,
          'suid' => $suidi[$i]));
          if ($registre == false) {
          SessionUtil::setVar('errormsg', $this->__('An error occurred at the time of carrying out the orders'));
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 4)));
          return false;
          }

          switch ($registre['accio']) {
          case 5:
          //Donem de baixa a l'usuari que tingui la uid de la taula auxiliar d'usuaris
          //Esborrem l'usuari de la taula users de PN
          ModUtil::apiFunc('IWusers', 'admin', 'deleteUser', array('uid' => $registre['uid']));
          //Esborrem l'usuari de la taula de grups de PN
          ModUtil::apiFunc('IWusers', 'admin', 'deleteUserFromGroups', array('uid' => $registre['uid']));
          break;
          default:
          break;
          }
          ModUtil::apiFunc('IWusers', 'admin', 'esborra_registre', array('taula' => 2,
          'suid' => $suidi[$i]));
          }
          $i++;
          }
          //Tornem a fer un recompte dels registres que queden per gestionar
          $conflictes = ModUtil::apiFunc('IWusers', 'admin', 'conflictes');
          }

          $sortida->FormHidden('pas', 3);
          $sortida->FormHidden('subpas', 4);
          $sortida->FormHidden('authid', SecurityUtil::generateAuthKey());
          $sortida->text($this->__('Review and execute the orders'));
          $sortida->linebreak(1);

          //Buquem els usuaris que tenen un 1 a les dues taules
          $usuaris = ModUtil::apiFunc('IWusers', 'admin', 'get_ordres', array('inici' => 0,
          'numitems' => 20));
          if ($usuaris == false) {
          $sortida->linebreak(1);
          $sortida->boldtext($this->__('No orders have been found to manage.'));
          $sortida->linebreak(1);
          $no_hi_ha = true;
          }

          if (!$no_hi_ha) {
          //Possibles opcions en la tria de les dades
          $sortida->linebreak(1);
          $sortida->Tablestart('', '', '1');
          $sortida->Tablerowstart();
          $sortida->Tablecolstart('2', '');
          $sortida->Boldtext($this->__('Action to be held'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Boldtext($this->__('Option'));
          $sortida->Tablecolend();
          $sortida->Tablerowend();

          // Get all the groups information
          $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
          $groupsInfo = ModUtil::func('IWmain', 'user', 'getAllGroupsInfo', array('sv' => $sv));

          foreach ($usuaris as $usuari) {
          $sortida->FormHidden('suidi[]', $usuari['suidi']);
          $sortida->FormHidden('suids[]', $usuari['suids']);
          switch ($usuari['accios']) {
          case 2:
          $explicaordre = $this->__('The user') . '<strong>' . $usuari['noms'] . ' ' . $usuari['cognom1s'] . ' ' . $usuari['cognom2s'] . '</strong>' . $this->__('Is on the two tables and I want only  that are in the import file');
          $ordre = $this->__('Elect to the data of import file');
          break;
          case -2:
          $explicaordre = $this->__('The user') . '<strong>' . $usuari['noms'] . ' ' . $usuari['cognom1s'] . ' ' . $usuari['cognom2s'] . '</strong>' . $this->__('On the two tables and so on with one on the intranet');
          $ordre = $this->__('Elect to the intranet data');
          break;
          case 3:

          if ($usuari['grup'] != '') {
          $groups = explode('|', substr($usuari['grup'], 0, -1));
          $groupsString = '';
          foreach ($groups as $g) {
          $groupsString .= $groupsInfo[$g] . ', ';
          }
          $dinsgrup = $this->__(' in the group/s: ') . '<strong>' . substr($groupsString, 0, -2) . '</strong>';

          if (substr_count($dinsgrup, ", ") > 0) {
          $lastspace = strrpos($dinsgrup, ", ");
          $dinsgrup = substr_replace($dinsgrup, " " . $this->__('and') . " ", $lastspace, 1);
          }
          } else {
          $dinsgrup = '';
          }
          $explicaordre = $this->__('The user') . '<strong>' . $usuari['noms'] . ' ' . $usuari['cognom1s'] . ' ' . $usuari['cognom2s'] . '</strong>' . $this->__('will be discharged to the intranet with the username ') . ' <strong>' . $usuari['login'] . '</strong>' . $dinsgrup;
          $ordre = $this->__('Discharged in the intranet');
          break;
          case -3:
          $explicaordre = $this->__('There is no action on the user') . '<strong>' . $usuari['noms'] . ' ' . $usuari['cognom1s'] . ' ' . $usuari['cognom2s'] . '</strong>' . $this->__('Import file ');
          $ordre = $this->__('Any action on the user') . $this->__('Import file ');
          break;
          case 4:
          $explicaordre = $this->__('The user') . '<strong>' . $usuari['noms'] . ' ' . $usuari['cognom1s'] . ' ' . $usuari['cognom2s'] . '</strong>' . $this->__('	associated with the user  ') . '<strong>' . $usersFullName[$usuari['uids']] . '</strong>' . $this->__('for the intranet ');


          $ordre = $this->__('Association of users');
          break;
          }
          $taula = 1;
          $quins = $usuari['suids'];

          if ($usuari['accioi'] != '') {
          switch ($usuari['accioi']) {
          case 5:
          $explicaordre = $this->__('The user') . '<strong>' . $usuari['nomi'] . ' ' . $usuari['cognom1i'] . ' ' . $usuari['cognom2i'] . '</strong>' . $this->__('');
          $ordre = $this->__('Low users');
          break;
          case -5:
          $explicaordre = $this->__('There is no action on the user') . '<strong>' . $usuari['nomi'] . ' ' . $usuari['cognom1i'] . ' ' . $usuari['cognom2i'] . '</strong>' . $this->__('fot he intranet');
          $ordre = $this->__('Any action on the user') . $this->__('fot he intranet');
          break;
          default:
          break;
          }
          $taula = 2;
          $quins = $usuari['suidi'];
          }

          $sortida->Tablerowstart();
          $sortida->Tablecolstart('', 'left');
          $sortida->text($ordre);
          $sortida->Tablecolend();

          $sortida->Tablecolstart('', 'left');
          $sortida->SetInputMode(_PNH_VERBATIMINPUT);
          $sortida->text($explicaordre);
          $sortida->SetInputMode(_PNH_PARSEINPUT);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('quins' => $quins,
          'taula' => $taula,
          'pas' => 3,
          'subpas' => 6))), $this->__('Discard'));
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          }
          $sortida->Tableend();
          }
          if ($conflictes['ordres'] > 0) {
          $sortida->text($this->__('Are') . $conflictes['ordres'] . $this->__('manage records in this section '));
          $sortida->linebreak(2);
          $sortida->formsubmit($this->__('Next >>'), '', 'submit');
          $sortida->linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3, 'subpas' => 0))), $this->__('Back to the menu of options'));
          } else {
          //Enviem a l'usuari al menï¿œ de gestiï¿œ de les dades
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0)));
          $sortida->linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0))), $this->__('Back to the menu of options'));
          }
          break;

          case 5: //Reseteja totes les modificacions portades a terme
          ModUtil::apiFunc('IWusers', 'admin', 'fesreset', array('quins' => $quins));
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0)));
          break;
          case 6: //Reseteja una modificaciï¿œ
          ModUtil::apiFunc('IWusers', 'admin', 'fesresetuna', array('quins' => $quins,
          'taula' => $taula));
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 4)));
          $sortida->linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 0))), $this->__('Back to the menu of options'));
          $sortida->linebreak(2);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 4))), $this->__('Back to the table of execution.'));
          break;
          case 7: //Marca els registres per no fer acciï¿œ sobre d'ells
          ModUtil::apiFunc('IWusers', 'admin', 'capaccio', array('quins' => $quins));
          System::redirect(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3)));
          break;
          default;
          $sortida->text($this->__('Choose the action you want to do'));
          $sortida->linebreak(2);
          $sortida->TableStart('', '', '0', '100%');
          $sortida->Tablerowstart();
          $sortida->Tablecolstart('', 'left', 'top');
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 1))), $this->__('Managing users who are on both tables, but have differences in data'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart('', '', 'top');
          $sortida->text($conflictes['diferents'] . $this->__('Registration '));
          $sortida->Tablecolend();
          $sortida->Tablecolstart('', 'left', 'top');
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 5,
          'quins' => 1))), $this->__('Cancel the changes'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart('', 'left', 'top');
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 7,
          'quins' => 1))), $this->__('Do not make any action'));
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $sortida->Tablerowstart();
          $sortida->Tablecolstart('', 'left', 'top');
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 2))), $this->__('Managing users who are on the table and not import to the intranet'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart('', '', 'top');
          $sortida->text($conflictes['saganointranet'] . $this->__('Registration '));
          $sortida->Tablecolend();
          $sortida->Tablecolstart('', 'left', 'top');
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 5,
          'quins' => 2))), $this->__('Cancel the changes'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart('', 'left', 'top');
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 7,
          'quins' => 2))), $this->__('Do not make any action'));
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $sortida->Tablerowstart();
          $sortida->Tablecolstart('', 'left', 'top');
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 3))), $this->__('Managing users who are on the table on the intranet and not in the table to import'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart('', '', 'top');
          $sortida->text($conflictes['intranetnosaga'] . $this->__('Registration '));
          $sortida->Tablecolend();
          $sortida->Tablecolstart('', 'left', 'top');
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 5,
          'quins' => 3))), $this->__('Cancel the changes'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart('', 'left', 'top');
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 7,
          'quins' => 3))), $this->__('Do not make any action'));
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $sortida->Tablerowstart();
          $sortida->Tablecolstart('10');
          $sortida->text('---');
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $sortida->Tablerowstart();
          $sortida->Tablecolstart('', 'left', 'top');
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'import', array('pas' => 3,
          'subpas' => 4))), $this->__('Review and execute the orders'));
          $sortida->Tablecolend();
          $sortida->Tablecolstart('', '', 'top');
          $sortida->text($conflictes['ordres'] . $this->__('Registration '));
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          $sortida->TableEnd();
          $sortida->linebreak(1);
          $sortida->text($this->__('In the field import of users have'));
          $sortida->text($conflictes['saga']);
          $sortida->text($this->__('Registration  to manage'));
          $sortida->linebreak(1);
          $sortida->text($this->__('In the table of users of the intranet have'));
          $sortida->text($conflictes['intranet']);
          $sortida->text($this->__('Registration  to manage'));
          $sortida->linebreak(2);
          break;
          }
          if ($error) {
          $text_error = array(_USUARISREGISTRESINCORRECTES, _USUARISENTRADADADESERROR);
          $sortida->text($text_error[$error_text]);
          $pas = 0;
          break;
          }
          break;
          case 4:
          //Notifiquem l'ï¿œxit de la l'operaciï¿œ d'importaciï¿œ
          $sortida->Text($this->__('The data import was successful.'));
          //Mostrem, en cas d'haver-n'hi, les dades de connexiï¿œ dels usuaris que s'han creat en el procï¿œs
          $usuaris = ModUtil::apiFunc('IWusers', 'admin', 'get_gestio', array('subpas' => 4,
          'inici' => 0,
          'numitems' => 999999999999));
          $sortida->linebreak(2);
          if ($usuaris == false) {
          $sortida->Text($this->__('Has not discharged any user.'));
          } else {
          $sortida->linebreak(1);
          $sortida->Text($this->__('Data connection of new users created'));
          //mostra la llista d'usuaris amb les dades de connexiï¿œ en una taula
          $sortida->Tablestart('', '', 3);
          foreach ($usuaris as $usuari) {
          $sortida->Tablerowstart();
          $sortida->Tablecolstart();
          $sortida->text($usuari['noms']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->text($usuari['cognom1s']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->text($usuari['cognom2s']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->boldtext($this->__('Username'));
          $sortida->linebreak(1);
          $sortida->text($usuari['login']);
          $sortida->Tablecolend();
          $sortida->Tablecolstart();
          $sortida->boldtext($this->__('Password'));
          $sortida->linebreak(1);
          $sortida->text($usuari['contrasenya']);
          $sortida->Tablecolend();
          $sortida->Tablerowend();
          }
          $sortida->Tableend();
          $sortida->linebreak(1);
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'fitxer', array('pas' => 4))), $this->__('To create a txt file with the data, click on the link'));
          }
          $sortida->LineBreak(2);
          //Esborrem el contingut de les taules auxiliars de la importaciï¿œ i retornem a la taula d'usuaris
          $sortida->URL(DataUtil::formatForDisplay(ModUtil::url('IWusers', 'admin', 'final_import')), $this->__('The process ends'));
          break;
          }

          if ($potpassar) {
          $sortida->FormHidden('pas', $pas + 1);
          $sortida->LineBreak(2);
          $sortida->FormSubmit($this->__('Go to step') . ($pas + 1));
          }


          $output = $sortida->GetOutput();

          $view->assign('output', $output);
          return $view->fetch('IWusers_admin_import.htm');
         *
         */
    }

    /*
      Funció que filtra els noms d'usuari assignats per defecte per tal que no continguin caràcters rars
     */

    public function posanom($args) {

        $nom = FormUtil::getPassedValue('nom', isset($args['nom']) ? $args['nom'] : null, 'POST');
        $cognom1 = FormUtil::getPassedValue('cognom1', isset($args['cognom1']) ? $args['cognom1'] : null, 'POST');

        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }

        $login = strtolower(substr($nom, 0, 1) . substr($cognom1, 0, 7));
        for ($i = 0; $i <= strlen($login); $i++) {
            switch (substr($login, $i, 1)) {
                case 'á':
                    $caracter = 'a';
                    break;
                case 'à':
                    $caracter = 'a';
                    break;
                case 'é':
                    $caracter = 'e';
                    break;
                case 'è':
                    $caracter = 'e';
                    break;
                case 'í':
                    $caracter = 'i';
                    break;
                case 'ï':
                    $caracter = 'i';
                    break;
                case 'ó':
                    $caracter = 'o';
                    break;
                case 'ò':
                    $caracter = 'o';
                    break;
                case 'ú':
                    $caracter = 'u';
                    break;
                case 'ü':
                    $caracter = 'u';
                    break;
                case 'ñ':
                    $caracter = 'n';
                    break;
                case 'ç':
                    $caracter = 'c';
                    break;
                case ' ':
                    $caracter = '';
                    break;
                case '-':
                    $caracter = '';
                    break;
                default:
                    $caracter = substr($login, $i, 1);
                    break;
            }
            $login1 .= $caracter;
        }
        $i = 1;

        // Check if uname exists
        // Get all users uname
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersUname = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'l',
                            'sv' => $sv));

        while (in_array($login1, $usersUname)) {
            $login1 .= $i;
        }

        //Retornem la proposta de login
        return $login1;
    }

    /*
      Funciï¿œ que genera un fitxer imprimible amb els nous usuaris creats durant la importaciï¿œ
     */

    public function fitxer($args) {
        die('es necessita fitxer');
        /*
          //Agafem els parï¿œmetres per si se'ns retorna a la funciï¿œ desprï¿œs d'enviar les dades del formulari
          list($pas) = FormUtil::getPassedValue('pas');
          extract($args);

          $sortida = & new pnHTML();

          // Security check
          if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
          return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
          }

          //generem el fitxer on escriurem les dades a exportar
          $fitxer = ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder') . '/usuaris' . date('dmY') . '.txt';

          $f = fopen($fitxer, 'w');
          $usuaris = ModUtil::apiFunc('IWusers', 'admin', 'get_gestio',
          array('subpas' => 4,
          'inici' => 0,
          'numitems' => 10000));
          $sortida->linebreak(2);
          if ($usuaris == false) {
          $sortida->Text($this->__('Has not discharged any user.'));
          } else {
          fwrite($f, $this->__('Data connection of new users created') . "\r\n\r\n");
          foreach ($usuaris as $usuari) {
          fwrite($f, $usuari['noms'] . ' ' . $usuari['cognom1s'] . ' ' . $usuari['cognom2s'] . "\r\n" . $this->__('Username') . ': ' . $usuari['login'] . "\r\n" . $this->__('Password') . ' ' . $usuari['contrasenya'] . "\r\n---\r\n");
          }
          }
          fclose($f);

          //Comprovem que el fitxer s'hagi creat amb Ãšxit
          if (!is_file($fitxer)) {
          SessionUtil::setVar('errormsg', _USUARISFITXERERRORS);
          System::redirect(ModUtil::url('IWusers', 'user', 'main'));
          return false;
          }

          //Gather relevent info about file
          $len = filesize($fitxer);
          $filename = basename($fitxer);
          $file_extension = strtolower(substr(strrchr($filename, "."), 1));
          $ctype = "TXT/txt";
          //Begin writing headers
          header("Pragma: public");
          header("Expires: 0");
          header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
          header("Cache-Control: public");
          header("Content-Description: File Transfer");

          //Use the switch-generated Content-Type
          header("Content-Type: $ctype");

          //Force the download
          $header = "Content-Disposition: attachment; filename=" . $filename . ";";
          header($header);
          header("Content-Transfer-Encoding: binary");
          header("Content-Length: " . $len);
          @readfile($fitxer);

          //Esborra el fitxer del servidor
          unlink($fitxer);
          exit;
         *
         */
    }

    /*
      Funciï¿œ que finalitza l'operaciï¿œ d'importaciï¿œ de dades de SAGA i esborra les taules no necessaries
     */

    public function final_import($args) {

        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }

        //Agafem els parï¿œmetres per si se'ns retorna a la funciï¿œ desprï¿œs d'enviar les dades del formulari
        list($pas) = FormUtil::getPassedValue('pas');
        extract($args);

        ModUtil::apiFunc('IWusers', 'admin', 'buida_taules');
        return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
    }

    /*
      FunciÃ³ que presenta el formulari des d'on es poden editar les dades de connexiÃ³ (nom d'usuari i contrasenya) dels usuaris
     */

    public function editLogin($args) {

        $userId = FormUtil::getPassedValue('userId', isset($args) ? $args : null, 'POST');
        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
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

            $usersArray [] = array('uname' => $user['uid'],
                'uid' => $user['uid'],
                'nom' => $user['nom'],
                'cognom1' => $user['cognom1'],
                'cognom2' => $user['cognom2']);
        }

        // Get all users info
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersFullNames = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'ncc',
                            'sv' => $sv,
                            'list' => $usersList));
        // Get all users info
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersNames = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'l',
                            'sv' => $sv,
                            'list' => $usersList));
        // Get all users email
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersMails = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                array('info' => 'e',
                    'sv' => $sv,
                    'list' => $usersList));

        // Create output object
        $view = Zikula_View::getInstance('IWusers', false);

        $view->assign('users', $usersArray);
        $view->assign('usersFullNames', $usersFullNames);
        $view->assign('usersNames', $usersNames);
        $view->assign('usersMails', $usersMails);

        return $view->fetch('IWusers_admin_editLogin.htm');
    }

    /*
      FunciÃ³ que modifica les dades de connexiÃ³ dels usuaris
     */

    public function updateLogin($args) {

        $uid = FormUtil::getPassedValue('uid', isset($args) ? $args : null, 'POST');
        $userName = FormUtil::getPassedValue('userName', isset($args) ? $args : null, 'POST');
        $pass = FormUtil::getPassedValue('pass', isset($args) ? $args : null, 'POST');
        $userMail = FormUtil::getPassedValue('userMail', isset($args) ? $args : null, 'POST');

        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('IWusers', 'admin', 'main'));
        }

        $error = false;
        foreach ($uid as $u) {
            // Check if uname exists
            $usersList .= $u . '$$';
        }

        // Get all users uname
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersUname = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'l',
                            'sv' => $sv,
                            'usersList' => $usersList));
        foreach ($uid as $u) {
            $uname = $usersUname[$u];
            if (in_array($userName[$u], $usersUname) && $userName[$u] != $uname) {
                LogUtil::registerError($this->__('Username') . ' <strong>' . $userName[$u] . '</strong> ' . $this->__('already exists. You have to choose another.'));
                return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
            }
        }
        $method = ModUtil::getVar('Users', 'hash_method', 'sha1');

        foreach ($uid as $u) {
            if ($pass[$u] != '') {
                $contra = DataUtil::hash($pass[$u], $method);
            } else {
                $contra = '';
            }
            $usersGroups = array();
            $userCurrentGroups = ModUtil::apiFunc('Groups', 'user', 'getUserGroups', array('uid' => $u));

            foreach($userCurrentGroups as $userGroup) {
                $usersGroups[] = $userGroup['gid'];
            }

            $lid = ModUtil::apiFunc('Users', 'admin', 'updateUser',
                    array('userinfo' => array('uid' => $u,
                                              'uname' => $userName[$u],
                                              'email' => $userMail[$u],
                                              ),
                          'emailagain' => $userMail[$u],
                          'access_permissions' => $usersGroups,
                        ));
            if ($lid == false) {
                $error = true;
            }
        }

        if ($error) {
            LogUtil::registerError($this->__('Has been a problem updating the user login information.'));
            return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
        }
        LogUtil::registerStatus($this->__('The records have been published successfully'));
        return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
    }

    /**
     * Show the main configurable parameters needed to configurate the module IWusers
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @return:	The form with needed to change the parameters
     */
    public function config() {

        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        $friendsSystemAvailable = ModUtil::getVar('IWusers', 'friendsSystemAvailable');
        $invisibleGroupsInList = ModUtil::getVar('IWusers', 'invisibleGroupsInList');
        $usersCanManageName = ModUtil::getVar('IWusers', 'usersCanManageName');
        // Create output object
        $view = Zikula_View::getInstance('IWusers', false);
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
        $view->assign('friendsSystemAvailable', $friendsSystemAvailable);
        $view->assign('invisibleGroupsInList', $invisibleGroupsInList);
        $view->assign('usersCanManageName', $usersCanManageName);
        $view->assign('groupsArray', $groupsArray);
        return $view->fetch('IWusers_admin_config.htm');
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
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('IWusers', 'admin', 'main'));
        }
        $groupsString = '$';
        foreach ($groups as $group) {
            $groupsString .= '$' . $group . '$';
        }
        ModUtil::setVar('IWusers', 'friendsSystemAvailable', $friendsSystemAvailable);
        ModUtil::setVar('IWusers', 'invisibleGroupsInList', $groupsString);
        ModUtil::setVar('IWusers', 'usersCanManageName', $usersCanManageName);
        LogUtil::registerStatus($this->__('The configuration has changed'));
        return System::redirect(ModUtil::url('IWusers', 'admin', 'main'));
    }

}