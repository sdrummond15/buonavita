<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('filelist');


/**
 * Supports an HTML select list of articles
 * @since 1.6
 */
class JFormFieldjdfileiconconfig2 extends JFormFieldFileList
{
     /**
     * The form field type.
     *
     * @var string
     * @since 1.6
     */
     protected $type = 'jdfileiconconfig2';

     protected function getInput()
     {
     
        $params = JComponentHelper::getParams( 'com_jdownloads' );
        $width  = $params->get( 'file_pic_size' );
        $height = $params->get( 'file_pic_size_height' );        
         
        $pic_dir_path = JURI::root().'images/jdownloads/fileimages/flat_2/';
        
        // add javascript
        $this->onchange = "javascript:if (document.adminForm.jform_file_pic_default_filename2.options[selectedIndex].value != '-1') {document.imagelib22.src='$pic_dir_path' + document.adminForm.jform_file_pic_default_filename2.options[selectedIndex].value} else {document.imagelib22.src=''}";
        
        // create icon select box for the symbol
        $pic_list = parent::getInput();
        
        // add javascript
        $pic_list .= '<script language="javascript" type="text/javascript">'."
            if (document.adminForm.jform_file_pic_default_filename2.options.value != '-1'){
                jsimg=\"".JURI::root().'images/jdownloads/fileimages/flat_2/'."\" + getSelectedText( 'adminForm', 'jform_file_pic_default_filename2' );
            } else {
                jsimg='';
            }
            document.write('<p style=\"margin-top:15px;\"><img src=' + jsimg + ' name=\"imagelib22\" width=\"".$width."\" height=\"".$height."\" border=\"1\" alt=\"\" /></p>');
           </script>";
        
        return $pic_list;
    }  
}    
     
?>