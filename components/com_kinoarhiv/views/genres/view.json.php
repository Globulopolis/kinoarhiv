<?php defined('_JEXEC') or die;

class KinoarhivViewGenres extends JViewLegacy {
	protected $items = null;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();

		$params = $app->getParams('com_kinoarhiv');
		$items = array(
			'items'=>$this->get('Items'),
			'Itemid'=>$app->input->get('Itemid', 0, 'int'),
			'lang'=>$app->input->get('lang', '', 'word'),
			'view'=>$app->input->get('view', 'genres', 'cmd'),
		);

		$document->setName('genres');
		echo json_encode($items);
	}
}
