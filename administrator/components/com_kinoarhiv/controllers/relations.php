<?php defined('_JEXEC') or die;

class KinoarhivControllerRelations extends JControllerLegacy {
	public function display($cachable = false, $urlparams = array()) {
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$action = $app->input->get('action', '', 'cmd');
		$task = $app->input->get('task', '', 'cmd');
		$model = $this->getModel('relations');

		$document->setName('response');

		switch ($action) {
			case 'getList':
				$result = $model->getDataList($task);

				echo json_encode($result);
				break;
			case 'saveOrder':
				$result = $model->saveOrder($task);

				echo json_encode($result);
				break;
			default:
				return;
				break;
		}
	}

	public function add() {
		$this->edit(true);
	}

	public function edit($isNew=false) {
		$view = $this->getView('relations', 'html');
		$model = $this->getModel('relations');
		$view->setModel($model, true);

		if ($isNew === true) {
			$tpl = 'relations_add';
		} elseif ($isNew === false) {
			$tpl = 'relations_edit';
		}

		$view->display($tpl);

		return $this;
	}

	public function delete() {
		$document = JFactory::getDocument();
		$model = $this->getModel('relations');

		$document->setName('response');
		$result = $model->relations_remove();

		echo json_encode($result);
	}

	public function save2new() {
		$this->apply();
	}

	public function save() {
		$this->apply();
	}

	public function apply() {
		$document = JFactory::getDocument();

		if ($document->getType() == 'html') {
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		} else {
			JSession::checkToken() or jexit(
				json_encode(
					array(
						'success'=>false,
						'message'=>JText::_('JINVALID_TOKEN')
					)
				)
			);
		}

		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			if ($document->getType() == 'html') {
				JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
				return;
			} else {
				echo json_encode(
					array(
						'success'=>false,
						'message'=>JText::_('JERROR_ALERTNOAUTHOR')
					)
				);
				return;
			}
		}

		$model = $this->getModel('relations');

		if ($document->getType() == 'json') {
			$document->setName('response');
		} elseif ($document->getType() == 'raw') {
			$document->setMimeEncoding('application/json');
		}

		$result = $model->apply();

		echo json_encode($result);
	}

	public function saveOrder() {
		$document = JFactory::getDocument();

		if ($document->getType() == 'html') {
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		} else {
			JSession::checkToken() or jexit(
				json_encode(
					array(
						'success'=>false,
						'message'=>JText::_('JINVALID_TOKEN')
					)
				)
			);
		}

		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv')) {
			if ($document->getType() == 'html') {
				JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
				return;
			} else {
				echo json_encode(
					array(
						'success'=>false,
						'message'=>JText::_('JERROR_ALERTNOAUTHOR')
					)
				);
				return;
			}
		}

		$model = $this->getModel('relations');

		if ($document->getType() == 'json') {
			$document->setName('response');
		} elseif ($document->getType() == 'raw') {
			$document->setMimeEncoding('application/json');
		}

		$result = $model->saveOrder();

		echo json_encode($result);
	}
}
