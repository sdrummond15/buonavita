<?php
/**
 * @package jDownloads
 * @version 4.0  
 * @copyright (C) 2007 - 2017 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
 
defined('_JEXEC') or die();

/**
 * View to edit a license 
 * 
 * 
 **/

 class jdownloadsViewLicense extends JViewLegacy
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
        $this->state       = $this->get('State');
        $this->item        = $this->get('Item');
        $this->form        = $this->get('Form');
        
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
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
        $canDo       = JDownloadsHelper::getActions();
        
        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');
        
        $title = ($isNew) ? JText::_('COM_JDOWNLOADS_LICEDIT_ADD') : JText::_('COM_JDOWNLOADS_LICEDIT_EDIT'); 
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.$title, 'pencil-2 jdlicenses'); 

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit')|| $canDo->get('core.create')))
        {
            JToolBarHelper::apply('license.apply');
            JToolBarHelper::save('license.save');
        }
        if (!$checkedOut && $canDo->get('core.create')){
            JToolBarHelper::save2new('license.save2new');
        }
        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            JToolBarHelper::save2copy('license.save2copy');
        }
        if (empty($this->item->id)) {
            JToolBarHelper::cancel('license.cancel');
        }
        else {
            JToolBarHelper::cancel('license.cancel', 'JTOOLBAR_CLOSE');
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