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

defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

jimport( 'joomla.application.component.view' ); 

JHtml::_('jquery.framework'); 

/**
 * View downloads list
  * @package    jDownloads
 */
class jdownloadsViewDownloads extends JViewLegacy
{
    /**
     * The item authors
     *
     * @var  stdClass
     */
    protected $authors;

    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var  JPagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  object
     */
    protected $state;

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
     * The sidebar markup
     *
     * @var  string
     */
    protected $sidebar;
        
    protected static $rows = array();
    
    
    /**
	 * Downloads list view method
     * 
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise a Error object.
	 **/
	public function display($tpl = null)
	{
        // Load the backend helper
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';
        
        $app = JFactory::getApplication();
        $doc = JFactory::getDocument();

        if ($this->getLayout() !== 'modal' && $this->getLayout() !== 'modallist')
        {
            JDownloadsHelper::addSubmenu('downloads');
            $app->setUserState( 'jd_modal', false );
        } else {
            // set a switch so we can build later a valid: db query
            $app->setUserState( 'jd_modal', true );
        }
        
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->authors       = $this->get('Authors');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        
        $params = $this->state->params;
        
        // build categories list box 
        $lists = array();
        $config = array('filter.published' => array(0, 1));
        $select[] = JHtml::_('select.option', 0, JText::_('JLIB_HTML_BATCH_NO_CATEGORY'));
        
        // get the categories data
        $categories = $this->getCategoriesList($config);
        $this->categories = @array_merge($select, $categories);        
        
        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors), 500);
        }
        
        if ($params->get('use_lightbox_function')){
            $doc->addScript(JURI::base().'components/com_jdownloads/assets/lightbox/src/js/lightbox.js');
            $doc->addStyleSheet( JURI::base()."components/com_jdownloads/assets/lightbox/src/css/lightbox.css", 'text/css', null, array() );
        }
        
        // We need icomoon font
        $doc->addStyleSheet($this->baseurl.'/media/jui/css/icomoon.css');
   
        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal' && $this->getLayout() !== 'modallist') {        
            $this->addToolbar();
            $this->sidebar = JHtmlSidebar::render();
        } else {
            // Added to support the Joomla Language Associations
            // In download associations modal we need to remove language filter if forcing a language.
            // We also need to change the category filter to show categories with All or the forced language.
            if ($forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
            {
                // If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
                $languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
                $this->filterForm->setField($languageXml, 'filter', true);

                // Also, unset the active language filter so the search tools is not open by default with this filter.
                unset($this->activeFilters['language']);

                // One last changes needed is to change the category filter to just show categories with All language or with the forced language.
                $this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
            }
        }    
        return parent::display($tpl);
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
        
        // Get the toolbar object instance
        $bar = JToolBar::getInstance('toolbar');
        
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_DOWNLOADS'), 'stack jddownloads');
        
        JToolBarHelper::link('index.php?option=com_jdownloads', JText::_('COM_JDOWNLOADS_CPANEL'), 'home-2 cpanel');
        
        if ($canDo->get('core.create')) {
            JToolBarHelper::addNew('download.add');
        }
        if ($canDo->get('core.edit')) {
            JToolBarHelper::editList('download.edit');
        }

        if ($canDo->get('core.edit.state')) {
            JToolBarHelper::divider();
            JToolBarHelper::publish('downloads.publish', 'COM_JDOWNLOADS_PUBLISH', true);
            JToolBarHelper::unpublish('downloads.unpublish', 'COM_JDOWNLOADS_UNPUBLISH', true);
            JToolbarHelper::custom('downloads.featured', 'featured.png', 'featured_f2.png', 'COM_JDOWNLOADS_FEATURE', true);
            JToolbarHelper::custom('downloads.unfeatured', 'unfeatured.png', 'unfeatured_f2.png', 'COM_JDOWNLOADS_UNFEATURE', true);            
            JToolBarHelper::divider();
            JToolBarHelper::checkin('downloads.checkin');            
        }
        if ($canDo->get('core.delete')) {
            JToolBarHelper::deleteList(JText::_('COM_JDOWNLOADS_DELETE_LIST_ITEM_CONFIRMATION'), 'downloads.delete', 'COM_JDOWNLOADS_TOOLBAR_REMOVE');
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

        JToolBarHelper::divider();
        
        if ($canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_jdownloads');
            JToolBarHelper::divider();
        } 
        
        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '135&tmpl=jdhelp';
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
     *
     */
    protected function getSortFields()
    {
        return array(
            'a.ordering'     => JText::_('COM_JDOWNLOADS_ORDERING'),
            'a.published'    => JText::_('COM_JDOWNLOADS_STATUS'),
            'a.title'        => JText::_('COM_JDOWNLOADS_TITLE'),
            'category_title' => JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_CAT'),
            'access_level'   => JText::_('COM_JDOWNLOADS_ACCESS'),
            'a.created_by'   => JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_AUTHOR'),
            'language'       => JText::_('COM_JDOWNLOADS_LANGUAGE'),
            'a.created'      => JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_DADDED'),
            'a.id'           => JText::_('COM_JDOWNLOADS_ID'),
            'a.featured'     => JText::_('COM_JDOWNLOADS_FEATURED')
        );
    }    
}
