<?php
function smarty_function_iwusersadminmenulinks($params, &$smarty)
{
	
	$inici = FormUtil::getPassedValue('inici', isset($args['inici']) ? $args['inici'] : null, 'POST');
	$filtre = FormUtil::getPassedValue('filtre', isset($args['filtre']) ? $args['attached'] : 0, 'POST');
	$campfiltre = FormUtil::getPassedValue('campfiltre', isset($args['campfiltre']) ? $args['campfiltre'] : 1, 'POST');
	$numitems = FormUtil::getPassedValue('numitems', isset($args['numitems']) ? $args['numitems'] : 20, 'POST');

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

	$usersadminmenulinks = "<span class=\"" . $params['class'] . "\">" . $params['start'] . " ";

	if (SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
		$usersadminmenulinks .= "<a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWusers', 'admin', 'new')) . "\">" . __('Create user',$dom) . "</a> " . $params['seperator'];
	}

	if (SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
		$usersadminmenulinks .= " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWusers', 'admin', 'main')) . "\">" . __('Show the list of users',$dom) . "</a> " . $params['seperator'];
	}

	if (SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
		$usersadminmenulinks .= " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWusers', 'admin', 'import')) . "\">" . __('Import from a file',$dom) . "</a> " . $params['seperator'];
	}

	if (SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
		$usersadminmenulinks .= " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWusers', 'admin', 'export')) . "\">" . __('Export to CSV',$dom) . "</a> " . $params['seperator'];
	}

	if (SecurityUtil::checkPermission('IWusers::', "::", ACCESS_ADMIN)) {
		$usersadminmenulinks .= " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWusers', 'admin', 'config')) . "\">" . __('Configure the module',$dom) . "</a> ";
	}
	
	$usersadminmenulinks .= $params['end'] . "</span>\n";

	return $usersadminmenulinks;
}
