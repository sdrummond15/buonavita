<?php
/**
 * @package jDownloads
 * @version 2.0  
 * @copyright (C) 2007 - 2012 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Categories view class
 *
 * @package    jDownloads
 */
class jdownloadsViewCategories extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;
    protected $string;
    protected $assoc;
    
    public $activeFilters;
    public $filterForm;
    
    
    protected static $rows = array();
    protected $canDo;
    
    
    /**
	 * Categories view display
	 *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
	 **/
	function display($tpl = null)
	{
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';  
        
        $this->state        = $this->get('State');
        $this->items        = $this->get('Items');
        $this->pagination   = $this->get('Pagination');
        
        // The filter form file must exist in the models/forms folder (e.g. filter_categories.xml) 
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        
        // What Access Permissions does this user have? What can (s)he do?
        $this->canDo = jDownloadsHelper::getActions();
        
        $this->assoc = $this->get('Assoc');        
       
        // Check for errors.
        if (count($errors = $this->get('Errors'))){
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // Preprocess the list of items to find ordering divisions.
        foreach ($this->items as &$item){
            $this->ordering[$item->parent_id][] = $item->id;
        }
        
        // build categories list box for batch operations 
        $lists = array();
        $config = array('filter.published' => array(0, 1));
        $select[] = JHtml::_('select.option', 0, JText::_('COM_JDOWNLOADS_SELECT_CATEGORY'));
        $select[] = JHtml::_('select.option', 1, JText::_('COM_JDOWNLOADS_BATCH_ROOT_CAT'));
        
        // get the categories data
        $categories = $this->getCategoriesList($config);
        $this->categories = @array_merge($select, $categories);        

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal'){
            $this->addToolbar();
            $this->sidebar = JHtmlSidebar::render();
        } else {
            // In Download associations modal we need to remove language filter if forcing a language.
            if ($forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
            {
                // If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
                $languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
                $this->filterForm->setField($languageXml, 'filter', true);

                // Also, unset the active language filter so the search tools is not open by default with this filter.
                unset($this->activeFilters['language']);
            }
        }

        return parent::display($tpl);       
	}
    
    /**
     * Add the page title and toolbar.
     *
     * @since    1.6
     */
    protected function addToolbar()
    {
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';

        $params = JComponentHelper::getParams('com_jdownloads');
        
        $categoryId = $this->state->get('filter.category_id');
        $canDo      = JDownloadsHelper::getActions($categoryId, 'category');        
        $state      = $this->get('State');
        $user       = JFactory::getUser();

        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');
        
        // Get the toolbar object instance
        $bar = JToolBar::getInstance('toolbar');
        
        JDownloadsHelper::addSubmenu('categories');
        
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_CATEGORIES'), 'folder jdcategories');
        
        JToolBarHelper::link('index.php?option=com_jdownloads', JText::_('COM_JDOWNLOADS_CPANEL'), 'home-2 cpanel');        
        
        if ($canDo->get('core.create')) {
            JToolBarHelper::addNew('category.add');
        }
        if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
            JToolBarHelper::editList('category.edit');
        }    
        if ($canDo->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('categories.publish', 'COM_JDOWNLOADS_PUBLISH', true);
            JToolBarHelper::unpublish('categories.unpublish', 'COM_JDOWNLOADS_UNPUBLISH', true);
            JToolBarHelper::divider();
            JToolBarHelper::checkin('categories.checkin');
        } 
        if ($canDo->get('core.delete')) {
            JToolBarHelper::deleteList(JText::_('COM_JDOWNLOADS_DELETE_LIST_ITEM_CONFIRMATION'), 'categories.delete', 'COM_JDOWNLOADS_TOOLBAR_REMOVE');
            JToolBarHelper::divider();
        }
        
        // Add a batch button
        if ($canDo->get('core.create') && $canDo->get('core.edit') && $canDo->get('core.edit.state'))
        {
            JHtml::_('bootstrap.modal', 'collapseModal');
            $title = JText::_('JTOOLBAR_BATCH');

            // Instantiate a new JLayoutFile instance and render the batch button
            $layout = new JLayoutFile('joomla.toolbar.batch');

            $dhtml = $layout->render(array('title' => $title));
            $bar->appendButton('Custom', $dhtml, 'batch');
        }          
        
        if ($canDo->get('core.admin')) {
            JToolBarHelper::custom('categories.rebuild', 'refresh.png', 'refresh_f2.png', 'COM_JDOWNLOADS_REBUILD', false);
            JToolBarHelper::divider();
        }
        
        if ($canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_jdownloads');
            JToolBarHelper::divider();
        }         
        
        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '107&tmpl=jdhelp';
        $help_url = $params->get('help_url').$help_page;
        $exists_url = JDownloadsHelper::existsHelpServerURL($help_url);
        if ($exists_url !== false){
            JToolBarHelper::help($help_url, false, $exists_url);
        } else {
            JToolBarHelper::help('help.general', true); 
        }
    }  
    
    /**
     * Returns an array of the categories 
     *
     * @param   array   $config     An array of configuration options. By default, only
     *                              published and unpublished categories are returned.
     *
     * @return  array
     *
     */    
    public static function getCategoriesList($config = array('filter.published' => array(0, 1)))
    {
                $hash = md5('com_jdownloads' . '.categories.' . serialize($config));

        if (!isset(static::$rows[$hash]))
        {
            $config = (array) $config;
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('a.id, a.title, a.level');
            $query->from('#__jdownloads_categories AS a');
            $query->where('a.parent_id > 0');

            // Filter on the published state
            if (isset($config['filter.published']))
            {
                if (is_numeric($config['filter.published']))
                {
                    $query->where('a.published = ' . (int) $config['filter.published']);
                }
                elseif (is_array($config['filter.published']))
                {
                    ArrayHelper::toInteger($config['filter.published']);
                    $query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
                }
            }
            
            // Filter on the language
            if (isset($config['filter.language']))
            {
                if (is_string($config['filter.language']))
                {
                    $query->where('a.language = ' . $db->quote($config['filter.language']));
                }
                elseif (is_array($config['filter.language']))
                {
                    foreach ($config['filter.language'] as &$language)
                    {
                        $language = $db->quote($language);
                    }

                    $query->where('a.language IN (' . implode(',', $config['filter.language']) . ')');
                }
            }

            // Filter on the access
            if (isset($config['filter.access']))
            {
                if (is_string($config['filter.access']))
                {
                    $query->where('a.access = ' . $db->quote($config['filter.access']));
                }
                elseif (is_array($config['filter.access']))
                {
                    foreach ($config['filter.access'] as &$access)
                    {
                        $access = $db->quote($access);
                    }

                    $query->where('a.access IN (' . implode(',', $config['filter.access']) . ')');
                }
            }

            $query->order('a.lft');

            $db->setQuery($query);
            $rows = $db->loadObjectList();

            // Assemble the list options.
            static::$rows[$hash] = array();

            foreach ($rows as &$row)
            {
                $repeat = ($row->level - 1 >= 0) ? $row->level - 1 : 0;
                $row->title = str_repeat('- ', $repeat) . $row->title;
                self::$rows[$hash][] = JHtml::_('select.option', $row->id, $row->title);
            }
        }

        return static::$rows[$hash];              
    }
    
    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        return array(
            'a.lft' => JText::_('COM_JDOWNLOADS_ORDERING'),
            'a.published' => JText::_('COM_JDOWNLOADS_STATUS'),
            'a.title' => JText::_('COM_JDOWNLOADS_TITLE'),
            'a.access' => JText::_('COM_JDOWNLOADS_ACCESS'),
            'language' => JText::_('COM_JDOWNLOADS_LANGUAGE'),
            'a.id' => JText::_('COM_JDOWNLOADS_ID')
        );
    }    
    
}
?>