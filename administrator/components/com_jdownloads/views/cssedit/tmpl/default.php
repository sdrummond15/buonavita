<?php
/**
 * @package jDownloads
 * @version 2.0  
 * @copyright (C) 2007 - 2012 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
 
defined('_JEXEC') or die('Restricted access');

    JHtml::_('bootstrap.tooltip');
?>

<form action="<?php echo JRoute::_('index.php?option=com_jdownloads');?>" method="post" name="adminForm" id="adminForm">
    
    <div>
        <fieldset style="background-color: #ffffff;" class="uploadform">
            <legend style="margin-bottom:5px;"><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_INFO_TITLE'); ?></legend>
            <div class="alert alert-info"><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_INFO_1'); ?></div> 
			<div class="alert alert-info"><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_INFO_4'); ?></div>			
            <div class="alert alert-info"><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_INFO_2'); ?></div> 
            <div class="alert alert-info"><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_INFO_3'); ?></div> 
        </fieldset>
    </div>

        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'default')); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'default', 'jdownloads_fe.css'); ?>
            <div class="row-fluid">
                <div class="span9">
                    <fieldset class="adminform">
                         <label id="csstext-lbl" class="" title="" for="csstext">
                         <strong><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_FIELD_TITLE').':</strong><span style="color:darkred;"> '.$this->cssfile; ?></span><br />
                         
                         <small><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_WRITE_STATUS_TEXT')." ";
                            if ($this->cssfile_writable) {
                                echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_YES');
                            } else {
                                echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_NO'); ?>
                                <br /><strong>
                                <?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_INFO'); ?></strong><br />
                        <?php } ?></small>
                        </label>
                        <textarea class="input_box" name="cssfile" cols="100" rows="20"><?php echo $this->csstext; ?></textarea>
                    </fieldset>
                </div>
            </div>        
        <?php echo JHtml::_('bootstrap.endTab'); ?>
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'rtl', 'jdownloads_fe_rtl.css'); ?>
			<div class="row-fluid">
                <div class="span9">
                    <fieldset class="adminform">
                        <label id="csstext-lbl" class="" title="" for="csstext4">
                             <strong><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_FIELD_TITLE').':</strong><span style="color:darkred;"> '.$this->cssfile4; ?></span><br />
                             
                             <small><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_WRITE_STATUS_TEXT')." ";
                                if ($this->cssfile_writable4) {
                                    echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_YES');
                                } else {
                                    echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_NO'); ?>
                                    <br /><strong>
                                    <?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_INFO'); ?></strong><br />
                            <?php } ?></small>
                            
                            </label>
                        <textarea class="input_box" name="cssfile4" cols="100" rows="20"><?php echo $this->csstext4; ?></textarea>
                    </fieldset>
                </div>
            </div>      
        <?php echo JHtml::_('bootstrap.endTab'); ?>
    
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'buttons', 'jdownloads_buttons.css'); ?>
            <div class="row-fluid">
                <div class="span9">
                    <fieldset class="adminform">
                        <label id="csstext-lbl" class="" title="" for="csstext2">
                             <strong><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_FIELD_TITLE').':</strong><span style="color:darkred;"> '.$this->cssfile2; ?></span><br />
                             
                             <small><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_WRITE_STATUS_TEXT')." ";
                                if ($this->cssfile_writable2) {
                                    echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_YES');
                                } else {
                                    echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_NO'); ?>
                                    <br /><strong>
                                    <?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_INFO'); ?></strong><br />
                            <?php } ?></small>
                            
                            </label>
                        <textarea class="input_box" name="cssfile2" cols="100" rows="20"><?php echo $this->csstext2; ?></textarea>
                    </fieldset>
                </div>
            </div>        
        <?php echo JHtml::_('bootstrap.endTab'); ?>            
    
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'custom', 'jdownloads_custom.css'); ?>
            <div class="row-fluid">
                <div class="span9">
                    <fieldset class="adminform">
                         <label id="csstext-lbl" class="" title="" for="csstext3">
                         <strong><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_FIELD_TITLE').':</strong><span style="color:darkred;"> '.$this->cssfile3; ?></span><br />
                         
                         <small><?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_CSS_WRITE_STATUS_TEXT')." ";
                            if ($this->cssfile_writable3) {
                                echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_YES');
                            } else {
                                echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_NO'); ?>
                                <br /><strong>
                                <?php echo JText::_('COM_JDOWNLOADS_BACKEND_EDIT_LANG_CSS_FILE_WRITABLE_INFO'); ?></strong><br />
                        <?php } ?></small>
                        
                        </label>
                        <textarea class="input_box" name="cssfile3" cols="100" rows="20"><?php echo $this->csstext3; ?></textarea>
                    </fieldset>
                </div>
            </div>        
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php echo JHtml::_('bootstrap.endTabSet'); ?>                                                
    
    <input type="hidden" name="option" value="com_jdownloads" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="view" value="cssedit" />
    <input type="hidden" name="hidemainmenu" value="0" />
    
    <?php echo JHtml::_('form.token'); ?>
   </form>
