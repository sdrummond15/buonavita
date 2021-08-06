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


jimport('joomla.application.component.helper');
jimport('joomla.application.categories');

JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

$path = JPATH_SITE.'/components/com_jdownloads/helpers/categories.php';
if (is_file($path)) include_once $path;

/**
 * jDownloads Component Route Helper
 *
 * @static
 */
abstract class JdownloadsHelperRoute
{
	protected static $lookup;

	/**
     * Get the Download route.
     * 
	 * @param	 int	$id         The route of the download item
     * @param    int    $catid      The route of the category item 
     * @param    string $language   The language from the item
     * @param    string $layout     The layout value
     * 
     * @return  string  The Download route.
     * 
     */
	public static function getDownloadRoute($id, $catid = 0, $language = 0, $layout = null)
	{
        // We need all associations when multi lingual is activated
        if (JLanguageMultilang::isEnabled()){
            $app    = JFactory::getApplication();
            $menus  = $app->getMenu();
            $active = $menus->getActive(); 
            if ($active){
                $assocs = MenusHelper::getAssociations( $active->id );    
            } else {
                $assocs = array();
            }
        }
        
        $needles = array('download' => array((int) $id));
		
        //Create the link
		$link = 'index.php?option=com_jdownloads&view=download&id='. $id;
		
        if ((int)$catid > 1){
			$categories = JDCategories::getInstance('');
			$category = $categories->get((int)$catid);
			
            if ($category){
				$needles['category'] = array_reverse($category->getPath());
				$needles['categories'] = $needles['category'];
				$link .= '&catid='.$catid;
			}
		}

		// This part is only required when multi languages is activated

		if ($language && $language != "*" && JLanguageMultilang::isEnabled()) {
			$db		= JFactory::getDBO();
			$query	= $db->getQuery(true);
			$query->select('a.sef AS sef');
			$query->select('a.lang_code AS lang_code');
			$query->from('#__languages AS a');
			//$query->where('a.lang_code = ' .$language);
			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang){
				if ($language == $lang->lang_code){
					$assoc_language_code = $language;
                    $language = $lang->sef;
					$link .= '&lang='.$language;
				}
			}
		} else {
            $assoc_language_code = '';
        }
        
        if ($layout){
            $link .= '&layout=' . $layout;
        }
        
        // Exists associations, then use the right one for the Itemid
        if (isset($assocs) && count($assocs) > 1 && $assoc_language_code){
            // Okay, lets try to find the correctly Itemid
            foreach ($assocs as $key => $value){
                if ($key == $assoc_language_code){
                    $item = $value;
                    continue;
                }
            }
            $link .= '&Itemid='.$item;
            
        } else {

		    if ($item = self::_findItem($needles)){
			    $link .= '&Itemid='.$item;
		    } elseif ($item = self::_findItem()){
			    $link .= '&Itemid='.$item;
		    }
        }

