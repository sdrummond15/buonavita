<?php
/**
* @version $Id: mod_jdownloads_top.php v3.8
* @package mod_jdownloads_top
* @copyright (C) 2018 Arno Betz
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author Arno Betz http://www.jDownloads.com
*
* This modul shows you the most recent downloads from the jDownloads component. 
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
   
    $before                = trim($params->get( 'text_before' ) );
    $text_before           = modJdownloadsTopHelper::getOnlyLanguageSubstring($before);
    $after                 = trim($params->get( 'text_after' ) );
    $text_after            = modJdownloadsTopHelper::getOnlyLanguageSubstring($after);
    $catid                 = $params->get('catid', array());
    $sum_view              = intval(($params->get( 'sum_view' ) ));
    $sum_char              = intval(($params->get( 'sum_char' ) ));
    $short_char            = $params->get( 'short_char' ) ; 
    $short_version         = $params->get( 'short_version' );
    $detail_view           = $params->get( 'detail_view' ) ; 
    $view_hits             = $params->get( 'view_hits' ) ;
    $view_hits_same_line   = $params->get( 'view_hits_same_line' );
    $hits_label            = $params->get( 'hits_label' );
    $hits_alignment        = $params->get( 'hits_alignment' );
    $view_pics             = $params->get( 'view_pics' ) ;
    $view_pics_size        = $params->get( 'view_pics_size' ) ;
    $view_numerical_list   = $params->get( 'view_numerical_list' );
    $view_thumbnails       = $params->get( 'view_thumbnails' );
    $view_thumbnails_size  = $params->get( 'view_thumbnails_size' );
    $view_thumbnails_dummy = $params->get( 'view_thumbnails_dummy' );
    $hits_alignment        = $params->get( 'hits_alignment' ); 
    $cat_show              = $params->get( 'cat_show' );
    $cat_show_type         = $params->get( 'cat_show_type' );
    $cat_show_text         = $params->get( 'cat_show_text' );
    $cat_show_text         = modJdownloadsTopHelper::getOnlyLanguageSubstring($cat_show_text);
    $cat_show_text_color   = $params->get( 'cat_show_text_color' );
    $cat_show_text_size    = $params->get( 'cat_show_text_size' );
    $cat_show_as_link      = $params->get( 'cat_show_as_link' ); 
    $view_tooltip          = $params->get( 'view_tooltip' ); 
    $view_tooltip_length   = intval($params->get( 'view_tooltip_length' ) ); 
    $alignment             = $params->get( 'alignment' );
    
    $thumbfolder = JUri::base().'images/jdownloads/screenshots/thumbnails/';
    $thumbnail = '';
    $border = ''; 
    
    $cat_show_text = trim($cat_show_text);
    if ($cat_show_text) $cat_show_text = ' '.$cat_show_text.' ';

    if ($sum_view == 0) $sum_view = 5;
    $option = 'com_jdownloads';
        
    $files = modJdownloadsTopHelper::getList($params);

    if (!count($files)) {
	    return;
    }

    $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

    require JModuleHelper::getLayoutPath('mod_jdownloads_top',$params->get('layout', 'default'));
?>