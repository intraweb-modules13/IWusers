<?php
function iw_users_ajax_addContact($args)
{
	$dom=ZLanguage::getModuleDomain('iw_users');
	if (!SecurityUtil::checkPermission('iw_users::', '::', ACCESS_READ)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$gid = FormUtil::getPassedValue('gid', -2, 'GET');
	if ($gid == -2) {
		AjaxUtil::error('no group id');
	}
	$fuid = FormUtil::getPassedValue('fuid', -1, 'GET');
	if ($fuid == -1) {
		AjaxUtil::error('no user id');
	}
	$action = FormUtil::getPassedValue('action', -1, 'GET');
	if ($action == -1) {
		AjaxUtil::error('no action defined');
	}
	$view = Zikula_View::getInstance('iw_users',false);
	if($action == 'add'){
		if(!ModUtil::apiFunc('iw_users', 'user', 'addContant', array('fuid' => $fuid))){
			AjaxUtil::error('error');
		}
		$view->assign ('add',true);
	}
	if($action == 'delete'){
		if(!ModUtil::apiFunc('iw_users', 'user', 'deleteContant', array('fuid' => $fuid))){
			AjaxUtil::error('error');
		}
		$view->assign ('add',false);
	}
	$view->assign ('fuid',$fuid);
	$view->assign ('gid',$gid);
	$vars = UserUtil::getVars($fuid);
	$view->assign ('uname',$vars['uname']);
	$content = $content = $view->fetch('iw_users_user_members_optionsContent.htm');
	AjaxUtil::output(array('fuid' => $fuid,
							'content' => $content,
							'gid' => $gid));
}

function iw_users_ajax_delUserGroup($args)
{
	$dom=ZLanguage::getModuleDomain('iw_users');
	if (!SecurityUtil::checkPermission('iw_users::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$uid = FormUtil::getPassedValue('uid', -1, 'GET');
	if ($uid == -1) {
		AjaxUtil::error('no user id');
	}
	$gid = FormUtil::getPassedValue('gid', -1, 'GET');
	if ($gid == -1) {
		AjaxUtil::error('no group id');
	}
	if(!ModUtil::apiFunc('groups', 'admin', 'removeuser', array('uid' => $uid,
															'gid' => $gid))){
		AjaxUtil::error('error deleting group');										
	}
	AjaxUtil::output(array('uid' => $uid,
							'gid' => $gid));
}

function iw_users_ajax_addUserGroup($args)
{
	$dom=ZLanguage::getModuleDomain('iw_users');
	if (!SecurityUtil::checkPermission('iw_users::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$uid = FormUtil::getPassedValue('uid', -1, 'GET');
	if ($uid == -1) {
		AjaxUtil::error('no user id');
	}
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$allGroups = ModUtil::func('iw_main', 'user', 'getAllGroups', array('sv' => $sv));
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$userGroups = ModUtil::func('iw_main', 'user', 'getAllUserGroups', array('sv' => $sv,
																			'uid' => $uid));
	$usersGroupsArray = array();
	foreach($allGroups as $group){
		if(!array_key_exists($group['id']  , $userGroups)){
		$userGroupsArray[] = array('id' => $group['id'],
									'name' => $group['name']);
		}
	}
	// Create output object
	$view = Zikula_View::getInstance('iw_users',false);
	$view->assign('groups', $userGroupsArray);
	$view->assign('uid', $uid);
	$view->assign('list', true);
	$content = $view->fetch('iw_users_admin_addGroupForm.htm');
	AjaxUtil::output(array('uid' => $uid,
							'content' => $content));
}

function iw_users_ajax_addGroupProceed($args)
{
	$dom=ZLanguage::getModuleDomain('iw_users');
	if (!SecurityUtil::checkPermission('iw_users::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$uid = FormUtil::getPassedValue('uid', -1, 'GET');
	if ($uid == -1) {
		AjaxUtil::error('no user id');
	}
	$gid = FormUtil::getPassedValue('gid', -1, 'GET');
	if ($gid == -1) {
		AjaxUtil::error('no group id');
	}
	if(!ModUtil::apiFunc('groups', 'admin', 'adduser', array('uid' => $uid,
															'gid' => $gid))){
		AjaxUtil::error('error adding group');										
	}
	// Get all the groups information
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$groupsInfo = ModUtil::func('iw_main','user','getAllGroupsInfo', array('sv' => $sv));
	$sv = ModUtil::func('iw_main', 'user', 'genSecurityValue');
	$groups = ModUtil::func('iw_main', 'user', 'getAllUserGroups', array('sv' => $sv,
																		'uid' => $uid));															
	$userGroups = array();
	foreach($groups as $group){
		if($group['id']){
			array_push($userGroups, array('id' => $group['id'],
											'name' => $groupsInfo[$group['id']]));
		}
	}
	$view = Zikula_View::getInstance('iw_users',false);
	$view->assign('user', array('groups' => $userGroups,
										'uid' => $uid));
	$content = $view->fetch('iw_users_admin_userGroupsList.htm');
	$content1 = $view->fetch('iw_users_admin_addGroupForm.htm');
	AjaxUtil::output(array('uid' => $uid,
							'content' => $content,
							'content1' => $content1));
}