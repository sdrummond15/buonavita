<?php
/**
 * @package jDownloads
 * @version 3.9.7.3  
 * @copyright (C) 2007 - 2021 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
 
defined('_JEXEC') or die('Restricted access');

setlocale(LC_ALL, 'C.UTF-8', 'C');

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Utilities\ArrayHelper; 
use Joomla\String\StringHelper;
use Joomla\CMS\Factory;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.log.log');

/**
 * Install Script file of jDownloads component
 */
class com_jdownloadsInstallerScript
{
    
	private $dbversion;
    private $new_version;
    private $new_version_short;
    private $target_joomla_version;
    private $old_version_short;
    private $install_msg;
    
    public $custom_fields_items;
    public $created_custom_fields;
    public $field_titles;
    public $lang_keys;
    public $multilanguages;
    public $tables_copied;

    /**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
        
        // try to set time limit
        @set_time_limit(0);

        // try to increase memory limit
        if ((int) ini_get( 'memory_limit' ) < 32){
            @ini_set( 'memory_limit', '32M' );
        }

        if (!defined('DS')){
           define('DS',DIRECTORY_SEPARATOR);
        }
        
        $db = JFactory::getDBO();
        $user   = JFactory::getUser();

        // Add a log entry
        self::addLog(JText::sprintf('COM_JDOWNLOADS_INSTALL_LOG_START', $user->id, $user->name, $this->new_version), 'JLog::INFO', false);                
        
        $params   = JComponentHelper::getParams('com_jdownloads');
        $files_upload_dir = $params->get( 'files_uploaddir' );
        
		// insert the new default header, subheader and footer layouts in every layout.
		require_once (JPATH_ADMINISTRATOR.'/components/com_jdownloads/helpers/jd_layouts.php');
		
        /*
        / Copy frontend images to the joomla images folder
        */
        $target = JPATH_ROOT.DS.'images'.DS.'jdownloads';
        $source = dirname(__FILE__).DS.'site'.DS.'assets'.DS.'images'.DS.'jdownloads';
        
        $images_copy_result   = false;
        $images_folder_exists = false;
        
        if (!JFolder::exists($target)){
            $images_copy_result = JFolder::copy($source,$target);
        } else {
            $images_folder_exists = true;
        }       

        // Check whether custom css file already exist
        $custom_css_path = JPATH_ROOT.DS.'components'.DS.'com_jdownloads'.DS.'assets'.DS.'css'.DS.'jdownloads_custom.css';
        if (!JFile::exists($custom_css_path)){
            // create a new css file
            $text  = "/* Custom CSS File for jDownloads\n";
            $text .= "   If this file already exist then jDownloads does not overwrite it when installing or upgrading jDownloads.\n";
            $text .= "   This file is loaded after the standard jdownloads_fe.css.\n";   
            $text .= "   So you can use it to overwrite the standard css classes for your own customising.\n*/";               
            $x = file_put_contents($custom_css_path, $text, FILE_APPEND);
        }
        
        /*
        / Install modules and plugins
        */
        jimport('joomla.installer.installer');
        $status = new JObject();
        $status->modules = array();
        $status->plugins = array();
        $src_modules = dirname(__FILE__).DS.'modules';
        $src_plugins = dirname(__FILE__).DS.'plugins';

        // plugins
        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_system_jdownloads');
        $status->plugins[] = array('name'=>'jDownloads System Plugin','group'=>'system', 'result'=>$result);
        
