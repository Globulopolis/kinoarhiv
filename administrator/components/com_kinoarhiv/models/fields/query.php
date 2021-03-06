<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Supports an custom SQL select list
 *
 * @since  11.1
 */
class JFormFieldQuery extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'Query';

	/**
	 * The keyField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $keyField;

	/**
	 * The valueField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $valueField;

	/**
	 * The translate.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $translate = false;

	/**
	 * The query.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $query;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'keyField':
			case 'valueField':
			case 'translate':
			case 'query':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'keyField':
			case 'valueField':
			case 'translate':
			case 'query':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->keyField   = $this->element['key_field'] ? (string) $this->element['key_field'] : 'value';
			$this->valueField = $this->element['value_field'] ? (string) $this->element['value_field'] : (string) $this->element['name'];
			$this->translate  = $this->element['translate'] ? (string) $this->element['translate'] : false;

			if ($this->element['usemask'])
			{
				$filter = JFilterInput::getInstance();
				$filter_vars = JFactory::getApplication()->getUserStateFromRequest($this->element['userstate'], 'filter', null, 'array');
				$where_and = (string) $this->element['where'];
				$where_and_arr = explode(',', $where_and);
				$_where = array();

				foreach ($where_and_arr as $where)
				{
					if ($where !== '')
					{
						list($field, $value, $filter_type) = explode(':', $where);
						$filter_type = empty($filter_type) ? 'cmd' : $filter_type;
						$value_clean = $filter->clean($filter_vars[$value], strtoupper($filter_type));

						if ($filter_vars[$value] !== '')
						{
							if (is_int($value_clean))
							{
								$_where[] = $field . " = " . $value_clean . " AND";
							}
							else
							{
								$_where[] = $field . " = '" . $value_clean . "' AND";
							}
						}
					}
				}

				$query = (string) $this->element['query'];
				$this->query = preg_replace('#{where}#', implode(' ', $_where), $query);
			}
			else
			{
				$this->query = (string) $this->element['query'];
			}
		}

		return $return;
	}

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();

		// Initialize some field attributes.
		$key   = $this->keyField;
		$value = $this->valueField;

		// Get the database object.
		$db = JFactory::getDbo();

		// Set the query and get the result list.
		$db->setQuery($this->query);

		try
		{
			$items = $db->loadObjectlist();
		}
		catch (Exception $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if ($this->translate == true)
				{
					$options[] = JHtml::_('select.option', $item->$key, JText::_($item->$value));
				}
				else
				{
					$options[] = JHtml::_('select.option', $item->$key, $item->$value);
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
