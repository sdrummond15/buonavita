<?php

defined('_JEXEC') or die();

/**
 * Files View
 *
 * @package    jDownloads
 */
class jdownloadsViewfiles extends JViewLegacy
{
	
    protected $items;
    protected $pagination;
    protected $state;
    
    public $filterForm;
    public $activeFilters;
    
    /**
     * list files display method
     * @return void
     **/
    function display($tpl = null)
    {
        $this->state = $this->get('State');
        
        // Get the files data from the model
        $items = $this->get('Items');

        // the filter form file must exist in the models/forms folder (e.g. filter_files.xml) 
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        
        // Assign data to the view
        $this->items        = $items;
        $this->pagination   = $this->get('Pagination');
        $this->state        = $this->get('state');
        
        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();        
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
        
        $canDo    = JDownloadsHelper::getActions();
        $user     = JFactory::getUser();

        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');
        
        JDownloadsHelper::addSubmenu('files');  
        
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_FILES'), 'copy jdfiles');
        
        JToolBarHelper::link('index.php?option=com_jdownloads', JText::_('COM_JDOWNLOADS_CPANEL'), 'home-2 cpanel');

        JToolBarHelper::custom( 'files.uploads', 'upload.png', 'upload.png', JText::_('COM_JDOWNLOADS_FILESLIST_TITLE_FILES_UPLOAD'), false );
        JToolBarHelper::custom( 'files.downloads', 'stack.png', 'stack.png', JText::_('COM_JDOWNLOADS_DOWNLOADS'), false );
                    
        if ($canDo->get('core.delete')) {
            JToolBarHelper::deleteList(JText::_('COM_JDOWNLOADS_DELETE_LIST_ITEM_CONFIRMATION'), 'files.delete', 'COM_JDOWNLOADS_TOOLBAR_REMOVE');
            JToolBarHelper::divider();
        } 

        JToolBarHelper::divider();
        
        if ($canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_jdownloads');
            JToolBarHelper::divider();
        }         
        
        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '000&tmpl=jdhelp';
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