<?php
/**
 * @package jDownloads
 * @version 3.8  
 * @copyright (C) 2007 - 2018 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


/**
 * 
 *
 * 
 */
class jdownloadsViewjdownloads extends JViewLegacy
{
    protected $canDo;

	protected $modules = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';
        
        $app        = JFactory::getApplication();
		$user 		= JFactory::getUser();
        $db         = JFactory::getDBO();
        $query      = $db->getQuery(true);

        $params = JComponentHelper::getParams( 'com_jdownloads' );
		
        // Check here wether we must still create the 'uncategorised' default category after an UPGRADE from 3.2

        $files_upload_dir = $params->get('files_uploaddir');
        
        if (!$files_upload_dir){
            // main path is missing - display message and abort    
            $app->enqueueMessage(JText::_('Error: The files path is not defined in option settings! Check this settings in the jDownloads options and correct it.'), 'warning');
        } else {
            $uncat_created = $params->get('uncat_already_created');
            $new_cat_id = 0;
            $amount     = 0;
            $new_uncat_folder_name = JText::_('COM_JDOWNLOADS_UNCATEGORISED_CATEGORY_NAME');
            $target_path = $files_upload_dir.'/'.$new_uncat_folder_name;
            
            if (!$uncat_created){
                JDownloadsHelper::changeParamSetting('create_auto_cat_dir', '1');
                if (!JFolder::exists($files_upload_dir.'/'.$new_uncat_folder_name)){
				    $result = $this->createUncatCategory($files_upload_dir);
                    if ($result){
                        $db->setQuery('SELECT `id` FROM #__jdownloads_categories WHERE `title` = '.$db->quote($new_uncat_folder_name).' AND `level` = "1"');
                        $new_cat_id = (int)$db->loadResult();
                    }
                } 
                
                JDownloadsHelper::changeParamSetting('uncat_already_created', '1');
            
                // Need we still to move the exists uncategorised files to the new created category folder (above)?
                if (JFolder::exists($files_upload_dir.'/_uncategorised_files')){
                    $files = scandir($files_upload_dir.'/_uncategorised_files');
                    if (count($files)){
                        // we must move all files here and update the database
                        foreach ($files as $file){
                            if ($file == '.' || $file == '..' || $file == 'index.html' || strpos($file, '"') > 0 ){
                                continue;
                            }
                            $result = JFile::move($files_upload_dir.'/_uncategorised_files/'.$file, $target_path.'/'.$file);
                            if ($result) $amount++;
                        }

                        // update the db table
                        $db->setQuery('UPDATE #__jdownloads_files SET `catid` = '.$db->quote($new_cat_id).' WHERE `catid` = "1"');
                        $db->execute(); 
                    } 
                    
                    // we must finaly only delete the old folders
                    JFolder::delete($files_upload_dir.'/_uncategorised_files');
                    
                    if (JFolder::exists($files_upload_dir.'/_private_user_area')){
                        JFolder::delete($files_upload_dir.'/_private_user_area');
                    }
                    
                    // Add some messages to the message queue
                    $app->enqueueMessage(JText::sprintf('COM_JDOWNLOADS_UPGRADE32_AMOUNT_UNCAT_FILES_MOVED_MSG', $amount), 'message');
                    $app->enqueueMessage(JText::_('COM_JDOWNLOADS_UPGRADE32_OLD_FOLDERS_DELETED_MSG'), 'message'); 
                    $app->enqueueMessage(JText::_('COM_JDOWNLOADS_UPGRADE32_SUCCESFUL_FINISHED_MSG'), 'message');
                    
                    // Add messages to log
                    $logoptions['text_entry_format'] = '{DATE} {TIME}    {PRIORITY}     {MESSAGE}';
                    $logoptions['text_file'] = 'com_jdownloads_install_logs.php';
            
                    JLog::addLogger($logoptions, JLog::ALL, 'jD');

                    try
                    {
                        JLog::add(JText::sprintf('COM_JDOWNLOADS_UPGRADE32_AMOUNT_UNCAT_FILES_MOVED_MSG', $amount), JLog::INFO, 'jD');
                        JLog::add(JText::_('COM_JDOWNLOADS_UPGRADE32_OLD_FOLDERS_DELETED_MSG'), JLog::INFO, 'jD');
                        JLog::add(JText::_('COM_JDOWNLOADS_UPGRADE32_SUCCESFUL_FINISHED_MSG'), JLog::INFO, 'jD');
                    }            
                    catch (RuntimeException $exception)
                    {
                        // Informational log only
                    }
                }
            }
        }
        
        // Check here wether all option settings are actualized after an UPDATE from 3.9.x
        
        // Help URL 
        $help_url = $params->get('help_url');
        if ($help_url != 'https://www.jdownloads.net/index.php?option=com_content&view=article&id='){
            JDownloadsHelper::changeParamSetting('help_url', 'https://www.jdownloads.net/index.php?option=com_content&view=article&id=');    
        }
        
        // 3 new options added in 3.9.6 - so make sure all has correct default values after update from a prior 3.9.x installation
        $new_options = $params->get('be_amount_of_pics_in_downloads_list');
        if ($new_options === null){
            JDownloadsHelper::changeParamSetting('be_amount_of_pics_in_downloads_list', 3);
            JDownloadsHelper::changeParamSetting('view_preview_file_in_downloads_list', 1);
            JDownloadsHelper::changeParamSetting('view_price_field_in_downloads_list', 1);
        }
        
