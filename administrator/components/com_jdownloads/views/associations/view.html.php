<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('JDAssociationsHelper', JPATH_ADMINISTRATOR . '/components/com_jdownloads/helpers/associationshelper.php');

/**
 * View class for a list of articles.
 *
 * @since  3.7.0
 */
class JDownloadsViewAssociations extends JViewLegacy
{
	/**
	 * An array of items
	 *
	 * @var   array
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
     * Form object for search filters
     *
     * @var  JForm
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

	/**
	 * The model state
	 *
	 * @var    object
	 *
	 * @since  3.7.0
	 */
	protected $state;
    
    protected $_defaultModel = 'associations';

	/**
	 * Selected item type properties.
	 *
	 * @var    Registry
	 *
	 * @since  3.7.0
	 */
	public $itemType = null;
    
    /**
     * The sidebar markup
     *
     * @var  string
     */
    protected $sidebar;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function display($tpl = null)
	{
        // Load the backend helper
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';
        
        JDownloadsHelper::addSubmenu('associations');
        
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (!self::isEnabledAssoc())
		{
			$link = JRoute::_('index.php?option=com_plugins&task=plugin.edit&extension_id=' . JDAssociationsHelper::getLanguagefilterPluginId());
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JDOWNLOADS_ASSOCIATIONS_ERROR_NO_ASSOC', $link), 'warning');
		}
		elseif ($this->state->get('itemtype') == '' || $this->state->get('language') == '')
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JDOWNLOADS_ASSOCIATIONS_NOTICE_NO_SELECTORS'), 'notice');
		}
		else
		{
			$type = null;

			list($extensionName, $typeName) = explode('.', $this->state->get('itemtype'));

			$extension = JDAssociationsHelper::getSupportedExtension('com_jdownloads');

			$types = $extension->get('types');

			if (array_key_exists($typeName, $types))
			{
				$type = $types[$typeName];
			}

			$this->itemType = $type;

			if (is_null($type))
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM__JDOWNLOADS_ASSOCIATIONS_ERROR_NO_TYPE'), 'warning');
			}
			else
			{
				$this->extensionName = $extensionName;
				$this->typeName      = $typeName;
				$this->typeSupports  = array();
				$this->typeFields    = array();

				$details = $type->get('details');

				if (array_key_exists('support', $details))
				{
					$support = $details['support'];
					$this->typeSupports = $support;
				}

				if (array_key_exists('fields', $details))
				{
					$fields = $details['fields'];
					$this->typeFields = $fields;
				}

				// Dynamic filter form.
				// This selectors doesn't have to activate the filter bar.
				unset($this->activeFilters['itemtype']);
				unset($this->activeFilters['language']);

				// Remove filters options depending on selected type.
				if (empty($support['state']))
				{
					unset($this->activeFilters['state']);
					$this->filterForm->removeField('state', 'filter');
				}

				if (empty($support['category']))
				{
					unset($this->activeFilters['category_id']);
					$this->filterForm->removeField('category_id', 'filter');
				}

				if ($extensionName !== 'com_menus')
				{
					unset($this->activeFilters['menutype']);
					$this->filterForm->removeField('menutype', 'filter');
				}

				if (empty($support['level']))
				{
					unset($this->activeFilters['level']);
					$this->filterForm->removeField('level', 'filter');
				}

				if (empty($support['acl']))
				{
					unset($this->activeFilters['access']);
					$this->filterForm->removeField('access', 'filter');
				}

				// Add extension attribute to category filter.
				if (empty($support['catid']))
				{
					$this->filterForm->setFieldAttribute('category_id', 'extension', $extensionName, 'filter');

					if ($this->getLayout() == 'modal')
					{
						// We need to change the category filter to only show categories tagged to All or to the forced language.
						if ($forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
						{
							$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
						}
					}
				}

				$this->items      = $this->get('Items');
				$this->pagination = $this->get('Pagination');

				$linkParameters = array(
					'layout'     => 'edit',
					'itemtype'   => $extensionName . '.' . $typeName,
					'task'       => 'association.edit',
				);

				$this->editUri = 'index.php?option=com_jdownloads&view=association&' . http_build_query($linkParameters);
			}
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal' && $this->getLayout() !== 'modallist') {        
            $this->addToolbar();
            $this->sidebar = JHtmlSidebar::render();
        }

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
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';

