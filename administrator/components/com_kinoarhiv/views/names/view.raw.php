<?php defined('_JEXEC') or die;

class KinoarhivViewNames extends JViewLegacy {
	protected $item;
	protected $form;

	public function display($tpl = null) {
		$item   = $this->get('Item');
		$form   = $this->get('Form');
		$params = JComponentHelper::getParams('com_kinoarhiv');

		$this->item   = &$item;
		$this->form   = &$form;
		$this->params = &$params;

		parent::display($tpl);
	}
}
