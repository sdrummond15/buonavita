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
 
setlocale(LC_ALL, 'C.UTF-8', 'C');

use Joomla\Utilities\ArrayHelper; 
use Joomla\String\StringHelper; 
 
jimport( 'joomla.application.component.controller');
jimport( 'joomla.filesystem.folder' ); 
jimport( 'joomla.filesystem.file' );
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jdownloads'.DS.'tables');

class JDownloadsHelper
{	

    /*
     * Configure the Linkbar.
     *
     * @param    string    The name of the active view.
     */
    public static function addSubmenu($vName = 'jdownloads')
    {
        
        $params = JComponentHelper::getParams( 'com_jdownloads' );
        
        $canDo = self::getActions();
        
        JHtmlSidebar::addEntry( '<span class="icon-home-2"></span> '.JText::_( 'COM_JDOWNLOADS_CPANEL' ), 'index.php?option=com_jdownloads', $vName == 'jdownloads');
        JHtmlSidebar::addEntry( '<span class="icon-folder"></span> '.JText::_( 'COM_JDOWNLOADS_CATEGORIES' ), 'index.php?option=com_jdownloads&view=categories', $vName == 'categories');
        JHtmlSidebar::addEntry( '<span class="icon-stack"></span> '.JText::_( 'COM_JDOWNLOADS_DOWNLOADS' ), 'index.php?option=com_jdownloads&view=downloads', $vName == 'downloads');    
        JHtmlSidebar::addEntry( '<span class="icon-copy"></span> '.JText::_( 'COM_JDOWNLOADS_FILES' ), 'index.php?option=com_jdownloads&view=files', $vName == 'files');
        JHtmlSidebar::addEntry( '<span class="icon-key"></span> '.JText::_( 'COM_JDOWNLOADS_LICENSES' ), 'index.php?option=com_jdownloads&view=licenses', $vName == 'licenses');
        JHtmlSidebar::addEntry( '<span class="icon-brush"></span> '.JText::_( 'COM_JDOWNLOADS_LAYOUTS' ), 'index.php?option=com_jdownloads&view=layouts', $vName == 'layouts');
        JHtmlSidebar::addEntry( '<span class="icon-list-2"></span> '.JText::_( 'COM_JDOWNLOADS_LOGS' ), 'index.php?option=com_jdownloads&view=logs', $vName == 'logs');
        JHtmlSidebar::addEntry( '<span class="icon-contract"></span> '.JText::_( 'COM_JDOWNLOADS_MULTILINGUAL_ASSOCIATIONS' ), 'index.php?option=com_jdownloads&view=associations', $vName == 'associations');
        
        if ($canDo->get('edit.user.limits')) {
            JHtmlSidebar::addEntry( '<span class="icon-users"></span> '.JText::_( 'COM_JDOWNLOADS_USER_GROUPS' ), 'index.php?option=com_jdownloads&view=groups', $vName == 'groups');
        }
        
        // add support for com_fields
        if (JComponentHelper::isEnabled('com_fields') && $params->get('custom_fields_enable') == 1){
            JHtmlSidebar::addEntry( '<span class="icon-file-add"></span> '.JText::_('COM_JDOWNLOADS_CUSTOM_FIELDS' ), 'index.php?option=com_fields&context=com_jdownloads.download', $vName == 'fields.download');
            JHtmlSidebar::addEntry( '<span class="icon-folder-plus-2"></span> '.JText::_('COM_JDOWNLOADS_CUSTOM_FIELD_GROUPS' ), 'index.php?option=com_fields&view=groups&context=com_jdownloads.download', $vName == 'fields.groups');
        }

        if ($canDo->get('core.admin')) {
            JHtmlSidebar::addEntry( '<span class="icon-cogs"></span> '.JText::_( 'COM_JDOWNLOADS_TOOLS' ), 'index.php?option=com_jdownloads&view=tools', $vName == 'tools');
        }    
        
        JHtmlSidebar::addEntry( '<span class="icon-info"></span> '.JText::_( 'COM_JDOWNLOADS_TERMS_OF_USE' ), 'index.php?option=com_jdownloads&view=info', $vName == 'info');
    }

     /*
     * Configure the Linkbar.
     *
     * @param    string    The name of the active view.
     */
    public static function addTemplateSubmenu($vName = '')
    {
        
        $params = JComponentHelper::getParams( 'com_jdownloads' );
        
        $canDo = self::getActions();
        
        if ($canDo->get('core.manage')) {
            JHtmlSidebar::addEntry( '<span class="icon-brush"></span> '.JText::_( 'COM_JDOWNLOADS_BACKEND_TEMP_TYP1' ), 'index.php?option=com_jdownloads&amp;view=templates&type=1', $vName == 'type1');
            JHtmlSidebar::addEntry( '<span class="icon-brush"></span> '.JText::_( 'COM_JDOWNLOADS_BACKEND_TEMP_TYP8' ), 'index.php?option=com_jdownloads&amp;view=templates&type=8', $vName == 'type8');
            JHtmlSidebar::addEntry( '<span class="icon-brush"></span> '.JText::_( 'COM_JDOWNLOADS_BACKEND_TEMP_TYP4' ), 'index.php?option=com_jdownloads&amp;view=templates&type=4', $vName == 'type4');
            JHtmlSidebar::addEntry( '<span class="icon-brush"></span> '.JText::_( 'COM_JDOWNLOADS_BACKEND_TEMP_TYP2' ), 'index.php?option=com_jdownloads&amp;view=templates&type=2', $vName == 'type2');    
            JHtmlSidebar::addEntry( '<span class="icon-brush"></span> '.JText::_( 'COM_JDOWNLOADS_BACKEND_TEMP_TYP5' ), 'index.php?option=com_jdownloads&amp;view=templates&type=5', $vName == 'type5');
            JHtmlSidebar::addEntry( '<span class="icon-brush"></span> '.JText::_( 'COM_JDOWNLOADS_BACKEND_TEMP_TYP3' ), 'index.php?option=com_jdownloads&amp;view=templates&type=3', $vName == 'type3');
            JHtmlSidebar::addEntry( '<span class="icon-brush"></span> '.JText::_( 'COM_JDOWNLOADS_BACKEND_TEMP_TYP7' ), 'index.php?option=com_jdownloads&amp;view=templates&type=7', $vName == 'type7');
            JHtmlSidebar::addEntry( '<span class="icon-edit"></span> '.JText::_( 'COM_JDOWNLOADS_BACKEND_EDIT_CSS_TITLE' ), 'index.php?option=com_jdownloads&amp;view=cssedit', $vName == 'cssedit');
        }    
    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @param    int     id
     * @param    string  $assetSection (access section name from access.xml)
     * @return   JObject
     */
    public static function getActions($id = 0, $assetSection = '')
    {
        jimport('joomla.access.access');

        $user    = JFactory::getUser();
        $result    = new JObject;
        
        if (empty($id)){
            $assetName = 'com_jdownloads';
            $section   = 'component';
        } else {
            $assetName = 'com_jdownloads.'.$assetSection.'.'.(int) $id;
            if ( $assetSection != '' ){
                if ($assetSection == 'category'){
                    $section   = 'category';
                } else {
                    $section   = 'download';
                }
            }       
        }
        
        $actions = JAccess::getActions('com_jdownloads', 'component');
        foreach ($actions as $action){
                 $result->set($action->name, $user->authorise($action->name, $assetName));
        }
        return $result;        
    }
    
    /**
     * Method to get the versions number from jDownloads
     * @return string version value
     */
    public static function getjDownloadsVersion()
    {
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );
        
        
        $file = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jdownloads'.DS.'jdownloads.xml';
        if (JFile::exists($file)) {
            if ($data = JApplicationHelper::parseXMLInstallFile($file)) {
                if (isset($data['version']) && $data['version'] != '' ) {
                    return $data['version'];
                } else {
                    return 'Not defined!';
                }
            }
        } else {
            return 'Can not get jDownloads version number!';
        }
    }
    
    /**
    * @desc     Change params value in jD options
    * 
    * @param    string   $key
    *           string   $value
    * 
    * @return   boolean
    * 
    */   
    // 
    public static function changeParamSetting($key, $value){
        
        $db = JFactory::getDBO();
        
        // Load the current component params
        $params = JComponentHelper::getParams('com_jdownloads');
        
        // Set new value of param(s)
        $params->set($key, $value);
        
        // Save the parameters
        $componentid = JComponentHelper::getComponent('com_jdownloads')->id;
        $table = JTable::getInstance('extension');
        $table->load($componentid);
        $table->bind(array('params' => $params->toString()));

        // check for error
        if (!$table->check()) {
            echo $table->getError();
            return false;
        }
        // Save to database
        if (!$table->store()) {
            echo $table->getError();
            return false;
        }
        return true;
    }
    
    
    // get the plugin info to view it in the logs table list header
    public static function getLogsHeaderInfo(){
         
        $params = JComponentHelper::getParams('com_jdownloads');
         
        if (!$params->get('activate_download_log')){
            return JText::_('COM_JDOWNLOADS_BACKEND_LOG_LIST_SETTINGS_OFF');
        } else {
            $plugin = JPluginHelper::getPlugin('system', 'jdownloads');
            if (!$plugin){
                // plugin is set off
                return JText::_('COM_JDOWNLOADS_BACKEND_LOG_LIST_INFO').' '.JText::_('COM_JDOWNLOADS_SYSTEM_PLUGIN_OFF_MSG');
            }    
            $pluginParams = json_decode($plugin->params);

            $reduce_log_data = (int)$pluginParams->reduce_log_data_sets_to;
            if ($reduce_log_data > 0){
                return JText::_('COM_JDOWNLOADS_BACKEND_LOG_LIST_INFO').' '.sprintf(JText::_('COM_JDOWNLOADS_BACKEND_LOG_LIST_REDUCE_ON'), $reduce_log_data);
            } else {
                return JText::_('COM_JDOWNLOADS_BACKEND_LOG_LIST_INFO').' '.JText::_('COM_JDOWNLOADS_BACKEND_LOG_LIST_REDUCE_OFF');
            }  
        }  
    }
    
    
    // get download stats data to view it in cpanel  
    public static function getDownloadStatsData() {
        $db = JFactory::getDBO();
        
        $db->setQuery('SELECT COUNT(*) FROM #__jdownloads_categories WHERE level > 0');
        $sum_cats = intval($db->loadResult());
        $db->setQuery("SELECT COUNT(*) FROM #__jdownloads_files");
        $sum_files = intval($db->loadResult());
        $db->setQuery("SELECT SUM(downloads) FROM #__jdownloads_files");
        $sum_downloads = intval($db->loadResult());
        $db->setQuery("SELECT COUNT(*) FROM #__jdownloads_files WHERE published = 0");
        $sum_files_unpublished = intval($db->loadResult());
        $db->setQuery("SELECT COUNT(*) FROM #__jdownloads_categories WHERE published = 0");
        $sum_cats_unpublished = intval($db->loadResult());        
        $color = '#990000';
        
        $data['downloaded']         = self::strToNumber($sum_downloads);
        $data['cats_public']        = self::strToNumber($sum_cats - $sum_cats_unpublished);
        $data['files_public']       = self::strToNumber($sum_files - $sum_files_unpublished);
        $data['cats_not_public']    = self::strToNumber($sum_cats_unpublished);
        $data['files_not_public']   = self::strToNumber($sum_files_unpublished);
        $data['files_total']        = self::strToNumber($sum_files);
        $data['cats_total']         = self::strToNumber($sum_cats);
        return $data;
    }