		return $link;
        
	}

    /**
     * @param    int    The route of the download item 
     *           int    The route of the category item
     *           string The language from the item
     *           string The URL type
     *           int    The mirror value (can be 0,1,2) 
     */
    public static function getOtherRoute($id, $catid = 0, $language = 0, $type = '', $m = '', $layout = null)
    {
        // We need all associations when multi lingual is activated
        if (JLanguageMultilang::isEnabled()){
            $app    = JFactory::getApplication();
            $menus  = $app->getMenu();
            $active = $menus->getActive(); 
            if ($active){
                $assocs = MenusHelper::getAssociations( $active->id );    
            } else {
                $assocs = array();
            }    
        }
        
        $needles = array('download' => array((int) $id));
        
        //Create the link
        $link = 'index.php?option=com_jdownloads&view='.$type.'&id='. $id;
        
        if ((int)$catid > 1){
            $categories = JDCategories::getInstance('');
            $category = $categories->get((int)$catid);
            
            if ($category){
                $needles['category'] = array_reverse($category->getPath());
                $needles['categories'] = $needles['category'];
                $link .= '&catid='.$catid;
            }
        }

        if ($language && $language != "*" && JLanguageMultilang::isEnabled()){
            $db        = JFactory::getDBO();
            $query    = $db->getQuery(true);
            $query->select('a.sef AS sef');
            $query->select('a.lang_code AS lang_code');
            $query->from('#__languages AS a');
            //$query->where('a.lang_code = ' .$language);
            $db->setQuery($query);
            $langs = $db->loadObjectList();
            
            foreach ($langs as $lang){
                if ($language == $lang->lang_code){
                    $assoc_language_code = $language;
                    $language = $lang->sef;
                    $link .= '&lang='.$language;
                }
            }
        } else {
            $assoc_language_code = '';
        }

        // mirror
        if ($m != ''){
            $link .= '&m='.$m;
        }
        
        if ($layout){ 
            $link .= '&layout=' . $layout;
        }
        
        // Exists associations, then use the right one for the Itemid
        if (isset($assocs) && count($assocs) > 1 && $assoc_language_code){
            // Okay, lets try to find the correctly Itemid
            foreach ($assocs as $key => $value){
                if ($key == $assoc_language_code){
                    $item = $value;
                    continue;
                }
            }
            $link .= '&Itemid='.$item;
            
        } else {
        
            if ($item = self::_findItem($needles)){
                $link .= '&Itemid='.$item;
            } elseif ($item = self::_findItem()){
                $link .= '&Itemid='.$item;
            }
        }

        return $link;
    }    
    
    /* Get the category route.
     *
     * @param   integer  $catid             The category ID.
     * @param   boolean  $complete_link     True when the url must be complete
     * @param   integer  $language          The language code.
     * @param   string   $layout            The layout value.
     *
     * @return  string  The category route.    
     */
	public static function getCategoryRoute($catid, $complete_link = false, $language = 0, $layout = null)
	{
        // We need all associations when multi lingual is activated
        if (JLanguageMultilang::isEnabled()){
            $app    = JFactory::getApplication();
            $menus  = $app->getMenu();
            $active = $menus->getActive(); 
            if ($active){
                $assocs = MenusHelper::getAssociations( $active->id );    
            } else {
                $assocs = array();
            }
        }
        
        if ($catid instanceof JCategoryNode){
			$id = $catid->id;
			$category = $catid;
		} else {
			$id = (int) $catid;
			$category = JDCategories::getInstance('jdownloads')->get($id);
		}
        
		if ($id < 1){
			$link = '';
        } else {
            $needles = array('category' => array($id));

			if (!$complete_link && $item = self::_findItem($needles)){
				$link = 'index.php?Itemid='.$item;
			} else {
				//Create the link
				$link = 'index.php?option=com_jdownloads&view=category&catid='.$id;
				
                if ($category){
					$catids = array_reverse($category->getPath());
					$needles = array(
						'category' => $catids,
						'categories' => $catids
					);
					
                    if ($language && $language != "*" && JLanguageMultilang::isEnabled()){
                        $db        = JFactory::getDBO();
                        $query    = $db->getQuery(true);
                        $query->select('a.sef AS sef');
                        $query->select('a.lang_code AS lang_code');
                        $query->from('#__languages AS a');
                        //$query->where('a.lang_code = ' .$language);
                        $db->setQuery($query);
                        $langs = $db->loadObjectList();
                        
                        foreach ($langs as $lang){
                            if ($language == $lang->lang_code){
                                $assoc_language_code = $language;
                                $language = $lang->sef;
                                $link .= '&lang='.$language;
                            }
                        }
                        
                    } else {
                        $assoc_language_code = '';
                    }
                    
                    if ($layout){
                        $link .= '&layout=' . $layout;
                    }
                    
                    // Exists associations, then use the right one for the Itemid
                    if (isset($assocs) && count($assocs) > 1 && $assoc_language_code){
                        // Okay, lets try to find the correctly Itemid
                        foreach ($assocs as $key => $value){
                            if ($key == $assoc_language_code){
                                $item = $value;
                                continue;
                            }
                        }
                        $link .= '&Itemid='.$item;
                        
                    } else {
                    
                        if ($item = self::_findItem($needles)){
                            $link .= '&Itemid='.$item;
                        } elseif ($item = self::_findItem()){
                            $link .= '&Itemid='.$item;
                        }
                    }
                        
				}
			}
		}

		return $link;
        
	}

    /**
     * Get the form route.
     *
     * @param   integer  $id  The form ID.
     *
     * @return  string  The Download route.
     *
     */
	public static function getFormRoute($id)
	{
		if ($id) {
			$link = 'index.php?option=com_jdownloads&task=download.edit&a_id='. $id;
		} else {
			$link = 'index.php?option=com_jdownloads&task=download.edit&a_id=0';
		}

		return $link;
	}

    /**
     * Find Item static function
     *
     * @param   array  $needles  Array used to get the language value
     *
     * @return null
     *
     * @throws Exception
     */
	protected static function _findItem($needles = null)
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu('site');
        $language   = isset($needles['language']) ? $needles['language'] : '*';

        // Prepare the reverse lookup array.
        if (!isset(self::$lookup[$language]))
        {
            self::$lookup[$language] = array();

            $component  = JComponentHelper::getComponent('com_jdownloads');

            $attributes = array('component_id');
            $values     = array($component->id);

            if ($language != '*')
            {
                $attributes[] = 'language';
                $values[]     = array($needles['language'], '*');
            }

            $items = $menus->getItems($attributes, $values);

            foreach ($items as $item)
            {
                if (isset($item->query) && isset($item->query['view']))
                {
                    $view = $item->query['view'];

                    if (!isset(self::$lookup[$language][$view]))
                    {
                        self::$lookup[$language][$view] = array();
                    }

                    if (isset($item->query['id']))
                    {
                        /**
                         * Here it will become a bit tricky
                         * language != * can override existing entries
                         * language == * cannot override existing entries
                         */
                        if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
                        {
                            self::$lookup[$language][$view][$item->query['id']] = $item->id;
                        }
                    }
                }
                
				// find the jDownloads main link (root)
                if ($item->link === 'index.php?option=com_jdownloads&view=categories' && (!isset($item->query['id']) || $item->query['id'] == 0)){
                    self::$lookup[$language]['categories']['root'] = $item->id;
                }    
            }
        }

        if ($needles)
        {
            foreach ($needles as $view => $ids)
            {
                if (isset(self::$lookup[$language][$view]))
                {
                    foreach ($ids as $id)
                    {
                        if (isset(self::$lookup[$language][$view][(int) $id]))
                        {
                            return self::$lookup[$language][$view][(int) $id];
                        }
                    }
                }
            }
        }

        // Check if the active menuitem matches the requested language
        $active = $menus->getActive();

        if ($active && $active->component == 'com_jdownloads' && ($active->query['view'] != 'search') && ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled()))
        {
            return $active->id;
        }

        // If not found, return language specific jD root link
        if (isset(self::$lookup[$language]['categories']) && (isset(self::$lookup[$language]['categories']['root']))){
            $default =  (int)self::$lookup[$language]['categories']['root'];    
        } else {
            $default = '';
        }    
        
        // If not found, return language specific home link
        if (!$default){
            $menus->getDefault($language);
            return !empty($default->id) ? $default->id : null;
        } else {
            return !empty($default) ? $default : null;
        }    
    
	}
}