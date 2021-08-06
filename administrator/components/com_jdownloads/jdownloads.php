<?php
/**
 * @package jDownloads
 * @version 4.0  
 * @copyright (C) 2007 - 2016 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
 
defined('_JEXEC') or die('Restricted access');

if (!defined('DS')){
   define('DS',DIRECTORY_SEPARATOR);
} 

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_jdownloads')) {
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';
require_once JPATH_COMPONENT.DS.'helpers'.DS.'jdownloads.php';

jimport('joomla.application.component.controller');

// Get an instance of the controller
$controller = JControllerLegacy::getInstance('jdownloads');
 
// Perform the Request task
$jinput = JFactory::getApplication()->input;
$controller->execute($jinput->get('task'));
 
// Redirect if set by the controller
$controller->redirect();

if (!$jinput->getString('layout') == 'modal' && !$jinput->getString('layout') == 'modallist'){
    $footer = JDownloadsHelper::buildBackendFooterText('center');
} else {
    $footer = '';
}
echo $footer;
                   

?>