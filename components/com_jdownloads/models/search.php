<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 * 
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * @package jDownloads
 * @version 3.9  
 * Some parts from the search component (and search content plugin) adapted and modified to can use it in jDownloads as an internal search function. 
 */

defined('_JEXEC') or die;

/**
 * Search Model
 *
 */
class jdownloadsModelSearch extends JModelLegacy
{
	/**
	 * Sezrch data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Search total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Search areas
	 *
	 * @var integer
	 */
	var $_areas = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		//Get configuration
		$app	= JFactory::getApplication();
		$config = JFactory::getConfig();
        
        $quoted = false;
        
        // we use a session to store the required data for the pagination 
        $session = JFactory::getSession();

        // When '1' we do clean the prior stored session data  
        $reset = (int)$app->input->get('reset', '', 'string');
        
        if ( $reset == 1){
            $session->set('jd_searchword', '');
            $session->set('jd_searchphrase', '');
            $session->set('jd_ordering', '');
        } else {
            $old_searchword     = $session->get('jd_searchword');
            $old_searchphrase   = $session->get('jd_searchphrase');
            $old_ordering       = $session->get('jd_ordering');
        }
        
		// Get the pagination request variables
		$this->setState('limit', $app->getUserStateFromRequest('com_jdownloads.limit', 'limit', $config->get('list_limit'), 'uint'));
        $this->setState('limitstart', $app->input->get('limitstart', 0, 'uint'));

        // Set the search parameters
        $keyword  = urldecode($app->input->getString('searchword'));

        if ($keyword && !$reset){
            // slashes cause errors, <> get stripped anyway later on. # causes problems.
            $badchars = array('#', '>', '<', '\\');
            $searchword = trim(str_replace($badchars, '', $keyword));
            // if searchword enclosed in double quotes, strip quotes and do exact match
            if (substr($searchword, 0, 1) == '"' && substr($searchword, -1) == '"') {
                $keyword = substr($searchword, 1, -1);
                $quoted = true;
            } else {
                $keyword = $searchword;
            }
            $session->set('jd_searchword', $keyword);
        } else {
            if (isset($old_searchword) && $old_searchword != ''){
                $keyword = $old_searchword;
            } else {
                $keyword = '';
            }
        }

        if ($quoted){
            $searchphrase = 'exact';      
        } else {    
            $searchphrase	= $app->input->get('searchphrase', '', 'word');
        }    
        if ($searchphrase && !$reset){
            $session->set('jd_searchphrase', $searchphrase);
        } else {
            if (isset($old_searchphrase) && $old_searchphrase != ''){
                $searchphrase = $old_searchphrase;
            } else {
                $searchphrase = 'all';
            }
        }
		
        $ordering = $app->input->get('ordering', '', 'word');
		if ($ordering && !$reset){
            $session->set('jd_ordering', $ordering);
        } else {
            if (isset($old_ordering) && $old_ordering != ''){
                $ordering = $old_ordering;
            } else {
                $ordering = 'newest';
            }
        }
        
        $this->setSearch($keyword, $searchphrase, $ordering);
        
