<?php

defined('_JEXEC') or die;

/**
 * View to edit jDownloads limits from a Joomla user group.
 *
 */
class jdownloadsViewGroup extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $state;
    protected $canDo;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		require_once JPATH_COMPONENT.'/helpers/jdownloads.php';
        
        $this->state	= $this->get('State');
		$this->item		= $this->get('Item');
        $this->group_id = $this->item->group_id;
		$this->form		= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

        $this->form->title = JDownloadsHelper::getUserGroupInfos($this->item->group_id);

		$this->addToolbar();
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
        
        JRequest::setVar('hidemainmenu', 1);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
        $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));        
		$canDo		= JDownloadsHelper::getActions();
        
        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');

		JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_USERGROUP_EDIT_TITLE'), 'pencil-2 jdgroups');

		if ($canDo->get('edit.user.limits')) {
			JToolBarHelper::apply('group.apply');
			JToolBarHelper::save('group.save');
		}

        JToolBarHelper::cancel('group.cancel', 'JTOOLBAR_CLOSE');

		JToolBarHelper::divider();
        
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
