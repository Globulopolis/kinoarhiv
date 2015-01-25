<?php defined('JPATH_PLATFORM') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class JFormFieldDatetime extends JFormField {
	protected $type = 'Datetime';
	protected $maxLength;
	protected $script = null;

	public function __construct() {
		JHtml::_('jquery.framework');
		JHtml::_('script', JURI::root().'components/com_kinoarhiv/assets/js/jquery-ui.min.js');
		GlobalHelper::getScriptLanguage('datepicker-', true, 'ui', true);
		JHtml::_('script', JURI::base().'components/com_kinoarhiv/assets/js/jquery-ui-timepicker.min.js');
		GlobalHelper::getScriptLanguage('jquery-ui-timepicker-', true, 'timepicker', false);
		JHtml::_('script', JURI::base().'components/com_kinoarhiv/assets/js/utils.js');

		parent::__construct();
	}

	protected function getInput() {
		$attributes = ' ';
		$class = 'hasDatetime ';

		if (!empty($this->size)) {
			$attributes .= 'size="'.$this->size.'" ';
		}
		if (!empty($this->maxLength)) {
			$attributes .= 'maxlength="'.$this->maxlength.'" ';
		}
		if (!empty($this->class)) {
			$class .= $this->class.' ';
		}
		if ($this->readonly) {
			$attributes .= 'readonly ';
		}
		if ($this->disabled) {
			$attributes .= 'disabled ';
		}
		if ($this->element['required']) {
			$attributes .= 'required aria-required="true" ';
		}
		if (!empty($this->element['dateformat'])) {
			$attributes .= 'data-date-format="'.$this->element['dateformat'].'" ';
		}
		if (!empty($this->element['timeformat'])) {
			$attributes .= 'data-time-format="'.$this->element['timeformat'].'" ';
		}
		if (!empty($this->element['datatype'])) {
			$attributes .= 'data-type="'.$this->element['datatype'].'" ';
		} else {
			$attributes .= 'data-type="datetime" ';
		}

		$html = '<div class="input-append">
			<input type="text" name="'.$this->name.'" id="'.$this->id.'" value="'. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" class="'.$class.'" '.$attributes.' />
			<button class="btn btn-default cmd-datetime" id="'.$this->id.'_img"><i class="icon-calendar"></i></button>
		</div>';

		return $html;
	}
}
