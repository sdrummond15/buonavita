<?php
/**
 * @package jDownloads
 * @version 3.7  
 * @copyright (C) 2007 - 2017 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
 
defined('_JEXEC') or die('Restricted access');

/**
 * jownloads categories view.
 *
 */
class jdownloadsViewCategories extends JViewLegacy
{
	protected $state = null;
	protected $item = null;
	protected $items = null;
	protected $pagination = null;

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null) 
	{
        $document = JFactory::getDocument();
        
        $app   = JFactory::getApplication();
        $user  = JFactory::getUser();
        $model = $this->getModel();
        
        $jd_user_settings = JDHelper::getUserRules();
        
        $layout = JDHelper::getLayout(1);
        
        // Add JavaScript Frameworks
        JHtml::_('bootstrap.framework');

        // Load optional RTL Bootstrap CSS
        if ($layout->uses_bootstrap){
            JHtml::_('bootstrap.loadCss', true, $this->document->direction);
        }

        // Load optional w3css framework
        if ($layout->uses_w3css){
            $w3_css_path = JPATH_ROOT.'/components/com_jdownloads/assets/css/w3.css';
            if (JFile::exists($w3_css_path)){
                $document->addStyleSheet( JURI::base()."components/com_jdownloads/assets/css/w3.css", 'text/css', null, array() );                
            }
        }
        
        // Get some data from the models
		$state		= $this->get('State');
        $params     = $state->params;
		$items		= $this->get('Items');
        
        jimport('joomla.html.pagination');
        $pagination = new JPagination($model->getTotal(),$model->getState('list.start'), $model->getState('list.limit'));        
       
		$parent		= $this->get('Parent');
        
        // upload icon handling
        $this->view_upload_button = false;
        
        if ($jd_user_settings->uploads_view_upload_icon){
            // we must here check whether the user has the permissions to create new downloads 
            // this can be defined in the components permissions but also in any category
            // but the upload icon is only viewed when in the user groups settings is also activated the: 'display add/upload icon' option
                
            // 1. check the component permissions
            if (!$user->authorise('core.create', 'com_jdownloads')){
                // 2. not global permissions so we must check now every category (for a lot of categories can this be very slow)
                $this->authorised_cats = JDHelper::getAuthorisedJDCategories('core.create', $user);
                if (count($this->authorised_cats) > 0){
                    $this->view_upload_button = true;
                }
            } else {
                $this->view_upload_button = true;
            }        
        }
                        
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

        if ($items == false){
           // return JError::raiseError(404, JText::_('COM_JDOWNLOADS_CATEGORY_NOT_FOUND'));
        }

        if ($parent == false){
            JError::raiseError(404, JText::_('COM_JDOWNLOADS_CATEGORY_PARENT_NOT_FOUND'));
            return false;
        }          
        
        // Get the tags
        foreach ($items as $item){
            $item->tags = new JHelperTags;
            $item->tags->getItemTags('com_jdownloads.category', $item->id);
        } 
        
        // add all other needed scripts and css files
        
        $document->addScript(JURI::base().'components/com_jdownloads/assets/js/jdownloads.js');
        
        if ($params->get('view_ratings')){
            $document->addScript(JURI::base().'components/com_jdownloads/assets/rating/js/ajaxvote.js');
        }
        
        if ($params->get('use_lightbox_function')){
            $document->addScript(JURI::base().'components/com_jdownloads/assets/lightbox/src/js/lightbox.js');
            $document->addStyleSheet( JURI::base()."components/com_jdownloads/assets/lightbox/src/css/lightbox.css", 'text/css', null, array() );
        }
            
        $document->addScriptDeclaration('var live_site = "'.JURI::base().'";');
        $document->addScriptDeclaration('function openWindow (url) {
                fenster = window.open(url, "_blank", "width=550, height=480, STATUS=YES, DIRECTORIES=NO, MENUBAR=NO, SCROLLBARS=YES, RESIZABLE=NO");
                fenster.focus();
                }');

        $document->addStyleSheet( JURI::base()."components/com_jdownloads/assets/css/jdownloads_buttons.css", "text/css", null, array() ); 
        
        if ($params->get('load_frontend_css')){
            $document->addStyleSheet( JURI::base()."components/com_jdownloads/assets/css/jdownloads_fe.css", "text/css", null, array() );
			$currentLanguage = JFactory::getLanguage();
            $isRTL = $currentLanguage->get('rtl');
            if ($isRTL) {
                $document->addStyleSheet( JURI::base()."components/com_jdownloads/assets/css/jdownloads_fe_rtl.css", "text/css", null, array() );
			}
        } else {
            if ($params->get('own_css_file')){
                $own_css_path = JPATH_ROOT.'/components/com_jdownloads/assets/css/'.$params->get('own_css_file');
                if (JFile::exists($own_css_path)){
                    $document->addStyleSheet( JURI::base()."components/com_jdownloads/assets/css/".$params->get('own_css_file'), "text/css", null, array() );
                }
            }
        }    
        
        if ($params->get('view_ratings')){
            $document->addStyleSheet( JURI::base()."components/com_jdownloads/assets/rating/css/ajaxvote.css", "text/css", null, array() );
        }
        
        $custom_css_path = JPATH_ROOT.'/components/com_jdownloads/assets/css/jdownloads_custom.css';
        if (JFile::exists($custom_css_path)){
            $document->addStyleSheet( JURI::base()."components/com_jdownloads/assets/css/jdownloads_custom.css", 'text/css', null, array() );                
        }          
        
        $this->jd_image_path = JPATH_ROOT  . '/images/jdownloads';        


		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->maxLevelcat = $params->get('maxLevelcat', -1);
        $this->assignRef('state',       $state);
        $this->assignRef('params',		$params);
		$this->assignRef('parent',		$parent);
		$this->assignRef('items',		$items);
        $this->assignRef('pagination',  $pagination);
        $this->assignRef('layout',           $layout);
        $this->assignRef('jd_user_settings', $jd_user_settings);
        
		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$title	= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if($menu)
		{
			$this->params->def('page_heading', $this->params->def('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('COM_JDOWNLOADS_DOWNLOADS'));
		}
        
		$title = $this->params->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
