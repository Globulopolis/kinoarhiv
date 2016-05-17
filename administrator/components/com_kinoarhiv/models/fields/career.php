<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field to load a list of careers
 *
 * @since  3.0
 */
class JFormFieldCareer extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $type = 'Career';

	/**
	 * Method to get the field input.
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.0
	 */
	protected function getInput()
	{
		if (!is_array($this->value) && !empty($this->value))
		{
			if (is_string($this->value))
			{
				$this->value = explode(',', $this->value);
			}
		}

		$input = parent::getInput();

		return $input;
	}

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.0
	 */
	protected function getOptions()
	{
		$lang = JFactory::getLanguage();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id AS value, title AS text')
			->from('#__ka_names_career')
			->where('language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')')
			->order('title ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		$options[] = (object) array(
			'value'     => 0,
			'text'      => JText::_('JALL'),
			'published' => 1
		);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
