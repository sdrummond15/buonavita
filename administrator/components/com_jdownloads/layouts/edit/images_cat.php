<?php

defined('JPATH_BASE') or die;

    $options = $this->options;
    $form = $displayData->getForm();
    
    echo $form->renderField('pic'); ?>
    <script language="javascript" type="text/javascript">
        if (document.adminForm.pic.options.value!=''){
            jsimg="<?php echo JURI::root().'images/jdownloads/catimages/'; ?>" + getSelectedText( 'adminForm', 'pic' );
        } else {
            jsimg='';
        }
        document.write('<div class="controls" style="margin-bottom:15px;"><img src=' + jsimg + ' name="imagelib" width="<?php echo $options['cat_pic_size']; ?>" height="<?php echo $options['cat_pic_size']; ?>" border="1" alt="<?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_DEFAULT_CAT_FILE_NO_DEFAULT_PIC'); ?>" /></div>');
    </script>

    <?php echo $form->renderField('picnew'); 
    

         
?>