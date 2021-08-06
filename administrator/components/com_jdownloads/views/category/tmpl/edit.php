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

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

use Joomla\CMS\HTML\HTMLHelper;

JHtml::_('bootstrap.tooltip');

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');    
HTMLHelper::_('behavior.calendar');
HTMLHelper::_('behavior.tabstate');
HTMLHelper::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0 ));

$params = $this->state->params;

$options['ini_upload_max_filesize'] = JDownloadsHelper::return_bytes(ini_get('upload_max_filesize'));
$options['admin_images_folder']     = JURI::root().'administrator/components/com_jdownloads/assets/images/';
$options['cat_pic_size']            = $params->get('cat_pic_size');
$options['categories_editor']       = 1;
$options['create_auto_cat_dir']     = $params->get('create_auto_cat_dir');

$app = JFactory::getApplication();
$input = $app->input;

jimport( 'joomla.form.form' );

$basePath = JPATH_ROOT .'/administrator/components/com_jdownloads/layouts';

$assoc = JLanguageAssociations::isEnabled();

// Are associations implemented for this extension?
$extensionassoc = array_key_exists('item_associations', $this->form->getFieldsets());

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('jmetadata', 'item_associations');

// In case of modal
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';


?>

<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'category.cancel' || document.formvalidator.isValid(document.getElementById('category-form'))) {
            Joomla.submitform(task, document.getElementById('category-form'));
        }
        else {
            alert('<?php echo $this->escape(JText::_('COM_JDOWNLOADS_VALIDATION_FORM_FAILED'));?>');
        }
    }
    
    // get the selected file name to view the cat pic new 
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
</script>

<form accept-charset="utf-8" action="<?php echo JRoute::_('index.php?option=com_jdownloads&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="category-form" enctype="multipart/form-data" class="form-validate">

    <?php echo JLayoutHelper::render('edit.title_alias_catdir', $this, $basePath, $options); ?>

    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('JCATEGORY')); ?>
        <div class="row-fluid">
            <div class="span9">
                 <?php echo $this->form->getLabel('description'); ?>
                 <div class="clr"></div> 
                 <?php echo $this->form->getInput('description'); ?>
            </div>
            <div class="span3">
                <?php echo JLayoutHelper::render('edit.global', $this, $basePath); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'additional', JText::_('COM_JDOWNLOADS_ADDITIONAL_DATA')); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <?php echo JLayoutHelper::render('edit.images_cat', $this, $basePath, $options); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        
        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_JDOWNLOADS_PUBLISHING_DETAILS')); ?>
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <?php 
                echo JLayoutHelper::render('edit.publishingdata_cat', $this, $basePath, $options); ?>
            </div>
            <div class="span6">
                    <?php echo JLayoutHelper::render('edit.metadata', $this, $basePath, $options); ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        
        <?php if ( ! $isModal && $assoc && $extensionassoc) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
            <?php echo $this->loadTemplate('associations'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php elseif ($isModal && $assoc && $extensionassoc) : ?>
            <div class="hidden"><?php echo $this->loadTemplate('associations'); ?></div>
        <?php endif; ?>
        
        <?php 
        if ($this->canDo->get('core.admin')) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'rules', JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL')); ?>
            <?php echo $this->form->getInput('rules'); ?>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php endif; ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <?php
            if ($params->get('create_auto_cat_dir')){
                if (!$this->item->id){            
                    // cat_dir is defined as required, so we need a default value here
                    echo '<input type="hidden" name="jform[cat_dir]" value="DUMMY" />';
                }         
            }        
        ?>
        <?php echo $this->form->getInput('extension'); ?>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>" />
        <input type="hidden" name="view" value="category" />         
        <input type="hidden" name="cat_dir_org" value="<?php echo $this->item->cat_dir; ?>" />
        <input type="hidden" name="cat_dir_parent_org" value="<?php echo $this->item->cat_dir_parent; ?>" />
        <input type="hidden" name="cat_title_org" value="<?php echo $this->item->title; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>    
    