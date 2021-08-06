<?php
defined('_JEXEC') or die;

?>
<a class="btn" type="button" onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value='';document.getElementById('batch-user-id').value='';document.getElementById('batch-tag-id').value=''" data-dismiss="modal">
    <?php echo JText::_('COM_JDOWNLOADS_CANCEL'); ?>
</a>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('category.batch');">
    <?php echo JText::_('COM_JDOWNLOADS_BATCH_PROCESS'); ?>
</button>