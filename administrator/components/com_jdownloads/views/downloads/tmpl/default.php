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

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('COM_JDOWNLOADS_SELECT_TAG')));
JHtml::_('formbehavior.chosen', '.multipleCategories', null, array('placeholder_text_multiple' => JText::_('COM_JDOWNLOADS_SELECT_CATEGORY')));
JHtml::_('formbehavior.chosen', '.multipleAccessLevels', null, array('placeholder_text_multiple' => JText::_('COM_JDOWNLOADS_SELECT_ACCESS')));
JHtml::_('formbehavior.chosen', '.multipleAuthors', null, array('placeholder_text_multiple' => JText::_('COM_JDOWNLOADS_SELECT_AUTHOR')));
JHtml::_('formbehavior.chosen', 'select');

use Joomla\String\StringHelper; 

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$app    = JFactory::getApplication();
$user   = JFactory::getUser();
$userId = $user->get('id');

$params = $this->state->params;

// Path to the layouts folder 
$basePath = JPATH_ROOT .'/administrator/components/com_jdownloads/layouts';
$options['base_path'] = $basePath;

// Path to the mime type image folder (for file symbols) 
switch ($params->get('selected_file_type_icon_set'))
{
    case 1:
        $file_pic_folder = 'images/jdownloads/fileimages/';
        break;
    case 2:
        $file_pic_folder = 'images/jdownloads/fileimages/flat_1/';
        break;
    case 3:
        $file_pic_folder = 'images/jdownloads/fileimages/flat_2/';
        break;
}


// Path to the image folders  
$thumbnails_folder = JURI::root().'images/jdownloads/screenshots/thumbnails/';
$screenshots_folder = JURI::root().'images/jdownloads/screenshots/';

// Path to the preview file symbol
$preview_symbol = JURI::root().'administrator/components/com_jdownloads/assets/images/external_blue.gif';

$extern_symbol = JURI::root().'administrator/components/com_jdownloads/assets/images/link_extern.gif';

// Path to the preview files folder
$previews_folder =  JUri::root().basename($params->get('files_uploaddir')).'/'.$params->get('preview_files_folder_name').'/';

// Amount of columns (for 'colspan')
$columns = 14; 

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if (strpos($listOrder, 'publish_up') !== false){
    $orderingColumn = 'publish_up';
} elseif (strpos($listOrder, 'publish_down') !== false){
    $orderingColumn = 'publish_down';
} elseif (strpos($listOrder, 'modified') !== false){
    $orderingColumn = 'modified';
} else {
    $orderingColumn = 'created';
}

$amount_preview_files = $this->state->get('amount_previews', 0);
$amount_images      = (int)$params->get('be_amount_of_pics_in_downloads_list', 10);
$view_preview_file  = (int)$params->get('view_preview_file_in_downloads_list', 1);
$view_price_field   = (int)$params->get('view_price_field_in_downloads_list', 1);


