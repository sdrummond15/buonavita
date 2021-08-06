<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('filelist');


/**
 * Supports an HTML select list of articles
 * @since 1.6
 */
class JFormFieldjdFeaturedIconconfig extends JFormFieldFileList
{
     /**
     * The form field type.
     *
     * @var string
     * @since 1.6
     */
     protected $type = 'jdfeaturediconconfig';

     protected function getInput()
     {
        $params = JComponentHelper::getParams( 'com_jdownloads' );
        $width  = $params->get( 'featured_pic_size' );
        $height = $params->get( 'featured_pic_size_height' );

        $pic_dir_path = JURI::root().'images/jdownloads/featuredimages/';
      
        // add javascript
        $this->onchange = "javascript:if (document.adminForm.jform_featured_pic_filename.options[selectedIndex].value != '-1') {document.imagelib3.src='$pic_dir_path' + document.adminForm.jform_featured_pic_filename.options[selectedIndex].value} else {document.imagelib3.src=''}";
        
        // create icon select box for the symbol
        $pic_list = parent::getInput();
        
        // add javascript
        $pic_list .= '<script language="javascript" type="text/javascript">'."
            if (document.adminForm.jform_featured_pic_filename.options.value != '-1'){
                jsimg=\"".JURI::root().'images/jdownloads/featuredimages/'."\" + getSelectedText( 'adminForm', 'jform_featured_pic_filename' );
            } else {
                jsimg='';
            }
            document.write('<p style=\"margin-top:15px;\"><img src=' + jsimg + ' name=\"imagelib3\" width=\"".$width."\" height=\"".$height."\" border=\"1\" alt=\"\" /></p>');
           </script>";
        
        return $pic_list;    
    }  
}    
     
?>