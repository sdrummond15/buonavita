<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 * 
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * @package jDownloads
 * @version 3.9  
 * Some parts from the search component (and search content plugin) adapted and modified to can use it in jDownloads as an internal search function. 
 */

// no direct access
defined('_JEXEC') or die;

    $db         = JFactory::getDBO(); 
    $document   = JFactory::getDocument();
    $jinput     = JFactory::getApplication()->input;
    $app        = JFactory::getApplication();    
    $params     = $app->getParams(); 
    $user       = JFactory::getUser();
    
    $lang = JFactory::getLanguage();
    $upper_limit = $lang->getUpperLimitSearchWord();
    
    // get jD user limits and settings
    $jd_user_settings = JDHelper::getUserRules();    

    $html       = '';
    $layout     = '';

    // Get the needed layout data - type = 7 for a 'search form' layout
    // Please note that he are stored only header data not the layout self - not the best solutions. So perhaps will we change it in later releases.            
    $layout = JDHelper::getLayout(7);
    if ($layout){
        $layout_text = $layout->template_text;
        $header      = $layout->template_header_text;
        $subheader   = $layout->template_subheader_text;
        $footer      = $layout->template_footer_text;
    } else {
        // We have not a valid layout data
        echo '<big>No valid layout found!</big>';
    }
    

    // get current category menu ID when exist and all needed menu IDs for the header links
    $menuItemids = JDHelper::getMenuItemids(0);
    
    // get all other menu category IDs so we can use it when we needs it
    $cat_link_itemids = JDHelper::getAllJDCategoryMenuIDs();
    
    // "Home" menu link itemid
    $root_itemid =  $menuItemids['root'];
    
    // Get CSS button settings
    $menu_color             = $params->get('css_menu_button_color');
    $menu_size              = $params->get('css_menu_button_size');
    
    if ($params->get('css_buttons_with_font_symbols')){
        $span_home_symbol   = '<span class="icon-home-2 jd-menu-icon"> </span>';
        $span_search_symbol = '<span class="icon-search jd-menu-icon"> </span>';
        $span_upper_symbol  = '<span class="icon-arrow-up-2 jd-menu-icon"> </span>';
        $span_upload_symbol = '<span class="icon-new jd-menu-icon"> </span>';
    } else {
        $span_home_symbol   = '';
        $span_search_symbol = '';
        $span_upper_symbol  = '';
        $span_upload_symbol = '';
    }      
    
    // ==========================================
    // HEADER SECTION
    // ==========================================

    if ($header != ''){
        
        $menuItemids = JDHelper::getMenuItemids(0);
        
        // component title - not more used. So we must replace the placeholder from layout with spaces!
        $header = str_replace('{component_title}', '', $header);
        
        // replace both Google adsense placeholder with script
        $header = JDHelper::insertGoogleAdsenseCode($header);        
        
        // components description
        if ($params->get('downloads_titletext') != '') {
            $header_text = stripslashes(JDHelper::getOnlyLanguageSubstring($params->get('downloads_titletext')));

            // replace both Google adsense placeholder with script
            $header_text = JDHelper::insertGoogleAdsenseCode($header_text); 
            $header .= $header_text;
        }        
        
        // check $Itemid exist
        if (!isset($menuItemids['search'])) $menuItemids['search'] = $menuItemids['root'];
        if (!isset($menuItemids['upload'])) $menuItemids['upload'] = $menuItemids['root'];
        
        // build home link        
        $home_link = '<a href="'.JRoute::_('index.php?option=com_jdownloads&amp;Itemid='.$menuItemids['root']).'" title="'.JText::_('COM_JDOWNLOADS_HOME_LINKTEXT_HINT').'">'.'<span class="jdbutton '.$menu_color.' '.$menu_size.'">'.$span_home_symbol.JText::_('COM_JDOWNLOADS_HOME_LINKTEXT').'</span>'.'</a>';
        
        // build search link
        $search_link = '<a href="'.JRoute::_('index.php?option=com_jdownloads&amp;view=search&amp;Itemid='.$menuItemids['search']).'" title="'.JText::_('COM_JDOWNLOADS_SEARCH_LINKTEXT_HINT').'">'.'<span class="jdbutton '.$menu_color.' '.$menu_size.'">'.$span_search_symbol.JText::_('COM_JDOWNLOADS_SEARCH_LINKTEXT').'</span>'.'</a>';        

        // build frontend upload link
        $upload_link = '<a href="'.JRoute::_('index.php?option=com_jdownloads&amp;view=form&amp;layout=edit&amp;Itemid='.$menuItemids['upload']).'"  title="'.JText::_('COM_JDOWNLOADS_UPLOAD_LINKTEXT_HINT').'">'.'<span class="jdbutton '.$menu_color.' '.$menu_size.'">'.$span_upload_symbol.JText::_('COM_JDOWNLOADS_UPLOAD_LINKTEXT').'</span>'.'</a>';
        
        // build level up link
        $upper_link = JRoute::_('index.php?option=com_jdownloads&amp;view=categories&amp;Itemid='.$menuItemids['root']);
        
        $header = str_replace('{home_link}', $home_link, $header);
        $header = str_replace('{search_link}', $search_link, $header);
        
        if ($jd_user_settings->uploads_view_upload_icon){
            if ($this->view_upload_button){
                $header = str_replace('{upload_link}', $upload_link, $header);
            } else {
                $header = str_replace('{upload_link}', '', $header);
            }             
        } else {
            $header = str_replace('{upload_link}', '', $header);
        }          
        $header = str_replace('{upper_link}', '<a href="'.$upper_link.'"  title="'.JText::_('COM_JDOWNLOADS_UPPER_LINKTEXT_HINT').'">'.'<span class="jdbutton '.$menu_color.' '.$menu_size.'">'.$span_upper_symbol.JText::_('COM_JDOWNLOADS_UPPER_LINKTEXT').'</span>'.'</a>', $header);    
        
        // create category listbox and viewed it when it is activated in configuration
        if ($params->get('show_header_catlist')){
            
            // get current selected cat id from listbox
            $catlistid = 0;
            
            $orderby_pri = '';
            $data = JDHelper::buildCategorySelectBox($catlistid, $cat_link_itemids, $root_itemid, $params->get('view_empty_categories', 1), $orderby_pri );            
            
            // build special selectable URLs for category listbox
            $root_url       = JRoute::_('index.php?option=com_jdownloads&Itemid='.$root_itemid);
            $allfiles_url   = str_replace('Itemid[0]', 'Itemid', JRoute::_('index.php?option=com_jdownloads&view=downloads&Itemid='.$root_itemid));
            $topfiles_url   = str_replace('Itemid[0]', 'Itemid', JRoute::_('index.php?option=com_jdownloads&view=downloads&type=top&Itemid='.$root_itemid));
            $newfiles_url   = str_replace('Itemid[0]', 'Itemid', JRoute::_('index.php?option=com_jdownloads&view=downloads&type=new&Itemid='.$root_itemid));
            
            $listbox = JHtml::_('select.genericlist', $data['options'], 'cat_list', 'class="inputbox" title="'.JText::_('COM_JDOWNLOADS_SELECT_A_VIEW').'" onchange="gocat(\''.$root_url.'\', \''.$allfiles_url.'\', \''.$topfiles_url.'\',  \''.$newfiles_url.'\'  ,\''.$data['url'].'\')"', 'value', 'text', $data['selected'] ); 
            
            $header = str_replace('{category_listbox}', '<form name="go_cat" id="go_cat" method="post">'.$listbox.'</form>', $header);
        } else {                                                                        
            $header = str_replace('{category_listbox}', '', $header);         
        }
        
        $html .= $header;  

    }

    // ==========================================
    // SUB HEADER SECTION
    // ==========================================

    if ($subheader != ''){
        
        // replace both Google adsense placeholder with script
        $subheader = JDHelper::insertGoogleAdsenseCode($subheader);    
        $html .= $subheader;            
    }

    // ==========================================
    // MAIN SECTION
    // ==========================================    
    if ($layout_text) {
        // replace both Google adsense placeholder with script
        $layout_text = JDHelper::insertGoogleAdsenseCode($layout_text);                 
    } 

    $html .= $layout_text;
    
    // remove empty html tags
    if ($params->get('remove_empty_tags')){
        $html = JDHelper::removeEmptyTags($html);
    }
    
    echo $html;    
    
