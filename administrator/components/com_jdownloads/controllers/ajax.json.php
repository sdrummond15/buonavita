<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\LanguageHelper;

/**
 * The download controller for ajax requests
 *
 * @since  3.9.0
 */
class JdownloadsControllerAjax extends JControllerLegacy
{
	/**
	 * Method to fetch associations of an download
	 *
	 * The method assumes that the following http parameters are passed in an Ajax Get request:
	 * token: the form token
	 * assocId: the id of the download whose associations are to be returned
	 * excludeLang: the association for this language is to be excluded
	 *
	 * @return  null
	 *
	 * @since  3.9.0
	 */
	public function fetchAssociations()
	{
		if (!JSession::checkToken('get'))
		{
			echo new JResponseJson(null, JText::_('JINVALID_TOKEN'), true);
		}
		else
		{
			// We need the type (category or download)
            $app = JFactory::getApplication();
            $type = $app->getUserState('type', 'string');
            
            $input = $app->input;
            
			$assocId = $input->getInt('assocId', 0);

			if ($assocId == 0)
			{
				echo new JResponseJson(null, JText::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', 'assocId'), true);

				return;
			}

			$excludeLang = $input->get('excludeLang', '', 'STRING');

			if ($type == 'download'){
                $associations = JLanguageAssociations::getAssociations('com_jdownloads', '#__jdownloads_files', 'com_jdownloads.item', (int) $assocId, 'id', 'alias', '');
			    unset($associations[$excludeLang]);

			    // Add the title to each of the associated records
			    $contentTable = JTable::getInstance('download', 'jdownloadsTable');
            } else {
                $associations = JLanguageAssociations::getAssociations('com_jdownloads', '#__jdownloads_categories', 'com_jdownloads.category.item', (int) $assocId, 'id', 'alias', '');
                unset($associations[$excludeLang]);

                // Add the title to each of the associated records
                $contentTable = JTable::getInstance('category', 'jdownloadsTable');
            }

			foreach ($associations as $lang => $association)
			{
				$contentTable->load($association->id);
				$associations[$lang]->title = $contentTable->title;
			}

			$countContentLanguages = count(LanguageHelper::getContentLanguages(array(0, 1)));

			if (count($associations) == 0)
			{
				$message = JText::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_NONE');
			}
			elseif ($countContentLanguages > count($associations) + 2)
			{
				$tags    = implode(', ', array_keys($associations));
				$message = JText::sprintf('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_SOME', $tags);
			}
			else
			{
				$message = JText::_('JGLOBAL_ASSOCIATIONS_PROPAGATE_MESSAGE_ALL');
			}

			echo new JResponseJson($associations, $message);
		}
	}
}
