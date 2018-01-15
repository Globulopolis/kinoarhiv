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
class JFormFieldRelease extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $type = 'Release';

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
				->select('release_date AS value, DATE_FORMAT(release_date, \'%Y-%m-%d\') AS text')
				->from($db->quoteName('#__ka_releases'))
				->where("language IN (" . $db->quote(JFactory::getLanguage()->getTag()) . ",'*')")
				->group('release_date')
				->order('release_date DESC');
		}
		elseif ($this->element['data-content'] == 'countries')
		{
			$query = $db->getQuery(true)
				->select('r.id AS value, c.name AS text')
				->from($db->quoteName('#__ka_releases', 'r'))
				->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON c.id = r.country_id')
				->where("r.country_id != 0 AND r.language IN (" . $db->quote(JFactory::getLanguage()->getTag()) . ",'*')")
				->group('r.country_id')
				->order('c.name ASC');
		}
		elseif ($this->element['data-content'] == 'vendors')
		{
			$query = $db->getQuery(true)
				->select('r.id, v.company_name')
				->from($db->quoteName('#__ka_releases', 'r'))
				->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON v.id = r.vendor_id')
				->where("r.vendor_id != 0 AND r.language IN (" . $db->quote(JFactory::getLanguage()->getTag()) . ",'*')")
				->group('r.vendor_id');
		}
		else
		{
			return false;
		}

		try
		{
			$db->setQuery($query);
			$objects_list = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			KAComponentHelper::eventLog('Error while fetching data from DB in ' . __METHOD__ . '(): ' . $e->getMessage());

			return false;
		}

		if ($this->element['data-content'] == 'vendors')
		{
			$new_objects = array();

			foreach ($objects_list as $item)
			{
				$new_objects[] = array(
					'value' => $item->id,
					'text'  => $item->company_name
				);
			}

			$objects_list = array_merge($options, $new_objects);
		}
		else
		{
			$objects_list = array_merge($options, $objects_list);
		}

		$html[] = JHtml::_('select.genericlist', $objects_list, $this->name, $attr, 'value', 'text', $this->value);

		return implode($html);
	}
}
