<?php
/**
 * @package jDownloads
 * @version 2.0  
 * @copyright (C) 2007 - 2012 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined('_JEXEC') or die('Restricted access');
 
// import Joomla table library
jimport('joomla.database.table');
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );
 
/**
 * jDownloads (group) Table class
 */
class jdownloadsTablegroup extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(&$db) 
	{
		parent::__construct('#__jdownloads_usergroups_limits', 'id', $db);
	}
    
    
    public function check()
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );        
        
        
        if ($this->form_user_access){
            $this->form_access = 1;
        }
        
        // remove special characters from extension list
        $this->uploads_allowed_types         = strtolower(preg_replace('/[^0-9a-zA-Z,]/', '', $this->uploads_allowed_types));
        $this->uploads_allowed_preview_types = strtolower(preg_replace('/[^0-9a-zA-Z,]/', '', $this->uploads_allowed_preview_types));
        
        // we need at min one field when the customers form is activated
        if ($this->view_inquiry_form == 1 && $this->form_fieldset == '{"0":""}'){
            $this->form_fieldset = '{"0":"1"}';
        }
                
        return true;
    }    

}
?>