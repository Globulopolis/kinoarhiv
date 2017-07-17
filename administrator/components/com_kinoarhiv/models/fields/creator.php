<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field to load a list of content authors fro com_kinoarhiv items.
 *
 * @since  3.2
 */
class JFormFieldCreator extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	public $type = 'Creator';

	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  3.0
	 */
	protected static $options = array();

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.0
	 */
	protected function getOptions()
	{
		// Accepted modifiers
		$hash = md5($this->element);
		$table = $this->element['table'] ? $this->element['table'] : 'content';
		$field = $this->element['field'] ? $this->element['field'] : 'created_by';

		if (!isset(static::$options[$hash]))
		{
			static::$options[$hash] = parent::getOptions();
			$db = JFactory::getDbo();

			// Construct the query
			$query = $db->getQuery(true)
				->select('u.id AS value, u.name AS text')
				->from($db->quoteName('#__users') . ' AS u')
				->join('INNER', $db->quoteName('#__' . $table) . ' AS c ON c.' . $field . ' = u.id')
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
