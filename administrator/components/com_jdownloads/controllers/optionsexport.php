<?php
/**
 * @package jDownloads
 * @version 3.9  
 * @copyright (C) 2007 - 2018 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

/**
 * jDownloads export options Controller
 *
 */
class jdownloadsControllerOptionsExport extends jdownloadsController
{
	/**
	 * Constructor
	 *                                 
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * logic for export the options in a file
	 *
	 */
    public function runExport()
    {

        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $db = JFactory::getDBO();
        $jinput = JFactory::getApplication()->input;

        $params = JComponentHelper::getParams('com_jdownloads');
        $files_uploaddir = $params->get('files_uploaddir');
        $tempdir         = $params->get('tempzipfiles_folder_name');
        
        $jd_version = JDownloadsHelper::getjDownloadsVersion();
        $jd_version = str_replace(' ', '_', $jd_version);
        
        $config = JFactory::getConfig();
        $sitename = $db->escape($config->get('sitename'));
        $sitename = substr($sitename, 0, 10);
  	    
        // Access check 
        if (!JFactory::getUser()->authorise('core.admin','com_jdownloads')){            
            JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
            $this->setRedirect(JRoute::_('index.php?option=com_jdownloads', true));
        } else {
                
  		    $db->setQuery('SELECT `params` FROM #__extensions WHERE `element` = "com_jdownloads" AND `type` = "component" AND `enabled` = "1"');
            $settings = $db->loadResult();
            
            $settings = json_decode($settings);
            $settings = json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        
            if ($settings && $settings != '{}'){
                
                // create file with data             
                $date_current = JHtml::_('date', '','Y-m-d_H-i-s');
                $filename = $sitename.'_options__jD_v'.$jd_version.'_date_'.$date_current.'.txt';
                $path = $files_uploaddir.'/'.$tempdir.'/'.$filename;
                 
                $result = file_put_contents($path, $settings);
                
                if ($result !== false && jFile::exists($path)){
               
                    $len = filesize($path);
                    $ctype = 'text/css';

                    ob_end_clean();

                    // Needed for MS IE - otherwise content disposition is not used?
                    if (ini_get('zlib.output_compression')){
                        ini_set('zlib.output_compression', 'Off');
                    }
                    
                    // Send the file
                    header("Cache-Control: public, must-revalidate");
                    header('Cache-Control: pre-check=0, post-check=0, max-age=0');
                    header("Expires: 0"); 
                    header("Content-Description: File Transfer");
                    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
                    header("Content-Type: " . $ctype);
                    header("Content-Length: ".(string)$len);
                    header('Content-Disposition: attachment; filename="'.$filename.'"');
                    header("Content-Transfer-Encoding: binary\n");

                    if (!ini_get('safe_mode')){ 
                        @set_time_limit(0);
                    }

                    @readfile($path);                
                    exit;
                    
                
                } else {
                    // We could not create the file
                    $this->setRedirect(JRoute::_('index.php?option=com_jdownloads'),  JText::_('Abort! Could not create the options export file!'), 'error');
                }
            } else {
                // We could not find any data
                $this->setRedirect(JRoute::_('index.php?option=com_jdownloads'),  JText::_('Abort! No configuration data found!'), 'error');
            }
            
        }     
    }	
}
?>