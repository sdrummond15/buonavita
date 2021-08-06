<?php
/**
 * @package jDownloads
 * @version 3.9  
 * @copyright (C) 2007 - 2019 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined('_JEXEC') or die;

jimport('joomla.application.categories');

class JdownloadsRouter extends JComponentRouterView
{
    protected $noIDs = false;
    protected $sef_advanced = false;

    /**
     * jDownloads Component router constructor
     *
     * @param   JApplicationCms  $app   The application object
     * @param   JMenu            $menu  The menu object to work with
     */
    public function __construct($app = null, $menu = null)
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        $this->noIDs = (bool) $params->get('sef_ids');
        $this->sef_advanced = (bool) $params->get('sef_advanced');
        
        // Register here every possible view type
        
        $categories = new JComponentRouterViewconfiguration('categories');
        $categories->setKey('id');
        $this->registerView($categories);
        
        $category = new JComponentRouterViewconfiguration('category');
        $category->setKey('id')->setParent($categories, 'catid')->setNestable();          
        $this->registerView($category);
        
        $download = new JComponentRouterViewconfiguration('download');
        $download->setKey('id')->setParent($category, 'catid');
        $this->registerView($download);

        $downloads = new JComponentRouterViewconfiguration('downloads');
        $downloads->setKey('id')->setParent($category, 'catid');
        $this->registerView($downloads);

        $this->registerView(new JComponentRouterViewconfiguration('search'));
        
        $this->registerView(new JComponentRouterViewconfiguration('summary'));
        
        $this->registerView(new JComponentRouterViewconfiguration('report'));
        
        $this->registerView(new JComponentRouterViewconfiguration('survey'));
        
        $form = new JComponentRouterViewconfiguration('form');
        $form->setKey('a_id');
        $this->registerView($form);

        parent::__construct($app, $menu);

        $this->attachRule(new JComponentRouterRulesMenu($this));

        if ($params->get('sef_advanced', 0))
        {
            $this->attachRule(new JComponentRouterRulesStandard($this));
            $this->attachRule(new JComponentRouterRulesNomenu($this));
        }
        else
        {
            JLoader::register('JdownloadsRouterRulesLegacy', __DIR__ . '/helpers/legacyrouter.php');
            $this->attachRule(new JdownloadsRouterRulesLegacy($this));
        }
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $id     ID of the category to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getCategorySegment($id, $query)
    {
        if ($this->sef_advanced){
            
	        JLoader::register('JDCategories', __DIR__ . '/helpers/categories.php');
	        $category = JDCategories::getInstance($this->getName())->get($id);
	
	        if ($category){
	            $path = array_reverse($category->getPath(), true);
	            $path[0] = '1:root';
	
	            if ($this->noIDs){
	                
                    foreach ($path as &$segment){
	                    list($id, $segment) = explode(':', $segment, 2);
	                }
	            }

            	return $path;
            }
        }

        return array();
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $id     ID of the category to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getCategoriesSegment($id, $query)
    {
        return $this->getCategorySegment($id, $query);
    }

    /**
     * Method to get the segment(s) for an download
     *
     * @param   string  $id     ID of the download to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getDownloadSegment($id, $query)
    {
        if (!strpos($id, ':'))
        {
            $db = JFactory::getDbo();
            $dbquery = $db->getQuery(true);
            $dbquery->select($dbquery->qn('alias'))
                ->from($dbquery->qn('#__jdownloads_files'))
                ->where('id = ' . $dbquery->q($id));
            $db->setQuery($dbquery);

            $id .= ':' . $db->loadResult();
        }

        if ($this->noIDs)
        {
            list($void, $segment) = explode(':', $id, 2);

            return array($void => $segment);
        }

        return array((int) $id => $id);
    }

    /**
     * Method to get the segment(s) for a form
     *
     * @param   string  $id     ID of the download form to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     *
     * @since   3.7.3
     */
    public function getFormSegment($id, $query)
    {
        return $this->getDownloadSegment($id, $query);
    }

    /**
     * Method to get the id for a category
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCategoryId($segment, $query)
    {
        if (isset($query['id']))
        {
            JLoader::register('JDCategories', __DIR__ . '/helpers/categories.php');
            $category = JDCategories::getInstance($this->getName(), array('access' => false))->get($query['id']);

            if ($category)
            {
                foreach ($category->getChildren() as $child)
                {
                    if ($this->noIDs)
                    {
                        if ($child->alias == $segment)
                        {
                            return $child->id;
                        }
                    }
                    else
                    {
                        if ($child->id == (int) $segment)
                        {
                            return $child->id;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Method to get the segment(s) for a category
     *
     * @param   string  $segment  Segment to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getCategoriesId($segment, $query)
    {
        return $this->getCategoryId($segment, $query);
    }

    /**
     * Method to get the segment(s) for an download
     *
     * @param   string  $segment  Segment of the download to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getDownloadId($segment, $query)
    {
        if ($this->noIDs)
        {
            $db = JFactory::getDbo();
            $dbquery = $db->getQuery(true);
            $dbquery->select($dbquery->qn('id'))
                ->from($dbquery->qn('#__jdownloads_files'))
                ->where('alias = ' . $dbquery->q($segment))
                ->where('catid = ' . $dbquery->q($query['id']));
            $db->setQuery($dbquery);

            return (int) $db->loadResult();
        }

        return (int) $segment;
    }
}

/**
 * jDownloads router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function JDownloadsBuildRoute(&$query)
{
    $app = JFactory::getApplication();
    $router = new ContentRouter($app, $app->getMenu());

    return $router->build($query);
}

/**
 * Parse the segments of a URL.
 *
 * This function is a proxy for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @since   3.3
 * @deprecated  4.0  Use Class based routers instead
 */
function JDownloadsParseRoute($segments)
{
    $app = JFactory::getApplication();
    $router = new ContentRouter($app, $app->getMenu());

    return $router->parse($segments);
}

 
    
    
    


?>