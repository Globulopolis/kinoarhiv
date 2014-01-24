<?php defined('JPATH_PLATFORM') or die;

class JFormFieldDatetime extends JFormField {
	protected $type = 'Datetime';
	protected $maxLength;

	protected function getInput() {
		//$format = $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';

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
		if (!empty($this->element['format'])) {
			$attributes .= 'data-format="'.$this->element['format'].'" ';
		}

		$html = '<div class="input-append">
			<input type="text" name="'.$this->name.'" id="'.$this->id.'" value="'. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" '.$attributes.' />
			<button class="btn cmd-datetime" id="'.$this->id.'_img"><i class="icon-calendar"></i></button>
		</div>';

		return $html;
	}
}
