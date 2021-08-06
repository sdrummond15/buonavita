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

// No direct access
defined('_JEXEC') or die;

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR.'/components/com_jdownloads/models/download.php';

/**
 * jDownloads Component Download Model
 *
 */
class jdownloadsModelSurvey extends jdownloadsModeldownload
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;

		// Load state from the request.
		$pk = $app->input->get('id', 0, 'int');
		$this->setState('download.id', $pk);

		$this->setState('download.catid', $app->input->get('catid', 0, 'int'));

        $return = $jinput->get('return', null, 'base64');
		$this->setState('return_page', urldecode(base64_decode($return)));

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $jinput->get('layout'));
	}

	/**
	 * Method to get download data.
	 *
	 * @param	integer	The id of the download.
	 *
	 * @return	mixed	Content item data object on success, false on failure.
	 */
	public function getItem($itemId = null)
	{
		// Initialise variables.
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('download.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return false;
		}

		$properties = $table->getProperties(1);
		$value = JArrayHelper::toObject($properties, 'JObject');

		// Convert attrib field to Registry.
		$value->params = new JRegistry;

		return $value;
	}

	/**
	 * Get the return URL.
	 *
	 * @return	string	The return URL.
	 */
	public function getReturnPage()
	{
		return base64_encode(urlencode($this->getState('return_page')));
	}
    
    /**
     * Get the report form.
     * 
     * @param    array      The data for the form.
     *           boolean    True when data shall be loaded   
     *
     * @return    array     The form
     */    
    public function getForm($data = array(), $loadData = true) 
    {
        
        // Initialise variables.
        $app    = JFactory::getApplication();
        
        // Get the form.
        $form = $this->loadForm('com_jdownloads.survey', 'survey',
                                array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) 
        {
            return false;
        }
        return $form;
    }    
    
    /**
     * Send the mail with the given data
     *
     * @return    boolean    True when succesful
     */      
    public function send()
    {
        $app    = JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;
        $params = $app->getParams();
        
        // Initialise variables.
        $data    = JRequest::getVar('jform', array(), 'post', 'array');
        
        $name       = array_key_exists('name', $data)       ? $data['name'] : '';
        $company    = array_key_exists('company', $data)    ? $data['company'] : '';
        $country    = array_key_exists('country', $data)    ? $data['country'] : '';
        $address    = array_key_exists('address', $data)    ? $data['address'] : '';
        $email      = array_key_exists('email', $data)      ? $data['email'] : '';
        $fileid     = array_key_exists('id', $data)         ? intval($data['id']) : 0;
        $filetitle  = array_key_exists('title', $data)      ? $data['title'] : '';
        $file_name  = array_key_exists('url_download', $data)  ? $data['url_download'] : '';
        $catid      = array_key_exists('catid', $data)      ? $data['catid'] : 0;
        $cat_title  = array_key_exists('cat_title', $data)  ? $data['cat_title'] : '';
        
        // get the users IP
        $ip = JDHelper::getRealIp();

        // automatically removes html formatting
        $name       = JFilterInput::getInstance()->clean($name, '');
        $company    = JFilterInput::getInstance()->clean($company, 'string');
        $country    = JFilterInput::getInstance()->clean($country, 'string');
        $address    = JFilterInput::getInstance()->clean($address, 'string');
        $email      = JFilterInput::getInstance()->clean($email, 'string');
        
        // Get all users email addresses in an array
        $recipients = explode( ';', $params->get('customers_send_mailto'));

        // Check to see if there are any users in this group before we continue
        if (!count($recipients)) {
            $this->setError(JText::_('COM_JDOWNLOADS_NO_EMAIL_RECIPIENT_FOUND'));
            return false;
        }

        // Get the Mailer
        $mailer = JFactory::getMailer();

        // Build email message format.
        $mailer->setSender(array($app->getCfg('mailfrom'), $app->getCfg('fromname')));
        $mailer->setSubject('jDownloads - '.stripslashes(JDHelper::getOnlyLanguageSubstring($params->get('customers_mail_subject'))));
        
        $message = JDHelper::getOnlyLanguageSubstring($params->get('customers_mail_layout'));
        $message = str_replace('{category}', $cat_title, $message);
        $message = str_replace('{cat_id}', $catid, $message);
        $message = str_replace('{file_id}', $fileid, $message);
        $message = str_replace('{file_title}', $filetitle, $message);
        $message = str_replace('{file_name}', $file_name, $message);
        $message = str_replace('{name}', $name, $message);
        $message = str_replace('{company}', $company, $message);
        $message = str_replace('{country}', $country, $message);
        $message = str_replace('{address}', $address, $message);
        $message = str_replace('{mail}', $email, $message);
        $message = str_replace('{ip}', $ip, $message);
        $date_format = JDHelper::getDateFormat();
        $message = str_replace('{date_time}', JHtml::date($input = 'now', $date_format['long'], true), $message);
        
        $mailer->setBody($message);
        
        // Example: Optional file attached
        // $mailer->addAttachment(JPATH_COMPONENT.'/assets/document.pdf');
        // Example: Optionally add embedded image 
        // $mailer->AddEmbeddedImage( JPATH_COMPONENT.'/assets/logo128.jpg', 'logo_id', 'logo.jpg', 'base64', 'image/jpeg' );
        
        // Needed for use HTML 
        $mailer->IsHTML(true);
        $mailer->Encoding = 'base64';

        // Add recipients
        $mailer->addRecipient($recipients[0]);
        
        // remove the first recipient and add all other recipients to the BCC field 
        if (count($recipients) > 1){
             array_shift($recipients);
             $mailer->addBCC($recipients);
        }        
        
        // Send the Mail
        $result    = $mailer->Send();

        if ( $result !== true ) {
            $this->setError($result->getError());
            return false;
        } else {
            JError::raiseNotice( 100, JText::_('COM_JDOWNLOADS_EMAIL_SUCCESSFUL_SENDED'));
            return true;
        }        
    }    
    
}
