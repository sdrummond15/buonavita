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

defined('_JEXEC') or die;

$params = $this->state->params;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('COM_JDOWNLOADS_SELECT_TAG')));
JHtml::_('formbehavior.chosen', '.multipleAccessLevels', null, array('placeholder_text_multiple' => JText::_('COM_JDOWNLOADS_SELECT_ACCESS')));
JHtml::_('formbehavior.chosen', 'select');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$root      = JURI::root();

// Path to the layouts folder 
$basePath = JPATH_ROOT.'/administrator/components/com_jdownloads/layouts';

// Path to the images folder (for file symbols) 
$cat_pic_folder = 'images/jdownloads/catimages/';

$options['base_path'] = $basePath;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = ($listOrder == 'a.lft' && strtolower($listDirn) == 'asc');
$section   = null;
$columns   = 9;

$canOrder  = $user->authorise('core.edit.state', 'com_jdownloads');

if ($saveOrder){
    $saveOrderingUrl = 'index.php?option=com_jdownloads&task=categories.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl, false, true);
}

$assoc = JLanguageAssociations::isEnabled();
?>

<form action="<?php echo JRoute::_('index.php?option=com_jdownloads&view=categories');?>" method="POST" name="adminForm" id="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <?php
        // Search tools bar
        echo JLayoutHelper::render('searchtools.default', array('view' => $this), $basePath, $options);
        ?>
    
        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                <?php echo JText::_('COM_JDOWNLOADS_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-striped" id="categoryList">
                <thead>
                    <tr>
                        <th class="nowrap center hidden-phone" style="width:1%;">
                            <?php echo JHtml::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'COM_JDOWNLOADS_ORDERING', 'icon-menu-2'); ?>
                        </th>
                        <th class="center" style="width:1%;">
                            <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
                        <th class="nowrap center" style="width:1%;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_STATUS', 'a.published', $listDirn, $listOrder); ?>
                        </th>
                        <th class="nowrap hidden-phone" style="width:1%;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_CATSLIST_PIC', 'a.pic', $listDirn, $listOrder ); ?>
                        </th>
                        <th class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th class="nowrap hidden-phone" style="text-align:center; width: 5%;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_DESCRIPTION', 'a.description', $listDirn, $listOrder ); ?>
                        </th>
                        <th class="nowrap hidden-phone" style="text-align:center; width: 5%;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_CATSLIST_PATH', 'a.cat_dir', $listDirn, $listOrder ); ?>
                        </th>
                        <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) :
                            $columns++; ?>
                            <th class="nowrap center hidden-phone hidden-tablet" style="width:1%;">
                                <i class="icon-publish hasTooltip" title="<?php echo JText::_('COM_JDOWNLOADS_PUBLISHED_DOWNLOADS'); ?>"></i>
                            </th>
                        <?php endif;?>
                        <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) :
                            $columns++; ?>
                            <th class="nowrap center hidden-phone hidden-tablet" style="width:1%;">
                                <i class="icon-unpublish hasTooltip" title="<?php echo JText::_('COM_JDOWNLOADS_UNPUBLISHED_DOWNLOADS'); ?>"></i>
                            </th>
                        <?php endif;?>
                        <th class="nowrap hidden-phone" style="width:10%;">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($assoc) :
                            $columns++; ?>
                            <th width="5%" class="nowrap hidden-phone hidden-tablet">
                                <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
                            </th>
                        <?php endif; ?>
                        <th class="nowrap hidden-phone" style="width:10%;">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'language_title', $listDirn, $listOrder); ?>
                        </th>
                        <th class="nowrap hidden-phone" style="width:1%;">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo $columns; ?>">
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <?php foreach ($this->items as $i => $item) : ?>
                        <?php
                        $orderkey   = array_search($item->id, $this->ordering[$item->parent_id]);
                        $canEdit    = $user->authorise('core.edit',       'com_jdownloads.category.' . $item->id);
                        $canCheckin = $user->authorise('core.admin',      'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                        $canEditOwn = $user->authorise('core.edit.own',   'com_jdownloads.category.' . $item->id) && $item->created_user_id == $userId;
                        $canChange  = $user->authorise('core.edit.state', 'com_jdownloads.category.' . $item->id) && $canCheckin;

                        // Get the parents of item for sorting
                        if ($item->level > 1)
                        {
                            $parentsStr = "";
                            $_currentParentId = $item->parent_id;
                            $parentsStr = " " . $_currentParentId;
                            for ($i2 = 0; $i2 < $item->level; $i2++)
                            {
                                foreach ($this->ordering as $k => $v)
                                {
                                    $v = implode("-", $v);
                                    $v = "-" . $v . "-";
                                    if (strpos($v, "-" . $_currentParentId . "-") !== false)
                                    {
                                        $parentsStr .= " " . $k;
                                        $_currentParentId = $k;
                                        break;
                                    }
                                }
                            } 
                        }
                        else
                        {
                            $parentsStr = "";
                        }
                        ?>
                        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->parent_id; ?>" item-id="<?php echo $item->id ?>" parents="<?php echo $parentsStr ?>" level="<?php echo $item->level ?>">
                            <td class="order nowrap center hidden-phone">
                                <?php
                                $iconClass = '';
                                if (!$canChange)
                                {
                                    $iconClass = ' inactive';
                                }
                                elseif (!$saveOrder)
                                {
                                    $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
                                }
                                ?>
                                <span class="sortable-handler<?php echo $iconClass ?>">
                                    <span class="icon-menu"></span>
                                </span>
                                <?php if ($canChange && $saveOrder) : ?>
                                    <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" />
                                <?php endif; ?>
                            </td>
                            <td class="center">
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td class="center">
                                <div class="btn-group">
                                    <?php echo JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange); ?>
                                </div>
                            </td>
                            <td class="small hidden-phone" style="text-align:center;">
                            <?php if ($item->pic != '') { 
                                $cat_pic_url = $cat_pic_folder.$this->escape($item->pic);
                                ?>
                                <img src="<?php echo JURI::root().JRoute::_( $cat_pic_url ); ?>" width="28px" height="28px" style="vertical-align: middle; border:0px;"/>
                            <?php } ?>
                        </td>
                            <td>
                                <?php echo JLayoutHelper::render('joomla.html.treeprefix', array('level' => $item->level)); ?>
                                <?php if ($item->checked_out) : ?>
                                    <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'categories.', $canCheckin); ?>
                                <?php endif; ?>
                                <?php if ($canEdit || $canEditOwn) : ?>
                                    <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_jdownloads&task=category.edit&id=' . $item->id); ?>" title="<?php echo JText::_('COM_JDOWNLOADS_EDIT_CAT_EDIT'); ?>">
                                        <?php echo $this->escape($item->title); ?></a>
                                <?php else : ?>
                                    <?php echo $this->escape($item->title); ?>
                                <?php endif; ?>
                                <span class="small" title="">
                                    <?php if (empty($item->notes)) : ?>
                                        <?php echo JText::sprintf('COM_JDOWNLOADS_LIST_ALIAS', $this->escape($item->alias)); ?>
                                    <?php else : ?>
                                        <?php echo JText::sprintf('COM_JDOWNLOADS_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->notes)); ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="small hidden-phone" style="text-align:center;">
                                <?php
                                    $description = JHtml::_('string.truncate', $this->escape(strip_tags($item->description)), 400, true, false); // Do not cut off words; HTML not allowed;

                                    if ($description != '') {
                                        echo JHtml::_('tooltip', $description, JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_DESCRIPTION_SHORT'), JURI::root().'administrator/components/com_jdownloads/assets/images/tooltip_blue.gif'); 
                                    }
                                ?>
                            </td>                            
                            <td class="small hidden-phone" style="text-align:center;">
                                <?php
                                    if ($item->parent_id > 1) {
                                        echo JHtml::_('tooltip',strip_tags($params->get('files_uploaddir').DS.$item->cat_dir_parent.DS.$item->cat_dir), JText::_('COM_JDOWNLOADS_CATSLIST_PATH'), JURI::root().'administrator/components/com_jdownloads/assets/images/tooltip_blue.gif'); 
                                    } else {
                                        echo JHtml::_('tooltip',strip_tags($params->get('files_uploaddir').DS.$item->cat_dir), JText::_('COM_JDOWNLOADS_CATSLIST_PATH'), JURI::root().'administrator/components/com_jdownloads/assets/images/tooltip_blue.gif');
                                    }    
                                ?>
                            </td>                            
                            <?php 
                            if (isset($this->items[0]) && property_exists($this->items[0], 'count_published')) : ?>
                                <td class="center btns hidden-phone hidden-tablet">
                                    <a class="badge <?php if ($item->count_published > 0) echo "badge-success"; ?>" title="<?php echo JText::_('COM_JDOWNLOADS_PUBLISHED_DOWNLOADS');?>" href="<?php echo JRoute::_('index.php?option=com_jdownloads&view=downloads&filter[category_id]=' . (int) $item->id . '&filter[published]=1' . '&filter[level]=' . (int) $item->level);?>">
                                        <?php echo $item->count_published; ?></a>
                                </td>
                            <?php endif;?>
                            <?php if (isset($this->items[0]) && property_exists($this->items[0], 'count_unpublished')) : ?>
                                <td class="center btns hidden-phone hidden-tablet">
                                    <a class="badge <?php if ($item->count_unpublished > 0) echo "badge-important"; ?>" title="<?php echo JText::_('COM_JDOWNLOADS_UNPUBLISHED_DOWNLOADS');?>" href="<?php echo JRoute::_('index.php?option=com_jdownloads&view=downloads&filter[category_id]=' . (int) $item->id . '&filter[published]=0' . '&filter[level]=' . (int) $item->level);?>">
                                        <?php echo $item->count_unpublished; ?></a>
                                </td>
                            <?php endif;?>

                            <td class="small hidden-phone">
                                <?php echo $this->escape($item->access_level); ?>
                            </td>
                            
                            <?php // Added to support the Joomla Language Associations
                                  if ($assoc) : ?>
                                        <td class="hidden-phone hidden-tablet">
                                            <?php if ($item->association) : ?>
                                                <?php echo JHtml::_('jdownloadsadministrator.catAssociation', $item->id); ?>
                                            <?php endif; ?>
                                        </td>
                            <?php endif; ?>
                            
                            <td class="small nowrap hidden-phone">
                                <?php echo JLayoutHelper::render('joomla.content.language', $item); ?>
                            </td>
                            <td class="hidden-phone">
                                <span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt); ?>">
                                    <?php echo (int) $item->id; ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php echo $this->pagination->getListFooter(); ?>
            
            <?php // Load the batch processing form. ?>
            <?php if ($user->authorise('core.create', 'com_jdownloads')
                        && $user->authorise('core.edit', 'com_jdownloads')
                        && $user->authorise('core.edit.state', 'com_jdownloads')) : ?>
                        <?php echo JHtml::_(
                                'bootstrap.renderModal',
                                'collapseModal',
                                array(
                                    'title' => JText::_('COM_JDOWNLOADS_BATCH_CAT_OPTIONS'),
                                    'footer' => $this->loadTemplate('batch_footer')
                                ),
                                $this->loadTemplate('batch')
                            ); ?>
            <?php endif; ?>
        <?php endif; ?>

    </div>
    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>    
    </div>
</form>
