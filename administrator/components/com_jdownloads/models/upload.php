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
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );

class jdownloadsModelupload extends JModelLegacy
{

	function __construct()
	{
		parent::__construct();
		
	}
	
	function upload($fieldName)
	{

       jimport('joomla.filesystem.file');
       jimport('joomla.filesystem.folder');
     
        //check the file extension is ok */
        $fileName = $_FILES[$fieldName]['name'];

		//this is the name of the field in the html form, filedata is the default name for swfupload
		//so we will leave it as that
		$fieldName = 'Filedata';
 
		//any errors the server registered on uploading
		$fileTemp = $_FILES[$fieldName]['tmp_name'];
        $uploadPath  = JPATH_SITE.'/jdownloads/'.$fileName ;
 
		if(!JFile::upload($fileTemp, $uploadPath, false, true)) 
		{
			echo JText::_( 'COM_JDOWNLOADS_UPLOAD_ERROR_MOVING_FILE' );
			return;
		}
		else
		{
			exit(0);
		}
	}
}
?>