?>

<form id="searchForm" action="<?php echo JRoute::_('index.php?option=com_jdownloads&view=search');?>" method="post" accept-charset="utf-8">

    <div class="btn-toolbar">	
	    <div class="btn-group pull-left">
            <label for="search-searchword" class="element-invisible">
			<?php echo JText::_('COM_JDOWNLOADS_SEARCH_KEYWORD'); ?>
		</label>
		    <input type="text" name="searchword" title="<?php echo JText::_('COM_JDOWNLOADS_SEARCH_KEYWORD'); ?>" placeholder="<?php echo JText::_('COM_JDOWNLOADS_SEARCH_KEYWORD'); ?>" id="search-searchword" size="30" maxlength="<?php echo $upper_limit; ?>" value="<?php echo $this->escape($this->origkeyword); ?>" class="inputbox" />
	    </div>
        <div class="btn-group pull-left">
            <button name="Search"  onclick="this.form.submit()" class="btn hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_JDOWNLOADS_SEARCH');?>">
				<span class="icon-search"></span>
				<?php echo JText::_('COM_JDOWNLOADS_SEARCH'); ?>
			</button>
			<button name="Resetit" onclick="this.form.reset.value = 1;this.form.submit();" class="btn hasTooltip" title="<?php echo JHtml::_('tooltipText', 'COM_JDOWNLOADS_SEARCH_RESET');?>">
				<span class="icon-refresh"></span>
				<?php echo JText::_('COM_JDOWNLOADS_SEARCH_RESET'); ?>
			</button>
        
        <input type="hidden" name="task" value="search" />
        <input type="hidden" name="reset" value="" />
        </div>
        <div class="clearfix"></div>
    </div>

	<div class="searchintro<?php echo $this->params->get('pageclass_sfx'); ?>">
		<?php if (!empty($this->searchword)):?>
        <div><?php echo JText::plural('COM_JDOWNLOADS_SEARCH_KEYWORD_N_RESULTS', '<span class="badge badge-info">' . $this->total . '</span>'); ?></div>
		<?php endif;?>
	</div>

	<fieldset class="phrases">
		<legend><?php echo JText::_('COM_JDOWNLOADS_SEARCH_FOR');?>
		</legend>
			<div class="phrases-box">
			<?php echo $this->lists['searchphrase']; ?>
			</div>
			<div class="ordering-box">
			<label for="ordering" class="ordering">
				<?php echo JText::_('COM_JDOWNLOADS_SEARCH_ORDERING');?>
			</label>
			<?php echo $this->lists['ordering'];?>
			</div>
	</fieldset>

	<?php if ($this->params->get('search_areas', 1)) : ?>
		<fieldset class="only">
		<legend><?php echo JText::_('COM_JDOWNLOADS_SEARCH_ONLY_IN');?></legend>
		<?php foreach ($this->searchareas['search'] as $val => $txt) :
			$checked = is_array($this->searchareas['active']) && in_array($val, $this->searchareas['active']) ? 'checked="checked"' : '';
		?>
		<label for="area-<?php echo $val;?>" class="checkbox">
        <input type="checkbox" name="areas[]" value="<?php echo $val;?>" id="area-<?php echo $val;?>" <?php echo $checked;?> />
				<?php echo JText::_($txt); ?>
		</label>
		<?php endforeach; ?>
		</fieldset>
	<?php endif; ?>

<?php if ($this->total > 0) : ?>

	<div class="form-limit">
		<label for="limit">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
		</label>
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>
<p class="counter">
        <?php echo $this->pagination->getPagesCounter(); ?>
	</p>

<?php endif; ?>

</form>