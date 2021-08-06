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
 
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * upload manager View
 *
 */
class jdownloadsViewuploads extends JViewLegacy
{
    protected $canDo;
    
    /**
	 * uploads display method
	 * @return void
	 **/
	function display($tpl = null)
	{
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';
        
        $params = JComponentHelper::getParams('com_jdownloads');
        
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');
        
        // What Access Permissions does this user have? What can (s)he do?
        $this->canDo = JDownloadsHelper::getActions();
        
        $language = JFactory::getLanguage();
        $lang = $language->getTag();
        
        $langfiles        = JPATH_COMPONENT_ADMINISTRATOR.'/assets/plupload/js/i18n/';        
        $PLdataDir        = JURI::root() . "administrator/components/com_jdownloads/assets/plupload/";
        $document         = JFactory::getDocument();
        $PLuploadScript   = new PLuploadScript($PLdataDir);
        $runtimeScript    = $PLuploadScript->runtimeScript;
        $runtime          = $PLuploadScript->runtime;
                
        //add default PL css
        $document->addStyleSheet($PLdataDir . 'css/plupload.css');
        
        //add PL styles and scripts
        $document->addStyleSheet($PLdataDir . 'js/jquery.plupload.queue/css/jquery.plupload.queue.css', 'text/css', 'screen');
        $document->addScript($PLdataDir . 'js/jquery.min.js');
		$document->addScript($PLdataDir . 'js/plupload.full.min.js');
		
        // load plupload language file
        if ($lang){
            if (JFile::exists($langfiles . $lang.'.js')){
                $document->addScript($PLdataDir . 'js/i18n/'.$lang.'.js');      
            } else {
                $document->addScript($PLdataDir . 'js/i18n/en-GB.js');      
            }
        } 
        $document->addScript($PLdataDir . 'js/jquery.plupload.queue/jquery.plupload.queue.js');
        $document->addScriptDeclaration( $PLuploadScript->getScript() );
        
        //set variables for the template
        $this->enableLog = $params->get('plupload_enable_uploader_log');
        $this->runtime = $runtime;
        $this->currentDir = $params->get('files_uploaddir').'/';
                
        //set toolbar
        $this->addToolBar();
        $this->sidebar = JHtmlSidebar::render();        
        // Display the template
        parent::display($tpl);
    }
    
    /**
     * Setting the toolbar
     */
    protected function addToolBar() 
    {
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';

        $params = JComponentHelper::getParams('com_jdownloads');
        
        $canDo    = JDownloadsHelper::getActions();
        $user     = JFactory::getUser();

        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');
        
        JDownloadsHelper::addSubmenu('files');  
        
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_FILESLIST_TITLE_FILES_UPLOAD'), 'upload jdupload');
        
        JToolBarHelper::custom( 'uploads.files', 'upload.png', 'upload.png', JText::_('COM_JDOWNLOADS_FILES'), false, true );
        JToolBarHelper::custom( 'uploads.downloads', 'folder.png', 'folder.png', JText::_('COM_JDOWNLOADS_DOWNLOADS'), false, false );
        
        JToolBarHelper::divider();
        
        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '193&tmpl=jdhelp';
        $help_url = $params->get('help_url').$help_page;
        $exists_url = JDownloadsHelper::existsHelpServerURL($help_url);
        if ($exists_url !== false){
            JToolBarHelper::help($help_url, false, $exists_url);
        } else {
            JToolBarHelper::help('help.general', true); 
        }

    }
}
?>