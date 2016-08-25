<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Kinoarhiv Component Controller
 *
 * @since  3.0
 */
class KinoarhivController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   3.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$cachable = true;

		// Set the default view name and format from the Request.
		$vName = $this->input->getCmd('view', 'movies');
		$this->input->set('view', $vName);

		$user = JFactory::getUser();

		if ($user->get('id') || ($this->input->getMethod() == 'POST'))
		{
			$cachable = false;
		}

		$safeurlparams = array('id'      => 'INT', 'cid' => 'ARRAY', 'gid' => 'ARRAY', 'year' => 'INT', 'limit' => 'UINT', 'limitstart' => 'UINT',
								'showall' => 'INT', 'return' => 'BASE64', 'filter' => 'STRING', 'filter_order' => 'CMD', 'filter_order_Dir' => 'CMD',
								'filter-search' => 'STRING', 'print' => 'BOOLEAN', 'lang' => 'CMD', 'Itemid' => 'INT');

		parent::display($cachable, $safeurlparams);

		return $this;
	}

	/**
	 * Method to mark movie as favorite
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since  3.0
	 */
	public function favorite()
	{
		$user = JFactory::getUser();
		$document = JFactory::getDocument();

		if ($user->guest)
		{
			if ($document->getType() == 'raw' || $document->getType() == 'json')
			{
				$document->setMimeEncoding('application/json');

				echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));
			}
			else
			{
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			return false;
		}

		$view = $this->input->get('view', 'movies', 'cmd');
		$model = $this->getModel($view);
		$result = $model->favorite();

		if ($document->getType() == 'raw' || $document->getType() == 'json')
		{
			$document->setMimeEncoding('application/json');

			echo json_encode($result);
		}
		else
		{
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

	/**
	 * Method to mark movie as watched
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since  3.0
	 */
	public function watched()
	{
		$user = JFactory::getUser();
		$document = JFactory::getDocument();

		if ($user->guest)
		{
			if ($document->getType() == 'raw' || $document->getType() == 'json')
			{
				$document->setMimeEncoding('application/json');

				echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));
			}
			else
			{
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			return false;
		}

		$model = $this->getModel('movies');
		$result = $model->watched();

		if ($document->getType() == 'raw' || $document->getType() == 'json')
		{
			$document->setMimeEncoding('application/json');

			echo json_encode($result);
		}
		else
		{
			$view = $this->input->get('view', 'movies', 'cmd');
			$tab = $this->input->get('tab', '', 'cmd');

			if ($view == 'movies')
			{
				$url = JRoute::_('index.php?option=com_kinoarhiv&Itemid=' . $this->input->get('Itemid', 0, 'int'), false);
			}
			else
			{
				$id = $this->input->get('id', 0, 'int');
				$_id = ($id != 0) ? '&id=' . $id : '';
				$tab = !empty($tab) ? '&tab=' . $tab : '';

				$url = JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . $tab . $_id . '&Itemid=' . $this->input->get('Itemid', 0, 'int'), false);
			}

			$this->setMessage($result['message'], $result['success'] ? 'message' : 'error');

			$this->setRedirect($url);
		}
	}

	/**
	 * Method to process user votes
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	public function vote()
	{
		$user = JFactory::getUser();
		JFactory::getDocument()->setMimeEncoding('application/json');

		if ($user->guest)
		{
			echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

			return false;
		}

		$model = $this->getModel('movie');
		$result = $model->voted();

		echo json_encode($result);
	}
}
