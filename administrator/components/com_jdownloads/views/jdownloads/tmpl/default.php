<?php

/**
 * @package jDownloads
 * @version 3.8  
 * @copyright (C) 2007 - 2018 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;

jimport( 'joomla.application.module.helper' );

    JHtml::_('bootstrap.tooltip');
    
    HTMLHelper::_('behavior.multiselect');
    HTMLHelper::_('behavior.keepalive');
    HTMLHelper::_('behavior.formvalidator');  
    HTMLHelper::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0 ));

    $user    = JFactory::getUser();

	$params = JComponentHelper::getParams('com_jdownloads');
    
    $db = JFactory::getDBO();
    $db->setQuery("SELECT `rules` FROM #__assets WHERE `name` = 'com_jdownloads' AND `title` = 'com_jdownloads' AND `level` = '1'");
    $component_rules = $db->loadResult();    
    
    // get download stats
    $stats_data = jdownloadsHelper::getDownloadStatsData();
    $stats_data['downloaded'] = sprintf(JText::_('COM_JDOWNLOADS_CP_TOTAL_SUM_DOWNLOADED_FILES'), '<span class="badge badge-warning">'.$stats_data['downloaded'].'</span>');
    
    $user_rules     = jdownloadsHelper::getUserRules();

    // check that we have valid user rules - when not, create it from joomla users
    if (!$user_rules){
        $user_result = jdownloadsHelper::setUserRules();
    }
    
    $canDo = jdownloadsHelper::getActions();
    $option = 'com_jdownloads';
    

    
?>
    
    <form action="index.php" method="post" name="adminForm" id="adminForm">

    <div class="row-fluid">
        <div class="span3">
            <div class="cpanel-links">
                <div class="sidebar-nav quick-icons">
                     <div class="j-links-groups">
                        <ul class="j-links-group nav nav-list">
                            <li> 
                                <a href="index.php?option=com_jdownloads&amp;view=categories">
                                <span class="icon-folder"></span>
                                <span class="j-links-link"><?php echo JText::_( 'COM_JDOWNLOADS_CATEGORIES' ); ?></span>
                                </a>
                            </li>
                            <li> 
                                <a href="index.php?option=com_jdownloads&amp;view=downloads">
                                <span class="icon-stack"></span>
                                <span class="j-links-link"><?php echo JText::_( 'COM_JDOWNLOADS_DOWNLOADS' ); ?></span>
                                </a>
                            </li>                     
                            <li> 
                                <a href="index.php?option=com_jdownloads&amp;view=files">
                                <span class="icon-copy"></span>
                                <span class="j-links-link"><?php echo JText::_( 'COM_JDOWNLOADS_FILES' ); ?></span>
                                </a>
                            </li>                     
                            <li> 
                                <a href="index.php?option=com_jdownloads&amp;view=licenses">
                                <span class="icon-key"></span>
                                <span class="j-links-link"><?php echo JText::_( 'COM_JDOWNLOADS_LICENSES' ); ?></span>
                                </a>
                            </li>                       
                            <li> 
                                <a href="index.php?option=com_jdownloads&amp;view=layouts">
                                <span class="icon-brush"></span>
                                <span class="j-links-link"><?php echo JText::_( 'COM_JDOWNLOADS_LAYOUTS' ); ?></span>
                                </a>
                            </li>
                            <li> 
                                <a href="index.php?option=com_jdownloads&amp;view=logs">
                                <span class="icon-list-2"></span>
                                <span class="j-links-link"><?php echo JText::_( 'COM_JDOWNLOADS_LOGS' ); ?></span>
                                </a>
                            </li>
                            <li> 
                                <a href="index.php?option=com_jdownloads&amp;view=associations">
                                <span class="icon-contract"></span>
                                <span class="j-links-link"><?php echo JText::_( 'COM_JDOWNLOADS_MULTILINGUAL_ASSOCIATIONS' ); ?></span>
                                </a>
                            </li>
                            <li> 
                                <a href="index.php?option=com_jdownloads&amp;view=groups">
                                <span class="icon-users"></span>
                                <span class="j-links-link"><?php echo JText::_( 'COM_JDOWNLOADS_USER_GROUPS' ); ?></span>
                                </a>
                            </li>
                            <?php
                                if (JComponentHelper::isEnabled('com_fields') && $params->get('custom_fields_enable') == 1) { ?>
                                    <li> 
                                        <a href="index.php?option=com_fields&context=com_jdownloads.download">
                                        <span class="icon-file-add"></span>
                                        <span class="j-links-link"><?php echo JText::_('COM_JDOWNLOADS_CUSTOM_FIELDS' ); ?></span>
                                        </a>
                                    </li>
                                    <li> 
                                        <a href="index.php?option=com_fields&view=groups&context=com_jdownloads.download">
                                        <span class="icon-folder-plus-2"></span>
                                        <span class="j-links-link"><?php echo JText::_('COM_JDOWNLOADS_CUSTOM_FIELD_GROUPS' ); ?></span>
                                        </a>
                                    </li>
                                <?php                                         
                                }
                            ?>
                            <li> 
                                <a href="index.php?option=com_jdownloads&amp;view=tools">
                                <span class="icon-cogs"></span>
                                <span class="j-links-link"><?php echo JText::_( 'COM_JDOWNLOADS_TOOLS' ); ?></span>
                                </a>
                            </li>                     
                            <li> 
                                <a href="index.php?option=com_jdownloads&amp;view=info">
                                <span class="icon-info"></span>
                                <span class="j-links-link"><?php echo JText::_( 'COM_JDOWNLOADS_TERMS_OF_USE' ); ?></span>
                                </a>
                            </li>                             
                        </ul>
                     </div>
                </div>
            </div>                
        </div>

	    <div class="span9">
            <div class="row-fluid">
                <?php
                    // exist the defined upload root folder?
                    if (!is_dir($params->get('files_uploaddir')) &&  $params->get('files_uploaddir') != ''){ ?>
                        <div class="alert alert-error" style="margin-top:10px;"><b><?php echo JText::sprintf('COM_JDOWNLOADS_AUTOCHECK_DIR_NOT_EXIST', $params->get('files_uploaddir')).'</b><br /><br />'.JText::_('COM_JDOWNLOADS_AUTOCHECK_DIR_NOT_EXIST_2'); ?></div> 
                <?php }  ?>
                
                <?php 
                if ($params->get('offline')) {
                    echo '<div class="alert alert-error">';                     
                    echo JText::_('COM_JDOWNLOADS_BACKEND_PANEL_STATUS_TITEL').' ';
                    echo JText::_('COM_JDOWNLOADS_BACKEND_PANEL_STATUS_OFFLINE').'<br /><br />';
                    echo JText::_('COM_JDOWNLOADS_BACKEND_PANEL_STATUS_DESC_OFFLINE').'<br /></div>';
                } else { 
                    echo '<div class="alert alert-success">';
                    echo JText::_('COM_JDOWNLOADS_BACKEND_PANEL_STATUS_TITEL').' ';
                    echo JText::_('COM_JDOWNLOADS_BACKEND_PANEL_STATUS_ONLINE').'</div>';
                }
                ?>
            </div>    
            
            <div class="row-fluid">
            

                <?php
                 
                $spans = 0;

                foreach ($this->modules as $module)
                {
                    // Get module parameters
                    $params = new Registry($module->params);
                    $bootstrapSize = $params->get('bootstrap_size');
                    if (!$bootstrapSize)
                    {
                        $bootstrapSize = 12;
                    }
                    $spans += $bootstrapSize;
                    if ($spans > 12)
                    {
                        echo '</div><div class="row-fluid">';
                        $spans = $bootstrapSize;
                    }
                	echo JModuleHelper::renderModule($module, array('style' => ''));
                }
                ?>        
             </div>
         </div>
                
	 </div>

     <input type="hidden" name="option" value="com_jdownloads" />
     <input type="hidden" name="task" value="" />
     <input type="hidden" name="boxchecked" value="0" />
     <input type="hidden" name="controller" value="jdownloads" />
     </form>
     
     
