<?php defined('_JEXEC') or die;

class KinoarhivViewCareers extends JViewLegacy {
	protected $item;
	protected $form;

	public function display($tpl = null) {
		$item = $this->get('editNamesData');
		$form = $this->get('Form');

		$this->item = &$item;
		$this->form = &$form;

		parent::display($tpl);
	}
}