if ($saveOrder){
    $saveOrderingUrl = 'index.php?option=com_jdownloads&task=downloads.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'downloadList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$canOrder    = $user->authorise('core.edit.state', 'com_jdownloads');

// Added to support the Joomla Language Associations
$assoc = JLanguageAssociations::isEnabled();

?>
<form action="<?php echo JRoute::_('index.php?option=com_jdownloads&view=downloads');?>" method="POST" name="adminForm" id="adminForm">

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
    echo JLayoutHelper::render('searchtools.default', array('view' => $this), $basePath, $options);
    ?>  

    <?php if (empty($this->items)) : ?>
        <div class="alert alert-no-items">
            <?php echo JText::_('COM_JDOWNLOADS_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
        <table class="table table-striped" id="downloadList">
            <thead>
                <tr>
                    <th class="nowrap center hidden-phone" style="width:1%;">
                        <?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'COM_JDOWNLOADS_ORDERING', 'icon-menu-2'); ?>
                    </th>
                    <th class="center" style="width:1%;">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th class="nowrap center" style="min-width:55px; width:1%;">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_STATUS', 'a.published', $listDirn, $listOrder); ?>
                    </th>
                    <th class="nowrap hidden-phone" style="width:1%;">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_PIC', 'a.file_pic', $listDirn, $listOrder ); ?>
                    </th>
                    <th class="nowrap" style="min-width:100px">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_TITLE', 'a.title', $listDirn, $listOrder); ?>
                    </th>
                    <th class="nowrap hidden-phone" style="width:5%;">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_RELEASE', 'a.release', $listDirn, $listOrder ); ?>
                    </th>                        
                    <th class="nowrap hidden-phone" style="text-align:center; width:5%">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_DESCRIPTION', 'a.description', $listDirn, $listOrder ); ?>
                    </th> 
                    <th class="nowrap hidden-phone" style="text-align:center; width:5%">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_FILENAME', 'a.url_download', $listDirn, $listOrder ); ?>
                    </th> 
                    <?php if ($amount_preview_files && $view_preview_file): ?>
                              <?php $columns++; ?>
                              <th class="nowrap hidden-phone" style="text-align:center; width:5%">
                                  <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_PREVIEW_FILE', 'a.preview_filename', $listDirn, $listOrder ); ?>
                              </th>
                    <?php endif; ?>
                    <th class="nowrap hidden-phone" style="width:5%;">
                        <?php echo JHtml::_('searchtools.sort',  'COM_JDOWNLOADS_BACKEND_FILESLIST_AUTHOR', 'a.created_by', $listDirn, $listOrder); ?>
                    </th>                        
                    <th class="nowrap hidden-phone" style="width:5%;">
                        <?php echo JHtml::_('searchtools.sort',  'COM_JDOWNLOADS_ACCESS', 'a.access', $listDirn, $listOrder); ?>
                    </th>
                    <?php // Added to support the Joomla Language Associations
                        if ($assoc) : ?>
                            <?php $columns++; ?>
                            <th class="nowrap hidden-phone" style="width:5%;">
                                <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_HEADING_ASSOCIATION', 'association', $listDirn, $listOrder); ?>
                            </th>
                    <?php endif; ?>
					<th class="nowrap hidden-phone" style="width:5%;">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                    </th>
                    <th class="nowrap hidden-phone" style="text-align:center; width:2%;">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_HEADING_DATE_' . strtoupper($orderingColumn), 'a.' . $orderingColumn, $listDirn, $listOrder); ?>
                    </th>
                    <th class="nowrap hidden-phone" style="width:1%;">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_HITS', 'a.downloads', $listDirn, $listOrder); ?>
                    </th>
                    <?php if ($view_price_field): ?>
	                    <th class="nowrap hidden-phone" style="width:1%;">
	                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_PRICE', 'a.price', $listDirn, $listOrder); ?>
	                    </th>
                    <?php endif; ?>    
                    <th class="nowrap hidden-phone" style="width:1%;">
                        <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_ID', 'a.id', $listDirn, $listOrder); ?>
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
            <?php foreach ($this->items as $i => $item) :
                $item->max_ordering = 0;
                $ordering   = ($listOrder == 'a.ordering');
                $canCreate  = $user->authorise('core.create',     'com_jdownloads.category.' . $item->catid);
                $canEdit    = $user->authorise('core.edit',       'com_jdownloads.download.' . $item->id);
                $canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
                $canEditOwn = $user->authorise('core.edit.own',   'com_jdownloads.download.' . $item->id) && $item->created_by == $userId;
                $canChange  = $user->authorise('core.edit.state', 'com_jdownloads.download.' . $item->id) && $canCheckin;
                $canEditCat    = $user->authorise('core.edit',       'com_jdownloads.category.' . $item->catid);
                $canEditOwnCat = $user->authorise('core.edit.own',   'com_jdownloads.category.' . $item->catid) && $item->category_uid == $userId;
                $canEditParCat    = $user->authorise('core.edit',       'com_jdownloads.category.' . $item->parent_category_id);
                $canEditOwnParCat = $user->authorise('core.edit.own',   'com_jdownloads.category.' . $item->parent_category_id) && $item->parent_category_uid == $userId;
                
                // Build images array
                $images = array();
                if ($item->images) $images = explode('|', $item->images);
                ?>
                <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->catid; ?>">
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
                            echo JHtml::_('jgrid.published', $item->published, $i, 'downloads.', $canChange, 'cb', $item->publish_up, $item->publish_down); 
                            echo JDownloadsHelper::setFeatured($item->featured, $i, $canChange); 
                            ?>
                        </div>
                    </td>
					<!-- symbol -->
                    <td class="small hidden-phone" style="text-align:center;">
                        <?php if ($item->file_pic != '') { 
                            $file_pic_url = $file_pic_folder.$this->escape($item->file_pic);
                            ?>
                            <img src="<?php echo JURI::root().JRoute::_( $file_pic_url ); ?>" width="38px" height="38px" style="vertical-align: middle; border:0px" />
                        <?php } ?>
                    </td>
                    <td class="has-context">
                        <div class="pull-left break-word">
                            <?php if ($item->checked_out) : ?>
                                <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'downloads.', $canCheckin); ?>
                            <?php endif; ?>
                            
                            <?php if ($item->language == '*'):?>
                                <?php $language = JText::alt('COM_JDOWNLOADS_ALL', 'language'); ?>
                            <?php else:?>
                                <?php $language = $item->language_title ? $this->escape($item->language_title) : JText::_('COM_JDOWNLOADS_UNDEFINED'); ?>
                            <?php endif;?>
                            
                            <?php 
                            if ($canEdit || $canEditOwn) : ?>
                                <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_jdownloads&task=download.edit&id=' . $item->id); ?>" title="<?php echo JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_TITLE'); ?>">
                                    <?php echo $this->escape($item->title); ?></a>
                            <?php else : ?>
                                <span title="<?php echo JText::sprintf('COM_JDOWNLOADS_ALIAS', $this->escape($item->alias)); ?>"><?php echo $this->escape($item->title); ?></span>
                            <?php endif; ?>
                            <span class="small break-word">
                                <?php if (empty($item->notes)) : ?>
                                        <?php echo JText::sprintf('COM_JDOWNLOADS_LIST_ALIAS', $this->escape($item->alias)); ?>
                                <?php else : ?>
                                        <?php echo JText::sprintf('COM_JDOWNLOADS_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->notes)); ?>
                                <?php endif; ?>
                            </span>
                            <div class="small">
                                <?php
                                    $ParentCatUrl = JRoute::_('index.php?option=com_jdownloads&task=category.edit&id=' . $item->parent_category_id);
                                    $CurrentCatUrl = JRoute::_('index.php?option=com_jdownloads&task=category.edit&id=' . $item->catid);
                                    $EditCatTxt = JText::_('COM_JDOWNLOADS_EDIT_CAT_EDIT');

                                        echo JText::_('COM_JDOWNLOADS_CATEGORY') . ': ';

                                        if ($item->category_level != '1') :
                                            if ($item->parent_category_level != '1') :
                                                echo ' &#187; ';
                                            endif;
                                        endif;

                                        if (JFactory::getLanguage()->isRtl())
                                        {
                                            if ($canEditCat || $canEditOwnCat) :
                                                echo '<a class="hasTooltip" href="' . $CurrentCatUrl . '" title="' . $EditCatTxt . '">';
                                            endif;
                                            echo $this->escape($item->category_title);
                                            if ($canEditCat || $canEditOwnCat) :
                                                echo '</a>';
                                            endif;

                                            if ($item->category_level != '1') :
                                                echo ' &#171; ';
                                                if ($canEditParCat || $canEditOwnParCat) :
                                                    echo '<a class="hasTooltip" href="' . $ParentCatUrl . '" title="' . $EditCatTxt . '">';
                                                endif;
                                                echo $this->escape($item->category_title_parent);
                                                if ($canEditParCat || $canEditOwnParCat) :
                                                    echo '</a>';
                                                endif;
                                            endif;
                                        }
                                        else
                                        {
                                            if ($item->category_level != '1') :
                                                if ($canEditParCat || $canEditOwnParCat) :
                                                    echo '<a class="hasTooltip" href="' . $ParentCatUrl . '" title="' . $EditCatTxt . '">';
                                                endif;
                                                echo $this->escape($item->category_title_parent);
                                                if ($canEditParCat || $canEditOwnParCat) :
                                                    echo '</a>';
                                                endif;
                                                echo ' &#187; ';
                                            endif;
                                            if ($canEditCat || $canEditOwnCat) :
                                                echo '<a class="hasTooltip" href="' . $CurrentCatUrl . '" title="' . $EditCatTxt . '">';
                                            endif;
                                            echo $this->escape($item->category_title);
                                            if ($canEditCat || $canEditOwnCat) :
                                                echo '</a>';
                                            endif;
                                        }
                                    ?>
                            
                            <?php 
                            if ($images) { 
	                              $all_images = count($images);
	                              if ($all_images < $amount_images){
	                                  $numbers = $all_images;
	                              } else {
	                                  $numbers = $amount_images;
	                              }
	                              
	                              if ($amount_images > 0){ 
	                                  echo '<div class="small">';
	                                  
	                                  for ($i=0; $i < $numbers; $i++) {
	                                      $img = $this->escape($images[$i]);
	                                      if ($params->get('use_lightbox_function')){
	                                          echo '<a href="'.$screenshots_folder.$img.'" data-lightbox="lightbox'.$item->id.'" data-title="'.$img.'" target="_blank"><img src="'.$thumbnails_folder.$img.'" class="img-polaroid" alt="'.$img.'" style="width:30px; height:30px"></a>';    
	                                      } else {
	                                          echo '<a href="'.$screenshots_folder.$img.'" target="_blank"><img src="'.$thumbnails_folder.$img.'" class="img-polaroid" alt="'.$img.'" style="width:30px; height:30px"></a>';    
	                                      }
	                                  }
	                                  echo '</div>';
	                              } else {
	                                  if (count($images) > 1){
	                                      echo '<span class="icon-images" style="font-size:16px; padding-left:5px; padding-right:5px;"></span>';  
	                                  } else {
	                                      echo '<span class="icon-image" style="font-size:16px; padding-left:5px; padding-right:5px;"></span>';  
	                                  }
	                              }
                            } ?>
                            </div>
                        </div>
                    </td>
                    
                    <td class="small hidden-phone">
                        <?php echo $this->escape($item->release); ?>
                    </td>                        
					<!-- description -->                    
                    
                    <td class="small hidden-phone" style="text-align:center;">
                        <?php
                        $description = JHtml::_('string.truncate', $this->escape(strip_tags($item->description)), 400, true, false); // Do not cut off words; HTML not allowed;

                        if ($description != '') {
                            echo JHtml::_('tooltip', $description, JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_DESCRIPTION_SHORT'), JURI::root().'administrator/components/com_jdownloads/assets/images/tooltip_blue.gif'); 
                        }
                        ?>
                    </td>
					<!-- File -->                    
                    
                    <td class="small hidden-phone" style="text-align:center;">
                        <?php
                        if ($item->url_download !=''){
                            echo JHtml::_('tooltip',strip_tags($item->url_download),  JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_FILENAME'), JURI::root().'administrator/components/com_jdownloads/assets/images/file_blue.gif'); 
                        } elseif ($item->extern_file != ''){
                            echo JHtml::_('tooltip',strip_tags($item->extern_file), JText::_('COM_JDOWNLOADS_BACKEND_FILE_EDIT_EXT_DOWNLOAD_TITLE'), JURI::root().'administrator/components/com_jdownloads/assets/images/external_orange.gif'); 
                        } elseif ($item->other_file_id > 0){
                            echo JHtml::_('tooltip',strip_tags(JText::sprintf('COM_JDOWNLOADS_BACKEND_FILESLIST_OTHER_DOWNLOADS_FILE_NAME', $item->other_file_name)), JText::sprintf('COM_JDOWNLOADS_BACKEND_FILESLIST_OTHER_DOWNLOADS_FILE_USED', $item->other_download_title), JURI::root().'administrator/components/com_jdownloads/assets/images/file_orange.gif'); 
                        } else {
                            // only a document without any files     
                            echo JHtml::_('tooltip',strip_tags(JText::_('COM_JDOWNLOADS_DOCUMENT_DESC1')), JText::_('COM_JDOWNLOADS_BACKEND_TEMPPANEL_TABTEXT_INFO'), JURI::root().'administrator/components/com_jdownloads/assets/images/tooltip_red.gif'); 
                        }
                        ?>
                    </td>
                    
                    <!-- Preview File -->                    
                    <?php if ($amount_preview_files && $view_preview_file): ?>
                        <td class="small hidden-phone" style="text-align:center;">
                            <?php
                            if ($item->preview_filename != ''){
                                $preview = $this->escape($item->preview_filename);
                                $filename = basename($preview);
                                $file_extension = strtolower(jFile::getExt($filename));
                                
                                $tooltip = JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_PREVIEW_TOOLTIP').'<br />'.$preview;
                                //echo '<a class="hasTooltip" href="'.$previews_folder.$preview.'" target="_blank" title="'.$tooltip.'"> <img src="'.$preview_symbol.'" class="" alt="'.$preview.'"></a>';
                                $prevModal = 'prevModal'.$item->id;
                                echo '<a href="#'.$prevModal.'" role="button" class="hasTooltip" title="'.$tooltip.'" data-toggle="modal"><img src="'.$preview_symbol.'" class="" alt="'.$preview.'"></a>';
                                
                                // Modal window 
                                $prevModal = 'prevModal'.$item->id;
                                ?>
                                
                                <div id="<?php echo $prevModal; ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                  <div class="modal-dialog">
                                      <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 id="myModalLabel"><?php echo JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_PREVIEW_FILE'); ?></h3>
                                      </div>
                                      <div class="modal-body">
                                        <p>
                                        <?php 
                                            switch($file_extension)
                                            {
                                                case 'mp4':
                                                case 'webm':
                                                case 'ogg':
                                                    echo '<video width="576" height="436" controls><source src="'.$previews_folder.$preview.'">Your browser does not support the video tag.</video>';    
                                                    break;
                                                
                                                case 'mp3':
                                                case 'wav':
                                                    echo '<audio width="400" height="150" controls><source src="'.$previews_folder.$preview.'">Your browser does not support the audio tag.</video>';
                                                    break;
                                            }
                                        ?></p>
                                        <div class="container">
                                            <span class="label label-info"><?php echo $preview; ?></span>
                                        </div>
                                      </div>
                                      <div class="modal-footer">
                                        <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_JDOWNLOADS_TOOLBAR_CLOSE'); ?></button>
                                      </div>
                                  </div>    
                                </div>
                                <?php
                            }
                            ?>
                        </td>
                    <?php endif; ?>
                    <!-- Author -->
                    
                    <td class="small hidden-phone">
                            <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_AUTHOR'); ?>">
                            <?php echo $this->escape($item->author_name); ?></a>
                    </td>

                    <!-- Access -->                    
                    
                    <td class="small hidden-phone">
                        <?php 
                        
                        if ($item->user_access && $item->single_user_access){
                            $user_name = JHtml::_('string.truncate', $this->escape(strip_tags($item->single_user_access_name)), 15);
                            ?>
                            <a class="badge badge-important hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->user_access); ?>" title="<?php echo JText::_('COM_JDOWNLOADS_USER_ACCESS').':<br />'.$this->escape($item->single_user_access_name); ?>"><?php echo $user_name; ?></a>
                            <?php
                        } else {
                            echo $this->escape($item->access_level);
                        }
                         ?>
                    </td>
                    
                    <?php // Added to support the Joomla Language Associations
                        if ($assoc) : ?>
                            <td class="hidden-phone">
                                <?php if ($item->association) : ?>
                                    <?php echo JHtml::_('jdownloadsadministrator.association', $item->id); ?>
                                <?php endif; ?>
                            </td>
                    <?php endif; ?>
                    <!-- Download Language -->
                    <td class="small hidden-phone">
                        <?php if ($item->language == '*'):?>
                            <?php echo JText::alt('COM_JDOWNLOADS_ALL', 'language'); ?>
                        <?php else:?>
                            <?php echo $item->language_title ? JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true) . '&nbsp;' . $this->escape($item->language_title) : JText::_('COM_JDOWNLOADS_UNDEFINED'); ?>
                        <?php endif;?>
                    </td>
                    <!-- Date Created -->                    
                    
                    <td class="nowrap small hidden-phone">
                        <?php 
                            $date = $item->{$orderingColumn};
                            echo $date > 0 ? JHtml::_('date', $date, JText::_('DATE_FORMAT_LC4')) : '-';
                        ?>
                    </td>
                    <!-- Downloaded -->
                    <td class="hidden-phone center">
                        <span class="badge badge-info">
                            <?php echo (int) $item->downloads; ?>
                        </span>
                    </td>
                    <!-- Price -->
                    <?php if ($view_price_field): ?>
	                    <td class="hidden-phone center">
	                        <?php if ($item->price != ''): ?>
	                        <span class="badge badge-important">
	                            <?php echo $this->escape($item->price); ?>
	                        </span>
	                        <?php else:?>
	                        <span class="badge badge-important">
	                            <?php echo ''; ?>
	                        </span>
	                        <?php endif;?>
	                    </td>
                    <?php endif; ?>    
                    <!-- ID -->
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
    
    <?php echo $this->pagination->getListFooter(); ?>          
                            
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>    
</div>
</form>
