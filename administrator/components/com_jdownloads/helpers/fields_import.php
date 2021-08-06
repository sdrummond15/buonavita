<?php
/**
 * @package jDownloads
 * @version 3.9
 * @copyright (C) 2007 - 2020 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 *
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

defined('_JEXEC') or die;

setlocale(LC_ALL, 'C.UTF-8', 'C');

JModelLegacy::addIncludePath(JPATH_SITE . '/administrator/components/com_fields/models', 'FieldsModel');

class JDFieldImportHelper
{    
    function __construct()
    {
    }

    /**
     * Import the old custom fields from jDownloads to the new one from com_fields
     *
     * @param   array   $field_titles           An array with all in jD created custom fields his default values and by lists this options
     * @param   array   $custom_fields_items    All the stored data in the jD fiels table - together with the ID and the Downloads title 
     * @param   array   $created_custom_fields  Only a comma seperated string with all the used data fields in jD (example: "custom_field_1, custom_field_2, custom_field_6, custom_field_11, custom_field_13, custom_field_14")
      
     * @return  boolean result of import
     *
     * @since   3.9
     */
    public static function fieldsImport($custom_fields_items, $old_created_custom_fields, $field_titles, $lang_keys)
    {                                  

        $result = array("created" =>"0", "saved_data" =>'0');
        
        $backend_lang = JComponentHelper::getParams('com_languages')->get('administrator');
        $language = JFactory::getLanguage();
        $language->load('com_jdownloads', JPATH_ADMINISTRATOR, $backend_lang, true);
        
        $lang = JFactory::getLanguage();
        $tag  = $lang->getTag();

        
        if (count($lang_keys) > 1){
            // Multiple languages are installed so we could also have custom fields for different languages.
        
            $new_fields = array();
            $x = 0;
            $empty_value_added = false;
                    
            foreach ($field_titles as $field){
                foreach ($lang_keys as $lkey){
                    if (isset($field->{"$lkey"})){           
                        if ($lkey == $tag){
                            // Copy the item values to a new '*' array when this is the active default language
                            $title_all = $field->{"$lkey"};
                            
                            $new_fields[$x]['fieldname']    = $field->setting_name; // The field type 
                            $new_fields[$x]['title']        = $title_all;           // Make the new 'ALL' title unique
                            $new_fields[$x]['name']         = $title_all.'_all';    
                            $new_fields[$x]['label']        = $title_all;    
                            $new_fields[$x]['language']     = '*';                  // Assign the language
                            
                            if (isset($field->values->{"$lkey"})){     // We have find options for a special language
                                // Add an option with empty values but only when we have a list field type 
                                if (strpos($field->setting_name, '.1.') || strpos($field->setting_name, '.2.') || 
                                    strpos($field->setting_name, '.3.') || strpos($field->setting_name, '.4.') || strpos($field->setting_name, '.5.')){
                                        array_unshift($field->values->{"$lkey"}, '');
                                        $empty_value_added = true;
                                }

                                $new_fields[$x]['options'] = $field->values->{"$lkey"};
                            }
                            $x++;
                        }
                        
                        $new_fields[$x]['fieldname']    = $field->setting_name; // The field type 
                        $new_fields[$x]['title']        = $field->{"$lkey"};    // We have find the title
                        $new_fields[$x]['language']     = $lkey;                // Assign the language
                    }
                    
                    if (isset($field->values->{"$lkey"})){     // We have find options for a special language
                        // Add an option with empty values but only when we have a list field type 
                        if (strpos($field->setting_name, '.1.') || strpos($field->setting_name, '.2.') || 
                            strpos($field->setting_name, '.3.') || strpos($field->setting_name, '.4.') || strpos($field->setting_name, '.5.')){
                                if (!$empty_value_added) array_unshift($field->values->{"$lkey"}, '');
                        }

                        $new_fields[$x]['options'] = $field->values->{"$lkey"}; 
                    }
                    $empty_value_added = false;
                    
                    if (isset($new_fields[$x])) $x ++;
                    
                }                    
            }
        }
        
        // First step: Create the fields 
        if(count($new_fields)){
            // With multiple languages
            $result['created'] = (int)self::createNewCustomFieldsMulti($new_fields, $lang_keys);
        } else {
            $result['created'] = (int)self::createNewCustomFields($field_titles);
        }
        
        if ($result['created'] > 0){
            
            // Second step: Save the data in the fields_value table
            if(count($new_fields)){
                $result['saved_data'] = (int)self::saveDataMulti($custom_fields_items, $new_fields);
            } else {
                $result['saved_data'] = (int)self::saveData($custom_fields_items, $old_created_custom_fields);
            }
            
            return $result;
        } else {
            return $result;
        }    
    }

    /**
     * Create the jD fields in com_fields - normal version without multi language keys
     *
     * @param   array   $field_titles  An array with all in jD created custom fields his default values and by lists this options
     * 
     * @return  integer $result        Amount of created fields
     *
     * @since   3.9
     */
    public static function createNewCustomFields($field_titles)
    {

        $db = JFactory::getDBO();
        $document = JFactory::getDocument();

        $user = JFactory::getUser();
        $user_id = $user->get('id'); // Get the current user ID

        $result = 0;
        
        JLoader::import('joomla.application.component.modeladmin');
        JLoader::import('joomla.application.component.model');
        JLoader::import('joomla.application.component.view');
        
        // Import com_fields models
        JLoader::import( 'field', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_fields' . DS . 'models' );
        JLoader::import( 'group', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_fields' . DS . 'models' );
                
        $model_field = JModelLegacy::getInstance( 'Field', 'FieldsModel', array('ignore_request' => true) );
        $model_group = JModelLegacy::getInstance( 'Group', 'FieldsModel', array('ignore_request' => true) );        
        
        $new_created_fields = array();
        
        foreach ($field_titles as $field){
            
            if ($field->setting_value == '') continue; // We can only import a field which has a title so in this case we go to the next field
            
            $title          = '';
            
            $default_value  = '';   // Must be a coresponding value - In our case only possible for a short text field
            $default_text   = '';
            
            $type           = '';   // Can in our case only be: list (No 1-5), text (No 6-10), date = calendar (No 11 + 12), textarea (No 13 + 14)
            $params         = '';   // Default by every list field type:
                                    // {"class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"0","layout":"","display_readonly":"2"}
                                    // For other types:
                                    // {"hint":"","class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"0","layout":"","display_readonly":"2"}
                                    // We use display = 0 as we want not any automaticly display positions. We use instead the jD placeholders in layouts.
            $fieldparams    = '';   // Can be different for every field type - example for a list field with 3 options and corresponding values:
                                    // {"multiple":"","options":{"options0":{"name":"Kunde","value":"1"},"options1":{"name":"Auftraggeber","value":"2"},"options2":{"name":"Dritte Option","value":"3"}}}

            $list_values    = '{"multiple":"","options":'; // '""}'; // So we can add later the required options when use.
            
            $title = $db->escape(strip_tags($field->setting_value));
            
            switch ($field->setting_name){
            
                case  'custom.field.1.title':
                case  'custom.field.2.title':
                case  'custom.field.3.title':
                case  'custom.field.4.title':
                case  'custom.field.5.title':
                      $type = 'list';
                
                      // We have a list field - so in the $field->value array should be the option values
                      if (isset($field->values)){
                          
                          // Add an option with empty values
                          array_unshift($field->values, '');
                          
                          $count = count($field->values);
                          for ($i=0; $i < $count; $i++){
                              if ($i == 0){
                                  $list_values .= '{"options'."$i".'":{"name":"'.$field->values[$i].'","value":""},';
                              } else {
                                  $list_values .= '"options'."$i".'":{"name":"'.$field->values[$i].'","value":"'.$i.'"},';
                              }
                          }
                          $list_values = substr($list_values, 0, -1);
                          $list_values .= '}}';
                          //$fieldparams = $list_values;
                      }
                      $params = '{"class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"2","layout":"","display_readonly":"2"}';
                      if ($list_values != '{"multiple":"","options":'){
                          $fieldparams = $list_values;
                      } else {
                          $fieldparams = '{"multiple":"","options":""}';
                      }
                      break;
                    
                case  'custom.field.6.title':
                case  'custom.field.7.title':
                case  'custom.field.8.title':
                case  'custom.field.9.title':
                case  'custom.field.10.title':
                      $type = 'text';
                
                      // We have a simple text field - so in the first $field->value array could only be a default value
                      if (isset($field->values[0])){
                          $default_value = $field->values[0];
                      }
                      $params = '{"hint":"","class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"0","layout":"","display_readonly":"2"}';
                      $fieldparams = '{"filter":"","maxlength":256}';
                      break;    

                case  'custom.field.11.title':
                case  'custom.field.12.title':
                      $type = 'calendar';
                      $params = '{"hint":"","class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"0","layout":"","display_readonly":"2"}';
                      $fieldparams = '{"showtime":0}';
                      break;                        
                      
                case  'custom.field.13.title':
                case  'custom.field.14.title':
                      $type = 'textarea';
                      $params = '{"hint":"","class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"0","layout":"","display_readonly":"2"}';
                      $fieldparams = '{"rows":"","cols":"","maxlength":"","filter":""}';
                      break;                        
            }
            
            $name   = JApplication::stringURLSafe($title);   // Should be the same syntax as an alias - use the lowercased title with hyphens for it
            $label  = $title;
            
            
            // Build table array with data
            $data = array (
                'id' => 0,
                'asset_id' => 0,
                'context' => 'com_jdownloads.download',
                'group_id' => 0,
                'title' => $title,
                'name' => $name,
                'label' => $label,
                'default_value' => '',
                'type' => $type,
                'note' => 'Imported from jD 3.2.x',
                'description' => '',
                'state' => 1,
                'required' => 0,
                'checked_out' => 0,
                'checked_out_time' => '0000-00-00 00:00:00',
                'ordering' => 0,
                'params' => $params,
                'fieldparams' => $fieldparams,
                'language' => '*',
                'created_time' => '0000-00-00 00:00:00',
                'created_user_id' => $user_id,
                'modified_time' => '0000-00-00 00:00:00',
                'modified_by' => 0,
                'access' => '1',
            );
            
            $res = $model_field->save($data);
            
            if ($res){
                $result++;
            }
        }
        return $result;
        
    }

    /**
     * Create the jD fields in com_fields - when in jD was use multi language keys in the custom fields definitions!
     *
     * @param   array   $field_titles  An array with all in jD created custom fields his default values and by lists this options
     * 
     * @return  integer $result        Amount of created fields
     *
     * @since   3.9
     */
    public static function createNewCustomFieldsMulti($new_fields, $lang_keys)
    {

        $db = JFactory::getDBO();
        $document = JFactory::getDocument();

        $lang = JFactory::getLanguage();
        $tag  = $lang->getTag();
        
        $user = JFactory::getUser();
        $user_id = $user->get('id'); // Get the current user ID

        $result = 0;
        
        JLoader::import('joomla.application.component.modeladmin');
        JLoader::import('joomla.application.component.model');
        JLoader::import('joomla.application.component.view');
        
        // Import com_fields models
        JLoader::import( 'field', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_fields' . DS . 'models' );
        JLoader::import( 'group', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_fields' . DS . 'models' );
                
        $model_field = JModelLegacy::getInstance( 'Field', 'FieldsModel', array('ignore_request' => true) );
        $model_group = JModelLegacy::getInstance( 'Group', 'FieldsModel', array('ignore_request' => true) );
        
        
        // Create for every language a fields group
        array_unshift($lang_keys, '*');
        $numbers = 0;
        
        foreach ($lang_keys as $lang){
            // Build table array with data for group
            $data = array (
                'id' => 0,
                'asset_id' => 0,
                'context' => 'com_jdownloads.download',
                'title' => $lang,
                'note' => 'Imported',
                'description' => '',
                'state' => 1,
                'checked_out' => 0,
                'checked_out_time' => '0000-00-00 00:00:00',
                'ordering' => 0,
                'params' => '{"display_readonly":"1"}',
                'language' => $lang,
                'created' => '0000-00-00 00:00:00',
                'created_by' => $user_id,
                'modified' => '0000-00-00 00:00:00',
                'modified_by' => 0,
                'access' => '1',
            );
            // Create group for every language
            $resgr = $model_group->save($data);
            if ($resgr){
                $numbers++;
            }
        }
        
        $query = $db->getQuery(true);
        $query->select('b.id, b.language');
        $query->from('#__fields_groups AS b');
        $query->where($db->quoteName('b.context') . ' = ' . $db->quote('com_jdownloads.download'));
        $query->order($db->quoteName('b.id'));
        $db->setQuery($query);
        $new_groups = $db->loadAssocList();
    
        $group_all = $new_groups[0]['id'];
        
        $new_created_fields = array();
        
        $runs = 0;
        
        foreach ($new_fields as $field){
            
            $field = (object)$field;
            
            if ($field->title == '') continue; // We can only import a field which has a title so in this case we go to the next field
            
            $title          = '';
            
            $default_value  = '';   // Must be a corresponding value - In our case only possible for a short text field
            $default_text   = '';
            
            $type           = '';   // Can in our case only be: list (No 1-5), text (No 6-10), date = calendar (No 11 + 12), textarea (No 13 + 14)
            $params         = '';   // Default by every list field type:
                                    // {"class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"0","layout":"","display_readonly":"2"}
                                    // For other types:
                                    // {"hint":"","class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"0","layout":"","display_readonly":"2"}
                                    // We use display = 0 as we want not any automaticly display positions. We use instead the jD placeholders in layouts.
            $fieldparams    = '';   // Can be different for every field type - example for a list field with 3 options and corresponding values:
                                    // {"multiple":"","options":{"options0":{"name":"Kunde","value":"1"},"options1":{"name":"Auftraggeber","value":"2"},"options2":{"name":"Dritte Option","value":"3"}}}

            $list_values    = '{"multiple":"","options":'; // '""}'; // So we can add later the required options when use.
            
            $title = $db->escape(strip_tags($field->title));
            
            $language = $field->language;
            
            $group = 0;
            
            foreach ($new_groups as $new_group){
                if (in_array($field->language, $new_group)){
                    $group = $new_group['id'];
                    break;
                } 
            }

            switch ($field->fieldname){
            
                case  'custom.field.1.title':
                case  'custom.field.2.title':
                case  'custom.field.3.title':
                case  'custom.field.4.title':
                case  'custom.field.5.title':
                      $type = 'list';
                
                      for ($i=0; $i < count($field->options); $i++){
                          if ($i == 0){
                              $list_values .= '{"options'."$i".'":{"name":"'.$field->options[$i].'","value":""},';
                          } else {
                              $list_values .= '"options'."$i".'":{"name":"'.$field->options[$i].'","value":"'.$i.'"},';
                          }
                      }
                      
                      $list_values = substr($list_values, 0, -1);
                      $list_values .= '}}';
                      
                      $params = '{"class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"0","layout":"","display_readonly":"2"}';
                      if ($list_values != '{"multiple":"","options":'){
                          $fieldparams = $list_values;
                      } else {
                          $fieldparams = '{"multiple":"","options":""}';
                      }
                      break;
                    
                case  'custom.field.6.title':
                case  'custom.field.7.title':
                case  'custom.field.8.title':
                case  'custom.field.9.title':
                case  'custom.field.10.title':
                      $type = 'text';
                
                      // We have a simple text field - so in the first $field->value array could only be a default value
                      if (isset($field->options[0])){
                          $default_value = $field->options[0];
                      }
                      $params = '{"hint":"","class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"0","layout":"","display_readonly":"2"}';
                      $fieldparams = '{"filter":"","maxlength":256}';
                      break;    

                case  'custom.field.11.title':
                case  'custom.field.12.title':
                      $type = 'calendar';
                      $params = '{"hint":"","class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"0","layout":"","display_readonly":"2"}';
                      $fieldparams = '{"showtime":0}';
                      break;                        
                      
                case  'custom.field.13.title':
                case  'custom.field.14.title':
                      $type = 'textarea';
                      $params = '{"hint":"","class":"","label_class":"","show_on":"","render_class":"","showlabel":"1","label_render_class":"","display":"0","layout":"","display_readonly":"2"}';
                      $fieldparams = '{"rows":"","cols":"","maxlength":"","filter":""}';
                      break;                        
            }
            
            if (isset($field->label)){
                $name = $field->name;
                $label = $field->label;
            } else {
                $name   = JApplication::stringURLSafe($title);   // Should be the same syntax as an alias - use the lowercased title with hyphens for it
                $label  = $title;
            }
            
            
            // Build table array with data
            $data = array (
                'id' => 0,
                'asset_id' => 0,
                'context' => 'com_jdownloads.download',
                'group_id' => $group,
                'title' => $title,
                'name' => $name,
                'label' => $label,
                'default_value' => '',
                'type' => $type,
                'note' => 'Imported',
                'description' => '',
                'state' => 1,
                'required' => 0,
                'checked_out' => 0,
                'checked_out_time' => '0000-00-00 00:00:00',
                'ordering' => 0,
                'params' => $params,
                'fieldparams' => $fieldparams,
                'language' => $language,
                'created_time' => '0000-00-00 00:00:00',
                'created_user_id' => $user_id,
                'modified_time' => '0000-00-00 00:00:00',
                'modified_by' => 0,
                'access' => '1',
            );
            
            $res = $model_field->save($data);
            
            if ($res){
                $result++;
            }
        }
        return $result;
    }
    
    /**
     * Save the jD fields data
     *
     * @param   array   $items      An array with all in jD created custom fields his default values and by lists this options
     * 
     * @return  integer             Amount of saved datasets
     * @return  array               The new created fields with ID and title
     * 
     * @since   3.9
     */
    public static function saveData($items, $old_created_custom_fields)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        $number  = 0;
        $res     = 0;
        
        // We need the new created fields from jD
        // Select the required fields from the table.
        $query->select('a.id, a.title, a.context, a.label');
        $query->from('#__fields AS a');
        $query->where($db->quoteName('a.context') . ' = ' . $db->quote('com_jdownloads.download'));
        $query->order($db->quoteName('a.id'));
        $db->setQuery($query);
        $new_fields = $db->loadObjectList();
        
        if (!$new_fields){
            return $res;
        }
        
        // Fill out the empty arrays
        $old_created_custom_fields = str_replace(' ', '', $old_created_custom_fields);
        $old_used_fields = explode(',', $old_created_custom_fields);
        
        for ($i=0; $i<count($new_fields); $i++){
             $new_fields[$i]->old_fieldname = $old_used_fields[$i];    
        }
        
        $columns = array('field_id', 'item_id', 'value');
        
        // Now we add the new Joomla field ID to the jD data array               
        foreach ($items as $item){
            
            $query = $db->getQuery(true);
            
            if (isset($item->custom_field_1) && $item->custom_field_1 != '' ){
                $new_field_id = self::getNewID('custom_field_1', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_1);             
                if ($res) $number++;
            }
            if (isset($item->custom_field_2) && $item->custom_field_2 != '' ){
                $new_field_id = self::getNewID('custom_field_2', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_2);
                if ($res) $number++;
            }
            if (isset($item->custom_field_3) && $item->custom_field_3 != '' ){
                $new_field_id = self::getNewID('custom_field_3', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_3);
                if ($res) $number++;
            }
            if (isset($item->custom_field_4) && $item->custom_field_4 != '' ){
                $new_field_id = self::getNewID('custom_field_4', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_4);
                if ($res) $number++;
            }
            if (isset($item->custom_field_5) && $item->custom_field_5 != '' ){
                $new_field_id = self::getNewID('custom_field_5', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_5);
                if ($res) $number++;
            }
            if (isset($item->custom_field_6) && $item->custom_field_6 != '' ){
                $new_field_id = self::getNewID('custom_field_6', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_6);
                if ($res) $number++;
            }
            if (isset($item->custom_field_7) && $item->custom_field_7 != '' ){
                $new_field_id = self::getNewID('custom_field_7', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_7);
                if ($res) $number++;
            }
            if (isset($item->custom_field_8) && $item->custom_field_8 != '' ){
                $new_field_id = self::getNewID('custom_field_8', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_8);
                if ($res) $number++;
            }
            if (isset($item->custom_field_9) && $item->custom_field_9 != '' ){
                $new_field_id = self::getNewID('custom_field_9', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_9);
                if ($res) $number++;
            }
            if (isset($item->custom_field_10) && $item->custom_field_10 != '' ){
                $new_field_id = self::getNewID('custom_field_10', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_10);
                if ($res) $number++;
            }
            if (isset($item->custom_field_11) && $item->custom_field_11 != '' ){
                $new_field_id = self::getNewID('custom_field_11', $new_fields);
                $item->custom_field_11 .= ' 00:00:00';
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_11);
                if ($res) $number++;
            }
            if (isset($item->custom_field_12) && $item->custom_field_12 != '' ){
                $new_field_id = self::getNewID('custom_field_12', $new_fields);
                $item->custom_field_12 .= ' 00:00:00';
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_12);
                if ($res) $number++;
            }
            if (isset($item->custom_field_13) && $item->custom_field_13 != '' ){
                $new_field_id = self::getNewID('custom_field_13', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_13);
                if ($res) $number++;
            }
            if (isset($item->custom_field_14) && $item->custom_field_14 != '' ){
                $new_field_id = self::getNewID('custom_field_14', $new_fields);
                $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_14);
                if ($res) $number++;
            }
            
        }
        
        return $number;        
    }

    /**
     * Save the jD fields data
     *
     * @param   array   $items      An array with all in jD created custom fields his default values and by lists this options
     * 
     * @return  integer             Amount of saved datasets
     * @return  array               The new created fields with ID and title
     * 
     * @since   3.9
     */
    public static function saveDataMulti($items, $new_added_fields)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        $number  = 0;
        $res     = 0;
        
        // We need the new created fields from jD
        // Select the required fields from the table.
        $query->select('a.id, a.title, a.context, a.label, a.language');
        $query->from('#__fields AS a');
        $query->where($db->quoteName('a.context') . ' = ' . $db->quote('com_jdownloads.download'));
        $query->order($db->quoteName('a.id'));
        $db->setQuery($query);
        $new_fields = $db->loadObjectList();
        
        if (!$new_fields){
            return $res;
        }
        
        for ($i=0; $i<count($new_fields); $i++){
             $new_fields[$i]->old_fieldname = $new_added_fields[$i]['fieldname'];
             $new_fields[$i]->old_fieldname = str_replace('.title', '', $new_fields[$i]->old_fieldname);      
             $new_fields[$i]->old_fieldname = str_replace('.', '_', $new_fields[$i]->old_fieldname);
        }
        
        $columns = array('field_id', 'item_id', 'value');
        
        // Now we add the new Joomla field ID to the jD data array               
        foreach ($items as $item){
            
            $query = $db->getQuery(true);
            
            if (isset($item->custom_field_1) && $item->custom_field_1 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_1', $new_fields, $item->language);
                if ($new_field_id){
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_1);   
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_2) && $item->custom_field_2 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_2', $new_fields, $item->language);
                if ($new_field_id){
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_2);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_3) && $item->custom_field_3 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_3', $new_fields, $item->language);
                if ($new_field_id){
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_3);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_4) && $item->custom_field_4 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_4', $new_fields, $item->language);
                if ($new_field_id){                    
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_4);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_5) && $item->custom_field_5 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_5', $new_fields, $item->language);
                if ($new_field_id){
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_5);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_6) && $item->custom_field_6 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_6', $new_fields, $item->language);
                if ($new_field_id){
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_6);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_7) && $item->custom_field_7 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_7', $new_fields, $item->language);
                if ($new_field_id){
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_7);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_8) && $item->custom_field_8 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_8', $new_fields, $item->language);
                if ($new_field_id){
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_8);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_9) && $item->custom_field_9 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_9', $new_fields, $item->language);
                if ($new_field_id){
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_9);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_10) && $item->custom_field_10 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_10', $new_fields, $item->language);
                if ($new_field_id){
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_10);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_11) && $item->custom_field_11 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_11', $new_fields, $item->language);
                if ($new_field_id){
                    $item->custom_field_11 .= ' 00:00:00';
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_11);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_12) && $item->custom_field_12 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_12', $new_fields, $item->language);
                if ($new_field_id){
                    $item->custom_field_12 .= ' 00:00:00';
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_12);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_13) && $item->custom_field_13 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_13', $new_fields, $item->language);
                if ($new_field_id){
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_13);
                    if ($res) $number++;
                }
            }
            if (isset($item->custom_field_14) && $item->custom_field_14 != '' ){
                $new_field_id = self::getNewIDMulti('custom_field_14', $new_fields, $item->language);
                if ($new_field_id){
                    $res = self::saveDataSet($columns, $new_field_id, $item->file_id, $item->custom_field_14);
                    if ($res) $number++;
                }
            }
        }
        return $number;        
    }

    
    /**
     * Get the new field ID 
     *
     * @param   integer   $searched_field_id   The required field ID
     *          array     $new_fields          The most important fields data
     * @return  integer                        The ID when found or 0
     * 
     * @since   3.9
     */
    public static function getNewID($searched_field_id, $new_fields){
        
        for ($i = 0; $i < count($new_fields); $i++) {
            if ($new_fields[$i]->old_fieldname == $searched_field_id) {
                return (int)$new_fields[$i]->id;
                break;
            }
        }
        return 0;
    }

    /**
     * Get the new field ID 
     *
     * @param   integer   $searched_field_id   The required field ID
     *          array     $new_fields          The most important fields data
     *          string    $language            The language from the new field that we search  
     * @return  integer                        The ID when found or 0
     * 
     * @since   3.9
     */
    public static function getNewIDMulti($searched_field_id, $new_fields, $language){
        
        for ($i = 0; $i < count($new_fields); $i++) {
            if ($new_fields[$i]->old_fieldname == $searched_field_id && $new_fields[$i]->language == $language) { 
                return (int)$new_fields[$i]->id;
                break;
            }
        }
        return 0;
    }
    
    /**
     * We try now to save for the new created fields the data 
     *
     * @param   string   $columns           The table columns
     *          integer  $new_field_id      The ID from the new created field in the _fields table
     *          integer  $file_id           The corresponded file ID to assign the data to the correct Download in jD
     *          string   $value             The value (text, string, date)
     * 
     * @return  boolean                     True on success
     * 
     * @since   3.9
     */
    public static function saveDataSet($columns, $new_field_id, $file_id, $value){
        
        $db = JFactory::getDBO();
        
        if (!$new_field_id) return;
        
        $values = array($db->quote($new_field_id), $db->quote($file_id), $db->quote($value));    
        
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__fields_values'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
            
        $db->setQuery($query);
        return $db->execute();    
    }
                              
    
}

?>