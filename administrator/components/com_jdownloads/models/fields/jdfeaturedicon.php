<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.html.html');
jimport('joomla.form.formfield');


/**
 * Supports an HTML select list of articles
 * @since 1.6
 */
class JFormFieldjdFeaturedIcon extends JFormField
{
     /**
     * The form field type.
     *
     * @var string
     * @since 1.6
     */
     protected $type = 'jdfeaturedicon';

     protected function getInput()
     {
        $params = JComponentHelper::getParams( 'com_jdownloads' );
        $symbol = $params->get( 'featured_pic' );
        $width  = $params->get( 'featured_pic_size' );
        $height = $params->get( 'featured_pic_size_height' );

        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );        
         
        // create icon select box for the symbol
        $pic_dir = '/images/jdownloads/featuredimages/';
        $pic_dir_path = JURI::root().'images/jdownloads/featuredimages/';
        $pic_files = JFolder::files( JPATH_SITE.$pic_dir );
        $pic_list[] = JHtml::_('select.option', '', JText::_('COM_JDOWNLOADS_SELECT_FEATURED_SYMBOL'));
        foreach ($pic_files as $file) {
            if (@preg_match( "/(gif|jpg|png)/i", $file )){
                $pic_list[] = JHtml::_('select.option',  $file );
            }
        } 
        
        // use the default icon when is selected in configuration
        $pic_default = '';
        $pic_default = $this->form->getValue('featured_pic');
        if ($symbol && !$pic_default) {
            $pic_default = $symbol;
        }    
      
        $inputbox_pic = JHtml::_('select.genericlist', $pic_list, 'featured_pic', "class=\"inputbox\" size=\"1\""
      . " onchange=\"javascript:if (document.adminForm.featured_pic.options[selectedIndex].value!='') {document.imagelib.src='$pic_dir_path' + document.adminForm.featured_pic.options[selectedIndex].value} else {document.imagelib.src=''}\"", 'value', 'text', $pic_default );
          
     $inputbox_pic .= '<script language="javascript" type="text/javascript">'."
                if (document.adminForm.featured_pic.options.value != ''){
                    jsimg='".JURI::root().'images/jdownloads/featuredimages/'."' + getSelectedText( 'adminForm', 'featured_pic' );
                } else {
                    jsimg='';
                }
                document.write('<img src=' + jsimg + ' name=\"imagelib\" width=\"".$width."\" height=\"".$height."\" border=\"1\" alt=\"".JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_DEFAULT_CAT_FILE_NO_DEFAULT_PIC')."\" />');
               </script>";
      
      
     return $inputbox_pic;
    
    }  
}    
     
?>