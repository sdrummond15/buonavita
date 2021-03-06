<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');
 
/**
 * General Controller of jDownloads component
 */
class jdownloadsController extends JControllerLegacy
{
/**
     * Method to display a view.
     *
     * @param    boolean            $cachable    If true, the view output will be cached
     * @param    array              $urlparams    An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return    JController        This object to support chaining.
     */
    public function display($cachable = false, $urlparams = array())
    {
        require_once JPATH_COMPONENT.'/helpers/jdownloads.php';
        require_once JPATH_COMPONENT.'/helpers/pluploadscript.php';
        
        $view   = $this->input->get('view', 'jdownloads');
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');
        
        // Check for edit form.
        if ($view == 'template' && $layout == 'edit' && !$this->checkEditId('com_jdownloads.edit.template', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
            $this->setMessage($this->getError(), 'error');
            $this->setRedirect(JRoute::_('index.php?option=com_jdownloads&view=templates', false));

            return false;
        }  

        parent::display($cachable);

        return $this;
    }
}
