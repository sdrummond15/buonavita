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

// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$app = JFactory::getApplication();

if ($app->isClient('site')){
    JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

require_once JPATH_ROOT . '/components/com_jdownloads/helpers/route.php';

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load jD language
JFactory::getLanguage()->load('com_jdownloads', JPATH_ADMINISTRATOR);

$document = JFactory::getDocument();
$document->addScript(JURI::root().'administrator/components/com_jdownloads/assets/js/admin-downloads-modal.js');

JHtml::_('behavior.core');
JHtml::_('behavior.polyfill', array('event'), 'lt IE 9');
JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
JHtml::_('bootstrap.popover', '.hasPopover', array('placement' => 'bottom'));
JHtml::_('behavior.multiselect');

JHtml::_('formbehavior.chosen', '.multipleTags', null, array('placeholder_text_multiple' => JText::_('COM_JDOWNLOADS_SELECT_TAG')));
JHtml::_('formbehavior.chosen', '.multipleCategories', null, array('placeholder_text_multiple' => JText::_('COM_JDOWNLOADS_SELECT_CATEGORY')));
JHtml::_('formbehavior.chosen', '.multipleAccessLevels', null, array('placeholder_text_multiple' => JText::_('COM_JDOWNLOADS_SELECT_ACCESS')));
JHtml::_('formbehavior.chosen', '.multipleAuthors', null, array('placeholder_text_multiple' => JText::_('COM_JDOWNLOADS_SELECT_AUTHOR')));
JHtml::_('formbehavior.chosen', 'select');

// Path to the layouts folder 
$basePath = JPATH_ROOT .'/administrator/components/com_jdownloads/layouts';
$options['base_path'] = $basePath;

$function  = $app->input->getCmd('function', 'jSelectDownload');
$editor    = $app->input->getCmd('editor', '');

$listOrder = str_replace(' ' . $this->state->get('list.direction'), '', $this->state->get('list.fullordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$onclick   = $this->escape($function);

if (!empty($editor))
{
    // This view is used also in com_menus. Load the xtd script only if the editor is set!
    JFactory::getDocument()->addScriptOptions('xtd-downloads', array('editor' => $editor));
    $onclick = "jSelectDownload";
}

?>

<div class="container-popup">
<form action="<?php echo JRoute::_('index.php?option=com_jdownloads&view=downloads&layout=modal&tmpl=component&function='.$function.'&'.JSession::getFormToken().'=1');?>" method="post" name="adminForm" id="adminForm" class="form-inline">
	
    <?php if ($function == 'jSelectDownload_jform_other_file_id'):?>
        <div class="alert">
            <?php echo JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_ONLY_WITH_FILES'); ?>
        </div>
    <?php endif; ?>    
	
    <div id="j-main-container">
    <?php 
    echo JLayoutHelper::render('searchtools.default', array('view' => $this), $basePath, $options); ?>

        <div class="clearfix"></div>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                <?php echo JText::_('COM_JDOWNLOADS_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else : ?>
            <table class="table table-striped table-condensed">
                <thead>
                    <tr>
                        <th style="width: 1%;" class="center nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_STATUS', 'a.published', $listDirn, $listOrder); ?>
                        </th>
                        <th class="title">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th style="width: 5%;" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_RELEASE', 'a.release', $listDirn, $listOrder ); ?>
                        </th>                        
                        <th style="width: 5%;" class="nowrap hidden-phone" style="text-align:center;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_DESCRIPTION', 'a.description', $listDirn, $listOrder ); ?>
                        </th> 
                        <th style="width: 5%;" class="nowrap hidden-phone" style="text-align:center;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_FILENAME', 'a.url_download', $listDirn, $listOrder ); ?>
                        </th> 
                        <th style="width: 5%;" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort',  'COM_JDOWNLOADS_BACKEND_FILESLIST_AUTHOR', 'author_name', $listDirn, $listOrder); ?>
                        </th> 
                        <th style="width: 10%;" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort',  'COM_JDOWNLOADS_ACCESS', 'a.access', $listDirn, $listOrder); ?>
                        </th>
                        <th style="width: 10%;" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_LANGUAGE', 'language', $listDirn, $listOrder); ?>
                        </th>
                        <th style="width: 5%;" class="nowrap hidden-phone" style="text-align:center;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_BACKEND_FILESLIST_DADDED', 'a.created', $listDirn, $listOrder ); ?>
                        </th>
                        <th style="width: 1%;" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_JDOWNLOADS_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="10">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                <?php
                $iconStates = array(
                    0 => 'icon-unpublish',
                    1 => 'icon-publish'
                );
                ?>
                <?php 
                foreach ($this->items as $i => $item) : ?>
                    <?php if ($item->language && JLanguageMultilang::isEnabled())
                    {
                        $tag = strlen($item->language);
                        if ($tag == 5)
                        {
                            $lang = substr($item->language, 0, 2);
                        }
                        elseif ($tag == 6)
                        {
                            $lang = substr($item->language, 0, 3);
                        }
                        else {
                            $lang = "";
                        }
                    }
                    elseif (!JLanguageMultilang::isEnabled())
                    {
                        $lang = "";
                    } 
                    ?>
                    <tr class="row<?php echo $i % 2; ?>">
                        <td class="center">
                            <span class="<?php echo $iconStates[$this->escape($item->published)]; ?>" aria-hidden="true"></span>
                        </td>
                        <td>
                            <?php $attribs = 'data-function="' . $this->escape($onclick) . '"'
                                . ' data-id="' . $item->id . '"'
                                . ' data-title="' . $this->escape($item->title) . '"'
                                . ' data-cat-id="' . $this->escape($item->catid) . '"'
                                . ' data-uri="' . $this->escape(JDownloadsHelperRoute::getDownloadRoute($item->id, $item->catid, $item->language)) . '"'
                                . ' data-language="' . $this->escape($lang) . '"';
                            ?>
                            <a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>>
                                <?php echo $this->escape($item->title); ?></a>
                            <span class="small break-word">
                                <?php if (empty($item->notes)) : ?>
                                    <?php echo JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_CAT') . ": " . $this->escape($item->category_title); ?>
                                    <?php echo JText::sprintf('COM_JDOWNLOADS_LIST_ALIAS', $this->escape($item->alias)); ?>
                                <?php else : ?>
                                    <?php echo JText::sprintf('COM_JDOWNLOADS_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->notes)); ?>
                                <?php endif; ?>
                             </span>
                            <div class="small">
                                <?php echo JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_CAT') . ': ' . $this->escape($item->category_title); ?>
                            </div>
                        </td>
                        
                        
                       <!-- <td>
                            <a href="javascript:void(0);" onclick="if (window.parent) window.parent.<?php echo $this->escape($function); ?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->title)); ?>', '<?php echo $this->escape($item->catid); ?>', null, '<?php echo $this->escape(JDownloadsHelperRoute::getDownloadRoute($item->id, $item->catid, $item->language)); ?>', '<?php echo $this->escape($lang); ?>', null);">
                                <?php echo $this->escape($item->title); ?></a>
                            <div class="small">
                                <?php echo JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_CAT') . ": " . $this->escape($item->category_title); ?>
                            </div>
                        </td> -->
                        <td class="small hidden-phone">
                            <?php echo $this->escape($item->release); ?>
                        </td>
                        <td class="small hidden-phone" style="text-align:center;">
                            <?php
                            if (strlen($item->description) > 200 ) {
                                $description_short = $this->escape(strip_tags(substr($item->description, 0, 200).' ...'));
                            } else {
                                $description_short = $this->escape(strip_tags($item->description));
                            }
                            if ($description_short != '') {
                                echo JHtml::_('tooltip', $description_short, JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_DESCRIPTION_SHORT'), JURI::root().'administrator/components/com_jdownloads/assets/images/tooltip_blue.gif'); 
                            }
                            ?>
                        </td>
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
                        <td class="small hidden-phone">
                                <a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->created_by); ?>" title="<?php echo JText::_('COM_JDOWNLOADS_BACKEND_FILESLIST_AUTHOR'); ?>">
                                <?php echo $this->escape($item->author_name); ?></a>
                        </td>                        
                        <td class="small hidden-phone">
                            <?php 
                            if ($item->user_access && $item->single_user_access){
                                echo '<span class="badge badge-important" style="font-weight:normal;">'.$this->escape($item->single_user_access).'</span>';
                            } else {
                                echo $this->escape($item->access_level);
                            }
                             ?>                            
                        </td>
                        <td class="small hidden-phone">
                            <?php if ($item->language == '*'): ?>
                                <?php echo JText::alt('COM_JDOWNLOADS_ALL', 'language'); ?>
                            <?php else:?>
                                <?php echo $item->language_title ? JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, array('title' => $item->language_title), true) . '&nbsp;' . $this->escape($item->language_title) : JText::_('COM_JDOWNLOADS_UNDEFINED'); ?>
                            <?php endif;?>
                        </td>
                        <td class="nowrap small hidden-phone">
                            <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
                        </td>
                        <td class="nowrap small hidden-phone">
                            <?php echo (int) $item->id; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="forcedLanguage" value="<?php echo $app->input->get('forcedLanguage', '', 'CMD'); ?>" />
        <?php echo JHtml::_('form.token'); ?>
        </div>
    </form>
</div>