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


defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

JLoader::register('JDownloadsHelper', JPATH_ADMINISTRATOR . '/components/com_jdownloads/helpers/jdownloads.php');

class jdownloadsModelDownload extends JModelAdmin
{
	
    // The prefix to use with controller messages.
    protected $text_prefix = 'COM_JDOWNLOADS';

    // The context used for the associations table
    protected $associationsContext = 'com_jdownloads.item';
    
    // Required for Joomla fields support
    protected $option = 'com_jdownloads';
    protected $name = 'download';
    
    // Allowed batch commands
    protected $jd_batch_commands = array(
        'price' => 'batchPrice'
    );
    
    /**
     * Method to test whether a record can be deleted.
     *
     * @param    object    A record object.
     * @return    boolean    True if allowed to delete the record. Defaults to the permission set in the component.
     * @since    1.6
     */
    protected function canDelete($record)
    {
        return parent::canDelete($record);
    }
    
    /**
     * Method to test whether a record can have its state changed.
     *
     * @param    object    A record object.
     * @return    boolean    True if allowed to change the state of the record. Defaults to the permission set in the component.
     * @since    1.6
     */
    protected function canEditState($record)
    {
        return parent::canEditState($record);
    }
    
	
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param    type    The table type to instantiate
     * @param    string    A prefix for the table class name. Optional.
     * @param    array    Configuration array for model. Optional.
     * @return    JTable    A database object
     * @since    1.6
     */
    public function getTable($type = 'download', $prefix = 'jdownloadsTable', $config = array()) 
    {
        return JTable::getInstance($type, $prefix, $config);
    }
    