        $params = JComponentHelper::getParams('com_jdownloads');

        $user = JFactory::getUser();
        
		if (isset($this->typeName) && isset($this->extensionName)){
			$helper = JDAssociationsHelper::getExtensionHelper('com_jdownloads');
			$title  = $helper->getTypeTitle($this->typeName);

			$languageKey = strtoupper($this->extensionName . '_' . $title . 'S');

			if ($this->typeName === 'category'){
				$languageKey = strtoupper($this->extensionName) . '_CATEGORIES';
			}

			JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_MULTILINGUAL_ASSOCIATIONS').' ('.JText::_($languageKey).')', 'contract jddownloads');

		} else {
            JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_MULTILINGUAL_ASSOCIATIONS'), 'contract jddownloads');
		}
        
        JToolBarHelper::link('index.php?option=com_jdownloads', JText::_('COM_JDOWNLOADS_CPANEL'), 'home-2 cpanel');        

		if ($user->authorise('core.admin', 'com_jdownloads') || $user->authorise('core.options', 'com_jdownloads')){
			
            if (!isset($this->typeName)){
				JToolbarHelper::custom('associations.purge', 'purge', 'purge', 'COM_JDOWNLOADS_ASSOCIATIONS_PURGE', false, false);
				JToolbarHelper::custom('associations.clean', 'refresh', 'refresh', 'COM_JDOWNLOADS_ASSOCIATIONS_DELETE_ORPHANS', false, false);
			}
		}

        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '163&tmpl=jdhelp';
        $help_url = $params->get('help_url').$help_page;
        $exists_url = JDownloadsHelper::existsHelpServerURL($help_url);
        if ($exists_url !== false){
            JToolBarHelper::help($help_url, false, $exists_url);
        } else {
            JToolBarHelper::help('help.general', true); 
        }
        
	}
    
    /**
     * Method to determine if the language filter Associations parameter is enabled.
     * This works for both site and administrator.
     *
     * @return  boolean  True if the parameter is implemented; false otherwise.
     *
     * @since   3.2
     */
    public static function isEnabledAssoc()
    {
        // Flag to avoid doing multiple database queries.
        static $tested = false;

        // Status of language filter parameter.
        static $enabled = false;

        if (self::isEnabledPlugin())
        {
            // If already tested, don't test again.
            if (!$tested)
            {
                $plugin = \JPluginHelper::getPlugin('system', 'languagefilter');

                if (!empty($plugin))
                {
                    $params = new Registry($plugin->params);
                    $enabled  = (boolean) $params->get('item_associations', true);
                }

                $tested = true;
            }
        }

        return $enabled;
    }
    
    /**
     * Method to determine if the language filter plugin is enabled.
     * This works for both site and administrator.
     *
     * @return  boolean  True if site is supporting multiple languages; false otherwise.
     *
     * @since   2.5.4
     */
    public static function isEnabledPlugin()
    {
        // Flag to avoid doing multiple database queries.
        static $tested = false;

        // Status of language filter plugin.
        static $enabled = false;

        // Get application object.
        $app = \JFactory::getApplication();

        // If being called from the frontend, we can avoid the database query.
        if ($app->isClient('site'))
        {
            $enabled = $app->getLanguageFilter();

            return $enabled;
        }

        // If already tested, don't test again.
        if (!$tested)
        {
            // Determine status of language filter plugin.
            $db = \JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('enabled')
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('languagefilter'));
            $db->setQuery($query);

            $enabled = $db->loadResult();
            $tested = true;
        }

        return (bool) $enabled;
    }
}
