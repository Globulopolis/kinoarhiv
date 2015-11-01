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

/**
 * Form Field to load a list of mediatypes
 *
 * @since  3.0
 */
class JFormFieldMediatypes extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $type = 'Mediatypes';

	/**
	 * The allowable maxlength of input text.
	 *
	 * @var    integer
	 * @since  3.0
	 */
	protected $maxlength;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 *
	 * @since   3.0
	 */
	public function __construct($form = null)
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');

		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', JURI::root() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/css/select.css');
		JHtml::_('script', JURI::root() . 'components/com_kinoarhiv/assets/js/select2.min.js');
		KAComponentHelper::getScriptLanguage('select2_locale_', true, 'select', true);

		parent::__construct();
	}

	/**
	 * Build the HTML structure for the input label
	 *
	 * @param   string  $name         The unique name of the input field.
	 * @param   string  $label        The label text.
	 * @param   string  $description  The descriptive title of the label.
	 * @param   array   $attributes   Associative array of attributes.
	 *
	 * @return  string
	 */
	public function getLabel($name = '', $label = '', $description = '', array $attributes = array())
	{
		// Get the label text from the XML element, defaulting to the element name.
		$text = $this->element['label'] ? (string) $this->element['label'] : (string) $label;
		$text = $this->translateLabel ? JText::_($text) : $text;

		if ($this->translateDescription && !empty($this->description))
		{
			$description = JText::_($this->description);
		}
		else
		{
			if ($this->translateDescription && !empty($description))
			{
				$description = JText::_($description);
			}
		}

		$id = $this->id ? $this->id : $name;
		$required = $this->required ? $this->required : (array_key_exists('required', $attributes) ? $attributes['required'] : '');
		$labelclass = $this->labelclass ? $this->labelclass : (array_key_exists('labelclass', $attributes) ? $attributes['labelclass'] : '');

		$displayData = array(
			'text'        => $text,
			'description' => $description,
			'for'         => $id,
			'required'    => (bool) $required,
			'classes'     => explode(' ', $labelclass),
			'position'    => ''
		);

		return JLayoutHelper::render($this->renderLabelLayout, $displayData);
	}

	/**
	 * Build the HTML structure for the input field with autocomplete
	 *
	 * @param   string  $name        The unique name of the field.
	 * @param   string  $default     Default value.
	 * @param   array   $attributes  Associative array of attributes. Support 'class', 'size', 'disabled', 'readonly',
	 *                               'maxlength', 'required'. Additional 'data-*' attributes:
	 *                               data-ac-type (string) - type of the field; data-allow-clear (bool) - allow clear selection;
	 *                               data-sel-size (int) - maximum selection size(-1 no limits), data-ignore-ids (string) - ID or list of IDs
	 *                               separated by commas; data-sortable (bool) - allow items sorting; data-multiple (bool) -
	 *                               allow multiple selection(ignored if data-sel-size set to 0 or 1).
	 *
	 * @return  string
	 */
	public function getInput($name = '', $default = '', array $attributes = array())
	{
		$name = $this->name ? $this->name : $name;
		$id = $this->id ? $this->id : $name;
		$class = 'hasAutocomplete ';
		$class .= !empty($this->class) ? $this->class : (array_key_exists('class', $attributes) ? $attributes['class'] : '');
		$size = !empty($this->size) ? ' size="' . $this->size . '"' : (array_key_exists('size', $attributes) ? ' size="' . $attributes['size'] . '"' : '');
		$disabled = $this->disabled ? ' disabled' : (array_key_exists('disabled', $attributes) ? ' disabled' : '');
		$readonly = $this->readonly ? ' readonly' : (array_key_exists('readonly', $attributes) ? ' readonly' : '');
		$maxlength = !empty($this->maxlength)
			? ' maxlength="' . $this->maxlength . '"'
			: (array_key_exists('maxlength', $attributes) ? ' maxlength="' . $attributes['maxlength'] . '"' : '');
		$required = $this->required
			? ' required aria-required="true"'
			: (array_key_exists('required', $attributes) ? ' required aria-required="true"' : '');

		$data = array();
		$data[] = !empty($this->element['data-ac-type'])
			? 'data-ac-type="' . (string) $this->element['data-ac-type'] . '"'
			: (array_key_exists('data-ac-type', $attributes) ? 'data-ac-type="' . (string) $attributes['data-ac-type'] . '"' : '');
		$data[] = $this->element['data-allow-clear']
			? 'data-allow-clear="true"'
			: (array_key_exists('data-allow-clear', $attributes) ? 'data-allow-clear="true"' : '');
		$data[] = $this->element['data-sel-size']
			? 'data-sel-size="' . (bool) $this->element['data-sel-size'] . '"'
			: (array_key_exists('data-sel-size', $attributes) ? 'data-sel-size="' . (bool) $attributes['data-sel-size'] . '"' : '');
		$data[] = $this->element['data-ignore-ids']
			? 'data-ignore-ids="' . $this->element['data-ignore-ids'] . '"'
			: (array_key_exists('data-ignore-ids', $attributes) ? 'data-ignore-ids="' . $attributes['data-ignore-ids'] . '"' : '');
		$data[] = $this->element['data-sortable']
			? 'data-sortable="true"'
			: (array_key_exists('data-sortable', $attributes) ? 'data-sortable="true"' : '');

		if (isset($this->element['data-multiple']) && $this->element['data-multiple'])
		{
			$data[] = 'data-multiple="true"';
			$multiple = true;
		}
		else
		{
			if (array_key_exists('data-multiple', $attributes) && $attributes['data-multiple'])
			{
				$data[] = 'data-multiple="true"';
				$multiple = true;
			}
			else
			{
				$multiple = false;
			}
		}

		$this->value = $this->value ? $this->value : $default;

		if (isset($this->value['ids']) && is_array($this->value['ids']))
		{
			$value = implode(',', $this->value['ids']);
		}
		else
		{
			if ($multiple)
			{
				$value = $this->value;
			}
			else
			{
				$value = (int) $this->value;
			}
		}

		return '<input type="hidden" name="' . $name . '" id="' . $id . '" value="' . $value . '" class="' . $class . '"'
		. $size . $disabled . $readonly . $maxlength . $required . ' ' . implode(' ', $data) . ' />';
	}
}
