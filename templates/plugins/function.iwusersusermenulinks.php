<?php
function smarty_function_iwusersusermenulinks($params, &$smarty)
{
	
	// set some defaults
	if (!isset($params['start'])) {
		$params['start'] = '[';
	}
	if (!isset($params['end'])) {
		$params['end'] = ']';
	}
	if (!isset($params['seperator'])) {
		$params['seperator'] = '|';
	}
	if (!isset($params['class'])) {
		$params['class'] = 'pn-menuitem-title';
	}
	$usersusermenulinks = "<span class=\"" . $params['class'] . "\">" . $params['start'] . " ";
	if (SecurityUtil::checkPermission('IWusers::', "::", ACCESS_READ)) {
		$usersusermenulinks .= "<a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWusers', 'user', 'main')) . "\">" . __('Shows the groups I belong',$dom) . "</a> ";
	}
	if (SecurityUtil::checkPermission('IWusers::', "::", ACCESS_COMMENT)) {
		$usersusermenulinks .= $params['seperator'] . " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWusers', 'user', 'main', array('all' => 1))) . "\">" . __('Show all the groups',$dom) . "</a> ";
	}
	if (SecurityUtil::checkPermission('IWusers::', "::", ACCESS_READ) && ModUtil::getVar('IWusers', 'friendsSystemAvailable') == 1) {
		$usersusermenulinks .= $params['seperator'] . " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWusers', 'user', 'members', array('gid' => -1))) . "\">" . __('Show contacts\' list',$dom) . "</a> ";
	}
	$usersusermenulinks .= $params['end'] . "</span>\n";
	return $usersusermenulinks;
}