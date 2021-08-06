<?php
/**
 * @package jDownloads
 * @version 4.0  
 * @copyright (C) 2007 - 2017 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined('JPATH_BASE') or die;

/**
 * Supports a modal Download picker.
 */
class JFormFieldModal_Downloadlist extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'Modal_Downloadlist';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$allowNew    = ((string) $this->element['new'] == 'true');
		$allowEdit   = ((string) $this->element['edit'] == 'true');
		$allowClear  = ((string) $this->element['clear'] != 'false');
		$allowSelect = ((string) $this->element['select'] != 'false');

		// Load language
		JFactory::getLanguage()->load('com_jdownloads', JPATH_ADMINISTRATOR);

		// The active Download id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Create the modal id.
		$modalId = 'Download_' . $this->id;

		// Add the modal field script to the document head.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/modal-fields.js', false, true);

		// Script to proxy the select modal function to the modal-fields.js file.
		if ($allowSelect)
		{
			static $scriptSelect = null;

			if (is_null($scriptSelect))
			{
				$scriptSelect = array();
			}

			if (!isset($scriptSelect[$this->id]))
			{
				JFactory::getDocument()->addScriptDeclaration("
				function jSelectDownload_" . $this->id . "(id, title, catid, object, url, language) {
					window.processModalSelect('Download', '" . $this->id . "', id, title, catid, object, url, language);
				}
				");

				$scriptSelect[$this->id] = true;
			}
		}

		// Setup variables for display.
		$linkDownloads = 'index.php?option=com_jdownloads&amp;view=downloads&amp;layout=modallist&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
		$linkDownload  = 'index.php?option=com_jdownloads&amp;view=download&amp;layout=modallist&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';

		if (isset($this->element['language']))
		{
			$linkDownloads .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkDownload  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle    = JText::_('COM_JDOWNLOADS_FILESEDIT_FILE_FROM_OTHER_DOWNLOAD_DEFAULT') . ' &#8212; ' . $this->element['label'];
		}
		else
		{
			$modalTitle    = JText::_('COM_JDOWNLOADS_JD_MENU_VIEWDOWNLOAD_LABEL2');
		}

		$urlSelect = $linkDownloads . '&amp;function=jSelectDownload_' . $this->id;
		$urlEdit   = $linkDownload . '&amp;task=download.edit&amp;id=\' + document.getElementById("' . $this->id . '_id").value + \'';
		$urlNew    = $linkDownload . '&amp;task=download.add';

		if ($value)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__jdownloads_files'))
				->where($db->quoteName('id') . ' = ' . (int) $value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}
		}

		$title = empty($title) ? JText::_('') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current Download display field.
		$html  = '<span class="input-append">';
		$html .= '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select Download button
		if ($allowSelect)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_select"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalSelect' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_JDOWNLOADS_JD_MENU_VIEWDOWNLOAD_LABEL2') . '">'
				. '<span class="icon-file"></span> ' . JText::_('COM_JDOWNLOADS_SELECT')
				. '</a>';
		}

		// New Download button
		if ($allowNew)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
				. ' id="' . $this->id . '_new"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalNew' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_JDOWNLOADS_BACKEND_FILESEDIT_ADD') . '">'
				. '<span class="icon-new"></span> ' . JText::_('COM_JDOWNLOADS_ACTION_CREATE')
				. '</a>';
		}

		// Edit Download button
		if ($allowEdit)
		{
			$html .= '<a'
				. ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_edit"'
				. ' data-toggle="modal"'
				. ' role="button"'
				. ' href="#ModalEdit' . $modalId . '"'
				. ' title="' . JHtml::tooltipText('COM_JDOWNLOADS_BACKEND_FILESEDIT_TITLE') . '">'
				. '<span class="icon-edit"></span> ' . JText::_('COM_JDOWNLOADS_ACTION_EDIT')
				. '</a>';
		}

		// Clear Download button
		if ($allowClear)
		{
			$html .= '<a'
				. ' class="btn' . ($value ? '' : ' hidden') . '"'
				. ' id="' . $this->id . '_clear"'
				. ' href="#"'
				. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
				. '<span class="icon-remove"></span>' . JText::_('COM_JDOWNLOADS_REMOVE')
				. '</a>';
		}

		$html .= '</span>';

		// Select Download modal
		if ($allowSelect)
		{
			$html .= JHtml::_(
				'bootstrap.renderModal',
				'ModalSelect' . $modalId,
				array(
					'title'       => $modalTitle,
					'url'         => $urlSelect,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<a role="button" class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</a>',
				)
			);
		}

		// New Download modal
		if ($allowNew)
		{
			$html .= JHtml::_(
				'bootstrap.renderModal',
				'ModalNew' . $modalId,
				array(
					'title'       => JText::_('COM_CONTENT_NEW_ARTICLE'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlNew,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<a role="button" class="btn" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'download\', \'cancel\', \'item-form\'); return false;">'
							. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</a>'
							. '<a role="button" class="btn btn-primary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'download\', \'save\', \'item-form\'); return false;">'
							. JText::_("JSAVE") . '</a>'
							. '<a role="button" class="btn btn-success" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'download\', \'apply\', \'item-form\'); return false;">'
							. JText::_("JAPPLY") . '</a>',
				)
			);
		}

		// Edit Download modal
		if ($allowEdit)
		{
			$html .= JHtml::_(
				'bootstrap.renderModal',
				'ModalEdit' . $modalId,
				array(
					'title'       => JText::_('COM_CONTENT_EDIT_ARTICLE'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlEdit,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<a role="button" class="btn" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'download\', \'cancel\', \'item-form\'); return false;">'
							. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</a>'
							. '<a role="button" class="btn btn-primary" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'download\', \'save\', \'item-form\'); return false;">'
							. JText::_("JSAVE") . '</a>'
							. '<a role="button" class="btn btn-success" aria-hidden="true"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'download\', \'apply\', \'item-form\'); return false;">'
							. JText::_("JAPPLY") . '</a>',
				)
			);
		}

		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		$html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars(JText::_('', true), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '" />';

		return $html;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
}
