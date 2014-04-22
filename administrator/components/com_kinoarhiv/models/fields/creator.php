<?php defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldCreator extends JFormFieldList {
	public $type = 'Creator';
	protected static $options = array();

	protected function getOptions() {
		// Accepted modifiers
		$hash = md5($this->element);
		$table = $this->element['table'] ? $this->element['table'] : 'content';
		$field = $this->element['field'] ? $this->element['field'] : 'created_by';

		if (!isset(static::$options[$hash])) {
			static::$options[$hash] = parent::getOptions();

			$options = array();

			$db = JFactory::getDbo();

			// Construct the query
			$query = $db->getQuery(true)
				->select('`u`.`id` AS `value`, `u`.`name` AS `text`')
				->from($db->quoteName('#__users').' AS `u`')
				->join('INNER', $db->quoteName('#__'.$table).' AS `c` ON `c`.`'.$field.'` = `u`.`id`')
				->group('`u`.`id`, `u`.`name`')
				->order('`u`.`name`');

			// Setup the query
			$db->setQuery($query);

			// Return the result
			if ($options = $db->loadObjectList()) {
				static::$options[$hash] = array_merge(static::$options[$hash], $options);
			}
		}

		return static::$options[$hash];
	}
}
