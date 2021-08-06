<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.html.html');
jimport('joomla.form.formfield');


/**
 * Supports an HTML select list of articles
 * @since 1.6
 */
class JFormFieldjdfileicon extends JFormField
{
     /**
     * The form field type.
     *
     * @var string
     * @since 1.6
     */
     protected $type = 'jdfileicon';

     protected function getInput()
     {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );        
         
        // Path to the mime type image folder (for file symbols) 
        switch ($params->get('selected_file_type_icon_set'))
        {
            case 1:
                $file_pic_folder = 'images/jdownloads/fileimages/';
                $file_pic_default_filename = $params->get('file_pic_default_filename');
                break;
            case 2:
                $file_pic_folder = 'images/jdownloads/fileimages/flat_1/';
                $file_pic_default_filename = $params->get('file_pic_default_filename1');
                break;
            case 3:
                $file_pic_folder = 'images/jdownloads/fileimages/flat_2/';
                $file_pic_default_filename = $params->get('file_pic_default_filename2');
                break;
        }
        
        // create icon select box for file (mime) symbol        
        $pic_dir = '/'.$file_pic_folder;
        $pic_dir_path = JURI::root().$file_pic_folder;
        $pic_files = JFolder::files( JPATH_SITE.$pic_dir );
        $pic_list[] = JHtml::_('select.option', '', JText::_('COM_JDOWNLOADS_BACKEND_SETTINGS_FRONTEND_FPIC_TEXT'));
        foreach ($pic_files as $file) {
            if (@preg_match( "/(gif|jpg|png)/i", $file )){
                $pic_list[] = JHtml::_('select.option',  $file );
            }
        } 
        
        // use the default icon when is selected in params (and use symbols is active and we have a new Download)
        $pic_default = '';
        $pic_default = $this->form->getValue('file_pic');
        if ($pic_default === NULL){
            $pic_default = $this->value;
        }
        if ($params->get('use_file_type_symbols') && $file_pic_default_filename && !$pic_default && !$this->form->getValue('id')) {
            $pic_default = $file_pic_default_filename;
        }    
      
        $inputbox_pic = JHtml::_('select.genericlist', $pic_list, 'file_pic', "class=\"inputbox\" size=\"1\""
      . " onchange=\"javascript:if (document.adminForm.file_pic.options[selectedIndex].value!='') {document.imagelib.src='$pic_dir_path' + document.adminForm.file_pic.options[selectedIndex].value} else {document.imagelib.src=''}\"", 'value', 'text', $pic_default );
          
        return $inputbox_pic;
    }  
}    
     
?>