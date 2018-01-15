<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * Movies controller class
 *
 * @since  3.1
 */
class KinoarhivControllerMovies extends JControllerLegacy
{
	/**
	 * Method to mark movie, person as favorite
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function favorite()
	{
		if (JFactory::getUser()->guest)
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids = $this->input->get('ids', array(), 'array');

		if (!is_array($ids) || count($ids) < 1)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			throw new Exception(JText::_('ERROR'), 500);
		}

		$view = $this->input->get('view', 'movies', 'cmd');
		$model = $this->getModel($view);
		$result = $model->favorite();
		$tab = $this->input->get('tab', '', 'cmd');
		$page = $this->input->get('page', '', 'cmd');
		$id = $this->input->get('id', 0, 'int');
		$_id = ($id != 0) ? '&id=' . $id : '';
		$tab = !empty($tab) ? '&tab=' . $tab : '';
		$page = !empty($page) ? '&page=' . $page : '';
		$return = $this->input->get('return', 'movies', 'cmd');
		$url = JRoute::_(
			'index.php?option=com_kinoarhiv&view=' . $return . $tab . $page . $_id
			. '&Itemid=' . $this->input->get('Itemid', 0, 'int'),
			false
		);

		$this->setMessage($result['message'], $result['success'] ? 'message' : 'error');

		$this->setRedirect($url);
	}

	/**
	 * Method to mark movie as watched
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function watched()
	{
		if (JFactory::getUser()->guest)
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids = $this->input->get('ids', array(), 'array');

		if (!is_array($ids) || count($ids) < 1)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			throw new Exception(JText::_('ERROR'), 500);
		}

		$model = $this->getModel('movies');
		$result = $model->watched();
		$tab = $this->input->get('tab', '', 'cmd');
		$page = $this->input->get('page', '', 'cmd');
		$id = $this->input->get('id', 0, 'int');
		$_id = ($id != 0) ? '&id=' . $id : '';
		$tab = !empty($tab) ? '&tab=' . $tab : '';
		$page = !empty($page) ? '&page=' . $page : '';
		$return = $this->input->get('return', 'movies', 'cmd');
		$url = JRoute::_(
			'index.php?option=com_kinoarhiv&view=' . $return . $tab . $page . $_id
			. '&Itemid=' . $this->input->get('Itemid', 0, 'int'),
			false
		);

		$this->setMessage($result['message'], $result['success'] ? 'message' : 'error');

		$this->setRedirect($url);
	}

	/**
	 * Removes user votes for movie.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function votesRemove()
	{
		if (JFactory::getUser()->guest)
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids = $this->input->get('ids', array(), 'array');

		if (!is_array($ids) || count($ids) < 1)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			throw new Exception(JText::_('ERROR'), 500);
		}

		$model = $this->getModel('movies');
		$result = $model->votesRemove($ids);
		$tab = $this->input->get('tab', '', 'cmd');
		$page = $this->input->get('page', '', 'cmd');
		$id = $this->input->get('id', 0, 'int');
		$_id = ($id != 0) ? '&id=' . $id : '';
		$tab = !empty($tab) ? '&tab=' . $tab : '';
		$page = !empty($page) ? '&page=' . $page : '';
		$return = $this->input->get('return', 'movies', 'cmd');
		$url = JRoute::_(
			'index.php?option=com_kinoarhiv&view=' . $return . $tab . $page . $_id
			. '&Itemid=' . $this->input->get('Itemid', 0, 'int'),
			false
		);

		$this->setMessage($result['message'], $result['success'] ? 'message' : 'error');

		$this->setRedirect($url);
	}
}
