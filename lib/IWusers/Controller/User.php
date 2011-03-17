<?php
/**
 * Show the list of user groups
 * @author:     Albert PÃ©rez Monfort (aperezm@xtec.cat)
 * @return:	The list of users
*/
function iw_users_user_main($args)
{
	$all = FormUtil::getPassedValue('all', isset($args['all']) ? $args['all'] : null, 'GET');
	// Create output object
	$view = Zikula_View::getInstance('iw_users', false);
	if($all == null){
		// Security check
		if (!SecurityUtil::checkPermission( 'iw_users::', '::', ACCESS_READ)) {
			return LogUtil::registerPermissionError();
		}
	}else{
		// Security check
		if (!SecurityUtil::checkPermission( 'iw_users::', '::', ACCESS_COMMENT)) {
			return LogUtil::registerPermissionError();
		}
		$view->assign('all', true);
	}
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$userGroups = ModUtil::func('iw_main', 'user', 'getAllUserGroups',
	                         array('sv'=> $sv));
	// Gets the groups
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$allGroups = ModUtil::func('iw_main', 'user', 'getAllGroups',
	                        array('sv' => $sv));
	foreach($allGroups as $group){
		$groupsNames[$group['id']] = $group['name'];
	}
	if($all != null){
		$userGroups = $allGroups;
	}
	$invisibleGroupsInList = ModUtil::getVar('iw_users', 'invisibleGroupsInList');	
	foreach($userGroups as $group){
		if(strpos($invisibleGroupsInList,'$'.$group['id'].'$') === false){
			$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');	
			$members = ModUtil::func('iw_main', 'user', 'getMembersGroup',
			                      array('sv' => $sv,
										'gid' => $group['id']));
			$groups[] = array('gid' => $group['id'],
                              'members' => count($members),
                              'name' => $groupsNames[$group['id']]);
		}
	}
	$view->assign('groups', $groups);
	return $view->fetch('iw_users_user_main.htm');
}

/**
 * Show the module information
 * @author	Albert PÃ©rez Monfort (aperezm@xtec.cat)
 * @return	The module information
 */
function iw_users_user_module(){
	$dom=ZLanguage::getModuleDomain('iw_users');
	// Security check
	if (!SecurityUtil::checkPermission('iw_users::', "::", ACCESS_READ)) {
		return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
	}
	// Create output object
	$view = Zikula_View::getInstance('iw_users',false);
	$module = ModUtil::func('iw_main', 'user', 'module_info',
                         array('module_name' => 'iw_users',
                               'type' => 'user'));
	$view->assign('module', $module);
	return $view->fetch('iw_users_user_module.htm');
}

/**
 * Show the list of members in a group
 * @author:     Albert PÃ©rez Monfort (aperezm@xtec.cat)
 * @param:	The group identity
 * @return:	The list of users
*/
function iw_users_user_members($args)
{
	$dom=ZLanguage::getModuleDomain('iw_users');
	$gid = FormUtil::getPassedValue('gid', isset($args['gid']) ? $args['gid'] : null, 'GET');
	// Security check
	if (!SecurityUtil::checkPermission('iw_users::', "::", ACCESS_READ)) {
		return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
	}
	//Check if user belongs to the group
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
    $isMember = ModUtil::func('iw_main', 'user', 'isMember',
                           array('sv' => $sv,
                                 'gid' => $gid,
                                 'uid' => UserUtil::getVar('uid')));
	// Security check
	if (!SecurityUtil::checkPermission('iw_users::', "::", ACCESS_COMMENT) && $isMember != 1 && $gid > 0) {
		return LogUtil::registerError(__('Sorry! No authorization to access this module.', $dom), 403);
	}
	if($gid > 0){
		//get group members
        $sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
        $members = ModUtil::func('iw_main', 'user', 'getMembersGroup',
                              array('sv' => $sv,
                                    'gid' => $gid));
	}else{
		if(ModUtil::getVar('iw_users', 'friendsSystemAvailable') != 1){
			LogUtil::registerError (__('Sorry! No authorization to access this module.', $dom));
			return System::redirect(ModUtil::url('iw_users', 'user', 'main'));
		}
		$members = array();
		$membersFriends = ModUtil::apiFunc('iw_users', 'user', 'getAllFriends');
		if(count($membersFriends) > 0){
			$usersList = '$$';
			foreach($membersFriends as $friend){
				$usersList .= $friend['fuid'] . '$$';
			}
			// Get all users names
            $sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
            $usersNames = ModUtil::func('iw_main', 'user', 'getAllUsersInfo',
                                     array('info' => 'ncc',
                                           'sv' => $sv,
                                           'list' => $usersList));
			foreach($membersFriends as $friend){
                $members[] = array('name' =>$usersNames[$friend['fuid']],
                                   'id' => $friend['fuid']);
			}
		}
	}
	asort($members);
	$usersList = '$$';
	foreach($members as $member){
		$usersList .= $member['id'].'$$';
	}
	// Get all users info
    $sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
    $usersNames = ModUtil::func('iw_main', 'user', 'getAllUsersInfo',
                             array('info' => 'l',
                                   'sv' => $sv,
                                   'list' => $usersList));
    // Get groups information
    $sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
    $groupsInfo = ModUtil::func('iw_main', 'user', 'getAllGroupsInfo',
                             array('sv' => $sv));
	$folder = ModUtil::getVar('iw_main', 'documentRoot').'/'.ModUtil::getVar('iw_main', 'usersPictureFolder');
	foreach($members as $member){
		//get the user small photo
        $sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
        $photo_s = ModUtil::func('iw_main', 'user', 'getUserPicture',
                              array('uname' => $usersNames[$member['id']] . '_s',
                                    'sv' => $sv));
		//if the small photo not exists, check if the normal size foto exists. In this case create the small one
		if($photo_s == ''){
            $sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
            $photo = ModUtil::func('iw_main', 'user', 'getUserPicture',
                                array('uname' => $usersNames[$member['id']],
                                      'sv' => $sv));
			if($photo != '' && is_writable($folder)){
				//create the small photo and take it
                $file_extension = strtolower(substr(strrchr($photo,"."),1));
                $e = ModUtil::func('iw_main', 'user', 'thumb',
                                array('imgSource' => $folder . '/' . $usersNames[$member['id']] . '.' . $file_extension,
                                      'imgDest' => $folder . '/' . $usersNames[$member['id']] . '_s.' . $file_extension,
                                      'new_width' => 30));
                $sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
                $photo_s = ModUtil::func('iw_main', 'user', 'getUserPicture',
                                      array('uname' => $usersNames[$member['id']] . '_s',
                                            'sv' => $sv));
			}
		}
		// get user friends
		$friends = ModUtil::apiFunc('iw_users', 'user', 'getAllFriends');
		$isFriend = (array_key_exists($member['id'], $friends)) ? 1 : 0;
        $usersArray[] = array('name' => $member['name'],
                              'photo' => $photo_s,
                              'uname' => $usersNames[$member['id']],
                              'isFriend' => $isFriend,
                              'uid' => $member['id']);
	}
	// Create output object
	$view = Zikula_View::getInstance('iw_users', false);
	$view->assign('members', $usersArray);
	$view->assign('gid', $gid);
	$view->assign('groupName', $groupsInfo[$gid]);
	$view->assign('friendsSystemAvailable', ModUtil::getVar('iw_users', 'friendsSystemAvailable'));
	return $view->fetch('iw_users_user_members.htm');
}