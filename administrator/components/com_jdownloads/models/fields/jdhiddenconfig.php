<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('hidden');

class JFormFieldjdHiddenConfig extends JFormFieldHidden
{
     /**
     * The form field type.
     *
     * @var string
     */
     protected $type = 'jdhiddenconfig';
     
     protected function getInput()
     {
         $params = JComponentHelper::getParams( 'com_jdownloads' );
         $name = $this->element['name'];
        
         switch ($name[0]){
            case 'root_dir':
                if ($params->get( 'files_uploaddir' )){
                    $default = $params->get( 'files_uploaddir' );
                } else {
                    $default = JPATH_ROOT.DS.'jdownloads';
                }
                break;
            case 'preview_dir':
                if ($params->get( 'preview_files_folder_name' )){
                    $default = $params->get( 'preview_files_folder_name' );
                } else {
                    $default = '_preview_files';
                }
                break;
            case 'temp_dir':
                if ($params->get( 'tempzipfiles_folder_name' )){
                    $default = $params->get( 'tempzipfiles_folder_name' );
                } else {
                    $default = '_tempzipfiles';
                }
                break;
            case 'help_url':
                $default = 'https://www.jdownloads.net/index.php?option=com_content&view=article&id=';
                break;
         }
         
         $this->value = $default;
         
         $text = parent::getInput();
         
         return $text;
    }
    
}    
?>