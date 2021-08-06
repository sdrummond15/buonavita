<?php
defined('JPATH_BASE') or die;

$app       = JFactory::getApplication();
$form      = $displayData->getForm();
$input     = $app->input;

$fields = $displayData->get('fields') ?: array(
	array('parent', 'parent_id'),
	array('published', 'state', 'enabled'),
	array('category', 'catid'),
	'featured',
	'access',
    'user_access',
	'language',
	'tags',
    'license',
    'license_agree',
    'file_language',
    'system',
    'update_active',
	'notes',
    'url',
);

$html   = array();
$html[] = '<fieldset class="form-vertical">';

foreach ($fields as $field)
{
	$field = is_array($field) ? $field : array($field);

	foreach ($field as $f)
	{
		if ($form->getField($f))
		{
			$html[] = $form->renderField($f);
			break;
		}
	}
}

$html[] = '</fieldset>';

echo implode('', $html);
