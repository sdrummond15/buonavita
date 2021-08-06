<?php
/**
* @version $Id: mod_jdownloads_admin_monitoring.php v3.8
* @package mod_jdownloads_admin_monitoring
* @copyright (C) 2018 Arno Betz
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @author Arno Betz http://www.jDownloads.com
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

JHtml::_('bootstrap.tooltip');
jimport('joomla.html.pane');

?>

<div class="alert alert-info">
    <?php echo JText::_(''); ?>

    <?php
     
    // get the secret key then we need it as link param
    // so nobody else outside can run the script (or he know the key value - e.g. to start it via a cronjob)
    $config = JFactory::getConfig();
    $key    = $config->get( 'secret' );                         
    $test   = (int)$params->get('use_first_testrun');
     
    ?>
    
    <div style="margin-top:15px;">
        <div class="dropdown clearfix">
            <button id="dropdownMenu1" class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            <?php echo JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_BUTTON_TEXT').'&nbsp;'; ?>
            <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                <li><a href="<?php echo JURI::base();?>components/com_jdownloads/helpers/scan.php?key=<?php echo $key; ?>&mode=0&test=<?php echo (int)$test; ?>" target="_blank" onclick="openWindow(this.href, 670, 365); return false"><?php echo JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_SELECT_OPTION_ALL'); ?></a></li>
                <li role="separator" class="divider"></li>
                <li><a href="<?php echo JURI::base();?>components/com_jdownloads/helpers/scan.php?key=<?php echo $key; ?>&mode=1&test=<?php echo (int)$test; ?>" target="_blank" onclick="openWindow(this.href, 480, 365); return false"><?php echo JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_SELECT_OPTION_SEARCH_DIRS'); ?></a></li>
                <li><a href="<?php echo JURI::base();?>components/com_jdownloads/helpers/scan.php?key=<?php echo $key; ?>&mode=2&test=<?php echo (int)$test; ?>" title="<?php echo JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_SELECT_OPTION_FILES_HINT'); ?>" target="_blank" onclick="openWindow(this.href, 480, 365); return false"><?php echo JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_SELECT_OPTION_SEARCH_FILES'); ?></a></li>
                <li><a href="<?php echo JURI::base();?>components/com_jdownloads/helpers/scan.php?key=<?php echo $key; ?>&mode=3&test=<?php echo (int)$test; ?>" target="_blank" onclick="openWindow(this.href, 480, 365); return false"><?php echo JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_SELECT_OPTION_CHECK_CATS'); ?></a></li>
                <li><a href="<?php echo JURI::base();?>components/com_jdownloads/helpers/scan.php?key=<?php echo $key; ?>&mode=4&test=<?php echo (int)$test; ?>" target="_blank" onclick="openWindow(this.href, 480, 365); return false"><?php echo JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_SELECT_OPTION_CHECK_DOWNLOADS'); ?></a></li>
            </ul>
        </div>
    </div>
    <div style="margin-top:11px;">                                        
        <?php echo '<small>'.JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_RUN_MONITORING_INFO').'</small>'; ?>
        <?php echo '<ul>';
              
              if ($test) {
                  echo '<li><small>'.JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_TEST_RUN_ACTIVE_HINT').'</small></li>'; 
              }
              
              if (!$params->get('all_folders_autodetect')) {
                  echo '<li><small>'.JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_EXCLUDE_INCLUDE_OPTION_IS_ACTIVE_HINT').'</small></li>'; 
              }
              
              if ($params->get('autopublish_founded_files')) {
                  echo '<li><small>'.JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_AUTO_PUBLISH_NEW_FOUND_ITEMS_HINT').'</small></li>'; 
              }
              
            if ($params->get('autopublish_use_cat_default_values')) {
                echo '<li><small>'.JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_AUTO_PUBLISH_USE_DEFAULT_CAT').'</small></li>'; 
            }
            
            if ($params->get('autopublish_use_default_values')) {
                echo '<li><small>'.JText::_('MOD_JDOWNLOADS_ADMIN_MONITORING_AUTO_PUBLISH_USE_DEFAULT_FILE').'</small></li>'; 
            }
              
              echo '</ul>';
        ?>
    </div>
</div>