        // system plugin must be enabled for user group limits
        $db->setQuery("UPDATE #__extensions SET enabled = '1' WHERE `name` = 'plg_system_jdownloads' AND `type` = 'plugin'");
        $db->execute();

        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_jdownloads_example');
        $status->plugins[] = array('name'=>'jDownloads Example Plugin','group'=>'jdownloads', 'result'=>$result);
        
        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_search_jdownloads');
        $status->plugins[] = array('name'=>'jDownloads Search Plugin','group'=>'search', 'result'=>$result);        

        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'editor_button_plugin_jdownloads_downloads');
        $status->plugins[] = array('name'=>'jDownloads Download Content Button Plugin','group'=>'editors-xtd', 'result'=>$result);        
        
        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_content_jdownloads');
        $status->plugins[] = array('name'=>'jDownloads Content Plugin','group'=>'content', 'result'=>$result);        

        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_finder_jdownloads');
        $status->plugins[] = array('name'=>'jDownloads Finder Plugin','group'=>'finder', 'result'=>$result);

        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_finder_folder');
        $status->plugins[] = array('name'=>'jDownloads Finder Categories Plugin','group'=>'finder', 'result'=>$result);
        
        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_content_jdownloads_tags_fix');
        $status->plugins[] = array('name'=>'jDownloads Tags Fix Content Plugin','group'=>'content', 'result'=>$result);        

		// tags fix plugin must be enabled 
        $db->setQuery("UPDATE #__extensions SET enabled = '1' WHERE `name` = 'plg_content_jdownloads_tags_fix' AND `type` = 'plugin'");
        $db->execute();		

        /*$installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_actionlog_jdownloads');
        $status->plugins[] = array('name'=>'jDownloads Actionlog Plugin','group'=>'actionlog', 'result'=>$result);        

        // tags fix plugin must be enabled 
        $db->setQuery("UPDATE #__extensions SET enabled = '1' WHERE `name` = 'plg_actionlog_jdownloads' AND `type` = 'plugin'");
        $db->execute();
        */
        
        // modules
        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_latest');
        $status->modules[] = array('name'=>'jDownloads Latest Module','client'=>'site', 'result'=>$result);

        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_top');
        $status->modules[] = array('name'=>'jDownloads Top Module','client'=>'site', 'result'=>$result);

        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_last_updated');
        $status->modules[] = array('name'=>'jDownloads Last Updated Module','client'=>'site', 'result'=>$result);

        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_most_recently_downloaded');
        $status->modules[] = array('name'=>'jDownloads Most Recently Downloaded Module','client'=>'site', 'result'=>$result);
        
        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_stats');
        $status->modules[] = array('name'=>'jDownloads Stats Module','client'=>'site', 'result'=>$result);        

        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_tree');
        $status->modules[] = array('name'=>'jDownloads Tree Module','client'=>'site', 'result'=>$result);
        
        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_related');
        $status->modules[] = array('name'=>'jDownloads Related Module','client'=>'site', 'result'=>$result);        

        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_rated');
        $status->modules[] = array('name'=>'jDownloads Rated Module','client'=>'site', 'result'=>$result);

        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_featured');
        $status->modules[] = array('name'=>'jDownloads Featured Module','client'=>'site', 'result'=>$result);
        
        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_view_limits');
        $status->modules[] = array('name'=>'jDownloads View Limits Module','client'=>'site', 'result'=>$result);
        
        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_admin_stats');
        $status->modules[] = array('name'=>'jDownloads Admin Stats Module','client'=>'admin', 'result'=>$result);
        
        // New admin stats module (should now be published on position 'jdcpanel').
        $mod_array = array ("view_latest" => "1", "view_popular" => "1", "view_featured" => "1", "view_most_rated" => "1", "view_top_rated" => "1", "amount_items" => "5", "view_statistics" => "1", "view_monitoring_log" => "1", "view_restore_log" => "1", "view_server_info" => "1", "layout" => "_:default", "moduleclass_sfx" => "", "cache" => "0", "cache_time" => "900", "module_tag" => "div", "bootstrap_size" => "0", "header_tag" => "h3", "header_class" => "", "style" => "0");
        $mod_params = json_encode($mod_array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $db->setQuery("UPDATE #__modules SET `published` = '1', `position` = 'jdcpanel', `ordering` = '1', `showtitle` = '0', `params` = '".$mod_params."' WHERE `module` = 'mod_jdownloads_admin_stats'");
        $db->execute();
        
        //  It must also exist a dataset in the _modules_menu table to get the module visible!
        // Get ID
        $db->setQuery("SELECT id FROM #__modules WHERE `module` = 'mod_jdownloads_admin_stats'");
        $module_id = (int)$db->loadResult();
        
        if ($module_id){
            $db->setQuery("SELECT COUNT(*) FROM #__modules_menu WHERE `moduleid` = '$module_id'");
            $result = (int)$db->loadResult();
            // Insert it only when not already exist            
            if (!$result){
            	$db->setQuery("INSERT INTO #__modules_menu (moduleid, menuid) VALUES ('$module_id' , '0')");
            	$db->execute();
        	}
        }
        
        // New monitoring admin module
        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_admin_monitoring');
        $status->modules[] = array('name'=>'jDownloads Admin Monitoring Module','client'=>'admin', 'result'=>$result);
        
        // admin monitoring module should be published on position 'jdcpanel'.
        $mod_array = array ("layout" => "_:default", "moduleclass_sfx" => "", "cache" => "0", "cache_time" => "900", "module_tag" => "div", "bootstrap_size" => "0", "header_tag" => "h3", "header_class" => "", "style" => "0");
        $mod_params = json_encode($mod_array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $db->setQuery("UPDATE #__modules SET `published` = '1', `position` = 'jdcpanel', `ordering` = '2', `showtitle` = '0', `params` = '".$mod_params."' WHERE `module` = 'mod_jdownloads_admin_monitoring'");
        $db->execute();
        
        //  It must also exist a dataset in the _modules_menu table to get the module visible!
        // Get ID
        $db->setQuery("SELECT id FROM #__modules WHERE `module` = 'mod_jdownloads_admin_monitoring'");
        $module_id = (int)$db->loadResult();
        
        if ($module_id){
            $db->setQuery("SELECT COUNT(*) FROM #__modules_menu WHERE `moduleid` = '$module_id'");
            $result = (int)$db->loadResult();
            // Insert it only when not already exist            
            if (!$result){
	            $db->setQuery("INSERT INTO #__modules_menu (moduleid, menuid) VALUES ('$module_id' , '0')");
	            $db->execute();
        	}
        }
        
        
      ?>
      <hr>
      <div class="adminlist" style="">
        <h4 style="color:#555;"><?php echo JText::_('COM_JDOWNLOADS_INSTALL_0'); ?></h4>
        
        <ul>

       <?php
        
       // Exist the jDownloads tables?
       // Get DB prefix string
       $prefix = self::getCorrectDBPrefix();
       $tablelist = $db->getTableList();
       
       if ( !in_array ( $prefix.'jdownloads_files', $tablelist ) ){
           Jerror::raiseWarning(null, JText::_('COM_JDOWNLOADS_INSTALL_ERROR_NO_TABLES'));         
           return false;  
       
       } else {
       
           $jd_version = $this->new_version_short;
              
               switch ($this->old_version_found){
                   
                   case '3.7':
                   case '4.0':
                        // view messages when data from prior 3.7.x version exist 
                        /*foreach ($this->old_update_message as $upd_msg){
                            echo $upd_msg;
                        }*/                       
                        
                        $monitoring = '0';
                        $old_version_found = '0';
                        
                   default:
                        // fresh installation
                        // build upload root path
                        $jd_upload_root = JPATH_ROOT.DS.'jdownloads';
                        $monitoring = '1';
                        $old_version_found = '0';
               } 
              
              if ($this->old_version_found == 0){
                  /*
                  / install config default data - but only when we have really a 'fresh' installation and we have not found any older DB tables
                  */
                  $query = array();
                     
                  // write default layouts in database      
                  $sum_layouts = 0;

                  // Categories Standard Layout  (activated by installation as default)
                  $cats_layout       = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_CATS_DEFAULT);
                  $cats_header       = stripslashes($cats_header);
                  $cats_subheader    = stripslashes($cats_subheader);
                  $cats_footer       = stripslashes($cats_footer);
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_DEFAULT_NAME'))."', 1, '".$cats_layout."', '".$cats_header."', '".$cats_subheader."', '".$cats_footer."', '', '', '', 1, 1, '*', 1)");
                  $db->execute();
                  $sum_layouts++;
                  
                  // Categories Layout with 4 columns
                  $cats_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_CATS_COL_DEFAULT); 
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, cols, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_COL_TITLE'))."', 1, '".$cats_layout."', '".$cats_header."', '".$cats_subheader."', '".$cats_footer."', '', '', 0, 1, '".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_COL_NOTE'))."', 4, '*', 2)");
                  $db->execute();
                  $sum_layouts++;

                  // Categories Layout with 2 columns
                  $cats_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_CATS_COL2_DEFAULT); 
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, cols, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_COL2_TITLE'))."', 1, '".$cats_layout."', '".$cats_header."', '".$cats_subheader."', '".$cats_footer."', '', '', 0, 1, '', 2, '*', 3)");
                  $db->execute();
                  $sum_layouts++;
                                          
                  // This layout is used to view the subcategories from a category. 
                  $cats_layout        = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_DEFAULT);
                  $cats_layout_before = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_BEFORE);
                  $cats_layout_after  = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_AFTER);
                  $cats_header       = '';
                  $cats_subheader    = '';
                  $cats_footer       = '';
                  $note              = stripslashes(JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_USE_SUBCATS_NOTE'));
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, cols, language, preview_id )  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_DEFAULT_PAGINATION_NAME'))."', 8, '".$cats_layout."', '".$cats_header."', '".$cats_subheader."', '".$cats_footer."', '".$cats_layout_before."', '".$cats_layout_after."', '".$db->escape($note)."', 1, 1, 1, '*', 4)");
                  $db->execute();
                  $sum_layouts++;

                  // This layout is used to view the subcategories from a category in a multi column example. 
                  $cats_layout        = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_MULTICOLUMN_DEFAULT);
                  $cats_layout_before = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_BEFORE);
                  $cats_layout_after  = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_AFTER);
                  $cats_header       = '';
                  $cats_subheader    = '';
                  $cats_footer       = '';
                  $note              = stripslashes(JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_USE_SUBCATS_NOTE'));
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, cols, language, preview_id )  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_SUBCAT_DEFAULT_NAME'))."', 8, '".$cats_layout."', '".$cats_header."', '".$cats_subheader."', '".$cats_footer."', '".$cats_layout_before."', '".$cats_layout_after."', '".$db->escape($note)."', 0, 1, 4, '*', 5)");
                  $db->execute();
                  $sum_layouts++;
                                          
                  // Category Standard Layout (activated by installation as default)
                  $cat_layout       = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_CAT_DEFAULT);
                  $cat_header       = stripslashes($cat_header);
                  $cat_subheader    = stripslashes($cat_subheader);
                  $cat_footer       = stripslashes($cat_footer);
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CAT_DEFAULT_NAME'))."', 4, '".$cat_layout."', '".$cat_header."', '".$cat_subheader."', '".$cat_footer."', '', '', '', 1, 1, '*', 6)");
                  $db->execute();              
                  $sum_layouts++;
                  
                  // Files Standard Layout (with mini icons)
                  $files_layout       = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT);
                  $files_header       = stripslashes($files_header);
                  $files_subheader    = stripslashes($files_subheader);
                  $files_footer       = stripslashes($files_footer);
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NAME'))."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 1, 0, '*', 7)");
                  $db->execute();
                  $sum_layouts++;

                  // Files Simple Layout with Checkboxes
                  $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NEW_SIMPLE_1); 
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NEW_SIMPLE_1_NAME'))."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 0, 1, '*', 8)");
                  $db->execute();
                  $sum_layouts++;
                        
                  // Files Simple Layout without Checkboxes (activated by installation as default)
                  $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NEW_SIMPLE_2); 
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NEW_SIMPLE_2_NAME'))."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 1, 1, '', 1, 1, '*', 9)");
                  $db->execute();
                  $sum_layouts++;

                  // Files Layout - Alternate
                  $files_layout        = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_NEW_ALTERNATE_1);
                  $files_layout_before = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_NEW_ALTERNATE_1_BEFORE);
                  $files_layout_after  = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_NEW_ALTERNATE_1_AFTER);
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NEW_ALTERNATE_1_NAME'))."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '".$files_layout_before."', '".$files_layout_after."', 0, 1, '', 1, 1, '*', 10)");
                  $db->execute();
                  $sum_layouts++;                            

                  // Files Layout with Full Info
                  $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_FULL_INFO); 
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_FULL_INFO_NAME'))."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 1, 1, '*', 11)");
                  $db->execute();
                  $sum_layouts++;
                  
                  // Files Layout - Just a Link
                  $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_JUST_LINK); 
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_JUST_LINK_NAME'))."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 1, 1, '*', 12)");
                  $db->execute();
                  $sum_layouts++;

                  // Files Layout - Single Line
                  $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_SINGLE_LINE); 
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_SINGLE_LINE_NAME'))."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 1, 1, '*', 13)");
                  $db->execute();
                  $sum_layouts++;
                  
                  // Files Layout - Compact with checkboxes v.3.9 (by Colin)
                  $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_COMPACT_CHECKBOXES); 
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_COMPACT_NAME_2'))."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 0, 1, '*', 14)");
                  $db->execute();
                  $sum_layouts++;

                  // Files Layout - Compact with download buttons v.3.9 (by Colin)
                  $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_COMPACT_WITHOUT_CHECKBOXES); 
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_COMPACT_NAME_1'))."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 1, 1, '*', 15)");
                  $db->execute();
                  $sum_layouts++;

                  // Details Standard Layout
                  $detail_layout        = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT);
                  $details_header       = stripslashes($details_header);
                  $details_subheader    = stripslashes($details_subheader);
                  $details_footer       = stripslashes($details_footer);               
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_NAME'))."', 5, '$detail_layout', '".$details_header."', '".$details_subheader."', '".$details_footer."', '', '', '', 1, 1, 1, '*', 16)");
                  $db->execute();
                  $sum_layouts++;
                  
                  // Details Layout with Tabs
                  $detail_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_WITH_TABS);
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_WITH_TABS_TITLE'))."', 5, '$detail_layout', '".$details_header."', '".$details_subheader."', '".$details_footer."', '', '', '', '0', 1, 1, '*', 17)");
                  $db->execute();
                  $sum_layouts++;
                  
                  // Details Layout with all new Data Fields v2.5
                  $detail_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_NEW_25);
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_25_TITLE'))."', 5, '$detail_layout', '".$details_header."', '".$details_subheader."', '".$details_footer."', '', '', '', '0', 1, 1, '*', 18)");
                  $db->execute();
                  $sum_layouts++;
                  
                  // Details Layout with all new Data Fields (FULL Info with Related) v3.9
                  $detail_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_WITH_RELATED);
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_WITH_RELATED_TITLE'))."', 5, '$detail_layout', '".$details_header."', '".$details_subheader."', '".$details_footer."', '', '', '', '0', 1, 1, '*', 19)");
                  $db->execute();
                  $sum_layouts++;
                  
                  // New details Layout whsh use W3.CSS option v3.9
                  $detail_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_WITH_W3CSS);
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, symbol_off, uses_w3css, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_WITH_W3CSS_NAME').' 3.9')."', 5, '$detail_layout', '".$details_header."', '".$details_subheader."', '".$details_footer."', '', '', '', '0', 1, 1, 1, '*', 23)");
                  $db->execute();
                  $sum_layouts++;              

                  // Summary Standard Layout
                  $summary_layout       = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUMMARY_DEFAULT);
                  $summary_header      = stripslashes($summary_header);
                  $summary_subheader    = stripslashes($summary_subheader);
                  $summary_footer       = stripslashes($summary_footer);              
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_SUMMARY_DEFAULT_NAME'))."', 3, '".$summary_layout."', '".$summary_header."', '".$summary_subheader."', '".$summary_footer."', '', '', '', 1, 1, '*', 20)");
                  $db->execute();
                  $sum_layouts++;

                  // default search results layout vertical
                  $search_result_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SEARCH_DEFAULT);
                  $search_header       = stripslashes($search_header);
                  $search_subheader    = stripslashes($search_subheader);
                  $search_footer       = stripslashes($search_footer);  
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, cols, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_SEARCH_DEFAULT_NAME'))."', 7, '".$search_result_layout."', '".$search_header."', '".$search_subheader."', '".$search_footer."', '', '', 1, 1, '', 4, '*', 21)");
                  $db->execute();
                  $sum_layouts++;
                  
                  // horizontal search results layout - take from $search2_header, $search2_subheader and $search2_footer
                  $search_result_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SEARCH_DEFAULT_HORIZONTAL);
                  $search_header       = stripslashes($search2_header);
                  $search_subheader    = stripslashes($search2_subheader);
                  $search_footer       = stripslashes($search2_footer);  
                  $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, cols, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_SEARCH_DEFAULT2_NAME'))."', 7, '".$search_result_layout."', '".$search_header."', '".$search_subheader."', '".$search_footer."', '', '', 0, 1, '', 4, '*', 22)");
                  $db->execute();
                  $sum_layouts++;                  
                  
                  echo '<li><font color="green">'.JText::sprintf('COM_JDOWNLOADS_INSTALL_4', $sum_layouts).'</font></li>';
              
                  // Write default licenses in database      
          
                  $lic_total = (int)JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE_TOTAL');                                 
                  $sum_licenses = 7;

                  $db->setQuery("INSERT INTO #__jdownloads_licenses (title, alias, description, url, language, published, ordering)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE1_TITLE'))."', '".ApplicationHelper::stringURLSafe(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE1_TITLE'))."', '', '".JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE1_URL')."', '*', 1, 1)");
                  $db->execute();

                  $db->setQuery("INSERT INTO #__jdownloads_licenses (title, alias, description, url, language, published, ordering)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE2_TITLE'))."', '".ApplicationHelper::stringURLSafe(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE2_TITLE'))."', '', '".JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE2_URL')."', '*', 1, 2)");
                  $db->execute();
                  
                  $db->setQuery("INSERT INTO #__jdownloads_licenses (title, alias, description, url, language, published, ordering)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE3_TITLE'))."', '".ApplicationHelper::stringURLSafe(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE3_TITLE'))."', '".JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE3_TEXT')."', '', '*', 1, 3)");
                  $db->execute();
          
                  $db->setQuery("INSERT INTO #__jdownloads_licenses (title, alias, description, url, language, published, ordering)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE4_TITLE'))."', '".ApplicationHelper::stringURLSafe(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE4_TITLE'))."', '".JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE4_TEXT')."', '', '*', 1, 4)");
                  $db->execute();

                  $db->setQuery("INSERT INTO #__jdownloads_licenses (title, alias, description, url, language, published, ordering)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE5_TITLE'))."', '".ApplicationHelper::stringURLSafe(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE5_TITLE'))."', '".JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE5_TEXT')."', '', '*', 1, 5)");
                  $db->execute();

                  $db->setQuery("INSERT INTO #__jdownloads_licenses (title, alias, description, url, language, published, ordering)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE6_TITLE'))."', '".ApplicationHelper::stringURLSafe(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE6_TITLE'))."', '', '', '*', 1, 1)");
                  $db->execute();

                  $db->setQuery("INSERT INTO #__jdownloads_licenses (title, alias, description, url, language, published, ordering)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE7_TITLE'))."', '".ApplicationHelper::stringURLSafe(JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE7_TITLE'))."', '', '".JText::_('COM_JDOWNLOADS_SETTINGS_LICENSE7_URL')."', '*', 1, 6)");
                  $db->execute();

                  self::addLog(JText::sprintf('COM_JDOWNLOADS_INSTALL_6', $sum_licenses), 'JLog::INFO');

                  echo '<li><font color="green">'.JText::sprintf('COM_JDOWNLOADS_INSTALL_6', $sum_licenses).'</font></li>';
              }              
              
              // final checks
              
              // Checked if exist Falang - if yes, move the files

              if (JFolder::exists(JPATH_SITE.'/administrator/components/com_falang/contentelements') && !JFile::exists(JPATH_SITE.'/administrator/components/com_falang/contentelements/jdownloads_files.xml')){
                  $fishresult = 1;
                  JFile::copy( JPATH_SITE."/administrator/components/com_jdownloads/assets/falang/jdownloads_categories.xml", JPATH_SITE."/administrator/components/com_falang/contentelements/jdownloads_categories.xml");
                  //JFile::copy( JPATH_SITE."/administrator/components/com_jdownloads/assets/falang/jdownloads_config.xml", JPATH_SITE."/administrator/components/com_falang/contentelements/jdownloads_config.xml");
                  JFile::copy( JPATH_SITE."/administrator/components/com_jdownloads/assets/falang/jdownloads_files.xml", JPATH_SITE."/administrator/components/com_falang/contentelements/jdownloads_files.xml");
                  JFile::copy( JPATH_SITE."/administrator/components/com_jdownloads/assets/falang/jdownloads_templates.xml", JPATH_SITE."/administrator/components/com_falang/contentelements/jdownloads_templates.xml");
                  JFile::copy( JPATH_SITE."/administrator/components/com_jdownloads/assets/falang/jdownloads_licenses.xml", JPATH_SITE."/administrator/components/com_falang/contentelements/jdownloads_licenses.xml");
                  JFile::copy( JPATH_SITE."/administrator/components/com_jdownloads/assets/falang/jdownloads_usergroups_limits.xml", JPATH_SITE."/administrator/components/com_falang/contentelements/jdownloads_usergroups_limits.xml");
                  JFolder::delete( JPATH_SITE."/administrator/components/com_jdownloads/assets/falang"); 
              } else { 
                  $fishresult = 0;
              }               
              
              if ($fishresult) {
                  self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_17')." ".JPATH_SITE.'/administrator/components/com_falang/contentelements', 'JLog::INFO');
                  echo '<li><font color="green">'.JText::_('COM_JDOWNLOADS_INSTALL_17')." ".JPATH_SITE.'/administrator/components/com_falang/contentelements'.'</font></li>';
              } else {
                  self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_18')." ".JPATH_SITE.'/administrator/components/com_jdownloads/assets/falang'.'<br />'.JText::_('COM_JDOWNLOADS_INSTALL_19'), 'JLog::INFO');
                  echo '<li><font color="green">'.JText::_('COM_JDOWNLOADS_INSTALL_18')." ".JPATH_SITE.'/administrator/components/com_jdownloads/assets/falang'.'<br />'.JText::_('COM_JDOWNLOADS_INSTALL_19').'</font></li>';
              }        
        
            // Check default upload directory 
            $dir_exist = JFolder::exists($jd_upload_root);
            
            
            if ($dir_exist) {
                if (is_writable($jd_upload_root)) {
                    self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_7'), 'JLog::INFO');
                    echo '<li><font color="green">'.JText::_('COM_JDOWNLOADS_INSTALL_7').'</font></li>';
                } else {
                    self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_8'), 'JLog::INFO');
                    echo '<li><font color="red"><strong>'.JText::_('COM_JDOWNLOADS_INSTALL_8').'</strong></font></li>';
                    
                }
            } else {
                if ($makedir =  JFolder::create($jd_upload_root, 0755)) {
                    self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_9'), 'JLog::INFO');
                    echo '<li><font color="green">'.JText::_('COM_JDOWNLOADS_INSTALL_9').'</font></li>';
                } else {
                    self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_10'), 'JLog::INFO');
                    echo '<li><font color="red"><strong>'.JText::_('COM_JDOWNLOADS_INSTALL_10').'</strong></font></li>'; 
                }
            }
            
            // Check default directory for preview files like mp3 or avi
            $dir_exist_preview = JFolder::exists($jd_upload_root.DS.'_preview_files');

            if($dir_exist_preview) {
                if (is_writable($jd_upload_root.DS.'_preview_files')) {
                    self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_30'), 'JLog::INFO');
                    echo '<li><font color="green">'.JText::_('COM_JDOWNLOADS_INSTALL_30').'</font></li>';
                } else {
                    self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_31'), 'JLog::INFO');
                    echo '<li><font color="red"><strong>'.JText::_('COM_JDOWNLOADS_INSTALL_31').'</strong></font></li>';
                }
            } else {
                if ($makedir =  JFolder::create($jd_upload_root.DS.'_preview_files', 0755)) {
                    self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_28'), 'JLog::INFO');
                    echo '<li><font color="green">'.JText::_('COM_JDOWNLOADS_INSTALL_28').'</font></li>';
                } else {
                    self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_29'), 'JLog::INFO');
                    echo '<li><font color="red"><strong>'.JText::_('COM_JDOWNLOADS_INSTALL_29').'</strong></font></li>';
                }
            }            
            
            // tempzipfiles
            $dir_existzip = JFolder::exists($jd_upload_root.DS.'_tempzipfiles');

            if($dir_existzip) {
               if (is_writable($jd_upload_root.DS.'_tempzipfiles')) {
                   self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_11'), 'JLog::INFO');
                   echo '<li><font color="green">'.JText::_('COM_JDOWNLOADS_INSTALL_11').'</font></li>';
               } else {
                   self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_12'), 'JLog::INFO');
                   echo '<li><font color="red"><strong>'.JText::_('COM_JDOWNLOADS_INSTALL_12').'</strong></font></li>';
               }
            } else {
                if ($makedir = JFolder::create($jd_upload_root.DS.'_tempzipfiles'.DS, 0755)) {
                    self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_13'), 'JLog::INFO');
                    echo '<li><font color="green">'.JText::_('COM_JDOWNLOADS_INSTALL_13').'</font></li>';
                } else {
                    self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_14'), 'JLog::INFO');
                    echo '<li><font color="red"><strong>'.JText::_('COM_JDOWNLOADS_INSTALL_14').'</strong></font></li>';
                }
             }
       
       
              echo '</ul>';

        /*
        / Display the results from the extension installation
        /
        / 
        /
        */ 
        
        $rows = 0;
        ?>                           

        
        </div>
        <hr>

        <table class="adminlist" style="width: 100%; margin:10px 10px 10px 10px;">
            <thead>
                <tr>
                    <th class="title" style="text-align:left;"><?php echo JText::_('COM_JDOWNLOADS_INSTALL_EXTENSION'); ?></th>
                    <th style="width: 50%; text-align:center;"><?php echo JText::_('COM_JDOWNLOADS_INSTALL_STATUS'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($status->modules)) : ?>
                <tr>
                    <th style="text-align:left;"><?php echo JText::_('COM_JDOWNLOADS_INSTALL_MODULE'); ?></th>
                </tr>
                <?php foreach ($status->modules as $module) : ?>
                <tr class="row<?php echo (++ $rows % 2); ?>">
                    <td class="key"><?php echo $module['name']; ?></td>
                    <td style="text-align:center;"><?php echo ($module['result'])?JText::_('COM_JDOWNLOADS_INSTALL_INSTALLED'):JText::_('COM_JDOWNLOADS_INSTALL_NOT_INSTALLED'); ?></td>
                </tr>
                <?php endforeach;?>
                <?php endif;?>
                <?php if (count($status->plugins)) : ?>
                <tr>
                    <th style="text-align:left;"><?php echo JText::_('COM_JDOWNLOADS_INSTALL_PLUGIN'); ?></th>
                </tr>
                <?php foreach ($status->plugins as $plugin) : ?>
                <tr class="row<?php echo (++ $rows % 2); ?>">
                    <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
                    <td style="text-align:center;"><?php echo ($plugin['result'])?JText::_('COM_JDOWNLOADS_INSTALL_INSTALLED'):JText::_('COM_JDOWNLOADS_INSTALL_NOT_INSTALLED'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
       }
		
	}
 
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
	
        jimport('joomla.installer.installer');
        
        $db       = JFactory::getDBO();
        $app      = JFactory::getApplication();
        $session  = JFactory::getSession();
        
        
        $params = JComponentHelper::getParams('com_jdownloads');
        
        $uninstall_options_results = array();
        
        $del_images = $session->get('del_jd_images', -1);
        $del_files  = $session->get('del_jd_files', -1);
        $del_tables = $session->get('del_jd_tables', -1);
        
        if ($del_images == -1 && $del_files == -1 && $del_tables == -1){
           // move the user to the uninstall options 
           $app->redirect(JRoute::_('index.php?option=com_jdownloads&view=uninstall', false));
           exit;
        }        
        
        $status = new JObject();
        $status->modules = array();
        $status->plugins = array();
        $src = $src = dirname(__FILE__);

        // Top Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_top" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads Top Module','client'=>'site', 'result'=>$result);
        }

        // Latest Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_latest" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads Latest Module','client'=>'site', 'result'=>$result);
        }

        // Last Upadated Downloads Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_last_updated" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads Last Updated Module','client'=>'site', 'result'=>$result);
        }        

        // Most Recently Downloaded Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_most_recently_downloaded" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads Most Recently Downloaded Module','client'=>'site', 'result'=>$result);
        }  
        
        // Stats Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_stats" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads Stats Module','client'=>'site', 'result'=>$result);
        }        
        
        // Tree Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_tree" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads Tree Module','client'=>'site', 'result'=>$result);
        }        

        // Related Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_related" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads Related Module','client'=>'site', 'result'=>$result);
        } 

        // Rated Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_rated" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads Rated Module','client'=>'site', 'result'=>$result);
        }

        // View Limits Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_view_limits" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads View Limits Module','client'=>'site', 'result'=>$result);
        }         
        
        // Featured Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_featured" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads Featured Module','client'=>'site', 'result'=>$result);
        }
        
        

        // Admin Stats Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_admin_stats" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads Admin Stats Module','client'=>'admin', 'result'=>$result);
        }

        // Admin Monitoring Module
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `element` = "mod_jdownloads_admin_monitoring" AND `type` = "module"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('module',$id,1);
            $status->modules[] = array('name'=>'jDownloads Admin Monitoring Module','client'=>'admin', 'result'=>$result);
        }
        
        // System Plugin
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `name` = "plg_system_jdownloads" AND `folder` = "system"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('plugin',$id,1);
            $status->plugins[] = array('name'=>'jDownloads System Plugin','group'=>'system', 'result'=>$result);
        }

        // Search Plugin
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `name` = "plg_search_jdownloads" AND `folder` = "search"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('plugin',$id,1);
            $status->plugins[] = array('name'=>'jDownloads Search Plugin','group'=>'search', 'result'=>$result);
        }        
        
        // Example Plugin
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = "example" AND `folder` = "jdownloads"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('plugin',$id,1);
            $status->plugins[] = array('name'=>'jDownloads Example Plugin','group'=>'jdownloads', 'result'=>$result);
        }
        
        // Button Plugin Download Link
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = "downloadlink" AND `folder` = "editors-xtd"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('plugin',$id,1);
            $status->plugins[] = array('name'=>'jDownloads Download Link Button Plugin','group'=>'editors-xtd', 'result'=>$result);
        }        		

        // Button Plugin Download Content
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = "jdownloads" AND `folder` = "editors-xtd"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('plugin',$id,1);
            $status->plugins[] = array('name'=>'jDownloads Download Content Button Plugin','group'=>'editors-xtd', 'result'=>$result);
        } 
		
        // Content Plugin
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `name` = "plg_content_jdownloads" AND `folder` = "content"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('plugin',$id,1);
            $status->plugins[] = array('name'=>'jDownloads Content Plugin','group'=>'content', 'result'=>$result);
        }

        // Finder Plugin
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `name` = "plg_finder_jdownloads" AND `folder` = "finder"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('plugin',$id,1);
            $status->plugins[] = array('name'=>'jDownloads Finder Plugin','group'=>'finder', 'result'=>$result);
        }

        // Finder Plugin
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `name` = "plg_finder_folder" AND `folder` = "finder"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('plugin',$id,1);
            $status->plugins[] = array('name'=>'jDownloads Finder Category Plugin','group'=>'finder', 'result'=>$result);
        }
        
        // Actionlog Plugin
        /* $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `name` = "plg_actionlog_jdownloads" AND `folder` = "actionlog"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('plugin',$id,1);
            $status->plugins[] = array('name'=>'jDownloads Actionlog Plugin','group'=>'actionlog', 'result'=>$result);
        }
        */
		
		// Content Plugin Tags fix
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `name` = "plg_content_jdownloads_tags_fix" AND `folder` = "content"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('plugin',$id,1);
            $status->plugins[] = array('name'=>'jDownloads Content Tags Fix Plugin','group'=>'content', 'result'=>$result);
        }

        // check uninstall type from session 
        
        // Use special font colors for some situations/options in results messages 
         
        if ($del_images == '0'){
            // we shall remove jD completely
            // at first the image folders
            $path = JPATH_ROOT.DS.'images'.DS.'jdownloads';
            if (JFolder::exists($path)){
                if (JFolder::delete($path)){
                    // add message for succesful action
                    $uninstall_options_results[] = '<p align="center"><b><span style="color:#00CC00">'.JText::_('COM_JDOWNLOADS_UNINSTALL_IMAGES_DELETED').'</b></p>';
                } else {
                    // add message for not succesful action
                    $uninstall_options_results[] = '<p align="center"><b><span style="color:#FF0000">'.JText::_('COM_JDOWNLOADS_UNINSTALL_IMAGES_NOT_DELETED').'</b></p>';
                }
            } else {
                // folder not found
                $uninstall_options_results[] = '<p align="center"><b><span style="color:#FF0000">Image folder not found!</b></p>';
            }
        } else {
            $uninstall_options_results[] = '<p align="center"><b><span style="color:#FF8040">'.JText::_('COM_JDOWNLOADS_UNINSTALL_IMAGES_NOT_SELECTED').'</b></p>';
        }
            
        if ($del_files == '0'){            
            
            // delete upload folder with all files as next  
            $path = $params->get('files_uploaddir');

            if ($path && JFolder::exists($path)){
                if (JFolder::delete($path)){
                    // add message for succesful action
                    $uninstall_options_results[] = '<p align="center"><b><span style="color:#00CC00">'.JText::_('COM_JDOWNLOADS_UNINSTALL_FILES_DELETED').'</b></p>';
                } else {
                    // add message for not succesful action
                    $uninstall_options_results[] = '<p align="center"><b><span style="color:#FF0000">'.JText::_('COM_JDOWNLOADS_UNINSTALL_FILES_NOT_DELETED').'</b></p>';
                }
            } else {
                // folder not found
                $uninstall_options_results[] = '<p align="center"><b><span style="color:#FF0000">Upload folder not found!</b></p>';
            }
        } else {
            $uninstall_options_results[] = '<p align="center"><b><span style="color:#FF8040">'.JText::_('COM_JDOWNLOADS_UNINSTALL_FILES_NOT_SELECTED').'</b></p>';
        }
        
        if ($del_tables == '0'){            
            
            // delete database tables now
            $db->setQuery('DROP TABLE IF EXISTS #__jdownloads_categories, #__jdownloads_files, #__jdownloads_licenses, #__jdownloads_logs, #__jdownloads_ratings, #__jdownloads_templates, #__jdownloads_usergroups_limits');
            $result = $db->execute();
            if ($result === true){
                // add message for succesful action
                $uninstall_options_results[] = '<p align="center"><b><span style="color:#00CC00">'.JText::_('COM_JDOWNLOADS_UNINSTALL_TABLES_DELETED').'</b></p>';
            } else {
                // add message for not succesful action
                $uninstall_options_results[] = '<p align="center"><b><span style="color:#FF0000">'.JText::_('COM_JDOWNLOADS_UNINSTALL_TABLES_NOT_DELETED').'</b></p>';
            }
        } else {
            $uninstall_options_results[] = '<p align="center"><b><span style="color:#FF8040">'.JText::_('COM_JDOWNLOADS_UNINSTALL_TABLES_NOT_SELECTED').'</b></p>';
        }
        
        $session->set('del_jd_images', -1);
        $session->set('del_jd_files',  -1);
        $session->set('del_jd_tables', -1);
        
        $msg = '<h4>'.JText::_('COM_JDOWNLOADS_DEINSTALL_0').'</h4><hr>';
        foreach ($uninstall_options_results as $result){
            $msg .= $result; 
        }
        $msg .= '<hr>';
                
        $msg .= '       
        <table class="adminlist" width="100%">
            <thead>
                <tr>
                    <th class="title" style="text-align:left;">'.JText::_('COM_JDOWNLOADS_INSTALL_EXTENSION').'</th>
                    <th style="width:50%; text-align:center;">'.JText::_('COM_JDOWNLOADS_INSTALL_STATUS').'</th>
                </tr>
            </thead>
            <tbody>
                <tr class="row0">
                    <td class="key">'.JText::_('COM_JDOWNLOADS_INSTALL_COMPONENT').' '.JText::_('COM_JDOWNLOADS_INSTALL_JDOWNLOADS').'</td>
                    <td style="text-align:center;">'.JText::_('COM_JDOWNLOADS_DEINSTALL_REMOVED').'</td>
                </tr>';

        if (count($status->modules)){
            $msg .=
            '<tr>
                <th style="text-align:left;">'.JText::_('COM_JDOWNLOADS_INSTALL_MODULE').'</th>
            </tr>';
        
            foreach ($status->modules as $module) {
                $msg .= 
                '<tr class="">
                <td class="key">'.$module['name'].'</td>
                <td style="text-align:center">';
                if ($module['result']){
                    $msg .= JText::_('COM_JDOWNLOADS_DEINSTALL_REMOVED').'</td></tr>';
                }else {
                    $msg .= JText::_('COM_JDOWNLOADS_DEINSTALL_NOT_REMOVED').'</td></tr>';
                }
            }
        }

        if (count($status->plugins)){
            $msg .=
            '<tr>
                <th style="text-align:left;">'.JText::_('COM_JDOWNLOADS_INSTALL_PLUGIN').'</th>
            </tr>';
            foreach ($status->plugins as $plugin){
                $msg .=
                '<tr class="">
                    <td class="key">'.ucfirst($plugin['name']).'</td>
                    <td style="text-align:center;">';
                if ($plugin['result']){
                    $msg .= JText::_('COM_JDOWNLOADS_DEINSTALL_REMOVED').'</td></tr>';
                }else {
                    $msg .= JText::_('COM_JDOWNLOADS_DEINSTALL_NOT_REMOVED').'</td></tr>';
                }
            }
        }
        $msg .=
            '</tbody>
        </table>
        <hr>';
        echo $msg;
        $session->set('jd_uninstall_msg', $msg);
	}
 
	/**
	 * method to update the component
     * 
     * We can update only from a 3.9 series or from the last published version the 3.2.x series
	 * ========================================================================================
     * 
	 * @return void
	 */
	function update($parent) 
	{
        // try to set time limit
        @set_time_limit(0);

        // try to increase memory limit
        if ((int) ini_get( 'memory_limit' ) < 32){
            @ini_set( 'memory_limit', '32M' );
        }
        
        if (!defined('DS')){
           define('DS',DIRECTORY_SEPARATOR);
        }
        
        $user   = JFactory::getUser();
                
        $db = JFactory::getDBO();
        $prefix = self::getCorrectDBPrefix();
        $tablelist = $db->getTableList();

        // Add a log entry
        self::addLog(JText::sprintf('COM_JDOWNLOADS_UPDATE_LOG_START', $user->id, $user->name, $this->old_version_short, $this->new_version), 'JLog::INFO');
        
        $rows = 0;
        $amount_mod = 0;
        $amount_plg = 0;
       
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');        

        // Copy new file type icon sets when not exist
        $target = JPATH_ROOT.DS.'images'.DS.'jdownloads'.DS.'fileimages';
        $source = dirname(__FILE__).DS.'site'.DS.'assets'.DS.'images'.DS.'jdownloads'.DS.'fileimages';

        if (!JFolder::exists($target.DS.'flat_1')){
            $res = JFolder::copy($source.DS.'flat_1', $target.DS.'flat_1');
            $res = JFolder::copy($source.DS.'flat_2', $target.DS.'flat_2');
            if ($res){
                self::addLog(JText::_('Modern file type icon sets added!'), 'JLog::INFO');
            }
        }
        
        // Copy new folder type icons (for categories) when not exist (overwrite) 
        $target = JPATH_ROOT.DS.'images'.DS.'jdownloads'.DS.'catimages';
        $source = dirname(__FILE__).DS.'site'.DS.'assets'.DS.'images'.DS.'jdownloads'.DS.'catimages';

        if (!JFile::exists($target.DS.'folder-ubuntu-home.png')){
            $res = JFolder::copy($source, $target, null, true);
            if ($res){
                self::addLog(JText::_('Additional Folder Icons added!'), 'JLog::INFO');
            }
        } 
        
        // install the new modules when this not already exists
        jimport('joomla.installer.installer');

        $status = new JObject();
        $status->modules = array();
        $status->plugins = array();
        
        $src_modules = dirname(__FILE__).DS.'modules';
        $src_plugins = dirname(__FILE__).DS.'plugins';
        
        // we must install again all modules and plugins since it can be that we must also install here an update
        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_latest');
        $status->modules[] = array('name'=>'jDownloads Latest Module','client'=>'site', 'result'=>$result);
        if ($result) $amount_mod ++;

        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_top');
        $status->modules[] = array('name'=>'jDownloads Top Module','client'=>'site', 'result'=>$result);
        if ($result) $amount_mod ++;

        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_last_updated');
        $status->modules[] = array('name'=>'jDownloads Last Updated Module','client'=>'site', 'result'=>$result);
        if ($result) $amount_mod ++;

        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_most_recently_downloaded');
        $status->modules[] = array('name'=>'jDownloads Most Recently Downloaded Module','client'=>'site', 'result'=>$result);
        if ($result) $amount_mod ++;
        
        $installer = new JInstaller;                                                                                     
        $result = $installer->install($src_modules.DS.'mod_jdownloads_stats');
        $status->modules[] = array('name'=>'jDownloads Stats Module','client'=>'site', 'result'=>$result);        
        if ($result) $amount_mod ++;

        $installer = new JInstaller;                                                                                     
        $result = $installer->install($src_modules.DS.'mod_jdownloads_tree');
        $status->modules[] = array('name'=>'jDownloads Tree Module','client'=>'site', 'result'=>$result);         
        if ($result) $amount_mod ++;

        $installer = new JInstaller;                                                                                     
        $result = $installer->install($src_modules.DS.'mod_jdownloads_related');
        $status->modules[] = array('name'=>'jDownloads Related Module','client'=>'site', 'result'=>$result);        
        if ($result) $amount_mod ++;

        $installer = new JInstaller;                                                                                     
        $result = $installer->install($src_modules.DS.'mod_jdownloads_rated');
        $status->modules[] = array('name'=>'jDownloads Rated Module','client'=>'site', 'result'=>$result);
        if ($result) $amount_mod ++;
        
        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_featured');
        $status->modules[] = array('name'=>'jDownloads Featured Module','client'=>'site', 'result'=>$result);
        if ($result) $amount_mod ++;

        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_view_limits');
        $status->modules[] = array('name'=>'jDownloads View Limits Module','client'=>'site', 'result'=>$result);
        if ($result) $amount_mod ++;
        
        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_admin_stats');
        $status->modules[] = array('name'=>'jDownloads Admin Stats Module','client'=>'admin', 'result'=>$result);
        if ($result) $amount_mod ++;

        $installer = new JInstaller;
        $result = $installer->install($src_modules.DS.'mod_jdownloads_admin_monitoring');
        $status->modules[] = array('name'=>'jDownloads Admin Monitoring Module','client'=>'admin', 'result'=>$result);
        if ($result) $amount_mod ++;
        
        self::addLog(JText::sprintf('COM_JDOWNLOADS_UPDATE_LOG_MODS_INSTALLED_UPDATED', $amount_mod), 'JLog::INFO');
        
        // admin stats module should now be published on position 'jdcpanel'.
        $mod_array = array ("view_latest" => "1", "view_popular" => "1", "view_featured" => "1", "view_most_rated" => "1", "view_top_rated" => "1", "amount_items" => "5", "view_statistics" => "1", "view_monitoring_log" => "1", "view_restore_log" => "1", "view_server_info" => "1", "layout" => "_:default", "moduleclass_sfx" => "", "cache" => "0", "cache_time" => "900", "module_tag" => "div", "bootstrap_size" => "0", "header_tag" => "h3", "header_class" => "", "style" => "0");
        $mod_params = json_encode($mod_array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $db->setQuery("UPDATE #__modules SET `published` = '1', `position` = 'jdcpanel', `ordering` = '1', `showtitle` = '0', `params` = '".$mod_params."' WHERE `module` = 'mod_jdownloads_admin_stats'");
        $db->execute();        
        
        // It must also exist a dataset in the _modules_menu table to get the module visible!
        // Get ID
        $db->setQuery("SELECT id FROM #__modules WHERE `module` = 'mod_jdownloads_admin_stats'");
        $module_id = (int)$db->loadResult();
        
        $db->setQuery("SELECT COUNT(*) FROM #__modules_menu WHERE `moduleid` = '$module_id'");
        $result = (int)$db->loadResult();
        
        if (!$result && $module_id){
            $db->setQuery("INSERT INTO #__modules_menu (moduleid, menuid) VALUES ('$module_id' , '0')");
            $db->execute();
        }

        // admin monitoring module should be published on position 'jdcpanel'.
        $mod_array = array ("layout" => "_:default", "moduleclass_sfx" => "", "cache" => "0", "cache_time" => "900", "module_tag" => "div", "bootstrap_size" => "0", "header_tag" => "h3", "header_class" => "", "style" => "0");
        $mod_params = json_encode($mod_array);
        $db->setQuery("UPDATE #__modules SET `published` = '1', `position` = 'jdcpanel', `ordering` = '2', `showtitle` = '0', `params` = '".$mod_params."' WHERE `module` = 'mod_jdownloads_admin_monitoring'");
        $db->execute();
        
        //  It must also exist a dataset in the _modules_menu table to get the module visible!
        // Get ID
        $db->setQuery("SELECT id FROM #__modules WHERE `module` = 'mod_jdownloads_admin_monitoring'");
        $module_id = (int)$db->loadResult();
        
        $db->setQuery("SELECT COUNT(*) FROM #__modules_menu WHERE `moduleid` = '$module_id'");
        $result = (int)$db->loadResult();
        
        if (!$result && $module_id){
            $db->setQuery("INSERT INTO #__modules_menu (moduleid, menuid) VALUES ('$module_id' , '0')");
        $db->execute();        
        }
        
        // Plugins
        
        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_system_jdownloads');
        $status->plugins[] = array('name'=>'jDownloads System Plugin','group'=>'system', 'result'=>$result);
        if ($result) $amount_plg ++;
                
        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_search_jdownloads');
        $status->plugins[] = array('name'=>'jDownloads Search Plugin','group'=>'search', 'result'=>$result);       
        if ($result) $amount_plg ++;
        
        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'editor_button_plugin_jdownloads_downloads');
        $status->plugins[] = array('name'=>'jDownloads Download Content Button Plugin','group'=>'editors-xtd', 'result'=>$result);               
        if ($result) $amount_plg ++;

        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_content_jdownloads');
        $status->plugins[] = array('name'=>'jDownloads Content Plugin','group'=>'content', 'result'=>$result);               
        if ($result) $amount_plg ++;

        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_jdownloads_example');
        $status->plugins[] = array('name'=>'jDownloads Example Plugin','group'=>'jdownloads', 'result'=>$result);
        if ($result) $amount_plg ++;
        
        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_finder_jdownloads');
        $status->plugins[] = array('name'=>'jDownloads Finder Plugin','group'=>'finder', 'result'=>$result);
        if ($result) $amount_plg ++;

        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_finder_folder');
        $status->plugins[] = array('name'=>'jDownloads Finder Category Plugin','group'=>'finder', 'result'=>$result);
        if ($result) $amount_plg ++;
        
        /* $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_actionlog_jdownloads');
        $status->plugins[] = array('name'=>'jDownloads Actionlog Plugin','group'=>'actionlog', 'result'=>$result);        
        if ($result) $amount_plg ++;
        */

        $installer = new JInstaller;
        $result = $installer->install($src_plugins.DS.'plg_content_jdownloads_tags_fix');
        $status->plugins[] = array('name'=>'jDownloads Content Tags Fix Plugin','group'=>'content', 'result'=>$result);
        if ($result) $amount_plg ++;
        
		// tags fix plugin must be enabled 
        $db->setQuery("UPDATE #__extensions SET enabled = '1' WHERE `name` = 'plg_content_jdownloads_tags_fix' AND `type` = 'plugin'");
        $db->execute();		
        
        // delete old tags fix plugin from 3.2 when exist
        $db->setQuery('SELECT `extension_id` FROM #__extensions WHERE `type` = "plugin" AND `element` = "jdownloads_tags_fix" AND `folder` = "content"');
        $id = $db->loadResult();
        if($id)
        {
            $installer = new JInstaller;
            $result = $installer->uninstall('plugin',$id,1);
            // $status->plugins[] = array('name'=>'jDownloads Content Tags Fix Plugin','group'=>'content', 'result'=>$result);
        }

        self::addLog(JText::sprintf('COM_JDOWNLOADS_UPDATE_LOG_PLGS_INSTALLED_UPDATED', $amount_plg), 'JLog::INFO');
        
        // We must add default values for some user groups 'importance' fields.        
        $db->setQuery("SELECT COUNT(*) FROM #__jdownloads_usergroups_limits WHERE importance > 0");
        $importance_values_exists = $db->loadResult();
        
        if (!$importance_values_exists){
            // Get all rules
            $db->setQuery("SELECT * FROM #__jdownloads_usergroups_limits");
            $jd_groups = $db->loadObjectList();
            
            // Create the default values
            if ($jd_groups){
                   for ($i=0; $i < count($jd_groups); $i++) {
                       if ((int)$jd_groups[$i]->group_id == 1){ 
                           $importance = 1; 
                        } elseif ((int)$jd_groups[$i]->group_id == 2){ 
                           $importance = 20;
                        } elseif ((int)$jd_groups[$i]->group_id == 3){ 
                            $importance = 30;
                        } elseif ((int)$jd_groups[$i]->group_id == 4){ 
                            $importance = 40;
                        } elseif ((int)$jd_groups[$i]->group_id == 5){ 
                            $importance = 50;
                        } elseif ((int)$jd_groups[$i]->group_id == 6){ 
                            $importance = 60;
                        } elseif ((int)$jd_groups[$i]->group_id == 7){ 
                            $importance = 70;
                        } elseif ((int)$jd_groups[$i]->group_id == 8){ 
                            $importance = 100;
                        } else {
                            $importance = 0;
                        }
                        $id = (int)$jd_groups[$i]->id;
                        $db->SetQuery("UPDATE #__jdownloads_usergroups_limits SET importance = '$importance' WHERE id = '$id'");
                        $db->execute();
                   }
            }           
        } // end jdownloads_usergroups_limits update 
        
        echo '<h4 style="color:#555;">' . JText::_('COM_JDOWNLOADS_UPDATE_TEXT') . '</h4>';
        
        if (count($status->modules) || count($status->plugins)){
            ?>    
            <hr>
            <table class="adminlist" style="width:100%; margin:10px 10px 10px 10px;">
                <thead>
                    <tr>
                        <th class="title" style="text-align:left;"><?php echo JText::_('COM_JDOWNLOADS_INSTALL_EXTENSION'); ?></th>
                        <th style="width:50%; text-align:center;"><?php echo JText::_('COM_JDOWNLOADS_INSTALL_STATUS'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($status->modules)) : ?>
                    <tr>
                        <th style="text-align:left;"><?php echo JText::_('COM_JDOWNLOADS_INSTALL_MODULE'); ?></th>
                    </tr>
                    <?php foreach ($status->modules as $module) : ?>
                    <tr class="row<?php echo (++ $rows % 2); ?>">
                        <td class="key"><?php echo $module['name']; ?></td>
                        <td style="text-align:center;"><?php echo ($module['result'])?JText::_('COM_JDOWNLOADS_INSTALL_INSTALLED'):JText::_('COM_JDOWNLOADS_INSTALL_NOT_INSTALLED'); ?></td>
                    </tr>
                    <?php endforeach;?>
                    <?php endif;?>
                    <?php if (count($status->plugins)) : ?>
                    <tr>
                        <th style="text-align:left;"><?php echo JText::_('COM_JDOWNLOADS_INSTALL_PLUGIN'); ?></th>
                    </tr>
                    <?php foreach ($status->plugins as $plugin) : ?>
                    <tr class="row<?php echo (++ $rows % 2); ?>">
                        <td class="key"><?php echo ucfirst($plugin['name']); ?></td>
                        <td style="text-align:center;"><?php echo ($plugin['result'])?JText::_('COM_JDOWNLOADS_INSTALL_INSTALLED'):JText::_('COM_JDOWNLOADS_INSTALL_NOT_INSTALLED'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>            
            <?php            
        }
        
        // Must we update the db tables from 3.2.6x?
        if ($this->old_version_found){
            if ($this->run_upgrade_from_32){
                // ************************
                // Try to start the upgrade 
                // ************************
                self::addLog(JText::_('COM_JDOWNLOADS_UPGRADE32_HINT_5'), 'JLog::INFO');
                
                // Update the table structure - Important also for any backup restoration process to a later date as the tables structure must be identical.
                // Categories table
                $tablefields = $db->getTableColumns('#__jdownloads_categories'); 
                if ( !isset($tablefields['user_access']) ){
                    // Delete at first the old (and maybe to long) index
                    $db->setQuery('ALTER TABLE '. $db->quoteName('#__jdownloads_categories') . ' DROP KEY ' . $db->quoteName('idx_alias'));
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // Change categories alias field length to 191                                          
                    $db->setQuery("ALTER TABLE #__jdownloads_categories CHANGE alias alias varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT ''");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // Create the alias index again
                    $db->setQuery('ALTER TABLE '. $db->quoteName('#__jdownloads_categories') . ' ADD KEY '  . $db->quoteName('idx_alias') . '(' . $db->quoteName('alias') . '(100))');
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // create the missing field 
                    $db->setQuery("ALTER TABLE #__jdownloads_categories ADD `user_access` int(11) unsigned NOT NULL DEFAULT '0' AFTER `access`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // change access field
                    $db->setQuery("ALTER TABLE #__jdownloads_categories MODIFY `access` int(10) unsigned NOT NULL DEFAULT '0'");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // add new index
                    $db->setQuery("ALTER TABLE #__jdownloads_categories ADD INDEX `idx_published` (`published`)");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                } 

                // Files table
                $tablefields = $db->getTableColumns('#__jdownloads_files'); 
                if ( !isset($tablefields['user_access']) ){
                    // Delete at first the old (and maybe to long) index
                    $db->setQuery('ALTER TABLE '. $db->quoteName('#__jdownloads_files') . ' DROP KEY ' . $db->quoteName('idx_alias'));
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // Change files alias field length to 191                                          
                    $db->setQuery("ALTER TABLE #__jdownloads_files CHANGE file_alias alias varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT ''");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // Create the alias index again
                    $db->setQuery('ALTER TABLE '. $db->quoteName('#__jdownloads_files') . ' ADD KEY '  . $db->quoteName('idx_alias') . '(' . $db->quoteName('alias') . '(190))');
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // create the missing field
                    $db->setQuery("ALTER TABLE #__jdownloads_files ADD `user_access` int(11) unsigned NOT NULL DEFAULT '0' AFTER `access`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }

                    // change access field
                    $db->setQuery("ALTER TABLE #__jdownloads_files MODIFY `access` int(10) unsigned NOT NULL DEFAULT '0'");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // add new index
                    $db->setQuery("ALTER TABLE #__jdownloads_files ADD INDEX `idx_user_access` (`user_access`)");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }

                    // delete deprecated fields
                    $db->setQuery("ALTER TABLE #__jdownloads_files DROP COLUMN `thumbnail`, DROP COLUMN `thumbnail2`, DROP COLUMN `thumbnail3`,  DROP COLUMN `created_by`, DROP COLUMN `modified_by`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // We must delete also old custom fields. As we use here the update process the user has already removed the prior activated custom fields.
                    $db->setQuery("ALTER TABLE #__jdownloads_files DROP COLUMN `custom_field_1`, DROP COLUMN `custom_field_2`, DROP COLUMN `custom_field_3`,  DROP COLUMN `custom_field_4`, DROP COLUMN `custom_field_5`, DROP COLUMN `custom_field_6`,
                                                                   DROP COLUMN `custom_field_7`, DROP COLUMN `custom_field_8`, DROP COLUMN `custom_field_9`, DROP COLUMN `custom_field_10`, DROP COLUMN `custom_field_11`, DROP COLUMN `custom_field_12`,
                                                                   DROP COLUMN `custom_field_13`, DROP COLUMN `custom_field_14`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }

                    // Rename some fields to the new identifier
                    $db->setQuery("ALTER TABLE #__jdownloads_files CHANGE file_id id int(11) NOT NULL AUTO_INCREMENT");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    $db->setQuery("ALTER TABLE #__jdownloads_files CHANGE file_title title varchar(255) NOT NULL DEFAULT ''");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }

                    $db->setQuery("ALTER TABLE #__jdownloads_files CHANGE cat_id catid int(11) NOT NULL DEFAULT '0'");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }

                    $db->setQuery("ALTER TABLE #__jdownloads_files CHANGE date_added created datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                                                                                             
                    $db->setQuery("ALTER TABLE #__jdownloads_files CHANGE publish_from publish_up datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    $db->setQuery("ALTER TABLE #__jdownloads_files CHANGE publish_to publish_down datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    $db->setQuery("ALTER TABLE #__jdownloads_files CHANGE created_id created_by int(11) NOT NULL DEFAULT '0'");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    $db->setQuery("ALTER TABLE #__jdownloads_files CHANGE modified_id modified_by int(11) NOT NULL DEFAULT '0'");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    $db->setQuery("ALTER TABLE #__jdownloads_files CHANGE modified_date modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00'");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                }                
                
                // Usergroups limits table
                $tablefields = $db->getTableColumns('#__jdownloads_usergroups_limits'); 
                if ( !isset($tablefields['form_fieldset']) ){
                    // create the missing fields
                    $db->setQuery("ALTER TABLE #__jdownloads_usergroups_limits ADD `form_fieldset` CHAR( 100 ) NOT NULL AFTER `must_form_fill_out`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    $db->setQuery("ALTER TABLE #__jdownloads_usergroups_limits ADD `form_user_access` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `form_access_x`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // Modify
                    $db->setQuery("ALTER TABLE #__jdownloads_usergroups_limits MODIFY `group_id` int(10) NOT NULL DEFAULT '0'");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    //  delete the old custom fields setting fields
                    $db->setQuery("ALTER TABLE #__jdownloads_usergroups_limits DROP COLUMN `form_extra_select_box_1`, DROP COLUMN `form_extra_select_box_1_x`, DROP COLUMN `form_extra_select_box_2`, DROP COLUMN `form_extra_select_box_2_x`, 
                                                                                    DROP COLUMN `form_extra_select_box_3`, DROP COLUMN `form_extra_select_box_3_x`, DROP COLUMN `form_extra_select_box_4`, DROP COLUMN `form_extra_select_box_4_x`,
                                                                                    DROP COLUMN `form_extra_select_box_5`, DROP COLUMN `form_extra_select_box_5_x`, DROP COLUMN `form_extra_short_input_1`, DROP COLUMN `form_extra_short_input_1_x`,
                                                                                    DROP COLUMN `form_extra_short_input_2`, DROP COLUMN `form_extra_short_input_2_x`, DROP COLUMN `form_extra_short_input_3`, DROP COLUMN `form_extra_short_input_3_x`,
                                                                                    DROP COLUMN `form_extra_short_input_4`, DROP COLUMN `form_extra_short_input_4_x`, DROP COLUMN `form_extra_short_input_5`, DROP COLUMN `form_extra_short_input_5_x`,
                                                                                    DROP COLUMN `form_extra_date_1`, DROP COLUMN `form_extra_date_1_x`, DROP COLUMN `form_extra_date_2`, DROP COLUMN `form_extra_date_2_x`, DROP COLUMN `form_extra_large_input_1`,
                                                                                    DROP COLUMN `form_extra_large_input_1_x`, DROP COLUMN `form_extra_large_input_2`, DROP COLUMN `form_extra_large_input_2_x`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                }
                
                $tablefields = $db->getTableColumns('#__jdownloads_usergroups_limits'); 
                if ( !isset($tablefields['form_select_from_other']) ){
                    // create the missing fields
                    $db->setQuery("ALTER TABLE #__jdownloads_usergroups_limits ADD `form_select_from_other` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `form_tags`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                }
                          
                // New template fields
                $tablefields = $db->getTableColumns('#__jdownloads_templates'); 
                if ( !isset($tablefields['uses_bootstrap']) ){
                    // create the missing field
                    $db->setQuery("ALTER TABLE #__jdownloads_templates ADD `uses_bootstrap` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `cols`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    $db->setQuery("ALTER TABLE #__jdownloads_templates ADD `uses_w3css` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `uses_bootstrap`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                    
                    // Change the name length to 255
                    $db->setQuery("ALTER TABLE #__jdownloads_templates CHANGE template_name template_name varchar(255) NOT NULL DEFAULT ''");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }

	            }
                if ( !isset($tablefields['preview_id']) ){
                    // create the missing field
                    $db->setQuery("ALTER TABLE #__jdownloads_templates ADD `preview_id` TINYINT( 3 ) NOT NULL DEFAULT '0' AFTER `cols`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                }
            } else {
                // Only required by an update from pre beta 6 to public beta 6
                // New template field
                $tablefields = $db->getTableColumns('#__jdownloads_templates');             
                if ( !isset($tablefields['preview_id']) ){
                    // create the missing field
                    $db->setQuery("ALTER TABLE #__jdownloads_templates ADD `preview_id` TINYINT( 3 ) NOT NULL DEFAULT '0' AFTER `cols`");
                    try {
                        $db->execute();
                    } catch(RuntimeException $e) {
                        self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                        JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
                    }
                }
            }
        }
        
        // Only required by an update from beta 5 to public beta 6
        // New template field
        $tablefields = $db->getTableColumns('#__jdownloads_templates');             
        if ( !isset($tablefields['preview_id']) ){
            // create the missing field
            $db->setQuery("ALTER TABLE #__jdownloads_templates ADD `preview_id` TINYINT( 3 ) NOT NULL DEFAULT '0' AFTER `cols`");
            try {
                $db->execute();
            } catch(RuntimeException $e) {
                self::addLog(JText::_($e->getMessage()), 'JLog::ERROR');
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
		}
        
    }
        
 
	/**
	 * method to run before an install/update/
     * 
	 * @return void
	 */
	function preflight($type, $parent) 
	{
        
        $parent   = $parent->getParent();
        $source   = $parent->getPath("source");
        $manifest = $parent->get("manifest");
        $db = JFactory::getDBO();
        
        $session = JFactory::getSession();        

        $this->dbversion = $db->getVersion();
        
        $this->old_version_found = 0;
        $this->run_upgrade_from_32 = 0;
        
        $pos = strpos($manifest->version, ' ');
        if ($pos){
            $this->new_version_short     = substr($manifest->version, 0, $pos);
        } else {
            $this->new_version_short     = $manifest->version;
        }    
        $this->new_version              = (string)$manifest->version;
        $this->target_joomla_version    = (string)$manifest->targetjoomla;
        $this->minimum_databases        = (string)$manifest->minimum_databases;
        
        // Add a log entry - with basic system information at first
        self::addLog('', 'JLog::INFO', true);                
        
        if ( $type == 'install'){     
            self::addLog('------------------------------------------------------ Installation Started', '');                
        }
        if ( $type == 'update'){     
            self::addLog('------------------------------------------------------ Update Started', '');                
        }
        
        $prefix = self::getCorrectDBPrefix();
        $tablelist = $db->getTableList();
        
        if ( $type == 'install' || $type == 'update' ) {
            // this component does only work with Joomla release 3.9 or higher - otherwise abort
            $jversion     = new JVersion;
            $jversion_value = $jversion->getShortVersion();
        
            if (!$jversion->isCompatible($this->target_joomla_version)) {
                // is not the required joomla target version
                Jerror::raiseWarning(null, JText::_('COM_JDOWNLOADS_INSTALL_WRONG_JOOMLA_RELEASE'));
                return false;
            }
         
            if ( $type == 'update' ) {
                $component_header = JText::_('COM_JDOWNLOADS_DESCRIPTION');
                $typetext = JText::_('COM_JDOWNLOADS_INSTALL_TYPE_UPDATE');
                $db->setQuery('SELECT * FROM #__extensions WHERE `element` = "com_jdownloads" AND `type` = "component"');
                $item = $db->loadObject();
                $old_manifest = json_decode($item->manifest_cache); 
                $pos = strpos($old_manifest->version, ' ');
                if ($pos){
                    $this->old_version_short = substr($old_manifest->version, 0, $pos);    
                } else {
                    $this->old_version_short = $old_manifest->version;    
                } 
                
                $rel = $this->old_version_short . ' to ' . $this->new_version;

                // Exist data from old 3.2 series and it is the right version to try the upgrade?
                if (in_array ( $prefix.'jdownloads_config', $tablelist) && (substr($this->old_version_short, 0, 3) == '3.2' )) {
                    if (version_compare($old_manifest->version, $manifest->requiredForUpgrade) >= 0 ){
                        $this->old_version_found = 1;
                        $this->run_upgrade_from_32 = 1;    

                        // Check if DB & version is supported via <minimum_databases> tag, assume supported if tag isn't present
                        if (isset($this->minimum_databases) && $this->minimum_databases){
                            $dbMatch = false;
                            $supportedDbs = array();

                            $dbType     = strtolower($db->getServerType());
                            $dbVersion  = $db->getVersion();
                            $array      = explode(" ", $this->minimum_databases);
                            foreach ($array as $result) {
                                $b = explode('=', $result);
                                $supportedDbs[$b[0]] = $b[1];
                            }
 
                            // MySQL and MariaDB use the same database driver but not the same version numbers
                            if ($dbType === 'mysql'){
                                // Check whether we have a MariaDB version string and extract the proper version from it
                                if (stripos($dbVersion, 'mariadb') !== false){
                                    // MariaDB: Strip off any leading '5.5.5-', if present
                                    $dbVersion = preg_replace('/^5\.5\.5-/', '', $dbVersion);
                                    $dbType    = 'mariadb';
                                }
                            }

                            // Do we have an entry for the database?
                            if (array_key_exists($dbType, $supportedDbs)){
                                $minumumVersion = $supportedDbs[$dbType];
                                $dbMatch        = version_compare($dbVersion, $minumumVersion, '>=');

                                if (!$dbMatch){
                                    // Notify the user of the potential update
                                    $dbMsg = \JText::sprintf(
                                        'COM_JDOWNLOADS_UPGRADE32_AVAILABLE_UPDATE_DB_MINIMUM',
                                        'jDownloads',
                                        $this->new_version,
                                        \JText::_($db->name),
                                        $dbVersion,
                                        $minumumVersion
                                    );

                                    // Upgrade abort with hint about db problem
                                    $message = '<div>
                                                    <h5>'.$dbMsg.'</h5>
                                                </div>';
                                    JError::raiseWarning(null, $message);         
                                    return false;
                                }
                            }
                        }
                    
                        //*********************************************
                        // Create first a backup from the jDownloads v3.2 tables when not already exist
                        //*********************************************
                    
                        $this->tables_copied = 0;
                        $exists_already = 0;
                        $backup = 'backup_'. $this->old_version_short;
                        $backup = str_replace('.', '_', $backup);
                        
                        if (!in_array ( $prefix."jdownloads_categories_$backup", $tablelist)) {
                            $query = $db->getQuery(true);
                            $db->setQuery("CREATE TABLE #__jdownloads_categories_$backup LIKE #__jdownloads_categories");
                            if ($db->execute()){
                                $query = $db->getQuery(true);
                                $db->setQuery("INSERT INTO #__jdownloads_categories_$backup SELECT * FROM #__jdownloads_categories");
                                if ($db->execute()) $this->tables_copied++;
                            }
                        } else {
                            $exists_already ++;
                        }

                        if (!in_array ( $prefix."jdownloads_files_$backup", $tablelist)) {
                            $query = $db->getQuery(true);
                            $db->setQuery("CREATE TABLE #__jdownloads_files_$backup LIKE #__jdownloads_files");
                            if ($db->execute()){
                                $query = $db->getQuery(true);
                                $db->setQuery("INSERT INTO #__jdownloads_files_$backup SELECT * FROM #__jdownloads_files");
                                if ($db->execute()) $this->tables_copied++;
                            }
                        } else {
                            $exists_already ++;
                        }
                        
                        if (!in_array ( $prefix."jdownloads_licenses_$backup", $tablelist)) {
                            $query = $db->getQuery(true);
                            $db->setQuery("CREATE TABLE #__jdownloads_licenses_$backup LIKE #__jdownloads_licenses");
                            if ($db->execute()){
                                $query = $db->getQuery(true);
                                $db->setQuery("INSERT INTO #__jdownloads_licenses_$backup SELECT * FROM #__jdownloads_licenses");
                                if ($db->execute()) $this->tables_copied++;
                            }
                        } else {
                            $exists_already ++;
                        }
                        
                        if (!in_array ( $prefix."jdownloads_templates_$backup", $tablelist)) {
                            $query = $db->getQuery(true);
                            $db->setQuery("CREATE TABLE #__jdownloads_templates_$backup LIKE #__jdownloads_templates");
                            if ($db->execute()){
                                $query = $db->getQuery(true);
                                $db->setQuery("INSERT INTO #__jdownloads_templates_$backup SELECT * FROM #__jdownloads_templates");
                                if ($db->execute()) $this->tables_copied++;
                            }
                        }
                            
                        if (!in_array ( $prefix."jdownloads_logs_$backup", $tablelist)) {
                            $query = $db->getQuery(true);
                            $db->setQuery("CREATE TABLE #__jdownloads_logs_$backup LIKE #__jdownloads_logs");
                            if ($db->execute()){
                                $query = $db->getQuery(true);
                                $db->setQuery("INSERT INTO #__jdownloads_logs_$backup SELECT * FROM #__jdownloads_logs");
                                if ($db->execute()) $this->tables_copied++;
                            }
                        } else {
                            $exists_already ++;
                        }                                                                                                
                            
                        if (!in_array ( $prefix."jdownloads_ratings_$backup", $tablelist)) {
                            $query = $db->getQuery(true);
                            $db->setQuery("CREATE TABLE #__jdownloads_ratings_$backup LIKE #__jdownloads_ratings");
                            if ($db->execute()){
                                $query = $db->getQuery(true);
                                $db->setQuery("INSERT INTO #__jdownloads_ratings_$backup SELECT * FROM #__jdownloads_ratings");
                                if ($db->execute()) $this->tables_copied++;
                            }
                        } else {
                            $exists_already ++;
                        }

                        if (!in_array ( $prefix."jdownloads_usergroups_limits_$backup", $tablelist)) {
                            $query = $db->getQuery(true);
                            $db->setQuery("CREATE TABLE #__jdownloads_usergroups_limits_$backup LIKE #__jdownloads_usergroups_limits");
                            if ($db->execute()){
                                $query = $db->getQuery(true);
                                $db->setQuery("INSERT INTO #__jdownloads_usergroups_limits_$backup SELECT * FROM #__jdownloads_usergroups_limits");
                                if ($db->execute()) $this->tables_copied++;
                            }
                        } else {
                            $exists_already ++;
                        }
                            
                        if (!in_array ( $prefix."jdownloads_config_$backup", $tablelist)) {
                            $query = $db->getQuery(true);
                            $db->setQuery("CREATE TABLE #__jdownloads_config_$backup LIKE #__jdownloads_config");
                            if ($db->execute()){
                                $query = $db->getQuery(true);
                                $db->setQuery("INSERT INTO #__jdownloads_config_$backup SELECT * FROM #__jdownloads_config");
                                if ($db->execute()) $this->tables_copied++;
                            }
                        } else {
                            $exists_already ++;
                        }   
                        
                        if ($this->tables_copied){
                            self::addLog(JText::sprintf('COM_JDOWNLOADS_UPGRADE32_AMOUNT_TABLE_BACKUP', $this->tables_copied, $backup), 'JLog::INFO');
                        } else {
                            if ($exists_already){
                                self::addLog(JText::_('COM_JDOWNLOADS_UPGRADE32_TABLE_BACKUP_EXISTS_ALREADY'), 'JLog::INFO');
                            } else {
                                self::addLog(JText::_('COM_JDOWNLOADS_UPGRADE32_TABLE_BACKUP_NO_SUCCESS'), 'JLog::INFO');    
                            }
                        } 
                                               
                    
                    
                        //*********************************************
                        // Use the old version custom fields from 3.2?
                        //*********************************************

                        $this->custom_fields_items = array();
                        
                        // We need a list with all installed language keys for the case that multiple languages are used in jD custom fields
                        $lang_keys = self::getInstalledLanguageKeyList();
                        $this->lang_keys = $lang_keys;
                        count($lang_keys) > 1 ? $this->multilanguages = true : $this->multilanguages = false;
                        
                        // Get at first custom field titles from config
                        $db->setQuery('SELECT setting_name, setting_value FROM #__jdownloads_config WHERE `setting_name` LIKE "custom.field.%.title" AND `setting_value` != ""');
                        $field_titles = $db->loadObjectList();
                        
                        
                        // Get secondly the custom field selectable values from config table
                        $db->setQuery('SELECT setting_name, setting_value FROM #__jdownloads_config WHERE `setting_name` LIKE "custom.field.%.values" AND `setting_value` != ""');
                        $field_values = $db->loadObjectList();

                        // Exist multi language keys in titles?
                        foreach ($field_titles as $title){
                            foreach ($lang_keys as $lkey){
                                if (StringHelper::strpos($title->setting_value, '{'.$lkey.'}') !== false){
                                    $title->{"$lkey"} = $db->escape(self::getOnlyLanguageSubstring($title->setting_value, $lkey));
                                } 
                            }
                        }
                        
                        // Exist multi language keys in values?
                        foreach ($field_values as $value){
                            foreach ($lang_keys as $lkey){
                                if (StringHelper::strpos($value->setting_value, '{'.$lkey.'}') !== false){
                                    $value->{"$lkey"} = $db->escape(self::getOnlyLanguageSubstring($value->setting_value, $lkey));
                                } 
                            }
                        }
                        
                        // Add this data here in the field_titles object
                        for ($x=0; $x < count($field_values); $x++){
                            $field_titles[$x]->values = new stdClass();
                            foreach ($lang_keys as $lkey){
                                if (isset($field_values[$x]->{"$lkey"})){
                                    $field_titles[$x]->values->{"$lkey"} = explode(',',$field_values[$x]->{"$lkey"});     
                                } else {
                                    $field_titles[$x]->values = explode(',',$field_values[$x]->setting_value);     
                                    break 1;
                                }
                            }    
                        }
                        
                        if ($field_titles){
                            $this->field_titles = $field_titles;
                            // The old version seems to use it
                            //$titles  = array('ID', 'Title');
                            $fields = '';

                            foreach ($field_titles as $field_title){
                                //array_push($titles, htmlspecialchars(self::getOnlyLanguageSubstring($field_title->setting_value, $lkey), ENT_QUOTES));

                                $field = str_replace('.', '_', $field_title->setting_name);
                                $field = str_replace('_title', ', ', $field);
                                $fields = $fields.$field;
                            }
                            
                            $fields = StringHelper::substr($fields, 0, -2);
                            
                            // 2. We need now all Downloads which has stored custom fields data
                            
                            $query = $db->getQuery(true);
                            $query->select("file_id, file_title, language, $fields");
                            $query->from('`#__jdownloads_files` AS a');
                            $query->where("a.custom_field_1  != '0' ", 'OR');
                            $query->where("a.custom_field_2  != '0' ", 'OR');
                            $query->where("a.custom_field_3  != '0' ", 'OR');
                            $query->where("a.custom_field_4  != '0' ", 'OR');
                            $query->where("a.custom_field_5  != '0' ", 'OR');
                            $query->where("a.custom_field_6  != '' ", 'OR');
                            $query->where("a.custom_field_7  != '' ", 'OR');
                            $query->where("a.custom_field_8  != '' ", 'OR');
                            $query->where("a.custom_field_9  != '' ", 'OR');
                            $query->where("a.custom_field_10 != '' ", 'OR');
                            $query->where("a.custom_field_11 != '0000-00-00' ", 'OR');
                            $query->where("a.custom_field_12 != '0000-00-00' ", 'OR');
                            $query->where("a.custom_field_13 != '' ", 'OR');
                            $query->where("a.custom_field_14 != '' ", 'OR');
                            $db->setQuery($query);
                            $items = $db->loadObjectList();
                            
                            foreach ($items as $row){
                                // We need for the two 'date' fields empty values when no date is defined
                                if (isset($row->custom_field_11) && $row->custom_field_11 == '0000-00-00'){
                                    $row->custom_field_11 = '';
                                }
                                if (isset($row->custom_field_12) && $row->custom_field_12 == '0000-00-00'){
                                    $row->custom_field_12 = '';
                                }
                                
                                }
                            
                            if ($items){
                                // we need this data later again for the import in the update() method
                                $this->custom_fields_items = $items;
                                $this->created_custom_fields = $fields;
                            }

                        }
                        
                        // Display the warn and informations message by upgrade when user run the process and he has not prior already click on the confirm button!
                        // View also a special hint when old custom fields are used in 3.2 in the past
                        
                        $db->setQuery('SELECT setting_value FROM #__jdownloads_config WHERE `setting_name` LIKE "confirm.update39"');
                        $confirmed = $db->loadResult();
                        
                        if (!$confirmed){
                            
                            // The user must manually confirm the Update Informations before he can run the update process finaly
                            // When he click the button we write a new key value in the old 3.2 config.
                            
                            $message = '<div>
                                            <div>
                                                <h5><img src="'.JURI::base().'components/com_jdownloads/assets/images/jdownloads.jpg" style="float:right; margin-left:15px;" alt="jDownloads Logo" />'.JText::_('COM_JDOWNLOADS_UPGRADE32_FIELDS_WILL_BE_RENAMED_WARNING').'</h5>
                                            </div>
                                            <div>
                                                <a href="index.php?option=com_jdownloads&task=tools.confirmUpdate39" class="btn btn-primary btn-block">'.JText::_('COM_JDOWNLOADS_UPGRADE32_CONFIRM_BUTTON_TEXT').'</a>
                                            </div>
                                        </div>';
                            
                            JError::raiseNotice(null, $message);         
                            return false;
                        }
                        
                        
                    } else {
                        // Abort Update from the old jD 3.2 series - the installed version is to old
                        $msg = JText::sprintf('COM_JDOWNLOADS_UPDATE_ERROR_JD_TO_OLD', $old_manifest->version, $manifest->requiredForUpgrade); 
                        self::addLog($msg, 'JLog::ERROR');                
                        
                        JError::raiseWarning(null, $msg);         
                        return false;
                    
                    }
                }                
                
                if ( !version_compare($this->new_version_short, $this->old_version_short, '>=' ) ) {
                    
                    self::addLog(JText::_('COM_JDOWNLOADS_UPDATE_ERROR_INCORRECT_VERSION').' '.$rel, 'JLog::WARNING');                
                    
                    // abort if the release being installed is not newer (or equal) than the currently installed jDownloads version
                    JError::raiseWarning(null, JText::_('COM_JDOWNLOADS_UPDATE_ERROR_INCORRECT_VERSION').' '.$rel);         
                    return false;
                }
                
            } else {
                $component_header = JText::_('COM_JDOWNLOADS_DESCRIPTION');
                $typetext =  JText::_('COM_JDOWNLOADS_INSTALL_TYPE_INSTALL');
                $rel = $this->new_version; 
            }

            $install_msg = '<p><b>'.$component_header.'</b></p>
                            <p>'.$typetext.' '.JText::_('COM_JDOWNLOADS_INSTALL_VERSION').' '.$rel.'</p>';

            $this->install_msg = $install_msg."\n";
            
            ?>
            <table class="adminlist" style="width:100%;">
                <thead>
                    <tr>
                        <th class="title" style="text-align:center;"><img src="<?php echo JURI::base(); ?>components/com_jdownloads/assets/images/jdownloads.jpg" style="border:0;" alt="jDownloads Logo" /><br />
                        <?php echo $this->install_msg; ?>
                        </th>
                    </tr>
                </thead>
           </table>
        <form>
            <div style="text-align:center; margin:25px,0px,25px,0px;"><input class="btn btn-primary" style="align:center;" type="button" value="<?php echo JText::_('COM_JDOWNLOADS_INSTALL_16').'&nbsp; '; ?>" onclick="window.location.href='index.php?option=com_jdownloads'" /></div>
        </form>
        
        <?php  // end install/update
        
        } else {
            
            if ($type == 'uninstall'){
                       
            }
           
        }
        // afterwards are copied the component files 
	}
 
	/**
	 * method to run after an install/update/discover_install method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)

        require_once (JPATH_ADMINISTRATOR.'/components/com_jdownloads/helpers/jdownloads.php');
        
        require_once (JPATH_ADMINISTRATOR.'/components/com_jdownloads/helpers/jd_layouts.php');
        
        $app    = JFactory::getApplication(); 
        $db     = JFactory::getDBO();        
        
        $old_cats_template = false; 
        
        if ( $type == 'install' || ($type == 'update' && $this->run_upgrade_from_32)){
            
            require_once (JPATH_ADMINISTRATOR.'/components/com_jdownloads/helpers/fields_import.php');
            
            // Write default permission settings in the assets table when not exist already (also after an update from 3.2 series)
            $query = $db->getQuery(true);
            $query->select('rules');
            $query->from('#__assets');
            $query->where('name = '.$db->Quote('com_jdownloads'));
            $db->setQuery($query);
            $jd_component_rule = $db->loadResult();              
              
            if ($jd_component_rule = '' || $jd_component_rule == '{}'){              
                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__assets'));
                $query->set('rules = '.$db->Quote('{"download":{"1":1}}'));
                $query->where('name = '.$db->Quote('com_jdownloads'));
                $db->setQuery($query);
                if (!$db->execute()){
                    $this->setError($db->getErrorMsg());
                }    
            }
            
            // Get the normal backend language ini file
            $language = JFactory::getLanguage();
            $language->load('com_jdownloads', JPATH_ADMINISTRATOR);
            
            // We need the old stored configuration data when we have found an old 3.2 series
            if ($this->run_upgrade_from_32){
                $jlistConfig = array();
                $query = $db->getQuery(true);
                $db->setQuery("SELECT setting_name, setting_value FROM #__jdownloads_config");
                $jlistConfigObj = $db->loadObjectList();
                if(!empty($jlistConfigObj)){
                    foreach ($jlistConfigObj as $jlistConfigRow){
                        $jlistConfig[$jlistConfigRow->setting_name] = $jlistConfigRow->setting_value;
                    }
                }                
            }
            
            // Get the currently stored jD params
            $query = $db->getQuery(true);
            $db->setQuery('SELECT `params` FROM #__extensions WHERE `type` = "component" AND `element` = "com_jdownloads"');
            $old_params = $db->loadResult();
            if ($old_params == '{}' || !$old_params){
                // Create the default param values
                $json = file_get_contents(JPATH_ADMINISTRATOR.'/components/com_jdownloads/default_params.txt');
                $def_params = json_decode($json);
                
                if ($this->run_upgrade_from_32 && $jlistConfig){
                    // Old series found so we must copy some params from old config table
                    $def_params->files_uploaddir                            = $jlistConfig['files.uploaddir'];
                    $def_params->root_dir                                   = $jlistConfig['files.uploaddir'];
                    $def_params->tempzipfiles_folder_name                   = $db->escape($jlistConfig['tempzipfiles.folder.name']);
                    $def_params->preview_files_folder_name                  = $db->escape($jlistConfig['preview.files.folder.name']);
                    $def_params->global_datetime                            = $db->escape($jlistConfig['global.datetime']);
                    $def_params->global_datetime_short                      = $db->escape($jlistConfig['global.datetime.short']);
                    $def_params->use_php_script_for_download                = $db->escape($jlistConfig['use.php.script.for.download']);
                    $def_params->activate_download_log                      = $db->escape($jlistConfig['activate.download.log']);
                    $def_params->delete_also_images_from_downloads          = $db->escape($jlistConfig['delete.also.images.from.downloads']);
                    $def_params->delete_also_preview_files_from_downloads   = $db->escape($jlistConfig['delete.also.preview.files.from.downloads']);
                    $def_params->file_types_view                            = $db->escape($jlistConfig['file.types.view']);
                    $def_params->zipfile_prefix                             = $db->escape($jlistConfig['zipfile.prefix']);
                    $def_params->tempfile_delete_time                       = $db->escape($jlistConfig['tempfile.delete.time']);
                    $def_params->be_upload_amount_of_pictures               = $db->escape($jlistConfig['be.upload.amount.of.pictures']);
                    $def_params->all_files_autodetect                       = $db->escape($jlistConfig['all.files.autodetect']);
                    $def_params->file_types_autodetect                      = $db->escape($jlistConfig['file.types.autodetect']);
                    $def_params->autopublish_founded_files                  = $db->escape($jlistConfig['autopublish.founded.files']);
                    $def_params->autopublish_default_description            = $db->escape($jlistConfig['autopublish.default.description']);
                    $def_params->autopublish_default_cat_description        = '';
                    $def_params->offline                                    = $db->escape($jlistConfig['offline']);
                    $def_params->offline_text                               = $db->escape($jlistConfig['offline.text']);
                    $def_params->downloads_titletext                        = $db->escape($jlistConfig['downloads.titletext']);
                    $def_params->downloads_footer_text                      = $db->escape($jlistConfig['downloads.footer.text']);
                    $def_params->categories_per_side                        = $db->escape($jlistConfig['categories.per.side']);
                    $def_params->files_per_side                             = $db->escape($jlistConfig['files.per.side']);
                    $def_params->cats_order                                 = $db->escape($jlistConfig['cats.order']);
                    $def_params->files_order                                = $db->escape($jlistConfig['files.order']);
                    $def_params->direct_download                            = $db->escape($jlistConfig['direct.download']);
                    $def_params->view_detailsite                            = $db->escape($jlistConfig['view.detailsite']);
                    $def_params->use_download_title_as_download_link        = $db->escape($jlistConfig['use.download.title.as.download.link']);
                    $def_params->show_header_catlist                        = $db->escape($jlistConfig['show.header.catlist']);
                    $def_params->show_header_catlist_all                    = $db->escape($jlistConfig['show.header.catlist.all']);
                    $def_params->show_header_catlist_topfiles               = $db->escape($jlistConfig['show.header.catlist.topfiles']);
                    $def_params->show_header_catlist_newfiles               = $db->escape($jlistConfig['show.header.catlist.newfiles']);
                    $def_params->option_navigate_top                        = $db->escape($jlistConfig['option.navigate.top']);
                    $def_params->option_navigate_bottom                     = $db->escape($jlistConfig['option.navigate.bottom']);
                    $def_params->use_pagination_subcategories               = $db->escape($jlistConfig['use.pagination.subcategories']);
                    $def_params->amount_subcats_per_page_in_pagination      = $db->escape($jlistConfig['amount.subcats.per.page.in.pagination']);
                    $def_params->show_header_catlist_levels                 = $db->escape($jlistConfig['show.header.catlist.levels']);
                    $def_params->view_category_info                         = $db->escape($jlistConfig['view.category.info']);
                    $def_params->view_empty_categories                      = 1;
                    $def_params->view_no_file_message_in_empty_category     = $db->escape($jlistConfig['view.no.file.message.in.empty.category']);
                    $def_params->view_sort_order                            = $db->escape($jlistConfig['view.sort.order']);
                    $def_params->sortorder_fields                           = $db->escape($jlistConfig['sortorder.fields']);
                    $def_params->view_back_button                           = $db->escape($jlistConfig['view.back.button']);
                    $def_params->remove_field_title_when_empty              = $db->escape($jlistConfig['remove.field.title.when.empty']);
                    $def_params->remove_empty_tags                          = $db->escape($jlistConfig['remove.empty.tags']);
                    $def_params->use_lightbox_function                      = $db->escape($jlistConfig['use.lightbox.function']);
                    $def_params->activate_general_plugin_support            = $db->escape($jlistConfig['activate.general.plugin.support']);
                    $def_params->use_real_user_name_in_frontend             = $db->escape($jlistConfig['use.real.user.name.in.frontend']);
                    $def_params->auto_file_short_description_value          = $db->escape($jlistConfig['auto.file.short.description.value']);
                    $def_params->auto_cat_short_description_value           = 0; // added in 3.9.0.6
                    $def_params->use_tabs_type                              = $db->escape($jlistConfig['use.tabs.type']);
                    $def_params->additional_tab_title_1                     = $db->escape($jlistConfig['additional.tab.title.1']);
                    $def_params->additional_tab_title_2                     = $db->escape($jlistConfig['additional.tab.title.2']);
                    $def_params->additional_tab_title_3                     = $db->escape($jlistConfig['additional.tab.title.3']);
                    $def_params->view_ratings                               = $db->escape($jlistConfig['view.ratings']);
                    $def_params->rating_only_for_regged                     = $db->escape($jlistConfig['rating.only.for.regged']);
                    $def_params->jcomments_active                           = $db->escape($jlistConfig['jcomments.active']);
                    $def_params->info_icons_size                            = $db->escape($jlistConfig['info.icons.size']);
                    $def_params->cat_pic_default_filename                   = $db->escape($jlistConfig['cat.pic.default.filename']);
                    $def_params->cat_pic_size                               = $db->escape($jlistConfig['cat.pic.size']);
                    $def_params->cat_pic_size_height                        = $db->escape($jlistConfig['cat.pic.size.height']);
                    $def_params->file_pic_default_filename                  = $db->escape($jlistConfig['file.pic.default.filename']);
                    $def_params->file_pic_size                              = $db->escape($jlistConfig['file.pic.size']);
                    $def_params->file_pic_size_height                       = $db->escape($jlistConfig['file.pic.size.height']);
                    $def_params->featured_pic_filename                      = $db->escape($jlistConfig['featured.pic.filename']);
                    $def_params->featured_pic_size                          = $db->escape($jlistConfig['featured.pic.size']);
                    $def_params->featured_pic_size_height                   = $db->escape($jlistConfig['featured.pic.size.height']);
                    $def_params->css_button_color_download                  = $db->escape($jlistConfig['css.button.color.download']);
                    $def_params->css_button_size_download                   = $db->escape($jlistConfig['css.button.size.download']);
                    $def_params->css_button_size_download_small             = $db->escape($jlistConfig['css.button.size.download.small']);
                    $def_params->css_button_color_hot                       = $db->escape($jlistConfig['css.button.color.hot']);
                    $def_params->css_button_color_new                       = $db->escape($jlistConfig['css.button.color.new']);
                    $def_params->css_button_color_updated                   = $db->escape($jlistConfig['css.button.color.updated']);
                    $def_params->days_is_file_new                           = $db->escape($jlistConfig['days.is.file.new']);
                    $def_params->loads_is_file_hot                          = $db->escape($jlistConfig['loads.is.file.hot']);
                    $def_params->days_is_file_updated                       = $db->escape($jlistConfig['days.is.file.updated']);
                    $def_params->thumbnail_size_height                      = $db->escape($jlistConfig['thumbnail.size.height']);
                    $def_params->thumbnail_size_width                       = $db->escape($jlistConfig['thumbnail.size.width']);
                    $def_params->create_auto_thumbs_from_pics               = $db->escape($jlistConfig['create.auto.thumbs.from.pics']);
                    $def_params->create_auto_thumbs_from_pics_by_scan       = $db->escape($jlistConfig['create.auto.thumbs.from.pics.by.scan']);
                    $def_params->create_auto_thumbs_from_pics_image_height  = $db->escape($jlistConfig['create.auto.thumbs.from.pics.image.height']);
                    $def_params->create_auto_thumbs_from_pics_image_width   = $db->escape($jlistConfig['create.auto.thumbs.from.pics.image.width']);
                    $def_params->create_pdf_thumbs                          = $db->escape($jlistConfig['create.pdf.thumbs']);
                    $def_params->create_pdf_thumbs_by_scan                  = $db->escape($jlistConfig['create.pdf.thumbs.by.scan']);
                    $def_params->pdf_thumb_image_type                       = $db->escape($jlistConfig['pdf.thumb.image.type']);
                    $def_params->pdf_thumb_height                           = $db->escape($jlistConfig['pdf.thumb.height']);
                    $def_params->pdf_thumb_width                            = $db->escape($jlistConfig['pdf.thumb.width']);
                    $def_params->pdf_thumb_pic_height                       = $db->escape($jlistConfig['pdf.thumb.pic.height']);
                    $def_params->pdf_thumb_pic_width                        = $db->escape($jlistConfig['pdf.thumb.pic.width']);
                    $def_params->thumbnail_view_placeholder                 = $db->escape($jlistConfig['thumbnail.view.placeholder']);
                    $def_params->thumbnail_view_placeholder_in_lists        = $db->escape($jlistConfig['thumbnail.view.placeholder.in.lists']);
                    $def_params->html5player_use                            = $db->escape($jlistConfig['html5player.use']);
                    $def_params->html5player_view_video_only_in_details     = $db->escape($jlistConfig['html5player.view.video.only.in.details']);
                    $def_params->html5player_width                          = $db->escape($jlistConfig['html5player.width']);
                    $def_params->html5player_height                         = $db->escape($jlistConfig['html5player.height']);
                    $def_params->html5player_audio_width                    = $db->escape($jlistConfig['html5player.audio.width']);
                    $def_params->flowplayer_use                             = $db->escape($jlistConfig['flowplayer.use']);
                    $def_params->flowplayer_view_video_only_in_details      = $db->escape($jlistConfig['flowplayer.view.video.only.in.details']);
                    $def_params->flowplayer_playerwidth                     = $db->escape($jlistConfig['flowplayer.playerwidth']);
                    $def_params->flowplayer_playerheight                    = $db->escape($jlistConfig['flowplayer.playerheight']);
                    $def_params->flowplayer_playerheight_audio              = $db->escape($jlistConfig['flowplayer.playerheight.audio']);
                    $def_params->mp3_view_id3_info                          = $db->escape($jlistConfig['mp3.view.id3.info']);
                    $def_params->mp3_info_layout                            = $db->escape($jlistConfig['mp3.info.layout']);
                    $def_params->plupload_runtime                           = $db->escape($jlistConfig['plupload.runtime']);
                    $def_params->plupload_max_file_size                     = $db->escape($jlistConfig['plupload.max.file.size']);
                    $def_params->plupload_chunk_size                        = $db->escape($jlistConfig['plupload.chunk.size']);
                    $def_params->plupload_chunk_unit                        = $db->escape($jlistConfig['plupload.chunk.unit']);
                    $def_params->plupload_image_file_extensions             = $db->escape($jlistConfig['plupload.image.file.extensions']);
                    $def_params->plupload_other_file_extensions             = $db->escape($jlistConfig['plupload.other.file.extensions']);
                    $def_params->plupload_rename                            = $db->escape($jlistConfig['plupload.rename']);
                    $def_params->plupload_unique_names                      = $db->escape($jlistConfig['plupload.unique.names']);
                    $def_params->plupload_enable_image_resizing             = $db->escape($jlistConfig['plupload.enable.image.resizing']);
                    $def_params->plupload_resize_width                      = $db->escape($jlistConfig['plupload.resize.width']);
                    $def_params->plupload_resize_height                     = $db->escape($jlistConfig['plupload.resize.height']);
                    $def_params->plupload_resize_quality                    = $db->escape($jlistConfig['plupload.resize.quality']);
                    $def_params->plupload_enable_uploader_log               = $db->escape($jlistConfig['plupload.enable.uploader.log']);
                    $def_params->create_auto_cat_dir                        = $db->escape($jlistConfig['create.auto.cat.dir']);
                    $def_params->use_unicode_path_names                     = $db->escape($jlistConfig['use.unicode.path.names']);
                    $def_params->transliterate_at_first                     = $db->escape($jlistConfig['transliterate.at.first']);
                    $def_params->fix_upload_filename_blanks                 = $db->escape($jlistConfig['fix.upload.filename.blanks']);
                    $def_params->fix_upload_filename_uppercase              = $db->escape($jlistConfig['fix.upload.filename.uppercase']);
                    $def_params->fix_upload_filename_specials               = $db->escape($jlistConfig['fix.upload.filename.specials']);
                    $def_params->use_files_and_folder_settings_for_monitoring = $db->escape($jlistConfig['use.files.and.folder.settings.for.monitoring']);
                    $def_params->anti_leech                                 = $db->escape($jlistConfig['anti.leech']);
                    $def_params->check_leeching                             = $db->escape($jlistConfig['check.leeching']);
                    $def_params->block_referer_is_empty                     = $db->escape($jlistConfig['block.referer.is.empty']);
                    $def_params->allowed_leeching_sites                     = $db->escape($jlistConfig['allowed.leeching.sites']);
                    $def_params->mail_cloaking                              = $db->escape($jlistConfig['mail.cloaking']);
                    // $def_params->use_blocking_list                          = $db->escape($jlistConfig['use.blocking.list']);
                    // $def_params->blocking_list                              = $db->escape($jlistConfig['blocking.list']);
                    $def_params->send_mailto_option                         = $db->escape($jlistConfig['send.mailto.option']);
                    $def_params->send_mailto_html                           = $db->escape($jlistConfig['send.mailto.html']);
                    $def_params->send_mailto                                = $db->escape($jlistConfig['send.mailto']);
                    $def_params->send_mailto_betreff                        = $db->escape($jlistConfig['send.mailto.betreff']);
                    $def_params->send_mailto_template_download              = $db->escape($jlistConfig['send.mailto.template.download']);
                    $def_params->send_mailto_betreff_upload                 = $db->escape($jlistConfig['send.mailto.betreff.upload']);
                    $def_params->send_mailto_template_upload                = $db->escape($jlistConfig['send.mailto.template.upload']);
                    $def_params->send_mailto_option_upload                  = $db->escape($jlistConfig['send.mailto.option.upload']);
                    $def_params->send_mailto_html_upload                    = $db->escape($jlistConfig['send.mailto.html.upload']);
                    $def_params->send_mailto_upload                         = $db->escape($jlistConfig['send.mailto.upload']);
                    $def_params->send_mailto_report                         = $db->escape($jlistConfig['send.mailto.report']);
                    $def_params->report_mail_layout                         = $db->escape($jlistConfig['report.mail.layout']);
                    $def_params->report_mail_subject                        = $db->escape($jlistConfig['report.mail.subject']);
                    $def_params->use_alphauserpoints                        = $db->escape($jlistConfig['use.alphauserpoints']);
                    $def_params->use_alphauserpoints_with_price_field       = $db->escape($jlistConfig['use.alphauserpoints.with.price.field']);
                    $def_params->user_message_when_zero_points              = $db->escape($jlistConfig['user.message.when.zero.points']);
                    $def_params->google_adsense_active                      = $db->escape($jlistConfig['google.adsense.active']);
                    $def_params->google_adsense_code                        = $db->escape($jlistConfig['google.adsense.code']);
                    $def_params->com                                        = $db->escape($jlistConfig['com']);
                    $def_params->plugin_auto_file_short_description_value   = $db->escape($jlistConfig['plugin.auto.file.short.description.value']);
                    $def_params->fileplugin_show_downloadtitle              = $db->escape($jlistConfig['fileplugin.show_downloadtitle']);
                    $def_params->fileplugin_show_jdfiledisabled             = $db->escape($jlistConfig['fileplugin.show_jdfiledisabled']);
                    $def_params->fileplugin_enable_plugin                   = $db->escape($jlistConfig['fileplugin.enable_plugin']);
                    $def_params->system_list                                = $db->escape($jlistConfig['system.list']);
                    $def_params->language_list                              = $db->escape($jlistConfig['language.list']);
                    $def_params->customers_mail_subject                     = $db->escape(JText::_('COM_JDOWNLOADS_CONFIG_CUSTOMERS_MAIL_SUBJECT_DEFAULT'));
                    $def_params->customers_mail_layout                      = $db->escape(JText::_('COM_JDOWNLOADS_CONFIG_CUSTOMERS_MAIL_LAYOUT_DEFAULT'));
                    $def_params->user_message_when_zero_points              = $db->escape($jlistConfig['user.message.when.zero.points']);
                    $def_params->fileplugin_defaultlayout                   = $db->escape($jlistConfig['fileplugin.defaultlayout']);
                    $def_params->fileplugin_offline_title                   = $db->escape($jlistConfig['fileplugin.offline_title']);
                    $def_params->fileplugin_offline_descr                   = $db->escape($jlistConfig['fileplugin.offline_descr']);
                    $def_params->checkbox_top_text                          = $db->escape($jlistConfig['checkbox.top.text']);

                    $json = json_encode($def_params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    $query = $db->getQuery(true);
                    $db->setQuery("UPDATE #__extensions SET params = '".$json."' WHERE `type` = 'component' AND `element` = 'com_jdownloads'");
                    if ($db->execute()){
                        self::addLog(JText::_('COM_JDOWNLOADS_UPGRADE32_HINT_2'), 'JLog::INFO');                
                    }
                    
                    // Delete the old config db table as we have already created a backup at the top from the update procedure
                    $query = $db->getQuery(true);
                    $db->setQuery("DROP TABLE #__jdownloads_config");
                    $db->execute();

                    // Update the jdownloads_templates table - Here has changed the handling from the special layout for subcategories with pagination as it has now an own type number
                    // So we must change the old layout and deactivate it by default. 
                    
                    $sum_layouts = 0;                                                                                       
                    $query = $db->getQuery(true);
                    $db->setQuery("SELECT * FROM #__jdownloads_templates WHERE `locked` = '1' AND `use_to_view_subcats` = '1'");
                    $old_templates = $db->loadObjectList();
                    $active = 0;
                    $note   = stripslashes(JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_USE_SUBCATS_NOTE'));
                    $newname = stripslashes(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_DEFAULT_PAGINATION_NAME'));
                    
                    if ($old_templates){
                        foreach ($old_templates as $old_template){
                            $query = $db->getQuery(true);
                            $db->setQuery("UPDATE #__jdownloads_templates SET `template_typ` = '8', `template_active` = ".$db->quote($active).", `note` = ".$db->quote($note).", `template_name` = ".$db->quote($newname)."  WHERE `id` = ".$db->quote($old_template->id));
                            if ($db->execute()) $old_cats_template = true;
                        }
                        
                        // Create now a new default layout type = 8 and activate it - This layout type is used to view the subcategories from a category with pagination.  
                        $cats_layout        = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_DEFAULT);
                        $cats_layout_before = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_BEFORE);
                        $cats_layout_after  = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_AFTER);
                        $preview_id         = 4;
                        $note               = stripslashes(JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_USE_SUBCATS_NOTE'));
                        $new_subcat_name_39 = $db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_DEFAULT_PAGINATION_NAME')).' 3.9';
                        $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, language, preview_id )  VALUES ('".$new_subcat_name_39."', 8, '".$cats_layout."', '', '', '', '".$cats_layout_before."', '".$cats_layout_after."', '".$db->escape($note)."', 1, 1, '*', '".$preview_id."' )");
                        if ($db->execute()){
                            $old_cats_template = true;
                            $sum_layouts++;
                        }
                    
                    } else {
                        // Create a new default layout type = 8 - This layout type is used to view the subcategories from a category with pagination.  
                        $cats_layout        = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_DEFAULT);
                        $cats_layout_before = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_BEFORE);
                        $cats_layout_after  = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_AFTER);
                        $preview_id        = 4;
                        $note              = stripslashes(JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_USE_SUBCATS_NOTE'));
                        $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, language, preview_id )  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_DEFAULT_PAGINATION_NAME').' 3.9')."', 8, '".$cats_layout."', '', '', '', '".$cats_layout_before."', '".$cats_layout_after."', '".$db->escape($note)."', 1, 1, '*', '".$preview_id."' )");
                        if ($db->execute()){
                            $old_cats_template = true;
                            $sum_layouts++;
                        }
                    }
                    
                    // Add the new table fields for bootstrap and w3.css option
                    $query = $db->getQuery(true);
                    $tablefields = $db->getTableColumns('#__jdownloads_templates'); 
                    
                    if ( !isset($tablefields['uses_bootstrap']) ){
                        // create the missing fields
                        $db->setQuery("ALTER TABLE #__jdownloads_templates ADD `uses_bootstrap` tinyint(1) NOT NULL DEFAULT '0' AFTER `cols`");
                        if (!$db->execute()){
                            self::addLog(JText::sprintf('COM_JDOWNLOADS_UPGRADE32_ERROR_01', 'uses_bootstrap'), 'JLog::ERROR');
                        }
                        $db->setQuery("ALTER TABLE #__jdownloads_templates ADD `uses_w3css` tinyint(1) NOT NULL DEFAULT '0' AFTER `uses_bootstrap`");
                        if (!$db->execute()){
                           self::addLog(JText::sprintf('COM_JDOWNLOADS_UPGRADE32_ERROR_02', 'uses_w3css'), 'JLog::ERROR');
                        }    
                    }
                    if ( !isset($tablefields['preview_id']) ){
                        // create the missing field
                        $db->setQuery("ALTER TABLE #__jdownloads_templates ADD `preview_id` TINYINT( 3 ) NOT NULL DEFAULT '0' AFTER `cols`");
                        if (!$db->execute()){
                            self::addLog(JText::sprintf('COM_JDOWNLOADS_UPGRADE32_ERROR_01', 'preview_id'), 'JLog::ERROR');
                        }
                    }
                    
                    // *********************************
                    // Install also all 3.9 layouts
                    // *********************************
                    $files_header       = stripslashes($files_header);
                    $files_subheader    = stripslashes($files_subheader);
                    $files_footer       = stripslashes($files_footer);
                    
                    // This layout is used to view the subcategories from a category in a multi column example. 
                    $cats_layout        = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_MULTICOLUMN_DEFAULT);
                    $cats_layout_before = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_BEFORE);
                    $cats_layout_after  = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUBCATS_PAGINATION_AFTER);
                    $preview_id        = 5;
                    $note              = stripslashes(JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_USE_SUBCATS_NOTE'));
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, cols, language, preview_id )  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_SUBCAT_DEFAULT_NAME').' 3.9')."', 8, '".$cats_layout."', '', '', '', '".$cats_layout_before."', '".$cats_layout_after."', '".$db->escape($note)."', 0, 1, 4, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;
                    
                    // Categories Standard Layout  (activated by installation as default)
                    $cats_layout       = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_CATS_DEFAULT);
                    $cats_header       = stripslashes($cats_header);
                    $cats_subheader    = stripslashes($cats_subheader);
                    $cats_footer       = stripslashes($cats_footer);
                    $preview_id        = 1;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_DEFAULT_NAME').' 3.9')."', 1, '".$cats_layout."', '".$cats_header."', '".$cats_subheader."', '".$cats_footer."', '', '', '', 0, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;
                    
                    // Categories Layout with 4 columns
                    $cats_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_CATS_COL_DEFAULT); 
                    $preview_id  = 2;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, cols, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_COL_TITLE').' 3.9')."', 1, '".$cats_layout."', '".$cats_header."', '".$cats_subheader."', '".$cats_footer."', '', '', 0, 1, '".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_COL_NOTE'))."', 4, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;

                    // Categories Layout with 2 columns
                    $cats_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_CATS_COL2_DEFAULT); 
                    $preview_id  = 3;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, cols, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_COL2_TITLE').' 3.9')."', 1, '".$cats_layout."', '".$cats_header."', '".$cats_subheader."', '".$cats_footer."', '', '', 0, 1, '', 2, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;
                                          
                    // Category Standard Layout (activated by installation as default)
                    $cat_layout       = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_CAT_DEFAULT);
                    $cat_header       = stripslashes($cat_header);
                    $cat_subheader    = stripslashes($cat_subheader);
                    $cat_footer       = stripslashes($cat_footer);
                    $preview_id       = 6;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CAT_DEFAULT_NAME').' 3.9')."', 4, '".$cat_layout."', '".$cat_header."', '".$cat_subheader."', '".$cat_footer."', '', '', '', 0, 1, '*', '".$preview_id."' )");
                    $db->execute();              
                    $sum_layouts++;
                  
                    // Files Standard Layout (with mini icons)
                    $files_layout       = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT);
                    $files_header       = stripslashes($files_header);
                    $files_subheader    = stripslashes($files_subheader);
                    $files_footer       = stripslashes($files_footer);
                    $preview_id         = 7;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NAME').' 3.9')."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 1, 0, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;

                    // Files Simple Layout with Checkboxes
                    $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NEW_SIMPLE_1); 
                    $preview_id  = 8;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NEW_SIMPLE_1_NAME').' 3.9')."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 0, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;
                        
                    // Files Simple Layout without Checkboxes (activated by installation as default)
                    $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NEW_SIMPLE_2); 
                    $preview_id  = 9;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NEW_SIMPLE_2_NAME').' 3.9')."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 1, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;

                    // Files Layout - Alternate
                    $files_layout        = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_NEW_ALTERNATE_1);
                    $files_layout_before = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_NEW_ALTERNATE_1_BEFORE);
                    $files_layout_after  = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_NEW_ALTERNATE_1_AFTER);
                    $preview_id  = 10;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NEW_ALTERNATE_1_NAME').' 3.9')."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '".$files_layout_before."', '".$files_layout_after."', 0, 1, '', 1, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;                            

                    // Files Layout with Full Info
                    $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_FULL_INFO); 
                    $preview_id  = 11;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_FULL_INFO_NAME').' 3.9')."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 1, 1, '*' , '".$preview_id."')");
                    $db->execute();
                    $sum_layouts++;
                  
                    // Files Layout - Just a Link
                    $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_JUST_LINK); 
                    $preview_id  = 12;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_JUST_LINK_NAME').' 3.9')."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 1, 1, '*', '".$preview_id."')");
                    $db->execute();
                    $sum_layouts++;

                    // Files Layout - Single Line
                    $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_SINGLE_LINE); 
                    $preview_id  = 13;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_SINGLE_LINE_NAME').' 3.9')."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 1, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;
                  
                    // Files Layout - Compact with checkboxes v.3.9 (by Colin)
                    $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_COMPACT_CHECKBOXES); 
                    $preview_id  = 14;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_COMPACT_NAME_2').' 3.9')."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 0, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;

                    // Files Layout - Compact with download buttons v.3.9 (by Colin)
                    $files_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_FILES_COMPACT_WITHOUT_CHECKBOXES); 
                    $preview_id  = 15;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, checkbox_off, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_COMPACT_NAME_1').' 3.9')."', 2, '".$files_layout."', '".$files_header."', '".$files_subheader."', '".$files_footer."', '', '', 0, 1, '', 1, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;

                    // Details Standard Layout
                    $detail_layout        = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT);
                    $details_header       = stripslashes($details_header);
                    $details_subheader    = stripslashes($details_subheader);
                    $details_footer       = stripslashes($details_footer);               
                    $preview_id  = 16;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_NAME').' 3.9')."', 5, '$detail_layout', '".$details_header."', '".$details_subheader."', '".$details_footer."', '', '', '', 0, 1, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;
                  
                    // Details Layout with Tabs
                    $detail_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_WITH_TABS);
                    $preview_id  = 17;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_WITH_TABS_TITLE').' 3.9')."', 5, '$detail_layout', '".$details_header."', '".$details_subheader."', '".$details_footer."', '', '', '', '0', 1, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;
                  
                    // Details Layout with all new Data Fields 
                    $detail_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_NEW_25);
                    $preview_id  = 18;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_25_TITLE').' 3.9')."', 5, '$detail_layout', '".$details_header."', '".$details_subheader."', '".$details_footer."', '', '', '', '0', 1, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;
                  
                    // Details Layout with all new Data Fields (FULL Info with Related) v3.9
                    $detail_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_WITH_RELATED);
                    $preview_id  = 19;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, symbol_off, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_WITH_RELATED_TITLE').' 3.9')."', 5, '$detail_layout', '".$details_header."', '".$details_subheader."', '".$details_footer."', '', '', '', '0', 1, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;              

                    // New details Layout whish use W3.CSS option v3.9
                    $detail_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_WITH_W3CSS);
                    $preview_id  = 23;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, symbol_off, uses_w3css, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_DEFAULT_WITH_W3CSS_NAME').' 3.9')."', 5, '$detail_layout', '".$details_header."', '".$details_subheader."', '".$details_footer."', '', '', '', '0', 1, 1, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;
                    
                    // Summary Standard Layout
                    $summary_layout       = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SUMMARY_DEFAULT);
                    $summary_header      = stripslashes($summary_header);
                    $summary_subheader    = stripslashes($summary_subheader);
                    $summary_footer       = stripslashes($summary_footer);              
                    $preview_id  = 20;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, note, template_active, locked, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_SUMMARY_DEFAULT_NAME').' 3.9')."', 3, '".$summary_layout."', '".$summary_header."', '".$summary_subheader."', '".$summary_footer."', '', '', '', 0, 1, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;

                    // default search results layout vertical
                    $search_result_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SEARCH_DEFAULT);
                    $search_header       = stripslashes($search_header);
                    $search_subheader    = stripslashes($search_subheader);
                    $search_footer       = stripslashes($search_footer);  
                    $preview_id  = 21;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, cols, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_SEARCH_DEFAULT_NAME').' 3.9')."', 7, '".$search_result_layout."', '".$search_header."', '".$search_subheader."', '".$search_footer."', '', '', 0, 1, '', 4, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;
                  
                    // horizontal search results layout - take from $search2_header, $search2_subheader and $search2_footer
                    $search_result_layout = stripslashes($JLIST_BACKEND_SETTINGS_TEMPLATES_SEARCH_DEFAULT_HORIZONTAL);
                    $search_header       = stripslashes($search2_header);
                    $search_subheader    = stripslashes($search2_subheader);
                    $search_footer       = stripslashes($search2_footer);  
                    $preview_id  = 22;
                    $db->setQuery("INSERT INTO #__jdownloads_templates (template_name, template_typ, template_text, template_header_text, template_subheader_text, template_footer_text, template_before_text, template_after_text, template_active, locked, note, cols, language, preview_id)  VALUES ('".$db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_SEARCH_DEFAULT2_NAME').' 3.9')."', 7, '".$search_result_layout."', '".$search_header."', '".$search_subheader."', '".$search_footer."', '', '', 0, 1, '', 4, '*', '".$preview_id."' )");
                    $db->execute();
                    $sum_layouts++;                  
                  
                    $msg = '<b>'.JText::sprintf('COM_JDOWNLOADS_INSTALL_4', $sum_layouts).'</b>';
                    
                    // Add hint also to log
                    self::addLog($msg, 'JLog::INFO');
                    
                    // Delete not more required files from old 32 series
                    self::deleteOldFiles();
                    
                    $fields_result_msg = array();
                    
                    // Exist Custom Fields data then we must import it now
                    if (count($this->custom_fields_items)){
                        $result = JDFieldImportHelper::fieldsImport($this->custom_fields_items, $this->created_custom_fields, $this->field_titles, $this->lang_keys);

                        // Add a hint about the import result
                        if ($result['created'] !== ''){
                            $fields_result_msg[0]  = '<b>'.JText::sprintf('COM_JDOWNLOADS_UPGRADE32_FIELDS_IMPORT_SUCCESSFUL', (int)$result['created']).'</b>';

                            if ($result['saved_data'] !== ''){
                                $fields_result_msg[1] = '<b>'.JText::sprintf('COM_JDOWNLOADS_UPGRADE32_FIELDS_IMPORT_PART2_SUCCESSFUL', (int)$result['saved_data']).'</b>';
                            }
                        } else {
                            $fields_result_msg[0] .= '<b>'.JText::_('COM_JDOWNLOADS_UPGRADE32_FIELDS_IMPORT_NOT_SUCCESSFUL').'</b>';
                        }
                            
                    }
                    
                    if ($old_cats_template) self::addLog(JText::_('COM_JDOWNLOADS_UPGRADE32_HINT_3'), 'JLog::INFO');                
                    
                    // View a hint that the jD control panel must be called to complete the upgrade process! 
                    $msg = '<b>'.JText::_('COM_JDOWNLOADS_UPGRADE32_HINT_CALL_CP_MSG').'</b>';
                    
                    // Add the results messages from custom fields import when exist
                    if (count($fields_result_msg)){
                        $msg .= '<br /><br />'.$fields_result_msg[0];
                        
                        if (isset($fields_result_msg[1])){
                            $msg .= '<br />'.$fields_result_msg[1];
                        }
                    }
                    
                    $app->enqueueMessage($msg, 'warning');
                    
                    // Add hint also to log
                    self::addLog($msg, 'JLog::INFO');                
                    
                } else {
                    // It exist not an old series so we do only the normal install or update job
                    
                    // Add the root path
                    $files_uploaddir = $db->escape(JPATH_ROOT.DS.'jdownloads'); 
                    $files_uploaddir = rtrim($files_uploaddir, "/");
                    $files_uploaddir = rtrim($files_uploaddir, "\\");
                    $files_uploaddir = str_replace('\\', '/', $files_uploaddir);
                    
                    $def_params->files_uploaddir = $files_uploaddir;
                    $def_params->root_dir        = $files_uploaddir;
                    
                    // Some fields must have values from the current active jD language files
                    $def_params->global_datetime                = $db->escape(JText::_('COM_JDOWNLOADS_INSTALL_DEFAULT_DATE_FORMAT'));
                    $def_params->global_datetime_short          = $db->escape(JText::_('COM_JDOWNLOADS_INSTALL_DEFAULT_DATE_FORMAT'));
                    $def_params->offline_text                   = $db->escape(JText::_('COM_JDOWNLOADS_BACKEND_OFFLINE_MESSAGE_DEFAULT'));
                    $def_params->system_list                    = $db->escape(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_SYSTEM_DEFAULT_LIST'));
                    $def_params->language_list                  = $db->escape(JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_LANGUAGE_DEFAULT_LIST'));
                    $def_params->send_mailto_betreff            = $db->escape(JText::_('COM_JDOWNLOADS_SETTINGS_INSTALL_3'));
                    $def_params->send_mailto_template_download  = $db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_MAIL_DEFAULT'));
                    $def_params->send_mailto_betreff_upload     = $db->escape(JText::_('COM_JDOWNLOADS_SETTINGS_INSTALL_6'));
                    $def_params->send_mailto_template_upload    = $db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_GLOBAL_MAIL_UPLOAD_TEMPLATE'));
                    $def_params->report_mail_subject            = $db->escape(JText::_('COM_JDOWNLOADS_CONFIG_REPORT_FILE_MAIL_SUBJECT_DEFAULT'));
                    $def_params->report_mail_layout             = $db->escape(JText::_('COM_JDOWNLOADS_CONFIG_REPORT_FILE_MAIL_LAYOUT_DEFAULT'));
                    $def_params->customers_mail_subject         = $db->escape(JText::_('COM_JDOWNLOADS_CONFIG_CUSTOMERS_MAIL_SUBJECT_DEFAULT'));
                    $def_params->customers_mail_layout          = $db->escape(JText::_('COM_JDOWNLOADS_CONFIG_CUSTOMERS_MAIL_LAYOUT_DEFAULT'));
                    $def_params->user_message_when_zero_points  = $db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SET_AUP_FE_MESSAGE_NO_DOWNLOAD'));
                    $def_params->fileplugin_defaultlayout       = $db->escape(JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DEFAULT_NAME'));
                    $def_params->fileplugin_offline_title       = $db->escape(JText::_('COM_JDOWNLOADS_FRONTEND_SETTINGS_FILEPLUGIN_OFFLINE_FILETITLE'));
                    $def_params->fileplugin_offline_descr       = $db->escape(JText::_('COM_JDOWNLOADS_FRONTEND_SETTINGS_FILEPLUGIN_DESCRIPTION'));
                    $def_params->checkbox_top_text              = $db->escape(JText::_('COM_JDOWNLOADS_SETTINGS_INSTALL_1'));
                    
                    //$def_params->sortorder_fields               = '["0","1","2","3","4"]';
                    
                    //$blocking_list = file_get_contents ( JPATH_SITE.'/administrator/components/com_jdownloads/assets/blacklist.txt' );
                    //$def_params->blocking_list = $blocking_list;
                
                    $json = json_encode($def_params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	                $query = $db->getQuery(true);
	                $db->setQuery("UPDATE #__extensions SET params = '".$json."' WHERE `type` = 'component' AND `element` = 'com_jdownloads'");
                    if ($db->execute()){
                        self::addLog(JText::_('COM_JDOWNLOADS_INSTALL_HINT_1'), 'JLog::INFO');                
                    }
	            }    
        	}
        } 
        
        // Write for the tags feature the jd data in the #__content_types table
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__content_types');
        $query->where('type_alias = '.$db->Quote('com_jdownloads.download'));
        $db->setQuery($query);
        $type_download = $db->loadResult();              
        
        if (!$type_download){              
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__content_types'))
                    ->columns(array($db->quoteName('type_title'), $db->quoteName('type_alias'), $db->quoteName('table'), $db->quoteName('rules'), $db->quoteName('field_mappings'), $db->quoteName('router')))
                    ->values($db->quote('jDownloads Download'). ', ' .$db->quote('com_jdownloads.download'). ',' .$db->quote('{"special":{"dbtable":"#__jdownloads_files","key":"id","type":"Download","prefix":"JdownloadsTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Download","prefix":"JTable","config":"array()"}}', false).', '.$db->quote('').', '.$db->quote('{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"views","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"null", "core_language":"language", "core_images":"images", "core_urls":"null", "core_version":"null", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"description_long":"description_long"}}', false).', ' .$db->quote('JdownloadsHelperRoute::getDownloadRoute'));
            $db->setQuery($query);                                                                                                                                                                                                                                                                                                                                                    
            if (!$db->execute()){
                $this->setError($db->getErrorMsg());
            }    
            
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__content_types'))
                    ->columns(array($db->quoteName('type_title'), $db->quoteName('type_alias'), $db->quoteName('table'), $db->quoteName('rules'), $db->quoteName('field_mappings'), $db->quoteName('router')))
                    ->values($db->quote('jDownloads Category'). ', ' .$db->quote('com_jdownloads.category'). ',' .$db->quote('{"special":{"dbtable":"#__jdownloads_categories","key":"id","type":"Category","prefix":"JdownloadsTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Category","prefix":"JTable","config":"array()"}}', false).', '.$db->quote('').', '.$db->quote('{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"views","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"null", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"null","extension":"null","note":"null"}}', false).', ' .$db->quote('JdownloadsHelperRoute::getCategoryRoute'));
            $db->setQuery($query);                                                                                                                                                                                                                                                                                                                                                               
            if (!$db->execute()){
                $this->setError($db->getErrorMsg());
            }    
        } else {
            // Upgrade from old 3.2 version. So we must still update the data for jD files tags in content_types table. A few data fields identifiers has changed. 
            if ($this->run_upgrade_from_32) {
                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__content_types'))
                        ->set($db->quoteName('table') . ' = ' . $db->quote('{"special":{"dbtable":"#__jdownloads_files","key":"id","type":"Download","prefix":"JdownloadsTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Download","prefix":"JTable","config":"array()"}}', false))
                        ->set($db->quoteName('field_mappings') . ' = ' . $db->quote('{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"views","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"null", "core_language":"language", "core_images":"images", "core_urls":"null", "core_version":"null", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"description_long":"description_long"}}', false))
                        ->set($db->quoteName('router') . ' = ' . $db->quote('JdownloadsHelperRoute::getDownloadRoute'))
                        ->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_jdownloads.download'));
                
                $db->setQuery($query);                                                                                                                                                                                                                                                                                                                                                    
                if (!$db->execute()){
                    $this->setError($db->getErrorMsg());
                } else {
                    self::addLog(JText::_('COM_JDOWNLOADS_UPGRADE32_HINT_4'), 'JLog::INFO');                
                }
            }
        } 
        
        // write for the Joomla user action lag feature the jd data in the #__action_log_config
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__action_log_config');
        $query->where('type_alias = '.$db->Quote('com_jdownloads.download'));
        $db->setQuery($query);
        $type_download = $db->loadResult();              
        
        if (!$type_download){              
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__action_log_config'))
                    ->columns(array($db->quoteName('type_title'), $db->quoteName('type_alias'), $db->quoteName('id_holder'), $db->quoteName('title_holder'), $db->quoteName('table_name'), $db->quoteName('text_prefix')))
                    ->values($db->quote('download'). ', ' .$db->quote('com_jdownloads.download'). ', ' .$db->quote('id'). ',' .$db->quote('title'). ', ' .$db->quote('#__jdownloads_files'). ', ' .$db->quote('COM_JDOWNLOADS_ACTIONLOG'));
            $db->setQuery($query);                                                                                                                                                                                                                                                                                                                                                    
            if (!$db->execute()){
                $this->setError($db->getErrorMsg());
            } 
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__action_log_config'))
                    ->columns(array($db->quoteName('type_title'), $db->quoteName('type_alias'), $db->quoteName('id_holder'), $db->quoteName('title_holder'), $db->quoteName('table_name'), $db->quoteName('text_prefix')))
                    ->values($db->quote('category'). ', ' .$db->quote('com_jdownloads.category'). ', ' .$db->quote('id'). ', ' .$db->quote('title'). ', ' .$db->quote('#__jdownloads_categories'). ', ' .$db->quote('COM_JDOWNLOADS_ACTIONLOG'));
            $db->setQuery($query);                                                                                                                                                                                                                                                                                                                                                    
            if (!$db->execute()){
                $this->setError($db->getErrorMsg());
            } 
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__action_log_config'))
                    ->columns(array($db->quoteName('type_title'), $db->quoteName('type_alias'), $db->quoteName('id_holder'), $db->quoteName('title_holder'), $db->quoteName('table_name'), $db->quoteName('text_prefix')))
                    ->values($db->quote('license'). ', ' .$db->quote('com_jdownloads.license'). ', ' .$db->quote('id'). ', ' .$db->quote('title'). ', ' .$db->quote('#__jdownloads_licenses'). ', ' .$db->quote('COM_JDOWNLOADS_ACTIONLOG'));
            $db->setQuery($query);                                                                                                                                                                                                                                                                                                                                                    
            if (!$db->execute()){
                $this->setError($db->getErrorMsg());
            } 
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__action_log_config'))
                    ->columns(array($db->quoteName('type_title'), $db->quoteName('type_alias'), $db->quoteName('id_holder'), $db->quoteName('title_holder'), $db->quoteName('table_name'), $db->quoteName('text_prefix')))
                    ->values($db->quote('layout'). ', ' .$db->quote('com_jdownloads.template'). ', ' .$db->quote('id'). ', ' .$db->quote('template_name'). ', ' .$db->quote('#__jdownloads_templates'). ', ' .$db->quote('COM_JDOWNLOADS_ACTIONLOG'));
            $db->setQuery($query);                                                                                                                                                                                                                                                                                                                                                    
            if (!$db->execute()){
                $this->setError($db->getErrorMsg());
            } 
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__action_log_config'))
                    ->columns(array($db->quoteName('type_title'), $db->quoteName('type_alias'), $db->quoteName('id_holder'), $db->quoteName('title_holder'), $db->quoteName('table_name'), $db->quoteName('text_prefix')))
                    ->values($db->quote('group'). ', ' .$db->quote('com_jdownloads.group'). ', ' .$db->quote('id'). ', ' .$db->quote('group_id'). ', ' .$db->quote('#__jdownloads_usergroups_limits'). ', ' .$db->quote('COM_JDOWNLOADS_ACTIONLOG'));
            $db->setQuery($query);                                                                                                                                                                                                                                                                                                                                                    
            if (!$db->execute()){
                $this->setError($db->getErrorMsg());
            } 
            
            // We need also a new dataset in the #__action_logs_extensions table
            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__action_logs_extensions');
            $query->where('extension = '.$db->Quote('com_jdownloads'));
            $db->setQuery($query);
            $result = $db->loadResult();              
            
            if (!$result){              
                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__action_logs_extensions'))
                        ->columns(array($db->quoteName('extension')))
                        ->values($db->quote('com_jdownloads'));
                $db->setQuery($query);                                                                                                                                                                                                                                                                                                                                                    
                if (!$db->execute()){
                    $this->setError($db->getErrorMsg());
                }
            }
        }                   
        
        if ( $type == 'install'){     
            self::addLog('------------------------------------------------------ Installation Finished', 'JLog::INFO');                
        }
        if ( $type == 'update'){     
            self::addLog('------------------------------------------------------ Update Finished', 'JLog::INFO');                
        }
                            
        ?>
        <form>
            <div style="text-align:center; margin:25px,0px,25px,0px;"><input class="btn btn-primary" style="align:center;" type="button" value="<?php echo JText::_('COM_JDOWNLOADS_INSTALL_16').'&nbsp; '; ?>" onclick="window.location.href='index.php?option=com_jdownloads'" /></div>
        </form>
        <?php
	}

    /**
     * Method to get the correct db prefix (problem with getTablelist() which always/sometimes has lowercase prefix names in array)
     *
     * @return string
     */
    function getCorrectDBPrefix() 
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
     * Method to delete after an upgrade from the old 3.2 series not longer required files
     *
     * @return
     */
    
    function deleteOldFiles(){
        
        $be_path = JPATH_SITE.'/administrator/components/com_jdownloads/';
        $fe_path = JPATH_SITE.'/components/com_jdownloads/';
        
        // backend files            
        if (JFile::exists($be_path.'controllers/config.php')){
            JFile::delete($be_path.'controllers/config.php');
        }
        if (JFile::exists($be_path.'controllers/languageedit.php')){
            JFile::delete($be_path.'controllers/languageedit.php');
        }
        if (JFolder::exists($be_path.'views/config/')){
            JFolder::delete($be_path.'views/config/');
        }
        if (JFolder::exists($be_path.'views/languageedit/')){
            JFolder::delete($be_path.'views/languageedit/');
        }
        if (JFile::exists($be_path.'views/layouts/tmpl/cat.php')){
            JFile::delete($be_path.'views/layouts/tmpl/cat.php');
        }
        if (JFile::exists($be_path.'views/layouts/tmpl/cats.php')){
            JFile::delete($be_path.'views/layouts/tmpl/cats.php');
        }
        if (JFile::exists($be_path.'views/layouts/tmpl/details.php')){
            JFile::delete($be_path.'views/layouts/tmpl/details.php');
        }
        if (JFile::exists($be_path.'views/layouts/tmpl/files.php')){
            JFile::delete($be_path.'views/layouts/tmpl/files.php');
        }
        if (JFile::exists($be_path.'views/layouts/tmpl/summary.php')){
            JFile::delete($be_path.'views/layouts/tmpl/summary.php');
        }        
        if (JFile::exists($be_path.'views/license/tmpl/default.php')){
            JFile::delete($be_path.'views/license/tmpl/default.php');
        }
        if (JFile::exists($be_path.'views/template/tmpl/default.php')){
            JFile::delete($be_path.'views/template/tmpl/default.php');
        }
        if (JFile::exists($be_path.'models/config.php')){
            JFile::delete($be_path.'models/config.php');
        }                
        if (JFile::exists($be_path.'tables/config.php')){
            JFile::delete($be_path.'tables/config.php');
        }
        if (JFile::exists($be_path.'helpers/jdownloadshelper.php')){
            JFile::delete($be_path.'helpers/jdownloadshelper.php');
        }
        for ($i=1; $i<15; $i++){
            if (JFile::exists($be_path."models/fields/jdcustomfield$i.php")){
                JFile::delete($be_path."models/fields/jdcustomfield$i.php");
            }
        }                
        for ($i=0; $i<63; $i++){
            if (JFile::exists($be_path."sql/updates/mysql/3.2.$i.sql")){
                JFile::delete($be_path."/updates/mysql/3.2.$i.sql");
            }
        }
        // Frontend files
        if (JFile::exists($fe_path.'helpers/jdownloadshelper.php')){
            JFile::delete($fe_path.'helpers/jdownloadshelper.php');
        }
        /* Not really required that we delete the images - maybe has users own images here
        if (JFolder::exists($fe_path.'assets/images/jdownloads/downloadimages/')){
            JFolder::delete($fe_path.'assets/images/jdownloads/downloadimages/');
        }                
        if (JFolder::exists($fe_path.'assets/images/jdownloads/hotimages/')){
            JFolder::delete($fe_path.'assets/images/jdownloads/hotimages/');
        }        
        if (JFolder::exists($fe_path.'assets/images/jdownloads/newimages/')){
            JFolder::delete($fe_path.'assets/images/jdownloads/newimages/');
        }
        if (JFolder::exists($fe_path.'assets/images/jdownloads/updimages/')){
            JFolder::delete($fe_path.'assets/images/jdownloads/updimages/');
        }        
        */

        return;
    }
    
    /**
     * Method to get only a part from the string in a selected language
     *
     * @return string
     */
    
    
    function getOnlyLanguageSubstring($msg, $lang_key = '')
    {
        // Get the current locale language tag
        if (!$lang_key){
            $lang       = JFactory::getLanguage();
            $lang_key   = $lang->getTag();        
        }
        
        // remove the language tag from the text
        $startpos = StringHelper::strpos($msg, '{'.$lang_key.'}') +  strlen( $lang_key) + 2 ;
        $endpos   = StringHelper::strpos($msg, '{/'.$lang_key.'}') ;
        
        if ($startpos !== false && $endpos !== false){
            return StringHelper::substr($msg, $startpos, ($endpos - $startpos ));
        } else {    
            return $msg;
        }    
    }
    
    function getInstalledLanguageKeyList()
    {
        // Try to get a list with all installed languages
        JLoader::register('LanguagesModelInstalled', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_languages'.DS.'models'.DS.'installed.php');
        $lang = new LanguagesModelInstalled();
        $current_languages = $lang ->getData();
        
        $lang_list = array();
        
        foreach ($current_languages as $languages){
            $lang_list[] = $languages->language;
        }
        
        return $lang_list;
    }
    
    /**
     * Method to add a log item 
     *
     * @return
     */
    function addLog($msg, $priority, $basic_informations = false)
    {
        if ($basic_informations){
            // Add a few important system information when param is set

            $db = JFactory::getDbo();
            $messages_array = array();

            $logoptions['text_entry_format'] = '{DATE} {TIME}    {PRIORITY}     {MESSAGE}';
            $logoptions['text_file'] = 'com_jdownloads_install_logs.php';
            
            JLog::addLogger($logoptions, JLog::ALL, 'jD');

            $version = new JVersion;
            
            $messages_array[] = '------------------------------------------------------ System Information';                
            $messages_array[] = 'OS                 : '.substr(php_uname(), 0, 7);
            $messages_array[] = 'PHP                : '.phpversion();
            $messages_array[] = 'MySQL              : '.$db->getVersion();
            $messages_array[] = 'Joomla!            : '.$version->getShortVersion();
            
            $messages_array[] = 'Debug              : '.JFactory::getApplication()->get('debug');
            $messages_array[] = 'Debug Language     : '.JFactory::getApplication()->get('debug_lang');
            
            $messages_array[] = 'Error Reporting    : '.JFactory::getApplication()->get('error_reporting');
            
            $messages_array[] = 'SEF                : '.JFactory::getApplication()->get('sef');
            $messages_array[] = 'Unicode Aliases    : '.JFactory::getApplication()->get('unicodeslugs');
            $messages_array[] = 'System Cache       : '.JFactory::getApplication()->get('caching');
            $messages_array[] = 'Captcha            : '.JFactory::getApplication()->get('captcha');
            
            foreach ($messages_array as $message){
                        
                try
                {
                    JLog::add(JText::_($message), JLOG::INFO, 'jD');
                }            
                catch (RuntimeException $exception)
                {
                    // Informational log only
                }
            }
            return;
        }
        
        // Add here the normal log item
        
        $logoptions['text_entry_format'] = '{DATE} {TIME}    {PRIORITY}     {MESSAGE}';
        $logoptions['text_file'] = 'com_jdownloads_install_logs.php';
        
        JLog::addLogger($logoptions, JLog::ALL, 'jD');

        try
        {
            JLog::add(JText::_($msg), $priority, 'jD');
        }            
        catch (RuntimeException $exception)
        {
            // Informational log only
        }
        return;
    }
                                                
}
