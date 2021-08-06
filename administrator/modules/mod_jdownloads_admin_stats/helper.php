<?php
/**
* @version $Id: mod_jdownloads_admin_stats.php v3.8
* @package mod_jdownloads_admin_stats
* @copyright (C) 2018 Arno Betz
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author Arno Betz http://www.jDownloads.com
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jdownloads/models', 'jDownloadsModel');

class modJDownloadsAdminStatsHelper
{
    public static function getLatestItems($params)
    {
        $user = JFactory::getuser();

        // Get an instance of the generic articles model
        $model = JModelLegacy::getInstance('Downloads', 'jdownloadsModel', array('ignore_request' => true));

        // Set List SELECT
        $model->setState('list.select', 'a.id, a.title, a.catid, a.checked_out, a.checked_out_time, ' .
            ' a.access, a.user_access, a.created, a.created_by, a.modified_by, a.featured, a.published, a.publish_up, a.publish_down');

        $model->setState('list.ordering', 'a.created');
        $model->setState('list.direction', 'DESC');
        
        // Set the Start and Limit
        $model->setState('list.start', 0);
        $model->setState('list.limit', $params->get('amount_items', 5));

        $items = $model->getItems();

        if ($error = $model->getError()){
            JError::raiseError(500, $error);

            return false;
        }

        // Set the links
        foreach ($items as &$item){
            if ($user->authorise('core.edit', 'com_jdownloads.download.' . $item->id)){
                $item->link = JRoute::_('index.php?option=com_jdownloads&task=download.edit&id=' . $item->id);
            } else {
                $item->link = '';
            }
            if ($user->authorise('core.edit', 'com_jdownloads.category.' . $item->catid)){
                $item->catlink = JRoute::_('index.php?option=com_jdownloads&task=category.edit&id=' . $item->catid);
            } else {
                $item->catlink = '';
            }
        }        
        
        return $items;
    }

    public static function getPopularItems($params)
    {
        $user = JFactory::getuser();

        // Get an instance of the generic articles model
        $model = JModelLegacy::getInstance('Downloads', 'jdownloadsModel', array('ignore_request' => true));

        // Set List SELECT
        $model->setState('list.select', 'a.id, a.title, a.catid, a.downloads AS hits, a.views, a.checked_out, a.checked_out_time, ' .
            ' a.access, a.user_access, a.created, a.created_by, a.modified_by, a.featured, a.published, a.publish_up, a.publish_down');

        $model->setState('list.ordering', 'a.downloads');
        $model->setState('list.direction', 'DESC');
        
        // Set the Start and Limit
        $model->setState('list.start', 0);
        $model->setState('list.limit', $params->get('amount_items', 5));

        $items = $model->getItems();

        if ($error = $model->getError()){
            JError::raiseError(500, $error);

            return false;
        }

        // Set the links
        foreach ($items as &$item){
            if ($user->authorise('core.edit', 'com_jdownloads.download.' . $item->id)){
                $item->link = JRoute::_('index.php?option=com_jdownloads&task=download.edit&id=' . $item->id);
            } else {
                $item->link = '';
            }
            if ($user->authorise('core.edit', 'com_jdownloads.category.' . $item->catid)){
                $item->catlink = JRoute::_('index.php?option=com_jdownloads&task=category.edit&id=' . $item->catid);
            } else {
                $item->catlink = '';
            }
        }        
        
        return $items;
    }

    public static function getFeaturedItems($params)
    {
        $user = JFactory::getuser();

        // Get an instance of the generic articles model
        $model = JModelLegacy::getInstance('Downloads', 'jdownloadsModel', array('ignore_request' => true));

        // Set List SELECT
        $model->setState('list.select', 'a.id, a.title, a.catid, a.checked_out, a.checked_out_time, ' .
            ' a.access, a.user_access, a.created, a.created_by, a.modified_by, a.featured, a.published, a.publish_up, a.publish_down');

        // Select only where featured field is set to '1'
        $model->setState('filter.featured', '1');           
        
        $model->setState('list.ordering', 'a.created');
        $model->setState('list.direction', 'DESC');
        
        // Set the Start and Limit
        $model->setState('list.start', 0);
        $model->setState('list.limit', $params->get('amount_items', 5));

        $items = $model->getItems();

        if ($error = $model->getError()){
            JError::raiseError(500, $error);

            return false;
        }

        // Set the links
        foreach ($items as &$item){
            if ($user->authorise('core.edit', 'com_jdownloads.download.' . $item->id)){
                $item->link = JRoute::_('index.php?option=com_jdownloads&task=download.edit&id=' . $item->id);
            } else {
                $item->link = '';
            }
            if ($user->authorise('core.edit', 'com_jdownloads.category.' . $item->catid)){
                $item->catlink = JRoute::_('index.php?option=com_jdownloads&task=category.edit&id=' . $item->catid);
            } else {
                $item->catlink = '';
            }
        }        
        
        return $items;
    }    
    
    public static function getMostRatedItems($params)
    {

        // Access filter
        $user = JFactory::getuser();
        $groups = implode(',', array_unique($user->getAuthorisedViewLevels()));
        
        $db = JFactory::getDBO();
        $query = "SELECT i.*, c.title AS category_title, v.name AS author, r.file_id, r.rating_count, round(( r.rating_sum / r.rating_count ) * 20) AS ratenum FROM #__jdownloads_files as i
        LEFT JOIN #__jdownloads_categories AS c ON c.id = i.catid
        INNER JOIN #__jdownloads_ratings AS r ON i.id = r.file_id 
        LEFT JOIN #__users AS v ON v.id = i.created_by
        WHERE i.access IN ('$groups') AND c.access IN ('$groups')
        ORDER BY rating_count DESC, ratenum DESC";
        $db->setQuery($query, 0, $params->get('amount_items', 5));
        $items = $db->loadObjectList();
        
        // Set the links
        foreach ($items as &$item){
            if ($user->authorise('core.edit', 'com_jdownloads.download.' . $item->id)){
                $item->link = JRoute::_('index.php?option=com_jdownloads&task=download.edit&id=' . $item->id);
            } else {
                $item->link = '';
            }
            if ($user->authorise('core.edit', 'com_jdownloads.category.' . $item->catid)){
                $item->catlink = JRoute::_('index.php?option=com_jdownloads&task=category.edit&id=' . $item->catid);
            } else {
                $item->catlink = '';
            }
        }  
        return $items;
    }

    public static function getTopRatedItems($params)
    {

        // Access filter
        $user = JFactory::getuser();
        $groups = implode(',', array_unique($user->getAuthorisedViewLevels()));
        
        $db = JFactory::getDBO();
        $query = "SELECT i.*, c.title AS category_title, v.name AS author, r.file_id, r.rating_count, round(( r.rating_sum / r.rating_count ) * 20) AS ratenum FROM #__jdownloads_files as i
        LEFT JOIN #__jdownloads_categories AS c ON c.id = i.catid
        INNER JOIN #__jdownloads_ratings AS r ON i.id = r.file_id 
        LEFT JOIN #__users AS v ON v.id = i.created_by
        WHERE i.access IN ('$groups') AND c.access IN ('$groups') 
        ORDER BY ratenum DESC , rating_count DESC";
        $db->setQuery($query, 0, $params->get('amount_items', 5));
        $items = $db->loadObjectList();
        
        // Set the links
        foreach ($items as &$item){
            if ($user->authorise('core.edit', 'com_jdownloads.download.' . $item->id)){
                $item->link = JRoute::_('index.php?option=com_jdownloads&task=download.edit&id=' . $item->id);
            } else {
                $item->link = '';
            }
            if ($user->authorise('core.edit', 'com_jdownloads.category.' . $item->catid)){
                $item->catlink = JRoute::_('index.php?option=com_jdownloads&task=category.edit&id=' . $item->catid);
            } else {
                $item->catlink = '';
            }
        }  
        return $items;
    }

    public static function getMonitoringLog()
    {
        if (!JFactory::getUser()->authorise('core.admin', 'com_jdownloads')) return '';
        
        // get log file
        if (JFile::exists(JPATH_COMPONENT_ADMINISTRATOR.'/monitoring_logs.txt')){
            $log_file = file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.'/monitoring_logs.txt');
        } else {
            $log_file = '';
        }
        return $log_file;
    }

    public static function getRestoreLog()
    {
        if (!JFactory::getUser()->authorise('core.admin', 'com_jdownloads')) return '';
        
        // get restore log file
        if (JFile::exists(JPATH_COMPONENT_ADMINISTRATOR.'/restore_logs.txt')){
            $restore_log_file = file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.'/restore_logs.txt');
        } else {
            $restore_log_file = '';
        } 
        return $restore_log_file;
    }

    public static function getInstallLog()
    {
        if (!JFactory::getUser()->authorise('core.admin', 'com_jdownloads')) return '';   
        
        // get installation log file
        $log_file = JFactory::getConfig()->get('log_path').'/com_jdownloads_install_logs.php';
        if (JFile::exists($log_file)){
            $install_log_file = file_get_contents($log_file);
            $install_log_file = nl2br($install_log_file);
            $install_log_file = str_replace('#<br />', '', $install_log_file);
            $install_log_file = str_replace("#<?php die('Forbidden.'); ?><br />", '', $install_log_file);    
        } else {
            $install_log_file = '';
        }
        return $install_log_file; 
    }
    
    public static function getStatistics()
    {
        $statistics = new stdClass;
        
        $downloads = self::countDownloads();
        $statistics->num_total_downloads        = (int)$downloads->total;
        $statistics->num_published_downloads    = (int)$downloads->published;
        $statistics->num_unpublished_downloads  = (int)$downloads->unpublished;
        $statistics->num_featured               = (int)$downloads->featured;
        $statistics->sum_downloaded             = (int)$downloads->downloaded;
        
        $categories = self::countCategories();
        $statistics->num_total_categories       = (int)$categories->total;
        $statistics->num_published_categories   = (int)$categories->published;
        $statistics->num_unpublished_categories = (int)$categories->unpublished;
        
        $statistics->category_tags              = self::getCategoryTags();
        $statistics->download_tags              = self::getDownloadTags();
        
        return $statistics;
    }

    public static function countDownloads()
    {
        $db = JFactory::getDBO();
        $query = "SELECT COUNT(*) AS total,
                         COUNT(NULLIF(published, '1')) AS unpublished,
                         COUNT(NULLIF(published, '0')) AS published,
                         COUNT(NULLIF(featured, '0'))  AS featured,
                         SUM(downloads)                AS downloaded 
                         FROM #__jdownloads_files";
        $db->setQuery($query);
        $result = $db->loadObject();
        return $result;
    }


    public static function countCategories()
    {
        $db = JFactory::getDBO();
        $query = "SELECT COUNT(*) AS total,
                         COUNT(NULLIF(published, '1')) AS unpublished,
                         COUNT(NULLIF(published, '0')) AS published
                         FROM #__jdownloads_categories 
                         WHERE level > 0";
        $db->setQuery($query);
        $result = $db->loadObject();
        return $result;
    }    
    
    public static function getCategoryTags()
    {
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_tags/models', 'TagsModel');
        
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();

        // Get an instance of the generic downloads model
        $model = JModelLegacy::getInstance ('Tags', 'TagsModel', array('ignore_request' => true));

        // Set application parameters in model
        $appParams = JComponentHelper::getParams('com_tags');
        $model->setState('params', $appParams);
       
        // Set the filters based on the module params
        $model->setState('list.start', 0);
        //$model->setState('list.limit', (int) $params->get('sum_view', 5));
        $model->setState('filter.published', 1);

        // Access filter
        $access = !JComponentHelper::getParams('com_jdownloads')->get('show_noauth');
        $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
        $model->setState('filter.access', $access);

        // User filter
        $userId = JFactory::getUser()->get('id');

        // Filter by language
        $model->setState('filter.language', '*');

        // Set sort ordering
        $ordering = 'a.title';
        $dir = 'ASC';

        $model->setState('list.ordering', $ordering);
        $model->setState('list.direction', $dir);

        $items = $model->getItems();

        if (count($items)){
            $items = JDownloadsHelper::countTagItems($items, 'com_jdownloads.category');
            return $items;
        } else {
            return '';
        }
    }
    
    public static function getDownloadTags()
    {
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_tags/models', 'TagsModel');
        
        $app = JFactory::getApplication();
        $db = JFactory::getDbo();

        // Get an instance of the generic downloads model
        $model = JModelLegacy::getInstance ('Tags', 'TagsModel', array('ignore_request' => true));

        // Set application parameters in model
        $appParams = JComponentHelper::getParams('com_tags');
        $model->setState('params', $appParams);
       
        // Set the filters based on the module params
        $model->setState('list.start', 0);
        //$model->setState('list.limit', (int) $params->get('sum_view', 5));
        $model->setState('filter.published', 1);

        // Access filter
        $access = !JComponentHelper::getParams('com_jdownloads')->get('show_noauth');
        $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
        $model->setState('filter.access', $access);

        // User filter
        $userId = JFactory::getUser()->get('id');

        // Filter by language
        $model->setState('filter.language', '*');

        // Set sort ordering
        $ordering = 'a.title';
        $dir = 'ASC';

        $model->setState('list.ordering', $ordering);
        $model->setState('list.direction', $dir);

        $items = $model->getItems();

        if (count($items)){
            $items = JDownloadsHelper::countTagItems($items, 'com_jdownloads.download');
            return $items;
        } else {
            return '';
        }
        
    }
    
    public static function getTemplates()
    {
        $db = JFactory::getDBO();
        $query = "SELECT name FROM #__extensions WHERE type = 'template' AND client_id = 0 AND enabled = 1";
        $db->setQuery($query);
        $result = $db->loadColumn();
        if ($result){
            return $result;  
        } else {
            return array();
        }
    }
 
    public static function findTextInArray(array &$array, $text) {
        $keys = [];
        foreach ($array as $key => &$value) {
            if (strpos($value, $text) !== false) {
                $keys[] = $key;
            }
        }
        return $keys;
    } 
    
    public static function getMainMenuItem() {
        $db = JFactory::getDBO();
        $query = "SELECT title FROM #__menu WHERE published = 1 AND link = 'index.php?option=com_jdownloads&view=categories' AND client_id = 0";
        $db->setQuery($query);
        $result = $db->loadColumn();
        if ($result){
            return $result;  
        } else {
            return '';
        }
    }
 
    public static function checkSystemPlugin() {
        $db = JFactory::getDBO();
        $query = "SELECT enabled FROM #__extensions WHERE type = 'plugin' AND name = 'plg_system_jdownloads'";
        $db->setQuery($query);
        $result = $db->loadResult();
        
        if (!$result){
            // Activate it again
            $db = JFactory::getDBO();
            $query = "UPDATE #__extensions SET `enabled` = 1 WHERE type = 'plugin' AND name = 'plg_system_jdownloads'";
            $db->setQuery($query);
            $update = $db->execute();
            return $result;  
        } else {
            return $result;
        }        
    } 
    
}