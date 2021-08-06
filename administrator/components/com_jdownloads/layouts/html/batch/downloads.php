<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * modified for jDownloads
 */

defined('JPATH_BASE') or die;

extract($displayData);

// Create the copy/move options.
$options = array(
	JHtml::_('select.option', 'n', JText::_('COM_JDOWNLOADS_NO')),
    JHtml::_('select.option', 'c', JText::_('COM_JDOWNLOADS_BATCH_COPY')),
    JHtml::_('select.option', 'cc', JText::_('COM_JDOWNLOADS_BATCH_COPY_WITH_FILES')),
    JHtml::_('select.option', 'ca', JText::_('COM_JDOWNLOADS_BATCH_COPY_FILE_ASSIGNED_FROM_SOURCE')),
	JHtml::_('select.option', 'm', JText::_('COM_JDOWNLOADS_BATCH_MOVE'))
);
?>
 
<label id="batch-choose-action-lbl" for="batch-choose-action" class="modalTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_JDOWNLOADS_BATCH_CATEGORY_LABEL', 'COM_JDOWNLOADS_BATCH_CATEGORY_LABEL_DESC'); ?>">
    <?php echo JText::_('COM_JDOWNLOADS_BATCH_CATEGORY_LABEL'); ?>
</label>

<div id="batch-choose-action" class="control-group">
        <?php echo JHtml::_('select.genericlist', $displayData, 'batch[category_id]', 'name="batch[category_id]" class="inputbox"', 'value', 'text'); ?>
</div>

<div id="batch-copy-move-jd" class="control-group radio">
	<?php 
    echo JText::_('JLIB_HTML_BATCH_MOVE_QUESTION'); ?>
	<?php 
    echo JHtml::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'n'); ?>
</div>
