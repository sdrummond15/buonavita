<?php


defined('_JEXEC') or die;

/**
 * View class for a list of user groups.
 *
 */
class jdownloadsViewGroups extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
    
    public $filterForm;
    public $activeFilters;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->items		 = $this->get('Items');
		$this->pagination	 = $this->get('Pagination');
		$this->state		 = $this->get('State');
        
        // the filter form file must exist in the models/forms folder (e.g. filter_files.xml) 
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
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/jdownloads.php';

        $params = JComponentHelper::getParams('com_jdownloads');
        
        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');
        
        JDownloadsHelper::addSubmenu('groups');		
        
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_USER_GROUPS'), 'users jdgroups');

        JToolBarHelper::link('index.php?option=com_jdownloads', JText::_('COM_JDOWNLOADS_CPANEL'), 'home-2 cpanel');
        
        $canDo    = jdownloadsHelper::getActions();
        
        if ($canDo->get('edit.user.limits') || $canDo->get('core.admin')) {
			JToolBarHelper::editList('group.edit', 'COM_JDOWNLOADS_USERGROUPS_CHANGE_LIMITS_TITLE');
			JToolBarHelper::divider();
		}

		if ($canDo->get('core.admin')) {
            JToolBarHelper::custom( 'groups.resetLimits', 'refresh.png', 'refresh.png', JText::_('COM_JDOWNLOADS_USERGROUPS_RESET_LIMITS_TITLE'), true, false );
			JToolBarHelper::divider();
		}
        
        if ($canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_jdownloads');
            JToolBarHelper::divider();
        }         
        
        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '167&tmpl=jdhelp';
        $help_url = $params->get('help_url').$help_page;
        $exists_url = JDownloadsHelper::existsHelpServerURL($help_url);
        if ($exists_url !== false){
            JToolBarHelper::help($help_url, false, $exists_url);
        } else {
            JToolBarHelper::help('help.general', true); 
        }
        
	}

}    