<?php

defined('JPATH_BASE') or die;

    $options = $this->options;
    $form = $displayData->getForm();
    $params = JComponentHelper::getParams('com_jdownloads');
    
    
    $displayData->getForm()->getValue('id');
    

    echo $form->renderField('view_captcha');
    echo $form->renderField('myspacer');
    echo $form->renderField('view_report_form');
    echo $form->renderField('myspacer');
    echo $form->renderField('view_inquiry_form');
    echo $form->renderField('must_form_fill_out');
    echo $form->renderField('form_fieldset');
    echo $form->renderField('myspacer');
    echo $form->renderField('countdown_timer_duration');
    echo $form->renderField('countdown_timer_msg');
    echo $form->renderField('myspacer');
    echo $form->renderField('view_user_his_limits');
    echo $form->renderField('view_user_his_limits_msg');
    echo $form->renderField('myspacer');
    echo $form->renderField('notes');
    echo $form->renderField('id');
    echo $form->renderField('group_id');
    
?>