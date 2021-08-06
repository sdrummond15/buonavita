<?php
/**
 * @package jDownloads
 *   
 * 
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\LanguageHelper;

/**
 * Supports a modal Download picker.
 */
class JFormFieldModal_Download extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'Modal_Download';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$allowNew       = ((string) $this->element['new'] == 'true');
		$allowEdit      = ((string) $this->element['edit'] == 'true');
		$allowClear     = ((string) $this->element['clear'] != 'false');
		$allowSelect    = ((string) $this->element['select'] != 'false');
        $allowPropagate = ((string) $this->element['propagate'] == 'true');

		$languages = LanguageHelper::getContentLanguages(array(0, 1));
        
        // Load language
		JFactory::getLanguage()->load('com_jdownloads', JPATH_ADMINISTRATOR);

		// The active Download id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Create the modal id.
		$modalId = 'Download_' . $this->id;
        
        if ($this->id == 'jform_other_file_id'){
            // Get the target file ID for the selection - in this case should this Download not be listed
            $fileid = $this->form->getValue('id'); 
        }

        // We should not deactivate the association select buttons - only the url_download field when required
        if (!$this->group == 'associations'){
	        if ($this->form->getValue('url_download')){
	            $disabled = 'disabled';
	        } else {
	            $disabled = '';
	        }
        } else {
            $disabled = '';
        }

		// Add the modal field script to the document head.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/modal-fields.js', array('version' => 'auto', 'relative' => true));

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

				JText::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

				$scriptSelect[$this->id] = true;
			}
		}

		// Setup variables for display.
		$linkDownloads = 'index.php?option=com_jdownloads&amp;view=downloads&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
		$linkDownload  = 'index.php?option=com_jdownloads&amp;view=download&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';

		if (isset($this->element['language']))
		{
			$linkDownloads .= '&amp;forcedLanguage=' . $this->element['language'];
			$linkDownload  .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle    = JText::_('COM_JDOWNLOADS_CHANGE_DOWNLOAD') . ' &#8212; ' . $this->element['label'];
		}
		else
		{
			$modalTitle    = JText::_('COM_JDOWNLOADS_CHANGE_DOWNLOAD');
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

		$title = empty($title) ? JText::_('COM_JDOWNLOADS_SELECT_A_DOWNLOAD') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current category display field.
		$html  = '<span class="input-append">';
		$html .= '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select Download button
		if ($allowSelect)
        {
            $html .= '<button'
                . ' type="button" '.$disabled.' '
                . ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
                . ' id="' . $this->id . '_select"'
                . ' data-toggle="modal"'
                . ' data-target="#ModalSelect' . $modalId . '"'
                . ' title="' . JHtml::tooltipText('COM_JDOWNLOADS_CHANGE_DOWNLOAD') . '">'
                . '<span class="icon-file" aria-hidden="true"></span> ' . JText::_('COM_JDOWNLOADS_SELECT')
                . '</button>';
        }
        
		// New Download button
        if ($allowNew)
        {
            $html .= '<button'
                . ' type="button"'
                . ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
                . ' id="' . $this->id . '_new"'
                . ' data-toggle="modal"'
                . ' data-target="#ModalNew' . $modalId . '"'
                . ' title="' . JHtml::tooltipText('COM_JDOWNLOADS_CREATE_DOWNLOAD') . '">'
                . '<span class="icon-new" aria-hidden="true"></span> ' . JText::_('COM_JDOWNLOADS_ACTION_CREATE')
                . '</button>';
        }

		// Edit Download button
        if ($allowEdit)
        {
            $html .= '<button'
                . ' type="button"'
                . ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_edit"'
                . ' data-toggle="modal"'
                . ' data-target="#ModalEdit' . $modalId . '"'
                . ' title="' . JHtml::tooltipText('COM_JDOWNLOADS_BACKEND_FILESEDIT_TITLE') . '">'
                . '<span class="icon-edit" aria-hidden="true"></span> ' . JText::_('COM_JDOWNLOADS_ACTION_EDIT')
                . '</button>';
        }

        // Clear Download button
        if ($allowClear)
        {
            $html .= '<button'
                . ' type="button"'
                . ' class="btn' . ($value ? '' : ' hidden') . '"'
                . ' id="' . $this->id . '_clear"'
                . ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
                . '<span class="icon-remove" aria-hidden="true"></span>' . JText::_('COM_JDOWNLOADS_REMOVE')
                . '</button>';
        }
        
        // Propagate Download button
        if ($allowPropagate && count($languages) > 2)
        {
            // Strip off language tag at the end
            $tagLength = (int) strlen($this->element['language']);
            $callbackFunctionStem = substr("jSelectDownload_" . $this->id, 0, -$tagLength);

            $html .= '<a'
            . ' class="btn hasTooltip' . ($value ? '' : ' hidden') . '"'
            . ' id="' . $this->id . '_propagate"'
            . ' href="#"'
            . ' title="' . JHtml::tooltipText('COM_JDOWNLOADS_ASSOCIATIONS_PROPAGATE_TIP') . '"'
            . ' onclick="Joomla.propagateAssociation(\'' . $this->id . '\', \'' . $callbackFunctionStem . '\');">'
            . '<span class="icon-refresh" aria-hidden="true"></span>' . JText::_('COM_JDOWNLOADS_ASSOCIATIONS_PROPAGATE_BUTTON')
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
					'footer'      => '<button type="button" class="btn" data-dismiss="modal">' . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
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
					'title'       => JText::_('COM_JDOWNLOADS_CREATE_DOWNLOAD'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlNew,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<button type="button" class="btn"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'download\', \'cancel\', \'download-form\'); return false;">'
							. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
							. '<button type="button" class="btn btn-primary"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'download\', \'save\', \'download-form\'); return false;">'
							. JText::_('JSAVE') . '</button>'
							. '<button type="button" class="btn btn-success"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'add\', \'download\', \'apply\', \'download-form\'); return false;">'
							. JText::_('JAPPLY') . '</button>',
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
					'title'       => JText::_('COM_JDOWNLOADS_EDIT_DOWNLOAD'),
					'backdrop'    => 'static',
					'keyboard'    => false,
					'closeButton' => false,
					'url'         => $urlEdit,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<button type="button" class="btn"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'download\', \'cancel\', \'download-form\'); return false;">'
							. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>'
							. '<button type="button" class="btn btn-primary"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'download\', \'save\', \'download-form\'); return false;">'
							. JText::_('JSAVE') . '</button>'
							. '<button type="button" class="btn btn-success"'
							. ' onclick="window.processModalEdit(this, \'' . $this->id . '\', \'edit\', \'download\', \'apply\', \'download-form\'); return false;">'
							. JText::_('JAPPLY') . '</button>',
				)
			);
		}

		// Note: class='required' for client side validation
		$class = $this->required ? ' class="required modal-value"' : '';

		$html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars(JText::_('COM_JDOWNLOADS_SELECT_A_DOWNLOAD', true), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '" />';    
        
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
