<?php defined('_JEXEC') or die;

/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */
class KinoarhivController extends JControllerLegacy
{
	public function display($cachable = false, $urlparams = false)
	{
		$cachable = true;

		// Set the default view name and format from the Request.
		$vName = $this->input->getCmd('view', 'movies');
		$this->input->set('view', $vName);

		$user = JFactory::getUser();

		if ($user->get('id') || ($this->input->getMethod() == 'POST')) {
			$cachable = false;
		}

		$safeurlparams = array('id'      => 'INT', 'cid' => 'ARRAY', 'gid' => 'ARRAY', 'year' => 'INT', 'limit' => 'UINT', 'limitstart' => 'UINT',
		                       'showall' => 'INT', 'return' => 'BASE64', 'filter' => 'STRING', 'filter_order' => 'CMD', 'filter_order_Dir' => 'CMD', 'filter-search' => 'STRING', 'print' => 'BOOLEAN', 'lang' => 'CMD', 'Itemid' => 'INT');

		parent::display($cachable, $safeurlparams);

		return $this;
	}

	public function favorite()
	{
		$user = JFactory::getUser();
		$document = JFactory::getDocument();

		if ($user->guest) {
			if ($document->getType() == 'raw' || $document->getType() == 'json') {
				$document->setMimeEncoding('application/json');

				echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));
			} else {
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			return false;
		}

		$view = $this->input->get('view', 'movies', 'cmd');
		$model = $this->getModel($view);
		$result = $model->favorite();

		if ($document->getType() == 'raw' || $document->getType() == 'json') {
			$document->setMimeEncoding('application/json');

			echo json_encode($result);
		} else {
			$tab = $this->input->get('tab', '', 'cmd');

			$page = $this->input->get('page', '', 'cmd');
			$id = $this->input->get('id', 0, 'int');
			$_id = ($id != 0) ? '&id=' . $id : '';
			$tab = !empty($tab) ? '&tab=' . $tab : '';
			$page = !empty($page) ? '&page=' . $page : '';
			$return = $this->input->get('return', 'movies', 'cmd');

			$url = JRoute::_('index.php?option=com_kinoarhiv&view=' . $return . $tab . $page . $_id . '&Itemid=' . $this->input->get('Itemid', 0, 'int'), false);

			$this->setMessage($result['message'], $result['success'] ? 'message' : 'error');

			$this->setRedirect($url);
		}
	}

	public function watched()
	{
		$user = JFactory::getUser();
		$document = JFactory::getDocument();

		if ($user->guest) {
			if ($document->getType() == 'raw' || $document->getType() == 'json') {
				$document->setMimeEncoding('application/json');

				echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));
			} else {
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			return false;
		}

		$model = $this->getModel('movies');
		$result = $model->watched();

		if ($document->getType() == 'raw' || $document->getType() == 'json') {
			$document->setMimeEncoding('application/json');

			echo json_encode($result);
		} else {
			$view = $this->input->get('view', 'movies', 'cmd');
			$tab = $this->input->get('tab', '', 'cmd');

			if ($view == 'movies') {
				$url = JRoute::_('index.php?option=com_kinoarhiv&Itemid=' . $this->input->get('Itemid', 0, 'int'), false);
			} else {
				$id = $this->input->get('id', 0, 'int');
				$_id = ($id != 0) ? '&id=' . $id : '';
				$tab = !empty($tab) ? '&tab=' . $tab : '';

				$url = JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . $tab . $_id . '&Itemid=' . $this->input->get('Itemid', 0, 'int'), false);
			}

			$this->setMessage($result['message'], $result['success'] ? 'message' : 'error');

			$this->setRedirect($url);
		}
	}

	public function vote()
	{
		$user = JFactory::getUser();
		$document = JFactory::getDocument();

		$document->setMimeEncoding('application/json');

		if ($user->guest) {
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return false;
		}

		$model = $this->getModel('movie');
		$result = $model->voted();

		echo json_encode($result);
	}

	public function ajaxData()
	{
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('global');
		$result = $model->getAjaxData();

		echo json_encode($result);
	}
}
