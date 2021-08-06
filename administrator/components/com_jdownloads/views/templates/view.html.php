<?php

defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

JHtml::_('jquery.framework');

class jdownloadsViewtemplates extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;

    public $filterForm;
    public $activeFilters;

    /**
	 * templates view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
        $this->state      = $this->get('State');
        $this->items        = $this->get('Items');
        $this->pagination   = $this->get('Pagination');
        
        // the filter form file must exist in the models/forms folder (e.g. filter_files.xml) 
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');        
        
        $params = $this->state->params;        

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }
   
        // get the template type from session to handle the correct output for every type.
        $session = JFactory::getSession();
        $type    = (int) $session->get( 'jd_tmpl_type', '' );
        $this->assignRef('jd_tmpl_type',        $type);
            
        // array with template typ name 
        $temp_type = array();
        $temp_type[1] = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP1');
        $temp_type[2] = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP2');
        $temp_type[3] = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP3');
        $temp_type[4] = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP4');
        $temp_type[5] = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP5');
        $temp_type[6] = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP6');
        $temp_type[7] = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP7');
        $temp_type[8] = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP8');
        $this->assignRef('temp_type_name',        $temp_type);    
        
        if ($params->get('use_lightbox_function')){
            $document = JFactory::getDocument();
            $document->addScript(JURI::base().'components/com_jdownloads/assets/lightbox/src/js/lightbox.js');
            $document->addStyleSheet( JURI::base()."components/com_jdownloads/assets/lightbox/src/css/lightbox.css", 'text/css', null, array() );
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
        
        $canDo    = JDownloadsHelper::getActions();
        $user     = JFactory::getUser();

        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');
        
        if ($this->jd_tmpl_type != ''){
            $type = 'type'.(int)$this->jd_tmpl_type;
        } else {
            $type = 'cssedit';
            
        }
        JDownloadsHelper::addTemplateSubmenu($type);
        
        // set the correct text in title for every layout type
        switch ($this->jd_tmpl_type) {
            case '1':  $layout_type = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP1'); break;
            case '2':  $layout_type = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP2'); break;
            case '3':  $layout_type = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP3'); break;
            case '4':  $layout_type = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP4'); break;
            case '5':  $layout_type = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP5'); break;
            case '6':  $layout_type = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP6'); break;
            case '7':  $layout_type = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP7'); break;
            case '8':  $layout_type = JText::_('COM_JDOWNLOADS_BACKEND_TEMP_TYP8'); break;
            default: $layout_type = ''; 
        }
        
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_BACKEND_CPANEL_TEMPLATES_NAME').': '.$layout_type, 'brush jdlayouts'); 
        
        JToolBarHelper::link('index.php?option=com_jdownloads', JText::_('COM_JDOWNLOADS_CPANEL'), 'home-2 cpanel');
        
        JToolBarHelper::custom( 'templates.cancel', 'list.png', 'list.png', JText::_('COM_JDOWNLOADS_LAYOUTS'), false, false );
        JToolBarHelper::divider();
        
        if ($canDo->get('core.create')) {
            JToolBarHelper::addNew('template.add');
        }
        if ($canDo->get('core.edit')) {
            JToolBarHelper::editList('template.edit');
        }
        if ($canDo->get('core.delete')) {
            JToolBarHelper::deleteList(JText::_('COM_JDOWNLOADS_DELETE_LIST_ITEM_CONFIRMATION'), 'templates.delete', 'COM_JDOWNLOADS_TOOLBAR_REMOVE');
        }            
        if ($canDo->get('core.edit')) {
            JToolBarHelper::divider();
            JToolBarHelper::custom( 'templates.activate', 'publish', 'publish', JText::_('COM_JDOWNLOADS_BACKEND_TEMPLIST_MENU_TEXT_ACTIVE'), true, false );            
            JToolBarHelper::checkin('templates.checkin');
        }
        if ($canDo->get('core.edit')) {
            JToolBarHelper::custom( 'layouts.install', 'upload.png', 'upload.png', JText::_('COM_JDOWNLOADS_LAYOUTS_IMPORT_LABEL'), false, false); 
            JToolBarHelper::custom( 'templates.export', 'download.png', 'download.png', JText::_('COM_JDOWNLOADS_LAYOUTS_EXPORT_LABEL'), true, false); 
        }        
        
        if ($canDo->get('core.admin')){
            JToolBarHelper::preferences('com_jdownloads');
        }
        
        JToolBarHelper::divider();
        
        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '138&tmpl=jdhelp';
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
            'a.template_name'   => JText::_('COM_JDOWNLOADS_TITLE'),
            'a.locked'          => JText::_('COM_JDOWNLOADS_FILTER_LOGS_TYPE'),
            'a.template_active' => JText::_('COM_JDOWNLOADS_LOGS_COL_IP_LABEL'),
            'a.id'              => JText::_('COM_JDOWNLOADS_ID')
        );
    }         
}