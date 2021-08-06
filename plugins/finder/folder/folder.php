<?php
/**
 * @package     Joomla.Plugin
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @extension jDownloads 
 * @copyright (C) Arno Betz - www.jdownloads.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('FinderIndexerAdapter', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php');

/**
 * Smart Search adapter for jDownloads Categories.
 *
 */
class PlgFinderFolder extends FinderIndexerAdapter
{

	protected $context      = 'Folder';
	protected $extension    = 'com_jdownloads';
	protected $layout       = 'category';
	protected $type_title   = 'Download Category';
	protected $table        = '#__jdownloads_categories';
    protected $state_field  = 'published';
	protected $autoloadLanguage = true;

	/**
	 * Method to remove the link information for items that have been deleted.
	 *
	 * @param   string  $context  The context of the action being performed.
	 * @param   JTable  $table    A JTable object containing the record to be deleted
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception on database error.
	 */
	public function onFinderDelete($context, $table)
	{
		if ($context === 'com_jdownloads.category'){
			$id = $table->id;
		} elseif ($context === 'com_finder.index'){
			$id = $table->link_id;
		} else {
			return true;
		}

		// Remove item from the index.
		return $this->remove($id);
	}

	/**
	 * Smart Search after save content method.
	 * Reindexes the link information for a category that has been saved.
	 * It also makes adjustments if the access level of the category has changed.
	 *
	 * @param   string   $context  The context of the category passed to the plugin.
	 * @param   JTable   $row      A JTable object.
	 * @param   boolean  $isNew    True if the category has just been created.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception on database error.
	 */
	public function onFinderAfterSave($context, $row, $isNew)
	{
		// We only want to handle categories here.
		if ($context === 'com_jdownloads.category'){
			// Check if the access levels are different.
			if (!$isNew && $this->old_access != $row->access){
				// Process the change.
				$this->itemAccessChange($row);
			}

			// Reindex the category item.
			$this->reindex($row->id);

			// Check if the parent access level is different.
			if (!$isNew && $this->old_cataccess != $row->access){
				$this->categoryAccessChangeJD($row);
			}
		}

		return true;
	}

	/**
	 * Smart Search before content save method.
	 * This event is fired before the data is actually saved.
	 *
	 * @param   string   $context  The context of the category passed to the plugin.
	 * @param   JTable   $row      A JTable object.
	 * @param   boolean  $isNew    True if the category is just about to be created.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception on database error.
	 */
	public function onFinderBeforeSave($context, $row, $isNew)
	{
		// We only want to handle categories here.
		if ($context === 'com_jdownloads.category'){
			// Query the database for the old access level and the parent if the item isn't new.
			if (!$isNew){
				$this->checkItemAccess($row);
				$this->checkCategoryAccessJD($row);
			}
		}

		return true;
	}

	/**
	 * Method to update the link information for items that have been changed
	 * from outside the edit screen. This is fired when the item is published,
	 * unpublished, archived, or unarchived from the list view.
	 *
	 * @param   string   $context  The context for the category passed to the plugin.
	 * @param   array    $pks      An array of primary key ids of the category that has changed state.
	 * @param   integer  $value    The value of the state that the category has been changed to.
	 *
	 * @return  void
	 *
	 */
	public function onFinderChangeState($context, $pks, $value)
	{
		// We only want to handle categories here.
		if ($context === 'com_jdownloads.category'){
			/*
			 * The category published state is tied to the parent category
			 * published state so we need to look up all published states
			 * before we change anything.
			 */
			foreach ($pks as $pk){
				$query = clone $this->getStateQuery();
				$query->where('a.id = ' . (int) $pk);

				$this->db->setQuery($query);
				$item = $this->db->loadObject();

				// Translate the state.
				$state = null;

				if ($item->parent_id != 1){
					$state = $item->cat_state;
				}

				$temp = $this->translateState($value, $state);

				// Update the item.
				$this->change($pk, 'state', $temp);

				// Reindex the item.
				$this->reindex($pk);
			}
		}

		// Handle when the plugin is disabled.
		if ($context === 'com_plugins.plugin' && $value === 0){
			$this->pluginDisable($pks);
		}
	}

