<?php

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;

    JHtml::_('bootstrap.tooltip');
    
    HTMLHelper::_('behavior.keepalive');
    HTMLHelper::_('behavior.formvalidator');    
    HTMLHelper::_('behavior.calendar');
    HTMLHelper::_('behavior.tabstate');
    HTMLHelper::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0 ));

?>
    
<form action="index.php" method="post" name="adminForm" id="adminForm">
    
    <?php if (!empty( $this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
                     </div>
        <div id="j-main-container" class="span10">
    <?php else : ?>
        <div id="j-main-container">
    <?php endif;?>

        <div class="span12">
            <div class="span12">
                <div class="alert alert-info">
                    <?php echo  JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_HEAD'); ?>
                    <?php echo  JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_HEAD_INFO').JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_HEAD_INFO2'); ?>
                </div>
            </div>
        </div>
    </div>    

    <input type="hidden" name="option" value="com_jdownloads" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="controller" value="layouts" />
</form>    
