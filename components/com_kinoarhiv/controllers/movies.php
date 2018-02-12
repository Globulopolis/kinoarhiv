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
	 * Mark movie as favorite
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function favorite()
	{
		if (JFactory::getUser()->guest)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv', JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		$action = $this->input->get('action', '', 'word');

		if ($action == 'delete')
		{
			$this->favoriteRemove();

			return;
		}

		$id     = $this->input->get('id', 0, 'int');
		$view   = $this->input->get('view', 'movies', 'cmd');
		$model  = $this->getModel('movies');
		$result = $model->favoriteAdd($id);
		$id     = ($view == 'movie') ? '&id=' . $id : '';

		if (!$result)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . $id, false));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . $id, false), JText::_('COM_KA_FAVORITE_ADDED'));
		}
	}

	/**
	 * Removes movie(s) from favorites list.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function favoriteRemove()
	{
		if (JFactory::getUser()->guest)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv', JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		$id = $this->input->get('id', 0, 'int');
		$view = $this->input->get('view', 'movies', 'cmd');
		$model = $this->getModel('movies');

		// If ID not empty when data from submitted form, else from 'Remove from favorite ' link.
		if (!$id)
		{
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

			$ids = $this->input->get('ids', array(), 'array');

			if (!is_array($ids) || count($ids) < 1)
			{
				$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=movies'), JText::_('ERROR'), 'error');

				return;
			}

			// Encoded value. Default 'view=profile'
			$return = $this->input->getBase64('return', 'dmlldz1wcm9maWxl');
			$redirUrl = JRoute::_('index.php?option=com_kinoarhiv&' . base64_decode($return), false);
			$result = $model->favoriteRemove($ids);

			$this->setRedirect($redirUrl);
		}
		else
		{
			$result = $model->favoriteRemove($id);
			$id     = ($view == 'movie') ? '&id=' . $id : '';

			if (!$result)
			{
				$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . $id, false));
			}
			else
			{
				$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . $id, false), JText::_('COM_KA_FAVORITE_REMOVED'));
			}
		}
	}

	/**
	 * Mark movie as watched
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function watched()
	{
		if (JFactory::getUser()->guest)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv', JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		$action = $this->input->get('action', '', 'word');

		if ($action == 'delete')
		{
			$this->watchedRemove();

			return;
		}

		$id     = $this->input->get('id', 0, 'int');
		$view   = $this->input->get('view', 'movies', 'cmd');
		$model  = $this->getModel('movies');
		$result = $model->watchedAdd($id);
		$id     = ($view == 'movie') ? '&id=' . $id : '';

		if (!$result)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . $id, false));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . $id, false), JText::_('COM_KA_WATCHED_ADDED'));
		}
	}

	/**
	 * Removes movie(s) from watched list.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function watchedRemove()
	{
		if (JFactory::getUser()->guest)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv', JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		$id = $this->input->get('id', 0, 'int');
		$view = $this->input->get('view', 'movies', 'cmd');
		$model = $this->getModel('movies');

		// If ID not empty when data from submitted form, else from 'Remove from favorite ' link.
		if (!$id)
		{
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

			$ids = $this->input->get('ids', array(), 'array');

			if (!is_array($ids) || count($ids) < 1)
			{
				$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=watched'), JText::_('ERROR'), 'error');

				return;
			}

			// Encoded value. Default 'view=profile'
			$return = $this->input->getBase64('return', 'dmlldz1wcm9maWxl');
			$redirUrl = JRoute::_('index.php?option=com_kinoarhiv&' . base64_decode($return), false);
			$result = $model->watchedRemove($ids);

			$this->setRedirect($redirUrl);
		}
		else
		{
			$result = $model->watchedRemove($id);
			$id     = ($view == 'movie') ? '&id=' . $id : '';

			if (!$result)
			{
				$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . $id, false));
			}
			else
			{
				$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=' . $view . $id, false), JText::_('COM_KA_WATCHED_REMOVED'));
			}
		}
	}

	/**
	 * Removes user votes for movie.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.1
	 */
	public function votesRemove()
	{
		if (JFactory::getUser()->guest)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv', JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return;
		}

		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids = $this->input->get('ids', array(), 'array');

		if (!is_array($ids) || count($ids) < 1)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes'), JText::_('ERROR'), 'error');

			return;
		}

		// Encoded value. Default 'view=profile'
		$return = $this->input->getBase64('return', 'dmlldz1wcm9maWxl');
		$redirUrl = JRoute::_('index.php?option=com_kinoarhiv&' . base64_decode($return), false);
		$model = $this->getModel('movies');
		$result = $model->votesRemove($ids);

		$this->setMessage($result['message'], $result['success'] ? 'message' : 'error');

		$this->setRedirect($redirUrl);
	}
}
