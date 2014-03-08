<?php defined('_JEXEC') or die;

class KinoarhivControllerPremieres extends JControllerLegacy {
		public function getPremieres() {
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('premieres');
		$result = $model->getPremieres();

		echo json_encode($result);
	}
}
