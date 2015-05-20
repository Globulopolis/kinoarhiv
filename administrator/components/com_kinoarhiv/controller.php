<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivController extends JControllerLegacy {
	protected $default_view = 'controlpanel';

	public function display($cachable = false, $urlparams = false) {
		JHtml::addIncludePath(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR);

		parent::display();
	}

	public function ajaxData() {
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('global');
		$result = $model->getAjaxData();

		echo json_encode($result);
	}

	public function loadTemplate() {
		$this->addModelPath(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'music'.DIRECTORY_SEPARATOR);

		$format = $this->input->get('format', 'html', 'word');
		$template = $this->input->get('template', '', 'string');
		$model = $this->input->get('model', '', 'cmd');
		$view = $this->input->get('view', '', 'cmd');

		$view = $this->getView($view, $format);
		$model = $this->getModel($model);
		$view->setModel($model, true);

		$view->display($template);

		return $this;
	}
}
