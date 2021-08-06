<?php
/**
* @version $Id: mod_jdownloads_rated.php v3.8
* @package mod_jdownloads_rated
* @copyright (C) 2018 Arno Betz
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author Arno Betz http://www.jDownloads.com
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

class ModJDownloadsRatedHelper
{
	static function getList($params)
	{
        $db = JFactory::getDbo();
        $app = JFactory::getApplication();
        $appParams = $app->getParams('com_jdownloads');
        
        // Get the current user for authorisation checks
        $user    = JFactory::getUser();
        $user->authorise('core.admin') ? $is_admin = true : $is_admin = false;
        $groups  = implode (',', $user->getAuthorisedViewLevels());
        
        $type = $params->get('top_view');
        if (!$type){
            // most rated 
            $order = 'rating_count DESC, ratenum DESC';
        } else {
            // top rated
            $order = 'ratenum DESC , rating_count DESC';
        }
        
        // Access filter
        $access = true;
        $authorised = $user->getAuthorisedViewLevels();
        $groups = implode(',', $authorised);
        
        $catid = $params->get('catid', array());
        $catid = implode(',', $catid);
        
        if ($user->id > 0){
            // User is not a guest so we can generally use the user-id to find also the Downloads with single user access
            if ($is_admin){
                // User is admin so we should display all possible Downloads - included the Downloads with single user access 
                $where  = ' WHERE ((a.access IN ('.$groups.') AND a.user_access = 0) OR (a.access != 0 AND a.user_access != 0))';
                $where .= ' AND c.access IN ('.$groups.')';
            } else {
                $where  = ' WHERE ((a.access IN ('.$groups.') AND a.user_access = 0) OR (a.access != 0 AND a.user_access = '.$db->quote($user->id). '))';
                $where .= ' AND c.access IN ('.$groups.')';
            }
        } else {    
            $where = ' WHERE a.access IN ('.$groups.')';
            $where .= ' AND c.access IN ('.$groups.')';
        }
        
        if (!$catid){
            $where .= ' AND a.published = 1 AND a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')';
        } else {
            $where .= ' AND a.published = 1 AND a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') .
            ') AND a.catid IN (' . $catid . ')';
        }

        // create the query
        $query = 'SELECT a.id, 
                         a.title,
                         a.alias, 
                         a.description,
                         a.file_pic,
                         a.url_download,
                         a.other_file_id,
                         a.extern_file,
                         a.catid,
                         a.release,
                         a.access,
                         a.user_access,
                         c.title as category_title,
                         c.access as category_access,
                         c.alias as category_alias,
                         c.cat_dir as category_cat_dir,
                         c.cat_dir_parent as category_cat_dir_parent,
                         mf.id as menu_itemid,
                         mf.link as menu_link,
                         mf.access as menu_access,
                         mf.published as menu_published,
                         mc.id as menu_cat_itemid,
                         mc.link as menu_cat_link,
                         mc.access as menu_cat_access,
                         mc.published as menu_cat_published,                         
                         r.file_id,
                         r.rating_count ,
                       round( r.rating_sum / r.rating_count ) * 20 as ratenum
                       
                    FROM #__jdownloads_files AS a
                    LEFT JOIN #__jdownloads_categories AS c
                          ON c.id = a.catid
                    LEFT JOIN #__menu AS mf
                          ON mf.link LIKE CONCAT(\'index.php?option=com_jdownloads&view=download&id=\',a.id)
                    LEFT JOIN #__menu AS mc
                          ON mc.link LIKE CONCAT(\'index.php?option=com_jdownloads&view=category&catid=\',a.catid)                                                    
                    INNER JOIN #__jdownloads_ratings AS r
                          ON a.id = r.file_id ' .
                          $where .
                    ' ORDER BY '.$order;

        $db->setQuery($query, 0, (int) $params->get('sum_view'));
        $items = $db->loadObjectList();
        
        if ($db->getErrorNum()) {
            jError::raiseWarning(S00, $db->stderr(true));
        }
        
        foreach ($items as &$item){
            $item->slug = $item->id . ':' . $item->alias;
            $item->catslug = $item->catid . ':' . $item->category_alias;

            if ($access || in_array($item->access, $authorised)){
                // We know that user has the privilege to view the download
                $item->link = '-';
            } else {
                $item->link = JRoute::_('index.php?option=com_users&view=login');
            }
        }
        return $items;        
	}
    
    /**
    * remove the language tag from a given text and return only the text
    *    
    * @param string     $msg
    */
    public static function getOnlyLanguageSubstring($msg)
    {
        // Get the current locale language tag
        $lang       = JFactory::getLanguage();
        $lang_key   = $lang->getTag();        
        
        // remove the language tag from the text
        $startpos = strpos($msg, '{'.$lang_key.'}') +  strlen( $lang_key) + 2 ;
        $endpos   = strpos($msg, '{/'.$lang_key.'}') ;
        
        if ($startpos !== false && $endpos !== false){
            return substr($msg, $startpos, ($endpos - $startpos ));
        } else {    
            return $msg;
        }    
    }      
}	
?>