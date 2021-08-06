<?php
/**
* @version $Id: mod_jdownloads_rated.php v3.8
* @package mod_jdownloads_rated
* @copyright (C) 2018 Arno Betz
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author Arno Betz http://www.jDownloads.com
*
* This modul shows you the most-rated or top-rated downloads from the jDownloads component. It is only for jDownloads 3.2 and later
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

    require_once __DIR__ . '/helper.php';
    
    require_once( JPATH_ROOT . DS . 'components' . DS . 'com_jdownloads' . DS . 'helpers' . DS .'jdownloads.php' );

    $db = JFactory::getDBO();
    $Itemid  = JRequest::getVar("Itemid");
    
    // get published root menu link
    $db->setQuery("SELECT id from #__menu WHERE link = 'index.php?option=com_jdownloads&view=categories' and published = 1 AND client_id = 0");
    $root_itemid = $db->loadResult();
    
    if ($root_itemid){
        $Itemid = $root_itemid;
    }
    
    // get this option from configuration to see whether the links shall run the download without summary page
    $app = JFactory::getApplication();
	$jdparams = $app->getParams('com_jdownloads');
	$direct_download_config = $jdparams->get('direct_download');
	$detail_view_config = $jdparams->get('view_detailsite');
    
    $top_view              = $params->get( 'top_view' );
    $before                = trim($params->get( 'text_before' ) );
    $text_before           = ModJDownloadsratedHelper::getOnlyLanguageSubstring($before);
    $after                 = trim($params->get( 'text_after' ) );
    $text_after            = ModJDownloadsRatedHelper::getOnlyLanguageSubstring($after);
    $catid                 = $params->get('catid', array()); 
    $sum_view              = intval($params->get( 'sum_view' ));
    $sum_char              = intval($params->get( 'sum_char' ));
    $short_char            = $params->get( 'short_char', '' ) ; 
    $short_version         = $params->get( 'short_version', '' );
    $detail_view           = $params->get( 'detail_view' ) ; 
    $view_pics             = intval($params->get( 'view_pics' ));
    $view_pics_size        = intval($params->get( 'view_pics_size' )) ;
    $view_numerical_list   = intval($params->get( 'view_numerical_list' ));
    $view_stars            = intval($params->get( 'view_stars' ) );
    $view_stars_new_line   = intval($params->get( 'view_stars_new_line' ) );
    $view_stars_rating_count = intval($params->get( 'view_stars_rating_count' ) );    
    $alignment             = $params->get( 'alignment' );
    $moduleclass_sfx       = htmlspecialchars($params->get('moduleclass_sfx'));
    
    if ($sum_view == 0) $sum_view = 5;
    $option = 'com_jdownloads';
        
    $files = ModJDownloadsRatedHelper::getList($params);

    if (!count($files)) {
        return;
    }
    
    require JModuleHelper::getLayoutPath('mod_jdownloads_rated',$params->get('layout', 'default'));    
?>