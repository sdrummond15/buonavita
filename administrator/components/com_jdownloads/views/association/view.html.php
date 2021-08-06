<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * View class for a list of articles.
 *
 * @since  3.7.0
 */
class JDownloadsViewAssociation extends JViewLegacy
{
	/**
	 * An array of items
	 *
	 * @var    array
	 *
	 * @since  3.7.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    JPagination
	 *
	 * @since  3.7.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    object
	 *
	 * @since  3.7.0
	 */
	protected $state;

	/**
	 * Selected item type properties.
	 *
	 * @var    Registry
	 *
	 * @since  3.7.0
	 */
	public $itemType = null;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		// Check for errors.
		if (count($errors = $this->get('Errors'))){
			throw new Exception(implode("\n", $errors), 500);
		}

		$this->app  = JFactory::getApplication();
		$this->form = $this->get('Form');
		$input      = $this->app->input;
		$this->referenceId = $input->get('id', 0, 'int');

		list($extensionName, $typeName) = explode('.', $input->get('itemtype', '', 'string'));

		$extension = JDAssociationsHelper::getSupportedExtension($extensionName);
		$types     = $extension->get('types');

		if (array_key_exists($typeName, $types)){
			$this->type          = $types[$typeName];
			$this->typeSupports  = array();
			$details             = $this->type->get('details');
			$this->save2copy     = false;

			if (array_key_exists('support', $details)){
				$support = $details['support'];
				$this->typeSupports = $support;
			}

			if (!empty($this->typeSupports['save2copy'])){
				$this->save2copy = true;
			}
		}

		$this->extensionName = $extensionName;
		$this->typeName      = $typeName;
		$this->itemtype      = $extensionName . '.' . $typeName;

		$languageField = JDAssociationsHelper::getTypeFieldName($extensionName, $typeName, 'language');
		$referenceId   = $input->get('id', 0, 'int');
		$reference     = ArrayHelper::fromObject(JDAssociationsHelper::getItem($extensionName, $typeName, $referenceId));

		$this->referenceLanguage = $reference[$languageField];
		$this->referenceTitle    = JDAssociationsHelper::getTypeFieldName($extensionName, $typeName, 'title');

		$options = array(
			'option'    => $extensionName,
			'view'      => $typeName,
			'extension' => $extensionName,
			'tmpl'      => 'component',
		);

		// Reference and target edit links.
		$this->editUri = 'index.php?' . http_build_query($options);

		// Get target language.
		$this->targetId         = '0';
		$this->targetLanguage   = '';
		$this->defaultTargetSrc = '';
		$this->targetAction     = '';
		$this->targetTitle      = '';

		if ($target = $input->get('target', '', 'string')){
			$matches = preg_split("#[\:]+#", $target);
			$this->targetAction     = $matches[2];
			$this->targetId         = $matches[1];
			$this->targetLanguage   = $matches[0];
			$this->targetTitle      = JDAssociationsHelper::getTypeFieldName($extensionName, $typeName, 'title');
			$task                   = $typeName . '.' . $this->targetAction;

			/* Let's put the target src into a variable to use in the javascript code
			*  to avoid race conditions when the reference iframe loads.
			*/
			$document = JFactory::getDocument();
			$document->addScriptOptions('targetSrc', JRoute::_($this->editUri . '&task=' . $task . '&id=' . (int) $this->targetId));
			$this->form->setValue('itemlanguage', '', $this->targetLanguage . ':' . $this->targetId . ':' . $this->targetAction);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function addToolbar()
	{
		// Hide main menu.
		JFactory::getApplication()->input->set('hidemainmenu', 1);

		require_once JPATH_COMPONENT.'/helpers/jdownloads.php';

        $params = JComponentHelper::getParams('com_jdownloads');

        $user = JFactory::getUser();
        
        $helper = JDAssociationsHelper::getExtensionHelper('com_jdownloads');
        $title  = $helper->getTypeTitle($this->typeName);

        $languageKey = strtoupper($this->extensionName . '_' . $title);

        if ($this->typeName === 'category'){
            $languageKey = strtoupper($this->extensionName) . '_CATEGORY';
        }

        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_ASSOCIATIONS_EDIT_ASSOCIATION').' ('.JText::_($languageKey).')', 'contract jddownloads');

		$bar = JToolbar::getInstance('toolbar');

		$bar->appendButton(
			'Custom', '<button onclick="Joomla.submitbutton(\'reference\')" '
			. 'class="btn btn-small btn-success"><span class="icon-apply icon-white" aria-hidden="true"></span>'
			. JText::_('COM_JDOWNLOADS_ASSOCIATIONS_SAVE_REFERENCE') . '</button>', 'reference'
		);

		$bar->appendButton(
			'Custom', '<button onclick="Joomla.submitbutton(\'target\')" '
			. 'class="btn btn-small btn-success"><span class="icon-apply icon-white" aria-hidden="true"></span>'
			. JText::_('COM_JDOWNLOADS_ASSOCIATIONS_SAVE_TARGET') . '</button>', 'target'
		);

		if ($this->typeName === 'category' || $this->extensionName === 'com_menus' || $this->save2copy === true){
			JToolBarHelper::custom('copy', 'copy.png', '', 'COM_JDOWNLOADS_ASSOCIATIONS_COPY_REFERENCE', false);
		}

		JToolbarHelper::cancel('association.cancel', 'JTOOLBAR_CLOSE');

        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '163&tmpl=jdhelp';
        $help_url = $params->get('help_url').$help_page;
	    $exists_url = JDownloadsHelper::existsHelpServerURL($help_url);
	    
        if ($exists_url !== false){
            JToolBarHelper::help($help_url, false, $exists_url);
        } else {
            JToolBarHelper::help('help.general', true); 
        }

		JHtmlSidebar::setAction('index.php?option=com_jdownloads');
	}
}
