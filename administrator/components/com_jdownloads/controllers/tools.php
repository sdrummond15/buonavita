<?php
/**
 * @package jDownloads
 * @version 3.7  
 * @copyright (C) 2007 - 2017 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * jDownloads Restore Controller
 *
 */
class jdownloadsControllertools extends jdownloadsController
{
	function __construct() {
        parent::__construct();
        
        // Register Extra task
        $this->registerTask( 'resetDownloadCounter',   'resetDownloadCounter' );        
        $this->registerTask( 'resetBatchSwitch',       'resetBatchSwitch' );        
        $this->registerTask( 'resetCom',               'resetCom' );
        $this->registerTask( 'cleanImageFolders',      'cleanImageFolders' ); 
        $this->registerTask( 'deleteBackupTables',     'deleteBackupTables' );
        $this->registerTask( 'resetCategoriesRules',   'resetCategoriesRules' );
        $this->registerTask( 'resetDownloadsRules',    'resetDownloadsRules' );
    }
    
    
    /**
     * Reset all download counters to zero
     */
    public function resetDownloadCounter()
    {        
        // check user access right
        if (JFactory::getUser()->authorise('core.admin','com_jdownloads'))
        {        
         
             $db = JFactory::getDBO();
             $query = $db->getQuery(true);
             $query->update($db->quoteName('#__jdownloads_files'));
             $query->set('downloads = \'0\'');
             $db->setQuery($query);
             try {
                  $result = $db->execute();
             } catch (Exception $e) {
                      $this->setError($e->getMessage());
                      $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=tools', false));
             }            
            
             JFactory::getApplication()->enqueueMessage(JText::_('COM_JDOWNLOADS_TOOLS_RESET_RESULT_OKAY_MSG'));
        }
        $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=tools', false)); 
    }

    /**
     * reset all categories permissions settings to 'inherited'
     */
    public function resetCategoriesRules()
    {        
        // check user access right
        if (JFactory::getUser()->authorise('core.admin','com_jdownloads'))
        {        
            $result = array();
            
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__assets');
            $query->where('name LIKE '.$db->Quote('%jdownloads.category%'));
            //$query->where('rules NOT LIKE '.$db->Quote('%download":[]%'));
            $db->setQuery($query);
            $result = $db->loadColumn();            
            $count = count($result);
            
            if ($result){
                 $ids = implode(',', $result);
            
                 $db = JFactory::getDBO();
                 $query = $db->getQuery(true);
                 $query->update($db->quoteName('#__assets'));
                 $query->set('rules = '.$db->Quote('{"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1},"download":[]}'));
                 
                 $query->where('id IN ('.$ids.')');
                 $db->setQuery($query);
                 try {
                      $result = $db->execute();
                 } catch (Exception $e) {
                          $this->setError($e->getMessage());
                          $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=tools', false));
                 }            
            }    
            JFactory::getApplication()->enqueueMessage(sprintf(JText::_('COM_JDOWNLOADS_TOOLS_RESET_RESULTS_MSG'),(int)$count));
             
        }
        $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=tools', false)); 
    }

    /**
     * reset all downloads permissions settings to 'inherited'
     */
    public function resetDownloadsRules()
    {        
        // check user access right
        if (JFactory::getUser()->authorise('core.admin','com_jdownloads'))
        {        

            $result = array();
            
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__assets');
            $query->where('name LIKE '.$db->Quote('%jdownloads.download%'));
            //$query->where('rules NOT LIKE '.$db->Quote('%download":[]%'));
            $db->setQuery($query);
            $result = $db->loadColumn();
            $count = count($result);            
            
            if ($result){
                 $ids = implode(',', $result);
            
                 $db = JFactory::getDBO();
                 $query = $db->getQuery(true);
                 $query->update($db->quoteName('#__assets'));
                 $query->set('rules = '.$db->Quote('{"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1},"download":[]}'));
                 $query->where('id IN ('.$ids.')');
                 $db->setQuery($query);
                 try {
                      $result = $db->execute();
                 } catch (Exception $e) {
                          $this->setError($e->getMessage());
                          $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=tools', false));
                 }            
            }    
            JFactory::getApplication()->enqueueMessage(sprintf(JText::_('COM_JDOWNLOADS_TOOLS_RESET_RESULTS_MSG'),(int)$count));

        } 
        $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=tools', false)); 
    }

    /**
     * 
     */
    public function resetCom()
    {        
        // check user access right
        if (JFactory::getUser()->authorise('core.admin','com_jdownloads'))
        {                
            $result = JDownloadsHelper::changeParamSetting('com', '');
            if ($result){
               JFactory::getApplication()->enqueueMessage(JText::_('COM_JDOWNLOADS_TOOLS_RESET_RESULT_OKAY_MSG')); 
            } 
        }    
        $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=tools', false)); 
        
    }
    
    /**
     * 
     */
    public function resetBatchSwitch()
    {        
        // check user access right
        if (JFactory::getUser()->authorise('core.admin','com_jdownloads'))
        {                
            $result  = JDownloadsHelper::changeParamSetting('categories_batch_in_progress', '0');
            $result2 = JDownloadsHelper::changeParamSetting('downloads_batch_in_progress', '0');
            
            JFactory::getApplication()->enqueueMessage(JText::_('COM_JDOWNLOADS_TOOLS_RESET_RESULT_OKAY_MSG'));
        }
        $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=tools', false)); 
    }
    
