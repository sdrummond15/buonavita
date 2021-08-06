<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_jdownloads
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * Modified by Arno Betz for jDownloads
 */

defined('_JEXEC') or die;

JLoader::register('JDownloadsHelper', JPATH_ADMINISTRATOR . '/components/com_jdownloads/helpers/jdownloads.php');
JLoader::register('JDownloadsHelperRoute', JPATH_SITE . '/components/com_jdownloads/helpers/route.php');
JLoader::register('JDownloadsAssociationsHelper', JPATH_ADMINISTRATOR . '/components/com_jdownloads/helpers/associations.php');
JLoader::register('CategoryHelperAssociation', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/association.php');


/**
 * jDownloads Component Association Helper
 *
 * @since  3.0
 */
abstract class JDownloadsHelperAssociation extends CategoryHelperAssociation
{
	/**
	 * Method to get the associations for a given item
	 *
	 * @param   integer  $id      Id of the item
	 * @param   string   $view    Name of the view
	 * @param   string   $layout  View layout
	 *
	 * @return  array   Array of associations for the item
	 *
	 * @since  3.0
	 */
	public static function getAssociations($id = 0, $view = null, $layout = null)
	{
		$jinput    = JFactory::getApplication()->input;
		$view      = $view === null ? $jinput->get('view') : $view;
		$component = $jinput->getCmd('option');
		$id        = empty($id) ? $jinput->getInt('id') : $id;

		if ($layout === null && $jinput->get('view') == $view && $component == 'com_jdownloads')
		{
			$layout = $jinput->get('layout', '', 'string');
		}

		if ($view === 'download')
		{
			if ($id)
			{
				$user      = JFactory::getUser();
				$groups    = implode(',', $user->getAuthorisedViewLevels());
				$db        = JFactory::getDbo();
				$advClause = array();

				// Filter by user groups
				$advClause[] = 'c2.access IN (' . $groups . ')';

				// Filter by current language
				$advClause[] = 'c2.language != ' . $db->quote(JFactory::getLanguage()->getTag());

				if (!$user->authorise('core.edit.state', 'com_jdownloads') && !$user->authorise('core.edit', 'com_jdownloads'))
				{
					// Filter by start and end dates.
					$nullDate = $db->quote($db->getNullDate());
					$date = JFactory::getDate();

					$nowDate = $db->quote($date->toSql());

					$advClause[] = '(c2.publish_up = ' . $nullDate . ' OR c2.publish_up <= ' . $nowDate . ')';
					$advClause[] = '(c2.publish_down = ' . $nullDate . ' OR c2.publish_down >= ' . $nowDate . ')';

					// Filter by published
					$advClause[] = 'c2.published = 1';
				}

				$associations = self::getJDAssociations('#__jdownloads_files', 'com_jdownloads.item', $id, 'id', 'alias', 'catid', $advClause);

				$return = array();

				foreach ($associations as $tag => $item){
                    $return[$tag] = JDownloadsHelperRoute::getDownloadRoute($item->id, (int) $item->catid, $item->language, $layout);
				}

				return $return;
			}
		}

		if ($view === 'category' || $view === 'categories')
		{
			return JDownloadsAssociationsHelper::getCategoryAssociations($id, 'com_jdownloads', $layout);
		}

		return array();
	}

	/**
	 * Method to display in frontend the associations for a given download
	 *
	 * @param   integer  $id  Id of the download
	 *
	 * @return  array  An array containing the association URL and the related language object
	 *
	 * @since  3.7.0
	 */
	public static function displayAssociations($id)
	{
		$return = array();

		if ($associations = self::getAssociations($id, 'download'))
		{
			$levels    = JFactory::getUser()->getAuthorisedViewLevels();
			$languages = JLanguageHelper::getLanguages();

			foreach ($languages as $language)
			{
				// Do not display language when no association
				if (empty($associations[$language->lang_code]))
				{
					continue;
				}

				// Do not display language without frontend UI
				if (!array_key_exists($language->lang_code, JLanguageHelper::getInstalledLanguages(0)))
				{
					continue;
				}

				// Do not display language without specific home menu
				if (!array_key_exists($language->lang_code, JLanguageMultilang::getSiteHomePages()))
				{
					continue;
				}

				// Do not display language without authorized access level
				if (isset($language->access) && $language->access && !in_array($language->access, $levels))
				{
					continue;
				}

				$return[$language->lang_code] = array('item' => $associations[$language->lang_code], 'language' => $language);
			}
		}

		return $return;
	}
    
    /**
     * Get the associations.
     *
     * @param   string   $tablename   The name of the table.
     * @param   string   $context     The context
     * @param   integer  $id          The primary key value.
     * @param   string   $pk          The name of the primary key in the given $table.
     * @param   string   $aliasField  If the table has an alias field set it here. Null to not use it
     * @param   string   $catField    If the table has a catid field set it here. Null to not use it
     * @param   array    $advClause   Additional advanced 'where' clause; use c as parent column key, c2 as associations column key
     *
     * @return  array  The associated items
     *
     * @since   3.1
     *
     * @throws  \Exception
     * 
     * Modified function from JLanguageAssociations::getAssociations() to make it compatible for jDownloads Category structure
     * Hard coded part for Joomla categories removed/modified
     */
    public static function getJDAssociations($tablename, $context, $id, $pk = 'id', $aliasField = 'alias', $catField = 'catid', $advClause = array())
    {
        // To avoid doing duplicate database queries.
        static $multilanguageAssociations = array();

        // Multilanguage association array key. If the key is already in the array we don't need to run the query again, just return it.
        $queryKey = md5(serialize(array_merge(array($tablename, $context, $id), $advClause)));

        if (!isset($multilanguageAssociations[$queryKey]))
        {
            $multilanguageAssociations[$queryKey] = array();

            $db = \JFactory::getDbo();
            $categoriesExtraSql = '';
            $query = $db->getQuery(true)
                ->select($db->quoteName('c2.language'))
                ->from($db->quoteName($tablename, 'c'))
                ->join('INNER', $db->quoteName('#__associations', 'a') . ' ON a.id = c.' . $db->quoteName($pk) . ' AND a.context=' . $db->quote($context))
                ->join('INNER', $db->quoteName('#__associations', 'a2') . ' ON ' . $db->quoteName('a.key') . ' = ' . $db->quoteName('a2.key'))
                ->join('INNER', $db->quoteName($tablename, 'c2') . ' ON a2.id = c2.' . $db->quoteName($pk) . $categoriesExtraSql);

            // Use alias field ?
            if (!empty($aliasField))
            {
                $query->select(
                    $query->concatenate(
                        array(
                            $db->quoteName('c2.' . $pk),
                            $db->quoteName('c2.' . $aliasField),
                        ),
                        ':'
                    ) . ' AS ' . $db->quoteName($pk)
                );
            }
            else
            {
                $query->select($db->quoteName('c2.' . $pk));
            }

            // Use catid field ?
            if (!empty($catField))
            {
                $query->join(
                        'INNER',
                        $db->quoteName('#__jdownloads_categories', 'ca') . ' ON ' . $db->quoteName('c2.' . $catField) . ' = ca.id')
                    ->select(
                        $query->concatenate(
                            array('ca.id', 'ca.alias'),
                            ':'
                        ) . ' AS ' . $db->quoteName($catField)
                    );
            }

            $query->where('c.' . $pk . ' = ' . (int) $id);

            // Advanced where clause
            if (!empty($advClause))
            {
                foreach ($advClause as $clause)
                {
                    $query->where($clause);
                }
            }

            $db->setQuery($query);

            try
            {
                $items = $db->loadObjectList('language');
            }
            catch (\RuntimeException $e)
            {
                throw new \Exception($e->getMessage(), 500, $e);
            }

            if ($items)
            {
                foreach ($items as $tag => $item)
                {
                    // Do not return itself as result
                    if ((int) $item->{$pk} !== $id)
                    {
                        $multilanguageAssociations[$queryKey][$tag] = $item;
                    }
                }
            }
        }

        return $multilanguageAssociations[$queryKey];
    }    
    
}
