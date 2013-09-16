<?php defined('_JEXEC') or die;

class KinoarhivController extends JControllerLegacy {
	protected $default_view = 'controlpanel';

	public function display($cachable = false, $urlparams = false) {
		$view   = $this->input->get('view', 'movies');
		$layout = $this->input->get('layout', 'movies');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'movies' && $layout == 'edit' && !$this->checkEditId('com_kinoarhiv.edit.movie', $id)) {
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movies', false));

			return false;
		}

		parent::display();

		return $this;
	}

	public function ajaxData() {
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('global');
		$result = $model->getAjaxData();

		echo json_encode($result);
	}

	public function loadTemplate() {
		$format = $this->input->get('format', 'html', 'cmd');
		$template = $this->input->get('template', '', 'string');
		$model = $this->input->get('model', '', 'cmd');
		$view = $this->input->get('view', '', 'cmd');

		$view = $this->getView($view, (string)$format);
		$model = $this->getModel($model);
		$view->setModel($model, true);

		$view->display($template);

		return $this;
	}
}
