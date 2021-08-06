<?php
/**
 * @package jDownloads
 * @version 3.9  
 * @copyright (C) 2007 - 2019 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
 
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controllerform');

/**
*       
 */
class jdownloadsControllerSurvey extends JControllerForm
{


	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	$name	The model name. Optional.
	 * @param	string	$prefix	The class prefix. Optional.
	 * @param	array	$config	Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 *
	 */
	public function getModel($name = 'form', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return	string	The return URL.
	 */
	protected function getReturnPage()
	{
		$return = $this->input->get('return', null, 'base64');

		if (empty($return) || !JUri::isInternal(urldecode(base64_decode($return)))) {
			return JURI::base();
		}
		else {
			return urldecode(base64_decode($return));
		}
	}


	/**
	 * Method to send the report form data to the defined e-mail addresses
	 *
	 */
	public function send()
	{
	
        JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
        
        $db     = JFactory::getDBO();
        $jinput = JFactory::getApplication()->input;
        
        // Check for request forgeries.
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

        $model = $this->getModel('Survey');
        if ($model->send()) {
            $type = 'message';
        } else {
            $type = 'error';
        }

        $msg = $model->getError();
        
        $stored_fileid = (int)JDHelper::getSessionDecoded('jd_fileid');
        $stored_catid  = (int)JDHelper::getSessionDecoded('jd_catid');
        
        $itemid         = $db->escape($jinput->get('Itemid', 0, 'int'));
        
        if ($type == 'message'){
            // run again the download process
            JDHelper::writeSessionEncoded('1', 'jd_survey_form_send');
            JDHelper::writeSessionEncoded($stored_catid, 'jd_survey_cat_id');
            JDHelper::writeSessionEncoded($stored_fileid, 'jd_survey_file_id');
            
            $this->setRedirect('index.php?option=com_jdownloads&task=download.send&id='.$stored_fileid.'&catid='.$stored_catid.'&m=0&Itemid='.$itemid, $msg, $type);
        } else {
            JDHelper::writeSessionEncoded('0', 'jd_survey_form_send');
            $this->setRedirect('index.php?option=com_jdownloads&task=download.send&id='.$stored_fileid.'&catid='.$stored_catid.'&m=0&Itemid='.$itemid, $msg, $type);
        }
    }

    /**
     * Method to skip the customers form.
     *
     */
    public function skip($key = null)
     {
       
        JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
            
        $db     = JFactory::getDBO();
        $jinput = JFactory::getApplication()->input;
        
        $user_rules = JDHelper::getUserRules();
        $msg        = '';
        
        // Check for request forgeries.
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

        $stored_fileid = (int)JDHelper::getSessionDecoded('jd_fileid');
        $stored_catid  = (int)JDHelper::getSessionDecoded('jd_catid');
        
        $itemid         = $db->escape($jinput->get('Itemid', 0, 'int'));
        
        // the user may skip the form
        JDHelper::writeSessionEncoded('1', 'jd_survey_form_send');
        JDHelper::writeSessionEncoded($stored_catid, 'jd_survey_cat_id');
        JDHelper::writeSessionEncoded($stored_fileid, 'jd_survey_file_id');
        
        $this->setRedirect('index.php?option=com_jdownloads&task=download.send&id='.$stored_fileid.'&catid='.$stored_catid.'&m=0&Itemid='.$itemid, $msg);
       
    }
    
    /**
     * Method to abort the customers form process.
     *
     */
    public function abort($key = null)
    { 
        JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
        
        // Check for request forgeries.
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
        
        JDHelper::writeSessionEncoded('0', 'jd_survey_form_send');
        $this->setRedirect('index.php?option=com_jdownloads');
     }

}