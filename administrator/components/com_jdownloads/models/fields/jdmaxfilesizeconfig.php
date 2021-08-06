<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

class JFormFieldjdMaxFileSizeConfig extends JFormFieldNote
{
     /**
     * The form field type.
     *
     * @var string
     */
     protected $type = 'jdmaxfilesizeconfig';

     protected function getLabel()
     {
        $text = parent::getLabel();
                  
        $text = '<small>'.$text.' '.ini_get('upload_max_filesize').'</small>';
         
        return $text;
    }  
}    
?>