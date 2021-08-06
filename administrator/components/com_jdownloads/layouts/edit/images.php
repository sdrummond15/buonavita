<?php

defined('JPATH_BASE') or die;

    $options = $this->options;
    $form = $displayData->getForm();
    
    $file_pic_folder = $options['file_pic_folder'];
    
    $default = $options['file_pic'];
    
    echo $form->renderField('file_pic', null, $default);
    
    ?>
    <script type="text/javascript">
        if (document.adminForm.file_pic.options.value!=''){
            jsimg="<?php echo JURI::root().$file_pic_folder; ?>" + getSelectedText( 'adminForm', 'file_pic' );
        } else {
            jsimg='';
        }
        document.write('<div class="controls" style="margin-bottom:15px;"><img src=' + jsimg + ' name="imagelib" width="<?php echo $options['file_pic_size']; ?>" height="<?php echo $options['file_pic_size']; ?>" border="1" alt="<?php echo JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_DEFAULT_CAT_FILE_NO_DEFAULT_PIC'); ?>" /></div>');
    </script>

    <?php echo $form->renderField('picnew'); ?>
    
    <?php echo $form->renderField('spacer'); ?>
    
        <?php 
        $image_id = 0;
        if ($options['images']){ ?>    
            <table class="admintable" width="100%" border="0" cellpadding="0" cellspacing="10">
            <tr><td><?php if ($options['images']) echo JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_THUMBNAIL_REMOVE'); ?></td></tr>
            <tr>
            <td valign="top">
            <?php 
            // display the selected images
            
            if ($options['images']){
                $images = array();
                $images = explode("|", $options['images']);
                echo '<ul style="list-style-type: none; margin: 0px 0 0 0; padding: 0; width: 250px; overflow: visible;" id="displayimages">';
                foreach ($images as $image){
                     $image_id ++;
                     echo '<li id="'.$image.'">';
                     echo '<input style="position:relative;
                            left: 7px;
                            top: 15px;
                            vertical-align: top;
                            z-index: 1;
                            margin: 0;
                            padding: 0;" type="checkbox" name="keep_image['.$image_id.']" value="'.$image.'" checked />';
                     echo '<a href="'.JURI::root().'images/jdownloads/screenshots/'.$image.'" target="_blank">';
                     
                     echo '<img border="0" style="position:relative;border:1px solid black; max-width:100px; max-height:100px;" align="middle" src="'.JURI::root().'images/jdownloads/screenshots/thumbnails/'.$image.'" alt="'.$image.'" title="'.$image.'" />';
                     echo '</a>';
                     echo '</li>';                         
                }
                echo '</ul>'; 
            }
            ?>
            </td>
            </tr>
            </table>                
            
        <?php } ?>
             
        <?php 
        if ($image_id < $options['be_upload_amount_of_pictures']){ ?>
            <div class="control-group">
                <div class="control-label">
                    <?php  echo JHtml::_('tooltip', JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_THUMBNAIL_UPLOAD_DESC'), JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_THUMBNAIL_UPLOAD_TITLE').'<br /><small>'.JText::sprintf('COM_JDOWNLOADS_BACKEND_FILESEDIT_THUMBNAIL_UPLOAD_DESC_LIMIT', $options['be_upload_amount_of_pictures']).'</small>', '', JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_THUMBNAIL_UPLOAD_TITLE').'<br /><small>'.JText::sprintf('COM_JDOWNLOADS_BACKEND_FILESEDIT_THUMBNAIL_UPLOAD_DESC_LIMIT', $options['be_upload_amount_of_pictures']).'</small>' ); ?>
                </div>
                <div class="controls">
                    <table id="files_table" class="admintable" border="0" cellpadding="0" cellspacing="10">
                    <tr id="new_file_row">
                    <td class=""><input type="file" name="file_upload_thumb[0]" id="file_upload_thumb[0]" size="40" accept="image/gif,image/jpeg,image/jpg,image/png" onchange="add_new_image_file(this)" />
                    </td>
                    </tr>
                    <tr><td><?php echo '<small>'.JText::_('COM_JDOWNLOADS_UPLOAD_MAX_FILESIZE_INFO_TITLE').' '.($options['ini_upload_max_filesize'] / 1024).' KB</small>'; ?></td></tr>
                    </table> 
                </div>
            </div>
         <?php
         } else { 
                // limit is reached - display a info message 
                echo '<p>'.JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_THUMBNAIL_LIMIT_REACHED').'</p>'; 
         }
         
?>