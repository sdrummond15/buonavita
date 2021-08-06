<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

class JFormFieldjdImagickStatusConfig extends JFormFieldNote
{
     /**
     * The form field type.
     *
     * @var string
     */
     protected $type = 'jdimagickstatusconfig';

     protected function getLabel()
     {
        $text = parent::getLabel();
        
        if (extension_loaded('imagick')){
            $text .= '<div><span class="label label-success">'.JText::_('COM_JDOWNLOADS_BACKEND_IMAGICK_STATE_ON').'</span></div>';
        } else {
            $text .= '<div><span class="label label-important">'.JText::_('COM_JDOWNLOADS_BACKEND_IMAGICK_STATE_OFF').'</span></div>';
        }
        
        return $text;
    }  
}    
?>