    /**
     * Clean the image folders 'screenshot' and 'thumbnails' and delete all not used images
     */
    public function cleanImageFolders()
    {        
        
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );

        // check user access right
        if (JFactory::getUser()->authorise('core.admin','com_jdownloads'))
        {        
            $pics_folder   = JPATH_SITE.'/images/jdownloads/screenshots/';
            $thumbs_folder = JPATH_SITE.'/images/jdownloads/screenshots/thumbnails/';
            
            $used_image_list    = array();
            $images             = array();
            $del_result         = false;
            $sum                = 0;
            
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('images');
            $query->from('#__jdownloads_files');
            $query->where('images != '.$db->Quote(''));
            $db->setQuery($query);
            $result = $db->loadObjectList();    
            
            // create a array with all used images 
            for ($i=0; $i < count($result); $i++){
                 $images = explode('|', $result[$i]->images);
                 foreach ($images as $image){
                    if (!in_array($image, $used_image_list)){
                        $used_image_list[] = $image;
                    }
                    
                 }   
            } 
            
            // get a files list with all images from folder
            $files_list = JFolder::files( $pics_folder, $filter= '.', $recurse=false, $fullpath=false, $exclude=array('index.html', 'no_pic.gif') );     
            // compare and get the difference
            $delete_files_list = array_diff($files_list, $used_image_list);
            // delete the founded files
            if ($delete_files_list){
                foreach ($delete_files_list as $delete_file_list){
                    $del_result = JFile::delete($pics_folder.$delete_file_list);
                                  JFile::delete($thumbs_folder.$delete_file_list);
                    if (!$del_result){
                        JError::raiseWarning( 100, JText::sprintf('COM_JDOWNLOADS_TOOLS_DELETE_NOT_USED_PICS_ERROR', $delete_file_list) );
                    } else {
                        $sum++;
                    }
                }
            }   
            JError::raiseNotice( 100, JText::sprintf('COM_JDOWNLOADS_TOOLS_DELETE_NOT_USED_PICS_SUM', $sum));
        }
        $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=tools', false)); 
    }
    
    /**
     * Clean the preview folder and delete all not used files from it
     */
    public function cleanPreviewFolder()
    {        
        $params = JComponentHelper::getParams('com_jdownloads');
        $files_uploaddir = $params->get('files_uploaddir');
        $tempdir         = $params->get('preview_files_folder_name');
        
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );        
        
        // check user access right
        if (JFactory::getUser()->authorise('core.admin','com_jdownloads'))
        {        
            $preview_folder   = $files_uploaddir.DS.$tempdir.DS;
            
            $used_files_list    = array();
            $images             = array();
            $del_result         = false;
            $sum                = 0;
            
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('preview_filename');
            $query->from('#__jdownloads_files');
            $query->where('preview_filename != '.$db->Quote(''));
            $db->setQuery($query);
            $result = $db->loadObjectList();    
            
            // create a array with all used images 
            for ($i=0; $i < count($result); $i++){
                 $used_files_list[] = $result[$i]->preview_filename;
            } 
            
            // get a files list with all images from folder
            $files_list = JFolder::files( $preview_folder, $filter= '.', $recurse=false, $fullpath=false, $exclude=array('index.html') );     
            // compare and get the difference
            $delete_files_list = array_diff($files_list, $used_files_list);
            // delete the founded files
            if ($delete_files_list){
                foreach ($delete_files_list as $delete_file_list){
                    $del_result = JFile::delete($preview_folder.$delete_file_list);
                    if (!$del_result){
                        JError::raiseWarning( 100, JText::sprintf('COM_JDOWNLOADS_TOOLS_DELETE_NOT_USED_PREVIEWS_ERROR', $delete_file_list) );
                    } else {
                        $sum++;
                    }
                }
            }   

            JError::raiseNotice( 100, JText::sprintf('COM_JDOWNLOADS_TOOLS_DELETE_NOT_USED_PREVIEWS_SUM', $sum));
        }
        $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=tools', false)); 
    }
    
    /**
     * Delete the log data from the last auto monitoring action
     */
    public function deleteMonitoringLog()
    {
        if (JFile::exists(JPATH_COMPONENT_ADMINISTRATOR.'/monitoring_logs.txt')){
            JFile::delete(JPATH_COMPONENT_ADMINISTRATOR.'/monitoring_logs.txt');
        }
        $this->setRedirect(JRoute::_('index.php?option=com_jdownloads', false)); 
    }
    
    /**
     * Delete the log data from the last restoration action
     */
    public function deleteRestorationLog()
    {
        if (JFile::exists(JPATH_COMPONENT_ADMINISTRATOR.'/restore_logs.txt')){
            JFile::delete(JPATH_COMPONENT_ADMINISTRATOR.'/restore_logs.txt');
        }
        $this->setRedirect(JRoute::_('index.php?option=com_jdownloads', false)); 
    }          
    
    /**
     * Delete the log data from the jD installation process
     */
    public function deleteInstallationLog()
    {
        $log_file = JFactory::getConfig()->get('log_path').'/com_jdownloads_install_logs.php';
        if (JFile::exists($log_file)){
            JFile::delete($log_file);
        }
        $this->setRedirect(JRoute::_('index.php?option=com_jdownloads', false)); 
    }
    
    
}
?>