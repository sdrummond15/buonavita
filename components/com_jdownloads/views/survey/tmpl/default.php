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

use Joomla\CMS\HTML\HTMLHelper;

    JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

    // For Tooltip
    JHtml::_('bootstrap.tooltip');
    
    HTMLHelper::_('behavior.keepalive');
    HTMLHelper::_('behavior.formvalidator');
    HTMLHelper::_('behavior.calendar');

    $jinput = JFactory::getApplication()->input; 
    
    $captcha_valid  = false;
    $captcha_invalid_msg = '';
    
    // which fields must be viewed
    $survey_fields = (array)json_decode($this->user_rules->form_fieldset);

    if ($this->user_rules->view_captcha){
        // already done?
        $captcha  = (int)JDHelper::getSessionDecoded('jd_captcha_run');
        if ($captcha == 2){
            // succesful
            $captcha_valid = true;
        } else {
        
            // get captcha plugin
            JPluginHelper::importPlugin('captcha');
            $plugin = JPluginHelper::getPlugin('captcha', 'recaptcha');

            // Get plugin param
            $pluginParams = new JRegistry($plugin->params);
            $captcha_version = $pluginParams->get('version');
            $public_key = $pluginParams->get('public_key');        
            
            $dispatcher = JDispatcher::getInstance();
            $dummy = $jinput->getString('g-recaptcha-response');
            if (!$dummy) $dummy = $jinput->getString('recaptcha_response_field');        
            
            // check now whether user has used the captcha already
            if (isset($dummy)){
                    $captcha_res = $dispatcher->trigger('onCheckAnswer', $dummy);
                    if (!$captcha_res[0]){
                        // init again for next try
                        $dispatcher->trigger('onInit','dynamic_recaptcha_1');
                        $captcha_invalid_msg = JText::_('COM_JDOWNLOADS_FIELD_CAPTCHA_INCORRECT_HINT');
                    } else {
                        $captcha_valid = true;
                    }
            } else {
                // init for first try
                $exist_event = $dispatcher->trigger('onInit','dynamic_recaptcha_1');
                
                // When plugin event not exist, we must do the work without it. But give NOT a public info about this problem.
                if (!$exist_event){
                    $captcha_valid = true;
                }
            } 
        }   
    } else {
        // we need this switch to handle the data output 
        $captcha_valid = true;
    }    

    // required for captcha
    $form_uri = JFactory::getURI();
    $form_uri = $form_uri->toString();
    $form_uri = $this->escape($form_uri);    
    
    // Create shortcuts to some parameters.
    $params     = $this->state->params;
    $user       = JFactory::getUser();    

?>
    <script type="text/javascript">
        Joomla.submitbutton = function(task) {
            if (task == 'survey.skip' || document.formvalidator.isValid(document.getElementById('adminForm'))) {
                Joomla.submitform(task);
            } else if (task == 'survey.abort'){
                   window.history.go(-1);
            } else {
                alert('<?php echo $this->escape(JText::_('COM_JDOWNLOADS_VALIDATION_FORM_FAILED'));?>');
            }
        }
    </script>       


