<?php defined('JPATH_BASE') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

JFormHelper::loadFieldClass('list');

class JFormFieldCareer extends JFormFieldList {
	protected $type = 'Career';
	protected $comParams = null;

	public function __construct() {
		parent::__construct();

		// Load com_kinoarhiv config
		$this->comParams = JComponentHelper::getParams('com_kinoarhiv');
	}

	protected function getInput() {
		if (!is_array($this->value) && !empty($this->value)) {
			if (is_string($this->value)) {
				$this->value = explode(',', $this->value);
			}
		}

		$input = parent::getInput();

		return $input;
	}

	protected function getOptions() {
		$options = array();
		$name = (string)$this->element['name'];
		$lang = JFactory::getLanguage();

		$db	= JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('`id` AS `value`, `title` AS `text`')
			->from('#__ka_names_career');

		// Filter language
		$query->where('`language` IN ('.$db->quote($lang->getTag()).','.$db->quote('*').')');

		$query->order('`title` ASC');

		// Get the options.
		$db->setQuery($query);

		try {
			$options = $db->loadObjectList();
		} catch (RuntimeException $e) {
			return false;
		}

		$options[] = (object)array(
			'value' => 0,
			'text' => JText::_('JALL'),
			'published' => 1
		);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
