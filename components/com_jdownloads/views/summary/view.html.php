<?php
/**
 * @package jDownloads
 * @version 2.5  
 * @copyright (C) 2007 - 2013 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
 
defined('_JEXEC') or die('Restricted access');


/**
 * HTML View summary class for the jDownloads component
 */
class jdownloadsViewSummary extends JViewLegacy
{
	protected $items;
    protected $params;
	protected $state;
	protected $user;
    protected $user_rules;

	function display($tpl = null)
	{
        $app        = JFactory::getApplication();
        $params     = $app->getParams();
		$user	    = JFactory::getUser();
        
        $document = JFactory::getDocument();
        
        // get jD User group settings and limitations
        $this->user_rules = JDHelper::getUserRules();
        
        // Get the needed layout data - type = 3 for a 'summary' layout
        $this->layout = JDHelper::getLayout(3);
        
        // Add JavaScript Frameworks
        JHtml::_('bootstrap.framework');

        // Load optional RTL Bootstrap CSS
        if ($this->layout->uses_bootstrap){
            JHtml::_('bootstrap.loadCss', true, $this->document->direction);
        }

        // Load optional w3css framework
        if ($this->layout->uses_w3css){
            $w3_css_path = JPATH_ROOT.'/components/com_jdownloads/assets/css/w3.css';
            if (JFile::exists($w3_css_path)){
                $document->addStyleSheet( JURI::base()."components/com_jdownloads/assets/css/w3.css", 'text/css', null, array() );                
            }
        }

		$dispatcher	= JDispatcher::getInstance();

        $this->items    = $this->get('Items');
		$this->state	= $this->get('State');
		$this->user		= $user;

        // upload icon handling
        $this->view_upload_button = false;
        
        if ($this->user_rules->uploads_view_upload_icon){
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
        
        // add all needed cripts and css files
        
        $document->addScript(JURI::base().'components/com_jdownloads/assets/js/jdownloads.js');

        if ($params->get('view_ratings')){
            $document->addScript(JUri::base().'components/com_jdownloads/assets/rating/js/ajaxvote.js');
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
        
        // Create a shortcut for $item.
        $items = &$this->items;
        
        foreach ($items as $item){
        
		    // Add router helpers.
		    $item->slug			= $item->alias ? ($item->id.':'.$item->alias) : $item->id;
		    $item->catslug		= $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
		    $item->parent_slug	= $item->category_alias ? ($item->parent_id.':'.$item->parent_alias) : $item->parent_id;

		    // Merge article params. If this is single-article view, menu params override article params
		    // Otherwise, article params override menu item params
		    $this->params	= $this->state->get('params');
		    $active	= $app->getMenu()->getActive();
		    $temp	= clone ($this->params);

		    // Check to see which parameters should take priority
		    if ($active) {
			    $currentLink = $active->link;
			    // If the current view is the active item and an download view for this download, then the menu item params take priority
			    if (strpos($currentLink, 'view=download') && (strpos($currentLink, '&id='.(string) $item->id))) {
				    // $item->params are the downloads params, $temp are the menu item params
				    // Merge so that the menu item params take priority
				    $item->params->merge($temp);
				    // Load layout from active query (in case it is an alternative menu item)
				    if (isset($active->query['layout'])) {
					    $this->setLayout($active->query['layout']);
				    }
			    }
			    else {
				    // Current view is not a single article, so the article params take priority here
				    // Merge the menu item params with the article params so that the article params take priority
				    $temp->merge($item->params);
				    $item->params = $temp;

				    // Check for alternative layouts (since we are not in a single-article menu item)
				    // Single-article menu item layout takes priority over alt layout for an article
				    if ($layout = $item->params->get('download_layout')) {
					    $this->setLayout($layout);
				    }
			    }
		    }
		    else {
			    // Merge so that article params take priority
			    $temp->merge($item->params);
			    $item->params = $temp;
			    // Check for alternative layouts (since we are not in a single-download menu item)
			    // Single-download menu item layout takes priority over alt layout for an download
			    if ($layout = $item->params->get('download_layout')) {
				    $this->setLayout($layout);
			    }
		    }

		    // Check the view access to the download (the model has already computed the values).
		    if ($item->params->get('access-view') != true && ($item->params->get('show_noauth') != true &&  $user->get('guest') ) ) {
			    JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
                return;
		    }

		    // Escape strings for HTML output
		    $this->pageclass_sfx = htmlspecialchars($item->params->get('pageclass_sfx'));
        }
        
        // Process the content plugins.
        JPluginHelper::importPlugin('content');

        $this->event = new stdClass();

        // We should not display custom fields here. So we use not really the results - ToDo: find another way to solve this

        $results = $dispatcher->trigger('onContentAfterTitle', array('com_jdownloads.download', &$item, &$this->params, 0));
        $results = array(); // remove results
        $this->event->afterDisplayTitle = trim(implode("\n", $results));

        $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_jdownloads.download', &$item, &$this->params, 0));
        $results = array(); // remove results
        $this->event->beforeDisplayContent = trim(implode("\n", $results));

        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_jdownloads.download', &$item, &$this->params, 0));
        $results = array(); // remove results
        $this->event->afterDisplayContent = trim(implode("\n", $results));        
        
        $this->assignRef('items',  $items);
        
		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
        $app      = JFactory::getApplication();
        $params   = $app->getParams();
        
		$menus	= $app->getMenu();
		$pathway = $app->getPathway();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_JDOWNLOADS_DOWNLOADS'));
		}

		if (count($this->items) > 1){
            $title = JText::_('COM_JDOWNLOADS_FRONTEND_HEADER_SUMMARY_PAGE_TITLE'); 
        } else {
            $title = $this->params->get('page_title', '');
            $title .= ' - '.JText::_('COM_JDOWNLOADS_FRONTEND_HEADER_SUMMARY_PAGE_TITLE');
        }    

        if (isset($menu->query['catid'])){
            $id = (int) @$menu->query['catid'];  // the Download category has an own menu item
        } else {
            $id = 0;  // the Download category has not an own menu item 
        }
        
		if ($menu) {
            // we have a single download process - so we can add the link to this download in the breadcrumbs
            if ($this->items[0]->title && count($this->items) == 1) {
                $title = $this->items[0]->title;
                $title .= ' - '.JText::_('COM_JDOWNLOADS_FRONTEND_HEADER_SUMMARY_PAGE_TITLE');
            }
            $path = array(array('title' => JText::_('COM_JDOWNLOADS_FRONTEND_HEADER_SUMMARY_PAGE_TITLE'), 'link' => ''));
            
            if (count($this->items) == 1){
                $path[] = array('title' => $this->items[0]->title, 'link' => JdownloadsHelperRoute::getDownloadRoute($this->items[0]->slug, $this->items[0]->catid, $this->items[0]->language));
            }

            $category = JDCategories::getInstance('Download')->get($this->items[0]->catid);
            
            while ($category && ($menu->query['option'] != 'com_jdownloads' || ($id == 0 && $id != $category->id)) && $category->id != 'root'){
                $path[] = array('title' => $category->title, 'link' => JdownloadsHelperRoute::getCategoryRoute($category->id, true));
                $category = $category->getParent();
            }                   
            
        	$path = array_reverse($path);
			foreach($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		// Check for empty title and add site name if param is set
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title)) {
			$title = JText::_('COM_JDOWNLOADS_FRONTEND_HEADER_SUMMARY_PAGE_TITLE');
		}

		$this->document->setTitle($title);
        $this->document->setDescription($this->params->get('menu-meta_description'));

        // use at first settings from download - alternate from jD configuration
        if ($params->get('robots')){
            // use settings from jD-config
            $this->document->setMetadata('robots', $params->get('robots'));    
        } else {
            // is not defined in item or jd-config - so we use the global config setting
            $this->document->setMetadata( 'robots' , $app->getCfg('robots'));
        }

	}
}