		//Set the search areas
		if (!$reset){
            $areas = $app->input->get('areas', null, 'array');
        } else {
            $areas = array();
        }
		$this->setAreas($areas);
	}

	/**
	 * Method to set the search parameters
	 *
	 * @access	public
	 * @param string search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 */
	function setSearch($keyword, $match = 'all', $ordering = 'newest')
	{
		if (isset($keyword)) {
			$this->setState('origkeyword', $keyword);
			if($match !== 'exact') {
				$keyword 		= preg_replace('#\xE3\x80\x80#s', ' ', $keyword);
			}
			$this->setState('keyword', $keyword);
		}

		if (isset($match)) {
			$this->setState('match', $match);
		}

		if (isset($ordering)) {
			$this->setState('ordering', $ordering);
		}
	}

	/**
	 * Method to set the search areas
	 *
	 * @access	public
	 * @param	array	Active areas
	 * @param	array	Search areas
	 */
	function setAreas($active = array(), $search = array())
	{
		$this->_areas['active'] = $active;
		$this->_areas['search'] = $search;
	}

	/**
	 * Method to get item data for the category
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets get the data if it doesn't already exist
		if (empty($this->_data))
		{
			$areas = $this->getAreas();

			$results = self::getSearchResults(
				$this->getState('keyword'),
				$this->getState('match'),
				$this->getState('ordering'),
				$areas['active']
			);

			$rows = array();
			
            $this->_total    = count($results);
            if ($this->getState('limit') > 0) {
                $this->_data    = array_splice($results, $this->getState('limitstart'), $this->getState('limit'));
            } else {
                $this->_data = $results;
            }            
		}
		return $this->_data;
	}

	/**
	 * Method to get the total number of download items for the category
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		return $this->_total;
	}

	/**
	 * Method to get a pagination object of the download items for the category
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Method to get the search areas
	 *
	 * @since 1.5
	 */
	function getAreas()
	{
        // Load the Category data
		if (empty($this->_areas['search']))
		{
			$areas = array();

            $areas['title']         = JText::_('COM_JDOWNLOADS_SEARCH_IN_TITLES');
			$areas['description']   = JText::_('COM_JDOWNLOADS_SEARCH_IN_DESCRIPTIONS');
            $areas['changelog']     = JText::_('COM_JDOWNLOADS_SEARCH_IN_CHANGELOG');
            $areas['author']        = JText::_('COM_JDOWNLOADS_SEARCH_IN_AUTHOR_NAME');
            $areas['metatags']       = JText::_('COM_JDOWNLOADS_SEARCH_IN_META_TAGS');
            $this->_areas['search'] = $areas;
		}
		return $this->_areas;
	}
    
    /**
     * Get the search result
     * The sql must return the following fields that are used in a common display
     * routine: href, title, section, created, text, browsernav
     * @param string Target search string
     * @param string mathcing option, exact|any|all
     * @param string ordering option, newest|oldest|popular|alpha|category
     * @param mixed An array if the search it to be restricted to areas, null if search all
     */    

      function getSearchResults($text, $phrase='', $ordering='', $areas=null) 
      {  
            $app      = JFactory::getApplication();
            $params   = $app->getParams();
          
            $db      = JFactory::getDbo();
        $serverType = $db->getServerType();
            $app     = JFactory::getApplication();
            $user    = JFactory::getUser();
            $groups  = implode(',', $user->getAuthorisedViewLevels());
            $tag     = JFactory::getLanguage()->getTag();

            require_once JPATH_SITE . '/components/com_jdownloads/helpers/route.php';
            require_once JPATH_SITE . '/components/com_jdownloads/helpers/search.php';
            require_once JPATH_SITE . '/components/com_jdownloads/helpers/jdownloads.php';
            
            $user_rules = JDHelper::getUserRules();
            
            $searchText = $text;

        $limit      = $this->state->get('search_limit', 100);

            $nullDate   = $db->getNullDate();
            $date = JFactory::getDate();
            $now = $date->toSql();

            $text = trim($text);
        if ($text === '') {
                return array();
            }

            $wheres = array();
            switch ($phrase) {
                case 'exact':
                    $text        = $db->Quote('%'.$db->escape($text, true).'%', false);
                    $wheres2    = array();
                    if (!$areas || in_array('title', $areas)){
                        $wheres2[]    = 'a.title LIKE '.$text;
                    }
                    if (!$areas || in_array('description', $areas)){
                        $wheres2[]    = 'a.description LIKE '.$text;
                        $wheres2[]    = 'a.description_long LIKE '.$text;
                    }
                    if (!$areas || in_array('changelog', $areas)){                    
                        $wheres2[]    = 'a.changelog LIKE '.$text;
                    }                        
                    if (!$areas || in_array('author', $areas)){
                        $wheres2[]    = 'a.author LIKE '.$text;
                    }    
                    if (!$areas || in_array('metatags', $areas)){
                        $wheres2[]    = 'a.metakey LIKE '.$text;
                        $wheres2[]    = 'a.metadesc LIKE '.$text;                        
                    }    
                
                $relevance[] = ' CASE WHEN ' . $wheres2[0] . ' THEN 5 ELSE 0 END ';
                
                    if (!$areas){
                    // Join over Fields when we shall search in all (7) fields. So no special field was selected to search only in this content.
                    $subQuery = $db->getQuery(true);
                    $subQuery->select("cfv.item_id")
                        ->from("#__fields_values AS cfv")
                        ->join('LEFT', '#__fields AS f ON f.id = cfv.field_id')
                        ->where('(f.context IS NULL OR f.context = ' . $db->q('com_jdownloads.download') . ')')
                        ->where('(f.state IS NULL OR f.state = 1)')
                        ->where('(f.access IS NULL OR f.access IN (' . $groups . '))')
                        ->where('cfv.value LIKE ' . $text);
                
                    // Filter by language.
                    if ($app->isClient('site') && JLanguageMultilang::isEnabled()){
                        $subQuery->where('(f.language IS NULL OR f.language in (' . $db->quote($tag) . ',' . $db->quote('*') . '))');
                    }

                    if ($serverType == "mysql"){
                        /* This generates a dependent sub-query so do no use in MySQL prior to version 6.0 !
                        * $wheres2[] = 'a.id IN( '. (string) $subQuery.')';
                        */

                        $db->setQuery($subQuery);
                        $fieldids = $db->loadColumn();

                        if (count($fieldids)){
                            $wheres2[] = 'a.id IN(' . implode(",", $fieldids) . ')';
                        }
                    } else {
                        $wheres2[] = $subQuery->castAsChar('a.id') . ' IN( ' . (string) $subQuery . ')';
                    }
                }                        
                    
                    $where        = '(' . implode(') OR (', $wheres2) . ')';
                    break;

                case 'all':
                case 'any':
                default:
                    $words = explode(' ', $text);
                    $wheres = array();
                $cfwhere = array();
                
                    foreach ($words as $word) {
                        $word        = $db->Quote('%'.$db->escape($word, true).'%', false);
                        $wheres2    = array();
                        if (!$areas || in_array('title', $areas)){
                            $wheres2[]    = 'a.title LIKE '.$word;
                        }    
                    if (!$areas || in_array('description', $areas)){
                        $wheres2[]    = 'a.description LIKE '.$word;
                        $wheres2[]    = 'a.description_long LIKE '.$word;
                    }
                    if (!$areas || in_array('changelog', $areas)){                    
                        $wheres2[]    = 'a.changelog LIKE '.$word;
                    }
                    if (!$areas || in_array('author', $areas)){
                        $wheres2[]    = 'a.author LIKE '.$word;
                    }    
                    if (!$areas || in_array('metatags', $areas)){
                        $wheres2[]    = 'a.metakey LIKE '.$word;
                        $wheres2[]    = 'a.metadesc LIKE '.$word;                        
                    }    
                    
                    $relevance[] = ' CASE WHEN ' . $wheres2[0] . ' THEN 5 ELSE 0 END ';
                    
                    if ($phrase === 'all'){
                        
                        if (!$areas){
                            // Join over Fields when we shall search in all possible fields. So no special field was selected to search only in this content.
                            $subQuery = $db->getQuery(true);
                            $subQuery->select("cfv.item_id")
                                ->from("#__fields_values AS cfv")
                                ->join('LEFT', '#__fields AS f ON f.id = cfv.field_id')
                                ->where('(f.context IS NULL OR f.context = ' . $db->q('com_jdownloads.download') . ')')
                                ->where('(f.state IS NULL OR f.state = 1)')
                                ->where('(f.access IS NULL OR f.access IN (' . $groups . '))')
                                ->where('LOWER(cfv.value) LIKE LOWER(' . $word . ')');
                        

                            // Filter by language.
                            if ($app->isClient('site') && JLanguageMultilang::isEnabled()){
                                $subQuery->where('(f.language IS NULL OR f.language in (' . $db->quote($tag) . ',' . $db->quote('*') . '))');
                            }

                            if ($serverType == "mysql"){
                                $db->setQuery($subQuery);
                                $fieldids = $db->loadColumn();

                                if (count($fieldids)){
                                    $wheres2[] = 'a.id IN(' . implode(",", $fieldids) . ')';
                                }
                            } else {
                                $wheres2[] = $subQuery->castAsChar('a.id') . ' IN( ' . (string) $subQuery . ')';
                            }
                        }
                    } else {
                        $cfwhere[] = 'LOWER(cfv.value) LIKE LOWER(' . $word . ')';
                    }
                    
                        $wheres[]    = implode(' OR ', $wheres2);
                    }
                
                if ($phrase === 'any'){
                    
                    if (!$areas){
                        // Join over Fields when we shall search in all possible fields. So no special field was selected to search only in this content.
                        $subQuery = $db->getQuery(true);
                        $subQuery->select("cfv.item_id")
                            ->from("#__fields_values AS cfv")
                            ->join('LEFT', '#__fields AS f ON f.id = cfv.field_id')
                            ->where('(f.context IS NULL OR f.context = ' . $db->q('com_jdownloads.download') . ')')
                            ->where('(f.state IS NULL OR f.state = 1)')
                            ->where('(f.access IS NULL OR f.access IN (' . $groups . '))')
                            ->where('(' . implode(($phrase === 'all' ? ') AND (' : ') OR ('), $cfwhere) . ')');

                        // Filter by language.
                        if ($app->isClient('site') && JLanguageMultilang::isEnabled()){
                            $subQuery->where('(f.language IS NULL OR f.language in (' . $db->quote($tag) . ',' . $db->quote('*') . '))');
                        }

                        if ($serverType == "mysql"){
                            $db->setQuery($subQuery);
                            $fieldids = $db->loadColumn();

                            if (count($fieldids)){
                                $wheres[] = 'a.id IN(' . implode(",", $fieldids) . ')';
                            }
                        } else {
                            $wheres[] = $subQuery->castAsChar('a.id') . ' IN( ' . (string) $subQuery . ')';
                        }
                    }
                }
                
                    $where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
                    break;
            }

            $morder = '';
            switch ($ordering) {
                case 'oldest':
                    $order = 'a.created ASC';
                    break;

                case 'popular':
                    $order = 'a.downloads DESC';
                    break;

                case 'alpha':
                    $order = 'a.title ASC';
                    break;

                case 'category':
                    $order = 'c.title ASC, a.title ASC';
                    $morder = 'a.title ASC';
                    break;

                case 'newest':
                default:
                    $order = 'a.created DESC';
                    break;
            }

            $rows = array();
            $query    = $db->getQuery(true);

            // search downloads
            if ($limit > 0)
            {
                $query->clear();
                //sqlsrv changes
                $case_when = ' CASE WHEN ';
                $case_when .= $query->charLength('a.alias');
                $case_when .= ' THEN ';
                $a_id = $query->castAsChar('a.id');
                $case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
                $case_when .= ' ELSE ';
                $case_when .= $a_id.' END as slug';

                $case_when1 = ' CASE WHEN ';
                $case_when1 .= $query->charLength('c.alias');
                $case_when1 .= ' THEN ';
                $c_id = $query->castAsChar('c.id');
                $case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
                $case_when1 .= ' ELSE ';
                $case_when1 .= $c_id.' END as catslug';
            
            if (!empty($relevance)){
                $query->select(implode(' + ', $relevance) . ' AS relevance');
                $order = ' relevance DESC, ' . $order;
            }

                $query->select('a.title AS title, a.metadesc, a.metakey, a.url_download, a.extern_file, a.other_file_id, a.license_agree, a.password, a.author, a.created AS created, a.language');
                $query->select($query->concatenate(array('a.description', 'a.description_long', 'a.changelog')).' AS text');
            $query->select('c.title AS section, ' . $case_when . ',' . $case_when1 . ', ' . '\'2\' AS browsernav');

                $query->from('#__jdownloads_files AS a');
                $query->innerJoin('#__jdownloads_categories AS c ON c.id = a.catid');
                $query->where('('. $where .')' . 'AND a.published = 1 AND c.published = 1 AND (a.access IN ('.$groups.') AND a.user_access = '.$db->quote(0).' OR a.user_access = '.$db->quote($user->id).') '
                            .'AND c.access IN ('.$groups.') '
                            .'AND (a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).') '
                            .'AND (a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).')' );
                $query->group('a.id, a.title, a.metadesc, a.metakey, a.author, a.created, a.description, a.description_long, a.changelog, c.title, a.alias, c.alias, c.id');
                $query->order($order);

                // Filter by language
            if ($app->isClient('site') && JLanguageMultilang::isEnabled()) {
                    $query->where('a.language in (' . $db->Quote($tag) . ',' . $db->Quote('*') . ')');
                    $query->where('c.language in (' . $db->Quote($tag) . ',' . $db->Quote('*') . ')');
                }

                $db->setQuery($query, 0, $limit);
            
            
            try {
                $list = $db->loadObjectList();
            }
            catch (RuntimeException $e){
                $list = array();
                JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            }
        
                $limit -= count($list);

            if (isset($list)){
                    foreach($list as $key => $item)
                    {
                        $direct_download = $params->get('direct_download');
                        if ((!$item->url_download && !$item->extern_file && !$item->other_file_id) || $item->password || $item->license_agree || $user_rules->view_captcha){
                            // this download is a simple document without a file so we can not use 'direct' download option
                            // or we need the summary page for password, captcha or license agree
                            $direct_download = 0;
                        }
                        
                        if ($params->get('view_detailsite')){
                            // we must link to the details page
                            $list[$key]->href = JDownloadsHelperRoute::getDownloadRoute($item->slug, $item->catslug, $item->language);
                        } else {
                            if ($direct_download){
                                // we must start the download process directly
                                $list[$key]->href = JRoute::_('index.php?option=com_jdownloads&amp;task=download.send&amp;id='.(int)$item->slug.'&amp;catid='.(int)$item->catslug.'&amp;m=0');                                
                            } else {
                                if (!$item->url_download && !$item->extern_file && !$item->other_file_id){
                                    // Download is only a simple document without a file so we must link to the details page
                                    $list[$key]->href = JDownloadsHelperRoute::getDownloadRoute($item->slug, $item->catslug, $item->language);
                                } else {
                                    // we must link to the summary page 
                                    $list[$key]->href = JRoute::_('index.php?option=com_jdownloads&amp;view=summary&amp;id='.$item->slug.'&amp;catid='.(int)$item->catslug);                                                                
                                }
                            }    
                        }                        
                    }
                }
                $rows[] = $list;
            }



            $results = array();
        
        if (count($rows)){
            foreach ($rows as $row){
                    $new_row = array();

                foreach ($row as $download){
                    // Not efficient to get these ONE Download at a TIME
                    // Lookup field values so they can be checked, GROUP_CONCAT would work in above queries, but isn't supported by non-MySQL DBs.
                    $query = $db->getQuery(true);
                    $query->select('fv.value')
                        ->from('#__fields_values as fv')
                        ->join('left', '#__fields as f on fv.field_id = f.id')
                        ->where('f.context = ' . $db->quote('com_jdownloads.download'))
                        ->where('fv.item_id = ' . $db->quote((int) $download->slug));
                    $db->setQuery($query);
                    $download->jcfields = implode(',', $db->loadColumn());

                    if (JDSearchHelper::checkNoHtml($download, $searchText, array('text', 'title', 'jcfields', 'metadesc', 'metakey'))){
                            $new_row[] = $download;
                        }
                    }
                    $results = array_merge($results, (array) $new_row);
                }
            }

            return $results;
        }    
}
