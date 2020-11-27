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
 * Kinoarhiv reviews class.
 *
 * @since  3.0
 */
class KinoarhivControllerReviews extends JControllerLegacy
{
	/**
	 * Method to save a review.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function save()
	{
		$app      = JFactory::getApplication();
		$id       = $app->input->get('id', null, 'int');
		$view     = $app->input->getCmd('return', 'movie');
		$itemid   = $app->input->getInt('Itemid', 0);
		$redirUrl = 'index.php?option=com_kinoarhiv&view=' . $view . '&id=' . $id . '&Itemid=' . $itemid;

		if (JSession::checkToken() === false)
		{
			KAComponentHelper::eventLog(JText::_('JINVALID_TOKEN'));
			$this->setRedirect(JRoute::_($redirUrl, false), JText::_('JINVALID_TOKEN'), 'error');

			return;
		}

		$user = JFactory::getUser();

		if ($user->guest)
		{
			KAComponentHelper::eventLog(JText::_('COM_KA_REVIEWS_AUTHREQUIRED_ERROR'));
			$this->setRedirect(JRoute::_($redirUrl, false), JText::_('COM_KA_REVIEWS_AUTHREQUIRED_ERROR'), 'error');

			return;
		}

		/** @var KinoarhivModelReviews $model */
		$model = $this->getModel('reviews');
		$data  = $app->input->post->get('form', array(), 'array');
		$form  = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');
			$this->setRedirect(JRoute::_($redirUrl, false));

			return;
		}

		$validData = $model->validate($form, $data);

		if ($validData === false)
		{
			if (!$user->guest)
			{
				$app->setUserState('com_kinoarhiv.' . $view . '.reviews.' . $id . '_user_' . $user->get('id') . 'edit', $data);
			}

			$errors = $model->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			$this->setRedirect(JRoute::_($redirUrl, false));

			return;
		}

		$result = $model->save($validData);

		if (!$result)
		{
			if (!$user->guest)
			{
				$app->setUserState('com_kinoarhiv.' . $view . '.reviews.' . $id . '_user_' . $user->get('id') . 'edit', $data);
			}

			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(JRoute::_($redirUrl, false));

			return;
		}

		// Clear stored data in session and redirect
		if (!$user->guest)
		{
			$app->setUserState('com_kinoarhiv.' . $view . '.reviews.' . $id . '_user_' . $user->get('id') . 'edit', null);
		}

		$this->setRedirect(JRoute::_($redirUrl, false));
	}

	/**
	 * Method to delete a review(s).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function delete()
	{
		$app      = JFactory::getApplication();
		$view     = $app->input->getCmd('view', 'movie');
		$itemid   = $app->input->getInt('Itemid', 0);
		$redirUrl = 'index.php?option=com_kinoarhiv&';
		$reviewID = ($view === 'profile')
			? $reviewID = $app->input->get('review_ids', array(), 'array')
			: $reviewID = $app->input->get('review_id', null, 'int');

		// Encoded value. Default 'view=profile'
		$return = $this->input->getBase64('return', 'dmlldz1wcm9maWxl');


		if ($view === 'profile' && JSession::checkToken() === false)
		{
			KAComponentHelper::eventLog(JText::_('JINVALID_TOKEN'));
			$this->setRedirect(JRoute::_($redirUrl . base64_decode($return), false), JText::_('JINVALID_TOKEN'), 'error');

			return;
		}
		elseif ($view !== 'profile' && KAComponentHelper::checkToken('get') === false)
		{
			KAComponentHelper::eventLog(JText::_('JINVALID_TOKEN'));
			$this->setRedirect(JRoute::_($redirUrl . base64_decode($return), false), JText::_('JINVALID_TOKEN'), 'error');

			return;
		}

		$user = JFactory::getUser();

		if ($user->guest)
		{
			KAComponentHelper::eventLog(JText::_('COM_KA_REVIEWS_AUTHREQUIRED_ERROR'));
			$this->setRedirect(JRoute::_($redirUrl . 'view=movies&Itemid=' . $itemid, false), JText::_('COM_KA_REVIEWS_AUTHREQUIRED_ERROR'), 'error');

			return;
		}

		if (!$user->authorise('core.delete.reviews', 'com_kinoarhiv'))
		{
			KAComponentHelper::eventLog(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'));
			$this->setRedirect(
				JRoute::_($redirUrl . base64_decode($return), false),
				JText::_('JGLOBAL_AUTH_ACCESS_DENIED'),
				'error'
			);

			return;
		}

		/** @var KinoarhivModelReviews $model */
		$model = $this->getModel('reviews');
		$result = $model->delete($reviewID);

		if (!$result)
		{
			$this->setMessage($model->getError(), 'error');
		}

		$this->setRedirect(JRoute::_($redirUrl . base64_decode($return), false));
	}
}
