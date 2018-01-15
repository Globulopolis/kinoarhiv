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

	/**
	 * Method to get the field input.
	 * data-allow-clear works only with placeholder(and with first empty <option> if attached to <select>).
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.1
	 */
	public function getInput()
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');

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

		$allowed_types = array('countries', 'vendors', 'genres-movie', 'genres-name', 'tags', 'amplua', 'mediatypes');
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
		$attr .= $this->element['data-placeholder'] ? ' data-placeholder="' . JText::_($this->element['data-placeholder']) . '"' : ' data-placeholder=""';

		// Select2 3.5.x require hidden input instead of select for sorting support.
		$attr .= ((string) $this->element['data-sortable'] == 'true') ? ' data-sortable="true"' : '';

		$attr .= $this->element['data-quiet-millis'] ? ' data-quiet-millis="' . (int) $this->element['data-quiet-millis'] . '"' : '';
		$attr .= $this->element['data-minimum-input-length']
			? ' data-minimum-input-length="' . (int) $this->element['data-minimum-input-length'] . '"' : '';
		$attr .= $this->element['data-maximum-selection-size']
			? ' data-maximum-selection-size="' . (int) $this->element['data-maximum-selection-size'] . '"' : '';
		$attr .= $this->element['data-content'] ? ' data-content="' . (string) $this->element['data-content'] . '"' : '';
		$attr .= $this->element['data-key'] ? ' data-key="' . (string) $this->element['data-key'] . '"' : '';

		// Use native input
		$attr .= $this->element['data-select2-disabled'] ? ' data-select2-disabled="true"' : '';

		// Content language. This option override default content language from query.
		$data_lang = $this->element['data-lang'];
		$attr .= $data_lang ? ' data-lang="' . (string) $data_lang . '"' : '';

		// Get id attribute.
		$id = $this->id !== false ? $this->id : $this->name;

		// Replace [] if id == field name. So fiel name like form[field] will be form_field
		$id = str_replace(array('[', ']'), '', $id);
		$attr .= $id !== '' ? ' id="' . $id . '"' : '';
		$options = (array) $this->getOptions();

		if (((string) $this->element['data-remote'] == 'false' || (string) $this->element['data-remote'] == '')
			&& in_array($this->element['data-content'], $allowed_types))
		{
			$db = JFactory::getDbo();
			$user = JFactory::getUser();
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$option_html = '';
			$query = null;

			if (!empty($data_lang))
			{
				if ($data_lang == '*')
				{
					$query_lang = "";
				}
				else
				{
					$query_lang = "language IN (" . $db->quote($data_lang) . ",'*')";
				}
			}
			else
			{
				// Default active language
				$query_lang = "language IN (" . $db->quote(JFactory::getLanguage()->getTag()) . ",'*')";
			}

			if ($this->element['data-content'] == 'countries')
			{
				$query = $db->getQuery(true)
					->select('id AS value, name AS text, ' . $db->quoteName('code', 'data-country-code'))
					->from($db->quoteName('#__ka_countries'))
					->where('state = 1');

				if ($query_lang != '')
				{
					$query->where($query_lang);
				}

				$query->order('name ASC');
			}
			elseif ($this->element['data-content'] == 'vendors')
			{
				$query = $db->getQuery(true)
					->select('id AS value, company_name')
					->from($db->quoteName('#__ka_vendors'))
					->where('state = 1');

				if ($query_lang != '')
				{
					$query->where($query_lang);
				}
			}
			elseif ($this->element['data-content'] == 'genres-movie')
			{
				$query = $db->getQuery(true)
					->select('id AS value, name AS text')
					->from($db->quoteName('#__ka_genres'))
					->where('state = 1 AND access IN (' . $groups . ')');

				if ($query_lang != '')
				{
					$query->where($query_lang);
				}

				$query->order('name ASC');
			}
			elseif ($this->element['data-content'] == 'tags')
			{
				$query = $db->getQuery(true)
					->select('id AS value, title AS text')
					->from($db->quoteName('#__tags'));

					$subquery = $db->getQuery(true)
						->select('tag_id')
						->from($db->quoteName('#__contentitem_tag_map'))
						->where("type_alias = 'com_kinoarhiv.movie'");

				$query->where('id IN (' . $subquery . ') AND access IN (' . $groups . ')')
					->where('published = 1');

				if ($query_lang != '')
				{
					$query->where($query_lang);
				}
			}
			elseif ($this->element['data-content'] == 'amplua')
			{
				$amplua_disabled = $params->get('search_names_amplua_disabled');
				$query = $db->getQuery(true)
					->select('id AS value, title AS text')
					->from($db->quoteName('#__ka_names_career'))
					->where('(is_mainpage = 1 OR is_amplua = 1)');

				if ($query_lang != '')
				{
					$query->where($query_lang);
				}

				if (!empty($amplua_disabled))
				{
					$amplua_disabled = is_array($amplua_disabled) ? implode(',', $amplua_disabled) : $amplua_disabled;
					$query->where('id NOT IN (' . $amplua_disabled . ')');
				}

				$query->group('title')
					->order('ordering ASC, title ASC');
			}
			elseif ($this->element['data-content'] == 'mediatypes')
			{
				$query = $db->getQuery(true)
					->select('id AS value, title AS text')
					->from($db->quoteName('#__ka_media_types'));

				if ($query_lang != '')
				{
					$query->where($query_lang);
				}

				$query->order('title ASC');
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

			if ((string) $this->element['data-sortable'] == 'false' || (string) $this->element['data-sortable'] == '')
			{
				$options = $this->multiple ? $objects_list : array_merge($options, $objects_list);

				foreach ($options as $elementKey => &$element)
				{
					if (!isset($element->text))
					{
						if ($this->element['data-content'] == 'vendors')
						{
							$element->text = $element->company_name;
						}
					}

					$option_attr = '';
					$splitText   = explode(' - ', $element->text, 2);
					$text        = $splitText[0];

					if (isset($splitText[1]) && $splitText[1] != "" && !preg_match('/^[\s]+$/', $splitText[1]))
					{
						$text .= ' - ' . $splitText[1];
					}

					if (isset($element->{'data-country-code'}))
					{
						$option_attr .= ' data-country-code="' . $element->{'data-country-code'} . '"';
					}

					if (is_array($this->value) && in_array($element->value, $this->value))
					{
						$selected = ' selected';
					}
					else
					{
						$selected = ($this->value == $element->value) ? ' selected' : '';
					}

					$option_html .= '<option value="' . $element->value . '"' . $option_attr . $selected . '>' . $text . '</option>';
				}

				$html = '<select name="' . $this->name . '" ' . trim($attr) . '>' . $option_html . '</select>';
			}
			else
			{
				$items = array();

				foreach ($objects_list as &$item)
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

				$html = '<input type="hidden" name="' . $this->name . '" value="' . $value . '" ' . trim($attr) . ' />';
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
			$attr .= $this->element['data-remote-show-all'] == 'true' ? ' data-remote-show-all="true"' : '';
			$attr .= $this->element['data-ignore-ids'] ? ' data-ignore-ids="[' . (string) $this->element['data-ignore-ids'] . ']"' : '';

			return '<input type="hidden" name="' . $this->name . '" value="' . $value . '" ' . trim($attr) . ' />';
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
}
