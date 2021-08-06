<?php
/**
* @version $Id: mod_jdownloads_admin_monitoring.php v3.8
* @package mod_jdownloads_admin_monitoring
* @copyright (C) 2018 Arno Betz
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author Arno Betz http://www.jDownloads.com
* 
* jDownloads admin stats module for use in the jDownloads Control Panel to scan the downloads folder.
* 
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

    require_once (JPATH_ADMINISTRATOR.'/components/com_jdownloads/helpers/jdownloads.php');

    $user   = JFactory::getUser();

	if (!$user->authorise('core.manage', 'com_jdownloads')){
		return;
	}
    
	$language = JFactory::getLanguage();
	$language->load('mod_jdownloads_admin_monitoring.ini', JPATH_ADMINISTRATOR);

    $params = JComponentHelper::getParams('com_jdownloads');
    
    require JModuleHelper::getLayoutPath('mod_jdownloads_admin_monitoring', $params->get('layout', 'default'));
