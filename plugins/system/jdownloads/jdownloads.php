<?php
/**
* @version 3.7
* @package JDownloads
* @copyright (C) 2017 www.jdownloads.com
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
* Plugin to handle some special features from jDownloads.
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

if (!defined('DS')){
     define('DS',DIRECTORY_SEPARATOR);
} 

class plgSystemjdownloads extends JPlugin 
{ 
    
    private $caching = 0;
     
    /**
     * Constructor
     *
     * @access      protected
     * @param       object  $subject The object to observe
     * @param       array   $params An array that holds the plugin configuration
     */
    public function __construct(& $subject, $params)
    {
        parent::__construct($subject, $params);
        $this->loadLanguage();
             
        // get jD language admin file
        $language = JFactory::getLanguage();
        $language->load('com_jdownloads');
    } 
     
    /**
     * This event is triggered after the framework has dispatched the application.
     * 
     * @param none
     * @return none
     */
    public function onAfterDispatch() 
    {
        
        // function to deactivate partially the Joomla 'cache option' for defined sections
        // inspired by cacheControl plugin from www.crosstec.de
        if (JFactory::getApplication()->isSite() && $this->checkCacheRules()){
            $plugin = JPluginHelper::getPlugin('system', 'jdownloads');
            jimport( 'joomla.html.parameter' );
            $pluginParams = $this->params;
            if($pluginParams->def('enable_again_after_dispatch', 0)){
                JFactory::getConfig()->set('caching', $this->caching);
            }
        }
    }     

    /**
     * This event is triggered after the framework has loaded and the application initialise method has been called.
     * 
     * @param none
     * @return none
     */     
    public function onAfterInitialise() 
    { 
     
        return;  
    }
     
    public function onJDUserGroupSettingsBeforeSave($type, $table) 
    {

        return true;
    }

    public function onJDUserGroupSettingsAfterSave($type, $table) 
    {
         
         return true;
    }
     
     
    public function onUserAfterSave($user, $isNew, $success) 
    {
        return;     
    }     
    
    
    public function onUserAfterDelete($user, $isNew, $success) 
    {
        return; 
    }

    /**
    * This event is triggered after the framework has rendered the application.
    * Rendering is the process of pushing the document buffers into the template placeholders, retrieving data from the document and pushing it into the JResponse buffer.
    * When this event is triggered the output of the application is available in the response buffer.
    * 
    * @param none
    * @return none
    */     
    public function onAfterRender() 
    { 
         $app = JFactory::getApplication();
         $database = JFactory::getDBO();
         $return = false;
         
         // exist the tables?
         $prefix = strtolower($database->getPrefix()); 
         $tablelist = $database->getTableList();
         if ( !in_array ( $prefix.'jdownloads_files', $tablelist ) ){
             $return = true;
         }     
         $plugin = JPluginHelper::getPlugin('system', 'jdownloads');
         jimport( 'joomla.utilities.utility' );
         // get params
         $params = $this->params;;
         $use_hider = $params->get( 'use_hider' );
         if (!$use_hider) $return = true;
    
         // No need in admin
         if (!$app->isAdmin()) {
             $body = JResponse::getBody();
             if (!$return){
             
                function _getParameter( $name, $default='' ) {
                    $return = "";
                    $return = $this->params->get( $name, $default );
                }
                
                // define the regular expression
                $regex1 = "#{jdreg}(.*?){/jdreg}#s";
                $regex2 = "#{jdpub}(.*?){/jdpub}#s";
                $regex3 = "#{jdauthor}(.*?){/jdauthor}#s";
                $regex4 = "#{jdeditor}(.*?){/jdeditor}#s";
                $regex5 = "#{jdpublisher}(.*?){/jdpublisher}#s";
                $regex6 = "#{jdmanager}(.*?){/jdmanager}#s";
                $regex7 = "#{jdadmin}(.*?){/jdadmin}#s";
                $regex8 = "#{jdsuper}(.*?){/jdsuper}#s";
                $regex9 = "#{jdspecial}(.*?){/jdspecial}#s";
                $regex10 = "#{jduser:(.*?)}(.*?){/jduser}#s";
                $regex11 = "#{jdgroups:(.*?)}(.*?){/jdgroups}#s";
                
                // replacement for _reg
                $body = preg_replace_callback( $regex1, array('plgSystemjdownloads', '_reg'), $body );
                // replacement for _pub
                $body = preg_replace_callback( $regex2, array('plgSystemjdownloads', '_pub'), $body );
                // replacements for groups by name
                $body = preg_replace_callback( $regex9, array('plgSystemjdownloads', '_special'), $body );
                $body = preg_replace_callback( $regex3, array('plgSystemjdownloads', '_author'), $body );
                $body = preg_replace_callback( $regex4, array('plgSystemjdownloads', '_editor'), $body );
                $body = preg_replace_callback( $regex5, array('plgSystemjdownloads', '_publisher'), $body );
                $body = preg_replace_callback( $regex6, array('plgSystemjdownloads', '_manager'), $body );
                $body = preg_replace_callback( $regex7, array('plgSystemjdownloads', '_admin'), $body );
                $body = preg_replace_callback( $regex8, array('plgSystemjdownloads', '_super'), $body );
                $body = preg_replace_callback( $regex10, array('plgSystemjdownloads', '_user'), $body );
                $body = preg_replace_callback( $regex11, array('plgSystemjdownloads', '_groups'), $body );

                JResponse::setBody($body);
             
             } else {
                // Hide option is deactivated - so we must remove maybe the prior inserted placeholder
                $body = str_replace('{jdreg}', '', $body);
                $body = str_replace('{/jdreg}', '', $body);
                $body = str_replace('{jdpub}', '', $body);
                $body = str_replace('{/jdpub}', '', $body);
                $body = str_replace('{jdauthor}', '', $body);
                $body = str_replace('{/jdauthor}', '', $body);
                $body = str_replace('{jdeditor}', '', $body);
                $body = str_replace('{/jdeditor}', '', $body);
                $body = str_replace('{jdpublisher}', '', $body);
                $body = str_replace('{/jdpublisher}', '', $body);
                $body = str_replace('{jdmanager}', '', $body);
                $body = str_replace('{/jdmanager}', '', $body);
                $body = str_replace('{jdadmin}', '', $body);
                $body = str_replace('{/jdadmin}', '', $body);
                $body = str_replace('{jdsuper}', '', $body);
                $body = str_replace('{/jdsuper}', '', $body);
                $body = str_replace('{jdspecial}', '', $body);
                $body = str_replace('{/jdspecial}', '', $body);
                $regex1 = "#{jduser:(.*?)}(.*?){/jduser}#s";
                $regex2 = "#{jdgroups:(.*?)}(.*?){/jdgroups}#s";
                $body = preg_replace_callback( $regex1, array('plgSystemjdownloads', '_remove'), $body );
                $body = preg_replace_callback( $regex2, array('plgSystemjdownloads', '_remove'), $body );
                JResponse::setBody($body);
             }     
         }
    } 

    /**
    * This event is triggered after the framework has loaded and initialised and the router has routed the client request.
    * Routing is the process of examining the request environment to determine which component should receive the request. The component optional parameters are then set in the request object that will be processed when the application is being dispatched.
    * When this event triggers, the router has parsed the route and pushed the request parameters into JRequest to be retrieved by the application.
    * 
    * @param none
    * @return none
    */  
	public function onAfterRoute()
    {

        jimport( 'joomla.database.table.extension' );
        $db = JFactory::getDBO();
        $params = $this->params;
        
        // Deactivate Joomla caching when required
         if( JFactory::getApplication()->isSite() && $this->checkCacheRules()){
             $this->caching = JFactory::getConfig()->get('caching');
             JFactory::getConfig()->set('caching', 0);
         }   

        // It is reuired to reduce the download and upload log data sets?
        // But we will do this only one time daily so we check at first whether we have done it today already
        $prefix = strtolower($db->getPrefix()); 
        $tablelist = $db->getTableList();
         if ( !in_array ( $prefix.'jdownloads_logs', $tablelist ) ){
             return;
         } 
         
        $last_action_date = $params->get('reduce_log_data_last_action'); 
        $today = date('Y-m-d');
        
        if (!$last_action_date || $last_action_date < $today){
            $store_days = (int)$params->get('reduce_log_data_sets_to');
            if ($store_days == 0) return;
            
            // Reduce the data now when we have to much data
            $db->setQuery("DELETE FROM #__jdownloads_logs WHERE log_datetime < DATE_SUB(NOW(), INTERVAL $store_days DAY)");
            $result = $db->execute();
            
            // Update now the stored date in plugins settings
            $table = JTable::getInstance('extension');
            $id    = $table->find(array('name' => 'plg_system_jdownloads'));
            
            if (!$table->load($id)){
                $this->setError($table->getError());
                return false;
            }

            $this->params->set('reduce_log_data_last_action', $today);
            $table->params = (string) $this->params;

            if (!$table->check()){
                $this->setError($table->getError());
                return false;
            }

            if (!$table->store()){
                $this->setError($table->getError());
                return false;
            }
         }
         
         return;
    }
    
    /**
    * We use this event to check the options settings from jD and (when required) to correct this settings or doing additional work.
    *      
    */    
    public function onExtensionBeforeSave($context, $table, $is_new)
    {
           
        // Check that we have really the jD settings
        if ($table->element != 'com_jdownloads') return;
        
        require_once JPATH_BASE.'/components/com_jdownloads/helpers/jdownloads.php';
        
        $db = JFactory::getDbo();
        
        // Load sys.ini language file
        $lang = JFactory::getLanguage();
        $lang->load('com_jdownloads.sys');
        
        $params = json_decode($table->params);
        
        // the cart plugin option can only be enabled when the plugin is also activated
        $cartplugin = JPluginHelper::getPlugin('content', 'jdownloadscart');
        if (!$cartplugin){
            // disable option again 
            $params->use_shopping_cart_plugin = 0;
        }
        
        $org_upload_path    = $params->root_dir;
        $org_preview_dir    = $params->preview_dir;
        $org_temp_dir       = $params->temp_dir;
        
        // The files_uploaddir field must always have a value
        if ($params->files_uploaddir == ''){
            $jd_upload_root = JPATH_ROOT.DS.'jdownloads';
            $params->files_uploaddir = str_replace('\\', '/', $jd_upload_root);
        }
        
        // Some other fields too
        if ($params->checkbox_top_text == ''){
            $params->checkbox_top_text = $db->escape(JText::_('COM_JDOWNLOADS_SETTINGS_INSTALL_1'));
        }        
        
        if ($params->system_list == ''){
            $params->system_list = $db->escape(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_SYSTEM_DEFAULT_LIST'));
        }        
        
        if ($params->language_list == ''){
            $params->language_list = $db->escape(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_LANGUAGE_DEFAULT_LIST'));
        }

        // Remove slashes on the end from path
        $params->files_uploaddir = rtrim($params->files_uploaddir, "/");
        $params->files_uploaddir = rtrim($params->files_uploaddir, "\\");
        
        // Replacing backslashes with slashes
        $params->files_uploaddir = str_replace('\\', '/', $params->files_uploaddir);
        if (!JFolder::exists($params->files_uploaddir)){
            JError::raiseWarning( 100, 'Error Upload root folder not found!' );
        }
        
        // Remove slash on the end from folder name
        $params->preview_files_folder_name = rtrim($params->preview_files_folder_name, "/");
        $params->preview_files_folder_name = rtrim($params->preview_files_folder_name, "\\");

        // Is sub folder name changed for preview files folder?
        if ($org_preview_dir != $params->preview_files_folder_name && $params->preview_files_folder_name != ''){
            // Rename the folder
            $result_preview = JFolder::move($params->files_uploaddir.DS.$org_preview_dir, $params->files_uploaddir.DS.$params->preview_files_folder_name);
            if ($result_preview !== true){
                JError::raiseWarning( 100, 'Error! Can not rename folder: '.$params->files_uploaddir.DS.$org_preview_dir );
            } else {
                JError::raiseNotice( 600, 'Folder successful renamed.');
            }
        }                

        // Remove slash on the end from folder name
        $params->tempzipfiles_folder_name = rtrim($params->tempzipfiles_folder_name, "/");
        $params->tempzipfiles_folder_name = rtrim($params->tempzipfiles_folder_name, "\\"); 

        // Is sub folder name changed for temporary files folder?
        if ($org_temp_dir != $params->tempzipfiles_folder_name && $params->tempzipfiles_folder_name != ''){
            // Rename the folder
            $result_temp = JFolder::move($params->files_uploaddir.DS.$org_temp_dir, $params->files_uploaddir.DS.$params->tempzipfiles_folder_name);
            if ($result_temp !== true){
                JError::raiseWarning( 100, 'Error! Can not rename folder: '.$params->files_uploaddir.DS.$org_temp_dir );
            } else {
                JError::raiseNotice( 600, 'Folder successful renamed:');
            }   
        }        

        // Resize thumbnails?
        if ($params->resize_thumbs == 1 && ($params->thumbnail_size_height > 0) && ($params->thumbnail_size_width > 0) ){
            $msg = JDownloadsHelper::resizeAllThumbs( $params->thumbnail_size_height, $params->thumbnail_size_width );
            JError::raiseNotice( 100, $msg );
            // Reset the switch
            $params->resize_thumbs = '0';
        }
        
        // 'remove special chars' and 'unicode' option can not be both active
        if ($params->use_unicode_path_names){
            $params->fix_upload_filename_specials = 0; 
        }

        // Check com field
        if ($params->com != ''){
            $config = JFactory::getConfig();
            $secret = $config->get( 'secret' );
            if ($params->com == $secret){
                $params->com = strrev($secret);
            } else {
                $com_dummy = strrev($params->com);
                if (!$com_dummy == $secret){ 
                $params->com = '';
                }
            }   
        }
        
        // Check e-mail addresses
        $params->send_mailto           = JDownloadsHelper::cleanEMailAddresses($params->send_mailto);
        $params->send_mailto_upload    = JDownloadsHelper::cleanEMailAddresses($params->send_mailto_upload);
        $params->send_mailto_report    = JDownloadsHelper::cleanEMailAddresses($params->send_mailto_report);        
        $params->customers_send_mailto = JDownloadsHelper::cleanEMailAddresses($params->customers_send_mailto);        
        
        // Check folder protection status
        $source = JPATH_SITE.'/administrator/components/com_jdownloads/htaccess.txt'; 
        $dest   = $params->files_uploaddir.'/.htaccess'; 
        if ($params->anti_leech && !is_file($dest)){
            // If activated - copy and rename the htaccess
            if (JFile::exists($source)){ 
                JFile::copy($source, $dest);
                $msg .= ' - '.JText::_('COM_JDOWNLOADS_ACTIVE_ANTILEECH_OK');
           } else {
               $msg .= ' - '.JText::_('COM_JDOWNLOADS_ACTIVE_ANTILEECH_ERROR');
           }
        } else {
            // Anti leech off? then delete the htaccess
            if (!$params->anti_leech) { 
                if (JFile::exists($dest)){
                    if (JFile::delete($dest)){
                        $msg .= ' - '.JText::_('COM_JDOWNLOADS_ACTIVE_ANTILEECH_OFF_OK');                
                    } else {
                        $msg .= ' - '.JText::_('COM_JDOWNLOADS_ACTIVE_ANTILEECH_OFF_ERROR');                
                    }   
                }
            }  
        }
        
        // Replace single and double quote characters in raw text fields
        $params->autopublish_default_cat_description = str_replace('"', '', $params->autopublish_default_cat_description);
        $params->autopublish_default_description     = str_replace('"', '', $params->autopublish_default_description);
        $params->autopublish_default_cat_description = str_replace("'", '', $params->autopublish_default_cat_description);
        $params->autopublish_default_description     = str_replace("'", '', $params->autopublish_default_description);
        
        /*
        $params->downloads_titletext                 = str_replace("'", '', $params->downloads_titletext);
        $params->downloads_footer_text               = str_replace("'", '', $params->downloads_footer_text);
        $params->offline_text                        = str_replace("'", '', $params->offline_text);
        $params->send_mailto_template_download       = str_replace("'", '', $params->send_mailto_template_download);
        $params->send_mailto_template_upload         = str_replace("'", '', $params->send_mailto_template_upload);
        $params->report_mail_layout                  = str_replace("'", '', $params->report_mail_layout);
        $params->customers_mail_layout               = str_replace("'", '', $params->customers_mail_layout);
        $params->customers_mail_subject              = str_replace("'", '', $params->customers_mail_subject);
        $params->report_mail_subject                 = str_replace("'", '', $params->report_mail_subject);
        $params->send_mailto_betreff_upload          = str_replace("'", '', $params->send_mailto_betreff_upload);
        $params->send_mailto_betreff                 = str_replace("'", '', $params->send_mailto_betreff);
        $params->user_message_when_zero_points       = str_replace("'", '', $params->user_message_when_zero_points);
        $params->fileplugin_offline_title            = str_replace("'", '', $params->fileplugin_offline_title);
        $params->fileplugin_show_downloadtitle       = str_replace("'", '', $params->fileplugin_show_downloadtitle);
        $params->checkbox_top_text                   = str_replace("'", '', $params->checkbox_top_text);
        */
        
        // Remove spaces from lists 
        $params->file_types_view         = strtolower(preg_replace('/[^0-9a-zA-Z,]/', '', $params->file_types_view));
        $params->file_types_autodetect   = strtolower(preg_replace('/[^0-9a-zA-Z,]/', '', $params->file_types_autodetect));

        $params->allowed_leeching_sites  = str_replace(' ', '', $params->allowed_leeching_sites);
        $params->send_mailto             = str_replace(' ', '', $params->send_mailto);                
        $params->send_mailto_upload      = str_replace(' ', '', $params->send_mailto_upload);
        $params->send_mailto_report      = str_replace(' ', '', $params->send_mailto_report);
        
        // Make sure that all AUP options are set back to default, when the main option is set off.
        if (!$params->use_alphauserpoints){
            $params->use_alphauserpoints_with_price_field = '0';
        }           
        
        // Installed imagick is needed
        if ($params->create_pdf_thumbs){
            if (!extension_loaded('imagick')){
                $params->create_pdf_thumbs = '0';
            }    
        }        
        
        // Check the ID3 Tag layout - when it is empty (after installation) we save the default layout
        if ($params->mp3_info_layout == ''){
            $params->mp3_info_layout = '<div class="jd_mp3_id3tag_name">{album_title}</div>
<div class="jd_mp3_id3tag_value">{album}</div>
<div class="jd_mp3_id3tag_name">{name_title}</div>
<div class="jd_mp3_id3tag_value">{name}</div>
<div class="jd_mp3_id3tag_name">{year_title}</div>
<div class="jd_mp3_id3tag_value">{year}</div>
<div class="jd_mp3_id3tag_name">{artist_title}</div>
<div class="jd_mp3_id3tag_value">{artist}</div>
<div class="jd_mp3_id3tag_name">{genre_title}</div>
<div class="jd_mp3_id3tag_value">{genre}</div>
<div class="jd_mp3_id3tag_name">{length_title}</div>
<div class="jd_mp3_id3tag_value">{length}</div>';
        } 
        
        $table->params = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);         
           
        return;
    }    

    /**
    * We use this event to check the options settings from jD and (when required) to correct this settings or run additional functions.
    *      
    */
    public function onExtensionAfterSave($context, $table, $is_new)
    {
    
        // check that we have really the jD settings
        if ($table->element != 'com_jdownloads') return;
        
        require_once JPATH_BASE.'/components/com_jdownloads/helpers/jdownloads.php';
        
        $params = json_decode($table->params);
        
        $db = JFactory::getDbo();
        
        // Change the menu items for custom fields when the usage is disabled in jD options.
        if (!$params->custom_fields_enable){
            $query = $db->getQuery(true);
            
            // Change the fields menu item
            $fields = array(
                $db->quoteName('client_id') . ' = ' . $db->quote('99')
            );

            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('menutype') . ' = ' . $db->quote('main'), 
                $db->quoteName('alias') . ' = ' . $db->quote('com-jdownloads-custom-fields')
            );

            $query->update($db->quoteName('#__menu'))->set($fields)->where($conditions);
            $db->setQuery($query);
            $result = $db->execute();
            
            // Change the fields group menu item
            $query = $db->getQuery(true);
            

            $conditions = array(
                $db->quoteName('menutype') . ' = ' . $db->quote('main'), 
                $db->quoteName('alias') . ' = ' . $db->quote('com-jdownloads-custom-field-groups')
            );

            $query->update($db->quoteName('#__menu'))->set($fields)->where($conditions);
            $db->setQuery($query);
            $result2 = $db->execute();
        } else {
            // Activate the menu items for custom fields when the usage is enabled again in jD options.
            $query = $db->getQuery(true);

            $fields = array(
                $db->quoteName('client_id') . ' = ' . $db->quote('1')
            );

            $conditions = array(
                $db->quoteName('menutype') . ' = ' . $db->quote('main'), 
                $db->quoteName('alias') . ' = ' . $db->quote('com-jdownloads-custom-fields')
            );

            $query->update($db->quoteName('#__menu'))->set($fields)->where($conditions);
            $db->setQuery($query);
            $result = $db->execute();
            
            $query = $db->getQuery(true);

            $conditions = array(
                $db->quoteName('menutype') . ' = ' . $db->quote('main'), 
                $db->quoteName('alias') . ' = ' . $db->quote('com-jdownloads-custom-field-groups')
            );

            $query->update($db->quoteName('#__menu'))->set($fields)->where($conditions);
            $db->setQuery($query);
            $result2 = $db->execute();
        }
        
        return;
    }    

    /**
    *  
    *      
    */
    function checkCacheRules()
    {
          
        $plugin = JPluginHelper::getPlugin('system', 'jdownloads');
        jimport( 'joomla.html.parameter' );
        $params = $this->params;
        $defs = trim(str_replace("\r","", $params->def('rules','')));
        $defs = explode("\n", $defs);
        
        foreach($defs as $def){
            if ($def != ''){
                $result = $this->parseQueryString($def);
                if(is_array($result)){
                    $found = 0;
                    $required = count($result);
                    foreach($result As $key => $value){
                        if( JRequest::getVar($key) == $value || ( JRequest::getVar($key, null) !== null && $value == '?' ) ){
                            $found++;
                        }
                    }
                    if($found == $required){
                        return true;
                    }
                }
            }
        }
        
        return false;
     } 
     
    /**
     * 
     * 
     * @param mixed $str
     */
    function parseQueryString($str) 
    {
        $op = array();
        $pairs = explode("&", $str);
        foreach ($pairs as $pair) {
            list($k, $v) = array_map("urldecode", explode("=", $pair));
            $op[$k] = $v;
        }
        return $op;
    }
     
    /**
    *  Functions for hide elements from output for special user groups
    * 
    *  Inspired by the hider content plugin from Dioscouri Design
    *  Parts of this functions are copyright by Dioscouri Design - www.dioscouri.com 
    * 
    * 
    */
    
    private function _reg( &$matches )
    {
        $user = JFactory::getUser();
        $return = '';
        if (!empty($user->id)) {
            $return = $matches[1];
        }
        return $return;
    }

    private function _pub( &$matches )
    {
        $user = JFactory::getUser();
        $return = $matches[1];
        if (!empty($user->id)){
            $return = ''; 
        }
        return $return;
    }

    private function _author( &$matches )
    {
        $user_groups = $this->getUserGroups();
    
        $return = '';
        if (in_array('author', $user_groups->group_names)){
            $return = $matches[1];
        }
        if (in_array('super users', $user_groups->group_names)){
            $return = $matches[1];
        }
        return $return;
    }

    private function _editor( &$matches )
    {
        $user_groups = $this->getUserGroups();
    
        $return = '';
        if (in_array('editor', $user_groups->group_names)){
            $return = $matches[1];
        }
        if (in_array('super users', $user_groups->group_names)){
            $return = $matches[1];
        }        
        return $return;
    }

    private function _publisher( &$matches )
    {
        $user_groups = $this->getUserGroups();
    
        $return = '';
        if (in_array('publisher', $user_groups->group_names)){
            $return = $matches[1];
        }
        if (in_array('super users', $user_groups->group_names)){
            $return = $matches[1];
        }        
        return $return;
    }

    private function _manager( &$matches )
    {
        $user_groups = $this->getUserGroups();
    
        $return = '';
        if (in_array('manager', $user_groups->group_names)){
            $return = $matches[1];
        }
        if (in_array('super users', $user_groups->group_names)){
            $return = $matches[1];
        }        
        return $return;
    }

    private function _admin( &$matches )
    {
        $user_groups = $this->getUserGroups();
    
        $return = '';
        if (in_array('administrator', $user_groups->group_names)){
            $return = $matches[1];
        }
        if (in_array('super users', $user_groups->group_names)){
            $return = $matches[1];
        }        
        return $return;
    }

    private function _super( &$matches )
    {
        $needles = array('super administrator', 'super users');
        $user_groups = $this->getUserGroups();
    
        $return = '';
        foreach ($needles as $needle){
            if (in_array($needle, $user_groups->group_names)){
                $return = $matches[1];
            }
        }
        return $return;
    }
    
    private function _special( &$matches )
    {
        $needles = array('super administrator', 'super users', 'author', 'editor', 'publisher', 'manager', 'administrator');
        $user_groups = $this->getUserGroups();
    
        $return = '';
        foreach ($needles as $needle){
            if (in_array($needle, $user_groups->group_names)){
                $return = $matches[1];
            }
        }
        return $return;        
    }
    
    private function _user( &$matches )
    {

        $user = JFactory::getUser();
        $userid = $user->get('id');
        $username = $user->get('username');

        $match = $matches[1];

        $return = '';

        if (($match == $username) || ($match == strval($userid))){
            $return = $matches[2];
        }
        return $return;
    }
    
    private function _groups( &$matches )
    {
        $match = $matches[1];
        // explode $match by ,
        $allowed_groups = explode(',', $match);
        foreach ($allowed_groups as $key=>$allowed_group){
            $allowed_groups[$key] = strtolower( trim($allowed_group) );
            if (empty($allowed_groups[$key])){
                unset($allowed_groups[$key]);
            }
        } 

        $user_groups = $this->getUserGroups(); 

        $return = '';
        // if the user is in any of the groups in $allowed_groups, grant access to $match[2]
        foreach ($allowed_groups as $allowed_group){
            if (in_array($allowed_group, $user_groups->group_ids) || in_array($allowed_group, $user_groups->group_names)){
                $return = $matches[2];
                return $return;
            }
        }    
        return $return;
    }

    private function _remove( &$matches )
    {

        $return = $matches[2];
        return $return;
    }

    
    private function getUserGroups()
    {
        // get all of the current user's groups
        $user = JFactory::getUser();
        $user_groups = array();
        $authorized_groups = array();
        
        $authorized_groups = $user->getAuthorisedGroups();
        foreach ($authorized_groups as $authorized_group) {
            $table = JTable::getInstance('Usergroup', 'JTable');
            $table->load($authorized_group);
            $user_groups[$authorized_group] = strtolower( $table->title );
        }

        $return = new stdClass();
        $return->group_names = $user_groups;
        $return->group_ids = $authorized_groups;
        
        return $return;
    }    
        
}
?>