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

defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

setlocale(LC_ALL, 'C.UTF-8', 'C');
 
jimport('joomla.database.table');
 
/**
 * Downloads (files) Table class
 */
class JdownloadsTableDownload extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(&$db) 
	{
        parent::__construct('#__jdownloads_files', 'id', $db);
        // we need also the 'tags' functionality
        JTableObserverTags::createObserver($this, array('typeAlias' => 'com_jdownloads.download'));        
	}
    
/**
     * Overloaded check method to ensure data integrity.
     *
     * @return    boolean    True on success.
     */
    public function checkData($isNew, $auto_added = false)
    {
        $params = JComponentHelper::getParams('com_jdownloads');

        jimport( 'joomla.filesystem.file ');
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.html.html' );

        $user = JFactory::getUser();
        $db   = JFactory::getDBO();
        $app  = JFactory::getApplication();
        
        // Verify that the alias is unique
        $table = JTable::getInstance('download', 'jdownloadsTable', array('dbo' => $this->getDbo()));
        if ($table->load(array('alias' => $this->alias, 'catid' => $this->catid)) && ($table->id != $this->id || $this->id == 0)) {
            if ($auto_added){
                $this->alias .= mt_rand(100,999);    
            } else {
                $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_ERROR_DOWNLOAD_UNIQUE_ALIAS'));
                return false;
            }
        }
        
        // we neeed the jform data
        $jinput = JFactory::getApplication()->input;
        $formdata = $jinput->get('jform', array(),'array');

        // we neeed also the jform files data
        $jFileInput = new JInput($_FILES);
        $files = $jFileInput->get('jform',array(),'array');
        
        $default_access_value_used = false;
        $preview_filename_renamed  = false;
        
        // Get the right path
        $file_pic_folder_path = JDownloadsHelper::getFileTypeIconPath($params->get('selected_file_type_icon_set'));
        
        // doing the next part only when we have a new download creation or an editing in frontend 
        if ($app->isClient('site') && !$auto_added){
            $user_rules = JDHelper::getUserRules();        
            
            // we must check some from the required fields manually, which are not checked with javascript
            if ($this->catid == 0)                                                                                                 $this->setError(JText::_('COM_JDOWNLOADS_REQUIRED_CATEGORY')); 
            if ($user_rules->form_changelog && $user_rules->form_changelog_x && $this->changelog == '')                             $this->setError(JText::_('COM_JDOWNLOADS_REQUIRED_CHANGELOG')); 
            if ($user_rules->form_short_desc && $user_rules->form_short_desc_x && $this->description == '')                         $this->setError(JText::_('COM_JDOWNLOADS_REQUIRED_SHORT_DESC')); 
            if ($user_rules->form_long_desc && $user_rules->form_long_desc_x && $this->description_long == '')                      $this->setError(JText::_('COM_JDOWNLOADS_REQUIRED_LONG_DESC')); 
            if ($user_rules->form_license && $user_rules->form_license_x && !$this->license)                                        $this->setError(JText::_('COM_JDOWNLOADS_REQUIRED_LICENSE')); 
            if ($user_rules->form_creation_date && $user_rules->form_creation_date_x && $this->created == '0000-00-00 00:00:00') $this->setError(JText::_('COM_JDOWNLOADS_REQUIRED_DATE_ADDED')); 
            if ($user_rules->form_file_date && $user_rules->form_file_date_x && $this->file_date == '0000-00-00 00:00:00')          $this->setError(JText::_('COM_JDOWNLOADS_REQUIRED_DATE_FILE')); 
            if ($user_rules->form_file_language && $user_rules->form_file_language_x && !$this->file_language)                      $this->setError(JText::_('COM_JDOWNLOADS_REQUIRED_LANGUAGE_FILE')); 
            if ($user_rules->form_file_system && $user_rules->form_file_system_x && !$this->system)                                 $this->setError(JText::_('COM_JDOWNLOADS_REQUIRED_SYSTEM_FILE')); 
            if ($params->get('use_file_type_symbols')){
            if ($user_rules->form_file_pic && $user_rules->form_file_pic_x && !$this->file_pic)                                     $this->setError(JText::_('COM_JDOWNLOADS_REQUIRED_PIC_FILE')); 
            }    
            
            // we need the total amount of selected image files
            $thumb_image_files = $jFileInput->get('file_upload_thumb',array(),'array');
            $amount_selected_thumbs_files = count($thumb_image_files['name']);
            foreach ($thumb_image_files['name'] as $name){
                if (!$name) $amount_selected_thumbs_files--; 
            }
            if ($user_rules->form_images && $user_rules->form_images_x && !$amount_selected_thumbs_files)                           $this->setError(JText::_('COM_JDOWNLOADS_REQUIRED_IMAGES'));             
            
            // break when we have before found a invalid data field
            if ($this->getErrors()){
                     return false;                
            }
            
            // make sure that all date fields has a correct datetime format
            if ($this->created){
                $date_dum = new DateTime($this->created);             
                $this->created = $date_dum->format('Y-m-d H:i:s'); 
            }
            
            if ($this->file_date){
                $date_dum = new DateTime($this->file_date);             
                $this->file_date = $date_dum->format('Y-m-d H:i:s'); 
            }
            
            if ($this->publish_up){
                $date_dum = new DateTime($this->publish_up);             
                $this->publish_up = $date_dum->format('Y-m-d H:i:s'); 
            }
            
            if ($this->publish_down){
                $date_dum = new DateTime($this->publish_down);             
                $this->publish_down = $date_dum->format('Y-m-d H:i:s'); 
            }
            
            if ($this->modified){
                $date_dum = new DateTime($this->modified);             
                $this->modified = $date_dum->format('Y-m-d H:i:s'); 
            }

            // check the file extension when frontend upload
            if ($files['tmp_name']['file_upload'] != '' || $files['name']['file_upload'] != ''){
                $file_extension = JFile::getExt($files['name']['file_upload']);
                $user_file_types = explode(',',strtolower($user_rules->uploads_allowed_types));
                if (!in_array(strtolower($file_extension), $user_file_types)){                
                     // error - user have tried to upload a not allowed file type
                     $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_INVALID_FILE_TYPE'));
                     return false;  
                }
                
                // check allowed file size
                if ( ($files['size']['file_upload'] > ($user_rules->uploads_maxfilesize_kb * 1024)) ||
                     ($files['name']['file_upload'] != '' && $files['size']['file_upload'] == 0) )
                {
                     // error - user have tried to upload a to big file
                     $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_INVALID_FILE_SIZE'));
                     return false;  
                }                
            }
            
            // check the file extension when frontend preview file upload
            if ($files['tmp_name']['preview_file_upload'] != '' || $files['name']['preview_file_upload'] != ''){
                $file_prev_extension = JFile::getExt($files['name']['preview_file_upload']);
                $user_preview_file_types = explode(',',$user_rules->uploads_allowed_preview_types);
                if (!in_array($file_prev_extension, $user_preview_file_types)){
                     // error - user have tried to upload a not allowed file type
                     $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_INVALID_PREVIEW_FILE_TYPE'));
                     return false;  
                }
                
                // check allowed file size
                if ( ($files['size']['preview_file_upload'] > ($user_rules->uploads_maxfilesize_kb * 1024)) ||
                     ($files['name']['preview_file_upload'] != '' && $files['size']['preview_file_upload'] == 0) )
                {
                     // error - user have tried to upload a to big file
                     $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_INVALID_FILE_SIZE'));
                     return false;  
                }
            }
            
            // check the access handling
            // check at first whether a single user is selected for access
            if ($this->user_access){
                // get the user access levels from the assigned user
                $levels = JAccess::getAuthorisedViewLevels((int)$this->user_access);
                // check and update the selected access field value when he exist not in the users levels or are invalid
                if (!in_array($this->access, $levels) || $this->access == 1 || $this->access == 5 ){
                    $this->access = max($levels);
                }
            } else {
                // no single user selected - so we use the normal check
                if ($user_rules->form_access == 0){
                    // the access select field was not viewed so we use the default value when exist
                    if ($user_rules->uploads_default_access_level){
                        $this->access = (int)$user_rules->uploads_default_access_level;
                        $default_access_value_used = true;
                    }    
                } else {
                    // the access select field was viewed
                    if ($this->access > 1){
                        // user has selected a special access level so we do not use the access value from parent category
                        $default_access_value_used = true;
                    }
                }
            }
        }
        
        // this part is always used
        if ($this->catid > 1){
            if ($isNew && !$default_access_value_used){
                if ($this->access == 1){
                    // user has not selected an special access level so we use the access value from parent category
	                $query = "SELECT * FROM #__jdownloads_categories WHERE id = '$this->catid'";
	                $db->setQuery( $query );
	                $parent_cat = $db->loadObject();
	                $this->access = $parent_cat->access;
                }
            }
        }         
        
        // we need the rest only when the new item is not added by monitoring !!!
        if (!$auto_added){
            
            if (!$app->isClient('site')){
                // this part is only required when the Download was not edited in frontend (otherwise is the check above already done)
                // check at first whether a single user is selected for access
                if ($this->user_access){
                    // get the user access levels from the assigned user
                    $levels = JAccess::getAuthorisedViewLevels((int)$this->user_access);
                    // check and update the selected access field value when he exist not in the users levels or are invalid
                    if (!in_array($this->access, $levels) || $this->access == 1 || $this->access == 5 ){
                        $this->access = max($levels);
                    }
                }
            }
            
            // get the uploaded image files
            $imagefiles = $jFileInput->get('file_upload_thumb',array(),'array');        
            
            $movedmsg           = '';
            $errormsg           = '';
            $cat_dir_org        = '';
            $filename_org       = '';
            $marked_cat_id      = '';
            $file_cat_changed   = false;
            $invalid_filename   = false;
            $thumb_created      = false;
            $image_created      = false;
            $image_thumb_name   = '';
            $filename_renamed   = false;
            $filename_new_name  = '';
            $filename_old_name  = '';
            $use_xml_for_file_info = 0;
            $selected_updatefile = 0;
            $file_pic_checked_already = false;                      

            // use xml install file to fill the file informations    
            if (isset($formdata['use_xml'])){
                $use_xml_for_file_info = (int)$formdata['use_xml'];
            }
            
            // marked cat id
            if (isset($formdata['catid'])){
                $marked_cat_id = (int)$formdata['catid'];
            } else {
                // is download added about jdownloadsModeldownload::createDownload() ?
                if ($this->catid > 0){
                    $marked_cat_id = (int)$this->catid;
                }    
            }   

            // prior marked cat id
            $cat_dir_org  = $jinput->get('cat_dir_org', 0, 'integer');

            // original filename changed?
            $filename_org = $jinput->get('filename_org', '', 'string');
            if (!$isNew && $filename_org != '' && $formdata['url_download'] != '' && $filename_org != $formdata['url_download'] ){
                $filename_renamed = true;
                $filename_new_name = $formdata['url_download'];
                $filename_old_name = $filename_org;
            }

            // original preview filename changed?
            $preview_filename_org = $jinput->get('preview_filename_org', '', 'string');
            if (!$isNew && $preview_filename_org != '' && $formdata['preview_filename'] != '' && $preview_filename_org != $formdata['preview_filename'] ){
                $preview_filename_renamed = true;
                $preview_filename_new_name = $formdata['preview_filename'];
                $preview_filename_old_name = $preview_filename_org;
            }        
            
            // get selected file from server for update download?
            if (isset($formdata['update_file'])){
                $selected_updatefile = $formdata['update_file'];
            }

            // When download is new created in frontend, we must do some other things... 
            if ($app->isClient('site') && !$auto_added){
                if ($isNew){
                    $this->submitted_by   = $user->id;
                    if ($user_rules->uploads_auto_publish == 1){
                        $this->published = 1;
                    }
                    if ($params->get('use_alphauserpoints') && $this->published == 1){
                        // add the AUP points
                        JDHelper::setAUPPointsUploads($this->submitted_by, $this->title);
                    }
                } else {
                    if ($params->get('use_alphauserpoints') && $this->published == 1){
                        // add the AUP points when an older download is published (maybe the first time)
                        JDHelper::setAUPPointsUploads($this->submitted_by, $this->title);
                    }
                }
            } else {    
                $this->set_aup_points = $jinput->get('set_aup_points', 0, 'integer');
                $this->submitted_by   = $jinput->get('submitted_by', 0, 'integer');
            }    
            
            $this->extern_file    = $formdata['extern_file'];
            
            $this->url_home   = $formdata['url_home'];
            $this->url_author = $formdata['url_author'];
            $this->author     = $formdata['author'];
            
            $this->mirror_1    = $formdata['mirror_1'];
            $this->mirror_2    = $formdata['mirror_2'];
            $this->extern_site = (int)$formdata['extern_site'];
            $this->extern_site_mirror_1 = (int)$formdata['extern_site_mirror_1'];
            $this->extern_site_mirror_2 = (int)$formdata['extern_site_mirror_2'];
            
            // check for valid name
            if (trim($this->title) == '') {
                $this->setError(JText::_('COM_JDOWNLOADS_TITLE_NOT_SET'));
                return false;
            }
            
            // check date, user id fields and catid
            if (!$isNew){
                // old download changed
                // set user id in modified field
                $this->modified_by = $user->id; 

                // fill out modified date field
                // get first the old date and compare it with the current value from the form
                // when user has self changed the date value - so we do not change it here
                // otherwise use we the current date and time 
                $modified_date_old = $jinput->get('modified_date_old', '', 'string'); 
                
                if ($this->modified == "") $this->modified = "0000-00-00 00:00:00";
                
                if ($modified_date_old == $this->modified){ 
                    $this->modified = JFactory::getDate()->toSql();
                }   
                
                if ($cat_dir_org != $marked_cat_id){
                      $file_cat_changed = true;
                      $this->catid = $marked_cat_id;
                }
            } else {
                // fill out created date field 
                $this->created = JFactory::getDate()->toSql();
                if (!$this->created_by){
                    // we use only automaticly the user ID when the user list is not listed in the form (this is selectable in the jD User Groups Settings)
                    if (isset($user_rules->form_created_id)){
                    if (!$user_rules->form_created_id){
                        $this->created_by = $user->id;    
                    }
                    } else {
                        $this->created_by = $user->id;    
                    }                    
                }    
            }        
            
            // get the selected categories folder name, when it is not uncategorised selected
            if ($marked_cat_id > 1){
                $db->SetQuery("SELECT cat_dir, cat_dir_parent FROM #__jdownloads_categories WHERE id = $marked_cat_id");
                $stored_catdir = $db->loadObject();
                if (!$stored_catdir){
                    $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_CAT_DIR_NOT_EXIST'));
                    return false;
                } else {
                    // build the complete stored category path
                    if ($stored_catdir->cat_dir_parent != ''){
                        $mark_catdir = $stored_catdir->cat_dir_parent.DS.$stored_catdir->cat_dir;
                    } else {
                        $mark_catdir = $stored_catdir->cat_dir;
                    }
                }
            } 
            
            // when we will use a file from another download, we must delete first the old file when it exist
            // the same, when we will use a file from the server
            if ($this->other_file_id > 0 && $this->url_download != '' || $selected_updatefile > 0 && $this->url_download != ''){
               $path = $params->get('files_uploaddir').'/'.$mark_catdir.'/'.$this->url_download;
               if (JFile::exists($path)) {
                   JFile::delete($path);
               }
               $this->url_download = ''; 
            }
            
            $this->description = rtrim(stripslashes($this->description));
            $this->description_long = rtrim(stripslashes($this->description_long));
            
            if ($this->id){    
                // get filesize and date if no value set
                if ($formdata['size'] != '' && $formdata['size'] != $this->size && $files['tmp_name']['file_upload'] == '' && !$file_cat_changed){
                    // user had changed the size manually
                    $this->size = JFilterInput::getInstance(null, null, 1, 1)->clean($formdata['size'], 'STRING');
                }
                if (!(int)$this->size > 0 && $files['tmp_name']['file_upload'] == '' && !$file_cat_changed) {
                    if ($this->url_download) {
                           $filepath = $params->get('files_uploaddir').'/'.$mark_catdir.'/'.$this->url_download;
                           if (JFile::exists($filepath)) {
                               $this->size = jdownloadsHelper::fsize($filepath);
                           }
                    } elseif ($this->extern_file != '') {
                            // get extern file size
                            $this->size = jdownloadsHelper::urlfilesize($this->extern_file,'b');
                    } elseif ($this->other_file_id > 0){
                            // use file from other download - get the size from it
                            $this->size = jdownloadsHelper::getFieldDataFromDownload($this->other_file_id, 'size');
                    }   
                }

                // is date empty get filedate - only for intern linked files
                if ($this->url_download){
                    if (empty($this->created) and $files['tmp_name']['file_upload'] == '' and !$file_cat_changed) {
                          $this->created = date("Y-m-d H:i:s", filemtime($params->get('files_uploaddir').'/'.$mark_catdir.'/'.$this->url_download));
                    }
                } elseif ($this->extern_file != '') {
                          // is extern file - try to get the data
                          if (empty($this->created) and $files['tmp_name']['file_upload'] == '' and !$file_cat_changed) {
                             $this->created = jdownloadsHelper::urlfiledate($this->extern_file);
                             $this->size = jdownloadsHelper::urlfilesize($this->extern_file,'b');
                          }
                }  elseif ($this->other_file_id > 0){
                            // use file from other download - get the date from it
                            $this->file_date = jdownloadsHelper::getFieldDataFromDownload($this->other_file_id, 'file_date');
                }             
            } else {
                if (!(int)$this->size > 0 && $files['tmp_name']['file_upload'] == '' && !$file_cat_changed) {
                    if ($this->url_download) {
                        $filepath = $params->get('files_uploaddir').'/'.$mark_catdir.'/'.$this->url_download;
                        if (JFile::exists($filepath)) {
                            $this->size = jdownloadsHelper::fsize($filepath);
                        }
                    } elseif ($this->extern_file != '') {
                            // get extern file file
                            $this->size = jdownloadsHelper::urlfilesize($this->extern_file,'b');
                    } elseif ($this->other_file_id > 0){
                            // use file from other download - get the size from it    
                            $this->size = jdownloadsHelper::getFieldDataFromDownload($this->other_file_id, 'size');
                    }   
                }
            }
            
            //handle now the basic file upload for this download
            if($files['tmp_name']['file_upload'] != ''){
                
                // clear the other fields
                $this->other_file_id = '';
                            
                // delete first old assigned file if exist - so we can use for a update a file with the same filename!
                // we must delete it, otherwise found the auto monitoring it as new file and will add it as new founded file!
                if ($this->url_download){
                    if (JFile::exists($params->get('files_uploaddir').'/'.$mark_catdir.'/'.$this->url_download)){
                        JFile::delete($params->get('files_uploaddir').'/'.$mark_catdir.'/'.$this->url_download);
                        $this->size = '';
                    }
                } 

                $upload_dir = $params->get('files_uploaddir').'/'.$mark_catdir.'/';
                
                $only_name = JFile::stripExt($files['name']['file_upload']);
                $file_extension = JFile::getExt($files['name']['file_upload']);            
                
                // check filename
                $filename_new = JDownloadsHelper::getCleanFolderFileName($only_name).'.'.$file_extension;
                
                $only_name = JFile::stripExt($filename_new);
                $file_extension = JFile::getExt($filename_new);

                if ($only_name != ''){
                    // filename is valid
                    $num = 0;
                    // rename new file when it exists in this folder
                    while (JFile::exists($upload_dir.$filename_new)){
                          $filename_new = $only_name.$num++.'.'.$file_extension;
                          if ($num > 5000) break; 
                    }
                    $files['name']['file_upload'] = $filename_new; 
                    $target_path = $upload_dir.$files['name']['file_upload'];
                    
                    // When file mime is an image type, make sure that we have not a fake pic
                    $file_is_image = JDownloadsHelper::fileIsImage($files['type']['file_upload']);
                    
                    if ($file_is_image && !JDownloadsHelper::imageFileIsValid($files['tmp_name']['file_upload'])){
                            $files['tmp_name']['file_upload'] = '';
                            // error - user have tried to upload a not valid image file
                            $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_INVALID_IMAGE_FILE'));
                            return false;                       
                    }
                    
                    if(JFile::upload($files['tmp_name']['file_upload'], $target_path, false, true)) {
                       
                       $this->sha1_value = sha1_file($target_path);
                       $this->md5_value  =  md5_file($target_path);
                        
                       $this->url_download = basename($target_path);
                       
                       $this->extern_file = '';
                       $this->extern_site = '';
                       
                       if ($params->get('use_file_type_symbols')){
	                       // set file extension pic
	                       if (JFile::exists($file_pic_folder_path.strtolower($file_extension).'.png')){
	                          $this->file_pic = strtolower($file_extension).'.png';
	                       } else {
	                          $this->file_pic = $params->get('file_pic_default_filename');
	                       }                       
                           // need a switch to overwrite the file_pic value later not again (line 911 ff.)
                           $file_pic_checked_already = true;                      
                       } else {
                           // We do nothing to let a manually assignment still possible
                       }
                       
                       // get filesize and date if no value set from user after upload
                       $this->size = jdownloadsHelper::fsize($target_path);

                       // is date empty get filedate
                       if (empty($this->created)) {
                          $this->created = JHtml::_('date', 'now','Y-m-d H:i:s');
                       }

                       // is file creation date empty - set filedate to now
                       if (empty($this->file_date)) {
                          $this->file_date = JHtml::_('date', 'now','Y-m-d H:i:s');
                       }                   
                       
                       // create thumbs form pdf
                       if ($params->get('create_pdf_thumbs') && strtolower($file_extension) == 'pdf'){
						   $thumb_file_type = strtolower($params->get('pdf_thumb_image_type'));
                           // make sure that we have an uniqe filename for the new pic
                           $thumb_path = JPATH_SITE.'/images/jdownloads/screenshots/thumbnails/';
                           $screenshot_path = JPATH_SITE.'/images/jdownloads/screenshots/';
                           $picfilename     = JFile::getName($target_path);
                           $only_name       = JFile::stripExt($picfilename);
                           $file_extension  = JFile::getExt($picfilename);
                          
                           $thumbfilename   = $thumb_path.$only_name.'.'.$thumb_file_type;
                          
                           $num = 1;
                           while (JFile::exists($thumbfilename)){
                               $picfilename = $only_name.$num.'.'.$thumb_file_type;
                               $thumbfilename = $thumb_path.$picfilename;
                               $num++;
                           }
                           // create now the new pdf thumbnail
                           $only_name = JFile::stripExt($picfilename);
                           $pdf_thumb_name = jdownloadsHelper::create_new_pdf_thumb($target_path, $only_name, $thumb_path, $screenshot_path);
                           if ($pdf_thumb_name){
                               $image_thumb_name = $pdf_thumb_name; 
                               $thumb_created = TRUE;
                           }    
                       }
                       // create auto thumb when extension is a pic
                       if ($params->get('create_auto_thumbs_from_pics') && $file_is_image){
                           // make sure that we have an uniqe filename for the new pic
                           $thumb_path      = JPATH_SITE.'/images/jdownloads/screenshots/thumbnails/';
                           $picfilename    = JFile::getName($target_path);
                           $only_name      = JFile::stripExt($picfilename);
                           $file_extension = JFile::getExt($picfilename);
                        
                           $thumbfilename = $thumb_path.$picfilename;
                        
                           $num = 1;
                           while (JFile::exists($thumbfilename)){
                               $picfilename = $only_name.$num.'.'.$file_extension;
                               $thumbfilename = $thumb_path.$picfilename;
                               $num++;
                           }
                           // create now the new thumbnail
                           $thumb_created = jdownloadsHelper::create_new_thumb($target_path, $picfilename);       
                          if ($thumb_created){
                               $image_thumb_name = $picfilename;
                                // create new big image for full view
                               $image_created = jdownloadsHelper::create_new_image($target_path, $picfilename);
                          }
                       }
                           
                       // use xml to read file info (works with joomla install packages (also others?)
                       if ($use_xml_for_file_info){
                           $xml_tags = jdownloadsHelper::getXMLdata($target_path, $this->url_download);
                           if ($xml_tags[name] != ''){
                               $row = $this;
                               $row_file_title = jdownloadsHelper::fillFileDateFromXML($row, $xml_tags);
                               if (!$row_file_title){
                                    $this->setError(JText::_('COM_JDOWNLOADS_BE_EDIT_FILES_USE_XML_RESULT_NO_DATA'));
                                    return false;                       
                               }
                               $movedmsg .= JText::_('COM_JDOWNLOADS_BE_EDIT_FILES_USE_XML_RESULT_OK');
                           } else {
                               // no xml data found
                               $this->title = $this->url_download;
                               $errormsg .= JText::_('COM_JDOWNLOADS_BE_EDIT_FILES_USE_XML_RESULT_NO_FILE');
                           }  
                       }
                    } else {
                        // error - can not write on server folder - wrong permissions set?
                        $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_CHECK_PERMISSIONS'));
                        return false;
                    }
                } else {
                   // filename is after clearing empty - invalid filename
                   $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_INVALID_FILENAME'));
                }  
                
             } else {
                // no new file seletcted for upload
                // check now whether assigned category has changed - if so, then move the file
                if ($file_cat_changed && $this->url_download != ''){
                    // move file
                    // get the folder name from the old category folder - so we can build the path 
                    $db->SetQuery("SELECT cat_dir, cat_dir_parent FROM #__jdownloads_categories WHERE id = '$cat_dir_org'");
                    $old_stored_catdir = $db->loadObject();            

                    // build the complete stored cat path
                    if ($old_stored_catdir->cat_dir_parent != ''){
                        $old_catdir = $old_stored_catdir->cat_dir_parent.DS.$old_stored_catdir->cat_dir;
                    } else {
                        $old_catdir = $old_stored_catdir->cat_dir;
                    }
                       
                    // move it now to the new folder place
                    if(jFile::move($old_catdir.DS.$this->url_download, $mark_catdir.DS.$this->url_download, $params->get('files_uploaddir').DS )) {
                        $movedmsg .= JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_SAVE_MOVEFILE_OK');
                    } else {
                        $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_SAVE_MOVEFILE_ERROR'));
                        return false; 
                    }
               }
               // is alternate a file from the server selected to update the download?
               if ($selected_updatefile){
                   
                   // clear the other field
                   $this->other_file_id = '';
                   
                   // okay, then we will use it
                   $update_dir = $params->get('files_uploaddir').DS;
                   
                   // todo: we must use here the new methode for this in next release 
                   $only_name = JFile::stripExt($selected_updatefile);
                   $file_extension = JFile::getExt($selected_updatefile);

                   $update_filename = JDownloadsHelper::getCleanFolderFileName($only_name).'.'.$file_extension;
                   
                   if ($update_filename != $selected_updatefile){
                       // rename file
                       jFile::move($update_dir.$selected_updatefile, $update_dir.$update_filename);
                   } 
                   // delete first old assigned file

                   $db->setQuery("SELECT cat_dir, cat_dir_parent FROM #__jdownloads_categories WHERE id = '$this->catid'");
                   $cat_dirs = $db->loadObject();
                   if ($cat_dirs->cat_dir_parent != ''){
                       $cat_dir = $cat_dirs->cat_dir_parent.'/'.$cat_dirs->cat_dir;
                   } else {
                       $cat_dir = $cat_dirs->cat_dir;
                   }

                   if (JFile::exists($params->get('files_uploaddir').DS.$cat_dir.DS.$this->url_download)){ 
                       JFile::delete($params->get('files_uploaddir').DS.$cat_dir.DS.$this->url_download); 
                   }    
                   // set new url_download value
                   $this->url_download = $update_filename;
                   // move the file from the upload root folder to the new target folder
                   $target_path = $params->get('files_uploaddir').DS.$cat_dir.DS.$update_filename;
                   if (jFile::move($update_dir.$update_filename, $target_path)){
                       $this->size = JDownloadsHelper::fsize($target_path);
                       $movedmsg .= JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_SAVE_MOVEFILE_OK');
                       $this->sha1_value = sha1_file($target_path);
                       $this->md5_value  =  md5_file($target_path);
                   }    
                   if (JFile::exists($update_dir.$update_filename)) JFile::delete($update_dir.$update_filename);     
                   
                   // use xml to read file info (works with joomla install packages (also others?)
                   if ($use_xml_for_file_info){
                       $xml_tags = JDownloadsHelper::getXMLdata($target_path, $this->url_download);
                       if ($xml_tags[name] != ''){
                           $row = $this;
                           $row_file_title = JDownloadsHelper::fillFileDateFromXML($row, $xml_tags);
                           if (!$row_file_title){
                                $this->setError(JText::_('COM_JDOWNLOADS_BE_EDIT_FILES_USE_XML_RESULT_NO_DATA'));
                                return false;                       
                           }    
                       }  else {
                           // no xml data found
                           $this->title = $this->url_download;
                           $this->setError(JText::_('COM_JDOWNLOADS_BE_EDIT_FILES_USE_XML_RESULT_NO_FILE'));
                       }  
                   }

                   // create thumbs form pdf
                   if ($params->get('create_pdf_thumbs') &&  strtolower($file_extension) == 'pdf'){
					   $thumb_file_type = strtolower($params->get('pdf_thumb_image_type'));
                       // make sure that we have an unique filename for the new pic
                       $thumb_path = JPATH_SITE.'/images/jdownloads/screenshots/thumbnails/';
                       $screenshot_path = JPATH_SITE.'/images/jdownloads/screenshots/';
                       $picfilename     = JFile::getName($target_path);
                       $only_name       = JFile::stripExt($picfilename);
                       $file_extension  = JFile::getExt($picfilename);
                      
                       $thumbfilename   = $thumb_path.$only_name.'.'.$thumb_file_type;
                      
                       $num = 1;
                       while (JFile::exists($thumbfilename)){
                           $picfilename = $only_name.$num.'.'.$thumb_file_type;
                           $thumbfilename = $thumb_path.$picfilename;
                           $num++;
                       }
                       // create now the new pdf thumbnail
                       $only_name = JFile::stripExt($picfilename);
                       $pdf_thumb_name = jdownloadsHelper::create_new_pdf_thumb($target_path, $only_name, $thumb_path, $screenshot_path);
                       if ($pdf_thumb_name){
                           $image_thumb_name = $pdf_thumb_name; 
                           $thumb_created = TRUE;
                       }    
                   }
                   
                    // When file mime is an image type, make sure that we have not a fake pic
                    $file_is_image = JDownloadsHelper::fileIsPicture($update_filename);
                    
                    if ($file_is_image && !JDownloadsHelper::imageFileIsValid($target_path)){
                            $this->images = '';
                            // error - user have tried to upload a not valid image file
                            $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_INVALID_IMAGE_FILE'));
                            return false;                       
                    }                   
                   
                   // create auto thumb when extension is a pic
                   if ($params->get('create_auto_thumbs_from_pics') && $file_is_image){
                       // make sure that we have an uniqe filename for the new pic
                       $thumb_path      = JPATH_SITE.'/images/jdownloads/screenshots/thumbnails/';
                       $picfilename    = JFile::getName($target_path);
                       $only_name      = JFile::stripExt($picfilename);
                       $file_extension = JFile::getExt($picfilename);
                    
                       $thumbfilename = $thumb_path.$picfilename;
                    
                       $num = 1;
                       while (JFile::exists($thumbfilename)){
                           $picfilename = $only_name.$num.'.'.$file_extension;
                           $thumbfilename = $thumb_path.$picfilename;
                           $num++;
                       }
                       // create now the new thumbnail
                       $thumb_created = jdownloadsHelper::create_new_thumb($target_path, $picfilename);       
                      if ($thumb_created){
                           $image_thumb_name = $picfilename;
                            // create new big image for full view
                           $image_created = jdownloadsHelper::create_new_image($target_path, $picfilename);
                      }
                   }                   
                   
                        
               } elseif ($this->other_file_id > 0){
                   // file from an other download is selected
                   // get mdh5 and sha1
                   $this->md5_value = jdownloadsHelper::getFieldDataFromDownload($this->other_file_id, 'md5_value');
                   $this->sha1_value = jdownloadsHelper::getFieldDataFromDownload($this->other_file_id, 'sha1_value');
               } else {
                   // has user the filename manually renamed? Then do it now. 
                   if ($filename_renamed){
                       
                       $only_name = JFile::stripExt($filename_new_name);
                       $file_extension = JFile::getExt($filename_new_name);
                       
                       // check new filename
                       $filename_new = JDownloadsHelper::getCleanFolderFileName($only_name).'.'.$file_extension;
                       
                       $only_name = JFile::stripExt($filename_new);

                       if ($only_name != ''){
                           if (JFile::move($params->get('files_uploaddir').DS.$mark_catdir.DS.$filename_old_name, $params->get('files_uploaddir').DS.$mark_catdir.DS.$filename_new)){
                               // change now value in table field
                               $this->url_download = $filename_new;
                               JError::raiseNotice( 100, JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_FILENAME_RENAMED'));
                           } else {
                               // error - can not rename
                               JError::raiseWarning( 100, JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_FILENAME_ERROR')); 
                           }
                       } else {
                           // filename is after clearing empty - invalid filename
                           JError::raiseWarning( 100, JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_FILENAME_ERROR'));
                       }
                   }
                   
                   // has user the preview filename manually renamed? Then do it now. 
                   if ($preview_filename_renamed){
                       
                       $only_name = JFile::stripExt($preview_filename_new_name);
                       $file_extension = JFile::getExt($preview_filename_new_name);
                       
                       // check new filename
                       $preview_filename_new = JDownloadsHelper::getCleanFolderFileName($only_name).'.'.$file_extension;
                       
                       $only_name = JFile::stripExt($preview_filename_new);

                       if ($only_name != ''){
                           if (JFile::move($params->get('files_uploaddir').DS.$params->get('preview_files_folder_name').DS.$preview_filename_old_name, $params->get('files_uploaddir').DS.$params->get('preview_files_folder_name').DS.$preview_filename_new)){
                               // change now value in table field
                               $this->preview_filename = $preview_filename_new;
                               JError::raiseNotice( 100, JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_FILENAME_RENAMED'));
                           } else {
                               // error - can not rename
                               JError::raiseWarning( 100, JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_FILENAME_ERROR')); 
                           }
                       } else {
                           // filename is after clearing empty - invalid filename
                           JError::raiseWarning( 100, JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_FILENAME_ERROR'));
                       }
                   }               
               }   
            }
            
            //handle now the preview file upload for this download
            if($files['tmp_name']['preview_file_upload'] != ''){
                            
                $upload_dir = $params->get('files_uploaddir').'/'.$params->get('preview_files_folder_name').'/';
                
                // delete first old assigned file if exist - so we can use for a update a file with the same filename!
                if ($this->preview_filename){
                    if (JFile::exists($upload_dir.$this->preview_filename)){
                        JFile::delete($upload_dir.$this->preview_filename);
                    }
                } 

                $only_name = JFile::stripExt($files['name']['preview_file_upload']);
                $file_extension = JFile::getExt($files['name']['preview_file_upload']);            
                
                // check filename
                $filename_new = JDownloadsHelper::getCleanFolderFileName($only_name).'.'.$file_extension;
                
                $only_name = JFile::stripExt($filename_new);
                $file_extension = JFile::getExt($filename_new);

                if ($only_name != ''){
                    // filename is valid
                    $files['name']['preview_file_upload'] = $filename_new; 
                    $target_path = $upload_dir.$files['name']['preview_file_upload'];

                    // When file mime is an image type, make sure that we have not a fake pic
                    $file_is_image = JDownloadsHelper::fileIsImage($files['type']['preview_file_upload']);
                    
                    if ($file_is_image && !JDownloadsHelper::imageFileIsValid($files['tmp_name']['preview_file_upload'])){
                            $files['tmp_name']['preview_file_upload'] = '';
                            
                            // error - user have tried to upload a not valid image file
                            $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_INVALID_IMAGE_FILE'));
                            return false;                       
                    }                
                    
                    if(JFile::upload($files['tmp_name']['preview_file_upload'], $target_path, false, true)) {
                       $this->preview_filename = basename($target_path);
                    } else {
                        // error - can not write on server folder - wrong permissions set?
                        $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_CHECK_PERMISSIONS'));
                        return false;
                    }
                } else {
                   // filename is after clearing empty - invalid filename
                   $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_INVALID_FILENAME'));
                }        
            }        
                 
            
            /**
            * @desc  Remove all not marked images from image folders and DB  
            */          
            if ($this->images != ''){
                $post = JRequest::get('post');
                if( isset($post['keep_image']) )
                {
                    $keep_image_ids = $post['keep_image'];
                } else {
                    $keep_image_ids = array();
                }

                // build an array so we can compare it
                $org_image_ids = explode('|', $this->images);        
                $results = array_diff($org_image_ids, $keep_image_ids);
                
                if ($results){
                    $images_dir = JPATH_SITE.'/images/jdownloads/screenshots/';
                    $thumb_dir = JPATH_SITE.'/images/jdownloads/screenshots/thumbnails/';
                    foreach ($results as $result){
                        // remove the unchecked images
                        if (JFile::exists($images_dir.$result)) JFile::delete($images_dir.$result); 
                        if (JFile::exists($thumb_dir.$result))  JFile::delete($thumb_dir.$result);       
                    }
                    // update the image field in the db table
                    $this->images = implode('|',$keep_image_ids);
                }
            }
            
            // only now can we add the above created thumbs for assigned image or pdf files
            if ($image_thumb_name){
               if ($this->images != ''){
                   $this->images = $this->images.'|'.$image_thumb_name;
               } else {
                   $this->images = $image_thumb_name;
               }
               $this->images = rtrim( $this->images, "|");
            }
                               
            /**
            * @desc  Check icon upload field
            *           if pic selected for upload:
            *           - check image typ
            *           - check whether filename exists. If so, rename the new file 
            *           - move new file to file icon folder
            */          
            
            if ($files['tmp_name']['picnew'] != ''){
                $file['tmp_name']   = $files['tmp_name']['picnew'];
                $file['name']       = $files['name']['picnew'];
                $file['type']       = $files['type']['picnew'];
                $file['size']       = $files['size']['picnew'];
                
                if (JDownloadsHelper::fileIsPicture($file['name'])){
                	
                    // Replace all spaces with underscores to prevent problems
                    $file['name'] = str_replace(' ', '_', $file['name']);
                    
                	$file['name'] = JFile::makeSafe($file['name']);
                	
                    if (!JFile::upload($file['tmp_name'], $file_pic_folder_path.$file['name'], false, true)){
                    	$this->setError(JText::_('COM_JDOWNLOADS_ERROR_CAN_NOT_MOVE_UPLOADED_IMAGE'));
                    	return false;
                	} else {
                    	// move ok - set new file name as selected
                    	$this->file_pic = $file['name'];
                	}        
                }        
            } else {
                if (!$file_pic_checked_already){
                	// check now whether it is selected manually another icon from server       
                	$selected_file_icon = $jinput->get('file_pic', '', 'string');
                	if ($selected_file_icon != '' && $selected_file_icon != $this->file_pic){
                    		$this->file_pic = $selected_file_icon;
                    } elseif ($selected_file_icon != $this->file_pic){
                       	    $this->file_pic = $selected_file_icon;
                    } else {
                       	if (!$params->get('use_file_type_symbols')){
                           	$this->file_pic = '';
                       	}
                   	}   
               	}   
            }
            
            /**
            * @desc  check thumbnail upload field
            *           if image selected for upload:
            *           - check image typ
            *           - check whether filename exists. If so, rename the new file. 
            *           - move new files to /screenshots and /screenshots/thumbnail folder 
            */          

            $filename = '';
            $tempname = '';
            $images = array();
            $upload_dir = JPATH_SITE.'/images/jdownloads/screenshots/'; 

            $sum = count($imagefiles['name']);
            
            if ($sum > 0){
                // new images are uploaded
                for ($i=0; $i < $sum; $i++){
                    
                    $filename = $imagefiles['name'][$i];
                    $tempname = $imagefiles['tmp_name'][$i];
                    $temptype = $imagefiles['type'][$i];
                
                    if ($filename != '' && JDownloadsHelper::fileIsImage($temptype)){     
                        // replace special chars in filename
                        $only_name = JFile::stripExt($filename);
                        $file_extension = JFile::getExt($filename);
                        $filename = JDownloadsHelper::getCleanFolderFileName($only_name).'.'.$file_extension;
                        $only_name = JFile::stripExt($filename);
                        
                        $num = 0;
                        while (JFile::exists($upload_dir.$filename)){
                            $filename = $only_name.$num++.'.'.$file_extension;
                            if ($num > 5000) break; 
                        }
                        
                        // make sure that we have not a fake image file
                        if (!JDownloadsHelper::imageFileIsValid($tempname)){
                            $imagefiles['tmp_name'][$i] = '';
                            // error - user have tried to upload a not valid image file
                            // but we do not break the upload process 
                            // $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_INVALID_IMAGE_FILE'));
                            // return false;                       
                        } else { 
                            if (!JFile::upload($tempname, $upload_dir.$filename, false, true)){
                                //$this->setError(JText::_('COM_JDOWNLOADS_ERROR_CAN_NOT_MOVE_UPLOADED_IMAGE'));
                                //return false;
                            } else {
                                // move okay - create now thumbnail
                                $x = JDownloadsHelper::create_new_thumb($upload_dir.$filename, $filename);       
                                // set correct chmod
                                @chmod($upload_dir.$filename, 0655);
                                // move ok - set new file name as selected
                                $images[] = $filename;
                            }   
                        }
                    } else {
                            // not a file with image mime selected
                            if ($filename != '' && !JDownloadsHelper::fileIsImage($imagefiles['type'][$i])){
                               // add a error message? Or do better nothing then we have always files stored above! 
                               // $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_CATSEDIT_ERROR_FILE_TITLE')); 
                            }
                            
                    }        
                }
                // add all uploaded or selected image files to the new images field   
                if ($this->images != ''){
                    $this->images = $this->images.'|'.implode('|', $images);
                } else {
                    $this->images = implode('|', $images);
                }
                 $this->images = rtrim( $this->images, "|");
            }
            
        }
        return true;
    }  
    
    public function buildNewTitle($alias, $title)
    {
        // Alter the title & alias
        $query = $this->_db->getQuery(true);
        $query->select('*');
        $query->from($this->_tbl);
        $query->where('title = ' . $this->_db->quote($title));
        $query->where('alias = ' . $this->_db->quote($alias));
        $this->_db->setQuery($query);
        $sum = $this->_db->loadResult();
        
        while ($sum){
            $title = JString::increment($title);
            $alias = JString::increment($alias, 'dash');

                $query = $this->_db->getQuery(true);
                $query->select('*');
                $query->from($this->_tbl);
                $query->where('title = ' . $this->_db->quote($title));
                $query->where('alias = ' . $this->_db->quote($alias));
                $this->_db->setQuery($query);
                $sum = $this->_db->loadResult();
        }

        return array($title, $alias);
    }
    
    /**
     * Method to compute the default name of the asset.
     * The default name is in the form `table_name.id`
     * where id is the value of the primary key of the table.
     *
     * @return    string
     * @since    1.6
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_jdownloads.download.'.(int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return    string
     * @since    1.6
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }

   /**
     * Get the parent asset id for the current item
     * 
     * @param   JTable   $table  A JTable object for the asset parent.
     * @param   integer  $id     Id to look up
     * 
     * @return  int The parent asset id for the item   
     */
    public function _getAssetParentId(JTable $table = NULL, $id = NULL)
    {
       
        // For simple cases, parent to the asset root.
        $assetParent = self::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
        
        // Default: if no asset-parent can be found we take the global asset
        $assetParentId = $assetParent->getRootId();
        
        // Find the parent-asset
        if ($this->catid > 1){
            // The item has a category as asset-parent
            $assetParent->loadByName('com_jdownloads.category.' . (int) $this->catid);
        } else {
            // The item has the component as asset-parent
            $assetParent->loadByName('com_jdownloads');
        }
        
        // Return the found asset-parent-id
        if ($assetParent->id) {
            $assetParentId = $assetParent->id;
        }
        return $assetParentId;
    }    
    
    /**
     * Overridden bind function
     *
     * @param       array           named array
     * @return      null|string     null if operation was satisfactory, otherwise returns an error
     * @see JTable:bind
     * @since 1.5
     */
    public function bind($array, $ignore = '') 
    {
        if (isset($array['params']) && is_array($array['params'])) 
        {
                // Convert the params field to a string.
                $parameter = new JRegistry;
                $parameter->loadArray($array['params']);
                $array['params'] = (string)$parameter;
        }

        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules']))
        {
            $rules = new JAccessRules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }              

    /**
     * Method to delete a download from the database table by primary key value.
     *
     * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
     *
     * @return  boolean  True on success.
     *
     */    
    public function delete($pk = null)
    {
        // Initialise variables.
        $k = $this->_tbl_key;
        $pk = (is_null($pk)) ? $this->$k : $pk;

        // If no primary key is given, return false.
        if ($pk === null)
        {
            $e = new JException(JText::_('JLIB_DATABASE_ERROR_NULL_PRIMARY_KEY'));
            $this->setError($e);
            return false;
        }
        
        // Implement JObservableInterface: Pre-processing by observers
        $this->_observers->update('onBeforeDelete', array($pk));        

        // If tracking assets, remove the asset first.
        if ($this->_trackAssets)
        {
            // Get and the asset name.
            $this->$k = $pk;
            $name = $this->_getAssetName();
            $asset = JTable::getInstance('Asset');

            if ($asset->loadByName($name))
            {
                if (!$asset->delete())
                {
                    $this->setError($asset->getError());
                    return false;
                }
            }
            else
            {
                $this->setError($asset->getError());
                return false;
            }
        }

        // Delete the row by primary key.
        $query = $this->_db->getQuery(true);
        $query->delete();
        $query->from($this->_tbl);
        $query->where($this->_tbl_key . ' = ' . $this->_db->quote($pk));
        $this->_db->setQuery($query);

        // Check for a database error.
        if (!$this->_db->execute())
        {
            $e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
            $this->setError($e);
            return false;
        }

        return true;
    }    
}
?>