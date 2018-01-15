<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Kinoarhiv component.
 *
 * @since  3.0
 */
class JFormFieldBudget extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $type = 'Budget';

	/**
	 * Method to get the field input.
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.0
	 */
	protected function getInput()
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$html = array();
		$attr = '';
		$options = (array) $this->getOptions();

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Build the query for the list.
		$query = $db->getQuery(true)
			->select('budget AS value, budget AS text')
			->from($db->quoteName('#__ka_movies'))
			->where("budget != '' AND state = 1 AND access IN (" . $groups . ") AND language IN (" . $db->quote(JFactory::getLanguage()->getTag()) . ",'*')")
			->group('budget')
			->order('budget ASC');

		$db->setQuery($query);
		$budget = $db->loadObjectList();
		$budget = array_merge($options, $budget);

		if ($this->element['data-range'] == 'true')
		{
			$_value_1 = (is_array($this->value) && array_key_exists(0, $this->value)) ? $this->value[0] : '';
			$_value_2 = (is_array($this->value) && array_key_exists(1, $this->value)) ? $this->value[1] : '';
			$html[] = JText::_($this->element['labelfrom']) . '&nbsp;';
			$html[] = JHtml::_('select.genericlist', $budget, $this->name . '[]', $attr, 'value', 'text', $_value_1, $this->id . '_from');
			$html[] = '&nbsp;&nbsp;&nbsp;&nbsp;' . JText::_($this->element['labelto']) . '&nbsp;';
			$html[] = JHtml::_('select.genericlist', $budget, $this->name . '[]', $attr, 'value', 'text', $_value_2, $this->id . '_to');
		}
		else
		{
			$html[] = JHtml::_('select.genericlist', $budget, $this->name, $attr, 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}
}
