<?php

defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class jdownloadsViewlogs extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
        
    public $filterForm;
    public $activeFilters;
    
    /**
	 * logs view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';
        
        $this->state        = $this->get('State');
        $this->items        = $this->get('Items');
        $this->pagination   = $this->get('Pagination');
        
        // the filter form file must exist in the models/forms folder (e.g. filter_files.xml) 
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        
        $this->logs_header_info = JDownloadsHelper::getLogsHeaderInfo();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

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
        
        $state    = $this->get('State');
        $canDo    = JDownloadsHelper::getActions();
        $user     = JFactory::getUser();

        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');
        
        JDownloadsHelper::addSubmenu('logs');
        
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_LOGS_TITLE_HEAD'), 'list-2 jdlogs');
        
        JToolBarHelper::link('index.php?option=com_jdownloads', JText::_('COM_JDOWNLOADS_CPANEL'), 'home-2 cpanel');
        
        if ($canDo->get('core.edit')) {
            JToolBarHelper::custom( 'logs.blockip', 'cancel.png', 'cancel.png', JText::_('COM_JDOWNLOADS_BACKEND_LOG_LIST_BLOCK_IP'), true ); 
        }    
        if ($canDo->get('core.delete')) {
            JToolBarHelper::deleteList(JText::_('COM_JDOWNLOADS_DELETE_LIST_ITEM_CONFIRMATION'), 'logs.delete', 'COM_JDOWNLOADS_TOOLBAR_REMOVE');
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
    
    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     *
     */
    protected function getSortFields()
    {
        return array(
            'a.log_datetime' => JText::_('COM_JDOWNLOADS_DATE'),
            'a.type'         => JText::_('COM_JDOWNLOADS_FILTER_LOGS_TYPE'),
            'a.log_ip'       => JText::_('COM_JDOWNLOADS_LOGS_COL_IP_LABEL'),
            'a.id'           => JText::_('COM_JDOWNLOADS_ID')
        );
    }         
}