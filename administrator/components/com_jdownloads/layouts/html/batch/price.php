<?php
defined('_JEXEC') or die;
                                                                              
?>
<fieldset>
<label id="batch-price-lbl" for="batch-price" class="modalTooltip" title="<?php
echo JHtml::_('tooltipText', 'COM_JDOWNLOADS_BATCH_PRICE_LABEL', 'COM_JDOWNLOADS_BATCH_PRICE_DESC'); ?>">
<?php echo JText::_('COM_JDOWNLOADS_BATCH_PRICE_LABEL'); ?>
</label>

<input id="batch_price" name="batch[price]" class="inputbox" type="text">
</fieldset>
