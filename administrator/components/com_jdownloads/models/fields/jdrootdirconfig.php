<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('text');

class JFormFieldjdRootDirConfig extends JFormFieldText
{
     /**
     * The form field type.
     *
     * @var string
     */
     protected $type = 'jdRootDirConfig';

     protected function getInput()
     {
        $params = JComponentHelper::getParams( 'com_jdownloads' );
        $path  = $params->get( 'files_uploaddir' );

        if (!$path){
            $this->value = JPATH_ROOT.'/jdownloads';
        }
        
        $text = parent::getInput();
         
        return $text;
    }  
}    
?>