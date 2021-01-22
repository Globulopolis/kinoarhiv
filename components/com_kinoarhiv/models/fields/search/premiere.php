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
class JFormFieldPremiere extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $type = 'Premiere';

	/**
	 * Method to get the field input.
	 *
	 * @return  string|boolean  The field input, false on database error.
	 *
	 * @since   3.0
	 */
	protected function getInput()
	{
		$db = JFactory::getDbo();
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= $this->readonly ? ' readonly' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1'|| (string) $this->disabled == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->multiple ? ' multiple' : '';

		// A 'data-allow-clear' required true/false instead of an empty value
		$attr .= (string) $this->element['data-allow-clear'] == 'true' ? ' data-allow-clear="true"' : ' data-allow-clear="false"';

		// A 'data-placeholder' must be always set
		$attr .= $this->element['data-placeholder'] ? ' data-placeholder="' . JText::_($this->element['data-placeholder']) . '"' : ' data-placeholder=""';

		$options = (array) $this->getOptions();

		if ($this->element['data-content'] == 'date')
		{
			$query = $db->getQuery(true)
				->select("DATE_FORMAT(premiere_date, '" . $this->element['data-dateformat'] . "') AS value")
				->select("DATE_FORMAT(premiere_date, '" . $this->element['data-dateformat'] . "') AS text")
				->from($db->quoteName('#__ka_premieres'))
				->where($db->quoteName('language') . " IN (" . $db->quote(JFactory::getLanguage()->getTag()) . ",'*')")
				->group($db->quoteName('value'))
				->order($db->quoteName('premiere_date') . ' DESC');
		}
		elseif ($this->element['data-content'] == 'countries')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('p.country_id', 'value') . ', ' . $db->quoteName('c.name', 'text'))
				->from($db->quoteName('#__ka_premieres', 'p'))
				->leftJoin($db->quoteName('#__ka_countries', 'c') . ' ON c.id = p.country_id')
				->where("p.country_id != 0 AND p.language IN (" . $db->quote(JFactory::getLanguage()->getTag()) . ",'*')")
				->group($db->quoteName('p.country_id'))
				->order($db->quoteName('c.name') . ' ASC');
		}
		elseif ($this->element['data-content'] == 'vendors')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName(array('p.id', 'v.company_name')))
				->from($db->quoteName('#__ka_premieres', 'p'))
				->leftJoin($db->quoteName('#__ka_vendors', 'v') . ' ON v.id = p.vendor_id')
				->where("p.vendor_id != 0 AND p.language IN (" . $db->quote(JFactory::getLanguage()->getTag()) . ",'*')")
				->group($db->quoteName('p.vendor_id'));
		}
		else
		{
			return false;
		}

		try
		{
			$db->setQuery($query);
			$values = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			$values = array();
		}

		if ($this->element['data-content'] == 'vendors')
		{
			$newValues = array();

			foreach ($values as $item)
			{
				$newValues[] = array(
					'value' => $item->id,
					'text'  => $item->company_name
				);
			}

			$values = array_merge($options, $newValues);
		}
		else
		{
			$values = array_merge($options, $values);
		}

		$html[] = JHtml::_('select.genericlist', $values, $this->name, $attr, 'value', 'text', $this->value);

		return implode($html);
	}
}
