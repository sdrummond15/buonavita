<?php
/**
 * @package jDownloads
 * @version 4.0  
 * @copyright (C) 2007 - 2016 - Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 * 
 * jDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */


defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\Utilities\ArrayHelper;

require_once JPATH_SITE.'/components/com_jdownloads/helpers/categories.php';
require_once JPATH_SITE.'/components/com_jdownloads/helpers/query.php';
require_once JPATH_SITE.'/administrator/components/com_jdownloads/helpers/jdownloads.php';
require_once JPATH_SITE.'/administrator/components/com_jdownloads/helpers/associations.php';

jimport('joomla.application.component.modeladmin');
 

class jdownloadsModelCategory extends JModelAdmin
{
	
    protected $text_prefix = 'COM_JDOWNLOADS';
    
    /**
     * The context used for the associations table
     *
     * @var      string
     * @since    3.4.4
     */
    protected $associationsContext = 'com_jdownloads.category.item';

    /**
     * Method to test whether a record can be deleted.
     *
     * @param    object    A record object.
     * @return    boolean    True if allowed to delete the record. Defaults to the permission set in the component.
     */
    protected function canDelete($record)
    {
        if (empty($record->id))
        {
            return false;
        }
        return JFactory::getUser()->authorise('core.delete', 'com_jdownloads.category.' . (int) $record->id);        
    }
    
    
    /**
     * Method to test whether a record can have its state changed.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     */
    protected function canEditState($record)
    {
        $user = JFactory::getUser();

        // Check for existing category.
        if (!empty($record->id))
        {
            return $user->authorise('core.edit.state', 'com_jdownloads.category.' . (int) $record->id);
        }

        // New category, so check against the parent.
        if (!empty($record->parent_id))
        {
            return $user->authorise('core.edit.state', 'com_jdownloads.category.' . (int) $record->parent_id);
        }

        // Default to component settings if neither category nor parent known.
        else
        {
            return $user->authorise('core.edit.state', 'com_jdownloads');
        }
    }    
    
	
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param    type    The table type to instantiate
     * @param    string    A prefix for the table class name. Optional.
     * @param    array    Configuration array for model. Optional.
     * @return    JTable    A database object
     */
    public function getTable($type = 'category', $prefix = 'jdownloadsTable', $config = array()) 
    {
        return JTable::getInstance($type, $prefix, $config);
    }
    
    /**
     * Method to get the record form.
     *
     * @param    array    $data        Data for the form.
     * @param    boolean    $loadData    True if the form is to load its own data (default case), false if not.
     * @return    mixed    A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true) 
    {
        $app    = JFactory::getApplication();
        $jinput = JFactory::getApplication()->input;
        
        // Get the form.
        $form = $this->loadForm('com_jdownloads.category', 'category',
                                array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)){
            return false;
        }
        
        $user = JFactory::getUser();

        if (!$user->authorise('core.edit.state', 'com_jdownloads.category.' . $jinput->get('id'))){
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }        
        
        return $form;
    }
    
    /**
     * Method to determine if an association exists
     *
     * @return  boolean  True if the association exists
     *
     * @since   3.0
     */
    public function getAssoc()
    {
        static $assoc = null;

        if (!is_null($assoc))
        {
            return $assoc;
        }

        $assoc = JLanguageAssociations::isEnabled();

        if (!$assoc)
        {
            $assoc = false;
        }
        else
        {
            JLoader::register('JdownloadsAssociationsHelper', JPATH_ADMINISTRATOR . '/components/com_jdownloads/helpers/associations.php');

            $assoc = class_exists('JdownloadsAssociationsHelper');  
        }

        return $assoc;
    }
    
