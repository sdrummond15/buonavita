<?php
/*
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 *
 * @component jDownloads
 * @version 4.0  
 * @copyright (C) 2007 - 2017 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
 
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

jimport( 'joomla.application.module.helper' );

    JHtml::_('bootstrap.tooltip');
    
    HTMLHelper::_('behavior.multiselect');
    HTMLHelper::_('behavior.keepalive');
    HTMLHelper::_('behavior.formvalidator');  
    HTMLHelper::_('formbehavior.chosen', 'select');

$user        = JFactory::getUser();
$userId      = $user->get('id');

?>

<form action="<?php echo JRoute::_('index.php?option=com_jdownloads&view=files');?>" method="post" name="adminForm" id="adminForm">
    
    <?php if (!empty( $this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
    <?php else : ?>
        <div id="j-main-container">
    <?php endif;?>
    
    <?php
    // Search tools bar
    echo JLayoutHelper::render('searchtools.default', array('view' => $this, 'options' => array('filterButton' => false))); ?>     
    
    <?php if (empty($this->items)) : ?>
        <div class="alert alert-no-items">
            <?php echo JText::_('COM_JDOWNLOADS_NO_MATCHING_RESULTS'); ?>
        </div>

    <?php else : ?>
        
        <div class="alert" style="margin-top:10px;"><?php echo JText::_('COM_JDOWNLOADS_MANAGE_FILES_DESC'); ?> </div>
        <div class="clr"> </div>            
            <table class="table table-striped" id="groupsList">
                <thead>
                    <tr>
                        <th class="center" style="width:1%;">
                        <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
                        
                        <th class="nowrap" style="min-width:80px; width:40%;">
                            <?php echo JText::_('COM_JDOWNLOADS_MANAGE_FILES_TITLE_NAME'); ?>
                        </th>
                        
                        <th class="nowrap hidden-phone" style="width:20%; text-align:right;">
                            <?php echo JText::_('COM_JDOWNLOADS_MANAGE_FILES_TITLE_DATE'); ?>
                        </th> 
                        
                        <th class="nowrap hidden-phone" style="width:20%; text-align:right;">
                            <?php echo JText::_('COM_JDOWNLOADS_MANAGE_FILES_TITLE_SIZE'); ?> 
                        </th>
                        
                        <th class="nowrap" style="width:15%; text-align:right;">
                            <?php echo JText::_(''); ?>
                        </th>

                    </tr>    
                </thead>
                <tfoot>
                  <tr>
                    <td colspan="5"><?php echo '<br />'.$this->pagination->getListFooter(); ?>
                    </td>
                  </tr>
                </tfoot>
                <tbody>  
                <?php 
                    foreach ($this->items as $i => $item) {
                        $canCreate    = $user->authorise('core.create',     'com_jdownloads');
                        $canEdit      = $user->authorise('core.edit',       'com_jdownloads');
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center">
                            <?php echo JHtml::_('grid.id', $i, htmlspecialchars($item['name'])); ?>
                        </td>
                        
                        <td>
                        <?php echo $this->escape($item['name']); ?>
                        </td>
                        
                        <td class="hidden-phone nowrap" style="text-align:right;">
                            <?php echo JHtml::_('date',$this->escape($item['date']), JText::_('DATE_FORMAT_LC4')); ?>
                        </td>
                            
                        <td class="hidden-phone nowrap" style="text-align:right;">
                             <?php echo $this->escape($item['size']) ?>
                        </td>

                        <td class="nowrap" style="text-align:right;">
                            <?php echo JRoute::_('<a class="btn btn-primary" href="index.php?option=com_jdownloads&amp;task=download.edit&amp;file='.$item['name'].'">'.JText::_('COM_JDOWNLOADS_MANAGE_FILES_TITLE_CREATE_NEW_DOWNLOAD').'</a>');
                            ?>
                        </td>
                    </tr>
                    <?php 
                     }
                    ?>
                </tbody>
            </table>
        <?php endif;?>
    </div>
    
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>    
</form>