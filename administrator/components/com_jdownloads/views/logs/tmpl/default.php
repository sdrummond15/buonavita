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

    JHtml::_('bootstrap.tooltip');
    
    HTMLHelper::_('behavior.multiselect');
    HTMLHelper::_('formbehavior.chosen', 'select');

    $user   = JFactory::getUser();
    $userId = $user->get('id');

    // Path to the layouts folder 
    $basePath = JPATH_ROOT .'/administrator/components/com_jdownloads/layouts';
    $options['base_path'] = $basePath;

    $listOrder = str_replace(' ' . $this->state->get('list.direction'), '', $this->state->get('list.fullordering'));
    $listDirn  = $this->escape($this->state->get('list.direction')); 
     
    $canOrder  = $user->authorise('core.edit.state', 'com_jdownloads'); 

?>
<form action="<?php echo JRoute::_('index.php?option=com_jdownloads&view=logs');?>" method="POST" name="adminForm" id="adminForm">
    
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
    echo JLayoutHelper::render('searchtools.default', array('view' => $this), $basePath, $options); ?>
    
    <?php if (empty($this->items)) : ?>
        <div class="alert alert-no-items">
            <?php echo JText::_('COM_JDOWNLOADS_NO_MATCHING_RESULTS'); ?>
        </div>

    <?php else : ?>

        <div class="alert alert-info" style="margin-top:10px;"><?php echo $this->logs_header_info; ?></div>
        <div class="clr"> </div> 
        
            <table class="table table-striped" id="logsList">
                <thead>
    	            <tr>
			            <th class="center" style="width:1%;">
                            <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
			            
                        <th class="nowrap" style="min-width:80px; width:25%;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_LOGS_COL_DATE_LABEL', 'a.log_datetime', $listDirn, $listOrder ); ?>
                        </th>
			            
                        <th class="nowrap hidden-phone">
                            <?php echo  JText::_('COM_JDOWNLOADS_LOGS_COL_USER_LABEL'); ?>
                        </th>
			            
                        <th class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_LOGS_COL_IP_LABEL', 'a.log_ip', $listDirn, $listOrder ); ?>
                        </th>
                        
                        <th class="nowrap hidden-phone">                        
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_LOGS_COL_FILETITLE_LABEL', 'a.log_title', $listDirn, $listOrder ); ?>
                        </th>

                        <th class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_LOGS_COL_FILENAME_LABEL', 'a.log_file_name', $listDirn, $listOrder ); ?>
                        </th>            

                        <th class="nowrap hidden-phone">
                            <?php echo  JText::_('COM_JDOWNLOADS_LOGS_COL_FILESIZE_LABEL'); ?>
                        </th>
                        
                        <th class="nowrap" >                        
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_LOGS_COL_TYPE_LABEL', 'a.type', $listDirn, $listOrder ); ?>
                        </th>
                        
                        <th class="nowrap hidden-phone" style="width: 1%;">                        
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_ID', 'a.id', $listDirn, $listOrder ); ?>
                        </th>
                    </tr>	
	            </thead>
		        <tfoot>
			        <tr>
				        <td colspan="9">
					        <?php echo '<br />'.$this->pagination->getListFooter(); ?>
				        </td>
			        </tr>
		        </tfoot>
		        <tbody>	
		            <?php 
                        foreach ($this->items as $i => $item) {
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            
                            <td class="center">
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            
                            <td class="nowrap">
                                 <?php echo JHtml::_('date',$this->escape($item->log_datetime), JText::_('DATE_FORMAT_LC2')); ?>
                            </td>
                            
                            <td class="nowrap hidden-phone">
                                <?php if ($item->username == ''){
                                  echo JText::_('COM_JDOWNLOADS_LOGS_COL_USER_ANONYMOUS');
                            } else {
                                  echo $this->escape($item->username);
                            } ?>
                            </td>

                            <td class="nowrap">
                                <?php echo $item->log_ip; ?>
                            </td>

                            <td class="nowrap hidden-phone">
                                <?php echo  $this->escape($item->log_title); ?>
                            </td>
                            
                            <td class="nowrap hidden-phone">
                                <?php echo  $this->escape($item->log_file_name); ?>
                            </td>

                            <td class="nowrap hidden-phone">
                                <?php
                                 echo  $this->escape($item->log_file_size); ?>
                            </td>

                            <td class="nowrap">
                            <?php if ($item->type == '1'){
                                  echo JText::_('COM_JDOWNLOADS_LOGS_COL_TYPE_DOWNLOAD');
                            } else {
                                  echo JText::_('COM_JDOWNLOADS_LOGS_COL_TYPE_UPLOAD');
                            } ?> 
                            </td>    
                            <td class="nowrap hidden-phone">
                                <?php echo (int) $item->id; ?>
                            </td>
		                </tr>
		                <?php 
                         }
                        ?>
	            </tbody>
	        </table>
        <?php endif;?>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="hidemainmenu" value="0">
    <?php echo JHtml::_('form.token'); ?>    
    </div>   
</form>