<div class="edit jd-item-page<?php echo $this->pageclass_sfx; ?>">

    <?php

    $is_admin   = false;

    if (JDHelper::checkGroup('8', true) || JDHelper::checkGroup('7', true)){
        $is_admin = true;
    }    

    // view offline message - but admins can view it always    
    if ($params->get('offline') && !$is_admin){
        if ($params->get('offline_text') != '') {
            echo JDHelper::getOnlyLanguageSubstring($params->get('offline_text')).'</div>';
        }
    } else {         
    ?>

<form action="<?php echo $form_uri; ?>" name="adminForm" method="post" id="adminForm" class="form-validate" accept-charset="utf-8">

        <fieldset>
            <?php echo '<div class="alert alert-info">'.JText::_('COM_JDOWNLOADS_SURVEY_INFO').'</div>'; ?> 

            <legend>
                <?php echo JText::_('COM_JDOWNLOADS_SURVEY_TEXT'); ?>
            </legend>
            
            <?php 
                // view it only when captcha_valid var is false  
                if (!$captcha_valid){
                    echo ' '.JText::_('COM_JDOWNLOADS_FORM_VERIFY_HUMAN'); 
                          
                    // add captcha
                    if ($captcha_version == '1.0'){
                        $captcha = '<div id="jd_container" class="jd_recaptcha">';                            
                    } elseif ($captcha_version == '2.0') {    
                        $captcha = '<div class="jd_recaptcha">';
                        $captcha .= '<div class="g-recaptcha" data-sitekey="'.$public_key.'"></div>';
                    }

                    $captcha .= '<div id="dynamic_recaptcha_1"></div>';
                    $captcha .= '<br /><input type="submit" name="submit" id="jd_captcha" class="button" value="'.JText::_('COM_JDOWNLOADS_FORM_BUTTON_TEXT').'" />';

                    if ($captcha_invalid_msg != ''){
                        $captcha .= $captcha_invalid_msg;
                    } 
                    
                    $captcha .= '</div>'; 
                    
                    echo $captcha;
        
                } else { 
                    ?>
                
                    <div class="formelm-buttons" style="padding-bottom: 15px;">
                        <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('survey.send')">
                            <?php echo JText::_('COM_JDOWNLOADS_SEND'); ?>
                        </button>
                        <?php if (!$this->user_rules->must_form_fill_out){?>
                            <button type="button" class="btn" onclick="Joomla.submitbutton('survey.skip')">
                                <?php echo JText::_('COM_JDOWNLOADS_SURVEY_SKIP'); ?>
                        </button> 
                        <?php } else { ?>
                            <button type="button" class="btn" onclick="Joomla.submitbutton('survey.abort')">
                                <?php echo JText::_('COM_JDOWNLOADS_SURVEY_ABORT'); ?>
                            </button> 
                        <?php } ?>
                    </div>
                    
                    <?php
                    if (in_array(1, $survey_fields)){ ?>
                        <div class="formelm">
                            <?php echo $this->form->getLabel('name'); ?>
                            <?php echo $this->form->getInput('name'); ?>
                        </div>
                    <?php
                    }
                    ?>

                    <?php
                    if (in_array(2, $survey_fields)){ ?>
                        <div class="formelm">
                            <?php echo $this->form->getLabel('company'); ?>
                            <?php echo $this->form->getInput('company'); ?>
                        </div>
                    <?php
                    }
                    ?>

                    <?php
                    if (in_array(3, $survey_fields)){ ?>
                        <div class="formelm">
                            <?php echo $this->form->getLabel('country'); ?>
                            <?php echo $this->form->getInput('country'); ?>
                        </div>
                    <?php
                    }
                    ?>

                    <?php
                    if (in_array(4, $survey_fields)){ ?>
                        <div class="formelm">
                            <?php echo $this->form->getLabel('address'); ?>
                            <?php echo $this->form->getInput('address'); ?>
                        </div>
                    <?php
                    }
                    ?>
                    
                    <?php
                    if (in_array(5, $survey_fields)){ ?>
                        <div class="formelm">
                            <?php echo $this->form->getLabel('email'); ?>
                            <?php echo $this->form->getInput('email'); ?>
                        </div>
                    <?php
                    }
                    ?>
                    
                    <div class="formelm">
                        <?php echo $this->form->getLabel('catid'); ?>
                        <?php echo $this->form->getInput('catid'); ?>
                    </div>

                    <div class="formelm">
                        <?php echo $this->form->getLabel('id'); ?>
                        <?php echo $this->form->getInput('id'); ?>
                    </div>                     

                    <div class="formelm">
                        <?php echo $this->form->getLabel('cat_title'); ?>
                        <?php echo $this->form->getInput('cat_title'); ?>
                    </div>
                    
                    <div class="formelm">
                        <?php echo $this->form->getLabel('title'); ?>
                        <?php echo $this->form->getInput('title'); ?>
                    </div>

                    <div class="formelm">
                        <?php echo $this->form->getLabel('url_download'); ?>
                        <?php echo $this->form->getInput('url_download'); ?>
                    </div>                    

        <?php } ?>
        
        </fieldset>
        
        <input type="hidden" name="task" value="survey" />
        <input type="hidden" name="return" value="<?php echo $this->return_page;?>" />         
        
        <?php echo JHtml::_('form.token'); ?>

    <div class="clr"></div>
    </form>
    
    <?php } 

    // back button
    /* if ($params->get('view_back_button')){
        echo '<a href="javascript:history.go(-1)">'.JText::_('COM_JDOWNLOADS_FRONTEND_BACK_BUTTON').'</a>'; 
    } */ ?>

    </div>