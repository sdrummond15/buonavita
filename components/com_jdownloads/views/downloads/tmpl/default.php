<?php
/**
 * @package jDownloads
 * @version 3.9  
 * @copyright (C) 2007 - 2021 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

 defined('_JEXEC') or die('Restricted access');

 setlocale(LC_ALL, 'C.UTF-8', 'C');
 
    JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
    
    // For Tooltip
    JHtml::_('bootstrap.tooltip');

    $app        = JFactory::getApplication();
    $db         = JFactory::getDBO(); 
    $document   = JFactory::getDocument();
    $jinput     = JFactory::getApplication()->input;
    $user       = JFactory::getUser();
    
    // Get jD user limits and settings
    $jd_user_settings = $this->user_rules;
    
    $listOrder = str_replace('a.', '', $this->escape($this->state->get('list.ordering')));    
    $listDirn  = $this->escape($this->state->get('list.direction'));    
    
    // Create shortcuts to some parameters.
    $params     = $this->params;
    $items      = $this->items;

    $html           = '';
    $html_files     = '';
    $body           = '';
    $footer_text    = '';
    $is_admin       = false;
    
    $jdownloads_root_dir_name = basename($params->get('files_uploaddir'));
    
    // Path to the mime type image folder (for file symbols) 
    $file_pic_folder = JDHelper::getFileTypeIconPath($params->get('selected_file_type_icon_set'));
    
    $checkbox_top_always_added = false;
    
    $date_format = JDHelper::getDateFormat();

    $layout_has_checkbox = false;
    $layout_has_download = false;

    if (JDHelper::checkGroup('8', true) || JDHelper::checkGroup('7', true)){
        $is_admin = true;
    }
    
    // Get the layout data            
    $layout_files = $this->layout;
    if ($layout_files){
        // Unused language placeholders must at first get removed from layout
        $layout_files_text        = JDHelper::removeUnusedLanguageSubstring($layout_files->template_text);
        $header                   = JDHelper::removeUnusedLanguageSubstring($layout_files->template_header_text);
        $subheader                = JDHelper::removeUnusedLanguageSubstring($layout_files->template_subheader_text);
        $footer                   = JDHelper::removeUnusedLanguageSubstring($layout_files->template_footer_text);
    } else {
        // We have not a valid layout data
        echo '<big>No valid layout found for files!</big>';
    }    
    
    
    if ($layout_files->symbol_off == 0 ) {
        $use_mini_icons = true;
    } else {
        $use_mini_icons = false; 
    }
    
    // we may not use in this listing checkboxes for mass downloads, since we have not a category layout with the required checkbox placeholders
    // so will view this listing always with download links
    // deactivate at first the setting when it is used - it is not used, we does nothing
    if ($layout_files->checkbox_off == 0){
        $layout_files->checkbox_off = 1;
        $layout_has_checkbox = true;
        // find out whether we have checkboxes AND download placeholders
        if (strpos($layout_files->template_text, '{url_download}')){
            // we have a layout also with download placeholder 
            $layout_has_download = true;
        }       
    } else {
        if (strpos($layout_files->template_text, '{url_download}')){
            // we have a layout also with download placeholder 
            $layout_has_download = true;
        }  
    }              
    
    // Get CSS button settings
    $menu_color             = $params->get('css_menu_button_color');
    $menu_size              = $params->get('css_menu_button_size');
    $status_color_hot       = $params->get('css_button_color_hot');
    $status_color_new       = $params->get('css_button_color_new');
    $status_color_updated   = $params->get('css_button_color_updated');
    $download_color         = $params->get('css_button_color_download');
    $download_size          = $params->get('css_button_size_download');
    $download_size_mirror   = $params->get('css_button_size_download_mirror');        
    $download_color_mirror1 = $params->get('css_button_color_mirror1');        
    $download_color_mirror2 = $params->get('css_button_color_mirror2');
    $download_size_listings = $params->get('css_button_size_download_small');
    
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
    
    $total_downloads  = $this->pagination->get('total');
    
    // get current category menu ID when exist and all needed menu IDs for the header links
    $menuItemids = JDHelper::getMenuItemids();
    
    // get all other menu category IDs so we can use it when we needs it
    $cat_link_itemids = JDHelper::getAllJDCategoryMenuIDs();
    
    // "Home" menu link itemid
    $root_itemid =  $menuItemids['root'];

    // make sure, that we have a valid menu itemid (we have not a category here)
    $category_menu_itemid = $root_itemid;
        
    $html = '<div class="jd-item-page'.$this->pageclass_sfx.'">';
    
    if ($this->params->get('show_page_heading')) {
        $html .= '<h1>'.$this->escape($this->params->get('page_heading')).'</h1>';
    }    
    
    // ==========================================
    // HEADER SECTION
    // ==========================================

    if ($header != ''){
        
        // component title - not more used. So we must replace the placeholder from layout with spaces!
        $header = str_replace('{component_title}', '', $header);
        
        // Cart option active?
        if ($this->cartplugin && $params->get('use_shopping_cart_plugin')){
            $cart_link = '<div class="cart_cartstatus">
                          <a href="'.$this->current_url.'#jdownloadscart'.'">'.JText::_('COM_JDOWNLOADS_YOUR_CART').': <span class="simpleCart_quantity"></span> '.JText::_('COM_JDOWNLOADS_ITEMS').'</a>
                      </div>';
                      
            $header = str_replace('{cart_link}', $cart_link, $header);                      
        } else {
            $header = str_replace('{cart_link}', '', $header);                      
        }
        
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

        if ($menuItemids['upper'] > 1 && $menuItemids['upper'] != $menuItemids['base']){   // 1 is 'root'
            // exists a single category menu link for the category a level up? 
            $level_up_cat_itemid = JDHelper::getSingleCategoryMenuID($cat_link_itemids, $menuItemids['upper'], $root_itemid);
            $upper_link = JRoute::_('index.php?option=com_jdownloads&amp;view=category&amp;catid='.$menuItemids['upper'].'&amp;Itemid='.$level_up_cat_itemid);
        } else {
            $upper_link = JRoute::_('index.php?option=com_jdownloads&amp;view=categories&amp;Itemid='.$menuItemids['root']);
        }
        $header = str_replace('{upper_link}', '<a href="'.$upper_link.'"  title="'.JText::_('COM_JDOWNLOADS_UPPER_LINKTEXT_HINT').'">'.'<span class="jdbutton '.$menu_color.' '.$menu_size.'">'.$span_upper_symbol.JText::_('COM_JDOWNLOADS_UPPER_LINKTEXT').'</span>'.'</a>', $header);    
        
        // create category listbox and viewed it when it is activated in configuration
        if ($params->get('show_header_catlist')){
            
            // get current selected item value from listbox
            $selected_item = $this->state->get('selected_item');
            switch ($selected_item){
                case 'new': 
                   $catlistid = -2;
                   break;
                case 'top': 
                   $catlistid = -3;
                   break;
                case '': 
                   $catlistid = -1;
                   break;
            }
           
            // get current sort order and direction
            $orderby_pri = $this->params->get('orderby_pri');
            if (!$orderby_pri){
                $orderby_pri = $this->state->get('parameters.menu[orderby_pri]');
            }
         
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
            $catlistid = 0;        
        }
        $html .= $header;  
    }

    // ==========================================
    // SUB HEADER SECTION
    // ==========================================

    if ($subheader != ''){

        // Display number of sub categories only when > 0 
        if ($total_downloads == 0){
            $total_files_text = '';
        } else {
            $total_files_text = JText::_('COM_JDOWNLOADS_NUMBER_OF_DOWNLOADS_LABEL').': '.$total_downloads;
        }
        
        // Display at first the list title
        switch ($catlistid){
            case '-2': 
               $subheader = str_replace('{subheader_title}', JText::_('COM_JDOWNLOADS_SELECT_NEWEST_DOWNLOADS'), $subheader);
               break;
            case '-3': 
               $subheader = str_replace('{subheader_title}', JText::_('COM_JDOWNLOADS_SELECT_HOTTEST_DOWNLOADS'), $subheader);
               $catlistid = -3;
               break;
            case '-1': 
            case '0': 
               $subheader = str_replace('{subheader_title}', JText::_('COM_JDOWNLOADS_FRONTEND_SUBTITLE_OVER_ALL_DOWNLOADS'), $subheader);
               break;
        }        
        
        // Display pagination            
        $subheader = JDHelper::insertPagination($params->get('option_navigate_top'), $this->params->get('show_pagination'), $this->params->get('show_pagination_results'), $this->pagination, $subheader);
        
        // Display amount of files - we use the sub categories placeholder
            $subheader = str_replace('{count_of_sub_categories}', $total_files_text, $subheader); 

        // Display sort order bar
        if ($params->get('view_sort_order') && $total_downloads > 1 && $this->params->get('show_sort_order_bar') != '0'
        || (!$params->get('view_sort_order') && $this->pagination->get('pages.total') > 1 && $this->params->get('show_sort_order_bar') == '1') )
        {
           // We must have at minimum a single field for sorting
           $sortorder_fields = $params->get('sortorder_fields', array());
           
           if ($sortorder_fields){
               if (!is_array($sortorder_fields)){
                   $sortorder_fields = explode(',', $sortorder_fields);
               }
           } else {
               $sortorder_fields = array();
           }
           
           if (count($sortorder_fields)){
           
               $limitstart = $this->pagination->limitstart;
               
               // create form
               $sort_form = '<form action="'.htmlspecialchars(JFactory::getURI()->toString()).'" method="post" name="adminForm" id="adminForm">';
               $sort_form_hidden = '<input type="hidden" name="filter_order" value="" />
                                   <input type="hidden" name="filter_order_Dir" value="" />
                                   <input type="hidden" name="limitstart" value="" /></form>';
                              
               $ordering = '<span class="jd-list-ordering" id="ordering1">'.JHtml::_('grid.sort', JText::_('COM_JDOWNLOADS_FE_SORT_ORDER_DEFAULT'), 'ordering', $listDirn, $listOrder).' | </span>';
               $title    = '<span class="jd-list-title" id="ordering2">'.JHtml::_('grid.sort', JText::_('COM_JDOWNLOADS_FE_SORT_ORDER_NAME'), 'title', $listDirn, $listOrder).' | </span>';
               $author   = '<span class="jd-list-author" id="ordering3">'.JHtml::_('grid.sort', JText::_('COM_JDOWNLOADS_FE_SORT_ORDER_AUTHOR'), 'author', $listDirn, $listOrder).' | </span>';               
               $date     = '<span class="jd-list-date" id="ordering4">'.JHtml::_('grid.sort', JText::_('COM_JDOWNLOADS_FE_SORT_ORDER_DATE'), 'created', $listDirn, $listOrder).' | </span>';
               $hits     = '<span class="jd-list-hits" id="ordering5">'.JHtml::_('grid.sort', JText::_('COM_JDOWNLOADS_FE_SORT_ORDER_HITS'), 'downloads', $listDirn, $listOrder).' | </span>';               
               $featured = '<span class="jd-list-featured" id="ordering6">'.JHtml::_('grid.sort', JText::_('COM_JDOWNLOADS_FE_SORT_ORDER_FEATURED'), 'featured', $listDirn, $listOrder).' | </span>';
               //$ratings  = '<span class="jd-list-ratings" id="ordering7">'.JHtml::_('grid.sort', JText::_('COM_JDOWNLOADS_FE_SORT_ORDER_RATINGS'), 'downloads', $listDirn, $listOrder).' | </span>';               

               $listorder_bar = $sort_form
                                .JText::_('COM_JDOWNLOADS_FE_SORT_ORDER_TITLE').' '
                                .'<br />';
                                
               foreach ($sortorder_fields as $sfield) {
                    switch ($sfield) {
                        case 0:
                            $listorder_bar = $listorder_bar.$ordering;
                            break;
                        case 1:
                            $listorder_bar = $listorder_bar.$title;
                            break;
                        case 2:
                            $listorder_bar = $listorder_bar.$author;
                            break;
                        case 3:
                            $listorder_bar = $listorder_bar.$date;
                            break;
                        case 4:
                            $listorder_bar = $listorder_bar.$hits;
                            break;
                        case 5:
                            $listorder_bar = $listorder_bar.$featured;
                            break;
                        /*case 6:
                            $listorder_bar = $listorder_bar.$ratings;
                            break; */                                                                                                                               
                    }
               }
               // remove | at the end
               $len = strlen($listorder_bar);
               $pos = strripos($listorder_bar, "|");
               $diff = $len - $pos;
               if ($pos > 0 && $diff == 9){
                   $listorder_bar = substr($listorder_bar, 0, ($len - $diff)).'</span>';  
               } 
               // add hidden fields
               $listorder_bar = $listorder_bar.$sort_form_hidden;
                                  
               $subheader = str_replace('{sort_order}', $listorder_bar, $subheader);
           } else {
               $subheader = str_replace('{sort_order}', '', $subheader);          
           }
        } else {   
           $subheader = str_replace('{sort_order}', '', $subheader);          
        }    
        
        // replace both Google adsense placeholder with script
        $subheader = JDHelper::insertGoogleAdsenseCode($subheader);
        
        $html .= $subheader;            
    }
    
    $formid = $total_downloads + 1;
    
    // ==========================================
    // BODY SECTION - VIEW THE DOWNLOADS DATA
    // ==========================================
    
    if ($this->cartplugin && $params->get('use_shopping_cart_plugin')){
    
    	$html_files = '<div class="cart_product_content">';
    }

    if ($layout_files_text != ''){
        
        // build the mini image symbols when used in layout ( 0 = activated !!! )
        if ($use_mini_icons) {
            $msize =  $params->get('info_icons_size');
            $pic_date = '<img src="'.JURI::base().'images/jdownloads/miniimages/date.png" style="text-align:middle;border:0px;" width="'.$msize.'" height="'.$msize.'"  alt="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_DATE').'" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_DATE').'" />&nbsp;';
            $pic_license = '<img src="'.JURI::base().'images/jdownloads/miniimages/license.png" style="text-align:middle;border:0px;" width="'.$msize.'" height="'.$msize.'"  alt="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_LICENCE').'" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_LICENCE').'" />&nbsp;';
            $pic_author = '<img src="'.JURI::base().'images/jdownloads/miniimages/contact.png" style="text-align:middle;border:0px;" width="'.$msize.'" height="'.$msize.'"  alt="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_AUTHOR').'" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_AUTHOR').'" />&nbsp;';
            $pic_website = '<img src="'.JURI::base().'images/jdownloads/miniimages/weblink.png" style="text-align:middle;border:0px;" width="'.$msize.'" height="'.$msize.'"  alt="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_WEBSITE').'" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_WEBSITE').'" />&nbsp;';
            $pic_system = '<img src="'.JURI::base().'images/jdownloads/miniimages/system.png" style="text-align:middle;border:0px;" width="'.$msize.'" height="'.$msize.'"  alt="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_SYSTEM').'" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_SYSTEM').'" />&nbsp;';
            $pic_language = '<img src="'.JURI::base().'images/jdownloads/miniimages/language.png" style="text-align:middle;border:0px;" width="'.$msize.'" height="'.$msize.'"  alt="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_LANGUAGE').'" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_LANGUAGE').'" />&nbsp;';
            $pic_downloads = '<img src="'.JURI::base().'images/jdownloads/miniimages/download.png" style="text-align:middle;border:0px;" width="'.$msize.'" height="'.$msize.'"  alt="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_DOWNLOAD').'" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_DOWNLOAD_HITS').'" />&nbsp;';
            $pic_price = '<img src="'.JURI::base().'images/jdownloads/miniimages/currency.png" style="text-align:middle;border:0px;" width="'.$msize.'" height="'.$msize.'"  alt="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_PRICE').'" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_PRICE').'" />&nbsp;';
            $pic_size = '<img src="'.JURI::base().'images/jdownloads/miniimages/stuff.png" style="text-align:middle;border:0px;" width="'.$msize.'" height="'.$msize.'"  alt="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_FILESIZE').'" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_FILESIZE').'" />&nbsp;';
        } else {
            $pic_date = '';
            $pic_license = '';
            $pic_author = '';
            $pic_website = '';
            $pic_system = '';
            $pic_language = '';
            $pic_downloads = '';
            $pic_price = '';
            $pic_size = '';
        }
        
        
        // build a little pic for extern links
        $extern_url_pic = '<img src="'.JURI::base().'components/com_jdownloads/assets/images/link_extern.gif" alt="external" />';        

        // ===========================================
        // Display now the files (Downloads)
        // ===========================================
        for ($i = 0; $i < count($items); $i++) {
            
            // build the categories path for the file
            if ($items[$i]->category_cat_dir_parent){
                $category_dir = $items[$i]->category_cat_dir_parent.'/'.$items[$i]->category_cat_dir;
            } elseif ($items[$i]->category_cat_dir) {
                $category_dir = $items[$i]->category_cat_dir;
            } else {
                // we have an uncategorised download so we must add the defined folder for this
                $category_dir = $params->get('uncategorised_files_folder_name');
            }            
            
            // When user has access: get data to publish the edit icon and publish data as tooltip
            if ($items[$i]->params->get('access-edit')){
                $editIcon = JDHelper::getEditIcon($items[$i]);
            } else {
                $editIcon = '';
            }            
            
            $has_no_file = false;
            $file_id = $items[$i]->id;

            // when we have not a menu item to the singel download, we need a menu item from the assigned category, or at lates the root itemid
            if ($items[$i]->menuf_itemid){
                $file_itemid =  (int)$items[$i]->menuf_itemid;
            } else {
                $file_itemid = $category_menu_itemid;
            }             
            
            if (!$items[$i]->url_download && !$items[$i]->other_file_id && !$items[$i]->extern_file){
               // only a document without file
               $userinfo = JText::_('COM_JDOWNLOADS_FRONTEND_ONLY_DOCUMENT_USER_INFO');
               $has_no_file = true;           
            }
            
            // use the activated/selected "files" layout text to build the output for every download
            $html_file = $layout_files_text;
            
            // add the content plugin event 'before display content'
            if (strpos($html_file, '{before_display_content}') > 0){
                $html_file = str_replace('{before_display_content}', $items[$i]->event->beforeDisplayContent, $html_file);
            } else {
                $html_file = $items[$i]->event->beforeDisplayContent.$html_file;    
            }

            // for the 'after display title' event can we only use a placeholder - a fix position is not really given
            $html_file = str_replace('{after_display_title}', $items[$i]->event->afterDisplayTitle, $html_file);           
           
            $html_file = str_replace('{file_id}',$items[$i]->id, $html_file);
            
            // replace 'featured' placeholders
            if ($items[$i]->featured){
                // add the css classes
				if ($params->get('use_featured_classes')){
                    $html_file = str_replace('{featured_class}', 'jd_featured', $html_file);
                    $html_file = str_replace('{featured_detail_class}', 'jd_featured_detail', $html_file);
				} else {
					$html_file = str_replace('{featured_class}', '', $html_file);
                    $html_file = str_replace('{featured_detail_class}', '', $html_file);	
				}
                // add the pic
                if ($params->get('featured_pic_filename')){
					$featured_pic_name = $params->get('featured_pic_filename');
                    $featured_pic = '<img class="jd_featured_star" src="'.JURI::base().'images/jdownloads/featuredimages/'.$params->get('featured_pic_filename').'" width="'.$params->get('featured_pic_size').'" height="'.$params->get('featured_pic_size_height').'" alt="'.substr($featured_pic_name,0,-4).'" />';
                    $html_file = str_replace('{featured_pic}', $featured_pic, $html_file);
                } else {
                    $html_file = str_replace('{featured_pic}', '', $html_file);
                }
            } else {
                $html_file = str_replace('{featured_class}', '', $html_file);
                $html_file = str_replace('{featured_detail_class}', '', $html_file);
                $html_file = str_replace('{featured_pic}', '', $html_file);
            }           

            // render the tags
            if ($params->get('show_tags', 1) && !empty($items[$i]->tags->itemTags)){ 
                $items[$i]->tagLayout = new JLayoutFile('joomla.content.tags');
                $html_file = str_replace('{tags}', $items[$i]->tagLayout->render($items[$i]->tags->itemTags), $html_file);
                $html_file = str_replace('{tags_title}', JText::_('COM_JDOWNLOADS_TAGS_LABEL'), $html_file);
            } else {
                $html_file = str_replace('{tags}', '', $html_file);
                $html_file = str_replace('{tags_title}', '', $html_file);
            }            
            
            // Insert the Joomla Fields data when used 
            if (isset($items[$i]->jcfields) && count((array)$items[$i]->jcfields)){
                foreach ($items[$i]->jcfields as $field){
                    if ($params->get('remove_field_title_when_empty') && !$field->value){
                        $html_file = str_replace('{jdfield_title '.$field->id.'}', '', $html_file);  // Remove label placeholder
                        $html_file = str_replace('{jdfield '.$field->id.'}', '', $html_file);        // Remove value placeholder
                    } else {
                        $html_file = str_replace('{jdfield_title '.$field->id.'}', $field->label, $html_file);  // Insert label
                        $html_file = str_replace('{jdfield '.$field->id.'}', $field->value, $html_file);        // Insert value
                    }
                }
                
                // In the layout could still exist not required field placeholders
                $results = JDHelper::searchFieldPlaceholder($html_file);
                if ($results){
                    foreach ($results as $result){
                        $html_file = str_replace($result[0], '', $html_file);   // Remove label and value placeholder
                    }
                } 
            } else {
                // In the layout could still exist not required field placeholders
                $results = JDHelper::searchFieldPlaceholder($html_file);
                if ($results){
                    foreach ($results as $result){
                        $html_file = str_replace($result[0], '', $html_file);   // Remove label and value placeholder
                    }
                }
            }
            
            // files title row info only view when it is the first file
            if ($i > 0){
                // remove all html tags in top cat output
                if ($pos_end = strpos($html_file, '{files_title_end}')){
                    $pos_beg = strpos($html_file, '{files_title_begin}');
                    $html_file = substr_replace($html_file, '', $pos_beg, ($pos_end - $pos_beg) + 17);
                }
            } else {
                $html_file = str_replace('{files_title_text}', JText::_('COM_JDOWNLOADS_FE_FILELIST_TITLE_OVER_FILES_LIST'), $html_file);
                $html_file = str_replace('{files_title_end}', '', $html_file);
                $html_file = str_replace('{files_title_begin}', '', $html_file);
            } 
     
            // create file titles
            $html_file = JDHelper::buildFieldTitles($html_file, $items[$i]);
            
            // create category title
            $html_file = str_replace('{category_title}', JText::_('COM_JDOWNLOADS_CATEGORY_LABEL'), $html_file);
            $html_file = str_replace('{category_name}', $items[$i]->category_title, $html_file);
            
            // Insert language associations
            if ($params->get('show_associations') && (!empty($items[$i]->associations))){
                $association_info = '<dd class="jd_associations">'.JText::_('COM_JDOWNLOADS_ASSOCIATION_HINT');
                
                foreach ($items[$i]->associations as $association){
                    if ($params->get('flags', 1) && $association['language']->image){
                        $flag = JHtml::_('image', 'mod_languages/' . $association['language']->image . '.gif', $association['language']->title_native, array('title' => $association['language']->title_native), true);
                        $line = '&nbsp;<a href="'.JRoute::_($association['item']).'">'.$flag.'</a>&nbsp';
                    } else {
                        $class = 'label label-association label-' . $association['language']->sef;
                        $line  = '&nbsp;<a class="'.$class.'" href="'.JRoute::_($association['item']).'">'.strtoupper($association['language']->sef).'</a>&nbsp';
                    }
                    $association_info .= $line;
                }
                $association_info .= '</dd>';
                
            } else {
                $association_info = '';
            }
            
            $html_file = str_replace('{show_association}', $association_info, $html_file);            
            
            // create filename
            if ($items[$i]->url_download){
                $html_file = str_replace('{file_name}', JDHelper::getShorterFilename($this->escape(strip_tags($items[$i]->url_download))), $html_file);
            } elseif (isset($items[$i]->filename_from_other_download) && $items[$i]->filename_from_other_download != ''){
                $html_file = str_replace('{file_name}', JDHelper::getShorterFilename($this->escape(strip_tags($items[$i]->filename_from_other_download))), $html_file);
            } else {
                $html_file = str_replace('{file_name}', '', $html_file);
            }             
             
             // replace both Google adsense placeholder with script
             $html_file = JDHelper::insertGoogleAdsenseCode($html_file);
             
             // report download link
             if ($jd_user_settings->view_report_form){
                $report_link = '<a href="'.JRoute::_("index.php?option=com_jdownloads&amp;view=report&amp;id=".$items[$i]->slug."&amp;catid=".$items[$i]->catid."&amp;Itemid=".$root_itemid).'" rel="nofollow">'.JText::_('COM_JDOWNLOADS_FRONTEND_REPORT_FILE_LINK_TEXT').'</a>';                
                $html_file = str_replace('{report_link}', $report_link, $html_file);
             } else {
                $html_file = str_replace('{report_link}', '', $html_file);
             }
            
             // view sum comments 
             if ($params->get('view_sum_jcomments') && $params->get('jcomments_active')){
                 // check that comments table exist - get DB prefix string
                 $prefix = $db->getPrefix();
                 // sometimes wrong uppercase prefix result string - so we fix it
                 $prefix2 = strtolower($prefix);
                 $tablelist = $db->getTableList();
                 if (in_array($prefix.'jcomments', $tablelist ) || in_array($prefix2.'jcomments', $tablelist )){
                     $db->setQuery('SELECT COUNT(*) from #__jcomments WHERE object_group = \'com_jdownloads\' AND object_id = '.$items[$i]->id);
                     $sum_comments = $db->loadResult();
                     if ($sum_comments >= 0){
                         $comments = sprintf(JText::_('COM_JDOWNLOADS_FRONTEND_JCOMMENTS_VIEW_SUM_TEXT'), $sum_comments); 
                         $html_file = str_replace('{sum_jcomments}', $comments, $html_file);
                     } else {
                        $html_file = str_replace('{sum_jcomments}', '', $html_file);
                     }
                 } else {
                     $html_file = str_replace('{sum_jcomments}', '', $html_file);
                 }    
             } else {   
                 $html_file = str_replace('{sum_jcomments}', '', $html_file);
             }    

            if ($items[$i]->release == '' ) {
                $html_file = str_replace('{release}', '', $html_file);
            } else {
                $html_file = str_replace('{release}', $items[$i]->release.' ', $html_file);
            }

            // display the thumbnails
            $html_file = JDHelper::placeThumbs($html_file, $items[$i]->images, 'list');
            
            // we change the old lightbox tag type to the new
            $html_file = str_replace('rel="lightbox"', 'data-lightbox="lightbox'.$items[$i]->id.'"', $html_file);                                                    


            if ($params->get('auto_file_short_description') && $params->get('auto_file_short_description_value') > 0){
                 if (strlen($items[$i]->description) > $params->get('auto_file_short_description_value')){ 
                     $shorted_text=preg_replace("/[^ ]*$/", '..', substr($items[$i]->description, 0, $params->get('auto_file_short_description_value')));
                     $html_file = str_replace('{description}', $shorted_text, $html_file);
                 } else {
                     $html_file = str_replace('{description}', $items[$i]->description, $html_file);
                 }    
            } else {
                 $html_file = str_replace('{description}', $items[$i]->description, $html_file);
            }   

            // compute for HOT symbol            
            if ($params->get('loads_is_file_hot') > 0 && $items[$i]->downloads >= $params->get('loads_is_file_hot') ){
                $html_file = str_replace('{pic_is_hot}', '<span class="jdbutton '.$status_color_hot.' jstatus">'.JText::_('COM_JDOWNLOADS_HOT').'</span>', $html_file);
            } else {    
                $html_file = str_replace('{pic_is_hot}', '', $html_file);
            }

            // compute for NEW symbol
            $days_diff = JDHelper::computeDateDifference(date('Y-m-d H:i:s'), $items[$i]->created);
            if ($params->get('days_is_file_new') > 0 && $days_diff <= $params->get('days_is_file_new')){
                $html_file = str_replace('{pic_is_new}', '<span class="jdbutton '.$status_color_new.' jstatus">'.JText::_('COM_JDOWNLOADS_NEW').'</span>', $html_file);
            } else {    
                $html_file = str_replace('{pic_is_new}', '', $html_file);
            }
            
            // compute for UPDATED symbol
            // view it only when in the download is activated the 'updated' option
            if ($items[$i]->update_active) {
                $days_diff = JDHelper::computeDateDifference(date('Y-m-d H:i:s'), $items[$i]->modified);
                if ($params->get('days_is_file_updated') > 0 && $days_diff >= 0 && $days_diff <= $params->get('days_is_file_updated')){
                    $html_file = str_replace('{pic_is_updated}', '<span class="jdbutton '.$status_color_updated.' jstatus">'.JText::_('COM_JDOWNLOADS_UPDATED').'</span>', $html_file);
                } else {    
                    $html_file = str_replace('{pic_is_updated}', '', $html_file);
                }
            } else {
               $html_file = str_replace('{pic_is_updated}', '', $html_file);
            }    
                
            // media player
            if ($items[$i]->preview_filename){
                // we use the preview file when exist  
                $is_preview = true;
                $items[$i]->itemtype = JDHelper::getFileExtension($items[$i]->preview_filename);
                $is_playable    = JDHelper::isPlayable($items[$i]->preview_filename);
                $extern_media = false;
            } else {                  
                $is_preview = false;
                if ($items[$i]->extern_file){
                    $extern_media = true;
                    $items[$i]->itemtype = JDHelper::getFileExtension($items[$i]->extern_file);
                    $is_playable    = JDHelper::isPlayable($items[$i]->extern_file);
                } else {    
                    $items[$i]->itemtype = JDHelper::getFileExtension($items[$i]->url_download);
                    $is_playable    = JDHelper::isPlayable($items[$i]->url_download);
                    $extern_media = false;
                }  
            }            
            
            if ( $is_playable ){
                
               if ($params->get('html5player_use')){
                    // we will use the new HTML5 player option
                    if ($extern_media){
                        $media_path = $items[$i]->extern_file;
                    } else {        
                        if ($is_preview){
                            // we need the relative path to the "previews" folder
                            $media_path = $jdownloads_root_dir_name.'/'.$params->get('preview_files_folder_name').'/'.$items[$i]->preview_filename;
                        } else {
                            // we use the normal download file for the player
                            $media_path = $jdownloads_root_dir_name.'/'.$category_dir.'/'.$items[$i]->url_download;
                        }   
                    }    
                            
                    // create the HTML5 player
                    $player = JDHelper::getHTML5Player($items[$i], $media_path);
                    
                    // we use the player for video files only in listings, when the option allowed this
                    if ($params->get('html5player_view_video_only_in_details') && $items[$i]->itemtype != 'mp3' && $items[$i]->itemtype != 'wav' && $items[$i]->itemtype != 'oga'){
                        $html_file = str_replace('{mp3_player}', '', $html_file);
                        $html_file = str_replace('{preview_player}', '', $html_file);
                    } else {                            
                        if ($items[$i]->itemtype == 'mp4' || $items[$i]->itemtype == 'webm' || $items[$i]->itemtype == 'ogg' || $items[$i]->itemtype == 'ogv' || $items[$i]->itemtype == 'mp3' || $items[$i]->itemtype == 'wav' || $items[$i]->itemtype == 'oga'){
                            // We will replace at first the old placeholder when exist
                            if (strpos($html_file, '{mp3_player}')){
                                $html_file = str_replace('{mp3_player}', $player, $html_file);
                                $html_file = str_replace('{preview_player}', '', $html_file);
                            } else {                
                                $html_file = str_replace('{preview_player}', $player, $html_file);
                            }    
                        } else {
                            $html_file = str_replace('{mp3_player}', '', $html_file);
                            $html_file = str_replace('{preview_player}', '', $html_file);
                        }    
                    } 

               } else {
               
                    if ($params->get('flowplayer_use')){
                        // we will use the new flowplayer option
                        if ($extern_media){
                            $media_path = $items[$i]->extern_file;
                        } else {        
                            if ($is_preview){
                                // we need the relative path to the "previews" folder
                                $media_path = $jdownloads_root_dir_name.'/'.$params->get('preview_files_folder_name').'/'.$items[$i]->preview_filename;
                            } else {
                                // we use the normal download file for the player
                                $media_path = $jdownloads_root_dir_name.'/'.$category_dir.'/'.$items[$i]->url_download;
                            }   
                        }    

                        $ipadcode = '';

                        if ($items[$i]->itemtype == 'mp3'){
                            $fullscreen = 'false';
                            $autohide = 'false';
                            $playerheight = (int)$params->get('flowplayer_playerheight_audio');
                            // we must use also the ipad plugin identifier when required
                            // see http://flowplayer.blacktrash.org/test/ipad-audio.html and http://flash.flowplayer.org/plugins/javascript/ipad.html
                            if ($this->ipad_user){
                               $ipadcode = '.ipad();'; 
                            }                  
                        } else {
                            $fullscreen = 'true';
                            $autohide = 'true';
                            $playerheight = (int)$params->get('flowplayer_playerheight');
                        }
                        
                        $player = '<a href="'.$media_path.'" style="display:block;width:'.$params->get('flowplayer_playerwidth').'px;height:'.$playerheight.'px;" class="player" id="player'.$items[$i]->id.'"></a>';
                        $player .= '<script language="JavaScript">
                        // install flowplayer into container
                                    flowplayer("player'.$items[$i]->id.'", "'.JURI::base().'components/com_jdownloads/assets/flowplayer/flowplayer-3.2.16.swf",  
                                     {  
                            plugins: {
                                controls: {
                                    // insert at first the config settings
                                    // and now the basics
                                    fullscreen: '.$fullscreen.',
                                    height: '.(int)$params->get('flowplayer_playerheight_audio').',
                                    autoHide: '.$autohide.',
                                }
                                
                            },
                            clip: {
                                autoPlay: false,
                                // optional: when playback starts close the first audio playback
                                 onBeforeBegin: function() {
                                    $f("player'.$items[$i]->id.'").close();
                                }
                            }
                        })'.$ipadcode.'; </script>';
                        // the 'ipad code' above is only required for ipad/iphone users
                        
                        // we use the player for video files only in listings, when the option allowed this
                        if ($params->get('flowplayer_view_video_only_in_details') && $items[$i]->itemtype != 'mp3'){ 
                            $html_file = str_replace('{mp3_player}', '', $html_file);
                            $html_file = str_replace('{preview_player}', '', $html_file);            
                        } else {    
                            if ($items[$i]->itemtype == 'mp4' || $items[$i]->itemtype == 'flv' || $items[$i]->itemtype == 'mp3'){    
                                // We will replace at first the old placeholder when exist
                                if (strpos($html_file, '{mp3_player}')){
                                    $html_file = str_replace('{mp3_player}', $player, $html_file);
                                    $html_file = str_replace('{preview_player}', '', $html_file);
                                } else {
                                    $html_file = str_replace('{preview_player}', $player, $html_file);
                                }                                
                            } else {
                                $html_file = str_replace('{mp3_player}', '', $html_file);
                                $html_file = str_replace('{preview_player}', '', $html_file);
                            }
                        }
                    }
                }
            } 
        
            if ($params->get('mp3_view_id3_info') && $items[$i]->itemtype == 'mp3' && !$extern_media){
                // read mp3 infos
                if ($is_preview){
                    // get the path to the preview file
                    $mp3_path_abs = $params->get('files_uploaddir').DS.$params->get('preview_files_folder_name').DS.$items[$i]->preview_filename;
                } else {
                    // get the path to the downloads file
                    $mp3_path_abs = $params->get('files_uploaddir').DS.$category_dir.DS.$items[$i]->url_download;
                }
                
                $info = JDHelper::getID3v2Tags($mp3_path_abs);         
                if ($info){
                    // add it
                    $mp3_info = '<div class="jd_mp3_id3tag_wrapper" style="max-width:'.(int)$params->get('html5player_audio_width').'px; ">'.stripslashes($params->get('mp3_info_layout')).'</div>';
                    $mp3_info = str_replace('{name_title}', JText::_('COM_JDOWNLOADS_FE_VIEW_ID3_TITLE'), $mp3_info);
                    if ($is_preview){
                        $mp3_info = str_replace('{name}', $items[$i]->preview_filename, $mp3_info);
                    } else {
                        $mp3_info = str_replace('{name}', $items[$i]->url_download, $mp3_info);
                    } 
                    $mp3_info = str_replace('{album_title}', JText::_('COM_JDOWNLOADS_FE_VIEW_ID3_ALBUM'), $mp3_info);
                    $mp3_info = str_replace('{album}', $info['TALB'], $mp3_info);
                    $mp3_info = str_replace('{artist_title}', JText::_('COM_JDOWNLOADS_FE_VIEW_ID3_ARTIST'), $mp3_info);
                    $mp3_info = str_replace('{artist}', $info['TPE1'], $mp3_info);
                    $mp3_info = str_replace('{genre_title}', JText::_('COM_JDOWNLOADS_FE_VIEW_ID3_GENRE'), $mp3_info);
                    $mp3_info = str_replace('{genre}', $info['TCON'], $mp3_info);
                    $mp3_info = str_replace('{year_title}', JText::_('COM_JDOWNLOADS_FE_VIEW_ID3_YEAR'), $mp3_info);
                    $mp3_info = str_replace('{year}', $info['TYER'], $mp3_info);
                    $mp3_info = str_replace('{length_title}', JText::_('COM_JDOWNLOADS_FE_VIEW_ID3_LENGTH'), $mp3_info);
                    $mp3_info = str_replace('{length}', $info['TLEN'].' '.JText::_('COM_JDOWNLOADS_FE_VIEW_ID3_MINS'), $mp3_info);
                    $html_file = str_replace('{mp3_id3_tag}', $mp3_info, $html_file); 
                }     
            }
        
            $html_file = str_replace('{mp3_player}', '', $html_file);
            $html_file = str_replace('{preview_player}', '', $html_file);
            $html_file = str_replace('{mp3_id3_tag}', '', $html_file);             

            // replace the {preview_url}
            if ($items[$i]->preview_filename){
                // we need the relative path to the "previews" folder
                $media_path = $jdownloads_root_dir_name.'/'.$params->get('preview_files_folder_name').'/'.$items[$i]->preview_filename;
                $html_file = str_replace('{preview_url}', $media_path, $html_file);
            } else {
                $html_file = str_replace('{preview_url}', '', $html_file);
            }   
            
            // replace the placeholder {information_header}
            $html_file = str_replace('{information_header}', JText::_('COM_JDOWNLOADS_INFORMATION'), $html_file);
                         
            // build the license info data and build link
            if ($items[$i]->license == '') $items[$i]->license = 0;
            $lic_data = '';

            if ($items[$i]->license_url != '') {
                 $lic_data = $pic_license.'<a href="'.$items[$i]->license_url.'" target="_blank" rel="nofollow" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_MINI_ICON_ALT_LICENCE').'">'.$items[$i]->license_title.'</a> '.$extern_url_pic;
            } else {
                if ($items[$i]->license_title != '') {
                     if ($items[$i]->license_text != '') {
                          $lic_data = $pic_license.$items[$i]->license_title;
                          $lic_data .= JHtml::_('tooltip', $items[$i]->license_text, $items[$i]->license_title);
                     } else {
                          $lic_data = $pic_license.$items[$i]->license_title;
                     }
                } else {
                    $lic_data = '';
                }
            }
            $html_file = str_replace('{license_text}', $lic_data, $html_file);
            
            // display checkboxes only, when the user have the correct access permissions and it is activated in layout ( = 0 !! )
            if ( $layout_files->checkbox_off == 0 ) {
                 $html_file = str_replace('{checkbox_list}',$checkbox_list, $html_file);
            } else {
                 //$html_file = str_replace('{checkbox_list}','', $html_file);
            }

            $html_file = str_replace('{cat_id}', $items[$i]->catid, $html_file);
            $html_file = str_replace('{cat_title}', $items[$i]->category_title, $html_file);
            
            // file size
            if ($items[$i]->size == '' || $items[$i]->size == '0 B') {
                $html_file = str_replace('{size}', '', $html_file);
                $html_file = str_replace('{filesize_value}', '', $html_file);
            } else {
                $html_file = str_replace('{size}', $pic_size.$items[$i]->size, $html_file);
                $html_file = str_replace('{filesize_value}', $pic_size.$items[$i]->size, $html_file);
            }            
            
            // price
            if ($items[$i]->price != '') {
                $html_file = str_replace('{price_value}', $pic_price.$items[$i]->price, $html_file);
            } else {
                $html_file = str_replace('{price_value}', '', $html_file);
            }

            // file_date
            if ($items[$i]->file_date != '0000-00-00 00:00:00') {
                 if ($this->params->get('show_date') == 0){ 
                     $filedate_data = $pic_date.JHtml::_('date',$items[$i]->file_date, $date_format['long']);
                 } else {
                     $filedate_data = $pic_date.JHtml::_('date',$items[$i]->file_date, $date_format['short']);
                 }    
            } else {
                 $filedate_data = '';
            }
            $html_file = str_replace('{file_date}',$filedate_data, $html_file);
            
            // date_added
            if ($items[$i]->created != '0000-00-00 00:00:00') {
                if ($this->params->get('show_date') == 0){ 
                    // use 'normal' date-time format field
                    $date_data = $pic_date.JHtml::_('date',$items[$i]->created, $date_format['long']);
                } else {
                    // use 'short' date-time format field
                    $date_data = $pic_date.JHtml::_('date',$items[$i]->created, $date_format['short']);
                }    
            } else {
                 $date_data = '';
            }
            $html_file = str_replace('{date_added}',$date_data, $html_file);
            $html_file = str_replace('{created_date_value}',$date_data, $html_file);
            
            if ($items[$i]->creator){
                $html_file = str_replace('{created_by_value}', $items[$i]->creator, $html_file);
            } else {
                $html_file = str_replace('{created_by_value}', '', $html_file);
            }                
            if ($items[$i]->modifier){
                $html_file = str_replace('{modified_by_value}', $items[$i]->modifier, $html_file);
            } else {                              
                $html_file = str_replace('{modified_by_value}', '', $html_file);
            }
            
            // modified_date
            if ($items[$i]->modified != '0000-00-00 00:00:00') {
                if ($this->params->get('show_date') == 0){ 
                    $modified_data = $pic_date.JHtml::_('date',$items[$i]->modified, $date_format['long']);
                } else {
                    $modified_data = $pic_date.JHtml::_('date',$items[$i]->modified, $date_format['short']);
                }    
            } else {
                $modified_data = '';
            }
            $html_file = str_replace('{modified_date_value}',$modified_data, $html_file);

            $user_can_see_download_url = 0;
           
            // only view download-url when user has correct access level
            if ($items[$i]->params->get('access-download') == true){ 
                $user_can_see_download_url++;
                $blank_window = '';
                $blank_window1 = '';
                $blank_window2 = '';
                // get file extension
                $view_types = array();
                $view_types = explode(',', $params->get('file_types_view'));
                $only_file_name = basename($items[$i]->url_download);
                $fileextension = JDHelper::getFileExtension($only_file_name);
                if (in_array($fileextension, $view_types)){
                    $blank_window = 'target="_blank"';
                }    
                // check is set link to a new window?
                if ($items[$i]->extern_file && $items[$i]->extern_site   ){
                    $blank_window = 'target="_blank"';
                }

                 // direct download without summary page?
                 if ($params->get('direct_download') == '0'){
                     $url_task = 'summary';
                     $download_link = JRoute::_(JDownloadsHelperRoute::getOtherRoute($items[$i]->slug, $items[$i]->catid, $items[$i]->language, $url_task));
                 } else {
                     if ($items[$i]->license_agree || $items[$i]->password || $jd_user_settings->view_captcha) {
                         // user must agree the license - fill out a password field - or fill out the captcha human check - so we must view the summary page!
                         $url_task = 'summary';
                         $download_link = JRoute::_(JDownloadsHelperRoute::getOtherRoute($items[$i]->slug, $items[$i]->catid, $items[$i]->language, $url_task));
                     } else {     
                         $url_task = 'download.send';
                         $download_link = JRoute::_('index.php?option=com_jdownloads&amp;task=download.send&amp;id='.$items[$i]->id.'&amp;catid='.$items[$i]->catid.'&amp;m=0');
                     }    
                 }                    
                
                 // when we have not a menu item to the single download, we need a menu item from the assigned category, or at lates the root itemid
                 if ($items[$i]->menuf_itemid){
                     $file_itemid =  (int)$items[$i]->menuf_itemid;
                 } else {
                     $file_itemid = $category_menu_itemid;
                 }                      
                 
                 if ($url_task == 'download.send'){ 
                     $download_link_text = '<a '.$blank_window.' href="'.$download_link.'" title="'.JText::_('COM_JDOWNLOADS_LINKTEXT_DOWNLOAD_URL').'" class="jdbutton '.$download_color.' '.$download_size_listings.'">';
                 } else {
                     $download_link_text = '<a href="'.$download_link.'" title="'.JText::_('COM_JDOWNLOADS_LINKTEXT_DOWNLOAD_URL').'" class="jdbutton '.$download_color.' '.$download_size_listings.'">';
                 }    
                 
                // view not any download link, when we have not really a file
                if ($has_no_file || !$items[$i]->state){
                    // remove download placeholder
                    $html_file = str_replace('{url_download}', '', $html_file);
                    $html_file = str_replace('{checkbox_list}', '', $html_file);
                } else {
                     // insert here the complete download link                 
                     if ($layout_has_download){
                         if (isset($items[$i]->cart_item)){
                             if ($items[$i]->cart_item){
                                $html_file = str_replace('{url_download}', $items[$i]->cart_item.'</a>', $html_file);  
                             }
                         } else {
                            $html_file = str_replace('{url_download}', $download_link_text.JText::_('COM_JDOWNLOADS_LINKTEXT_DOWNLOAD_URL').'</a>', $html_file);  
                         }
                     } else {
                         $html_file = str_replace('{checkbox_list}', $download_link_text.JText::_('COM_JDOWNLOADS_LINKTEXT_DOWNLOAD_URL').'</a>', $html_file);  
                     }
                }    
                
                 // mirrors
                 if ($items[$i]->mirror_1 && $items[$i]->state) {
                    if ($items[$i]->extern_site_mirror_1 && $url_task == 'download.send'){
                        $blank_window1 = 'target="_blank"';
                    }
                    $mirror1_link_dum = JRoute::_('index.php?option=com_jdownloads&amp;task=download.send&amp;id='.$items[$i]->id.'&amp;catid='.$items[$i]->catid.'&amp;m=1');
                    $mirror1_link = '<a '.$blank_window1.' href="'.$mirror1_link_dum.'" alt="'.JText::_('COM_JDOWNLOADS_LINKTEXT_DOWNLOAD_URL').'" class="jdbutton '.$download_color_mirror1.' '.$download_size_mirror.'">'.JText::_('COM_JDOWNLOADS_FRONTEND_MIRROR_URL_TITLE_1').'</a>'; 
                    $html_file = str_replace('{mirror_1}', $mirror1_link, $html_file);
                 } else {
                    $html_file = str_replace('{mirror_1}', '', $html_file);
                 }
                 
                 if ($items[$i]->mirror_2 && $items[$i]->state) {
                    if ($items[$i]->extern_site_mirror_2 && $url_task == 'download.send'){
                        $blank_window2 = 'target="_blank"';
                    }
                    $mirror2_link_dum = JRoute::_('index.php?option=com_jdownloads&amp;task=download.send&amp;id='.$items[$i]->id.'&amp;catid='.$items[$i]->catid.'&amp;m=2');
                    $mirror2_link = '<a '.$blank_window2.' href="'.$mirror2_link_dum.'" alt="'.JText::_('COM_JDOWNLOADS_LINKTEXT_DOWNLOAD_URL').'" class="jdbutton '.$download_color_mirror2.' '.$download_size_mirror.'">'.JText::_('COM_JDOWNLOADS_FRONTEND_MIRROR_URL_TITLE_2').'</a>'; 
                    $html_file = str_replace('{mirror_2}', $mirror2_link, $html_file);
                 } else {
                    $html_file = str_replace('{mirror_2}', '', $html_file);
                 }            
            } else {
                 $html_file = str_replace('{url_download}', '', $html_file);
                 $html_file = str_replace('{mirror_1}', '', $html_file); 
                 $html_file = str_replace('{mirror_2}', '', $html_file); 
            }
            
            $title_link = JRoute::_(JDownloadsHelperRoute::getDownloadRoute($items[$i]->slug, $items[$i]->catid, $items[$i]->language));
			
            if ($params->get('view_detailsite')){
                $title_link_text = '<a href="'.$title_link.'">'.$this->escape($items[$i]->title).'</a>';
                $detail_link_text = '<a href="'.$title_link.'">'.JText::_('COM_JDOWNLOADS_FE_DETAILS_LINK_TEXT_TO_DETAILS').'</a>';
			}  else {
				$title_link_text = $this->escape($items[$i]->title);
				$detail_link_text = JText::_('COM_JDOWNLOADS_FE_DETAILS_LINK_TEXT_TO_DETAILS');
			}			
	
			if ($params->get('link_in_symbols')){
				if ($params->get('use_download_title_as_download_link')){
                    // we need the download link
                    $pic_link = '<a href="'.$download_link.'">';
                    $pic_end = '</a>';     
                } else {
                    // We need the link to details
                    $pic_link = '<a href="'.$title_link.'">';
                    $pic_end = '</a>';    
                }
			} else {
				$pic_link = '';
				$pic_end = '';
			}

            if ($params->get('view_detailsite')){
                // Show symbol - also as url
                if ($items[$i]->file_pic != '' ) {
                    $filepic = $pic_link.'<img src="'.$file_pic_folder.$items[$i]->file_pic.'" style="text-align:top;border:0px;" width="'.$params->get('file_pic_size').'" height="'.$params->get('file_pic_size_height').'" alt="'.substr($items[$i]->file_pic,0,-4).$i.'"/>'.$pic_end;
                } else {
                    $filepic = '';
                }
                $html_file = str_replace('{file_pic}', $filepic, $html_file);
                $html_file = str_replace('{file_title}', $title_link_text.' '.$editIcon, $html_file);
                
            } elseif ($params->get('use_download_title_as_download_link')){
  
                if ($user_can_see_download_url && !$has_no_file){
                    // build title link as download link
                    if ($url_task == 'download.send'){ 
                        $download_link_text = '<a href="'.$download_link.$blank_window.'" title="'.JText::_('COM_JDOWNLOADS_LINKTEXT_DOWNLOAD_URL').'" class="jd_download_url">'.$items[$i]->title.'</a>';
                    } else {
                        $download_link_text = '<a href="'.$download_link.'" title="'.JText::_('COM_JDOWNLOADS_LINKTEXT_DOWNLOAD_URL').'">'.$items[$i]->title.'</a>';                  
                    }
					                
                    // View file icon also with link
                    if ($items[$i]->file_pic != '' ) {
                        $filepic = $pic_link.'<img src="'.$file_pic_folder.$items[$i]->file_pic.'" style="text-align:top;border:0px;" width="'.$params->get('file_pic_size').'" height="'.$params->get('file_pic_size_height').'" alt="'.substr($items[$i]->file_pic,0,4).$i.'" />'.$pic_end;
                    } else {
                        $filepic = '';
                    }

                    $html_file = str_replace('{file_pic}', $filepic, $html_file);
                    $html_file = str_replace('{file_title}', $download_link_text.' '.$editIcon, $html_file);
                } else {
                    // user may not use download link
                    $html_file = str_replace('{file_title}', $items[$i]->title, $html_file);
                    if ($items[$i]->file_pic != '' ) {
                        $filepic = '<img src="'.$file_pic_folder.$items[$i]->file_pic.'" style="text-align:top;border:0px;" width="'.$params->get('file_pic_size').'" height="'.$params->get('file_pic_size_height').'" alt="'.substr($items[$i]->file_pic, 0, -4).$i.'" />';
                    } else {
                        $filepic = '';
                    }
                    $html_file = str_replace('{file_pic}', $filepic, $html_file);
                }    
            } else {
                if ($items[$i]->file_pic != '' ) {
                    $filepic = $pic_link.'<img src="'.$file_pic_folder.$items[$i]->file_pic.'" style="text-align:top;border:0px;" width="'.$params->get('file_pic_size').'" height="'.$params->get('file_pic_size_height').'" alt="'.substr($items[$i]->file_pic,0,-4).$i.'" />'.$pic_end;
                } else {
                    $filepic = '';
                }
                $html_file = str_replace('{file_pic}', $filepic, $html_file);
                $html_file = str_replace('{file_title}', $items[$i]->title.' '.$editIcon, $html_file);
            }             
            
            // The link to detail view is always displayed - when not required must be removed the placeholder from the layout
            $html_file = str_replace('{link_to_details}', $detail_link_text, $html_file);
            
            // build website url
            if (!$items[$i]->url_home == '') {
                 if (strpos($items[$i]->url_home, 'http://') !== false or strpos($items[$i]->url_home, 'https://') !== false) {    
                     $html_file = str_replace('{url_home}',$pic_website.'<a href="'.$items[$i]->url_home.'" target="_blank" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_HOMEPAGE').'">'.JText::_('COM_JDOWNLOADS_FRONTEND_HOMEPAGE').'</a> '.$extern_url_pic, $html_file);
                     $html_file = str_replace('{author_url_text} ',$pic_website.'<a href="'.$items[$i]->url_home.'" target="_blank" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_HOMEPAGE').'">'.JText::_('COM_JDOWNLOADS_FRONTEND_HOMEPAGE').'</a> '.$extern_url_pic, $html_file);
                 } else {
                     $html_file = str_replace('{url_home}',$pic_website.'<a href="http://'.$items[$i]->url_home.'" target="_blank" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_HOMEPAGE').'">'.JText::_('COM_JDOWNLOADS_FRONTEND_HOMEPAGE').'</a> '.$extern_url_pic, $html_file);
                     $html_file = str_replace('{author_url_text}',$pic_website.'<a href="http://'.$items[$i]->url_home.'" target="_blank" title="'.JText::_('COM_JDOWNLOADS_FRONTEND_HOMEPAGE').'">'.JText::_('COM_JDOWNLOADS_FRONTEND_HOMEPAGE').'</a> '.$extern_url_pic, $html_file);
                 }    
            } else {
                $html_file = str_replace('{url_home}', '', $html_file);
                $html_file = str_replace('{author_url_text}', '', $html_file);
            }

            // encode is link a mail
            if (strpos($items[$i]->url_author, '@') && $params->get('mail_cloaking')){
                if (!$items[$i]->author) { 
                    $mail_encode = JHtml::_('email.cloak', $items[$i]->url_author);
                } else {
                    $mail_encode = JHtml::_('email.cloak',$items[$i]->url_author, true, $items[$i]->author, false);
                }        
            } else {
                $mail_encode = '';
            }
                    
            // build author link
            if ($items[$i]->author <> ''){
                if ($items[$i]->url_author <> '') {
                    if ($mail_encode) {
                        $link_author = $pic_author.$mail_encode;
                    } else {
                        if (strpos($items[$i]->url_author, 'http://') !== false or strpos($items[$i]->url_author, 'https://') !== false) {    
                            $link_author = $pic_author.'<a href="'.$items[$i]->url_author.'" target="_blank">'.$items[$i]->author.'</a> '.$extern_url_pic;
                        } else {
                            $link_author = $pic_author.'<a href="http://'.$items[$i]->url_author.'" target="_blank">'.$items[$i]->author.'</a> '.$extern_url_pic;
                        }        
                    }
                    $html_file = str_replace('{author}',$link_author, $html_file);
                    $html_file = str_replace('{author_text}',$link_author, $html_file);
                    $html_file = str_replace('{url_author}', '', $html_file);
                } else {
                    $link_author = $pic_author.$items[$i]->author;
                    $html_file = str_replace('{author}',$link_author, $html_file);
                    $html_file = str_replace('{author_text}',$link_author, $html_file);
                    $html_file = str_replace('{url_author}', '', $html_file);
                }
            } else {
                    $html_file = str_replace('{url_author}', $pic_author.$items[$i]->url_author, $html_file);
                    $html_file = str_replace('{author}','', $html_file);
                    $html_file = str_replace('{author_text}','', $html_file); 
            }

            // set system value
            $file_sys_values = explode(',' , JDHelper::getOnlyLanguageSubstring($params->get('system_list')));
            if ($items[$i]->system == 0 ) {
                $html_file = str_replace('{system}', '', $html_file);
                 $html_file = str_replace('{system_text}', '', $html_file); 
            } else {
                $html_file = str_replace('{system}', $pic_system.$file_sys_values[$items[$i]->system], $html_file);
                $html_file = str_replace('{system_text}', $pic_system.$file_sys_values[$items[$i]->system], $html_file);
            }

            // set language value
            $file_lang_values = explode(',' , JDHelper::getOnlyLanguageSubstring($params->get('language_list')));
            if ($items[$i]->file_language == 0 ) {
                $html_file = str_replace('{language}', '', $html_file);
                $html_file = str_replace('{language_text}', '', $html_file);
            } else {
                $html_file = str_replace('{language}', $pic_language.$file_lang_values[$items[$i]->file_language], $html_file);
                $html_file = str_replace('{language_text}', $pic_language.$file_lang_values[$items[$i]->file_language], $html_file);
            }

            // insert rating system
            if ($params->get('view_ratings')){
                $rating_system = JDHelper::getRatings($items[$i]->id, $items[$i]->rating_count, $items[$i]->rating_sum);
                $html_file = str_replace('{rating}', $rating_system, $html_file);
                $html_file = str_replace('{rating_title}', JText::_('COM_JDOWNLOADS_RATING_LABEL'), $html_file);
            } else {
                $html_file = str_replace('{rating}', '', $html_file);
                $html_file = str_replace('{rating_title}', '', $html_file);
            }
            
            // Remove the old custom fields placeholder
            for ($x=1; $x<15; $x++){
                $html_file = str_replace("{custom_title_$x}", '', $html_file);
                $html_file = str_replace("{custom_value_$x}", '', $html_file);
            } 
            
            $html_file = str_replace('{downloads}',$pic_downloads.JDHelper::strToNumber((int)$items[$i]->downloads), $html_file);
            $html_file = str_replace('{hits_value}',$pic_downloads.JDHelper::strToNumber((int)$items[$i]->downloads), $html_file);            
            $html_file = str_replace('{ordering}',$items[$i]->ordering, $html_file);
            $html_file = str_replace('{published}',$items[$i]->published, $html_file);
            
            // support for content plugins 
            if ($params->get('activate_general_plugin_support')) {  
                $html_file = JHtml::_('content.prepare', $html_file, '', 'com_jdownloads.downloads');
            }
		    
            //	remove any remaining {checkbox_list}
			$html_file = str_replace('{checkbox_list}','', $html_file);
            
            // add the content plugin event 'after display content'
            if (strpos($html_file, '{after_display_content}') > 0){
                $html_file = str_replace('{after_display_content}', $items[$i]->event->afterDisplayContent, $html_file);
                $event = '';
            } else {
                $event = $items[$i]->event->afterDisplayContent;    
            }
            
            $html_files .= $html_file;
            
            // finaly add the 'after display content' event output when required
            $html_files .= $event;
        }

        // display only downloads area when it exist data here
        if ($total_downloads > 0){
            $body = $html_files;
        } else {
            $no_files_msg = '';
            if ($params->get('view_no_file_message_in_empty_category')){
                $no_files_msg = '<br />'.JText::_('COM_JDOWNLOADS_FRONTEND_NOFILES').'<br /><br />';            
            } 
            $body = $no_files_msg;
        }    
        
        // display top checkbox only when the user can download any files here - right access permissions
        if (isset($user_can_see_download_url)){ 
            $checkbox_top = '<tr><form name="down'.$formid.'" action="'.JRoute::_('index.php?option=com_jdownloads&amp;view=summary&amp;Itemid='.$file_itemid).'"
                    onsubmit="return pruefen('.$formid.',\''.JText::_('COM_JDOWNLOADS_JAVASCRIPT_TEXT_1').' '.JText::_('COM_JDOWNLOADS_JAVASCRIPT_TEXT_2').'\');" method="post">
                    <td width="89%" style="text-align:right;">'.JDHelper::getOnlyLanguageSubstring($params->get('checkbox_top_text')).'</td>
                    <td width="11%" style="text-align:center;"><input type="checkbox" name="toggle"
                    value="" onclick="checkAlle('.$i.','.$formid.');" /></td></tr>';
            
            // view top checkbox only when activated in layout
            if ($layout_files->checkbox_off == 0 && !empty($items) && !$checkbox_top_always_added) {
               $body = str_replace('{checkbox_top}', $checkbox_top, $body);
               $checkbox_top_always_added = true;
            } else {
               $body = str_replace('{checkbox_top}', '', $body);
            }   
        } else {
            // view message for missing access permissions
            if ($user->guest){
                $regg = str_replace('<br />', '', JText::_('COM_JDOWNLOADS_FRONTEND_FILE_ACCESS_REGGED'));
            } else {
                $regg = str_replace('<br />', '', JText::_('COM_JDOWNLOADS_FRONTEND_FILE_ACCESS_REGGED_LIST'));
            }    

            if ($total_downloads > 0){
                $body = str_replace('{checkbox_top}', '<span class="label label-info" style="margin-top: 5px;"><strong>'.$regg.'</strong></span>', $body);                    
            } else {
                $body = str_replace('{checkbox_top}', '', $body);                    
            }    
        } 
        
        $form_hidden = '<input type="hidden" name="boxchecked" value=""/> ';
        $body = str_replace('{form_hidden}', $form_hidden, $body);
        // $body .= '<input type="hidden" name="catid" value="'.$catid.'"/>';
        $body .= JHtml::_( 'form.token' ).'</form>';

        // view submit button only when checkboxes are activated
        $button = '<input class="button" type="submit" name="weiter" value="'.JText::_('COM_JDOWNLOADS_FORM_BUTTON_TEXT').'"/>';
        
        // view only submit button when user has correct access level and checkboxes are used in layout
        if ($layout_files->checkbox_off == 0 && !empty($items)) {
            $body = str_replace('{form_button}', $button, $body);
        } else {
            $body = str_replace('{form_button}', '', $body);
        }        
        
        $html .= $body; 
        $html .= '</div>';  
    }    
  
    // ==========================================
    // FOOTER SECTION  
    // ==========================================

    // Display pagination for the Downloads when the placeholder is placed in the footer area from the Downloads layout 
    $footer = JDHelper::insertPagination($params->get('option_navigate_bottom'), $this->params->get('show_pagination'), $this->params->get('show_pagination_results'), $this->pagination, $footer);

    // components footer text
    if ($params->get('downloads_footer_text') != '') {
        $footer_text = stripslashes(JDHelper::getOnlyLanguageSubstring($params->get('downloads_footer_text')));
        
        // replace both Google adsense placeholder with script
        $footer_text = JDHelper::insertGoogleAdsenseCode($footer_text);
        $html .= $footer_text;
    }
    
    // back button
    if ($params->get('view_back_button')){
        $footer = str_replace('{back_link}', '<a href="javascript:history.go(-1)">'.JText::_('COM_JDOWNLOADS_FRONTEND_BACK_BUTTON').'</a>', $footer); 
    } else {
        $footer = str_replace('{back_link}', '', $footer);
    }
    
    // Cart option active?
    if ($this->cartplugin && $params->get('use_shopping_cart_plugin')){
        $cart = '<div class="clr"></div>
        <a id="jdownloadscart"></a>

        <h2>'.JText::_('COM_JDOWNLOADS_YOUR_CART').':
            (<span class="simpleCart_quantity"></span> '.JText::_('COM_JDOWNLOADS_ITEMS').')</h2>

        <div class="cart_yourcart">
            <div class="cart_yourcart_items">
                <div class="simpleCart_items">
                
                </div>
                <div class="cart_totals">
                    <div class="cart_summary"><span class="cart_checkout_label">'.JText::_('COM_JDOWNLOADS_SUB_TOTAL').':</span> <span class="simpleCart_total"></span></div>
                    
                    <div class="cart_summary cart_summary_total"><span class="cart_checkout_label">'.JText::_('COM_JDOWNLOADS_TOTAL').':</span> <span class="simpleCart_grandTotal"></span></div>
                </div>

                <div class="cart_buttons">
                    <a href="javascript:;" class="simpleCart_empty btn button"><i class="icon-trash"></i> '.JText::_('COM_JDOWNLOADS_EMPTY_CART').'<span></span></a>
                    <a href="javascript:;" class="simpleCart_checkout btn button btn-danger"><i class="icon-cart"></i> '.JText::_('COM_JDOWNLOADS_CHECKOUT').'<span></span></a>
                </div>
            </div>
        </div>';
        
        $footer = str_replace('{cart}', $cart, $footer);
    } else {
        $footer = str_replace('{cart}', '', $footer);
    }
    
    $footer .= JDHelper::checkCom();
   
    $html .= $footer; 
    
    if ($this->cartplugin && $params->get('use_shopping_cart_plugin')){
    
    	$html .= '</div>';
    }

    // remove empty html tags
    if ($params->get('remove_empty_tags')){
        $html = JDHelper::removeEmptyTags($html);
    }
    
    // ==========================================
    // VIEW THE BUILDED OUTPUT
    // ==========================================

    if (!$params->get('offline')){
            echo $html;
    } else {
        // admins can view it always
        if ($is_admin) {
            echo $html;     
        } else {
            // build the offline message
            $html = '';
            // offline message
            if ($params->get('offline_text') != '') {
                $html .= JDHelper::getOnlyLanguageSubstring($params->get('offline_text'));
            }
            echo $html;    
        }
    }     
    
?>