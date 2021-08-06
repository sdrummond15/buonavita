<?php
/* @component jDownloads
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

/**
 * View to edit a category 
 * 
 * 
 **/

 class jdownloadsViewcategory extends JViewLegacy
{
    protected $state;
    protected $item;
    protected $form;
    protected $canDo;
    protected $assoc;
    
    /**
     * Display the view
     * 
     * 
     */
    public function display($tpl = null)
    {
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';
        
        $app = JFactory::getApplication();
        $app->setUserState('type', 'category');  
        
        $this->form        = $this->get('Form');
        $this->item        = $this->get('Item');
        $this->state       = $this->get('State');
        
        // Get the users Permissions
        $this->canDo = jDownloadsHelper::getActions($this->item->id, 'category');
        
        $this->assoc = $this->get('Assoc');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // Check for tag type
        $this->checkTags = JHelperTags::getTypes('objectList', array('com_jdownloads.category'), true);

        JFactory::getApplication()->input->set('hidemainmenu', true);
        
        // If we are forcing a language in modal (used for associations).
        if ($this->getLayout() === 'modal' && $forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'cmd'))
        {
            // Set the language field to the forcedLanguage and disable changing it.
            $this->form->setValue('language', null, $forcedLanguage);
            $this->form->setFieldAttribute('language', 'readonly', 'true');

            // Only allow to select categories with All language or with the forced language.
            $this->form->setFieldAttribute('parent_id', 'language', '*,' . $forcedLanguage);

            // Only allow to select tags with All language or with the forced language.
            $this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
        }
        
        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal' && $this->getLayout() !== 'modallist') {        
            $this->addToolbar();
        }
        
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
        
        $user        = JFactory::getUser();
        $isNew       = ($this->item->id == 0);
        $checkedOut  = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        
        // Get the results for each action.
        $canDo = $this->canDo;
        
        // Check to see if the type exists
        $ucmType = new JUcmType;
        $this->typeId = $ucmType->getTypeId('com_jdownloads.category');
        
        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');
                                         
        $title = ($isNew) ? JText::_('COM_JDOWNLOADS_EDIT_CAT_ADD') : JText::_('COM_JDOWNLOADS_EDIT_CAT_EDIT'); 
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.$title, 'pencil-2 jdcategories'); 

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit')|| $canDo->get('core.create')))
        {
            JToolBarHelper::apply('category.apply');
            JToolBarHelper::save('category.save');
        }
        if (!$checkedOut && $canDo->get('core.create')){
            JToolBarHelper::save2new('category.save2new');
        }
        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            JToolBarHelper::save2copy('category.save2copy');
        }
        if (empty($this->item->id)) {
            JToolBarHelper::cancel('category.cancel');
        }
        else {
            JToolBarHelper::cancel('category.cancel', 'COM_JDOWNLOADS_TOOLBAR_CLOSE');
        }
        
        JToolBarHelper::divider();
        
        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '128&tmpl=jdhelp';
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