    // read sum of files for a given cat id
    public static function getSumDownloadsFromCat($catid) {       
       $db = JFactory::getDBO();
       $db->setQuery('SELECT COUNT(*) FROM #__jdownloads_files WHERE catid = '.$catid);
       $sum = $db->loadResult();
       return $sum;
    }

    // get the root and the current path from the given cat_dir
    public static function getSplittedCategoryDirectoryPath($cat_dir) {       
        $cat_dir_path = new JObject;
        $cat_dir_path->current = substr(strrchr($cat_dir,"/"),1);
        if (!$cat_dir_path->current){
            $cat_dir_path->current = $cat_dir;
        } else {   
            $path_pos = strrpos ( $cat_dir, "/" );
            $cat_dir_path->root = substr($cat_dir, 0, $path_pos + 1);
        }
        return $cat_dir_path;
    }    
        
    /**
    * @desc   check whether the selected upload file is a picture
    * 
    * @return boolean
    * 
    */
    public static function fileIsPicture($filename)
    {
        jimport( 'joomla.filesystem.file' );
        
        $types = array('png','gif','jpg','jpeg');
        $pictype = JFile::getExt($filename);
        
        if (in_array(strtolower($pictype), $types)){
            return true;
        } else {
            return false;
        }    
    }
    
    /**
    * @desc   check whether the selected upload file is a picture
    * 
    * @return boolean
    * 
    */
    public static function fileIsImage($filetype)
    {
        if ((($filetype == 'image/gif') || ($filetype == "image/jpeg") || ($filetype == "image/jpg") || ($filetype == "image/png"))){
            return true;
        } else {
            return false;
        }
    }    

