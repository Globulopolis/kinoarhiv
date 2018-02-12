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
 * Music controller class
 *
 * @since  3.1
 */
class KinoarhivControllerAlbums extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		$this->addModelPath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'music' . DIRECTORY_SEPARATOR);

		parent::__construct($config);
	}

	/**
	 * Adds album(s) to favorites list.
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
		$view   = $this->input->get('view', 'albums', 'cmd');
		$model  = $this->getModel('albums');
		$result = $model->favoriteAdd($id);
		$id     = ($view == 'album') ? '&id=' . $id : '';

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
	 * Removes album(s) from favorites list.
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
		$view = $this->input->get('view', 'albums', 'cmd');
		$model = $this->getModel('albums');

		// If ID not empty when data from submitted form, else from 'Remove from favorite ' link.
		if (!$id)
		{
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

			$ids = $this->input->get('ids', array(), 'array');

			if (!is_array($ids) || count($ids) < 1)
			{
				$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=albums'), JText::_('ERROR'), 'error');

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
			$id     = ($view == 'album') ? '&id=' . $id : '';

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
	 * Removes user votes
	 *
	 * @return  void
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

		$ids = $this->input->get('id', array(), 'array');

		if (!is_array($ids) || count($ids) < 1)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&tab=albums'), JText::_('ERROR'), 'error');

			return;
		}

		// Make sure the item ids are integers
		$ids = Joomla\Utilities\ArrayHelper::toInteger($ids);

		$model = $this->getModel('albums');
		$result = $model->votesRemove($ids);

		echo json_encode($result);
	}
}
