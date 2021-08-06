<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('filelist');


/**
 * Supports an HTML select list of articles
 * @since 1.6
 */
class JFormFieldjdcaticonconfig extends JFormFieldFileList
{
     /**
     * The form field type.
     *
     * @var string
     * @since 1.6
     */
     protected $type = 'jdcaticonconfig';

     protected function getInput()
     {
     
        $script = "        
        // get the selected file name to view the file type pic new
        function getSelectedText( frmName, srcListName ) 
        {
            var form = eval( 'document.' + frmName );
            var srcList = eval( 'form.' + srcListName );

            i = srcList.selectedIndex;
            if (i != null && i > -1) {
                return srcList.options[i].text;
            } else {
                return null;
            }
        };
        "; 
        
        $document = JFactory::getDocument();
        $document->addScriptDeclaration($script);
        
        $params = JComponentHelper::getParams( 'com_jdownloads' );
        $width  = $params->get( 'cat_pic_size' );
        $height = $params->get( 'cat_pic_size_height' );
        
        $pic_dir_path = JURI::root().'images/jdownloads/catimages/';
        
        // add javascript
        $this->onchange = "javascript:if (document.adminForm.jform_cat_pic_default_filename.options[selectedIndex].value != '-1') {document.imagelib.src='$pic_dir_path' + document.adminForm.jform_cat_pic_default_filename.options[selectedIndex].value} else {document.imagelib.src=''}";
        
        // create icon select box for the symbol
        $pic_list = parent::getInput();
        
        // add javascript
        $pic_list .= '<script language="javascript" type="text/javascript">'."
            if (document.adminForm.jform_cat_pic_default_filename.options.value != '-1'){
                jsimg=\"".JURI::root().'images/jdownloads/catimages/'."\" + getSelectedText( 'adminForm', 'jform_cat_pic_default_filename' );
            } else {
                jsimg='';
            }
            document.write('<p style=\"margin-top:15px;\"><img src=' + jsimg + ' name=\"imagelib\" width=\"".$width."\" height=\"".$height."\" border=\"1\" alt=\"\" /></p>');
           </script>";
        
        return $pic_list;
    }  
}    
     
?>