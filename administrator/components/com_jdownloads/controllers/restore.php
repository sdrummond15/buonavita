<?php
/*
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 *
 * @component jDownloads
 * @version 2.0  
 * @copyright (C) 2007 - 2011 - Arno Betz - www.jdownloads.com
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
 * jDownloads Restore Controller
 *
 */
class jdownloadsControllerrestore extends jdownloadsController
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
	 * logic to restore the backup file
	 *
	 */
	public function runrestore()
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        $files_uploaddir = $params->get('files_uploaddir');
        $tempdir         = $params->get('tempzipfiles_folder_name');
        
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Access check.
        if (!JFactory::getUser()->authorise('core.admin','com_jdownloads')){            
            JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
            $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=tools', true));
            
        } else {       
        
            jimport('joomla.filesystem.file');
            
            $model_category = JModelLegacy::getInstance( 'Category', 'jdownloadsModel' );
            $model_download = JModelLegacy::getInstance( 'Download', 'jdownloadsModel' );        
        
            $db = JFactory::getDBO();
            $user = JFactory::getUser();
            
            ini_set('max_execution_time', '600');
            ignore_user_abort(true);
            flush(); 
            
            $target_prefix = JDownloadshelper::getCorrectDBPrefix();
            
            $app = JFactory::getApplication();
            
            $original_upload_dir = $files_uploaddir;
            
            $output = '';
            $log = '';

            // get restore file
            $file = ArrayHelper::getValue($_FILES,'restore_file',array('tmp_name'=>''));
            
            // save it in upload root
            $upload_path = $files_uploaddir.'/'.$file['name'];
            // since Joomla 3.4 we need additional params to allow unsafe file (backup file contains php content)
            if (!JFile::upload($file['tmp_name'], $upload_path, false, true)){
                $app->redirect(JRoute::_('index.php?option=com_jdownloads'),  JText::_('COM_JDOWNLOADS_RESTORE_MSG_STORE_ERROR'), 'error');
            }
            
            if($file['tmp_name']!= ''){

                // write values in db tables
                require_once($upload_path);
                

                // we must restore the original stored upload root dir in params
                $result = JDownloadsHelper::changeParamSetting('files_uploaddir', $original_upload_dir);
                
                // create for every category a data set in the assets table
                // get at first all items
                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('#__jdownloads_categories');
                $query->order('lft ASC');
                $db->setQuery($query);
                $cats = $db->loadObjectList();
                // we need an array
                $cats = json_decode(json_encode($cats), true);          
                // sum of total categories (but compute not the root)
                $cats_sum = count($cats) - 1;
                
                $sum_updated_cats = 0;
                
                if ($cats_sum){
                    
                    foreach ($cats as $cat){
                        
                        if ($cat['id'] > 1){
                            // add the new rules array
                            $cat['rules'] = array(
                                    'core.create' => array(),
                                    'core.delete' => array(),
                                    'core.edit' => array(),
                                    'core.edit.state' => array(),
                                    'core.edit.own' => array(),
                                    'download' => array(),
                            );
                            // save now the category with the new rules
                            $update_result = $model_category->save( $cat, true );
                            if (!$update_result){
                                // error message
                                $log .= 'Category Results: Can not create new asset rules for category ID '.$cat['id'].'<br />';
                            } else {
                                $sum_updated_cats ++;                        
                            }              

                        }
                    }
                    $log .= "New data sets created in 'assets' db table for categories: ".$sum_updated_cats.'<br />';
                }                
                
                // create for every Download a data set in the assets table (added in 3.2.22)
                // get at first all items
                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('#__jdownloads_files');
                $query->order('id ASC');
                $db->setQuery($query);
                $files = $db->loadObjectList();
                // we need an array
                $files = json_decode(json_encode($files), true);          
                // sum of total Downloads
                $files_sum = count($files);
                
                $sum_updated_files = 0;
                
                if ($files_sum){
                    
                    foreach ($files as $file){
                        
                            // add the new rules array
                            $file['rules'] = array(
                                    'core.create' => array(),
                                    'core.delete' => array(),
                                    'core.edit' => array(),
                                    'core.edit.state' => array(),
                                    'core.edit.own' => array(),
                                    'download' => array(),
                            );
                            // save now the download with the new rules
                            $update_result = $model_download->save( $file, true, false, true );
                            if (!$update_result){
                                // error message
                                $log .= 'Downloads Results: Can not create new asset rules for download ID '.$file['id'].'<br />';
                            } else {
                                $sum_updated_files ++;                        
                            }              
                    }
                    $log .= "New data sets created in 'assets' db table for downloads: ".$sum_updated_files.'<br />';
                }                
                
                $sum = '<font color="green"><b>'.sprintf(JText::_('COM_JDOWNLOADS_RESTORE_MSG'),(int)$i).'</b></font>';
                
                if ($log){
                    $output = $db->escape($sum.'<br />'.$output.'<br />'.JText::_('COM_JDOWNLOADS_AFTER_RESTORE_TITLE_3').'<br />'.$log.'<br />'.JText::_('COM_JDOWNLOADS_CHECK_FINISH').'');
                } else {   
                    $output = $db->escape($sum.'<br />'.$output.'<br />'.JText::_('COM_JDOWNLOADS_CHECK_FINISH').'');
                }    
                
                // Write protocol to file 
                $x = file_put_contents(JPATH_COMPONENT_ADMINISTRATOR.'/restore_logs.txt', $output, FILE_APPEND | LOCK_EX);
                
                // delete the backup file in temp folder
                JFile::delete($upload_path);
            }
            $this->setRedirect( 'index.php?option=com_jdownloads',  $sum.' '.JText::_('COM_JDOWNLOADS_RESTORE_MSG_2') );
        }    
    }    
	
}
?>