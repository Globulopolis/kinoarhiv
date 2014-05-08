<?php defined('_JEXEC') or die;

class KinoarhivViewMovies extends JViewLegacy {
	protected $form;

	public function display($tpl = null) {
		$form   = $this->get('Form');
		$params = JComponentHelper::getParams('com_kinoarhiv');

		$this->form   = &$form;
		$this->params = &$params;

		parent::display($tpl);
	}
}
