<?php

class IWusers_Controller_User extends Zikula_Controller {

    /**
     * Show the list of user groups
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @return:	The list of users
     */
    public function main($args) {
        $all = FormUtil::getPassedValue('all', isset($args['all']) ? $args['all'] : null, 'GET');
        // Create output object
        $view = Zikula_View::getInstance('IWusers', false);
        if ($all == null) {
            // Security check
            if (!SecurityUtil::checkPermission('IWusers::', '::', ACCESS_READ)) {
                return LogUtil::registerPermissionError();
            }
        } else {
            // Security check
            if (!SecurityUtil::checkPermission('IWusers::', '::', ACCESS_COMMENT)) {
                return LogUtil::registerPermissionError();
            }
            $view->assign('all', true);
        }
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $userGroups = ModUtil::func('IWmain', 'user', 'getAllUserGroups',
                        array('sv' => $sv));
        // Gets the groups
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $allGroups = ModUtil::func('IWmain', 'user', 'getAllGroups',
                        array('sv' => $sv));
        foreach ($allGroups as $group) {
            $groupsNames[$group['id']] = $group['name'];
        }
        if ($all != null) {
            $userGroups = $allGroups;
        }
        $invisibleGroupsInList = ModUtil::getVar('IWusers', 'invisibleGroupsInList');
        foreach ($userGroups as $group) {
            if (strpos($invisibleGroupsInList, '$' . $group['id'] . '$') === false) {
                $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                $members = ModUtil::func('IWmain', 'user', 'getMembersGroup',
                                array('sv' => $sv,
                                    'gid' => $group['id']));
                $groups[] = array('gid' => $group['id'],
                    'members' => count($members),
                    'name' => $groupsNames[$group['id']]);
            }
        }
        $view->assign('groups', $groups);
        return $view->fetch('IWusers_user_main.htm');
    }

    /**
     * Show the list of members in a group
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	The group identity
     * @return:	The list of users
     */
    public function members($args) {

        $gid = FormUtil::getPassedValue('gid', isset($args['gid']) ? $args['gid'] : null, 'GET');
        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        //Check if user belongs to the group
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $isMember = ModUtil::func('IWmain', 'user', 'isMember',
                        array('sv' => $sv,
                            'gid' => $gid,
                            'uid' => UserUtil::getVar('uid')));
        // Security check
        if (!SecurityUtil::checkPermission('IWusers::', "::", ACCESS_COMMENT) && $isMember != 1 && $gid > 0) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        if ($gid > 0) {
            //get group members
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $members = ModUtil::func('IWmain', 'user', 'getMembersGroup',
                            array('sv' => $sv,
                                'gid' => $gid));
        } else {
            if (ModUtil::getVar('IWusers', 'friendsSystemAvailable') != 1) {
                LogUtil::registerError($this->__('Sorry! No authorization to access this module.'));
                return System::redirect(ModUtil::url('IWusers', 'user', 'main'));
            }
            $members = array();
            $membersFriends = ModUtil::apiFunc('IWusers', 'user', 'getAllFriends');
            if (count($membersFriends) > 0) {
                $usersList = '$$';
                foreach ($membersFriends as $friend) {
                    $usersList .= $friend['fuid'] . '$$';
                }
                // Get all users names
                $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                $usersNames = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                                array('info' => 'ncc',
                                    'sv' => $sv,
                                    'list' => $usersList));
                foreach ($membersFriends as $friend) {
                    $members[] = array('name' => $usersNames[$friend['fuid']],
                        'id' => $friend['fuid']);
                }
            }
        }
        asort($members);
        $usersList = '$$';
        foreach ($members as $member) {
            $usersList .= $member['id'] . '$$';
        }
        // Get all users info
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $usersNames = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'l',
                            'sv' => $sv,
                            'list' => $usersList));
        // Get groups information
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $groupsInfo = ModUtil::func('IWmain', 'user', 'getAllGroupsInfo',
                        array('sv' => $sv));
        $folder = ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'usersPictureFolder');
        foreach ($members as $member) {
            //get the user small photo
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $photo_s = ModUtil::func('IWmain', 'user', 'getUserPicture',
                            array('uname' => $usersNames[$member['id']] . '_s',
                                'sv' => $sv));
            //if the small photo not exists, check if the normal size foto exists. In this case create the small one
            if ($photo_s == '') {
                $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                $photo = ModUtil::func('IWmain', 'user', 'getUserPicture',
                                array('uname' => $usersNames[$member['id']],
                                    'sv' => $sv));
                if ($photo != '' && is_writable($folder)) {
                    //create the small photo and take it
                    $file_extension = strtolower(substr(strrchr($photo, "."), 1));
                    $e = ModUtil::func('IWmain', 'user', 'thumb',
                                    array('imgSource' => $folder . '/' . $usersNames[$member['id']] . '.' . $file_extension,
                                        'imgDest' => $folder . '/' . $usersNames[$member['id']] . '_s.' . $file_extension,
                                        'new_width' => 30));
                    $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                    $photo_s = ModUtil::func('IWmain', 'user', 'getUserPicture',
                                    array('uname' => $usersNames[$member['id']] . '_s',
                                        'sv' => $sv));
                }
            }
            // get user friends
            $friends = ModUtil::apiFunc('IWusers', 'user', 'getAllFriends');
            $isFriend = (array_key_exists($member['id'], $friends)) ? 1 : 0;
            $usersArray[] = array('name' => $member['name'],
                'photo' => $photo_s,
                'uname' => $usersNames[$member['id']],
                'isFriend' => $isFriend,
                'uid' => $member['id']);
        }
        // Create output object
        $view = Zikula_View::getInstance('IWusers', false);
        $view->assign('members', $usersArray);
        $view->assign('gid', $gid);
        $view->assign('groupName', $groupsInfo[$gid]);
        $view->assign('friendsSystemAvailable', ModUtil::getVar('IWusers', 'friendsSystemAvailable'));
        return $view->fetch('IWusers_user_members.htm');
    }

}