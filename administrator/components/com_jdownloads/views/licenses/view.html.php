<?php

defined('_JEXEC') or die();

class jdownloadsViewLicenses extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $sidebar;
    protected $canDo;    
    
    public $filterForm;
    public $activeFilters;
    
    /**
	 * licenses view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        
        // The filter form file must exist in the models/forms folder (e.g. filter_licenses.xml) 
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');        
        
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
        
        JDownloadsHelper::addSubmenu('licenses');
        
        // Get the toolbar object instance
        $bar = JToolbar::getInstance('toolbar');
        
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_LICENSES'), 'key jdlicenses');
        
        JToolBarHelper::link('index.php?option=com_jdownloads', JText::_('COM_JDOWNLOADS_CPANEL'), 'home-2 cpanel');
        
        if ($canDo->get('core.create')) {
            JToolBarHelper::addNew('license.add');
        }

        if ($canDo->get('core.edit')) {
            JToolBarHelper::editList('license.edit');
        }    

        if ($canDo->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('licenses.publish', 'JTOOLBAR_PUBLISH', true);
            JToolBarHelper::unpublish('licenses.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        }
        
        if ($canDo->get('core.admin')){
            JToolbarHelper::checkin('licenses.checkin');
        }        
         
        if ($canDo->get('core.delete')) {
            JToolBarHelper::deleteList(JText::_('COM_JDOWNLOADS_DELETE_LIST_ITEM_CONFIRMATION'), 'licenses.delete', 'COM_JDOWNLOADS_TOOLBAR_REMOVE');
        }
        
        // Add a batch button
        if ($user->authorise('core.create', 'com_jdownloads')
            && $user->authorise('core.edit', 'com_jdownloads')
            && $user->authorise('core.edit.state', 'com_jdownloads')){
            $title = JText::_('JTOOLBAR_BATCH');

            // Instantiate a new JLayoutFile instance and render the batch button
            $layout = new JLayoutFile('joomla.toolbar.batch');

            $dhtml = $layout->render(array('title' => $title));
            $bar->appendButton('Custom', $dhtml, 'batch');
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
            'a.ordering'     => JText::_('COM_JDOWNLOADS_ORDERING'),
            'a.published'    => JText::_('COM_JDOWNLOADS_STATUS'),
            'a.title'        => JText::_('COM_JDOWNLOADS_TITLE'),
            'a.url'          => JText::_('COM_JDOWNLOADS_FEATURED'),
            'a.description'  => JText::_('COM_JDOWNLOADS_DESCRIPTION'),
            'language'       => JText::_('COM_JDOWNLOADS_LANGUAGE'),
            'a.id'           => JText::_('COM_JDOWNLOADS_ID')
        );
    }    
}