    /**
    * @desc     Check whether the selected upload file is a picture. 
    *           If so, try to get an image size, so we are sure that we have not a fake pic.
    * 
    * @param    array   $file
    * 
    * @return   boolean
    * 
    */    
    public static function imageFileIsValid($file)
    {
        // GD lib is required
        try {
            $size = getimagesize($file);
            if ($size){
                $result = self::isBadImageFile($file);
                if ($result === true){
                    // bad code found
                    return false;
                } else {
                    return true;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false; 
        }
    }         
            
    public static function fsize($file) 
    {
        $a = array("B", "KB", "MB", "GB", "TB", "PB");

        $pos = 0;
        $size = filesize($file);
        while ($size >= 1024) {
                $size /= 1024;
                $pos++;
        }
        return round($size,2)." ".$a[$pos];
    }
    
    public static function return_bytes ($size_str)
    {
        switch (substr ($size_str, -1))
        {
            case 'M': case 'm': return (int)$size_str * 1048576;
            case 'K': case 'k': return (int)$size_str * 1024;
            case 'G': case 'g': return (int)$size_str * 1073741824;
            default: return $size_str;
        }
    }
    
    /**
     * A function to check file from bad codes.
     *
     * @param (string) $file - file path.
     * @return boolean  true = bad code found
     * 
     */
    public static function isBadImageFile($file)
    {
        if (file_exists($file))
        {
            $filedata = fopen($file, 'rb');
            $contents = fread($filedata, filesize($file));
            fclose($filedata);
 
            $check = array('<script', 'javascript:', '<?php', '$_GET', '$_POST', '$_COOKIE', '$_SERVER', '$HTTP', 'system(', 'exec(', 'passthru', 'eval(', '<input', '<frame', '<iframe');
            foreach($check as $chk){
                if(strpos($contents, strtolower($chk)) !== false){
                    return true;
                } 
            } 
            return false;     
        } else {
           return false;
        }
    }
            
    /**
    * @desc   search by file name from backend files list
    * 
    * @return array  - result with founded files 
    * 
    */
    public static function arrayRegexSearch ( $strPattern, $arHaystack, $bTarget = TRUE, $bReturn = TRUE ) 
    { 
        $arResults = array (); 
        foreach ( $arHaystack as $strKey => $strValue ) 
        { 
          $strHaystack = $strValue['name']; 
          if ( !$bTarget ) 
          { 
            $strHaystack = $strKey; 
          } 
          if ( preg_match ( $strPattern, $strHaystack ) ) 
          { 
            if ( $bReturn ) 
            { 
              $arResults[] = $strKey; 
            } 
            else 
            { 
              $arResults[] = $strValue; 
            } 
          } 
        } 
        if ( count ( $arResults ) ) 
        { 
          return $arResults; 
        } 
        return FALSE; 
    }     
      
    /*
    * Read user group settings and limitations from jDownloads user groups table
    *
    * @return array     $jd_user_settings 
    */
    public static function getUserRules(){
        
         $db   = JFactory::getDBO();
         $user = JFactory::getUser();
         $groups_id = $user->getAuthorisedViewLevels();
         
         if (!$groups_id) $groups_id[] = 1; // user is not registered = guest
         
         $groups_ids = implode(',', $groups_id);
         $sql = 'SELECT * FROM #__jdownloads_usergroups_limits WHERE group_id IN (' . $groups_ids. ')';
         $db->setQuery($sql);
         $jd_user_settings = $db->loadObjectList();

         if (!$jd_user_settings) return 0; // abort when we have not any result (only first time after a fresh installation)
         
         if (count($jd_user_settings) == 1){
             // user is only in a single group
             return $jd_user_settings[0];
         } else {
             // user is in multi groups
             // so we must get the group with the highest permission levels
             // default groups:
             // 1. super users ID = 8
             // 2. admin       ID = 7
             // 3. manager     ID = 6
             // 4. publisher   ID = 5
             // 5. editor      ID = 4
             // 6. author      ID = 3
             // 7. registered  ID = 2
             // 8. guest       ID = 9
             // 9. public      ID = 1
             if (in_array('8', $groups_id)) {
                 $key = self::findUserGroupID($jd_user_settings, '8');
                 return $jd_user_settings[$key];
             }
             if (in_array('7', $groups_id)) {
                 $key = self::findUserGroupID($jd_user_settings, '7');
                 return $jd_user_settings[$key];
             } 
             if (in_array('6', $groups_id)) {
                 $key = self::findUserGroupID($jd_user_settings, '6');
                 return $jd_user_settings[$key];
             } 
             if (in_array('5', $groups_id)) {
                 $key = self::findUserGroupID($jd_user_settings, '5');
                 return $jd_user_settings[$key];
             }                                          
             if (in_array('4', $groups_id)) {
                 $key = self::findUserGroupID($jd_user_settings, '4');
                 return $jd_user_settings[$key];
             } 
             if (in_array('3', $groups_id)) {
                 $key = self::findUserGroupID($jd_user_settings, '3');
                 return $jd_user_settings[$key];
             } 
             if (in_array('2', $groups_id)) {
                 $key = self::findUserGroupID($jd_user_settings, '2');
                 return $jd_user_settings[$key];
             } 
             if (in_array('9', $groups_id)) {
                 $key = self::findUserGroupID($jd_user_settings, '9');
                 return $jd_user_settings[$key];
             } 
             if (in_array('1', $groups_id)) {
                 $key = self::findUserGroupID($jd_user_settings, '1');
                 return $jd_user_settings[$key];
             }                                                     
         }
         return $jd_user_settings[0];
    }
    

    /*
    * find the correct index value for a given group ID from a array with jD user groups settings 
    *
    * @param mixed $jd_user_settings
    * @param mixed $id
    * @return mixed
    */
    public static function findUserGroupID($jd_user_settings, $id)
    {
        for ($i=0, $n=count($jd_user_settings); $i<$n; $i++){
             if ($jd_user_settings[$i]->group_id == $id){
                 return $i;
             }
        }
        return 0;
    }       

    /*
    * Make sure that we have a valid data for user groups after installation
    *
    * @return  boolean
    */
    public static function setUserRules(){
        
         $db     = JFactory::getDBO();
         
        // check whether this is the first run, then the table is empty
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__jdownloads_usergroups_limits');
        $db->setQuery($query);
        $jd_groups = $db->loadObjectList();
        $amount_jd_groups = count($jd_groups);
         
        if ($amount_jd_groups  == 0){

                // get the joomla usergroups
                $query = $db->getQuery(true);
                $query->select('*');
                $query->from('#__usergroups');
                $db->setQuery($query);
                $joomla_groups = $db->loadObjectList();
                $amount_joomla_groups = count($joomla_groups);

                // add the missing joomla user groups in jD groups
                if ($joomla_groups){
                   for ($i=0; $i < count($joomla_groups); $i++) {
                        $query = $db->getQuery(true);
                        $query->select('*');
                        $query->from('#__jdownloads_usergroups_limits');
                        $query->where('group_id = '.(int)$joomla_groups[$i]->id);
                        $db->setQuery($query);
                        if (!$result = $db->loadResult()){
                            // add the joomla group to the jD groups
                            $query = $db->getQuery(true);
                            $query->insert('#__jdownloads_usergroups_limits');
                            // add group_id
                            $query->set('group_id = '.$db->quote($joomla_groups[$i]->id));
                            // add default msg for timer
                            $query->set('countdown_timer_msg = '.$db->quote(JText::_('COM_JDOWNLOADS_USERGROUPS_VIEW_COUNTDOWN_MSG_TEXT')));
                            // add default msg for limits
                            $query->set('download_limit_daily_msg = '.$db->quote(JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_LIMIT_DAILY_MSG')));
                            $query->set('download_limit_weekly_msg = '.$db->quote(JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_LIMIT_WEEKLY_MSG')));
                            $query->set('download_limit_monthly_msg = '.$db->quote(JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_LIMIT_MONTHLY_MSG')));
                            // volume
                            $query->set('download_volume_limit_daily_msg = '.$db->quote(JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_VOLUME_LIMIT_DAILY_MSG')));
                            $query->set('download_volume_limit_weekly_msg = '.$db->quote(JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_VOLUME_LIMIT_WEEKLY_MSG')));
                            $query->set('download_volume_limit_monthly_msg = '.$db->quote(JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_VOLUME_LIMIT_MONTHLY_MSG')));
                            
                            $query->set('how_many_times_msg = '.$db->quote(JText::_('COM_JDOWNLOADS_USERGROUPS_DOWNLOAD_HOW_MANY_TIMES_MSG')));
                            $query->set('upload_limit_daily_msg = '.$db->quote(JText::_('COM_JDOWNLOADS_USERGROUPS_UPLOAD_LIMIT_DAILY_MSG')));
                            
                            $query->set('view_user_his_limits_msg = '.$db->quote(JText::_('COM_JDOWNLOADS_USERGROUPS_VIEW_USER_HIS_LIMITS_MSG')));
                            
                            // create some default values - also for editing or creating Downloads in Frontend
                            if ((int)$joomla_groups[$i]->id == 8){
                                $query->set('uploads_allowed_types = '.$db->quote('zip,rar,pdf,txt,doc,gif,png,jpg'));
                                $max = (int)ini_get('upload_max_filesize') * 1024;
                                $query->set('uploads_maxfilesize_kb = '.$db->quote($max));
                                $query->set('uploads_max_amount_images = '.$db->quote('10'));
                                $query->set('uploads_can_change_category = '.$db->quote('1'));
                                $query->set('uploads_auto_publish = '.$db->quote('1'));
                                $query->set('uploads_use_editor = '.$db->quote('1'));
                            } else {
                                $query->set('uploads_allowed_types = '.$db->quote('zip,rar,pdf,txt'));
                                $max = (int)ini_get('upload_max_filesize') * 1024;
                                if ($max > 5120) $max = 5120;
                                $query->set('uploads_maxfilesize_kb = '.$db->quote($max));
                                $query->set('uploads_max_amount_images = '.$db->quote('3'));
                                $query->set('uploads_can_change_category = '.$db->quote('1'));
                                $query->set('uploads_auto_publish = '.$db->quote('0'));
                                $query->set('uploads_use_editor = '.$db->quote('1'));
                            }
                            $query->set('uploads_allowed_preview_types = '.$db->quote('mp3,mp4'));
                            $query->set('download_limit_after_this_time = '.$db->quote('60'));
                            $query->set('transfer_speed_limit_kb = '.$db->quote('0'));
                            $query->set('download_limit_daily = '.$db->quote('0'));
                            $query->set('download_limit_weekly = '.$db->quote('0'));
                            $query->set('download_limit_monthly = '.$db->quote('0'));
                            $query->set('upload_limit_daily = '.$db->quote('0'));
                            $query->set('view_captcha = '.$db->quote('0'));
                            $query->set('view_report_form = '.$db->quote('0'));
                            $query->set('countdown_timer_duration = '.$db->quote('0'));
                            $query->set('download_volume_limit_daily = '.$db->quote('0'));
                            $query->set('download_volume_limit_weekly = '.$db->quote('0'));
                            $query->set('download_volume_limit_monthly = '.$db->quote('0'));                             
                            
                            if ((int)$joomla_groups[$i]->id == 1){ 
                               $query->set('importance = '.$db->quote(1)); 
                            } elseif ((int)$joomla_groups[$i]->id == 2){ 
                                $query->set('importance = '.$db->quote(20));
                            } elseif ((int)$joomla_groups[$i]->id == 3){ 
                                $query->set('importance = '.$db->quote(30));
                            } elseif ((int)$joomla_groups[$i]->id == 4){ 
                                $query->set('importance = '.$db->quote(40));
                            } elseif ((int)$joomla_groups[$i]->id == 5){ 
                                $query->set('importance = '.$db->quote(50));
                            } elseif ((int)$joomla_groups[$i]->id == 6){ 
                                $query->set('importance = '.$db->quote(60));
                            } elseif ((int)$joomla_groups[$i]->id == 7){ 
                                $query->set('importance = '.$db->quote(70));
                            } elseif ((int)$joomla_groups[$i]->id == 8){ 
                                $query->set('importance = '.$db->quote(100));
                            } else {
                                $query->set('importance = '.$db->quote(0));
                            }
                                                        
                            $db->setQuery($query);   
                            if (!$db->execute()){
                                $this->setError($db->getErrorMsg());
                                return false;
                            }                        
                        }               
                   }
                }        
        }
        return true; 

    }    
    
    public static function getXMLdata($fileandpath, $filename){
        
        $params = JComponentHelper::getParams('com_jdownloads');
        $files_uploaddir = $params->get('files_uploaddir');
        
        jimport( 'joomla.filesystem.archive' );
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');    
        
        $files = array();
        $xml_files = array();
        $xmltags = array();
        $path_parts = pathinfo($fileandpath);
        $destination_dir = $files_uploaddir.DS.'tempzipfiles'.DS.$path_parts['filename'];
        if ($ok = JFolder::create($destination_dir.DS)){
            if(JArchive::extract($fileandpath, $destination_dir.DS)){
                // get files list
                $xml_files = self::scan_dir($destination_dir.DS, $type=array('.xml','.XML'), $only=false, $allFiles=false, $recursive=TRUE, $onlyDir='', $exclude_folders='', $include_folders='', $jd_root='', $files);
                if ($xml_files){            
                    foreach($xml_files as $key => $array2) {
                       $filepath[] = $xml_files[$key]['path'].DS.$xml_files[$key]['file'];
                    }
                    // $xml_file = usort($filepath, "cmp_str"); 
                    foreach($filepath as $fpath){
                       $xmltags = self::use_xml($fpath);
                       // get xml file tags
                       if ($xmltags[name] != ''){
                           self::delete_dir_and_allfiles($destination_dir.DS);
                           return $xmltags;
                           break; 
                       }    
                    }
               }    
            }
            // delete all unzipped files and folder
            self::delete_dir_and_allfiles($destination_dir.DS);
        } 
        return false;     
    }

    public static function use_xml($u_xml){
        // function by JoomTools
        $felder = array("name","author","authorUrl", "authorMail", "creationDate","copyright","license","version","description");
        foreach($felder as $feld){
            $wert =preg_replace("/\s\s+/","",stripslashes(self::read_xml("<$feld>(.*)</$feld>",$u_xml)));
            $wert =str_replace(chr(91), '-', str_replace(chr(93), '-', $wert));
            $wert =ereg_replace("<!-CDATA-", "", $wert);
            $wert =ereg_replace("-->", "", $wert);
            $tag[$feld] = $wert;
        }
        return $tag;
    }

    public static function read_xml($search,$xmlfile){
        // function by JoomTools
        $fp = fopen($xmlfile,"r");
        while(!feof($fp)){
            $r_xml .= fgets($fp);
        }
        fclose($fp);
        eregi($search, $r_xml, $search_result1);
        $search_result = trim($search_result1[1]);
        return $search_result;
    }

    // fill file data from a given xml install file
    public static function fillFileDateFromXML($row, $xmltags){
        $database = JFactory::getDBO();   
        $lic_id = '';
        if ($xmltags['license']){
            $database->setQuery("SELECT id FROM #__jdownloads_licenses WHERE title LIKE '%".$xmltags['license']."%' OR url LIKE '%".$xmltags['license']."%'");
            $lic_id = $database->loadResult();                                      
        }
        $row->title = htmlspecialchars_decode($xmltags['name'], ENT_QUOTES); 
        $row->alias = JApplication::stringURLSafe($row->title);

        if(trim(str_replace('-','',$row->alias)) == '') {
           // get current 'now' data with correct local time zone
           $datenow = JFactory::getDate()->toSql();  // True to return the date string in the local time zone, false to return it in GMT.
           $row->alias = $datenow;
        }
        $row->release          = htmlspecialchars_decode($xmltags['version'], ENT_QUOTES);
        $row->description      = htmlspecialchars_decode($xmltags['description'], ENT_QUOTES); 
        $row->description_long = $row->description;
        if (!$lic_id){                                                           
            $row->license      = '';
        } else {
            $row->license      = (int)$lic_id;
        }    
        if ($date = strtotime($xmltags['creationDate'])){
            $row->file_date    = JHtml::_('date', $xmltags['creationDate'],'Y-m-d H:i:s');
        } else {
            $row->file_date    = '0000-00-00 00:00:00';
        }     
        $row->url_home         = $xmltags['authorUrl'];
        $row->author           = $xmltags['author'];
        $row->url_author       = $xmltags['authorMail'];
        return $row->title;
    }                   
        
        
    // Get the filesize from a given file url
    public static function urlfilesize($url) {
        if (substr($url,0,4)=='http' || substr($url,0,3)=='ftp') {
            $size = array_change_key_case(get_headers($url, 1),CASE_LOWER);
            $size = $size['content-length'];
            if (is_array($size)) { $size = $size[1]; }
        } else {
            $size = @filesize($url); 
        }
        $a = array("B", "KB", "MB", "GB", "TB", "PB");

        $pos = 0;
        while ($size >= 1024) {
               $size /= 1024;
               $pos++;
        }
        return round($size,2)." ".$a[$pos];    
    } 
             
    /**
    *  Get the external file date
    * 
    * @param mixed $url
    */
    public static function urlfiledate($url){
        if (file_exists($url)){
            $aktuell = date("Y-m-d H:i:s",filemtime($url));
        } else {
            $aktuell = date("Y-m-d H:i:s");
        }    
      return $aktuell;
    }
    
    /**
    * Check whether we have a valid URL
    * 
    * @param mixed $url
    * @return boolean true when valid
    */
    public static function urlValidate($url)
    {
        $url = trim($url);
        if (preg_match('%^(?:(?:https?)://)(?:\S+(?::\S*)?@|\d{1,3}(?:\.\d{1,3}){3}|(?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?(?:[^\s]*)?$%iu', $url)){
            return true;
        }
        return false;
    }    
              

    /* Create a new thumb from a given pic
     *
     * @param mixed $hight_new  only used when in params is activated the 'create all thumbs new option')
     * @param mixed $width_new  only used when in params is activated the 'create all thumbs new option')
    */
    public static function create_new_thumb($picturepath, $picfilename, $height_new = 0, $width_new = 0) {
        
        $params = JComponentHelper::getParams('com_jdownloads');
        
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );        
        
        // get info about GD installation
        if (function_exists('gd_info')) {
            $gda = gd_info();
            $gd['version'] = $gda['GD Version'];
            $gd['num'] = preg_replace('/[a-zA-Z\s()]+/','',$gda['GD Version']);
            $gd['freetype'] = $gda["FreeType Support"];
            $gd['gif_read'] = $gda["GIF Read Support"];
            $gd['gif_make'] = $gda["GIF Create Support"];
            $gd['jpg'] = $gda["JPEG Support"];
            $gd['png'] = $gda["PNG Support"];
        }
        
        $thumbpath = JPATH_SITE.'/images/jdownloads/screenshots/thumbnails/';
        
        if (!is_dir($thumbpath)){
            @mkdir("$thumbpath", 0755);
        }    
        
        if ($width_new > 0){
            $newwidth = $width_new;
        } else {        
            $newwidth = $params->get('thumbnail_size_width');
        }
        $newsize = $newwidth;    

        if ($height_new > 0){
            $newheight = $height_new;
        } else {        
            $newheight = $params->get('thumbnail_size_height');
        }         

        // Build the thumbnail filename
        $thumbfilename = $thumbpath.$picfilename;
        
        /* check that file exist */
        if(!file_exists($picturepath)) {
            return false;
        }
        
        /* get mime type */
        $size=getimagesize($picturepath);
        switch($size[2]) {
            case "1":
            $oldpic = imagecreatefromgif($picturepath);
            break;
            case "2":
            $oldpic = imagecreatefromjpeg($picturepath);
            break;
            case "3":
            $oldpic = imagecreatefrompng($picturepath);
            break;
            default:
            return false;
        }
        /* get old image dimensions */
        $width = $size[0];
        $height = $size[1]; 

        /* set new image dimensions */
        if($width >= $height) {
            $newwidth = $newsize;
            $newheight = $newsize * $height / $width;
        } else {
            $newheight = $newsize;
            $newwidth = $newsize * $width / $height;
        }            

        /* create new image with new dimensions */
        $newpic = imagecreatetruecolor($newwidth,$newheight);
        
        // Set alphablending to false to get a transparency background
        imagealphablending($newpic, false);
        imagesavealpha($newpic,true);
        
        /* resize it */
        // imagecopyresized will copy and scale and image. This uses a fairly primitive algorithm that tends to yield more pixelated results.
        //imagecopyresized($newpic,$oldpic,0,0,0,0,$newwidth,$newheight,$width,$height);
        // imagecopyresampled will copy and scale and image, it uses a smoothing and pixel interpolating algorithm that will generally yield much better results then imagecopyresized at the cost of a little cpu usage.
        imagecopyresampled($newpic,$oldpic,0,0,0,0,$newwidth,$newheight,$width,$height);  
        // store the image
        switch($size[2]){
            case "1":    return imagegif($newpic, $thumbfilename);
            break;
            case "2":    return imagejpeg($newpic, $thumbfilename);
            break;
            case "3":    return imagepng($newpic, $thumbfilename);
            break;
        }
        // delete the used memory
        imagedestroy($oldpic);
        imagedestroy($newpic);
    }
    
    /* Create a new image from a uploaded pic and store it in the screenshot folder
     *
     * 
     * 
     */
    public static function create_new_image($picturepath, $picfilename) {
        
        $params = JComponentHelper::getParams('com_jdownloads');
        
        $thumbpath = JPATH_SITE.'/images/jdownloads/screenshots/';
        
        if (!is_dir($thumbpath)){
            @mkdir("$thumbpath", 0755);
        }    
        
        $maxwidth = $params->get('create_auto_thumbs_from_pics_image_width');
        $maxheight = $params->get('create_auto_thumbs_from_pics_image_height');
        
        $thumbfilename = $thumbpath.$picfilename;
        
        /* check that file exist */
        if(!file_exists($picturepath)) {
            return false;
        }
        
        /* get mime type */
        $size=getimagesize($picturepath);
        switch($size[2]) {
            case "1":
            $oldpic = imagecreatefromgif($picturepath);
            break;
            case "2":
            $oldpic = imagecreatefromjpeg($picturepath);
            break;
            case "3":
            $oldpic = imagecreatefrompng($picturepath);
            break;
            default:
            return false;
        }
        /* get old image dimensions */
        $width = $size[0];
        $height = $size[1]; 
        
        /* set new image dimensions */
        // but we will not 'stretch' smaller images
        if ($width < $maxwidth || $height < $maxheight){
            $newwidth  = $width;
            $newheight = $height;
        } else {        
            if ($width/$maxwidth > $height/$maxheight) {
                $newwidth = $maxwidth;
                $newheight = $maxwidth*$height/$width;
            } else {
                $newheight = $maxheight;
                $newwidth = $maxheight*$width/$height;
            }
        }
        
        $newpic = imagecreatetruecolor($newwidth,$newheight);
        imagealphablending($newpic,false);
        imagesavealpha($newpic,true);
        
        // resize it 
        imagecopyresampled($newpic,$oldpic,0,0,0,0,$newwidth,$newheight,$width,$height); 
        // store the image
        switch($size[2]) {
            case "1":    return imagegif($newpic, $thumbfilename);
            break;
            case "2":    return imagejpeg($newpic, $thumbfilename);
            break;
            case "3":    return imagepng($newpic, $thumbfilename);
            break;
        }
        // delete the used memory
        imagedestroy($oldpic);
        imagedestroy($newpic);
    }


    /* Create a thumnail from a pdf file
     *
     * 
     * 
     *
     */ 
    public static function create_new_pdf_thumb($target_path, $only_name, $thumb_path, $screenshot_path){
        $params = JComponentHelper::getParams('com_jdownloads');    
        
        $pdf_thumb_file_name = '';
        $old_version = false;
        
        if (extension_loaded('imagick')){ 
        
            $version = Imagick::getVersion();
            preg_match('/ImageMagick ([0-9]+\.[0-9]+\.[0-9]+)/', $version['versionString'], $version);
            if (version_compare($version[1],'6.2.8') <= 0){
                $old_version = true;
            }
            
            if (JFile::exists($target_path)){ 
             
	            // create small thumb
	            $image = new Imagick($target_path);
	            if ($old_version){
	                // deprecated in newer versions
	            	$image -> setImageIndex(0);
	            } else {
	                $image->setIteratorIndex(0);
	            }
	            $image -> setImageFormat($params->get('pdf_thumb_image_type'));
	            $image -> scaleImage($params->get('pdf_thumb_height'), $params->get('pdf_thumb_width'), 1);
	            $pdf_thumb_file_name = $only_name.'.'.strtolower($params->get('pdf_thumb_image_type'));
	            $image->writeImage($thumb_path.$only_name.'.'.strtolower($params->get('pdf_thumb_image_type')));
	            $image->clear();
	            $image->destroy();
	            // create big thumb
	            $image = new Imagick($target_path);
	            if ($old_version){
	            	$image -> setImageIndex(0);
	            } else {
	                $image->setIteratorIndex(0);
	            }
	            $image -> setImageFormat($params->get('pdf_thumb_image_type'));
	            $image -> scaleImage($params->get('pdf_thumb_pic_height'), $params->get('pdf_thumb_pic_width'), 1);
	            $image->writeImage($screenshot_path.$only_name.'.'.strtolower($params->get('pdf_thumb_image_type')));
	            $image->clear();
	            $image->destroy();    
	        }
        }
        return $pdf_thumb_file_name; 
    }        
    
    /* Recreate all thumbs with new size
     * Used when params are saved 
     * 
     * 
    */ 
    public static function resizeAllThumbs($hight_new, $width_new)
    {
        // first delete all old thumbs
        $thumb_dir = JPATH_SITE.'/images/jdownloads/screenshots/thumbnails/';
        $screen_dir = JPATH_SITE.'/images/jdownloads/screenshots/';
        // this files shall not be delete
        $exceptions[] = 'index.html';
        self::delete_dir_and_allfiles($thumb_dir, false, $exceptions );
        $exclude_folders = array();  // folders which not shall be scanned
        $include_folders = array();
        $files     = array();
        $jd_root   = '';
        $only      = TRUE;
        $type      = array("png","jpg","gif");
        $allFiles  = false;
        $recursive = FALSE;
        $onlyDir   = FALSE;
        $ok = self::scan_dir($screen_dir, $type, $only, $allFiles, $recursive, $onlyDir, $exclude_folders, $include_folders, $jd_root, $files);
        if ($ok){
            ignore_user_abort(true);
            foreach ($files as $pic){
                @set_time_limit(0);
                $result = self::create_new_thumb($pic['path'].$pic['file'], $pic['file'], $hight_new, $width_new);
            }
            return JText::_('COM_JDOWNLOADS_CONFIG_SETTINGS_THUMBS_CREATE_ALL_MESSAGE');         
        }                        
            
    }
    
    public static function check_joomla_group($group, $inherited){
        $user = JFactory::getUser();
        $user_id = $user->get('id');
        
        if($inherited){
            //include inherited groups
            jimport( 'joomla.access.access' );
            $groups = JAccess::getGroupsByUser($user_id);
        }else{
            //exclude inherited groups
            $user =& JFactory::getUser($user_id);
            $groups = isset($user->groups) ? $user->groups : array();
        }
        $return = 0;
        
        if(in_array($group, $groups)){
           $return = true;
        }
        return $return;
    }

    
    // run download from backend
    public static function downloadFile($cid, $type = ''){
        $params = JComponentHelper::getParams('com_jdownloads');

        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );
        
        $app = JFactory::getApplication(); 
        $db = JFactory::getDBO();    
        clearstatcache(); 
        
        $view_types = array();
        $view_types = explode(',', $params->get('file_types_view'));
        
        // get path
        $db->SetQuery('SELECT * FROM #__jdownloads_files WHERE id = '.(int)$cid);
		$file = $db->loadObject();

        if ($type == 'prev'){
            if ($file->preview_filename){
                $file = $params->get('files_uploaddir').DS.$params->get('preview_files_folder_name').DS.$file->preview_filename; 
            }
        } else {
            if ($file->url_download){
                $db->SetQuery("SELECT cat_dir, cat_dir_parent FROM #__jdownloads_categories WHERE id = $file->catid");
                $cat_dirs = $db->loadObject();
                // build the complete stored category path
                if ($cat_dirs->cat_dir_parent != ''){
                    $cat_dir = $cat_dirs->cat_dir_parent.DS.$cat_dirs->cat_dir;
                } else {
                    $cat_dir = $cat_dirs->cat_dir;
                }
                
                $filename_direct = $params->get('files_uploaddir').DS.$cat_dir.DS.$file->url_download;
                $file = $params->get('files_uploaddir').DS.$cat_dir.DS.$file->url_download; 
            }    
        } 

        if (!jFile::exists($file)){
            exit;
        }        
        
        $len = filesize($file);
        
        // if set the option for direct link to the file
        if (!$params->get('use_php_script_for_download')){
            if (empty($filename_direct)) {
                $app->redirect($file);
            } else {
                $app->redirect($filename_direct);
            }
        } else {    
            $filename = basename($file);
            $file_extension = jFile::getExt($filename);
            $ctype = self::datei_mime($file_extension);
            ob_end_clean();
            // needed for MS IE - otherwise content disposition is not used?
            if (ini_get('zlib.output_compression')){
                ini_set('zlib.output_compression', 'Off');
            }
            
            header("Cache-Control: public, must-revalidate");
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            // header("Pragma: no-cache");  // Problems with MS IE
            header("Expires: 0"); 
            header("Content-Description: File Transfer");
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            header("Content-Type: " . $ctype);
            header("Content-Length: ".(string)$len);
            if (!in_array($file_extension, $view_types)){
                header('Content-Disposition: attachment; filename="'.$filename.'"');
            } else {
              // view file in browser
              header('Content-Disposition: inline; filename="'.$filename.'"');
            }   
            header("Content-Transfer-Encoding: binary\n");
            
            // set_time_limit doesn't work in safe mode
            if (!ini_get('safe_mode')){ 
                @set_time_limit(0);
            }
            @readfile($file);
        }
        exit;
    }

    public static function datei_mime($filetype) {
        
        switch ($filetype) {
            case "ez":  $mime="application/andrew-inset"; break;
            case "hqx": $mime="application/mac-binhex40"; break;
            case "cpt": $mime="application/mac-compactpro"; break;
            case "doc": $mime="application/msword"; break;
            case "bin": $mime="application/octet-stream"; break;
            case "dms": $mime="application/octet-stream"; break;
            case "lha": $mime="application/octet-stream"; break;
            case "lzh": $mime="application/octet-stream"; break;
            case "exe": $mime="application/octet-stream"; break;
            case "class": $mime="application/octet-stream"; break;
            case "dll": $mime="application/octet-stream"; break;
            case "oda": $mime="application/oda"; break;
            case "pdf": $mime="application/pdf"; break;
            case "ai":  $mime="application/postscript"; break;
            case "eps": $mime="application/postscript"; break;
            case "ps":  $mime="application/postscript"; break;
            case "xls": $mime="application/vnd.ms-excel"; break;
            case "ppt": $mime="application/vnd.ms-powerpoint"; break;
            case "wbxml": $mime="application/vnd.wap.wbxml"; break;
            case "wmlc": $mime="application/vnd.wap.wmlc"; break;
            case "wmlsc": $mime="application/vnd.wap.wmlscriptc"; break;
            case "vcd": $mime="application/x-cdlink"; break;
            case "pgn": $mime="application/x-chess-pgn"; break;
            case "csh": $mime="application/x-csh"; break;
            case "dvi": $mime="application/x-dvi"; break;
            case "spl": $mime="application/x-futuresplash"; break;
            case "gtar": $mime="application/x-gtar"; break;
            case "hdf": $mime="application/x-hdf"; break;
            case "js":  $mime="application/x-javascript"; break;
            case "nc":  $mime="application/x-netcdf"; break;
            case "cdf": $mime="application/x-netcdf"; break;
            case "swf": $mime="application/x-shockwave-flash"; break;
            case "tar": $mime="application/x-tar"; break;
            case "tcl": $mime="application/x-tcl"; break;
            case "tex": $mime="application/x-tex"; break;
            case "texinfo": $mime="application/x-texinfo"; break;
            case "texi": $mime="application/x-texinfo"; break;
            case "t":   $mime="application/x-troff"; break;
            case "tr":  $mime="application/x-troff"; break;
            case "roff": $mime="application/x-troff"; break;
            case "man": $mime="application/x-troff-man"; break;
            case "me":  $mime="application/x-troff-me"; break;
            case "ms":  $mime="application/x-troff-ms"; break;
            case "ustar": $mime="application/x-ustar"; break;
            case "src": $mime="application/x-wais-source"; break;
            case "zip": $mime="application/x-zip"; break;
            case "au":  $mime="audio/basic"; break;
            case "snd": $mime="audio/basic"; break;
            case "mid": $mime="audio/midi"; break;
            case "midi": $mime="audio/midi"; break;
            case "kar": $mime="audio/midi"; break;
            case "mpga": $mime="audio/mpeg"; break;
            case "mp2": $mime="audio/mpeg"; break;
            case "mp3": $mime="audio/mpeg"; break;
            case "aif": $mime="audio/x-aiff"; break;
            case "aiff": $mime="audio/x-aiff"; break;
            case "aifc": $mime="audio/x-aiff"; break;
            case "m3u": $mime="audio/x-mpegurl"; break;
            case "ram": $mime="audio/x-pn-realaudio"; break;
            case "rm":  $mime="audio/x-pn-realaudio"; break;
            case "rpm": $mime="audio/x-pn-realaudio-plugin"; break;
            case "ra":  $mime="audio/x-realaudio"; break;
            case "wav": $mime="audio/x-wav"; break;
            case "pdb": $mime="chemical/x-pdb"; break;
            case "xyz": $mime="chemical/x-xyz"; break;
            case "bmp": $mime="image/bmp"; break;
            case "gif": $mime="image/gif"; break;
            case "ief": $mime="image/ief"; break;
            case "jpeg": $mime="image/jpeg"; break;
            case "jpg": $mime="image/jpeg"; break;
            case "jpe": $mime="image/jpeg"; break;
            case "png": $mime="image/png"; break;
            case "tiff": $mime="image/tiff"; break;
            case "tif": $mime="image/tiff"; break;
            case "wbmp": $mime="image/vnd.wap.wbmp"; break;
            case "ras": $mime="image/x-cmu-raster"; break;
            case "pnm": $mime="image/x-portable-anymap"; break;
            case "pbm": $mime="image/x-portable-bitmap"; break;
            case "pgm": $mime="image/x-portable-graymap"; break;
            case "ppm": $mime="image/x-portable-pixmap"; break;
            case "rgb": $mime="image/x-rgb"; break;
            case "xbm": $mime="image/x-xbitmap"; break;
            case "xpm": $mime="image/x-xpixmap"; break;
            case "xwd": $mime="image/x-xwindowdump"; break;
            case "msh": $mime="model/mesh"; break;
            case "mesh": $mime="model/mesh"; break;
            case "silo": $mime="model/mesh"; break;
            case "wrl": $mime="model/vrml"; break;
            case "vrml": $mime="model/vrml"; break;
            case "css": $mime="text/css"; break;
            case "asc": $mime="text/plain"; break;
            case "txt": $mime="text/plain"; break;
            case "gpg": $mime="text/plain"; break;
            case "rtx": $mime="text/richtext"; break;
            case "rtf": $mime="text/rtf"; break;
            case "wml": $mime="text/vnd.wap.wml"; break;
            case "wmls": $mime="text/vnd.wap.wmlscript"; break;
            case "etx": $mime="text/x-setext"; break;
            case "xsl": $mime="text/xml"; break;
            case "flv": $mime="video/x-flv"; break;
            case "mpeg": $mime="video/mpeg"; break;
            case "mpg": $mime="video/mpeg"; break;
            case "mpe": $mime="video/mpeg"; break;
            case "qt":  $mime="video/quicktime"; break;
            case "mov": $mime="video/quicktime"; break;
            case "mxu": $mime="video/vnd.mpegurl"; break;
            case "avi": $mime="video/x-msvideo"; break;
            case "movie": $mime="video/x-sgi-movie"; break;
            case "asf": $mime="video/x-ms-asf"; break;
            case "asx": $mime="video/x-ms-asf"; break;
            case "wm":  $mime="video/x-ms-wm"; break;
            case "wmv": $mime="video/x-ms-wmv"; break;
            case "wvx": $mime="video/x-ms-wvx"; break;
            case "ice": $mime="x-conference/x-cooltalk"; break;
            case "rar": $mime="application/x-rar"; break;
            default:    $mime="application/octet-stream"; break; 
        }
        return $mime;
    }    
    
    /* Remove the assigned file from a download on the server and clean the url_download field
     *
     * @param   string  id 
     * 
     * @return    void
    */   
    public static function deleteFile($id){
        $params = JComponentHelper::getParams('com_jdownloads');

        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );
        
        $app = JFactory::getApplication(); 
        $db = JFactory::getDBO();    
        
        // get path
        $db->SetQuery("SELECT * FROM #__jdownloads_files WHERE id = $id");
        $file = $db->loadObject();

        if ($file->url_download){
            // get the cat folder path
            $db->SetQuery('SELECT cat_dir, cat_dir_parent FROM #__jdownloads_categories WHERE id = '.$file->catid);
            $cat_dirs = $db->loadObject();
            if ($cat_dirs->cat_dir_parent != ''){
                $cat_dir = $cat_dirs->cat_dir_parent.'/'.$cat_dirs->cat_dir;
            } else {
                $cat_dir = $cat_dirs->cat_dir;
            }
            $filename = $params->get('files_uploaddir').DS.$cat_dir.DS.$file->url_download;
            
            if (!jFile::exists($filename)){
                // file not exist - but we must always clear the data field 
                $db->SetQuery("UPDATE #__jdownloads_files SET url_download = '', size = '' WHERE id = '$id'");
                $db->execute();
                return false; 
            } else {
                if (jFile::delete($filename)){
                    $db->SetQuery("UPDATE #__jdownloads_files SET url_download = '', size = '' WHERE id = '$id'");
                    $db->execute();                    
                    return true;
                } else {
                    // delete error
                    return false;
                }    
            }
        } else {
            // url_download empty
            return false;
        }
    }        

    /* Remove the assigned preview file from a download on the server and clean the preview_filename field
     *
     * @param   string  id 
     * 
     * @return    void
    */   
    public static function deletePreviewFile($id){
        $params = JComponentHelper::getParams('com_jdownloads');

        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );
        
        $app = JFactory::getApplication(); 
        $db = JFactory::getDBO();    
        
        // get path
        $db->SetQuery("SELECT * FROM #__jdownloads_files WHERE id = $id");
        $file = $db->loadObject();

        if ($file->preview_filename){
            $filename = $params->get('files_uploaddir').DS.$params->get('preview_files_folder_name').DS.$file->preview_filename;

            // check whether other downloads use also this preview file
            $db->SetQuery("SELECT count(*) FROM #__jdownloads_files WHERE preview_filename = '$file->preview_filename'");
            $result = $db->loadResult();
            
            if (!jFile::exists($filename) || $result > 1){
                // file not exist - but we must always clear the data field 
                // the same when other downloads used also this file
                $db->SetQuery("UPDATE #__jdownloads_files SET preview_filename = '' WHERE id = '$id'");
                $db->execute();
                return false; 
            } else {
                if (jFile::delete($filename)){
                    $db->SetQuery("UPDATE #__jdownloads_files SET preview_filename = '' WHERE id = '$id'");
                    $db->execute();                    
                    return true;
                } else {
                    // delete error
                    return false;
                }    
            }
        } else {
            // preview_filename field empty
            return false;
        }
    }        
    
    /* Remove a folder from the download area (categories folder)
     *
     * @param   string  $cat_dir  Only the given sub path from the DB cat_dir field 
     * 
     * @return error_msg
    */
    public static function deleteCategoryFolder($cat_dir){

        $params = JComponentHelper::getParams('com_jdownloads'); 
        jimport('joomla.filesystem.folder');   

        // Make sure that we do not delete the completely root folder 
        if (!$cat_dir){
            JError::raiseError(100, 'Category folder name missing - deletion not possible!');
        } else {    
            // Try to delete the category folder
            $dir = $params->get('files_uploaddir').'/'.$cat_dir;
            if (JFolder::exists($dir)){
                if (!JFolder::delete($dir)){
                    JError::raiseWarning(100, JText::sprintf('COM_JDOWNLOADS_BE_DEL_CATS_DIRS_ERROR', $dir));
                } else {
                    JError::raiseNotice(100, JText::sprintf('COM_JDOWNLOADS_BE_DEL_CATS_DIRS_OK', $dir));
                }
            } else {
                JError::raiseWarning(100, JText::sprintf('COM_JDOWNLOADS_BE_DEL_CATS_DIRS_ERROR', $dir));
            }
        }
    }    
    
    /**
    * Methode to move all folders, subfolders and files to a other folder
    * 
    * @param mixed $source
    * @param mixed $dest
    * @param mixed $recursive
    * @param mixed $message
    * @param mixed $delete_source             when true, are all files and folders in the source path deleted after moving
    * @param mixed $delete_dest               when true, are all files and folders in the destination path deleted after moving 
    * @param mixed $delete_only_files         when true and $delete_source or $delete_source is true, are only the files in the selected subfolders deleted, excepts index.html
    * 
    * @return error_msg
    */
    public static function moveDirs($source, $dest, $recursive = true, $message, $delete_source = true, $delete_dest = false, $delete_only_files = false) {

        $error = false;
        
        if (!is_dir($dest)) { 
            @mkdir($dest); 
          } 
     
        $handle = @opendir($source);
        
        if(!$handle) {
            $message = JText::_('COM_JDOWNLOADS_BACKEND_CATSEDIT_ERROR_CAT_COPY');
            return $message;
        }
        
        while ($file = @readdir ($handle)) {
            if ($file == '.' || $file == '..'){
                continue;
            }
            
            if(!$recursive && $source != $source.$file."/") {
                if(is_dir($source.$file))
                    continue;
            }
            
            if(is_dir($source.$file)) {
                self::moveDirs($source.$file."/", $dest.$file."/", $recursive, $message, $delete_source, $delete_dest, $delete_only_files );
            } else {
                if (!@copy($source.$file, $dest.$file)) {
                    $error = true;
                }
            }
        }
        @closedir($handle);
        
        // delete $source when not an error
        if (!$error){
            if ($delete_dest){
                $path = $dest;
            } else {
                $path = $source;
            }
            if ($delete_source || $delete_dest){
                if ($delete_only_files){
                    // delete all files and folders from the source path
                    $exceptions = array('index.html');
                    $res = self::delete_dir_and_allfiles ($path, false, $exceptions);    
                    if ($res) {
                        $message = JText::sprintf('COM_JDOWNLOADS_BACKEND_CATSEDIT_ERROR_CAT_DEL_AFTER_COPY', $path);
                    }
                } else {   
                    // delete all files and folders from the source path
                    $res = self::delete_dir_and_allfiles ($path);    
                    if ($res) {
                        $message = JText::sprintf('COM_JDOWNLOADS_BACKEND_CATSEDIT_ERROR_CAT_DEL_AFTER_COPY', $path);
                    }
                }    
            }    
        } else {
            $message = JText::_('COM_JDOWNLOADS_BACKEND_CATSEDIT_ERROR_CAT_COPY');
        }
        return $message;
    } 

    
    /**
     * This method checked an given folder or file name
     * 
     * @param   string  $str                String to process
     *          boolean $is_monitoring      Is true, when this method is used from the auto monitoring function
     * 
     * @return  string  Processed string
     */
    public static function getCleanFolderFileName($str, $is_monitoring = false)
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        if ($params->get('transliterate_at_first')){
            // When activated we use here a transliteration at first.  
	        // Here are replaced all accented UTF-8 characters by unaccented ASCII-7 "equivalents".
	        // We can not use here the Joomla 'transliterate' function, since the Joomla function changed the string to lowercase 
	        // So we use an own modified version
	        include_once dirname(__FILE__) . '/transliterate.php';
	        $str = JDTransliterate::utf8_latin_to_ascii($str);    
        }        
        
        // Clean here the strings and use the settings
        // Special hint: Users which want to use special characters (like: #()) in your Folder and/or Filenames should always deactivate the 'Use utf-8' option! 
        if ($is_monitoring && !$params->get('use_files_and_folder_settings_for_monitoring')){
            // for auto monitoring is used the 'fix.upload.filename.specials' option 
            // but not when the special option (above) is active
            $str = preg_replace('/(\s|[^A-Za-z0-9._\-])+/', ' ', $str); 
        } else {
            if ($params->get('use_unicode_path_names')){
                
                // Replace double byte whitespaces by single byte (East Asian languages)
                $str = preg_replace('/\xE3\x80\x80/', ' ', $str);

                // Replace forbidden characters by whitespaces
                $str = preg_replace('#[:\#\*"@+=;!><&\%()\]\/\'\\\\|\[]#', "\x20", $str);

                // Delete all '?'
                $str = str_replace('?', '', $str);
                
            } else {
                
                if ($params->get('fix_upload_filename_specials')){       

                    // Is only done when the utf-8 option is not activated        
                    // Remove any duplicate whitespace, and ensure all characters are alphanumeric
                    $str = preg_replace('/(\s|[^A-Za-z0-9._\-])+/', ' ', $str); 
                }              
            }
        }    
        // Trim white spaces at beginning and end of string
        $str = trim($str);
        
        // make lowercase when this option is activated
        if ($params->get('fix_upload_filename_uppercase')){
            $str = JString::strtolower($str);
        }          
        
        // Remove all whitespace
        if ($params->get('fix_upload_filename_blanks')){
            $str = str_replace(' ', '_', $str);
        }
        
        if (strlen($str) == 0){
            // we can not use an empty folder/filename
            // so we use the current date for it
            if ( '\\' === DIRECTORY_SEPARATOR ){
                // windows system can not store a : character in name
                $str = date("Y-m-d H-i-s");
            } else {
                $str = date("Y-m-d H:i:s");
            }    
        } 
        
        return $str;
    }    
    
      
   /**
    *  Method to get the title from a user group 
    * 
    *  @param int       user group id
    *
    *  @return string   title
    * 
    */    
    public static function getUserGroupInfos($group_id)
    {
        $db = JFactory::getDBO();
        $result = '';
        
        $query = $db->getQuery(true);
        $query->select('title');
        $query->from('#__usergroups');
        $query->where('id = '.(int)$group_id);
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }    
    
    /**
    *  Method to get the id from jDownloads component in the assets table
    * 
    *  @param 
    *
    *  @return int   id
    * 
    */
    public static function getAssetRootID()
    {    
        $db = JFactory::getDBO();
        $result = '';
        
        $query = $db->getQuery(true);
        $query->select('id');
        $query->from('#__assets');
        $query->where('name = '.$db->Quote('com_jdownloads'));
        $query->where('parent_id = '.$db->Quote('1'));
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;    
    }                                           
    
    /**
     * Build the array with all finded file informations
     * (Path, Folder name, File name, File size, last update date 
     *
     * @param		string	$dir 			path to the folder
     * @param		string	$file			contains the file name
     * @param		string	$onlyDir		contains only the folder name
     * @param		array	$type		    search pattern for file types
     * @param		bool	$allFiles	    find all files and used not file types filter
     * @param		array	$files		    contains the complete folder structure
     * @return	    array					the complete results array
     * 
     */
    public static function buildArray($dir,$file,$onlyDir,$type,$allFiles,$files) {

	    $typeFormat = FALSE;
	    foreach ($type as $item)
      {
  	    if (strtolower($item) == substr(strtolower($file), -strlen($item)))
			    $typeFormat = TRUE;
	    }

	    if($allFiles || $typeFormat == TRUE)
	    {
		    if(empty($onlyDir))
			    $onlyDir = substr($dir, -strlen($dir), -1);
		    $files[$dir.$file]['path'] = $dir;
		    $files[$dir.$file]['file'] = $file;
		    $files[$dir.$file]['size'] = self::fsize($dir.$file);
		    $files[$dir.$file]['date'] = filemtime($dir.$file);
	    }
	    return $files;
    }

    /**
    *  Get all folders with files from a given path
    *  But files with a single or double quote character in the filename are ignored for security reasons ! 
    * 
    * @param mixed $dir
    * @param mixed $type
    * @param mixed $only
    * @param mixed $allFiles
    * @param mixed $recursive
    * @param mixed $onlyDir
    * @param mixed $except_folders
    * @param mixed $jd_root
    * @param mixed $files
    * 
    * @return array
    */
    public static function scan_dir($dir, $type=array(), $only=FALSE, $allFiles=FALSE, $recursive=TRUE, $onlyDir="", $exclude_folders, $include_folders, $jd_root, &$files)
    {
        $len = strlen($jd_root);
        
        $handle = @opendir($dir);
        if(!$handle) return false;
        
        while ($file = @readdir ($handle)){
            
            if (!empty($include_folders)){
                if (is_dir($dir.$file)){
                    if (!in_array($dir.$file.'/', $include_folders)){
                        continue;
                    }
                } else {
                    
                }
                
            }
            
            // || strpos($file, "'") > 0 removed to can handle folders with single quote characters in the name
            if ($file == '.' || $file == '..' || $file == 'index.html' || strpos($file, '"') > 0 || in_array($dir, $exclude_folders)){                           
                continue;
            }
            
            if(!$recursive && $dir != $dir.$file."/"){
                if(is_dir($dir.$file)) 
                 continue;
            }
            
            if(is_dir($dir.$file)){
                self::scan_dir($dir.$file."/", $type, $only, $allFiles, $recursive, $file, $exclude_folders, $include_folders, $jd_root, $files);
            } else {
               if($only)
                 $onlyDir = $dir;
                 if ($dir != $jd_root){
                     $files = self::buildArray($dir,$file,$onlyDir,$type,$allFiles,$files);
                 }    
            }
        }
        
        @closedir($handle);
        return $files;
    }

    /**
    *  Get all folders and subfolders
    * 
    * @param mixed $path        path to browse
    * @param mixed $maxdepth    how deep to browse (-1=unlimited)
    * @param mixed $mode        "FULL"|"DIRS"|"FILES"
    * @param mixed $d           must not be defined
    * @param array $exclude_folders     
    * @param array $include_folders
    * 
    * @return array
    */
    public static function searchdir($path , $maxdepth = -1 , $mode = "DIRS" , $d = 0, $exclude_folders = array(), $include_folders = array())
    {
       
       if ( substr ( $path , strlen ( $path ) - 1 ) != '/' ){
            $path .= '/';
       }
       
       $dirlist = array () ;
       if ( $mode != "FILES" ) {
           if (!in_array($path, $exclude_folders)){
               if (!empty($include_folders)){
                    if (in_array($path, $include_folders) || self::findStringInArray($include_folders, $path)){
               $dirlist[] = $path ;
           }    
               } else {
                   $dirlist[] = $path;
               }   
           }    
       }
       if ( $handle = opendir ( $path ) ) {
           while ( false !== ( $file = readdir ( $handle ) ) ) {
               if ( $file != '.' && $file != '..' && substr($file, 0, 1) !== '.') {
                   $file = $path . $file ;
                   if ( ! is_dir ( $file ) ) {
                      if ( $mode != "DIRS" ) {
                       $dirlist[] = $file ;
                      }
                    } elseif ( $d >=0 && ($d < $maxdepth || $maxdepth < 0) ) {
                       $result = self::searchdir ( $file . '/' , $maxdepth , $mode , $d + 1, $exclude_folders, $include_folders );
                       $dirlist = array_merge ( $dirlist , $result ) ;
                   }
               }
           }
           closedir ( $handle ) ;
       }
       if ( $d == 0 ) { 
           natcasesort($dirlist);
           $dirlist = array_values($dirlist);
       }
       return ( $dirlist ) ;
   }

    /**
    * Delete a folder with all files and subfolders 
    * 
    * @param mixed $path           the path to the folder
    * @param mixed $delete_folder  true, when the folder shall also be deleted
    * @return mixed 
    * RESULTS:
    *   0 - ok
    *  -1 - no folder
    *  -2 - delete error
    *  -3 - a item was not a file/folder/Link
    */
    public static function delete_dir_and_allfiles ( $path, $delete_folder = true, $exceptions = array() ) {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');    

        if (!is_dir ($path)) {
            return -1;
        }
        $dir = @opendir ($path);
        if (!$dir) {
            return -2;
        }

        while (($entry = @readdir($dir)) !== false) {
            if ($entry == '.' || $entry == '..' || in_array($entry, $exceptions)) continue;
            if (is_dir ($path.'/'.$entry)) {
                $res = self::delete_dir_and_allfiles ($path.'/'.$entry, $delete_folder, $exceptions);
                // manage errors
                if ($res == -1) {
                    @closedir ($dir); 
                    return -2; 
                } else if ($res == -2) {
                    @closedir ($dir); 
                    return -2; 
                } else if ($res == -3) {
                    @closedir ($dir); 
                    return -3; 
                } else if ($res != 0) { 
                    @closedir ($dir); 
                    return -2; 
                }
            } else if (is_file ($path.'/'.$entry) || is_link ($path.'/'.$entry)) {
                // delete file
                $res = JFile::delete($path.'/'.$entry);
                if (!$res) {
                    @closedir ($dir);
                    return -2; 
                }
            } else {
                @closedir ($dir);
                return -3;
            }
        }
        @closedir ($dir);
        
        // delete dir when defined
        if ($delete_folder && !$exceptions){
            $res = JFolder::delete($path);
            if (!$res) {
                return -2;
            }
        }
            
        return 0;
    }

    // get the value from a given downloads 'file date' field
    public static function getFieldDataFromDownload($id, $fieldname){
        $db = JFactory::getDBO();
        $db->setQuery("SELECT $fieldname FROM #__jdownloads_files WHERE id = '$id'");
        $value = $db->loadResult();
        return $value;        
    }
    
    /**
    * remove the language tag from a given text and return only the text
    *    
    * @param string     $msg
    */
    public static function getOnlyLanguageSubstring($msg)
    {
        // Get the current locale language tag
        $lang_key   = self::getLangKey();        
        
        // remove the language tag from the text
        $startpos = strpos($msg, '{'.$lang_key.'}') +  strlen( $lang_key) + 2 ;
        $endpos   = strpos($msg, '{/'.$lang_key.'}') ;
        
        if ($startpos !== false && $endpos !== false){
            return substr($msg, $startpos, ($endpos - $startpos ));
        } else {    
            return $msg;
        }    
    }
    
    /**
    * get the current used 'locale' language key 
    *    
    * @return string
    */    
    public static function getLangKey()
    {
        $lang        = JFactory::getLanguage();
        $locale      = $lang->getLocale();
        $lang_code   = null;

        if(empty($locale)){
            $lang_code = 'en-GB';
        } else {
            $lang_tag   = $locale[0];
            $lang_data  = explode('.', $lang_tag);
            $lang_code  = JString::str_ireplace('_', '-', $lang_data[0]);
        }
        return $lang_code;    
    }    
    
    
    /**
    * Rename older language files before we start the update to 2.5/3.x 
    * 
    * 
    *     
    */
    public static function renameOldLanguageFiles($dir)
    {
        
        if ($handle = dir($dir)) {
            while (false !== ($file = $handle->read())) {
                if (!is_dir($dir.'/'.$file)) {
                      if (strpos($file, 'com_jdownloads') !== false){
                           if (strpos($file, 'en-GB') === false && strpos($file, '.old') === false){ 
                               @rename("$dir/$file", "$dir/$file".'.old');
                           }    
                      }
                } elseif (is_dir($dir.'/'.$file) && $file != '.' && $file != '..') {
                    self::renameOldLanguageFiles($dir.'/'.$file);
                }
            }
            $handle->close();
        }       
    }
    
    /**
     * Method to get the correct db prefix (problem with getTablelist() which always/sometimes has lowercase prefix names in array)
     *
     * @return string
     */
    public static function getCorrectDBPrefix() 
    {
        $db = JFactory::getDBO();

        // get DB prefix string and table list
        $prefix     = $db->getPrefix();
        $prefix_low = strtolower($prefix);
        $tablelist  = $db->getTableList();

        if (!in_array ( $prefix.'assets', $tablelist)) {
            if (in_array ( $prefix_low.'assets', $tablelist)) {
                return $prefix_low;
            } else {
                // assets table not found? 
                return '';
            } 
        } else {
            return $prefix;
        }        

    }
    
    /**
    * Converts a string into Float while taking the given or locale number format into account
    * Used as default the defined separator characters from the Joomla main language ini file (as example: en-GB.ini)  
    * 
    * @param mixed $str
    * @param mixed $dec_point
    * @param mixed $thousands_sep
    * @param mixed $decimals
    * @return mixed
    */
    public static function strToNumber( $str, $dec_point=null, $thousands_sep=null, $decimals = 0 )
    {
        if( is_null($dec_point) || is_null($thousands_sep) ) {
            if( is_null($dec_point) ) {
                $dec_point = JText::_('DECIMALS_SEPARATOR');
            }
            if( is_null($thousands_sep) ) {
                $thousands_sep = JText::_('THOUSANDS_SEPARATOR');
            }
        }
        // in this case use we as default the en-GB format
        if (!$dec_point || $dec_point == 'DECIMALS_SEPARATOR') $dec_point = '.'; 
        if (!$thousands_sep || $thousands_sep == 'THOUSANDS_SEPARATOR') $thousands_sep = ',';
        
        // we will not round a value so we must check it
        if (is_numeric($str) && !is_int($str) && !is_double($str) && $decimals == 0){
            $decimals = 2;
        }         

        $number = number_format($str, $decimals, $dec_point, $thousands_sep);
        return $number;
    }    
    
    /**
    * Compute which date format shall be used for the output
    * 
    * @return mixed
    */
    public static function getDateFormat(){
        
        $params = JComponentHelper::getParams('com_jdownloads');
        
        $format = array();
        
        // check at first the long format 
        // when defined get the format from the current language
        if ($params->get('global_datetime')){
            $format['long'] = self::getOnlyLanguageSubstring($params->get('global_datetime'));
            if (!$format['long']){
                $format['long'] = JText::_('DATE_FORMAT_LC2');
            }
        } else {
            // format is not defined in params so we use a standard format from the language file (LC2)
            $format['long'] = JText::_('DATE_FORMAT_LC2');
        }

        // check now the short format field
        // when defined get the format from the current language
        if ($params->get('global_datetime_short')){
            $format['short'] = self::getOnlyLanguageSubstring($params->get('global_datetime_short'));
            if (!$format['short']){
                $format['short'] = JText::_('DATE_FORMAT_LC4');
            }            
        } else {
            // format is not defined in params so we use a standard format from the language file (LC4)
            $format['short'] = JText::_('DATE_FORMAT_LC4');
        }

        return $format;    
    } 
    
    /**
     * Show the feature/unfeature links
     *
     * @param   int      $value      The state value
     * @param   int      $i          Row number
     * @param   boolean  $canChange  Is user allowed to change?
     *
     * @return  string       HTML code
     */
    public static function getFeatureHtml($value = 0, $i, $canChange = true)
    {
        JHtml::_('bootstrap.tooltip');

        // Array of image, task, title, action
        $states = array(
            0 => array('unfeatured', 'downloads.featured', 'COM_JDOWNLOADS_UNFEATURED', 'COM_JDOWNLOADS_TOGGLE_FEATURED'),
            1 => array('featured', 'downloads.unfeatured', 'COM_JDOWNLOADS_FEATURED', 'COM_JDOWNLOADS_TOGGLE_FEATURED'),
        );
        $state = ArrayHelper::getValue($states, (int) $value, $states[1]);
        $icon  = $state[0];

        if ($canChange)
        {
            $html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
                . ($value == 1 ? ' active' : '') . '" title="' . JHtml::tooltipText($state[3]) . '"><span class="icon-' . $icon . '"></span></a>';
        }
        else
        {
            $html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
                . JHtml::tooltipText($state[2]) . '"><span class="icon-' . $icon . '"></span></a>';
        }

        return $html;
    }
    
    /**
     * Remove invalid parts from an e-mail addresses list
     *
     * @param   string   $string     The addresses list
     *
     * @return  string   $string     The cleaned addresses list
     */
    public static function cleanEMailAddresses($string = '')
    {
        // check email addresses
        if ($string){
            $checked_list = '';
            $addresses = explode(';', $string);
            foreach ($addresses as $address){
                if (filter_var($address, FILTER_VALIDATE_EMAIL)){
                    $checked_list .= $address.';';
                }    
            }
            $string = implode(';', explode(';', $checked_list, -1));
        }
        return $string;
    } 
    
    /**
     * Build the footer information part for the backend
     *
     * @param   string   $align      direction
     *
     * @return  string   $footer     the formatted 'info' text block
     */    
    public static function buildBackendFooterText($align = 'center')
    {
        $copy_year = "2007 - ".date('Y');

        $file = 'jdownloads.xml';
        $xml = simplexml_load_file(JPATH_COMPONENT.'/'.$file);
        
        $footer = '<div class="clearfix"></div><br /><div align="'.$align.'">
        <span><b>jDownloads Version '.$xml->version.'</a> </b> &copy; '.$copy_year.' - Arno Betz <a href="http://www.jdownloads.com" target="_blank">www.jdownloads.com</a><br /><a href="http://www.jdownloads.com/index.php/downloads/download/6-jdownloads/2-jdownloads-3-2.html" target="_blank" >Download</a> 
        | <a href="http://www.jdownloads.com/index.php/support-forum.html" target="_blank" >Support Forum</a> | <a href="http://www.jdownloads.net/documentations" target="_blank">Documentation</a></span></div>';
        return $footer;
    }
    
     /**
     * Show the feature/unfeature links
     *
     * @param   integer  $value      The state value
     * @param   integer  $i          Row number
     * @param   boolean  $canChange  Is user allowed to change?
     *
     * @return  string       HTML code
     */
    public static function setFeatured($value = 0, $i, $canChange = true)
    {
        JHtml::_('bootstrap.tooltip');

        // Array of image, task, title, action
        $states = array(
            0 => array('unfeatured', 'downloads.featured', 'COM_JDOWNLOADS_UNFEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
            1 => array('featured', 'downloads.unfeatured', 'COM_JDOWNLOADS_FEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
        );
        $state = JArrayHelper::getValue($states, (int) $value, $states[1]);
        $icon  = $state[0];

        if ($canChange)
        {
            $html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
                . ($value == 1 ? ' active' : '') . '" title="' . JHtml::tooltipText($state[3]) . '"><span class="icon-' . $icon . '"></span></a>';
        }
        else
        {
            $html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
                . JHtml::tooltipText($state[2]) . '"><span class="icon-' . $icon . '"></span></a>';
        }

        return $html;
    }
	
    /**
     * Sanitize a given url value - remove dangerous characters
     *
     * @param   string   $value    value
     *
     * @return  string   $value    the sanitized value
     */    
    public static function sanitizeUrlParam($value = '')
    {
    
         $value = str_replace('/', '', $value);
         $value = str_replace('\\', '', $value);
         $value = str_replace('..', '', $value);
         $value = str_ireplace('%255c', '', $value);
         $value = str_ireplace('%%35c', '', $value);
         $value = str_ireplace('%%35%63', '', $value);         
         $value = str_ireplace('%25%35%63', '', $value);
         $value = str_ireplace('%2f', '', $value);
         $value = str_ireplace('%5c', '', $value);
         $value = str_ireplace('%2e%2e', '', $value);
         
         return $value;
    }
	
    /**
     * Adds Count Items for Tag Manager.
     *
     * @param   stdClass[]  &$items     The content objects
     * @param   string      $extension  The name of the active view.
     *
     * @return  stdClass[]
     *
     * @since   3.6
     */
    public static function countTagItems(&$items, $extension)
    {
        $db = JFactory::getDbo();
        $parts     = explode('.', $extension);
        $section   = null;

        if (count($parts) > 1){
            $section = $parts[1];
        }

        $join  = $db->qn('#__jdownloads_files') . ' AS c ON ct.content_item_id = c.id';

        if ($section === 'category'){
            $join = $db->qn('#__jdownloads_categories') . ' AS c ON ct.content_item_id = c.id';
        }

        foreach ($items as $item)
        {
            $item->count_trashed = 0;
            $item->count_archived = 0;
            $item->count_unpublished = 0;
            $item->count_published = 0;
            
            // Only required for jD admin stats module
            $item->count_cat      = 0;
            $item->count_download = 0; 
            
            $query = $db->getQuery(true);
            
            $query->select('published, count(*) AS count')
                ->from($db->qn('#__contentitem_tag_map') . 'AS ct ')
                ->where('ct.tag_id = ' . (int) $item->id)
                ->where('ct.type_alias =' . $db->q($extension))
                ->join('LEFT', $join)
                ->group('published');
            
            $db->setQuery($query);
            $contents = $db->loadObjectList();

            foreach ($contents as $content){
                if ($content->published == 1){
                    $item->count_published = $content->count;
                }

                if ($content->published == 0){
                    $item->count_unpublished = $content->count;
                }
                
                // Only required for the jD admin stats module
                if ($extension == 'com_jdownloads.category'){
                    $item->count_cat = $content->count;
                } 
                
                if ($extension == 'com_jdownloads.download'){
                    $item->count_download = $content->count;
                }
            }
        }

        return $items;
    }  	
	
    /**
     * Get link to an item of given content type for user action log
     *
     * @param   string   $contentType
     * @param   integer  $id
     *
     * @return  string  Link to the content item
     *
     */
    public static function getContentTypeLink($contentType, $id)
    {
        
        switch ($contentType)
        {
            case ('download'):
            return 'index.php?option=com_jdownloads&task=download.edit&id=' . $id;    
            break;
            
            case ('category'):
            return 'index.php?option=com_jdownloads&task=category.edit&id=' . $id;
            break;
            
            case ('license'):
            return 'index.php?option=com_jdownloads&task=license.edit&id=' . $id;    
            break;
            
            case ('group'):
            return 'index.php?option=com_jdownloads&task=group.edit&id=' . $id;    
            break;
            
            case ('template'):
            return 'index.php?option=com_jdownloads&task=template.edit&id=' . $id;    
            break;
            
            default:
            return 'index.php?option=com_jdownloads';
            break;    
            
        }
    } 
	
    public static function findStringInArray(array &$array, $text) { 
        
        $keys = []; 
        
        foreach ($array as $key => &$value) { 
            if (strpos($text, $value) !== false) { 
                $keys[] = $key; 
            } 
        } 
        return $keys; 
    }    
	
    public static function existsHelpServerURL($help_url)
    {
        
	    $file_headers = @get_headers($help_url);
	    
	    if (!$file_headers || $file_headers[0] != 'HTTP/1.1 200 OK') {
	        return false;
	    } else {
	        return $help_url;
	    }
    }
    
    public static function url_check($url) 
    { 
        $hdrs = @get_headers($url); 
        return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/',$hdrs[0]) : false; 
    }
    
    /**
     * Gets a list of associations for a given category item.
     *
     * @param   integer  $pk         Content item key.
     *
     * @return  array of associations.
     */
    public static function getCatAssociations($pk, $extension = '')
    {
        $langAssociations = JLanguageAssociations::getAssociations($extension, '#__jdownloads_categories', 'com_jdownloads.category.item', $pk, 'id', 'alias', '');
        $associations     = array();
        $user             = JFactory::getUser();
        $groups           = implode(',', $user->getAuthorisedViewLevels());

        foreach ($langAssociations as $langAssociation)
        {
            // Include only published categories with user access
            $arrId    = explode(':', $langAssociation->id);
            $assocId  = $arrId[0];

            $db    = \JFactory::getDbo();

            $query = $db->getQuery(true)
                ->select($db->qn('published'))
                ->from($db->qn('#__jdownloads_categories'))
                ->where('access IN (' . $groups . ')')
                ->where($db->qn('id') . ' = ' . (int) $assocId);

            $result = (int) $db->setQuery($query)->loadResult();

            if ($result === 1)
            {
                $associations[$langAssociation->language] = $langAssociation->id;
            }
        }

        return $associations;
    }
	
    /**
     * Method to shorten a passed text to a certain length - with UTF8 compatibility
     * If we have more than one word, we do not cut the string within a word.
     *
     * @param   string  $string     Text string
     * 
     *          integer $lenght     Amount of characters
     * 
     *          boolean $end_char   True to add '...' at the shortened text (default)           
     *
     * @return  string  $result
     * 
     * Deprecated in 3.9 - use JHtml::_('string.truncate'...) instead
     */
    public static function shortText($string, $lenght, $end_char = true) 
    {
        if (StringHelper::strlen($string) > $lenght){
            
            $amount_blanks = StringHelper::strpos($string, ' ');
            
            // The string must have minimum 2 words otherwise we get an empty string
            if ($amount_blanks > 0){
                $result = StringHelper::substr ( $string, 0, StringHelper::strrpos ( $string, ' ', - ( StringHelper::strlen ( $string ) - $lenght ) ) ); 
            } else {
                // The string has only a single word or is an URL or something else
                $result = StringHelper::substr($string, 0, $lenght); 
            }

            // Add the 'suspension points' when required 
            if ($end_char){
                $result .= '...';
        }
                            
        } else {
        return $string;
        }
        return $result;
    }
    
    /**
    * Method to return the path to the activated file type icon set
    * 
    * @return string $file_pic_folder with the path
    */
    public static function getFileTypeIconPath($selected_icon_set)
    {
        // Path to the mime type image folder (for file symbols) 
        switch ($selected_icon_set)
        {
            case 2:
                $file_pic_folder = JPATH_SITE.'/images/jdownloads/fileimages/flat_1/';
                break;
            case 3:
                $file_pic_folder = JPATH_SITE.'/images/jdownloads/fileimages/flat_2/';
                break;
            default:
                $file_pic_folder = JPATH_SITE.'/images/jdownloads/fileimages/';
                break;
        }
        return $file_pic_folder;
    }

	
    
}?>