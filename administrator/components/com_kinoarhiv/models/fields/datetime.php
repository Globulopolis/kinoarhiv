<?php defined('JPATH_PLATFORM') or die;

class JFormFieldDatetime extends JFormField {
	protected $type = 'Datetime';
	protected $maxLength;

	protected function getInput() {
		$attributes = ' ';

		if (!empty($this->size)) {
			$attributes .= 'size="'.$this->size.'" ';
		}
		if (!empty($this->maxLength)) {
			$attributes .= 'maxlength="'.$this->maxlength.'" ';
		}
		if (!empty($this->class)) {
			$attributes .= 'class="'.$this->class.'" ';
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

		$html = '<div class="input-append">
			<input type="text" name="'.$this->name.'" id="'.$this->id.'" value="'. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" '.$attributes.' />
			<button class="btn cmd-datetime" id="'.$this->id.'_img"><i class="icon-calendar"></i></button>
		</div>';

		return $html;
	}
}