    /**
     * Method to get a single record.
     *
     * @param    integer    The id of the primary key.
     * @return    mixed    Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        
        if ($item->id){
            $registry = new JRegistry;
            // get the tags
            $item->tags = new JHelperTags;
            $item->tags->getTagIds($item->id, 'com_jdownloads.category');         
        }        
        
        // Added to support the Joomla Language Associations
        // Load associated Category items
        $assoc = JLanguageAssociations::isEnabled();

        if ($assoc)
        {
            $item->associations = array();

            if ($item->id != null)
            {
                $associations = JLanguageAssociations::getAssociations('com_jdownloads', '#__jdownloads_categories', 'com_jdownloads.category.item', $item->id, 'id', 'alias', '');  
                //$associations = JdownloadsAssociationsHelper::getAssociations('category', $item->id);  
                                
                foreach ($associations as $tag => $association)
                {
                    $item->associations[$tag] = $association->id;
                }
            }
        }
        
        return $item;
    }      
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     * @since    1.6
     */
    protected function loadFormData() 
    {
        // Check the session for previously entered form data.
        $app = JFactory::getApplication();
        $data = JFactory::getApplication()->getUserState('com_jdownloads.edit.category.data', array());

        if (empty($data)) 
        {
            $data = $this->getItem();
            
            // Pre-select some filters (Status, Language, Access) in edit form if those have been selected in Category Manager
            if (!$data->id)
            {
                $filters = (array) $app->getUserState('com_jdownloads.categories.filter');

                $data->set(
                    'published',
                    $app->input->getInt(
                        'published',
                        ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                    )
                );
                $data->set('language', $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)));
                $data->set(
                    'access',
                    $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access')))
                );
            }
            
        }
        
        $this->preprocessData('com_jdownloads.category', $data);
        
        return $data;
    }

    
    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @since    1.6
     */
    protected function prepareTable($table)
    {
        jimport('joomla.filter.output');
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        $table->title        = htmlspecialchars_decode($table->title, ENT_QUOTES);
        $table->alias        = JApplication::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = JApplication::stringURLSafe($table->title);
            if (trim(str_replace('-','',$table->alias)) == '') {
                $table->alias = JFactory::getDate()->format("Y-m-d-H-i-s");
        	}
        }        
        
        if (!empty($table->password)) {
            $table->password_md5 = hash('sha256', $table->password);
        }

        if (empty($table->id)) {
            // Set ordering to the last item if not set - DEPRECATED in jD 2.0 and not really used.
            if (empty($table->ordering)) {
                $db = JFactory::getDbo();
                $db->setQuery('SELECT MAX(ordering) FROM #__jdownloads_categories WHERE parent_id = \''.$table->parent_id.'\'');
                $max = $db->loadResult();
                $table->ordering = $max+1;
            } 
        }
        else {
            // Set the values for an old category
            
        }
    }
    
    /**
     * Method to preprocess the form.
     *
     * @param   JForm   $form   A JForm object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import.
     *
     * @return  mixed
     *
     * @see     JFormField
     * @since   1.6
     * @throws  Exception if there is an error in the form event.
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        
        // Association category items
        
        $languages = JLanguageHelper::getContentLanguages(false, true, null, 'ordering', 'asc');

        if (count($languages) > 1){
            $addform = new SimpleXMLElement('<form />');
            $fields = $addform->addChild('fields');
            $fields->addAttribute('name', 'associations');
            $fieldset = $fields->addChild('fieldset');
            $fieldset->addAttribute('name', 'item_associations');

            foreach ($languages as $language)
            {
                $field = $fieldset->addChild('field');
                $field->addAttribute('name', $language->lang_code);
                $field->addAttribute('type', 'modal_category');
                $field->addAttribute('language', $language->lang_code);
                $field->addAttribute('label', $language->title);
                $field->addAttribute('translate_label', 'false');
                // $field->addAttribute('extension', $extension);
                $field->addAttribute('select', 'true');
                $field->addAttribute('new', 'true');
                $field->addAttribute('edit', 'true');
                $field->addAttribute('clear', 'true');
                $field->addAttribute('propagate', 'true');
            }

            $form->load($addform, false);
        }
    

        // Trigger the default form events.
        parent::preprocessForm($form, $data, $group);
    }
    
    
    /**
     * Method to save the form data.
     *
     * @param    array    The form data.
     * @param    boolean  The switch for added by monitoring
     * @return    boolean    True on success.
     */
    public function save($data, $auto_added = false)
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        // Initialise variables;
        $dispatcher    = JEventDispatcher::getInstance();
        $jinput        = JFactory::getApplication()->input;
        $table         = $this->getTable();
        $pk            = (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
        $isNew         = true;
        $catChanged    = false;
        $title_changed = false;
        $cat_dir_changed_manually = false;
        $checked_cat_title = '';
        
        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');        
        
        // remove bad input values
        $data['parent_id'] = (int)$data['parent_id'];
        
        // Prevent a notice message when cat_dir not defined (while the 'uncategorised' category will be created)
        if (!isset($data['cat_dir'])){
            $data['cat_dir'] = '';
        }
       
        // Load the row if saving an existing category.
        if ($pk > 0) {
            $table->load($pk);
            $isNew = false;
            if ($table->parent_id != $data['parent_id']){
                // we must be here careful for the case that user has manipulated manually the parent_id
                if ($data['parent_id'] == 0){
                    // invalid value, so we do here nothing and use the old parent_id
                   $data['parent_id'] = $table->parent_id;
                } else {   
                   $catChanged = true;
                }   
            }
        }

        // parent id must have at minimum a 1 for 'root' category
        if ($data['parent_id'] == 0){
            $data['parent_id'] = 1;
        }         
        
        // is title changed?
        $org_title = $jinput->get('cat_title_org', '', 'string');
        if ($org_title != '' && $org_title != $data['title']) {
            $title_changed = true;
        }
        
        // cat_dir manually changed?
        $old_cat_dir = $jinput->get('cat_dir_org', '', 'string');
        if ($old_cat_dir != '' && $old_cat_dir != $data['cat_dir']) {
            $cat_dir_changed_manually = true;
        }
        
        if (!$auto_added){ 
            // we must check first the cat_dir content and remove some critical things
            if ($params->get('create_auto_cat_dir')){
                // check whether we have a different title and cat_dir (as example when prior was activated the manually category name building)
                if (!$title_changed && ($data['title'] != $data['cat_dir'])){
                    // activate this switch
                    $title_changed = true;
                } 
                
                // the cat_dir name is managed by jD and builded from category title
                $checked_cat_title = JDownloadsHelper::getCleanFolderFileName($data['title']);
            } else {
                // the cat_dir name is managed by the user and the cat_dir field
                $checked_cat_title = JDownloadsHelper::getCleanFolderFileName($data['cat_dir']);
            }    
            
            $data['cat_dir'] = $checked_cat_title;
        }

        if ($isNew || $title_changed || $cat_dir_changed_manually){
            // make sure that we have a new (valid) folder name / same when changed title or manually cat_dir field
           $data['cat_dir'] = $this->generateNewFolderName($data['parent_id'], $data['cat_dir'], $data['id']);        
        }
        
        if ($data['cat_dir'] == ''){
            // ERROR - we have a empty category folder name - not possible! 
            $this->setError(JText::_('COM_JDOWNLOADS_BACKEND_CATSEDIT_ERROR_FOLDER_NAME'));
            return false;
        }
        
        // Set the new parent id if parent id not matched OR while New/Save as Copy .
        if ($table->parent_id != $data['parent_id'] || $data['id'] == 0) {
            $table->setLocation($data['parent_id'], 'last-child');
        }

        // Alter the title for save as copy
        if ($jinput->get('task') == 'save2copy') {
            list($title,$alias) = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
            $data['title']    = $title;
            $data['alias']    = $alias;
        }
        
        if ((!empty($data['tags']) && $data['tags'][0] != ''))
        {
            $table->newTags = $data['tags'];
        }         

        // Bind the data.
        if (!$table->bind($data)) {
            $this->setError($table->getError());
            return false;
        }

        // Prepare the row for saving
        $this->prepareTable($table);
        
        // Check the data.
        if (!$table->checkData($isNew, $auto_added)) {
            $this->setError($table->getError());
            return false;
        }

        // Trigger the onContentBeforeSave event.
        $result = $dispatcher->trigger($this->event_before_save, array($this->option.'.'.$this->name, &$table, $isNew));
        if (in_array(false, $result, true)) {
            $this->setError($table->getError());
            return false;
        }

        // Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        }
        
        // folder handling functionality - not used for auto added
        if (!$auto_added){
            if (!$table->checkCategoryFolder($isNew, $catChanged, $title_changed, $checked_cat_title, $cat_dir_changed_manually)) {
                if ($table->published = 1){
                    $table->published = 0; 
                    $table->store();
                } 
                //return false;
            }
        }            

        $assoc = $this->getAssoc();

        if ($assoc)
        {
            // Adding self to the association
            $associations = isset($data['associations']) ? $data['associations'] : array();

            // Unset any invalid associations
            $associations = Joomla\Utilities\ArrayHelper::toInteger($associations);

            foreach ($associations as $tag => $id)
            {
                if (!$id)
                {
                    unset($associations[$tag]);
                }
            }

            // Detecting all item menus
            $allLanguage = $table->language == '*';

            if ($allLanguage && !empty($associations))
            {
                JError::raiseNotice(403, JText::_('COM_CATEGORIES_ERROR_ALL_LANGUAGE_ASSOCIATED'));
            }

            // Get associationskey for edited item
            $db    = $this->getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('key'))
                ->from($db->quoteName('#__associations'))
                ->where($db->quoteName('context') . ' = ' . $db->quote($this->associationsContext))
                ->where($db->quoteName('id') . ' = ' . (int) $table->id);
            $db->setQuery($query);
            $oldKey = $db->loadResult();

            // Deleting old associations for the associated items
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__associations'))
                ->where($db->quoteName('context') . ' = ' . $db->quote($this->associationsContext));

            if ($associations)
            {
                $query->where('(' . $db->quoteName('id') . ' IN (' . implode(',', $associations) . ') OR '
                    . $db->quoteName('key') . ' = ' . $db->quote($oldKey) . ')');
            }
            else
            {
                $query->where($db->quoteName('key') . ' = ' . $db->quote($oldKey));
            }

            $db->setQuery($query);

            try
            {
                $db->execute();
            }
            catch (RuntimeException $e)
            {
                $this->setError($e->getMessage());

                return false;
            }

            // Adding self to the association
            if (!$allLanguage)
            {
                $associations[$table->language] = (int) $table->id;
            }

            if (count($associations) > 1)
            {
                // Adding new association for these items
                $key = md5(json_encode($associations));
                $query->clear()
                    ->insert('#__associations');

                foreach ($associations as $id)
                {
                    $query->values(((int) $id) . ',' . $db->quote($this->associationsContext) . ',' . $db->quote($key));
                }

                $db->setQuery($query);

                try
                {
                    $db->execute();
                }
                catch (RuntimeException $e)
                {
                    $this->setError($e->getMessage());

                    return false;
                }
            }
        }
        
        // Trigger the onContentAfterSave event.
        $dispatcher->trigger($this->event_after_save, array($this->option.'.'.$this->name, &$table, $isNew));

        // Rebuild the path for the category:
        // but only when it is a sub category (parent_id > 1)
        if ($table->parent_id > 1) {
            if (!$table->rebuildPath($table->id)) {
                $this->setError($table->getError());
                return false;
            } 
        }
        
        // Rebuild the paths of the category's children:
        if ($table->hasChildren($table->id)){
            if ($table->cat_dir_parent != ''){
                $path = $table->cat_dir_parent.'/'.$table->cat_dir;
            } else {
                $path = $table->cat_dir;
            }
            if (!$table->rebuild($table->id, $table->lft, $table->level, $path)) {
                $this->setError($table->getError());
                return false;
            } 
        }
        
        $this->setState($this->getName().'.id', $table->id);

        // Clear the cache
        $this->cleanCache();

        return true;
    }
    
    /**
     * Method to save the reordered nested set tree.
     * First we save the new order values in the lft values of the changed ids.
     * Then we invoke the table rebuild to implement the new ordering.
     *
     * @param   array    $idArray    An array of primary key ids.
     * @param   integer  $lft_array  The lft value
     *
     * @return  boolean  False on failure or error, True otherwise
     *
    */
    public function saveorder($idArray = null, $lft_array = null)
    {
        // Get an instance of the table object.
        $table = $this->getTable();

        if (!$table->saveorder($idArray, $lft_array))
        {
            $this->setError($table->getError());
            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }
    
    /**
     * Batch copy categories to a new category.
     *
     * @param   integer  $value     The new category.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  mixed  An array of new IDs on success, boolean false on failure.
     *
     */
    protected function batchCopy($value, $pks, $contexts)
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );
        // UTF-8 aware alternative functions
        jimport( 'joomla.utilities.string' );        
        
        $type = new JUcmType;
        $this->type = $type->getTypeByAlias($this->typeAlias);
        
        $root_folder = $params->get('files_uploaddir');
        $new_parent_id = (int) $value;

        $table = $this->getTable();
        $db = $this->getDbo();
        $user = JFactory::getUser();

        // check at first, that it is not already a other batch job in progress
        if ($params->get('categories_batch_in_progress') || $params->get('downloads_batch_in_progress')){
            // generate the warning and return
            JError::raiseWarning(100, JText::_('COM_JDOWNLOADS_BATCH_IS_ALWAYS_STARTED')); 
            return false;
        } else {
            // update at first the batch progress setting in params 
            $result = JDownloadsHelper::changeParamSetting('categories_batch_in_progress', '1');
        }
        
        $i      = 0;
        $newId  = 0;
        $new_parent_dir_part = '';
        $changed_cat_dir = '';
        
        $old_cat_dir        = '';
        $old_cat_dir_parent = '';
        
        // base category directory name - changed or not
        $new_target_base_folder_name = '';
          

        // Check that the parent exists
        if ($new_parent_id){
            if (!$table->load($new_parent_id)){
                if ($error = $table->getError()){
                    // Fatal error
                    $this->setError($error);
                    return false;
                } else {
                    // Non-fatal error
                    $this->setError(JText::_('COM_JDOWNLOADS_BATCH_MOVE_PARENT_NOT_FOUND'));
                    $new_parent_id = 0;
                }
            }
        }

        // If the parent is 0, set it to the ID of the root item in the tree
        if (empty($new_parent_id) || $new_parent_id == 1){
            if (!$new_parent_id = $table->getRootId()){
                $this->setError($db->getErrorMsg());
                return false;
            } 
               // Make sure we can create in root
               elseif (!$user->authorise('core.create', 'com_jdownloads'))
            {
                $this->setError(JText::_('COM_JDOWNLOADS_BATCH_CANNOT_CREATE'));
                return false;
            }
        }

        // We need to log the parent ID
        $parents = array();

        // Calculate the emergency stop count as a precaution against a runaway loop bug
        $query = $db->getQuery(true);
        $query->select('COUNT(id)');
        $query->from($db->quoteName('#__jdownloads_categories'));
        $db->setQuery($query);
        $count = $db->loadResult();

        if ($error = $db->getErrorMsg()){
            $this->setError($error);
            return false;
        }

        // Parent exists so we let's proceed
        while (!empty($pks) && $count > 0)
        {
            // Pop the first id off the stack
            $pk = array_shift($pks);
            $table->reset();

            // Check that the row actually exists
            if (!$table->load($pk)){
                if ($error = $table->getError()){
                    // Fatal error
                    $this->setError($error);
                    return false;
                } else {
                    // Not fatal error
                    $this->setError(JText::sprintf('COM_JDOWNLOADS_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Copy is a bit tricky, because we also need to copy the children
            $query->clear();
            $query->select('id');
            $query->from($db->quoteName('#__jdownloads_categories'));
            $query->where('lft > ' . (int) $table->lft);
            $query->where('rgt < ' . (int) $table->rgt);
            $db->setQuery($query);
            $childIds = $db->loadColumn();

            // Add child ID's to the array only if they aren't already there.
            foreach ($childIds as $childId){
                if (!in_array($childId, $pks)){
                    array_push($pks, $childId);
                }
            }

            // Make a copy of the old ID and Parent ID
            $oldId = $table->id;
            $oldParentId = $table->parent_id; 
            
            // Make a copy of the old category folder path
            $old_cat_dir = $table->cat_dir;
            $old_cat_dir_parent = $table->cat_dir_parent;
            if ($old_cat_dir_parent != ''){
                $old_cat_path = $old_cat_dir_parent.'/'.$old_cat_dir;
            } else {
                $old_cat_path = $old_cat_dir;
            }

            
            // Reset the id because we are making a copy.
            $table->id = 0;

            // If we a copying children, the Old ID will turn up in the parents list
            // otherwise it's a new top level item
            $table->parent_id = isset($parents[$oldParentId]) ? $parents[$oldParentId] : $new_parent_id;
            if ($table->parent_id == 1){
                $table->cat_dir_parent = '';
            }    

            // Set the new location in the tree for the node.
            $table->setLocation($table->parent_id, 'last-child');

            $table->level = null;
            $table->lft = null;
            $table->rgt = null;

            // Alter the title & alias when we have the first cat
            list($title, $alias) = $this->generateNewTitle($table->parent_id, $table->alias, $table->title);
            $table->title = $title;
            $table->alias = $alias;
        
            // build new cat_dir from the old one
            $cat_dir = $this->generateNewFolderName($table->parent_id, $table->cat_dir, $table->id);

            $replace = array ( '(' => '', ')' => '' );
            $cat_dir = strtr ( $cat_dir, $replace );
            
            if ($cat_dir != $table->cat_dir){
                $changed_cat_dir = $cat_dir;
            }
            
            // we need the correct  path for the field cat_dir_parent
            if ($table->parent_id > 1 || $oldParentId > 1){
                $new_parent_cat_path = $table->getParentCategoryPath($table->parent_id);
            } else {
                // root cat
                $new_parent_cat_path = '';
            }            
            
            // build the new parent cat path
            $table->cat_dir_parent = $new_parent_cat_path;

            // make sure that we have not twice the same category folder name (but not for childrens)
            if (!in_array($oldParentId, $parents) && !$parents[$oldParentId]){
                if ($new_parent_cat_path != ''){
                    while (JFolder::exists($root_folder.'/'.$new_parent_cat_path.'/'.$cat_dir)){
                        $title = JString::increment($cat_dir);
                        $cat_dir = strtr ( $title, $replace );
                    }
                } else {
                    while (JFolder::exists($root_folder.'/'.$cat_dir)){
                        $title = JString::increment($cat_dir);
                        $cat_dir = strtr ( $title, $replace );
                    }
                }
            }            
            $table->cat_dir = $cat_dir;
            
            // Store the row
            if (!$table->store()){
                $this->setError($table->getError());
                return false;
            }
            
            // build the new cat_dir_parent part for the childrens
            if ($newId == 0 && $table->cat_dir_parent != ''){
               $new_parent_dir_part = $table->cat_dir_parent.'/'.$table->cat_dir; 
            }
            
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[$i] = $newId;
            $i++;

            // Now we log the old 'parent' to the new 'parent'
            $parents[$oldId] = $table->id;
            $count--;


            // Rebuild the hierarchy.
            if (!$table->rebuild()){
                $this->setError($table->getError());
                return false;
            }

            // Rebuild the tree path.
            if (!$table->rebuildPath($table->id)){
                $this->setError($table->getError());
                return false;
            }
            
            // build the source path 
            if ($old_cat_dir != ''){
                $source_dir = $root_folder.'/'.$old_cat_path;
            } else {
                $source_dir = $root_folder;
            }
            // build the target path 
            if ($new_parent_cat_path != ''){
                $target_dir = $root_folder.'/'.$new_parent_cat_path.'/'.$cat_dir;
            } else {
                $target_dir = $root_folder.'/'.$cat_dir;
            }

            // copy only the dir when we have it not already copied with parent folder 
            if (!in_array($oldParentId, $parents) && !$parents[$oldParentId]){
                
                // move now the complete category folder to the new location!
                // the path must have at the end a slash
                $message = '';
                JDownloadsHelper::moveDirs($source_dir.'/', $target_dir.'/', true, $message, false, true, true );
                if ($message){
                    JError::raiseWarning(100, $message);
                }             
            }                
        
        } 
        
        // actualize at last the batch progress setting 
        $result = JDownloadsHelper::changeParamSetting('categories_batch_in_progress', '0');
        
        return $newIds;
    }

    /**
     * Batch move categories to a other category.
     *
     * @param   integer  $value     The new category ID.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean  True on success.
     *
     */
    protected function batchMove($value, $pks, $contexts)
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.file' );
        // UTF-8 aware alternative functions
        jimport( 'joomla.utilities.string' );                

        $root_folder = $params->get('files_uploaddir');
        $new_parent_id = (int) $value;
        
        $table = $this->getTable();
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        // check at first, that it is not always run a other batch job
        if ($params->get('categories_batch_in_progress') || $params->get('downloads_batch_in_progress')){
            // generate the warning and return
            JError::raiseWarning(100, JText::_('COM_JDOWNLOADS_BATCH_IS_ALWAYS_STARTED')); 
            return false;
        } else {
            // actualize at first the batch progress setting 
            $result = JDownloadsHelper::changeParamSetting('categories_batch_in_progress', '1');
        }     

        // Check that the parent exists.
        if ($new_parent_id){
            if (!$table->load($new_parent_id)){
                if ($error = $table->getError()){
                    // Fatal error
                    $this->setError($error);
                    return false;
                } else {
                    // Non-fatal error
                    $this->setError(JText::_('COM_JDOWNLOADS_BATCH_MOVE_PARENT_NOT_FOUND'));
                    $new_parent_id = 0;
                }
            }
        }
        
        // We are going to store all the children and just move the category
        $children = array();

        // Parent exists so we let's proceed
        foreach ($pks as $pk){
            // Check that the row actually exists
            if (!$table->load($pk)){
                if ($error = $table->getError()){
                    // Fatal error
                    $this->setError($error);
                    return false;
                } else {
                    // Not fatal error
                    $this->setError(JText::sprintf('COM_JDOWNLOADS_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            //$oldParentId = $table->parent_id;
            $oldParentId = 0;
            
            // Make a copy of the old category folder path
            $old_cat_dir = $table->cat_dir;
            $old_cat_dir_parent = $table->cat_dir_parent;
            if ($old_cat_dir_parent != ''){
                $old_cat_path = $old_cat_dir_parent.'/'.$old_cat_dir;
            } else {
                $old_cat_path = $old_cat_dir;
            }   

            $cat_dir = $table->cat_dir;             

            // Set the new location in the tree for the node.
            $table->setLocation($new_parent_id, 'last-child');

            // Check if we are moving to a different parent
            if ($new_parent_id != $table->parent_id){
                // Add the child node ids to the children array.
                $query->clear();
                $query->select('id');
                $query->from($db->quoteName('#__jdownloads_categories'));
                $query->where($db->quoteName('lft' ) .' BETWEEN ' . (int) $table->lft . ' AND ' . (int) $table->rgt);
                $db->setQuery($query);
                $children = array_merge($children, (array) $db->loadColumn());
            }

            if ($new_parent_id > 1 || $oldParentId > 1){
                $new_parent_cat_path = $table->getParentCategoryPath($new_parent_id);
            } else {
                // root cat
                $new_parent_cat_path = '';
            }
            
            // build the new parent cat path
            $table->cat_dir_parent = $new_parent_cat_path;

            // build new cat_dir name when it exists allways
            $replace = array ( '(' => '', ')' => '' );
            if ($new_parent_id > 1){
                while (JFolder::exists($root_folder.'/'.$new_parent_cat_path.'/'.$cat_dir)){
                    $title = JString::increment($cat_dir);
                    $cat_dir = strtr ( $title, $replace );
                }
            } else {
                while (JFolder::exists($root_folder.'/'.$cat_dir)){
                    $title = JString::increment($cat_dir);
                    $cat_dir = strtr ( $title, $replace );
                }                
            }    
            
            $table->cat_dir = $cat_dir;            
            
            // Store the row
            if (!$table->store()){
                $this->setError($table->getError());
                return false;
            }
            
            // Rebuild the hierarchy.
            if (!$table->rebuild()){
                $this->setError($table->getError());
                return false;
            }            

            // Rebuild the tree path.
            if (!$table->rebuildPath()){
                $this->setError($table->getError());
                return false;
            }
            
            // biuld the source path 
            if ($old_cat_dir != ''){
                $source_dir = $root_folder.'/'.$old_cat_path;
            } else {
                $source_dir = $root_folder;
            }
            // build the target path 
            if ($new_parent_cat_path != ''){
                $target_dir = $root_folder.'/'.$new_parent_cat_path.'/'.$cat_dir;
            } else {
                $target_dir = $root_folder.'/'.$cat_dir;
            }

            // move now the complete category folder to the new location!
            // the path must have at the end a slash
            $message = '';
            JDownloadsHelper::moveDirs($source_dir.'/', $target_dir.'/', true, $message, true, false, false);
            if ($message == ''){
                // check the really result:
                if (JFolder::exists($target_dir) && !JFolder::exists($source_dir)){
                    // JError::raiseNotice(100, JText::sprintf('COM_JDOWNLOADS_BATCH_CAT_MOVED_MSG', $source_dir));    
                } else {
                    if (JFolder::exists($source_dir)){
                        $res = JDownloadsHelper::delete_dir_and_allfiles($source_dir);
                        if (JFolder::exists($source_dir)){
                            JError::raiseWarning(100, JText::sprintf('COM_JDOWNLOADS_BATCH_CAT_NOT_MOVED_MSG', $source_dir));
                        }    
                    }    
                }
            } else {
                JError::raiseWarning(100, $message);
            }                                
        }

        // Process the child rows
        if (!empty($children)){
            // Remove any duplicates and sanitize ids.
            $children = array_unique($children);
            ArrayHelper::toInteger($children);

            // Check for a database error.
            if ($db->getErrorNum()){
                $this->setError($db->getErrorMsg());
                return false;
            }
        }
        
        // actualize at last the batch progress setting 
        $result = JDownloadsHelper::changeParamSetting('categories_batch_in_progress', '0');

        return true;
    }    
    
    /**
     * Method to change the title & alias.
     *
     * @param   integer  $parent_id  The id of the parent.
     * @param   string   $alias      The alias.
     * @param   string   $title      The title.
     *
     * @return  array  Contains the modified title and alias.
     */
    protected function generateNewTitle($parent_id, $alias, $title)
    {
        // Alter the title & alias
        $table = $this->getTable();
        while ($table->load(array('alias' => $alias, 'parent_id' => $parent_id))){
            $title = JString::increment($title);
            $alias = JString::increment($alias, 'dash');
        }
        return array($title, $alias);
    } 
    
    /**
     * Method to get a valid category directory name, which not yet exists for the new created category
     *
     * @param   integer  $parent_id  The id of the parent category.
     * @param   string   $cat_dir    The given folder name
     * @param   integer  $id         The id of the category   
     *
     * @return  string  Contains the modified category name
     */    
    protected function generateNewFolderName($parent_id, $cat_dir, $id)
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        $table = $this->getTable();
        
        if ($table->load(array('cat_dir' => $cat_dir, 'parent_id' => $parent_id)) && ($table->id != $id)){
            // do it only when the $table->id is not the same as the current - otherwise it found it always 
            while ($table->load(array('cat_dir' => $cat_dir, 'parent_id' => $parent_id))){
                // use the settings from config
                if ($params->get('fix_upload_filename_blanks')){
                    $cat_dir = JString::increment($cat_dir, 'dash');
                } else {
                    $cat_dir = JString::increment($cat_dir, 'default');
                }    
            } 
        }
        return $cat_dir;
    }
    
    /**
     * Method to run categories tree rebuild
     *
     * @return  boolean  True on success.
     */        
    public function rebuildCategories()
    {
        $table = $this->getTable();
        
        // Rebuild the hierarchy.
        if (!$table->rebuild()){
            $this->setError($table->getError());
            return false;
        }            

        // Rebuild the tree path.
        if (!$table->rebuildPath()){
            $this->setError($table->getError());
            return false;
        }        
        return true;
    }
    
    /**
     * Method rebuild the entire nested set tree. Started by categories toolbar.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     */
    public function rebuild()
    {
        // Get an instance of the table object.
        $table = $this->getTable();

        if (!$table->rebuild())
        {
            $this->setError($table->getError());
            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }
    
    /**
    * Method to create a new category 
    * 
    * @param mixed $name
    * @param mixed $parent_id
    * @param mixed $note
    * @param mixed $description
    * @param mixed $published
    * @return JCategoryNode
    */
    public function createCategory( $name, $parent_id = 1, $note, $description, $published = 1 )
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_jdownloads/tables' );

        $cat_model = JModelLegacy::getInstance( 'Category', 'jdownloadsModel' );

        $data = array (
            'id' => 0,
            'parent_id' => $parent_id,
            'title' => $name,
            'alias' => '',
            'notes' => $note,
            'description' => $description,
            'pic' => $params->get('cat_pic_default_filename'),
            'published' => $published,
            'access' => '1',
            'metadesc' => '',
            'metakey' => '',
            'created_user_id' => '0',
            'language' => '*',
            'rules' => array(
                'core.create' => array(),
                'core.delete' => array(),
                'core.edit' => array(),
                'core.edit.state' => array(),
                'core.edit.own' => array(),
                'download' => array(),
            ),
            'params' => array(),
        );

        if( !$cat_model->save( $data ) )
        {
            return NULL;
        }
        
        $options = array();
        $categories = JDCategories::getInstance('jdownloads', $options);
        $subcategory = $categories->get( $cat_model->getState( "category.id" ) );
        return $subcategory;
    }                      

    /**
    * Method to create a new category from monitoring script 
    * 
    * @param mixed $data    
    * @return JCategoryNode
    */
    public function createAutoCategory($data)
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_jdownloads/tables' );

        $cat_model = JModelLegacy::getInstance( 'Category', 'jdownloadsModel' );

        if (!$cat_model->save( $data, true ) ){
            return NULL;
        }
        
        $new_category_id = $cat_model->getState( "category.id" ) ;
        if ($new_category_id > 0){
            return $new_category_id;
        } 
        
        return true;
    }                      

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    &$pks   A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     */
    public function publish(&$pks, $value = 1)
    {
        // Initialise variables.
        $dispatcher = JDispatcher::getInstance();
        $user = JFactory::getUser();
        $table = $this->getTable('category', 'jdownloadsTable');
        $pks = (array) $pks;

        // Include the content plugins for the change of state event.
        JPluginHelper::importPlugin('content');

        // Access checks.
        foreach ($pks as $i => $pk)
        {
            $table->reset();

            if ($table->load($pk))
            {
                if (!$this->canEditState($table)){
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    JLog::add(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
                    return false;
                } else {
                    if ($value == 1){
                        if (!$this->existCategoryFolder($table)){
                            // Prune items which can't be published.
                            $msg = JText::sprintf('COM_JDOWNLOADS_CATS_PUBLISH_NO_FOLDER', (int)$pks[$i]);
                            JLog::add($msg, JLog::WARNING, 'jerror');
                            unset($pks[$i]);
                            return false;
                        }  
                    }
                } 
                
                // If the table is checked out by another user, drop it and report to the user trying to change its state.
                if (property_exists($table, 'checked_out') && $table->checked_out && ($table->checked_out != $user->id)){
                    JLog::add(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'), JLog::WARNING, 'jerror');
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    return false;
                }                
                   
            }
        }

        // Attempt to change the state of the records.
        if (!$table->publish($pks, $value, $user->get('id')))
        {
            $this->setError($table->getError());
            return false;
        }

        $context = $this->option . '.' . $this->name;

        // Trigger the onContentChangeState event.
        $result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

        // Trigger the onCategoryChangeState event.
        $dispatcher->trigger('onCategoryChangeState', array($context, $pks, $value));        
        
        if (in_array(false, $result, true))
        {
            $this->setError($table->getError());
            return false;
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }
    
    /**
     * Method to check the presence from a categories folder
     *
     * @return  boolean  True on success.
     *
     */
    public function existCategoryFolder($table)
    {
        $params = JComponentHelper::getParams('com_jdownloads');

        jimport( 'joomla.filesystem.folder' );

        $root_dir = $params->get('files_uploaddir');
        
        if ($table->cat_dir_parent != ''){
            $path = $root_dir.'/'.$table->cat_dir_parent.'/'.$table->cat_dir;
        } else {
            $path = $root_dir.'/'.$table->cat_dir;
        }
        
        if (!JFolder::exists($path)){
            return false;
        } 
        
        return true;    
    }
    
        
}
?>