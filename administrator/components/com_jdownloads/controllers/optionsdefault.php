<?php
/**
 * @package jDownloads
 * @version 3.9  
 * @copyright (C) 2007 - 2018 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.controller');

/**
 * jDownloads Options Default Controller
 *
 */
class jdownloadsControllerOptionsDefault extends jdownloadsController
{
	/**
	 * Constructor
	 *
	 */
	    public function __construct($config = array())
    {
        parent::__construct($config);
	}

	/**
	 * Set back the configuration settings to the state after installation
	 *
	 */
	public function runDefault()
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        $files_uploaddir = $params->get('files_uploaddir');
        $root_dir        = $params->get('root_dir');
        $tempdir         = $params->get('tempzipfiles_folder_name');
        $preview_dir     = $params->get('preview_files_folder_name');   
        
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Access check.
        if (!JFactory::getUser()->authorise('core.admin','com_jdownloads')){            
            JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
            $this->setRedirect(JRoute::_('index.php?option=com_jdownloads', true));
            
        } else {       
        
            jimport('joomla.filesystem.file');
            
            $db = JFactory::getDBO();
            $user = JFactory::getUser();
            $app = JFactory::getApplication();
            
            ini_set('max_execution_time', '600');
            ignore_user_abort(true);
            flush(); 
            
            // get the default file
            if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_jdownloads/default_params.txt')){
                $def_params = file_get_contents(JPATH_ADMINISTRATOR.'/components/com_jdownloads/default_params.txt');

                $db->setQuery("UPDATE #__extensions SET params = '".$def_params."' WHERE `type` = 'component' AND `element` = 'com_jdownloads' AND `enabled`= '1'");
                if ($db->execute()){
                    // we must store again the original pathes in params
                    $result = JDownloadsHelper::changeParamSetting('files_uploaddir', $files_uploaddir);
                    $result = JDownloadsHelper::changeParamSetting('root_dir', $root_dir);
                    $result = JDownloadsHelper::changeParamSetting('tempzipfiles_folder_name', $tempdir);
                    $result = JDownloadsHelper::changeParamSetting('preview_files_folder_name', $preview_dir);
           
                    $this->setRedirect( JRoute::_('index.php?option=com_jdownloads'), JText::_('COM_JDOWNLOADS_OPTIONS_DEFAULT_DONE') );    
                } else {
                    // We could not save the new data
                    $this->setRedirect( JRoute::_('index.php?option=com_jdownloads'), JText::_('Aborted! Could not save the default options data!'), 'error');
                }

            } else {            
                $this->setRedirect(JRoute::_('index.php?option=com_jdownloads'),  JText::_('Aborted! Default options file not found!'), 'error');
            }
        } 
    }   
}
?>