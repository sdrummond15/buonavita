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
    ?>
</div>
