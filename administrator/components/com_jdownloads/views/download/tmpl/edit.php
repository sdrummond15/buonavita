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
use Joomla\Registry\Registry;

    // Include the component HTML helpers.
    JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

    HTMLHelper::_('behavior.formvalidator');
    HTMLHelper::_('behavior.keepalive');
    HTMLHelper::_('behavior.calendar');
    HTMLHelper::_('behavior.tabstate');
    
    JHtml::_('bootstrap.tooltip');
    JHtml::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));
    JHtml::_('formbehavior.chosen', 'select');
    
    //HTMLHelper::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0 ));

    // Create shortcut to parameters.
    $params = clone $this->state->get('params');
    
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
    
    // Added to support the Joomla Language Associations
    $assoc = JLanguageAssociations::isEnabled();

    $options['ini_upload_max_filesize'] = JDownloadsHelper::return_bytes(ini_get('upload_max_filesize'));
    $options['admin_images_folder'] = JURI::root().'administrator/components/com_jdownloads/assets/images/';
    $options['assigned_file'] = $this->item->url_download;
    $options['assigned_preview_file'] = $this->item->preview_filename;
    $options['file_pic_folder'] = $file_pic_folder;
    $options['file_pic'] = $this->item->file_pic;
    $options['file_pic_size'] = $params->get('file_pic_size');
    $options['files_editor'] = 1;
    $options['images'] = $this->item->images;
    $options['be_upload_amount_of_pictures'] = $params->get('be_upload_amount_of_pictures');

    $images = explode("|", $options['images']);
    $amount_images = count($images);

    // Path to the backend jD images folder 
    $admin_images_folder = JURI::root().'administrator/components/com_jdownloads/assets/images/';
    // Path to the layouts folder 
    $basePath = JPATH_ROOT .'/administrator/components/com_jdownloads/layouts';

    $this->hiddenFieldsets  = array();
    
    $app = JFactory::getApplication();
    $input = $app->input;
    
    // In case of modal
    $isModal = $input->get('layout') == 'modal' ? true : false;
    $layout  = $isModal ? 'modal' : 'edit';
    $tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';


?>

<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'download.cancel' || document.formvalidator.isValid(document.getElementById('download-form'))) {
            Joomla.submitform(task, document.getElementById('download-form'));
        }
        else {
            alert('<?php echo $this->escape(JText::_('COM_JDOWNLOADS_VALIDATION_FORM_FAILED'));?>');
        }
    }
    
    // get the selected file name to view the file type pic new
    function getSelectedText( frmName, srcListName ) 
    {
        var form = eval( 'document.' + frmName );
        var srcList = eval( 'form.' + srcListName );

        i = srcList.selectedIndex;
        if (i != null && i > -1) {
            return srcList.options[i].text;
        } else {
            return null;
        }
    }
    
    function editFilename(){
         document.getElementById('jform_url_download').readOnly = false;
         document.getElementById('jform_url_download').focus();
    }

    function editFilenamePreview(){
         document.getElementById('jform_preview_filename').readOnly = false;
         document.getElementById('jform_preview_filename').focus();
    }    
</script>

