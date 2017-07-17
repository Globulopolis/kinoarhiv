<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
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
class JFormFieldYear extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $type = 'Year';

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
		$html = array();
		$attr = '';
		$years = array();
		$options = (array) $this->getOptions();

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		if ($this->element['data-content'] == 'movie')
		{
			// Build the query for the list.
			$query = $db->getQuery(true)
				->select('year')
				->from($db->quoteName('#__ka_movies'));

			if (!empty($this->element['data-group']))
			{
				$query->group($db->escape($this->element['data-group']));
			}

			if (!empty($this->element['data-order']))
			{
				$query->order($db->escape($this->element['data-order']));
			}

			$db->setQuery($query);
			$years_list = $db->loadObjectList();
			$_years_list = array();

			foreach ($years_list as $key => $_years)
			{
				$y = explode('-', str_replace(' ', '', $_years->year));

				$_years_list[$key]['value'] = (int) $y[0];
				$_years_list[$key]['text'] = (int) $y[0];

				if (isset($y[1]) && !empty($y[1]))
				{
					$_years_list[$key]['value'] = (int) $y[1];
					$_years_list[$key]['text'] = (int) $y[1];
				}
			}

			$_years_list = array_unique($_years_list, SORT_REGULAR);
			rsort($_years_list);
			$years = array_merge($options, $_years_list);
		}

		if ($this->element['data-range'] == 'true')
		{
			$_value_1 = (is_array($this->value) && array_key_exists(0, $this->value)) ? $this->value[0] : '';
			$_value_2 = (is_array($this->value) && array_key_exists(1, $this->value)) ? $this->value[1] : '';
			$html[] = JText::_($this->element['labelfrom']) . '&nbsp;';
			$html[] = JHtml::_('select.genericlist', $years, $this->name . '[]', $attr, 'value', 'text', $_value_1, $this->id . '_from');
			$html[] = '&nbsp;&nbsp;&nbsp;&nbsp;' . JText::_($this->element['labelto']) . '&nbsp;';
			$html[] = JHtml::_('select.genericlist', $years, $this->name . '[]', $attr, 'value', 'text', $_value_2, $this->id . '_to');
		}
		else
		{
			$html[] = JHtml::_('select.genericlist', $years, $this->name, $attr, 'value', 'text', $this->value);
		}

		return implode($html);
	}
}
