<?php
/**
 * @package jDownloads
 * @version 4.0  
 * @copyright (C) 2007 - 2016 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
 
defined('_JEXEC') or die;

jimport( 'joomla.application.component.view' );
jimport( 'joomla.application.application' );

/**
 * View to edit a Download 
 * 
 * 
 **/

 class jdownloadsViewDownload extends JViewLegacy
{
    protected $state;
    protected $item;
    protected $form;
    protected $canDo;
    
    /**
     * Display the view
     * 
     * 
     */
    public function display($tpl = null)
    {
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';  
        
        $app = JFactory::getApplication();
        $app->setUserState('type', 'download');
        
        $this->form        = $this->get('Form');
        $this->item        = $this->get('Item');
        $this->state       = $this->get('State');        
        
        // What Access Permissions does this user have? What can (s)he do?
        $this->canDo = jDownloadsHelper::getActions($this->item->id, 'component');        
        
        // get filename when selected in files list
        $session = JFactory::getSession();
        $filename = $session->get('jd_filename');
        if ($filename){
            $this->selected_filename =  JFilterOutput::cleanText($filename);
        }    

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }
        
        // Added to support the Joomla Language Associations
        // If we are forcing a language in modal (used for associations).
        if ($this->getLayout() === 'modal' && $forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'cmd'))
        {
            // Set the language field to the forcedLanguage and disable changing it.
            $this->form->setValue('language', null, $forcedLanguage);
            $this->form->setFieldAttribute('language', 'readonly', 'true');

            // Only allow to select categories with All language or with the forced language.
            $this->form->setFieldAttribute('catid', 'language', '*,' . $forcedLanguage);

            // Only allow to select tags with All language or with the forced language.
            $this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
        }
        
        $this->addToolbar();
        parent::display($tpl);
    }		
    
    /**
     * Add the page title and toolbar.
     *
     * 
     */
    protected function addToolbar()
    {
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';  
        
        $params = JComponentHelper::getParams('com_jdownloads');
        
        JRequest::setVar('hidemainmenu', true);  
        
        $user        = JFactory::getUser();
        $isNew       = ($this->item->id == 0);
        $checkedOut  = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        $canDo       = JDownloadsHelper::getActions($this->item->id, 'download');
        
        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');
        
        $document->addScriptDeclaration('
        // dynamically add a new image file upload field when the prior generated fields is used
        // used in backend edit download page
        function add_new_image_file(field)
        {
            // Get the number of files previously uploaded.
            var count = parseInt(document.getElementById(\'image_file_count\').value);
            var sum = parseInt(document.getElementById(\'sum_listed_images\').value);
            var max = parseInt(document.getElementById(\'max_sum_images\').value);
            
            // Get the name of the file that has just been uploaded.
            var file_name = document.getElementById("file_upload_thumb["+count+"]").value;
           
            // Hide the file upload control containing the information about the picture that was just uploaded.
            document.getElementById(\'new_file_row\').style.display = "none";
            document.getElementById(\'new_file_row\').id = "new_file_row["+count+"]";
           
            // Get a reference to the table containing the uploaded pictures.       
            var table = document.getElementById(\'files_table\');
           
            // Insert a new row with the file name and a delete button.
            var row = table.insertRow(table.rows.length);
            row.id = "inserted_file["+count+"]";
            var cell0 = row.insertCell(0);
            cell0.innerHTML = \'<input type="text" disabled="disabled" name="inserted_file[\'+count+\']" value="\'+file_name+\'" size="40" /><input type="button" name="delete[\'+count+\']" value="'.JTEXT::_('COM_JDOWNLOADS_REMOVE').'" onclick="delete_inserted_image_field(this)">\';
           
            // Increment count of the number of files uploaded.
            ++count;
            if (count+sum < max){
                // Insert a new file upload control in the table.
                var row = table.insertRow(table.rows.length);
                row.id = "new_file_row";
                var cell0 = row.insertCell(0);
                cell0.innerHTML = \'<input type="file" name="file_upload_thumb[\'+count+\']" id="file_upload_thumb[\'+count+\']" size="40" accept="image/gif,image/jpeg,image/jpg,image/png" onchange="add_new_image_file(this)" />\';   
            }
            // Update the value of the file hidden input tag holding the count of files uploaded.
            document.getElementById(\'image_file_count\').value = count;
        }

        // user will remove the files they have previously added
        // used in backend edit download page
        function delete_inserted_image_field(field)
        {
            // Get the field name.
            var name = field.name;
            
            // Extract the file id from the field name.
            var id = name.substr(name.indexOf(\'[\') + 1, name.indexOf(\']\') - name.indexOf(\'[\') - 1);
           
            // Hide the row displaying the uploaded file name.
            document.getElementById("inserted_file["+id+"]").style.display = "none";
               
            // Get a reference to the uploaded file control.
            var control = document.getElementById("file_upload_thumb["+id+"]");
           
            // Remove the new file control.
            control.parentNode.removeChild(control);
            
            // check that we have always a input field when we remove a other file
            var found = false;
            for (var i = 0; i <= 30; i++){
                 if (document.adminForm.elements["file_upload_thumb["+i+"]"]) {
                    found = true;
                 }
            }
            if (!found) add_new_image_file(field);
        }');         
        
        $title = ($isNew) ? JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_ADD') : JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_EDIT'); 
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.$title, 'pencil-2 jddownloads'); 

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit')|| $canDo->get('core.create')))
        {
            JToolBarHelper::apply('download.apply');
            JToolBarHelper::save('download.save');
        }
        if (!$checkedOut && $canDo->get('core.create')){
            JToolBarHelper::save2new('download.save2new');
        }
        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            JToolBarHelper::save2copy('download.save2copy');
        }
        
        // Added to support the Joomla Language Associations
        /* if (JLanguageAssociations::isEnabled() && JComponentHelper::isEnabled('com_associations')){
            JToolbarHelper::custom('download.editAssociations', 'contract', 'contract', 'JTOOLBAR_ASSOCIATIONS', false, false);
        } */
        
        if (empty($this->item->id)) {
            JToolBarHelper::cancel('download.cancel');
        }
        else {
            JToolBarHelper::cancel('download.cancel', 'COM_JDOWNLOADS_TOOLBAR_CLOSE');
        }
        
        JToolBarHelper::divider();
        
        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '000&tmpl=jdhelp';
        $help_url = $params->get('help_url').$help_page;
        $exists_url = JDownloadsHelper::existsHelpServerURL($help_url);
        if ($exists_url !== false){
            JToolBarHelper::help($help_url, false, $exists_url);
        } else {
            JToolBarHelper::help('help.general', true); 
        }
    }    
   
}
?>