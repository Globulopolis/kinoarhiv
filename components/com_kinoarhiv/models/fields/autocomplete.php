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

jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);
JFormHelper::loadFieldClass('list');

/**
 * Form field to load a remote data or local
 *
 * @since  3.1
 */
class JFormFieldAutocomplete extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $type = 'Autocomplete';

	protected $items = null;

	/**
	 * Method to get the field input.
	 * data-allow-clear works only with placeholder(and with first empty <option> if attached to <select>).
	 *
	 * @return  string|boolean  The field input. False on error.
	 *
	 * @since   3.1
	 */
	public function getInput()
	{
		JHtml::_('jquery.framework');

		if ((string) $this->element['data-sortable'] == 'true')
		{
			JHtml::_('script', 'media/com_kinoarhiv/js/jquery-ui.min.js');
		}

		JHtml::_('script', 'system/html5fallback.js', false, true);
		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/select2.min.css');
		JHtml::_('script', 'media/com_kinoarhiv/js/select2.min.js');
		KAComponentHelper::getScriptLanguage('select2_locale_', 'media/com_kinoarhiv/js/i18n/select');
		JHtml::_('script', 'media/com_kinoarhiv/js/core.min.js');

		$allowedTypes = array('countries', 'vendors', 'genres', 'tags', 'amplua', 'mediatypes');
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="hasAutocomplete ' . $this->class . '"' : ' class="hasAutocomplete"';
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
		$attr .= $this->element['data-placeholder']
			? ' data-placeholder="' . JText::_($this->element['data-placeholder']) . '"'
			: ' data-placeholder=""';

		// Select2 3.5.x require hidden input instead of select for sorting support.
		$attr .= ((string) $this->element['data-sortable'] == 'true') ? ' data-sortable="true"' : '';

		$attr .= $this->element['data-quiet-millis'] ? ' data-quiet-millis="' . (int) $this->element['data-quiet-millis'] . '"' : '';
		$attr .= $this->element['data-minimum-input-length']
			? ' data-minimum-input-length="' . (int) $this->element['data-minimum-input-length'] . '"' : '';
		$attr .= $this->element['data-max-selection']
			? ' data-max-selection="' . (int) $this->element['data-max-selection'] . '"' : '';
		$attr .= $this->element['data-content'] ? ' data-content="' . (string) $this->element['data-content'] . '"' : '';
		$attr .= $this->element['data-key'] ? ' data-key="' . (string) $this->element['data-key'] . '"' : '';
		$attr .= $this->element['data-type'] ? ' data-type="' . (string) $this->element['data-type'] . '"' : '';

		// Use native input
		$attr .= $this->element['data-select2-disabled'] ? ' data-select2-disabled="true"' : '';

		// Content language. This option override default content language from query.
		$dataLang = $this->element['data-lang'];
		$attr .= $dataLang ? ' data-lang="' . (string) $dataLang . '"' : '';

		// Get id attribute.
		$id = $this->id !== false ? $this->id : $this->name;

		// Replace [] if id == field name. So field name like form[field] will be form_field
		$id      = str_replace(array('[', ']'), '', $id);
		$attr    .= $id !== '' ? ' id="' . $id . '"' : '';
		$options = (array) $this->getOptions();

		if (((string) $this->element['data-remote'] == 'false' || (string) $this->element['data-remote'] == '')
			&& in_array($this->element['data-content'], $allowedTypes))
		{
			$db         = JFactory::getDbo();
			$optionHtml = '';
			$query      = null;

			if (!empty($dataLang))
			{
				if ($dataLang == '*')
				{
					$queryLang = "";
				}
				else
				{
					$queryLang = "language IN (" . $db->quote($dataLang) . ",'*')";
				}
			}
			else
			{
				// Default active language
				$queryLang = "language IN (" . $db->quote(JFactory::getLanguage()->getTag()) . ",'*')";
			}

			if (!empty($this->element['data-content']))
			{
				$method = 'get' . ucfirst($this->element['data-content']);

				if (method_exists($this, $method))
				{
					$this->items = $this->{$method}($queryLang);
				}
				else
				{
					KAComponentHelper::eventLog('Error while fetching data from DB in ' . __METHOD__ . '(). Wrong data-content attribute value or method ' . $method . '() not found.');

					return false;
				}
			}

			if ((string) $this->element['data-sortable'] == 'false' || (string) $this->element['data-sortable'] == '')
			{
				$options = $this->multiple ? $this->items : array_merge($options, $this->items);

				foreach ($options as $elementKey => $element)
				{
					if (!isset($element->text))
					{
						if ($this->element['data-content'] == 'vendors')
						{
							$element->text = $element->company_name;
						}
					}

					$optionAttr = '';
					$splitText   = explode(' - ', $element->text, 2);
					$text        = $splitText[0];

					if (isset($splitText[1]) && $splitText[1] != "" && !preg_match('/^[\s]+$/', $splitText[1]))
					{
						$text .= ' - ' . $splitText[1];
					}

					if (isset($element->{'data-country-code'}))
					{
						$optionAttr .= ' data-country-code="' . $element->{'data-country-code'} . '"';
					}

					if (is_array($this->value) && in_array($element->value, $this->value))
					{
						$selected = ' selected';
					}
					else
					{
						$list = explode(',', $this->value);

						if (in_array($element->value, $list))
						{
							$selected = ' selected';
						}
						else
						{
							$selected = ($this->value == $element->value) ? ' selected' : '';
						}
					}

					$optionHtml .= '<option value="' . $element->value . '" ' . $optionAttr . $selected . ' >' . $text . '</option>';
				}

				$html = '<select name="' . $this->name . '" ' . trim($attr) . '>' . $optionHtml . '</select>';
			}
			else
			{
				$items = array();

				foreach ($this->items as $item)
				{
					if ($this->element['data-content'] == 'countries')
					{
						$items[] = array(
							'value' => $item->value,
							'text'  => $item->text,
							'code'  => $item->{'data-country-code'}
						);
					}
					elseif ($this->element['data-content'] == 'vendors')
					{
						$items[] = array(
							'value' => $item->value,
							'text'  => $item->company_name
						);
					}
					else
					{
						$items[] = array(
							'value' => $item->value,
							'text'  => $item->text
						);
					}
				}

				// We need to store objects with content for dropdown list in 'data-content-value' attribute
				$attr .= " data-content-value='" . json_encode($items) . "'";

				if ($this->multiple && is_array($this->value))
				{
					if (!count($this->value))
					{
						$value = '[]';
					}
					else
					{
						$value = implode(',', $this->value);
					}
				}
				else
				{
					$value = $this->value;
				}

				$html = '<input type="hidden" name="' . $this->name . '" value="' . preg_replace('/,+/', ',', $value) . '" ' . trim($attr) . ' />';
			}

			return $html;
		}
		else
		{
			if ($this->multiple && is_array($this->value))
			{
				if (!count($this->value))
				{
					$value = '';
				}
				else
				{
					$value = implode(',', $this->value);
				}
			}
			else
			{
				$value = $this->value;
			}

			$attr .= $this->element['data-remote'] ? ' data-remote="' . (string) $this->element['data-remote'] . '"' : '';
			$attr .= $this->element['data-ignore-ids'] ? ' data-ignore-ids="[' . (string) $this->element['data-ignore-ids'] . ']"' : '';

			return '<input type="hidden" name="' . $this->name . '" value="' . preg_replace('/,+/', ',', $value) . '" ' . trim($attr) . ' />';
		}
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.1
	 */
	protected function getOptions()
	{
		$fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
		$options = array();

		if (!is_object($this->element))
		{
			return array();
		}

		foreach ($this->element->xpath('option') as $option)
		{
			// Filter requirements
			if ($requires = explode(',', (string) $option['requires']))
			{
				// Requires multilanguage
				if (in_array('multilanguage', $requires) && !JLanguageMultilang::isEnabled())
				{
					continue;
				}

				// Requires associations
				if (in_array('associations', $requires) && !JLanguageAssociations::isEnabled())
				{
					continue;
				}
			}

			$value = (string) $option['value'];
			$text = trim((string) $option) ? trim((string) $option) : $value;

			$disabled = (string) $option['disabled'];
			$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');
			$disabled = $disabled || ($this->readonly && $value != $this->value);

			$checked = (string) $option['checked'];
			$checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

			$selected = (string) $option['selected'];
			$selected = ($selected == 'true' || $selected == 'selected' || $selected == '1');

			$tmp = array(
				'value'    => $value,
				'text'     => JText::alt($text, $fieldname),
				'disable'  => $disabled,
				'class'    => (string) $option['class'],
				'selected' => ($checked || $selected),
				'checked'  => ($checked || $selected),
			);

			// Set some event handler attributes. But really, should be using unobtrusive js.
			$tmp['onclick']  = (string) $option['onclick'];
			$tmp['onchange']  = (string) $option['onchange'];

			// Add the option object to the result set.
			$options[] = (object) $tmp;
		}

		reset($options);

		return $options;
	}

	/**
	 * Get list for names amplua.
	 *
	 * @param   string  $lang  Content language
	 *
	 * @return  object|false
	 *
	 * @since   3.1
	 */
	protected function getAmplua($lang)
	{
		$params         = JComponentHelper::getParams('com_kinoarhiv');
		$ampluaDisabled = $params->get('search_names_amplua_disabled');
		$db             = JFactory::getDbo();
		$query          = $db->getQuery(true)
			->select('id AS value, title AS text')
			->from($db->quoteName('#__ka_names_career'))
			->where('(is_mainpage = 1 OR is_amplua = 1)');

		if ($lang != '')
		{
			$query->where($lang);
		}

		if (!empty($ampluaDisabled))
		{
			$ampluaDisabled = is_array($ampluaDisabled) ? implode(',', $ampluaDisabled) : $ampluaDisabled;
			$query->where('id NOT IN (' . $ampluaDisabled . ')');
		}

		$query->group('title')
			->order('ordering ASC, title ASC');

		try
		{
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}
	}

	/**
	 * Get list for countries.
	 *
	 * @param   string  $lang  Content language
	 *
	 * @return  object|false
	 *
	 * @since   3.1
	 */
	protected function getCountries($lang)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id AS value, name AS text, ' . $db->quoteName('code', 'data-country-code'))
			->from($db->quoteName('#__ka_countries'))
			->where('state = 1');

		if ($lang != '')
		{
			$query->where($lang);
		}

		$query->order('name ASC');

		try
		{
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}
	}

	/**
	 * Get list for genres.
	 *
	 * @param   string  $lang  Content language
	 *
	 * @return  object|false
	 *
	 * @since   3.1
	 */
	protected function getGenres($lang)
	{
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true)
			->select('id AS value, name AS text')
			->from($db->quoteName('#__ka_genres'))
			->where('state = 1 AND access IN (' . $groups . ')');

		if ($lang != '')
		{
			$query->where($lang);
		}

		if (!empty($this->element['data-type']))
		{
			$query->where($db->quoteName('type') . ' = ' . (int) $this->element['data-type']);
		}

		$query->order('name ASC');

		try
		{
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}
	}

	/**
	 * Get list for mediatypes.
	 *
	 * @param   string  $lang  Content language
	 *
	 * @return  object|false
	 *
	 * @since   3.1
	 */
	protected function getMediatypes($lang)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id AS value, title AS text')
			->from($db->quoteName('#__ka_media_types'));

		if ($lang != '')
		{
			$query->where($lang);
		}

		$query->order('title ASC');

		try
		{
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}
	}

	/**
	 * Get list for tags.
	 *
	 * @param   string  $lang  Content language
	 *
	 * @return  object|false
	 *
	 * @since   3.1
	 */
	protected function getTags($lang)
	{
		$user   = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true)
			->select('id AS value, title AS text')
			->from($db->quoteName('#__tags'));

		$subquery = $db->getQuery(true)
			->select('tag_id')
			->from($db->quoteName('#__contentitem_tag_map'))
			->where("type_alias = 'com_kinoarhiv.movie'");

		$query->where('id IN (' . $subquery . ') AND access IN (' . $groups . ')')
			->where('published = 1');

		if ($lang != '')
		{
			$query->where($lang);
		}

		try
		{
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}
	}

	/**
	 * Get list for vendors/distributors.
	 *
	 * @param   string  $lang  Content language
	 *
	 * @return  object|false
	 *
	 * @since   3.1
	 */
	protected function getVendors($lang)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id AS value, company_name')
			->from($db->quoteName('#__ka_vendors'))
			->where('state = 1');

		if ($lang != '')
		{
			$query->where($lang);
		}

		try
		{
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}
	}
}
