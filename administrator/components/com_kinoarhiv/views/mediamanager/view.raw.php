<?php defined('_JEXEC') or die;

class KinoarhivViewMediamanager extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;

	public function display($tpl = null) {
		// Don't use JApplication, it's won't work with JDocumentRaw
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
		$state = $this->get('State');

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->items = &$items;
		$this->pagination = &$pagination;
		$this->state = &$state;
		$this->params = &$params;
		
		parent::display($tpl);
	}
}
