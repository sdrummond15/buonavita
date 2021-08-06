<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('filelist');


/**
 * Supports an HTML select list of articles
 * @since 1.6
 */
class JFormFieldjdfileiconconfig1 extends JFormFieldFileList
{
     /**
     * The form field type.
     *
     * @var string
     * @since 1.6
     */
     protected $type = 'jdfileiconconfig1';

     protected function getInput()
     {
     
        $params = JComponentHelper::getParams( 'com_jdownloads' );
        $width  = $params->get( 'file_pic_size' );
        $height = $params->get( 'file_pic_size_height' );        
         
        $pic_dir_path = JURI::root().'images/jdownloads/fileimages/flat_1/';
        
        // add javascript
        $this->onchange = "javascript:if (document.adminForm.jform_file_pic_default_filename1.options[selectedIndex].value != '-1') {document.imagelib21.src='$pic_dir_path' + document.adminForm.jform_file_pic_default_filename1.options[selectedIndex].value} else {document.imagelib21.src=''}";
        
        // create icon select box for the symbol
        $pic_list = parent::getInput();
        
        // add javascript
        $pic_list .= '<script language="javascript" type="text/javascript">'."
            if (document.adminForm.jform_file_pic_default_filename1.options.value != '-1'){
                jsimg=\"".JURI::root().'images/jdownloads/fileimages/flat_1/'."\" + getSelectedText( 'adminForm', 'jform_file_pic_default_filename1' );
            } else {
                jsimg='';
            }
            document.write('<p style=\"margin-top:15px;\"><img src=' + jsimg + ' name=\"imagelib21\" width=\"".$width."\" height=\"".$height."\" border=\"1\" alt=\"\" /></p>');
           </script>";
        
        return $pic_list;
    }  
}    
     
?>