	/**
	 * Method to index an item. The item must be a FinderIndexerResult object.
	 *
	 * @param   FinderIndexerResult  $item    The item to index as a FinderIndexerResult object.
	 * @param   string               $format  The item format.  Not used.
	 *
	 * @return  void
	 *
	 * @throws  Exception on database error.
	 */
	protected function index(FinderIndexerResult $item, $format = 'html')
	{
		// Check if the extension is enabled.
		if (JComponentHelper::isEnabled($this->extension) === false){
			return;
		}

		$item->setLanguage();

		$extension = 'com_jdownloads';
        $item->extension = $extension;

		// Initialize the item parameters.
		$item->params = new Registry($item->params);

		$item->metadata = new Registry($item->metadata);

		// Add the meta author.
		$item->metaauthor = $item->metadata->get('author');

		// Handle the link to the metadata.
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metakey');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(FinderIndexer::META_CONTEXT, 'author');

		// Trigger the onContentPrepare event.
		$item->summary = FinderIndexerHelper::prepareContent($item->summary, $item->params);

		// Build the necessary route and path information.
		$item->url = $this->getUrl($item->id, $item->extension, $this->layout);

		$class = 'JDownloadsHelperRoute';

		// Need to import component route helpers dynamically, hence the reason it's handled here.
		JLoader::register($class, JPATH_SITE . '/components/com_jdownloads/helpers/route.php');

		if (class_exists($class) && method_exists($class, 'getCategoryRoute')){
			$item->route = $class::getCategoryRoute($item->id, $item->language);
		}

		$item->path = FinderIndexerHelper::getContentPath($item->route);

		// Get the menu title if it exists.
		$title = $this->getItemMenuTitle($item->url);

		// Adjust the title if necessary.
		if (!empty($title) && $this->params->get('use_menu_title', true)){
			$item->title = $title;
		}

		// Translate the state. Categories should only be published if the parent category is published.
		$item->state = $this->translateState($item->state);

		// Add the type taxonomy data.
		$item->addTaxonomy('Type', 'Category');

		// Add the language taxonomy data.
		$item->addTaxonomy('Language', $item->language);

		// Get content extras.
		FinderIndexerHelper::getContentExtras($item);

		// Index the item.
		$this->indexer->index($item);
	}

	/**
	 * Method to setup the indexer to be run.
	 *
	 * @return  boolean  True on success.
	 *
	 */
	protected function setup()
	{
		// Load com_jdownloads route helper as it is the fallback for routing in the indexer in this instance.
		JLoader::register('JDownloadsHelperRoute', JPATH_SITE . '/components/com_jdownloads/helpers/route.php');

		return true;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param   mixed  $query  A JDatabaseQuery object or null.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 */
	protected function getListQuery($query = null)
	{
		$db = JFactory::getDbo();

		// Check if we can use the supplied SQL query.
		$query = $query instanceof JDatabaseQuery ? $query : $db->getQuery(true)
			->select('a.id, a.title, a.alias, a.description AS summary')
			->select('a.created_user_id AS created_by, a.modified_time AS modified, a.modified_user_id AS modified_by')
			->select('a.metakey, a.metadesc, a.language, a.lft, a.parent_id, a.level')
			->select('a.created_time AS start_date, a.published AS state, a.access, a.params');

		// Handle the alias CASE WHEN portion of the query.
		$case_when_item_alias = ' CASE WHEN ';
		$case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
		$case_when_item_alias .= ' THEN ';
		$a_id = $query->castAsChar('a.id');
		$case_when_item_alias .= $query->concatenate(array($a_id, 'a.alias'), ':');
		$case_when_item_alias .= ' ELSE ';
		$case_when_item_alias .= $a_id . ' END as slug';
		$query->select($case_when_item_alias)
			->from('#__jdownloads_categories AS a')
			->where($db->quoteName('a.id') . ' > 1');

		return $query;
	}

	/**
	 * Method to get a SQL query to load the published and access states for
	 * a category and its parents.
	 *
	 * @return  JDatabaseQuery  A database object.
	 *
	 */
	protected function getStateQuery()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('a.id'))
			->select($this->db->quoteName('a.parent_id'))
			->select('a.' . $this->state_field . ' AS state, c.published AS cat_state')
			->select('a.access, c.access AS cat_access')
			->from($this->db->quoteName('#__jdownloads_categories') . ' AS a')
			->join('LEFT', '#__jdownloads_categories AS c ON c.id = a.parent_id');

		return $query;
	}
    
    /**
     * Method to check the existing access level for categories
     *
     * @param   JTable  $row  A JTable object
     *
     * @return  void
     *
     */
    protected function checkCategoryAccessJD($row)
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('access'))
            ->from($this->db->quoteName('#__jdownloads_categories'))
            ->where($this->db->quoteName('id') . ' = ' . (int) $row->id);
        $this->db->setQuery($query);

        // Store the access level to determine if it changes
        $this->old_cataccess = $this->db->loadResult();
    }
    
    /**
     * Method to update index data on category access level changes
     *
     * @param   JTable  $row  A JTable object
     *
     * @return  void
     *
     */
    protected function categoryAccessChangeJD($row)
    {
        $query = clone $this->getStateQuery();
        $query->where('c.id = ' . (int) $row->id);

        // Get the access level.
        $this->db->setQuery($query);
        $items = $this->db->loadObjectList();

        // Adjust the access level for each item within the category.
        foreach ($items as $item){
            // Set the access level.
            $temp = max($item->access, $row->access);

            // Update the item.
            $this->change((int) $item->id, 'access', $temp);

            // Reindex the item
            $this->reindex($row->id);
        }
    }
    
}