    /**
     * Method to get the record form.
     *
     * @param    array    $data        Data for the form.
     * @param    boolean    $loadData    True if the form is to load its own data (default case), false if not.
     * @return    mixed    A JForm object on success, false on failure
     * @since    1.6
     */
    public function getForm($data = array(), $loadData = true) 
    {
        
        // Initialise variables.
        $app    = JFactory::getApplication();
        
        // Get the form.
        $form = $this->loadForm('com_jdownloads.download', 'download',
                                array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) 
        {
            return false;
        }
        
        $jinput = JFactory::getApplication()->input;

        /*
         * The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
         * The back end uses id so we use that the rest of the time and set it to 0 by default.
         */
        $id = $jinput->get('a_id', $jinput->get('id', 0));

        // Determine correct permissions to check.
        if ($this->getState('download.id'))
        {
            $id = $this->getState('download.id');

            // Existing record. Can only edit in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.edit');

            // Existing record. Can only edit own Downloads in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.edit.own');
        }
        else
        {
            // New record. Can only create in selected categories.
            $form->setFieldAttribute('catid', 'action', 'core.create');
        }

        $user = JFactory::getUser();

        // Check for existing Download.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_jdownloads.download.' . (int) $id))
            || ($id == 0 && !$user->authorise('core.edit.state', 'com_jdownloads')))
        {
            // Disable fields for display.
            $form->setFieldAttribute('featured', 'disabled', 'true');
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an Download you can edit.
            $form->setFieldAttribute('featured', 'filter', 'unset');
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        // Prevent messing with Download language and category when editing existing Download with associations
        $app = JFactory::getApplication();
        $assoc = JLanguageAssociations::isEnabled();

        // Check if article is associated
        if ($this->getState('download.id') && $app->isClient('site') && $assoc)
        {
            $associations = JLanguageAssociations::getAssociations('com_jdownloads', '#__jdownloads_files', 'com_jdownloads.item', $id, 'id', 'alias', 'catid');

            // Make fields read only
            if (!empty($associations))
            {
                $form->setFieldAttribute('language', 'readonly', 'true');
                $form->setFieldAttribute('catid', 'readonly', 'true');
                $form->setFieldAttribute('language', 'filter', 'unset');
                $form->setFieldAttribute('catid', 'filter', 'unset');
            }
        }
        
        return $form;
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
        $app  = JFactory::getApplication();
        $data = $app->getUserState('com_jdownloads.edit.download.data', array());
        
        if (empty($data)) 
        {
            $data = $this->getItem();

            // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Downloads Manager
            if ($this->getState('download.id') == 0)
            {
                $filters = (array) $app->getUserState('com_jdownloads.downloads.filter');
                $data->set(
                    'state',
                    $app->input->getInt(
                        'state',
                        ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                    )
                );
                $data->set('catid', $app->input->getInt('catid', (!empty($filters['category_id']) ? $filters['category_id'] : null)));
                $data->set('language', $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)));
                $data->set('access',
                    $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access')))
                );
            }
        
        }
        
        // If there are params fieldsets in the form it will fail with a registry object
        if (isset($data->params) && $data->params instanceof Registry){
            $data->params = $data->params->toArray();
        }
        
        $this->preprocessData('com_jdownloads.download', $data);
        
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

        $user = JFactory::getUser();
        
        // Set the publish date to now
        $db = $this->getDbo();
        if($table->published == 1 && intval($table->publish_up) == 0) {
            $table->publish_up = JFactory::getDate()->toSql(); // True to return the date string in the local time zone, false to return it in GMT.
        }
        
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
        } else {
            $table->password_md5 = '';
        }
        
        if (!$table->language){
            $table->language = '*';            
        }
                
        // Set the default values for new created download
        if (empty($table->id)) {
            // Reorder the downloads within the category so the new download is first
            $table->reorder('catid = '.(int) $table->catid.' AND published >= 0');

            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db->setQuery('SELECT MAX(ordering) FROM #__jdownloads_files');
                $max = $db->loadResult();
                $table->ordering = $max+1;
            }
        }
    }
    
    /**
     * Method to save the form data.
     *
     * @param    array    $data     The form data.
     * @param    boolean  $auto     true when the data comes from auto monitoring
     * @param    boolean  $import   true when the data comes from 1.9.x import process   
     * @return    boolean    True on success.
     */
    public function save($data, $auto = false, $import = false, $restore_in_progress = false)
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        $jinput  = JFactory::getApplication()->input;
        $filter = JFilterInput::getInstance();
        
        // Initialise variables;
        $dispatcher = JDispatcher::getInstance();
        $table      = $this->getTable();
        $context    = $this->option . '.' . $this->name;
        
        $key        = $table->getKeyName();
        $pk         = (!empty($data['id'])) ? (int)$data['id'] : (int)$this->getState($this->getName().'.id');
        $isNew      = true;
        
        // Include the plugins for the save events.
        \JPluginHelper::importPlugin($this->events_map['save']);

        $result = false;
        
        // Cast catid to integer for comparison
        /*  $catid = (int) $data['catid'];
        
        // Check if New Category exists
        if ($catid > 0){
            $catid = CategoriesHelper::validateCategoryId($data['catid'], 'com_jdownloads');
        }
        
        // Save New Category
        if ($catid == 0 && $this->canCreateCategory()){
            $table = array();
            $table['title'] = $data['catid'];
            $table['parent_id'] = 1;
            $table['language'] = $data['language'];
            $table['published'] = 1;
            
            // Create new category and get catid back
            $data['catid'] = CategoriesHelper::createCategory($table);
        }  */
        
        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');
        
        // Load the row if saving an existing download. Not when auto monitoring is activated (also use for import from old version)
        if ($pk > 0 && !$auto || ($pk > 0 && $restore_in_progress) ) {
            $table->load($pk);
            $isNew = false;
        }

        // Alter the title for save as copy
        if ($jinput->get('task') == 'save2copy') {
            list($title,$alias) = $table->buildNewTitle($data['alias'], $data['title']);
            $data['title']    = $title;
            $data['alias']    = $alias;
        }
        
        if (!isset($data['rules'])){
            $data['rules'] = array(
                'core.create' => array(),
                'core.delete' => array(),
                'core.edit' => array(),
                'core.edit.state' => array(),
                'core.edit.own' => array(),
                'download' => array(),
            ); 
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
        
        // Check the data and check the selected files and handle it.
        if (!$table->checkData($isNew, $auto)) {
            $this->setError($table->getError());
            return false;
        }
        
        // Trigger the onContentBeforeSave event.
        $result = $dispatcher->trigger($this->event_before_save, array($context, $table, $isNew, $data));
        if (in_array(false, $result, true)) {
            $this->setError($table->getError());
            return false;
        }

        // Store the data.
        if ($import === true){
            // set off this 
            $table->set('_autoincrement',false);
        }
        
        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        } else {
            // folder handling functionality
            if (!$auto){
                // Update only the log table when we have a new download creation in frontend
                $app  = JFactory::getApplication();
                if ($app->isClient('site') && $isNew){
                    $upload_data           = new stdClass();
                    $upload_data->id       = $table->id;
                    $upload_data->url_download  = $table->url_download;
                    $upload_data->title    = $table->title;
                    $upload_data->size          = $table->size;
                    JDHelper::updateLog($type = 2, '', $upload_data);
                    
                    // send e-mail after new download creation in frontend
                    if ($params->get('send_mailto_option_upload') == '1'){
                        JDHelper::sendMailUpload($table);               
                    }
                }
            }    
        }    

        // Trigger the onContentAfterSave event.
        // Also Custom fields are saved here!!!
        $dispatcher->trigger($this->event_after_save, array($context, $table, $isNew, $data));
       
        $this->setState($this->getName().'.id', $table->id);
        
        $this->setState($this->getName() . '.new', $isNew);
        
        // We need the 'association job' only when we are not in the monitoring routine 
        if (!$auto){
            
            if ($this->associationsContext && \JLanguageAssociations::isEnabled() && !empty($data['associations'])){
                $associations = $data['associations'];

                // Unset any invalid associations
                $associations = ArrayHelper::toInteger($associations);

                // Unset any invalid associations
                foreach ($associations as $tag => $id){
                    if (!$id){
                        unset($associations[$tag]);
                    }
                }

                // Show a warning if the item isn't assigned to a language but we have associations.
                if ($associations && $table->language === '*'){
                    \JFactory::getApplication()->enqueueMessage(
                        \JText::_(strtoupper($this->option) . '_ERROR_ALL_LANGUAGE_ASSOCIATED'),
                        'warning'
                    );
                }

                // Get associationskey for edited item
                $db    = $this->getDbo();
                $query = $db->getQuery(true)
                    ->select($db->qn('key'))
                    ->from($db->qn('#__associations'))
                    ->where($db->qn('context') . ' = ' . $db->quote($this->associationsContext))
                    ->where($db->qn('id') . ' = ' . (int) $table->$key);
                $db->setQuery($query);
                $old_key = $db->loadResult();

                // Deleting old associations for the associated items
                $query = $db->getQuery(true)
                    ->delete($db->qn('#__associations'))
                    ->where($db->qn('context') . ' = ' . $db->quote($this->associationsContext));

                if ($associations){
                    $query->where('(' . $db->qn('id') . ' IN (' . implode(',', $associations) . ') OR '
                        . $db->qn('key') . ' = ' . $db->q($old_key) . ')');
                } else {
                    $query->where($db->qn('key') . ' = ' . $db->q($old_key));
                }

                $db->setQuery($query);
                $db->execute();

                // Adding self to the association
                if ($table->language !== '*'){
                    $associations[$table->language] = (int) $table->$key;
                }

                if (count($associations) > 1){
                    // Adding new association for these items
                    $key   = md5(json_encode($associations));
                    $query = $db->getQuery(true)
                        ->insert('#__associations');

                    foreach ($associations as $id){
                        $query->values(((int) $id) . ',' . $db->quote($this->associationsContext) . ',' . $db->quote($key));
                    }

                    $db->setQuery($query);
                    $db->execute();
                }
            }
        }
        

        return true;
    }
    
     /**
     * Method to perform batch operations on an item or a set of items.
     *
     * @param   array  $commands  An array of commands to perform.
     * @param   array  $pks       An array of item ids.
     * @param   array  $contexts  An array of item contexts.
     *
     * @return  boolean  Returns true on success, false on failure.
     *
     */
    public function batch($commands, $pks, $contexts)
    {
        // Sanitize ids.
        $pks = array_unique($pks);
        $pks = ArrayHelper::toInteger($pks);
        
        $this->batch_commands = array_merge($this->batch_commands, $this->jd_batch_commands);

        // Remove any values of zero.
        if (array_search(0, $pks, true))
        {
            unset($pks[array_search(0, $pks, true)]);
        }

        if (empty($pks))
        {
            $this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));

            return false;
        }

        $done = false;

        // Set some needed variables.
        $this->user = JFactory::getUser();
        $this->table = $this->getTable();
        $this->tableClassName = get_class($this->table);
        $this->contentType = new JUcmType;
        $this->type = $this->contentType->getTypeByTable($this->tableClassName);
        $this->batchSet = true;

        if ($this->type == false)
        {
            $type = new JUcmType;
            $this->type = $type->getTypeByAlias($this->typeAlias);
        }

        $this->tagsObserver = $this->table->getObserverOfClass('JTableObserverTags');

        if ($this->batch_copymove && !empty($commands[$this->batch_copymove]))
        {
            $cmd = ArrayHelper::getValue($commands, 'move_copy', 'c');

            if ($cmd == 'c' || $cmd == 'cc' || $cmd == 'ca')
            {
                if ($cmd == 'cc'){ 
                    $copy_with_file = true;
                } else {
                    $copy_with_file = false;
                }
                
                if ($cmd == 'ca'){ 
                    $copy_and_assign_file = true;
                } else {
                    $copy_and_assign_file = false;
                }
                
                $result = $this->batchCopy($commands[$this->batch_copymove], $pks, $contexts, $copy_with_file, $copy_and_assign_file);

                if (is_array($result))
                {
                    foreach ($result as $old => $new)
                    {
                        $contexts[$new] = $contexts[$old];
                    }
                    $pks = array_values($result);
                }
                else
                {
                    return false;
                }
            }
            elseif ($cmd === 'm' && !$this->batchMove($commands[$this->batch_copymove], $pks, $contexts))
            {
                return false;
            }

            $done = true;
        }

        foreach ($this->batch_commands as $identifier => $command)
        {
            if (!empty($commands[$identifier]))
            {
                if (!$this->$command($commands[$identifier], $pks, $contexts))
                {
                    return false;
                }

                $done = true;
            }
        }

        if (!$done)
        {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));

            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }    
    
    
     /**
     * Batch copy downloads to a new category
     *
     * @param   integer  $value     The new category.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  mixed  An array of new IDs on success, boolean false on failure.
     *
     */
    protected function batchCopy($value, $pks, $contexts, $copy_with_file = false, $copy_and_assign_file = false)
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        $categoryId = (int) $value;

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
            $result = JDownloadsHelper::changeParamSetting('downloads_batch_in_progress', '1');
        }
        
        // Check that the target category exists
        if ($categoryId){
            $targetCategoryTable = JTable::getInstance('category', 'jdownloadsTable');
            if (!$targetCategoryTable->load($categoryId)){
                if ($error = $targetCategoryTable->getError()){
                    // Fatal error
                    $this->setError($error);
                    return false;
                } else {
                    $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
                    return false;
                }
            }
            
            // Build the file path from the Downloads target category when required
            if ($copy_with_file){
                if ($targetCategoryTable->cat_dir_parent != ''){
                    $target_path = $targetCategoryTable->cat_dir_parent.'/'.$targetCategoryTable->cat_dir;
                } else {
                    $target_path = $targetCategoryTable->cat_dir;
                }
            } else {
                $target_path = '';
            }
        }

        if (empty($categoryId)){
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
            return false;
        }
        
        // Check that the user has create permission for the component
        $extension = JFactory::getApplication()->input->get('option', '');
        $user = JFactory::getUser();
        
        if (!$user->authorise('core.create', $extension . '.category.' . $categoryId)){
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));
            return false;
        }
        
        $newIds = array();

        // Parent exists so we let's proceed
        while (!empty($pks))
        {
            // Pop the first ID off the stack
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
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }
            
            // It is required to copy also the assigned file?
            $source_path = '';
            
            if ($copy_with_file){
                if ($categoryId > 1 && $table->url_download != ''){
                    $copy_filename       = $table->url_download;
                    $source_cat_id       = $table->catid;
                    $target_cat_id       = $categoryId;
                    
                    // We must only copy when we have a different cat id.
                    if ($source_cat_id != $target_cat_id){
                        $must_copy_file = true;
             
                        // Get the data from the source category
                        $sourceCategoryTable = JTable::getInstance('category', 'jdownloadsTable');
                        if (!$sourceCategoryTable->load($source_cat_id)){
                            if ($error = $sourceCategoryTable->getError()){
                                // Fatal error
                                $this->setError($error);
                                return false;
                            } else {
                                // Category not found
                                $this->setError(JText::_('COM_JDOWNLOADS_BATCH_MOVE_ROW_NOT_FOUND'));
                                return false;
                            }
                        }
                        
                        // Build the file path from the Downloads source category
                        if ($sourceCategoryTable->cat_dir_parent != ''){
                            $source_path = $sourceCategoryTable->cat_dir_parent.'/'.$sourceCategoryTable->cat_dir.'/'.$copy_filename;
                        } else {
                            $source_path = $sourceCategoryTable->cat_dir.'/'.$copy_filename;
                        }
                        
                        if ($source_path && $target_path){
                            if (!JFile::exists($params->get('files_uploaddir') . '/'. $source_path)){
                                $this->setError(JText::sprintf('COM_JDOWNLOADS_BATCH_CANNOT_FIND_COPY_FILE', $source_path));
                            }
                            
                            if (!JFile::copy($params->get('files_uploaddir') . '/' . $source_path, $params->get('files_uploaddir') . '/' . $target_path.'/'.$copy_filename)){
                                $this->setError(JText::sprintf('COM_JDOWNLOADS_BATCH_CANNOT_COPY_FILES', $source_path));
                            }
                        }
                    }    
                }    
            }
            
            
            // Build a new title and alias
            list($title,$alias) = $table->buildNewTitle($table->alias, $table->title);
            $table->title    = $title;
            $table->alias    = $alias;

            if ($copy_and_assign_file){
                $table->other_file_id = (int)$table->id;
            }

            // Reset the ID because we are making a copy
            $table->id = 0;

            // New category ID
            $table->catid = $categoryId;
            
            // set correct new ordering
            $table->ordering = $this->getNewOrdering($categoryId); 
            
            $table->views = 0;
            $table->downloads = 0;
            $table->modified_by = 0;            
            $table->modified = '0000-00-00 00:00:00';
            if (!$copy_with_file || $copy_and_assign_file){
                $table->url_download = '';
            }
            
            // Check the row.
            if (!$table->check(true)){
                $this->setError($table->getError());
                return false;
            }
            
            if (!empty($this->type)){
                $this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
            }

            // Store the row.
            if (!$table->store()){
                $this->setError($table->getError());
                return false;
            }
            
            // Get the new item ID
            $newId = $table->get('id');
            
            $this->cleanupPostBatchCopy($this->table, $newId, $pk);

            // Add the new ID to the array
            $newIds[$pk] = $newId;
            
        }

        // Clean the cache
        $this->cleanCache();

        // actualize at last the batch progress setting 
        $result = JDownloadsHelper::changeParamSetting('downloads_batch_in_progress', '0');          
        
        return $newIds;
    } 
    
     /**
     * Function that can be overriden to do any data cleanup after batch copying data
     *
     * @param   \JTableInterface  $table  The table object containing the newly created item
     * @param   integer           $newId  The id of the new item
     * @param   integer           $oldId  The original item id
     *
     * @return  void
     *
     */
    protected function cleanupPostBatchCopy(\JTableInterface $table, $newId, $oldId)
    {

        // Register FieldsHelper
        JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

        $oldItem = $this->getTable();
        $oldItem->load($oldId);
        $fields = FieldsHelper::getFields('com_jdownloads.download', $oldItem, true);

        $fieldsData = array();

        if (!empty($fields))
        {
            $fieldsData['com_fields'] = array();

            foreach ($fields as $field)
            {
                $fieldsData['com_fields'][$field->name] = $field->rawvalue;
            }
        }

        JEventDispatcher::getInstance()->trigger('onContentAfterSave', array('com_jdownloads.download', &$this->table, true, $fieldsData));        
        
    } 
    
     /**
     * Batch move downloads to a new category
     *
     * @param   integer  $value  The new category ID.
     * @param   array    $pks    An array of row IDs.
     *
     * @return  booelan  True if successful, false otherwise and internal error is set.
     *
     */
    protected function batchMove($value, $pks, $contexts)
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        $this->typeAlias = 'com_jdownloads.download';
        
        $new_category_id    = (int) $value;

        $table = $this->getTable();
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $jinput = JFactory::getApplication()->input;
        
        // Check at first, that it is not always run another batch job
        if ($params->get('categories_batch_in_progress') || $params->get('downloads_batch_in_progress')){
            // Generate the warning and return
            JError::raiseWarning(100, JText::_('COM_JDOWNLOADS_BATCH_IS_ALWAYS_STARTED')); 
            return false;
        } else {
            // Actualize at first the batch progress setting 
            $result = JDownloadsHelper::changeParamSetting('downloads_batch_in_progress', '1');        
        }        

        // Check that the category exists
        if ($new_category_id) {
            $newCategoryTable = JTable::getInstance('category', 'jdownloadsTable');
            if (!$newCategoryTable->load($new_category_id)) {
                if ($error = $newCategoryTable->getError()) {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else {
                    $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
                    return false;
                }
            }
        }

        if (empty($new_category_id)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
            return false;
        }

        // Check that user has create and edit permission for the component
        $extension   = $jinput->get('option');
        $user        = JFactory::getUser();
        if (!$user->authorise('core.create', $extension)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'));
            return false;
        }

        if (!$user->authorise('core.edit', $extension)) {
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
            return false;
        }

        // Parent exists so we let's proceed
        foreach ($pks as $pk)
        {
            // Check that the Download actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else {
                    // Selected Download not found
                    $this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }
            
            $oldCategoryTable = JTable::getInstance('category', 'jdownloadsTable');
            
            if (!$oldCategoryTable->load($table->catid)) {
                if ($error = $oldCategoryTable->getError()) {
                    // Fatal error
                    $this->setError($error);
                    return false;
                }
                else {
                    $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_MOVE_CATEGORY_NOT_FOUND'));
                    return false;
                }
            }
            
            if ($oldCategoryTable->cat_dir_parent){
                $source_folder = $oldCategoryTable->cat_dir_parent . '/' . $oldCategoryTable->cat_dir;
            } else {
                $source_folder = $oldCategoryTable->cat_dir;
            }
            $source_path = $params->get('files_uploaddir') . '/' . $source_folder . '/' . $table->url_download;

            // Set the new category ID
            $table->catid = $new_category_id;
            
            // Move the file to the new category
            if ($table->url_download){
                if ($newCategoryTable->cat_dir_parent){
                    $target_folder = $newCategoryTable->cat_dir_parent . '/' . $newCategoryTable->cat_dir;
                } else {
                    $target_folder = $newCategoryTable->cat_dir;
                }
                $target_path = $params->get('files_uploaddir') . '/' . $target_folder . '/' . $table->url_download;
                
                $result = JFile::move($source_path , $target_path);
            }

            // Check the row.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }
            
            if (!empty($this->type)){
                $this->createTagsHelper($this->tagsObserver, $this->type, $pk, $this->typeAlias, $this->table);
            }

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        // Actualize at last the batch progress setting 
        $result = JDownloadsHelper::changeParamSetting('downloads_batch_in_progress', '0');                      
        
        return true;
    }
    
    /**
     * Method implementing the batch setting of price values
     */
    protected function batchPrice($value, $pks, $contexts)
    {
        $app = JFactory::getApplication();

        if (isset($value) && ($value != ''))
        {
            if (empty($this->batchSet))
            {
                // Set some needed variables.
                $this->user = JFactory::getUser();
                $this->table = $this->getTable();
                $this->tableClassName = get_class($this->table);
                $this->contentType = new JUcmType;
                $this->type = $this->contentType->getTypeByTable($this->tableClassName);
            }

            foreach ($pks as $pk)
            {
                if ($this->user->authorise('core.edit', $contexts[$pk]))
                {
                    $this->table->reset();
                    $this->table->load($pk);

                    if (isset($value))
                    {
                        $price = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                        $this->table->price = $price;
                    }

                    if (!$this->table->store())
                    {
                        $this->setError($this->table->getError());
                        return false;
                    }
                }
                else
                {
                    $this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
                    return false;
                }
            }
        }
        return true;
    }
    
    // compute the new ordering for batch copy
    public function getNewOrdering($categoryId) 
    {
        $ordering = 1;
        $this->_db->setQuery('SELECT MAX(ordering) FROM #__jdownloads_files WHERE catid='.(int)$categoryId);
        $max = $this->_db->loadResult();
        $ordering = $max + 1;
        return $ordering;
    }
    
    
    /**
    * Method to create a new download 
    * 
    * @param mixed $name
    * @param mixed $catid
    * @param mixed $note
    * @param mixed $description
    * @return JCategoryNode
    */

    public function createDownload( $name, $catid = 1, $note, $description, $filename, $filesize = 0 )
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_jdownloads/tables' );

        $download_model = JModelLegacy::getInstance( 'Download', 'jdownloadsModel' );

        $data = array (
            'id' => 0,
            'catid' => $catid,
            'title' => $name,
            'alias' => '',
            'notes' => $note,
            'url_download' => $filename,
            'size' => $filesize,
            'description' => $description,
            'file_pic' => $params->get('file_pic_default_filename'),
            'published' => '1',
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

        if( !$download_model->save( $data ) )
        {
            return NULL;
        }
        
        $download_id = $download_model->getState('download.id');
        $download = self::getItem($download_id);
        
        return $download;
    }                      

    /**
    * Method to create a new download from auto monitoring
    * 
    * @param array       $data
    *        boolean     $import    switch which is set true when import process is run   
    * @return boolean
    */

    public function createAutoDownload( $data, $import = false )
    {
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR . '/components/com_jdownloads/tables' );

        $download_model = JModelLegacy::getInstance( 'Download', 'jdownloadsModel' );

        if( !$download_model->save( $data, true, $import ) )
        {
            return false;
        }
        
        return true;
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
            $item->tags->getTagIds($item->id, 'com_jdownloads.download');         
        } 
        
        // Added to support the Joomla Language Associations
        // Load associated Download items
        $assoc = JLanguageAssociations::isEnabled();

        if ($assoc)
        {
            $item->associations = array();

            if ($item->id != null)
            {
                $associations = JLanguageAssociations::getAssociations('com_jdownloads', '#__jdownloads_files', 'com_jdownloads.item', $item->id, 'id', 'alias', '');    

                foreach ($associations as $tag => $association)
                {
                    $item->associations[$tag] = $association->id;
                }
            }
        }       

        return $item;
    }                           
    
    /**
     * Allows preprocessing of the JForm object.
     *
     * @param   JForm   $form   The form object
     * @param   array   $data   The data to be merged into the form object
     * @param   string  $group  The plugin group to be executed
     *
     * @return  void
     *
     * @since   3.0
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        if ($this->canCreateCategory())
        {
            $form->setFieldAttribute('catid', 'allowAdd', 'true');
        }

        // Association content items
        if (JLanguageAssociations::isEnabled())
        {
            $languages = JLanguageHelper::getContentLanguages(false, true, null, 'ordering', 'asc');

            if (count($languages) > 1)
            {
                $addform = new SimpleXMLElement('<form />');
                $fields = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');

                foreach ($languages as $language)
                {
                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $language->lang_code);
                    $field->addAttribute('type', 'modal_download');
                    $field->addAttribute('language', $language->lang_code);
                    $field->addAttribute('label', $language->title);
                    $field->addAttribute('translate_label', 'false');
                    $field->addAttribute('select', 'true');
                    $field->addAttribute('new', 'true');
                    $field->addAttribute('edit', 'true');
                    $field->addAttribute('clear', 'true');
                    $field->addAttribute('propagate', 'true');
                }

                $form->load($addform, false);
            }
        }

        parent::preprocessForm($form, $data, $group);
    }
    
    /**
     * Is the user allowed to create an on the fly category?
     *
     * @return  boolean
     *
     * @since   3.6.1
     */
    private function canCreateCategory()
    {
        return JFactory::getUser()->authorise('core.create', 'com_jdownloads');
    }
    
    
    /**
     * Method to remove a download and his file, preview file and images
     *
     * @access    public
     * @return    boolean    True on success
     */
    public function delete(&$pks = array())
    {
        $params = JComponentHelper::getParams('com_jdownloads');
        
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        
        // Initialise variables.
        $dispatcher = JDispatcher::getInstance();
        $pks = (array) $pks;
        $table = $this->getTable('download');
        
        $app       = JFactory::getApplication();
        $db        = JFactory::getDbo();
        
        $jinput = JFactory::getApplication()->input;
        
        $total     = count($pks);
        $query     = '';
        $cids = implode( ',', $pks );
        $del_error = false;
        $del_image_error = false;
        
        $pics_folder   = JPATH_SITE.'/images/jdownloads/screenshots/';
        $thumbs_folder = JPATH_SITE.'/images/jdownloads/screenshots/thumbnails/';
        
        $preview_folder = $params->get('files_uploaddir').DS.$params->get('preview_files_folder_name').DS;        
        
        // get selected option value to delete also the file
        $file_delete = $jinput->get('delete_file_option', 1, 'integer');
        
        // Include the content plugins for the on delete events.
        JPluginHelper::importPlugin('content');
        
        $can_delete = false;
        
        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk)
        {

            if ($table->load($pk))
            {
                if ($app->isClient('administrator')){
                    $can_delete = $this->canDelete($table);
                }    
                if ($app->isClient('site') || $can_delete)
                {

                    $context = $this->option . '.' . $this->name;

                    // Trigger the onContentBeforeDelete event.
                    $result = $dispatcher->trigger($this->event_before_delete, array($context, $table));
                    if (in_array(false, $result, true))
                    {
                        $this->setError($table->getError());
                        return false;
                    }
                    
                    // check file delete option - delete it at first when selected
                    if ($file_delete == 1){            
                          // only when no extern links are used
                          if ($table->url_download <> ''){
                              
                              // Check at first which other Downloads has still assigned to this Download file (use file from other Download option)
                              $db->setQuery("SELECT id, title FROM #__jdownloads_files WHERE other_file_id = '$table->id'");
                              $still_assigned_files = $db->loadObjectList();
                              if (count($still_assigned_files)){
                                  $msg = JText::sprintf('COM_JDOWNLOADS_BE_NO_DEL_FILES_ASSIGNED_ITEMS_EXISTS', $table->title);
                                  $msg .= '<ul>'; 
                                  foreach ($still_assigned_files as $assigned){
                                      $msg .= '<li>ID: <b>'.$assigned->id. '</b> Title: <b>'.$assigned->title.'</b></li>';
                                  }
                                  $msg .= '</ul>'; 
                                  JError::raiseWarning(100, $msg);
                                  return false;
                              }
                              
                              $db->setQuery("SELECT cat_dir, cat_dir_parent FROM #__jdownloads_categories WHERE id = '$table->catid'");
                              $cat_dirs = $db->loadObject();
                              if ($cat_dirs->cat_dir_parent != ''){
                                  $cat_dir = $cat_dirs->cat_dir_parent.'/'.$cat_dirs->cat_dir;
                              } else {
                                  $cat_dir = $cat_dirs->cat_dir;
                              }

                              if ($cat_dir && @file_exists($params->get('files_uploaddir').'/'.$cat_dir.'/'.$table->url_download)){
                                  // delete the file now
                                  if (!JFile::delete($params->get('files_uploaddir').'/'.$cat_dir.'/'.$table->url_download)) {
                                      $del_error = JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_DEL_FILES_ERROR');
                                  } 
                              } else {
                                    $del_error = JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_DEL_FILES_ERROR');                                
                              }   
                          }

                    }                   

                    // Delete also the assigned images from this download, when this option is activated in config
                    if ($params->get('delete_also_images_from_downloads') == 1){
                          if ($table->images) {
                              $pics = explode('|', $table->images);
                              foreach ($pics as $pic){
                                  if (!JFile::delete($pics_folder.$pic)) {
                                      $del_image_error = JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_DEL_IMAGES_ERROR');
                                  }    
                                  if (!JFile::delete($thumbs_folder.$pic)) {
                                      $del_image_error = JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_DEL_IMAGES_ERROR');
                                  }    
                              }
                          }
                    }                      
                      
                    // Delete also the assigned preview file from this download, when this option is activated in config
                    if ($params->get('delete_also_preview_files_from_downloads') == 1){
                          if ($table->preview_filename) {
                                  if (!JFile::delete($preview_folder.$table->preview_filename)) {
                                      $del_image_error = JText::_('COM_JDOWNLOADS_BACKEND_FILESEDIT_DEL_IMAGES_ERROR');
                                  }    
                          }
                    }                   
                    
                    if ($file_delete == 1 && $del_error){
                        // create error message
                        JError::raiseWarning(100, $del_error);
                    }
                    
                    if ($del_image_error){
                        // create error message
                        JError::raiseWarning(100, $del_image_error);
                    }                          
                    
                    // delete now the row in table
                    if (!$table->delete($pk))
                    {
                        $this->setError($table->getError());
                        return false;
                    }

                    // Trigger the onContentAfterDelete event.
                    $dispatcher->trigger($this->event_after_delete, array($context, $table));

                }
                else
                {

                    // Prune items that you can't change.
                    unset($pks[$i]);
                    $error = $this->getError();
                    if ($error)
                    {
                        JError::raiseWarning(500, $error);
                        return false;
                    }
                    else
                    {
                        JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
                        return false;
                    }
                }

            }
            else
            {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;      
    } 
    
    /**
     * Method to toggle the featured setting of Downloads.
     *
     * @param   array    $pks    The ids of the items to toggle.
     * @param   integer  $value  The value to toggle to.
     *
     * @return  boolean  True on success.
     */
    public function featured($pks, $value = 0)
    {
        // Sanitize the ids.
        $pks = (array) $pks;
        ArrayHelper::toInteger($pks);

        if (empty($pks)){
            $this->setError(JText::_('COM_JDOWNLOADS_NO_ITEM_SELECTED'));
            return false;
        }

        try
        {
            $db = $this->getDbo();
            $query = $db->getQuery(true)
                        ->update($db->quoteName('#__jdownloads_files'))
                        ->set('featured = ' . (int) $value)
                        ->where('id IN (' . implode(',', $pks) . ')');
            $db->setQuery($query);
            $db->execute();
        }
        catch (Exception $e)
        {
            $this->setError($e->getMessage());
            return false;
        }
        $this->cleanCache();
        return true;
    }
    
}
?>