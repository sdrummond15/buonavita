<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('filelist');

class JFormFieldjdcssbuttonconfig extends JFormFieldList
{
     /**
     * The form field type.
     *
     * @var string
     */
     protected $type = 'jdcssbuttonconfig';

     protected function getInput()
     {
        $document = JFactory::getDocument();
        $document->addStyleSheet('../components/com_jdownloads/assets/css/jdownloads_buttons.css');
        $document->addStyleSheet('../administrator/components/com_jdownloads/assets/css/style.css');
        
        $hint = $this->element['hint'];
        if ($hint){
            $hint = '<p>'.JText::_($hint).'</p>';
        }

        $type = $this->element['csstype'];
                
        switch ($type) {
            case 'colors':
                 $buttons = $hint;
                 $buttons .= '<span class="jdbutton jblack jmedium">'.JText::_('COM_JDOWNLOADS_BUTTON_BLACK').'</span> <span class="jdbutton jwhite jmedium">'.JText::_('COM_JDOWNLOADS_BUTTON_WHITE').'</span> <span class="jdbutton jgray jmedium">'.JText::_('COM_JDOWNLOADS_BUTTON_GRAY').'</span> <span class="jdbutton jorange jmedium">'.JText::_('COM_JDOWNLOADS_BUTTON_ORANGE').'</span> <span class="jdbutton jred jmedium">'.JText::_('COM_JDOWNLOADS_BUTTON_RED').'</span>'
                            .'<span class="jdbutton jblue jmedium">'.JText::_('COM_JDOWNLOADS_BUTTON_BLUE').'</span> <span class="jdbutton jgreen jmedium">'.JText::_('COM_JDOWNLOADS_BUTTON_GREEN').'</span> <span class="jdbutton jrosy jmedium">'.JText::_('COM_JDOWNLOADS_BUTTON_ROSY').'</span> <span class="jdbutton jpink jmedium">'.JText::_('COM_JDOWNLOADS_BUTTON_PINK').'</span>';
            break;
            
            case 'status':
                 $buttons = $hint;
                 $buttons .= '<span class="jdbutton jblack jstatus">'.JText::_('COM_JDOWNLOADS_BUTTON_BLACK').'</span> <span class="jdbutton jwhite jstatus">'.JText::_('COM_JDOWNLOADS_BUTTON_WHITE').'</span> <span class="jdbutton jgray jstatus">'.JText::_('COM_JDOWNLOADS_BUTTON_GRAY').'</span> <span class="jdbutton jorange jstatus">'.JText::_('COM_JDOWNLOADS_BUTTON_ORANGE').'</span> <span class="jdbutton jred jstatus">'.JText::_('COM_JDOWNLOADS_BUTTON_RED').'</span>'
                            .'<span class="jdbutton jblue jstatus">'.JText::_('COM_JDOWNLOADS_BUTTON_BLUE').'</span> <span class="jdbutton jgreen jstatus">'.JText::_('COM_JDOWNLOADS_BUTTON_GREEN').'</span> <span class="jdbutton jrosy jstatus">'.JText::_('COM_JDOWNLOADS_BUTTON_ROSY').'</span> <span class="jdbutton jpink jstatus">'.JText::_('COM_JDOWNLOADS_BUTTON_PINK').'</span>';
            break;
            
            case 'sizes':
                 $buttons = $hint;
                 $buttons .= '<span class="jdbutton jred">'.JText::_('COM_JDOWNLOADS_BUTTON_STANDARD').'</span> <span class="jdbutton jred jmedium">'.JText::_('COM_JDOWNLOADS_BUTTON_MEDIUM').'</span>'
                             .'<span class="jdbutton jred jsmall">'.JText::_('COM_JDOWNLOADS_BUTTON_SMALL').'</span>';
            break;
         }

        $list = parent::getInput();
        $list .= '<p>'.$buttons.'</p>';
        
        return $list;    
    }  
}    
     
?>