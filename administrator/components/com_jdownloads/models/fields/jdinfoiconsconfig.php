<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

class JFormFieldjdInfoIconsConfig extends JFormFieldText
{
     /**
     * The form field type.
     *
     * @var string
     * @since 1.6
     */
     protected $type = 'jdinfoiconsconfig';

     protected function getInput()
     {
        $params = JComponentHelper::getParams( 'com_jdownloads' );
        $msize  = $params->get( 'info_icons_size' );

        $text = parent::getInput();
         
        $sample_path = JURI::root().'images/jdownloads/miniimages/'; 
        $sample_pic = '<img src="'.$sample_path.'date.png" align="middle" width="'.$msize.'" height="'.$msize.'" border="0" alt="" /> ';
        $sample_pic .= '<img src="'.$sample_path.'language.png" align="middle" width="'.$msize.'" height="'.$msize.'" border="0" alt="" /> ';
        $sample_pic .= '<img src="'.$sample_path.'weblink.png" align="middle" width="'.$msize.'" height="'.$msize.'" border="0" alt="" />';
        $sample_pic .= '<img src="'.$sample_path.'stuff.png" align="middle" width="'.$msize.'" height="'.$msize.'" border="0" alt="" /> ';
        $sample_pic .= '<img src="'.$sample_path.'contact.png" align="middle" width="'.$msize.'" height="'.$msize.'" border="0" alt="" /> ';
        $sample_pic .= '<img src="'.$sample_path.'system.png" align="middle" width="'.$msize.'" height="'.$msize.'" border="0" alt="" />';
        $sample_pic .= '<img src="'.$sample_path.'currency.png" align="middle" width="'.$msize.'" height="'.$msize.'" border="0" alt="" /> ';
        $sample_pic .= '<img src="'.$sample_path.'download.png" align="middle" width="'.$msize.'" height="'.$msize.'" border="0" alt="" />';
        $text .='<p style="margin-top:15px;">'.$sample_pic.'</p>';
                                         
        return $text;
    }  
}    
?>