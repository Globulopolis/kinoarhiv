<?php defined('JPATH_PLATFORM') or die;

class JFormFieldDatetime extends JFormField {
	public $type = 'Datetime';

	protected function getInput() {
		$format = $this->element['format'] ? (string) $this->element['format'] : '%Y-%m-%d';

		$attributes = array();
		if ($this->element['size']) {
			$attributes['size'] = (int) $this->element['size'];
		}
		if ($this->element['maxlength']) {
			$attributes['maxlength'] = (int) $this->element['maxlength'];
		}
		if ($this->element['class']) {
			$attributes['class'] = (string) $this->element['class'];
		}
		if ((string) $this->element['readonly'] == 'true') {
			$attributes['readonly'] = 'readonly';
		}
		if ((string) $this->element['disabled'] == 'true') {
			$attributes['disabled'] = 'disabled';
		}
		if ($this->element['onchange']) {
			$attributes['onchange'] = (string) $this->element['onchange'];
		}
		if ($this->required) {
			$attributes['required'] = 'required';
			$attributes['aria-required'] = 'true';
		}

		if (!$readonly && !$disabled) {
			$html = '<div class="input-append">
				<input type="text" name="'.$this->name.'" id="'.$this->id.'" value="'. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" '.$attribs.' />
				<button class="btn cmd-datetime" id="'.$this->id.'_img"><i class="icon-calendar"></i></button>
			</div>';
		} else {
			$html = '<input type="text" value="'.(0 !== (int) $this->value ? self::_('date', $this->value, 'Y-m-d H:i:s', null) : '').'" '.$attribs.' />
				<input type="hidden" name="'.$this->name.'" id="'.$this->id.'" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" />';
		}

		return $html;
	}
}
