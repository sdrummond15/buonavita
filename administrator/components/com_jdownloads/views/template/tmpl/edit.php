<?php 

defined('_JEXEC') or die('Restricted access'); 

use Joomla\CMS\HTML\HTMLHelper;

    JHtml::_('bootstrap.tooltip');
    
    HTMLHelper::_('behavior.keepalive');
    HTMLHelper::_('behavior.formvalidator');    
    HTMLHelper::_('formbehavior.chosen', 'select');

    $app       = JFactory::getApplication();
    $jinput    = $app->input;
    $type      = $jinput->get('type'); 

    if ($this->item->template_typ == NULL ){
        // add a new layout - so we need the layout type number 
        $session = JFactory::getSession();
        $this->item->template_typ = (int) $session->get( 'jd_tmpl_type', '' );
    }

    // Path to the layouts folder 
    $basePath = JPATH_ROOT .'/administrator/components/com_jdownloads/layouts';

    $this->hiddenFieldsets  = array();

?>

<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'template.cancel' || document.formvalidator.isValid(document.getElementById('template-form'))) {
            <?php // echo $this->form->getField('template_text')->save(); ?>
            Joomla.submitform(task, document.getElementById('template-form'));
        }
        else {
            alert('<?php echo $this->escape(JText::_('COM_JDOWNLOADS_VALIDATION_FORM_FAILED'));?>');
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jdownloads&view=template&layout=edit&id='.(int) $this->item->id.'&type='.(int)$this->item->template_typ); ?>" method="post" name="adminForm" id="template-form" accept-charset="utf-8" class="form-validate">
    
    <?php echo JLayoutHelper::render('edit.title_type', $this, $basePath); ?>
    
    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_TABTEXT_EDIT_MAIN')); ?>
            <div class="row-fluid">
                <div class="span9">
                    <fieldset class="adminform">
                        
                        <?php if($this->item->template_typ == 1 || $this->item->template_typ == 2 || $this->item->template_typ == 4 || $this->item->template_typ == 8) { ?> 
                            <div style="margin-bottom: 10px;">
                                <?php echo $this->form->getLabel('template_before_text'); ?>
                                <div class="clr"></div> 
                                <?php echo $this->form->getInput('template_before_text'); ?>       
                            </div>
                            <div class="clr"></div>
                        <?php } ?>
                        
                        <div>
                            <?php
                                if ($this->item->template_typ == 7){
                                    $this->form->setFieldAttribute( 'template_text', 'description', '' ); 
                                } 
                                echo $this->form->getLabel('template_text'); 
                            ?>
                            <div class="clr"></div> 
                            <?php echo $this->form->getInput('template_text'); ?>       
                        </div>                

                        <?php if($this->item->template_typ == 1 || $this->item->template_typ == 2 || $this->item->template_typ == 4 || $this->item->template_typ == 8) { ?> 
                            <div>
                                <?php echo $this->form->getLabel('template_after_text'); ?>
                                <div class="clr"></div>
                                <?php echo $this->form->getInput('template_after_text'); ?>       
                            </div>            
                        <?php } ?>
                            
                    </fieldset>
                </div>
                <div class="span3" style="min-width: 220px;">
                    <!-- Add the right panel with basicly data: ID, locked, template_active, cols, note, symbol_off, checkbox_off  -->
                    <?php echo JLayoutHelper::render('edit.global_template', $this, $basePath); ?>
                    
                    <?php echo JHtml::_('sliders.start', 'jdlayout-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
                    <?php echo JHtml::_('sliders.panel', JText::_('COM_JDOWNLOADS_HELP_INFORMATIONS'), 'help-details'); ?>
                    <fieldset class="panelform alert alert-info">
                        <ul class="adminformlist">
                            <li>
                            <?php if($this->item->template_typ == 1) {      // Categories
                                    ?>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_DESC'); ?></p>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_DESC3'); ?></p>
                            <?php } ?>    

                            <?php if($this->item->template_typ == 2) {      // Files/Downloads
                                    ?>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DESC'); ?></p>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FILES_DESC2'); ?></p>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_INFO_LIGHTBOX'); ?></p>
                            <?php } ?>                 

                            <?php if($this->item->template_typ == 3) {    // Summary
                                    ?>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_FINAL_DESC'); ?></p>
                            <?php } ?>                 
                            
                            <?php if($this->item->template_typ == 4) {      // Category
                                    ?>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CAT_DESC'); ?></p>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_DESC2'); ?></p> 
                            <?php } ?>                 
                            
                            <?php if($this->item->template_typ == 5) {      // Details View
                                    ?>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_DESC'); ?></p>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_DETAILS_DESC_FOR_TABS'); ?></p>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_INFO_LIGHTBOX'); ?></p> 
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_INFO_LIGHTBOX2'); ?></p>                    
                            <?php } ?>                 
                            
                            <?php if($this->item->template_typ == 6) {      // Upload Form
                                    ?>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_UPLOADS_DESC'); ?></p>
                            <?php } ?>                 
                            
                            <?php if($this->item->template_typ == 7) {      // Search Result
                                    ?>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_SEARCH_DESC'); ?></p>
                            <?php } ?>
                            
                            <?php if($this->item->template_typ == 8) {      // SubCategories
                                    ?>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_SUB_CATS_DESC'); ?></p>
                                    <p><?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_CATS_DESC3'); ?></p>
                            <?php } ?>                 
                            
                            <?php echo '<p>'.JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_TEMPLATES_TAG_TIP').'</p>'; ?>
                            
                            </li>   
                        </ul>
                    </fieldset>    
                    <?php echo JHtml::_('sliders.end'); ?>
                    
                    
                    
                </div>
            </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        
        <?php if ($this->item->template_typ != 8){ ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'header', JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_TABTEXT_EDIT_HEADER')); ?>
                <div class="row-fluid">
                    <div class="span9">
                        <fieldset class="adminform">
                            <div style="margin-bottom: 10px;">
                                <?php echo $this->form->getLabel('template_header_text'); ?>
                                <div class="clr"></div> 
                                <?php echo $this->form->getInput('template_header_text'); ?>       
                            </div>
                            
                            <div style="margin-bottom: 10px;">
                                <?php 
                                    if ($this->item->template_typ == 7){
                                        $this->form->setFieldAttribute( 'template_subheader_text', 'description', JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_SUBHEADER_SEARCH_DESC') ); 
                                    }
                                
                                    echo $this->form->getLabel('template_subheader_text'); 
                                ?>
                                
                                <div class="clr"></div>
                                <?php echo $this->form->getInput('template_subheader_text'); ?>       
                            </div>                         
                            
                            <div style="margin-bottom: 10px;">
                                <?php 
                                    if ($this->item->template_typ == 7){
                                        $this->form->setFieldAttribute( 'template_footer_text', 'description', JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_FOOTER_OTHER_DESC') );
                                    }

                                    echo $this->form->getLabel('template_footer_text'); 
                                ?>
                                    
                                <div class="clr"></div>
                                <?php echo $this->form->getInput('template_footer_text'); ?>       
                            </div>
                        </fieldset>
                    </div>
                    <div class="span3">
                        <?php echo JHtml::_('sliders.start', 'jdlayout-sliders-2'.$this->item->id, array('useCookie'=>1)); ?>
                        <?php echo JHtml::_('sliders.panel', JText::_('COM_JDOWNLOADS_HELP_INFORMATIONS'), 'help-details2'); ?>
                        <fieldset class="panelform alert alert-info">
                            <ul class="adminformlist">
                                <li>
                                      <?php echo '<p><b>'.JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_HEADER_TEXT').'</b>:<br />';
                                            echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_HEADER_DESC').'</p>'; ?>
                                             
                                      <?php echo '<p><b>'.JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_SUBHEADER_TEXT').'</b>:<br />';
                                            switch ($this->item->template_typ) {
                                                case 1:  //cats
                                                case 2:  //files
                                                case 4:  //cat
                                                    echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_SUBHEADER_DESC').'</p>';
                                                    break;
                                                case 5:  //details                                   
                                                    echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_SUBHEADER_DETAIL_DESC').'</p>';
                                                    break;                                     
                                                case 3:  //summary                                   
                                                    echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_SUBHEADER_SUMMARY_DESC').'</p>';
                                                    break;
                                                case 6:  //upload form                                   
                                                    echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_SUBHEADER_DESC').'</p>';
                                                    break;                                    
                                                case 7:  //search results                                   
                                                    echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_SUBHEADER_SEARCH_DESC').'</p>';
                                                    break;                                    
                                            } ?>                       
                                      <?php echo '<p><b>'.JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_FOOTER_TEXT').'</b>:<br />';
                                            switch ($this->item->template_typ) {
                                                case 1:  //cats
                                                case 2:  //files
                                                case 4:  //cat
                                                    echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_FOOTER_FILES_CATS_DESC').'</p>';
                                                    break;
                                                default:  //other types                                   
                                                    echo JText::_('COM_JDOWNLOADS_BACKEND_TEMPEDIT_FOOTER_OTHER_DESC').'</p>';
                                                    break;
                                            } ?>
                                </li>   
                            </ul>
                        </fieldset>    
                        <?php echo JHtml::_('sliders.end'); ?>
                    </div>    
                        
                </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>                        
            <?php echo JHtml::_('bootstrap.endTabSet'); ?>                        
        <?php } ?>                
    </div>                
            
    <?php echo JHtml::_('tabs.end'); ?>

    <input type="hidden" name="task" value="" />
    
    <input type="hidden" name="hidemainmenu" value="0">        
    <input type="hidden" name="templocked" value="<?php echo $this->item->locked; ?>">
    <input type="hidden" name="tempname" value="<?php echo $this->item->template_name; ?>">
    <input type="hidden" name="type" value="<?php echo $this->item->template_typ; ?>">
    <input type="hidden" name="view" value="" />
    <?php echo JHtml::_('form.token'); ?>
    
    <div class="clr"></div>    
</form>
    
