<?php defined('_JEXEC') or die;

class KinoarhivControllerNames extends JControllerLegacy {
	public function quickSave() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv')) {
			if ($document->getType() == 'html') {
				JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
				return;
			} else {
				$document->setName('response');
				echo json_encode(array('success'=>false, 'message'=>JText::_('JERROR_ALERTNOAUTHOR')));
				return;
			}
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('names');
		$result = $model->quickSave();

		$document->setName('response');
		echo json_encode($result);
	}
}
