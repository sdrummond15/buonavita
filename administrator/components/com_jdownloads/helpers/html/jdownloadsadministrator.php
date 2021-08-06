<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Added to support the Joomla Language Associations

use Joomla\Utilities\ArrayHelper;

JLoader::register('JDownloadsHelper', JPATH_ADMINISTRATOR . '/components/com_jdownloads/helpers/jdownloads.php');

/**
 * Content HTML helper
 *
 * @since  3.0
 */
abstract class JHtmlJdownloadsAdministrator
{
	/**
	 * Render the list of associated items
	 *
	 * @param   integer  $fileid  The download item id
	 *
	 * @return  string  The language HTML
	 *
	 * @throws  Exception
	 */
	public static function association($fileid)
	{
		// Defaults
		$html = '';

		// Get the associations
		if ($associations = JLanguageAssociations::getAssociations('com_jdownloads', '#__jdownloads_files', 'com_jdownloads.item', $fileid, 'id', 'alias', ''))
		{
			foreach ($associations as $tag => $associated)
			{
				$associations[$tag] = (int) $associated->id;
			}

			// Get the associated menu items
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('c.*')
				->select('l.sef as lang_sef')
				->select('l.lang_code')
				->from('#__jdownloads_files as c')
				->select('cat.title as category_title')
				->join('LEFT', '#__jdownloads_categories as cat ON cat.id = c.catid')
				->where('c.id IN (' . implode(',', array_values($associations)) . ')')
				->where('c.id != ' . $fileid)
				->join('LEFT', '#__languages as l ON c.language = l.lang_code')
				->select('l.image')
				->select('l.title as language_title');
			$db->setQuery($query);

			try
			{
				$items = $db->loadObjectList('id');
			}
			catch (RuntimeException $e)
			{
				throw new Exception($e->getMessage(), 500, $e);
			}

			if ($items)
			{
				foreach ($items as &$item)
				{
					$text    = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
					$url     = JRoute::_('index.php?option=com_jdownloads&task=download.edit&id=' . (int) $item->id);

					$tooltip = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '<br />' . JText::sprintf('JCATEGORY_SPRINTF', $item->category_title);
					$classes = 'hasPopover label label-association label-' . $item->lang_sef;

					$item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes
						. '" data-content="' . $tooltip . '" data-placement="top">'
						. $text . '</a>';
				}
			}

			JHtml::_('bootstrap.popover');

			$html = JLayoutHelper::render('joomla.content.associations', $items);
		}

		return $html;
	}
    
    /**
     * Render the list of associated category items
     *
     * @param   integer  $catid      Category identifier to search its associations
     * @param   string   $extension  Category Extension
     *
     * @return  string   The language HTML
     *
     * @since   3.2
     * @throws  Exception
     */
    public static function catAssociation($catid, $extension = '')
    {
        // Defaults
        $html = '';

        // Get the associations
        if ($associations = JDownloadsHelper::getCatAssociations($catid, $extension))
        {
            $associations = ArrayHelper::toInteger($associations);

            // Get the associated categories
            $db = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('c.id, c.title')
                ->select('l.sef as lang_sef')
                ->select('l.lang_code')
                ->from('#__jdownloads_categories as c')
                ->where('c.id IN (' . implode(',', array_values($associations)) . ')')
                ->where('c.id != ' . $catid)
                ->join('LEFT', '#__languages as l ON c.language=l.lang_code')
                ->select('l.image')
                ->select('l.title as language_title');
            $db->setQuery($query);

            try
            {
                $items = $db->loadObjectList('id');
            }
            catch (RuntimeException $e)
            {
                throw new Exception($e->getMessage(), 500, $e);
            }

            if ($items)
            {
                foreach ($items as &$item)
                {
                    $text    = $item->lang_sef ? strtoupper($item->lang_sef) : 'XX';
                    $url     = JRoute::_('index.php?option=com_jdownloads&task=category.edit&id=' . (int) $item->id);
                    $classes = 'hasPopover label label-association label-' . $item->lang_sef;

                    $item->link = '<a href="' . $url . '" title="' . $item->language_title . '" class="' . $classes
                        . '" data-content="' . htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') . '" data-placement="top">'
                        . $text . '</a>';
                }
            }

            JHtml::_('bootstrap.popover');

            $html = JLayoutHelper::render('joomla.content.associations', $items);
        }

        return $html;
    }

}
