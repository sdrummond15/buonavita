<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

class JFormFieldjdPlaceholderImageConfig extends JFormFieldNote
{
     /**
     * The form field type.
     *
     * @var string
     */
     protected $type = 'jdplaceholderimageconfig';

     protected function getInput()
     {
         $params = JComponentHelper::getParams( 'com_jdownloads' );
         $sizeh  = $params->get( 'thumbnail_size_height' );
         $sizew  = $params->get( 'thumbnail_size_width' );
         
         $image_path = JURI::root().'images/jdownloads/screenshots/thumbnails/no_pic.gif'; 
         
         $text = parent::getInput();
        
         $text .= '<p style="padding-top:5px;"><img src="'.$image_path.'" align="middle" width="'.$sizew.'" height="'.$sizeh.'" border="0" alt="" /> ';
        
         return $text;
    }  
}    
?>