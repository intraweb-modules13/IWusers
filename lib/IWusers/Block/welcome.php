<?php
// $Id: users
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html

/**
 * IWusers module
 * 
 * The IWusers improve the users managment 
 *
 * Purpose of file:  Create a block to welcome users during connexion
 * 
 * @package      Intraweb_Modules
 * @subpackage   IWusers
 * @version      $Id: users.php
 * @author       Albert Pérez Monfort
 * @link         http://phobos.xtec.cat/intraweb  The Intraweb Project Home Page
 * @copyright    Copyright (C) 2009 by the Intraweb Project Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */ 

function IWusers_welcomeblock_init()
{
    SecurityUtil::registerPermissionSchema("IWusers:welcomeblock:", "Block title::");
}

function IWusers_welcomeblock_info()
{
	
    return array('text_type' => 'Welcome',
					'func_edit' => 'welcome_edit',
					'func_update' => 'welcome_update',
					'module' => 'IWusers',
					'text_type_long' => $this->__('Show a welcome message wend user is in home page'),
					'allow_multiple' => true,
					'form_content' => false,
					'form_refresh' => false,
					'show_preview' => true );
}

/**
 * Show the month calendar into a bloc
 * @autor:	Albert Pérez Monfort
 * @autor:	Toni Ginard Lladó
 * param:	The month and the year to show
 * return:	The calendar content
*/
function IWusers_welcomeblock_display($blockinfo)
{
	
	// Security check
	if (!SecurityUtil::checkPermission*(0, "IWusers:welcomeblock:", $blockinfo['title']."::", ACCESS_READ)) { 
		return; 
	} 
	$baseURL = System::getBaseUrl();
	$baseURL .= 'index.php';
	if('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] != $baseURL){
		return;
	}
	// Check if the module is available
	if(!ModUtil::available('IWusers')){
		return;
	}
	$user = (UserUtil::isLoggedIn()) ? UserUtil::getVar('uid') : '-1';
	// Only for loggedin users
	if($user == '-1'){
		return;
	}
	$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
	$userName = ModUtil::func('IWmain', 'user', 'getUserInfo', array('sv' => $sv,
																	'uid' => $user,
																	'info' => 'n'));									
	$values = explode('---', $blockinfo['url']);
	$hello = (!empty($values[0])) ? $values[0] : $this->__('Hi');
	$welcome = (!empty($values[0])) ? $values[1] : $this->__('welcome to the intranet');
	$date = $values[2];
	// Pass the data to the template
	$view = Zikula_View::getInstance('IWusers',false);
	$view -> assign('userName', $userName);
	$view -> assign('hello', $hello);
	$view -> assign('welcome', $welcome);
	$view -> assign('date', $date);
	$view -> assign('dateText', date('d/m/Y', time()));
	$view -> assign('timeText', date('H.i', time()));
	$s = $view -> fetch('IWusers_block_welcome.htm');
	// Populate block info and pass to theme
	$blockinfo['content'] = $s;
	return BlockUtil::themesideblock($blockinfo);
}

function welcome_update($blockinfo)
{
	// Security check
	if (!SecurityUtil::checkPermission*(0, "IWusers:welcomeblock:", $blockinfo['title']."::", ACCESS_ADMIN)) { 
		return; 
	}
	$url = $blockinfo['hello'] . '---' . $blockinfo['welcome'] . '---' . $blockinfo['date'];
	$blockinfo['url'] = "$url";
	return $blockinfo;
}

function welcome_edit($blockinfo)
{
	
	// Security check
	if (!SecurityUtil::checkPermission*(0, "IWusers:welcomeblock:", $blockinfo['title']."::", ACCESS_ADMIN)) {
		return; 
	}
	$values = explode('---', $blockinfo['url']);
	$hello = (!empty($values[0])) ? $values[0] : $this->__('Hi');
	$welcome = (!empty($values[1])) ? $values[1] : $this->__('welcome to the intranet');
	$date = $values[2];
	$checked = ($date == 1) ? 'checked' : '';
	$sortida = '<tr><td valign="top">' . $this->__('Geeting',$dom) . '</td><td>'."<input type=\"text\" name=\"hello\" size=\"50\" maxlength=\"50\" value=\"$hello\" />"."</td></tr>\n";
	$sortida .= '<tr><td valign="top">' . $this->__('Welcome text',$dom) . '</td><td>'."<input type=\"text\" name=\"welcome\" size=\"50\" maxlength=\"50\" value=\"$welcome\" />"."</td></tr>\n";
	$sortida .= '<tr><td valign="top">' . $this->__('Include date and time',$dom) . '</td><td>'."<input type=\"checkbox\" name=\"date\"  value=\"1\" $checked />"."</td></tr>\n";
	return $sortida;
}