<?php defined('_JEXEC') or die;

class KinoarhivViewSearch extends JViewLegacy {
	protected $items;

	public function display($tpl = null) {
		$app = JFactory::getApplication();

		$items = $this->get('Items');
		$activeFilters = $this->get('ActiveFilters');
		$this->home_itemid = $this->get('HomeItemid');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		$this->itemid = $app->input->get('Itemid', 0, 'int');
		$this->items = &$items;
		$this->params = &$params;

		parent::display($tpl);
	}

	public function setLabel($for, $title, $class='') {
		return '<label id="'.$for.'-lbl" class="'.$class.'" for="'.$for.'">'.JText::_($title).'</label>';
	}
}
