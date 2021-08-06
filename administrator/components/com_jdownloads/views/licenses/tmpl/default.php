<?php
/**
 * @package jDownloads
 * @version 4.0  
 * @copyright (C) 2007 - 2016 - Arno Betz - www.jdownloads.com
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

    $user        = JFactory::getUser();
    $userId      = $user->get('id');

    // Path to the layouts folder 
    $basePath = JPATH_ROOT .'/administrator/components/com_jdownloads/layouts';
    $options['base_path'] = $basePath;

    // Amount of columns (for 'colspan')
    $columns = 8;

    $listOrder = str_replace(' ' . $this->state->get('list.direction'), '', $this->state->get('list.fullordering'));
    $listDirn  = $this->escape($this->state->get('list.direction'));
    $saveOrder = $listOrder == 'a.ordering';

    $canOrder    = $user->authorise('core.edit.state', 'com_jdownloads');

    if ($saveOrder)
    {
        $saveOrderingUrl = 'index.php?option=com_jdownloads&task=licenses.saveOrderAjax&tmpl=component';
        JHtml::_('sortablelist.sortable', 'licenseList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
    }

?>
<form action="<?php echo JRoute::_('index.php?option=com_jdownloads&view=licenses');?>" method="POST" name="adminForm" id="adminForm">
    
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
    echo JLayoutHelper::render('searchtools.default', array('view' => $this));
    ?>   
    
    <?php if (empty($this->items)) : ?>
        <div class="alert alert-no-items">
            <?php echo JText::_('COM_JDOWNLOADS_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
    
            <table class="table table-striped" id="licenseList">
                <thead>
                    <tr>
                        <th class="nowrap center hidden-phone" style="width:1%;">
                            <?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'COM_JDOWNLOADS_ORDERING', 'icon-menu-2'); ?>
                        </th>
                        <th class="center" style="width:1%;">
                            <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
                        <th class="nowrap center" style="width:1%; min-width:55px;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_STATUS', 'a.published', $listDirn, $listOrder); ?>
                        </th>
                        <th style="min-width:100px">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th class="nowrap hidden-phone" style="text-align:center; width: 5px;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_DESCRIPTION', 'a.description', $listDirn, $listOrder ); ?>
                        </th> 
                        <th class="nowrap hidden-phone" style="width:20%;">
                            <?php echo JHtml::_('searchtools.sort',  'COM_JDOWNLOADS_LICLIST_LINK', 'a.url', $listDirn, $listOrder); ?>
                        </th>  
                        <th class="nowrap hidden-phone" style="width:15%;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                        </th>
                        <th class="nowrap hidden-phone" style="width:1%;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo $columns; ?>">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>

                <tbody>
                <?php foreach ($this->items as $i => $item) :
                    $item->max_ordering = 0;
                    $ordering   = ($listOrder == 'a.ordering');
                    $canCreate  = $user->authorise('core.create',     'com_jdownloads');
                    $canEdit    = $user->authorise('core.edit',       'com_jdownloads');
                    $canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                    $canChange  = $user->authorise('core.edit.state', 'com_jdownloads') && $canCheckin;
                    ?>
                    <tr class="row<?php echo $i % 2; ?>" sortable-group-id="">
                        <td class="order nowrap center hidden-phone">
                            <?php
                            $iconClass = '';
                            if (!$canChange)
                            {
                                $iconClass = ' inactive';
                            }
                            elseif (!$saveOrder)
                            {
                                $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('COM_JDOWNLOADS_ORDERING_DISABLED');
                            }
                            ?>
                            <span class="sortable-handler<?php echo $iconClass ?>">
                                <span class="icon-menu"></span>
                            </span>
                            <?php if ($canChange && $saveOrder) : ?>
                                <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
                            <?php endif; ?>
                        </td>
                        <td class="center">
                            <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                        </td>
                        <td class="center">
                            <div class="btn-group">
                                <?php 
                                echo JHtml::_('jgrid.published', $item->published, $i, 'licenses.', $canChange); ?>
                                ?>
                            </div>
                        </td>
                        <td class="has-context">
                            <div class="pull-left break-word">
                                <?php if ($item->checked_out) : ?>
                                    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'licenses.', $canCheckin); ?>
                                <?php endif; ?>
                                
                                <?php if ($item->language == '*'):?>
                                    <?php $language = JText::alt('COM_JDOWNLOADS_ALL', 'language'); ?>
                                <?php else:?>
                                    <?php $language = $item->language_title ? $this->escape($item->language_title) : JText::_('COM_JDOWNLOADS_UNDEFINED'); ?>
                                <?php endif;?>
                                
                                <?php 
                                if ($canEdit) : ?>
                                    <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_jdownloads&task=license.edit&id=' . $item->id); ?>" title="<?php echo JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_TITLE'); ?>">
                                        <?php echo $this->escape($item->title); ?></a>
                                <?php else : ?>
                                    <span><?php echo $this->escape($item->title); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <td class="small hidden-phone" style="text-align:center;">
                            <?php
                            if ($item->description != '') {
                                $description_short = JHtml::_('string.truncate', $this->escape(strip_tags($item->description)), 400, true, false); // Do not cut off words; HTML not allowed;
                                echo JHtml::_('tooltip', $description_short, JText::_('COM_JDOWNLOADS_DESCRIPTION'), JURI::root().'administrator/components/com_jdownloads/assets/images/tooltip_blue.gif'); 
                            }
                            ?>
                        </td>

                        <td class="small hidden-phone">
                            <?php 
                            if ($item->url != ''){
                                $url_short = JHtml::_('string.truncate', $this->escape(strip_tags($item->url)), 35, false, false); // May cut off words; HTML not allowed;
                                echo '<a href="'.$this->escape($item->url).'" target="_blank">'.$url_short.'<span class="icon-out-2" aria-hidden="true"></span></a>'; 
                            } ?>
                        </td> 
                        
                        <td class="small hidden-phone">
                            <?php if ($item->language == '*'):?>
                                <?php echo JText::alt('COM_JDOWNLOADS_ALL', 'language'); ?>
                            <?php else:?>
                                <?php echo $item->language_title ? JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true) . '&nbsp;' . $this->escape($item->language_title) : JText::_('COM_JDOWNLOADS_UNDEFINED'); ?>
                            <?php endif;?>
                        </td>
                        
                        <td class="hidden-phone">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>      
                </tbody>
            </table>
        
        <?php // Load the batch processing form. ?>
        <?php if ($user->authorise('core.create', 'com_jdownloads')
                    && $user->authorise('core.edit', 'com_jdownloads')
                    && $user->authorise('core.edit.state', 'com_jdownloads')) : ?>
                    <?php echo JHtml::_(
                            'bootstrap.renderModal',
                            'collapseModal',
                            array(
                                'title' => JText::_('COM_JDOWNLOADS_BATCH_OPTIONS'),
                                'footer' => $this->loadTemplate('batch_footer')
                            ),
                            $this->loadTemplate('batch')
                        ); ?>
        <?php endif; ?>
    <?php endif;?>         
                            
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="hidemainmenu" value="0">
    <?php echo JHtml::_('form.token'); ?>    
</div>
</form>
