<?php
/**
 * @package jDownloads
 * @version 4.0  
 * @copyright (C) 2007 - 2016 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

    JHtml::_('bootstrap.tooltip');
    
    HTMLHelper::_('behavior.keepalive');
    HTMLHelper::_('behavior.formvalidator');    
    HTMLHelper::_('behavior.calendar');
    HTMLHelper::_('behavior.tabstate');
    HTMLHelper::_('formbehavior.chosen', 'select');

    // Path to the layouts folder 
    $basePath = JPATH_ROOT .'/administrator/components/com_jdownloads/layouts'; 

    $this->hiddenFieldsets  = array();

    // Create shortcut to parameters.
    $params = $this->state->get('params'); 
 
?>

<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'license.cancel' || document.formvalidator.isValid(document.getElementById('license-form'))) {
            Joomla.submitform(task, document.getElementById('license-form'));
        } else {
            alert('<?php echo $this->escape(JText::_('COM_JDOWNLOADS_VALIDATION_FORM_FAILED'));?>');
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jdownloads&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="license-form" class="form-validate">
     
    <?php echo JLayoutHelper::render('edit.title_license', $this, $basePath); ?>
    
    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_JDOWNLOADS_GENERAL')); ?>
        <div class="row-fluid">
            <div class="span9">
                 <?php echo $this->form->getLabel('description'); ?>
                 <div class="clr"></div> 
                 <?php echo $this->form->getInput('description'); ?>
            </div>
            <div class="span3">
                <?php echo JLayoutHelper::render('edit.global', $this, $basePath); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="view" value="license" />         
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>    
    
