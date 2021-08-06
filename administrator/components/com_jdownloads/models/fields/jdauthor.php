<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

JFormHelper::loadFieldClass('list');

/**
 * Form Field to load a list of content authors/users
 *
 */
class JFormFieldjdAuthor extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'jdAuthor';

	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 */
	protected static $options = array();

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 */
	protected function getOptions()
	{
		// Accepted modifiers
		$hash = md5($this->element);

		if (!isset(static::$options[$hash]))
		{
			static::$options[$hash] = parent::getOptions();

			$options = array();

			$db = JFactory::getDbo();

			// Construct the query
			$query = $db->getQuery(true)
				->select('u.id AS value, u.name AS text')
				->from('#__users AS u')
				->join('INNER', '#__jdownloads_files AS c ON c.created_by = u.id')
				->group('u.id, u.name')
				->order('u.name');

			// Setup the query
			$db->setQuery($query);

			// Return the result
			if ($options = $db->loadObjectList())
			{
				static::$options[$hash] = array_merge(static::$options[$hash], $options);
			}
		}

		return static::$options[$hash];
	}
}
