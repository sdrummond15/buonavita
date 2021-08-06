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
 * jDownloads options import Controller
 *
 */
class jdownloadsControllerOptionsImport extends jdownloadsController
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
	 * logic to import the options from a file
	 *
	 */
	public function runImport()
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
            
            ini_set('max_execution_time', '600');
            ignore_user_abort(true);
            flush(); 
            
            $output = '';
            $log = '';

            // Get the import file informations
            $file = ArrayHelper::getValue($_FILES, 'options_import_file', array('tmp_name'=>''));
            
            if ($file['tmp_name'] != ''){
                
                // Check file extension and type
                if (strtolower(JFile::getExt($file['name'])) == 'txt' && $file['type'] == 'text/plain'){
                
                    // Store it in jD temp folder
                    $upload_path = $files_uploaddir.'/'.$tempdir.'/'.$file['name'];
                    
                    // since Joomla 3.4 we need additional params to allow unsafe file (backup file contains php content)
                    if (!JFile::upload($file['tmp_name'], $upload_path, false, true)){
                         $this->setRedirect( JRoute::_('index.php?option=com_jdownloads'), JText::_('COM_JDOWNLOADS_RESTORE_MSG_STORE_ERROR'), 'error');
                    }
                
                    // Write new params db table
                    $new_params = file_get_contents($files_uploaddir.'/'.$tempdir.'/'.$file['name']);
                    
                    // We must check the file content first
                    $check_params = json_decode($new_params);
                    
                    if (isset($check_params->files_uploaddir)){
                    
                    
                        $db->setQuery("UPDATE #__extensions SET params = '".$new_params."' WHERE `type` = 'component' AND `element` = 'com_jdownloads' AND `enabled`= '1'");
                        if ($db->execute()){
                            // we must store again the original pathes in params
                            $result = JDownloadsHelper::changeParamSetting('files_uploaddir', $files_uploaddir);
                            $result = JDownloadsHelper::changeParamSetting('root_dir', $root_dir);
                            $result = JDownloadsHelper::changeParamSetting('tempzipfiles_folder_name', $tempdir);
                            $result = JDownloadsHelper::changeParamSetting('preview_files_folder_name', $preview_dir);

                            // Delete the file in temp folder
                            JFile::delete($upload_path);
                        
                            $this->setRedirect( JRoute::_('index.php?option=com_jdownloads'), JText::_('COM_JDOWNLOADS_OPTIONS_IMPORT_DONE') );    
                            
                        } else {
                            // We could not save the new data
                            // Delete the file in temp folder
                            JFile::delete($upload_path);
                            $this->setRedirect( JRoute::_('index.php?option=com_jdownloads'), JText::_('Aborted! Could not save the imported options data!'), 'error');
                        }
                    } else {
                        // Seems to be a file with wrong content?
                        // Delete the file in temp folder
                        JFile::delete($upload_path);
                        $this->setRedirect( JRoute::_('index.php?option=com_jdownloads'),  JText::_('Aborted! The uploaded file seems not to have the correct content! Please choice the right file!'), 'error');
                    }
                
                } else {
                    // Invalid file?
                    $this->setRedirect( JRoute::_('index.php?option=com_jdownloads'),  JText::_('Aborted! The selected file seems not to be correct!'), 'error');
                }
            }
        } 
    }   
}
?>