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

/**
 * Form Field class for the Kinoarhiv component.
 *
 * @since  3.0
 */
class JFormFieldSlider extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $type = 'Slider';

	/**
	 * Method to get the field input.
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.0
	 */
	protected function getInput()
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');

		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/html5fallback.js', false, true);
		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/bootstrap-slider.min.css');
		JHtml::_('script', 'media/com_kinoarhiv/js/bootstrap-slider.min.js');
		JHtml::_('script', 'media/com_kinoarhiv/js/core.min.js');

		$attr = '';
		$attr .= !empty($this->class) ? ' class="hasSlider ' . $this->element['class'] . '"' : '';

		// Do not initialize slider script and do not display inputs
		$attr .= $this->element['data-slider-disabled'] == 'true' ? ' data-slider-disabled="true"' : '';

		// Disable slider
		$attr .= $this->element['data-slider-enabled'] == 'false' ? ' data-slider-enabled="false"' : '';

		$attr .= strlen($this->element['data-slider-step'])
			? ' data-slider-step="' . (float) $this->element['data-slider-step'] . '"' : '';
		$attr .= strlen($this->element['data-slider-precision'])
			? ' data-slider-precision="' . (int) $this->element['data-slider-precision'] . '"' : '';
		$attr .= strlen($this->element['data-slider-orientation'])
			? ' data-slider-orientation="' . (string) $this->element['data-slider-orientation'] . '"' : '';
		$attr .= strlen($this->element['data-slider-tooltip'])
			? ' data-slider-tooltip="' . (string) $this->element['data-slider-tooltip'] . '"' : '';
		$attr .= $this->element['data-slider-tooltip_split'] == 'true'
			? ' data-slider-tooltip_split="' . (bool) $this->element['data-slider-tooltip_split'] . '"' : '';
		$attr .= strlen($this->element['data-slider-tooltip_position'])
			? ' data-slider-tooltip_position="' . (string) $this->element['data-slider-tooltip_position'] . '"' : '';
		$attr .= strlen($this->element['data-slider-handle'])
			? ' data-slider-handle="' . (string) $this->element['data-slider-handle'] . '"' : '';
		$attr .= strlen($this->element['data-slider-input-min'])
			? ' data-slider-input-min="' . (string) $this->element['data-slider-input-min'] . '"' : '';
		$attr .= strlen($this->element['data-slider-input-max'])
			? ' data-slider-input-max="' . (string) $this->element['data-slider-input-max'] . '"' : '';
		$id = $this->id !== false ? $this->id : $this->name;
		$id = str_replace(array('[', ']'), '', $id);

		if (strlen($this->element['data-slider-min']))
		{
			if (strpos($this->element['data-slider-min'], 'params::') !== false)
			{
				$args = explode('::', $this->element['data-slider-min']);
				$min = $params->get($args[1]);
			}
			else
			{
				$min = (string) $this->element['data-slider-min'];
			}
		}
		else
		{
			$min = 0;
		}

		if (strlen($this->element['data-slider-max']))
		{
			if (strpos($this->element['data-slider-max'], 'params::') !== false)
			{
				$val_args = explode('::', $this->element['data-slider-max']);
				$max = $params->get($val_args[1]);
			}
			else
			{
				$max = (string) $this->element['data-slider-max'];
			}
		}
		else
		{
			$max = 10;
		}

		if ($this->element['data-slider-range'] == 'true')
		{
			$attr .= ' data-slider-range="true"';
			$def_args = explode(',', $this->element['data-default']);
			$def_values = array();

			// First default value
			if (strpos($def_args[0], 'params::') !== false)
			{
				$temp = explode('::', $def_args[0]);
				$def_values[0] = $params->get((string) $temp[1]);
			}
			else
			{
				// Remove openning square bracket
				$def_values[0] = substr($def_args[0], 1);
			}

			// Second default value
			if (strpos($def_args[1], 'params::') !== false)
			{
				$temp = explode('::', $def_args[1]);
				$def_values[1] = $params->get((string) substr($temp[1], 0, -1));
			}
			else
			{
				// Remove closing square bracket
				$def_values[1] = substr($def_args[1], 0, -1);
			}

			// It must be in format: [value1,value2]
			$current_value = strlen($this->value) ? '[' . $this->value . ']' : '[' . implode(',', $def_values) . ']';
		}
		else
		{
			$attr .= ' data-slider-range="false"';
			$current_value = $this->value;
		}

		if ($this->element['data-slider-disabled'] == 'false')
		{
			$attr .= ' type="text"';
		}
		else
		{
			// 'Draw' an input which hold default values.
			$attr .= ' type="hidden"';
		}

		return '<input name="' . $this->name . '" data-slider-min="' . $min . '" data-slider-max="' . $max . '"'
			. ' data-slider-value="' . $current_value . '"' . ($id !== '' ? ' id="' . $id . '"' : '') . ' ' . trim($attr) . ' />';
	}
}