<form accept-charset="utf-8" action="<?php echo JRoute::_('index.php?option=com_jdownloads&layout=' . $layout . $tmpl . '&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="download-form" enctype="multipart/form-data" class="form-validate">
    
    <input id="jform_form_title" type="hidden" name="download-form-title"/>
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $options['ini_upload_max_filesize']; ?>" />
    
    <!-- Has the user selected before a file from the 'files list' then we will give him a hint -->
    <?php 
    if (isset($this->selected_filename) && $this->selected_filename != ''){ ?>
        <div class="alert alert-warning"><?php echo JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_NOTE_FILE_SELECTED_IN_LIST'); ?> </div>
        <div class="clr"> </div> 
    <?php } ?> 
    
    <!-- View the title and alias --> 
    <?php echo JLayoutHelper::render('edit.title_alias_release', $this, $basePath); ?>
    
    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

        <!-- ========== GENERAL ============ -->        
        
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_JDOWNLOADS_GENERAL')); ?>
        <div class="row-fluid">
            <div class="span9">
                <fieldset class="adminform">
                    <div>
                        <?php echo $this->form->getLabel('description'); ?>
                        <div class="clr"></div> 
                        <?php echo $this->form->getInput('description'); ?>       
                    </div>
                    <div class="clr"></div>
                    <div>
                        <?php echo $this->form->getLabel('description_long'); ?>
                        <div class="clr"></div> 
                        <?php echo $this->form->getInput('description_long'); ?>       
                    </div>                
                    <div class="clr"></div>

                    <div>
                        <?php echo $this->form->getLabel('changelog'); ?>
                        <div class="clr"></div>
                        <?php echo $this->form->getInput('changelog'); ?>       
                    </div>            
                </fieldset>
            </div>
            <div class="span3">
                <!-- Add the right panel with basicly data: Status, Category, Featured, Access, language and Tags -->
                <?php echo JLayoutHelper::render('edit.global', $this, $basePath); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <!-- ========== FILES DATA ============ -->        
        
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'filesdata', JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_TABTITLE_3')); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <?php echo JLayoutHelper::render('edit.files', $this, $basePath, $options); ?>
            </div>
            <div class="span6">
                <?php echo JLayoutHelper::render('edit.preview', $this, $basePath, $options); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        
        <!-- ========== ADDITIONAL ============ -->
        
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'additional', JText::_('COM_JDOWNLOADS_ADDITIONAL_DATA')); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <?php echo JLayoutHelper::render('edit.additional', $this, $basePath, $options); ?>
                
                <?php echo JLayoutHelper::render('edit.custom_fields', $this, $basePath, $options); ?>
            
            </div>
            
            <div class="span6">
                <?php echo JLayoutHelper::render('edit.images', $this, $basePath, $options); ?>
            </div>
        </div>
        
        <?php echo JHtml::_('bootstrap.endTab'); ?>        
        
        <!-- ========== ADD THE CUSTOM_FIELDS ============ -->
        <?php 
            if (JComponentHelper::isEnabled('com_fields') && $params->get('custom_fields_enable') == 1){
                $this->ignore_fieldsets = array('general', 'info', 'detail', 'jmetadata', 'item_associations');
                echo JLayoutHelper::render('joomla.edit.params', $this);
            }
        ?>

        <!-- ========== PUBLISHING ============ -->        
        <?php // Do not show the publishing options if the edit form is configured not to. Not a part yet from this version. ?>
        <?php //if ($params->show_publishing_options) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_JDOWNLOADS_PUBLISHING_DETAILS')); ?>
            <div class="row-fluid form-horizontal-desktop">
                <div class="span6">
                    <?php echo JLayoutHelper::render('edit.publishingdata', $this, $basePath); ?>
                </div>
                <div class="span6">
                    <?php echo JLayoutHelper::render('edit.metadata', $this, $basePath); ?>
                </div>
            </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php // endif; ?>
        
        <!-- Added to support the Joomla Language Associations  -->
        
        <!-- ========== ASSOCIATIONS ============ -->
        
        <?php if ( ! $isModal && $assoc) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('COM_JDOWNLOADS_HEADING_ASSOCIATION')); ?>
            <?php 
            echo $this->loadTemplate('associations'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php elseif ($isModal && $assoc) : ?>
            <div class="hidden"><?php echo $this->loadTemplate('associations'); ?></div>
        <?php endif; ?>
        
        <!-- ========== PERMISSIONS ============ -->
        
        <?php if ($this->canDo->get('core.admin')) { ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL')); ?>
            <?php 
            if (empty($this->item->id)){ 
                     echo '<div>'.$this->form->getLabel('permissions_warning');
                     echo '</div><p>';
                     echo $this->form->getLabel('spacer');
                     echo '</p>';
            } ?> 
            <?php echo $this->form->getInput('rules'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php } ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>

    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
        <input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>" />

        <input type="hidden" name="view" value="download" />
        <input type="hidden" name="image_file_count" id="image_file_count" value="0" />         
        <input type="hidden" name="cat_dir_org" value="<?php echo $this->item->catid; ?>" />
        <input type="hidden" name="sum_listed_images" id="sum_listed_images" value="<?php echo $amount_images; ?>" />
        <input type="hidden" name="max_sum_images" id="max_sum_images" value="<?php echo (int)$params->get('be_upload_amount_of_pictures'); ?>" /> 
        
        <input type="hidden" name="filename" value="<?php echo $this->item->url_download; ?>" />        
        <input type="hidden" name="modified_date_old" value="<?php echo $this->item->modified; ?>" />
        <input type="hidden" name="submitted_by" value="<?php echo $this->item->submitted_by; ?>" />
        <input type="hidden" name="set_aup_points" value="<?php echo $this->item->set_aup_points; ?>" />
        <input type="hidden" name="filename_org" value="<?php echo $this->item->url_download; ?>" />          
        <input type="hidden" name="preview_filename_org" value="<?php echo $this->item->preview_filename; ?>" /> 
        <input type="hidden" name="file_pic_org" value="<?php echo $this->item->file_pic; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
    <div class="clr"></div>    
</form>    
    