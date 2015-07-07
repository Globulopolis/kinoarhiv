<?php defined('JPATH_BASE') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

JFormHelper::loadFieldClass('list');

class JFormFieldGenre extends JFormFieldList
{
	protected $type = 'Genre';

	public function __construct()
	{
		parent::__construct();
	}

	protected function getInput()
	{
		if (!is_array($this->value) && !empty($this->value)) {
			if (is_string($this->value)) {
				$this->value = explode(',', $this->value);
			}
		}

		$input = parent::getInput();

		return $input;
	}

	protected function getOptions()
	{
		$lang = JFactory::getLanguage();

		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('`id` AS `value`, `name` AS `text`, `state` AS `published`')
			->from('#__ka_genres');

		// Filter language
		$query->where('`language` IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');

		// Filter on the published state
		$query->where('`state` IN (0,1)');

		$query->order('`name` ASC');

		// Get the options.
		$db->setQuery($query);

		try {
			$options = $db->loadObjectList();
		} catch (RuntimeException $e) {
			return false;
		}

		$options[] = (object)array(
			'value'     => 0,
			'text'      => JText::_('JALL'),
			'published' => 1
		);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
