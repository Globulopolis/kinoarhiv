<?php defined('_JEXEC') or die;

class KinoarhivViewGenres extends JViewLegacy {
	protected $state = null;
	protected $items = null;
	protected $pagination = null;

	public function display($tpl = null) {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();

		$state = $this->get('State');
		$items = $this->get('Items');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}

		$params = $app->getParams('com_kinoarhiv');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		$this->params = &$params;
		$this->items = &$items;
		$this->user = &$user;
		$this->doctype = $document->getType();

		parent::display($tpl);
	}
}