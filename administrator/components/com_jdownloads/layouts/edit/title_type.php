<?php
defined('JPATH_BASE') or die;

$options = $this->options;
$form = $displayData->getForm();

?>
<div class="form-inline form-inline-header">
	<?php

	echo $form->renderField('template_name');
    
    echo $form->getLabel('template_typ');
    echo $form->getInput('template_typ');
    ?>
</div>
