<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

class JFormFieldjdGDStatusConfig extends JFormFieldNote
{
     /**
     * The form field type.
     *
     * @var string
     */
     protected $type = 'jdgdstatusconfig';

     protected function getLabel()
     {
        
        if (function_exists('gd_info')){
            $text = '<span class="label label-success">'.JText::_('COM_JDOWNLOADS_CONFIG_SETTINGS_THUMBS_STATUS_GD_OK').'</span>';
        } else {
            $text = '<span class="label label-important">'.JText::_('COM_JDOWNLOADS_CONFIG_SETTINGS_THUMBS_STATUS_GD_NOT_OK').'</span>';
        } 
        
        return $text;
    }  
}    
?>