        // New options added in 3.9.7 - so make sure that all it has the default value after update
        $link_in_symbols = $params->get('link_in_symbols');
        if ($link_in_symbols === null){
            JDownloadsHelper::changeParamSetting('link_in_symbols', 1);
            // New monitoring options
            // For categories
            JDownloadsHelper::changeParamSetting('autopublish_use_cat_default_values', 0);
            JDownloadsHelper::changeParamSetting('autopublish_cat_pic_default_filename', 'folder.png');
            JDownloadsHelper::changeParamSetting('autopublish_default_cat_description', '');
            JDownloadsHelper::changeParamSetting('autopublish_cat_access_level', 1);
            JDownloadsHelper::changeParamSetting('autopublish_cat_language', '*');
            JDownloadsHelper::changeParamSetting('autopublish_cat_tags', '');
            JDownloadsHelper::changeParamSetting('autopublish_cat_created_by', 0);
            // For Downloads
            JDownloadsHelper::changeParamSetting('autopublish_use_default_values', 1);
            JDownloadsHelper::changeParamSetting('autopublish_title_format_option', 0);
            JDownloadsHelper::changeParamSetting('autopublish_default_description', '');
            JDownloadsHelper::changeParamSetting('autopublish_access_level', 1);
            JDownloadsHelper::changeParamSetting('autopublish_language', '*');
            JDownloadsHelper::changeParamSetting('autopublish_tags', '');
            JDownloadsHelper::changeParamSetting('autopublish_created_by', 0);
            JDownloadsHelper::changeParamSetting('autopublish_price', '');
            JDownloadsHelper::changeParamSetting('autopublish_reset_use_default_values', 1);
        }
        
        // Currently we do not support the option to hide empty categories. This must first be overworked in the future.
        // So it must always be enabled
        JDownloadsHelper::changeParamSetting('view_empty_categories', 1);

        // End options check
        
        $this->addToolbar();
 
        $input = JFactory::getApplication()->input;

        $input->set('tmpl', 'cpanel');

        // Display the cpanel modules
        $this->modules = JModuleHelper::getModules('jdcpanel');

		parent::display($tpl);
	}
    
    /**
     * Add the page title and toolbar.
     *
     * 
     */
    protected function addToolbar()
    {
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';
        
        $params = JComponentHelper::getParams('com_jdownloads');
        
        $state    = $this->get('State');
        $canDo    = JDownloadsHelper::getActions();
        $user     = JFactory::getUser();

        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');
        $document->addScriptDeclaration('function openWindow (url, h, w) {
        params = \'height=\' + h + \', width=\' + w + \', STATUS=YES, DIRECTORIES=NO, MENUBAR=NO, SCROLLBARS=YES, RESIZABLE=NO\';
        scanWindow = window.open(url, "_blank", params);
        scanWindow.focus();
        }');

        
        JDownloadsHelper::addSubmenu('jdownloads');
        
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_CPANEL'), 'home-2');
        
        JToolBarHelper::link('index.php?option=com_jdownloads', JText::_('COM_JDOWNLOADS_REFRESH'), 'refresh cpanel');        
        
        if ($canDo->get('core.admin')){
            JToolBarHelper::preferences('com_jdownloads');
            JToolBarHelper::divider();
        }

        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '152&tmpl=jdhelp';
        $help_url = $params->get('help_url').$help_page;
        $exists_url = JDownloadsHelper::existsHelpServerURL($help_url);
        if ($exists_url !== false){
            JToolBarHelper::help($help_url, false, $exists_url);
        } else {
            JToolBarHelper::help('help.general', true); 
        }
    }        
    
    /**
     * Create the required Uncategorised Category and Folder after installation
     *
     * 
     */ 
    public static function createUncatCategory($root_dir)
    {         
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );
        
        $model = JModelLegacy::getInstance( 'Category', 'jdownloadsModel' );
        
        if (!$root_dir){
			$root_dir = JPATH_ROOT.'/jdownloads';
		}
            
        if (JFolder::exists($root_dir)) {
            if (is_writable($root_dir)) {      
                // create it only when the folder for the 'Uncategorised' category still not exists
                if (!JFolder::exists($root_dir.'/'.JText::_('COM_JDOWNLOADS_UNCATEGORISED_CATEGORY_NAME'))){
                    $create_result = $model->createCategory( JText::_('COM_JDOWNLOADS_UNCATEGORISED_CATEGORY_NAME' ), '1', '', '', 1);
                    if (!$create_result){
                        JError::raiseWarning( 100, JText::_('COM_JDOWNLOADS_BATCH_CANNOT_CREATE_FOLDER'));
                        return false;
                    } else {
                        return true;
                    }   
                } else {
                    // JError::raiseWarning( 100, JText::_('COM_JDOWNLOADS_SAMPLE_DATA_CREATE_ERROR'));
                    // return false;
                }                                
            } else {
                // error message: upload folder not writeable
                JError::raiseWarning( 100, JText::_('COM_JDOWNLOADS_ROOT_FOLDER_NOT_WRITABLE'));
                return false;
            } 
        } else {
            // error message: upload folder not found
            JError::raiseWarning( 100, JText::sprintf('COM_JDOWNLOADS_AUTOCHECK_DIR_NOT_EXIST', $root_dir));
            return false;
        } 	
    } 
}
