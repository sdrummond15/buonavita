<?php
defined('JPATH_BASE') or die;

$options = $this->options;
$form = $displayData->getForm();
$id = $displayData->getForm()->getValue('id');

$title = $form->getField('title') ? 'title' : ($form->getField('name') ? 'name' : '');


?>
<div class="form-inline form-inline-header">
	<?php

	echo $title ? $form->renderField($title) : '';

	echo $form->renderField('alias');
	
    if ($options['create_auto_cat_dir']){
        if ($id){ 
           // change category
           $form->setFieldAttribute( 'cat_dir',  'readonly', 'true' );
           $form->setFieldAttribute( 'cat_dir', 'required', 'false' );
           $form->setFieldAttribute( 'cat_dir', 'class', 'readonly' );
           $form->setFieldAttribute( 'cat_dir', 'description', JText::_('COM_JDOWNLOADS_EDIT_CAT_DIR_TITLE_MSG') );
           echo $form->getLabel('cat_dir'); 
           echo $form->getInput('cat_dir'); 
        } else { 
           // add category 
           echo $form->getLabel('cat_dir_parent');
           echo $form->getInput('cat_dir_parent');
        }    
    } else {
         // auto creation is set off
         echo $form->getLabel('cat_dir');
         echo $form->getInput('cat_dir');
    }
    
    ?>
</div>
