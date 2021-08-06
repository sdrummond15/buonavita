<?php


defined('_JEXEC') or die();

jimport('joomla.application.component.modellist'); 

class jdownloadsModeltemplates extends JModelList
{
    
     /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see      JController
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'template_name', 'a.template_name',
                'template_typ', 'a.template_typ',
                'template_header_text', 'a.template_header_text',
                'template_subheader_text', 'a.template_subheader_text',
                'template_footer_text', 'a.template_footer_text',
                'template_before_text', 'a.template_before_text',
                'template_text', 'a.template_text',
                'template_after_text', 'a.template_after_text',
                'template_active', 'a.template_active',
                'locked', 'a.locked',
                'note', 'a.note',
                'cols', 'a.cols',
                'uses_bootstrap', 'a.uses_bootstrap',
                'uses_w3css', 'a.uses_w3css',
                'checkbox_off', 'a.checkbos_off',
                'symbol_off', 'a.symbol_off',
                'use_to_view_subcats', 'a.use_to_view_subcats',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'ordering', 'a.ordering',
                'preview_id', 'a.preview_id'
            );
        }

        parent::__construct($config);
    }


/**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     */
    protected function populateState($ordering ='a.template_name', $direction = 'asc')
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        
        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        // Load the parameters.
        $params = JComponentHelper::getParams('com_jdownloads');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);        
        
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param    string        $id    A prefix for the store id.
     * @return    string        A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id.= ':' . $this->getState('filter.search');
        $id.= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return    JDatabaseQuery
     * @since    1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db        = $this->getDbo();
        $query     = $db->getQuery(true);
        $user      = JFactory::getUser();
        
        $jinput    = JFactory::getApplication()->input;
        $jd_tmpl_type = $jinput->get('type', '0', 'integer');
        
        if (!$jd_tmpl_type){
            $session = JFactory::getSession();
            $jd_tmpl_type  = (int) $session->get( 'jd_tmpl_type', '' );  
        } else {
            $session = JFactory::getSession();
            $session->set( 'jd_tmpl_type', $jd_tmpl_type );  
        }
        
        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.template_name, a.template_typ, a.template_header_text, a.template_subheader_text, a.template_footer_text, a.template_before_text, a.template_text, a.template_after_text, a.template_active, '  .
                'a.locked, a.note, a.cols, a.uses_bootstrap, a.uses_w3css, a.checkbox_off, a.symbol_off, a.use_to_view_subcats, a.checked_out, a.checked_out_time, a.preview_id'
            )
        );
        $query->from('`#__jdownloads_templates` AS a');
        
        $query->where('(a.template_typ = '.$jd_tmpl_type.')');
        
        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = '.(int) substr($search, 3));
            } else {
                $search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('(a.template_name LIKE '.$search.' OR a.note LIKE '.$search.')');
            }
        }                                                   

        // Add the list ordering clause.
        $orderCol    = $this->state->get('list.ordering', 'a.template_name');
        $orderDirn    = $this->state->get('list.direction', 'asc');
        
        $query->order($db->escape($orderCol.' '.$orderDirn));
        return $query;
    }
    
    /* Method to checkin a layout
     *
     * @access    public
     * @return    boolean    True on success
     */
    public function checkin($id)
    {
        $app       = JFactory::getApplication();
        $db        = $this->getDbo();
        $query     = $db->getQuery(true);
        $nullDate  = $db->getNullDate();
        $id = join(",", $id);
           
        $query = $db->getQuery(true)
                ->update($db->quoteName('#__jdownloads_templates'))
                ->set('checked_out = 0')
                ->set('checked_out_time = '.$db->Quote($nullDate))
                ->where('id IN ('.$id.')');

        $db->setQuery($query);
        if ($db->execute())
        {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string  $type    The table type to instantiate
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable    A database object
     *
     */
    public function getTable($type = 'Template', $prefix = 'JDownloadsTable', $config = array())
    {
        $return = JTable::getInstance($type, $prefix, $config);

        return $return;
    }
    
    /**
     * Method to remove a layout
     *
     * @param   array  &$pks  An array of item ids.
     *
     * @return  boolean  Returns true on success, false on failure.
     *
     */
    public function delete(&$pks)
    {
        // Typecast variable.
        $pks    = (array) $pks;
        $pks_list = implode(',', $pks);

        $error_msg = '';
        
        $app       = JFactory::getApplication();
        $db        = $this->getDbo();

        $user   = JFactory::getUser();
        $groups = JAccess::getGroupsByUser($user->get('id'));

        // Get a row instance.
        $table = $this->getTable();

        $dispatcher = JEventDispatcher::getInstance();
        
        // Do not allow to delete 'default' or active templates
        $db->setQuery('SELECT * FROM #__jdownloads_templates WHERE id IN ('.$pks_list.')');
        $rows = $db->loadObjectList();
        
        foreach ($rows as $row){
                // Changed in 3.9.7 to allow also to remove double default layouts
                /* if ($row->locked == '1') {
                    // a default template can not be erased!
                JError::raiseWarning(403, JText::_('COM_JDOWNLOADS_BACKEND_TEMPLIST_ERROR_IS_LOCKED'));
                return false;
                } */
                
                if ($row->template_active == '1') {
                    // an active template can not be erased!
                JError::raiseWarning(403, JText::_('COM_JDOWNLOADS_BACKEND_TEMPLIST_ERROR_IS_ACTIVE'));
                return false;
                }
            }

        // Iterate the items to delete each one
        foreach ($pks as $i => $pk)
        {
            if ($table->load($pk))
            {
                // Access checks.
                $allow = $user->authorise('core.delete', 'com_jdownloads');

                if ($allow)
                {
                    // Fire the before delete event.
                    $dispatcher->trigger('onContentBeforeDelete', array('com_jdownloads.template', $table, false));
                    //$dispatcher->trigger($this->event_before_delete, array($table->getProperties()));

                    if (!$table->delete($pk))
                    {
                        $this->setError($table->getError());
                        return false;
                    }
                    else
                    {
                        // Trigger the after delete event.
                        $dispatcher->trigger('onContentAfterDelete', array('com_jdownloads.template', $table, false));
                        //$dispatcher->trigger($this->event_after_delete, array($table->getProperties(), true, $this->getError()));
                    }
                }
                else
                {
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    JError::raiseWarning(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
                }
            }
                                
            else
            {
                $this->setError($table->getError());
                return false;
            }
        }

        return true;
    } 

    /**
     * Method to activate a layout
     *
     * @access    public
     * @return    boolean    True on success
     */
    public function activate($jd_tmpl_type = 0)
    {
        $app       = JFactory::getApplication();
        $jinput    = JFactory::getApplication()->input;
        $db        = $this->getDbo();
        $query     = $db->getQuery();
        
        $cid 	   = $jinput->get('cid', array(), 'array');
        $total     = count($cid);
        
        if ($total > 1) {
            echo "<script> alert('".JText::_('COM_JDOWNLOADS_BACKEND_TEMPLATE_ACTIVE_ERROR')."'); window.history.go(-1); </script>\n";
            exit();
        }
        
        if (count( $cid )){
            // first, deactivate the old layout
            $query = 'UPDATE #__jdownloads_templates'
                    . ' SET template_active = 0'
                    . ' WHERE template_typ = '.$jd_tmpl_type
                    . ' AND template_active = 1'
                    ;
            $this->_db->setQuery( $query );
            if(!$this->_db->execute()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }

            // activate the selected            
            $query = 'UPDATE #__jdownloads_templates'
                    . ' SET template_active = 1'
					. ' WHERE id = '.(int)$cid[0]
                    ;
            $this->_db->setQuery( $query );
            if(!$this->_db->execute()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }
        return true;
    }
}
?>