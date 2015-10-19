<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Default controller class
 *
 * @since  3.0
 */
class KinoarhivController extends JControllerLegacy
{
	protected $default_view = 'controlpanel';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 *
	 * @since   3.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		JHtml::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR);

		parent::display();
	}

	/**
	 * Get some data from DB
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function ajaxData()
	{
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('global');
		$result = $model->getAjaxData();

		echo json_encode($result);
	}

	/**
	 * Load a template file.
	 *
	 * @return  string  The output of the the template script.
	 *
	 * @since   3.0
	 * @throws  Exception
	 */
	public function loadTemplate()
	{
		$this->addModelPath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'music' . DIRECTORY_SEPARATOR);

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
