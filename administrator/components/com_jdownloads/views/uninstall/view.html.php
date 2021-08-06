<?php
/*
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 *
 * @component jDownloads
 * @version 3.2  
 * @copyright (C) 2007 - 2016 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * Uninstall View
 *
 */
class jdownloadsViewuninstall extends JViewLegacy
{
    protected $form;
    protected $canDo;

	/**
	 * restore display method
	 * @return void
	 **/
	function display($tpl = null)
	{
        $this->form        = $this->get('Form');
        
        // What Access Permissions does this user have? 
        $this->canDo = jdownloadsHelper::getActions();        
        
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
        
        $canDo    = JDownloadsHelper::getActions();
        $user     = JFactory::getUser();

        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jdownloads/assets/css/style.css');
        
        JToolBarHelper::title(JText::_('COM_JDOWNLOADS').': '.JText::_('COM_JDOWNLOADS_UNINSTALL'), 'cancel jduninstall');
        
        // Add help button - The first integer value must be the corresponding article ID from the documentation
        $help_page = '48&tmpl=jdhelp';
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