<?php defined('_JEXEC') or die;

class KinoarhivControllerPremieres extends JControllerLegacy {
	public function saveOrder() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();

		$model = $this->getModel('premieres');
		$result = $model->saveOrder();

		$document->setName('response');
		echo json_encode($result);
	}

	public function add() {
		$this->edit(true);
	}

	public function edit($isNew=false) {
		/*$view = $this->getView('awards', 'html');
		$model = $this->getModel('award');
		$view->setModel($model, true);

		if ($isNew === true) {
			$tpl = 'add';
		} elseif ($isNew === false) {
			$tpl = 'edit';
		}

		$view->display($tpl);

		return $this;*/
		echo 'ok';
	}
}
