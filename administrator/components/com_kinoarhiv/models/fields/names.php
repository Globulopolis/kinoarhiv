<?php defined('JPATH_PLATFORM') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class JFormFieldNames extends JFormField {
	protected $type = 'Names';

	public function __construct() {
		$params = JComponentHelper::getParams('com_kinoarhiv');

		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', JURI::root().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/css/select.css');
		JHtml::_('script', JURI::root().'components/com_kinoarhiv/assets/js/select2.min.js');
		GlobalHelper::getScriptLanguage('select2_locale_', true, 'select', true);

		parent::__construct();
	}

	/**
	 * Build the HTML structure for the input label
	 *
	 * @param   string   $name           The unique name of the input field.
	 * @param   string   $label          The label text.
	 * @param   string   $description    The descriptive title of the label.
	 * @param   string   $class          CSS class name for the HTML form label.
	 *
	 * @return  string
	 *
	*/
	public function getLabel($name='', $label='', $description='', $class='') {
		$name = $this->name ? $this->name : $name;
		$id = $this->id ? $this->id : $name;

		if (isset($this->element['class'])) {
			$label = (string)$this->element['class'];
		} elseif (!empty($class)) {
			$label = $class;
		} else {
			$label = '';
		}

        $text = $this->element['label'] ? (string) $this->element['label'] : (string) $label;

		if (isset($this->element['class'])) {
			$class = (string)$this->element['class'];
		} elseif (!empty($class)) {
			$class = $class;
		} else {
			$class = '';
		}

		if (!empty($this->description)) {
			JHtml::_('bootstrap.tooltip');
			$title = ' title="' . JHTML::tooltipText($text, $this->$description) . '"';
			$class .= ' hasTooltip';
		} elseif (!empty($description)) {
			JHtml::_('bootstrap.tooltip');
			$title = ' title="' . JHTML::tooltipText($text, $description) . '"';
			$class .= ' hasTooltip';
		} else {
			$title = '';
		}

		return '<label id="'.$id.'-lbl" class="'.$class.'" for="'.$name.'"'.$title.'>'.JText::_($text).'</label>';
	}

	/**
	 * Build the HTML structure for the input field with autocomplete
	 *
	 * @param   string   $name                The unique name of the field.
	 * @param   mixed    $size                The width of the text box in characters. If omitted the width is determined by the browser.
	 * @param   mixed    $maxlength           Limits the number of characters that may be entered.
	 * @param   string   $default             The default value.
	 * @param   string   $class               CSS class name for the HTML form field.
	 * @param   string   $readonly            The field cannot be changed and will automatically inherit the default value.
	 * @param   string   $disabled            The field cannot be changed and will automatically inherit the default value - it will also not submit.
	 * @param   string   $required            The field must be filled before submitting the form.
	 * @param   string   $multiple            Multiple items can be selected at the same time (true/false or empty value).
	 * @param   string   $data_type           Type of the data. Can be 'movies'.
	 * @param   string   $data_allow_clear    Allow clear selected data.
	 *
	 * @return  string
	 *
	*/
	public function getInput($name='', $size='', $maxlength='', $default='', $class='', $readonly='', $disabled='', $required='', $multiple='', $data_type='', $data_allow_clear='') {
		$_class = 'hasAutocomplete ';

		// Initialize some field attributes.
		$name = $this->name ? $this->name : $name;
		$id = $this->id ? $this->id : $name;

		if (isset($this->element['size'])) {
			$size = ' size="' . (int) $this->element['size'] . '"';
		} elseif (!empty($size)) {
			$size = ' size="' . (int) $size . '"';
		} else {
			$size = '';
		}

		if (isset($this->element['maxlength'])) {
			$maxlength = ' maxlength="' . (int) $this->element['maxlength'] . '"';
		} elseif (!empty($maxlength)) {
			$maxlength = ' maxlength="' . (int) $maxlength . '"';
		} else {
			$maxlength = '';
		}

		if (isset($this->element['class'])) {
			$class = $_class.(string)$this->element['class'];
		} elseif (!empty($class)) {
			$class = $_class.$class;
		} else {
			$class = '';
		}

		if (isset($this->element['readonly'])) {
			if ((string) $this->element['readonly'] == 'true') {
				$readonly = ' readonly="readonly"';
			} else {
				$readonly = '';
			}
		} elseif (!empty($readonly)) {
			$readonly = ' readonly="readonly"';
		} else {
			$readonly = '';
		}

		if (isset($this->element['multiple'])) {
			if ((string) $this->element['multiple'] == 'true') {
				$multiple = true;
			} else {
				$multiple = false;
			}
		} elseif (!empty($multiple)) {
			$multiple = true;
		} else {
			$multiple = false;
		}

		if (isset($this->element['disabled'])) {
			if ((string) $this->element['disabled'] == 'true') {
				$disabled = ' disabled="disabled"';
			} else {
				$disabled = false;
			}
		} elseif (!empty($disabled)) {
			$disabled = ' disabled="disabled"';
		} else {
			$disabled = '';
		}

		if ($this->required) {
			$required = ' required aria-required="true"';
		} elseif (!empty($required)) {
			$required = ' required aria-required="true"';
		} else {
			$required = '';
		}

		if (isset($this->element['data-ac-type'])) {
			$data_type = ' data-ac-type="'.(string)$this->element['data-ac-type'].'"';
		} elseif (!empty($data_type)) {
			$data_type = ' data-ac-type="'.(string)$data_type.'"';
		} else {
			$data_type = '';
		}

		if (isset($this->element['data-allow-clear'])) {
			$data_allow_clear = ' data-allow-clear="true"';
		} elseif (!empty($data_allow_clear)) {
			$data_allow_clear = ' data-allow-clear="true"';
		} else {
			$data_allow_clear = '';
		}

		$this->value = $this->value ? $this->value : $default;
		if (isset($this->value['ids']) && is_array($this->value['ids'])) {
			$value = implode(',', $this->value['ids']);
		} else {
			if ($multiple) {
				$value = $this->value;
			} else {
				$value = (int)$this->value;
			}
		}

		return '<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$value.'" class="'.$class.'"'.$size.$disabled.$readonly.$maxlength.$required.$data_type.$data_allow_clear.' />';
	}
}
