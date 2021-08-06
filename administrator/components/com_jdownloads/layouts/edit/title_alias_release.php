<?php
defined('JPATH_BASE') or die;

$form = $displayData->getForm();

$title = $form->getField('title') ? 'title' : ($form->getField('name') ? 'name' : '');

?>
<div class="form-inline form-inline-header">
	<?php
	echo $title ? $form->renderField($title) : '';
	echo $form->renderField('alias');
    echo $form->renderField('release');
	?>
</div>
