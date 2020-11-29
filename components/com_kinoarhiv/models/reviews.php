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

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Class KinoarhivModelReviews
 *
 * @since  3.0
 */
class KinoarhivModelReviews extends JModelForm
{
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   3.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_kinoarhiv.reviews', 'reviews', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $id        Review row ID.
	 * @param   integer  $itemType  Item type. 0 for movie, 1 for album.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @throws  Exception
	 * @since   3.1
	 */
	public function getItem($id = null, $itemType = 0)
	{
		if (empty($id))
		{
			return false;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('123id', 'uid', 'item_id', 'item_type', 'review', 'created', 'type', 'ip', 'state')))
			->from($db->quoteName('#__ka_reviews'))
			->where($db->quoteName('id') . ' = ' . (int) $id)
			->where($db->quoteName('item_type') . ' = ' . (int) $itemType)
			->where($db->quoteName('state') . ' = 1');

		$db->setQuery($query);

		try
		{
			return $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}
	}

	/**
	 * Method to save review into DB
	 *
	 * @param   array  $data  A raw string from POST
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function save($data)
	{
		$app      = JFactory::getApplication();
		$db       = $this->getDbo();
		$user     = JFactory::getUser();
		$params   = JComponentHelper::getParams('com_kinoarhiv');
		$itemID   = $app->input->get('id', 0, 'int');
		$stripTag = KAComponentHelper::cleanHTML($data['review'], null);

		if ($params->get('allow_reviews') == 0)
		{
			$this->setError(JText::_('COM_KA_REVIEWS_DISABLED'));

			return false;
		}

		if (StringHelper::strlen($stripTag) < $params->get('reviews_length_min')
			|| StringHelper::strlen($stripTag) > $params->get('reviews_length_max'))
		{
			$this->setError(JText::sprintf(JText::_('COM_KA_EDITOR_EMPTY'), $params->get('reviews_length_min'), $params->get('reviews_length_max')));

			return false;
		}

		$cleanedText = KAComponentHelper::cleanHTML($data['review']);
		$datetime    = date('Y-m-d H:i:s');
		$state       = $params->get('reviews_premod') == 1 ? 0 : 1;
		$itemType    = $app->input->getCmd('view', 'movie') == 'movie' ? 0 : 1;
		$ip          = '';

		if (!empty($_SERVER['HTTP_CLIENT_IP']))
		{
			$ip .= $_SERVER['HTTP_CLIENT_IP'] . ' ';
		}

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$ip .= $_SERVER['HTTP_X_FORWARDED_FOR'] . ' ';
		}

		if (!empty($_SERVER['REMOTE_ADDR']))
		{
			$ip .= $_SERVER['REMOTE_ADDR'];
		}

		$query = $db->getQuery(true)
			->insert($db->quoteName('#__ka_reviews'))
			->columns($db->quoteName(array('id', 'uid', 'item_id', 'item_type', 'review', 'created', 'type', 'ip', 'state')))
			->values("'', '" . (int) $user->get('id') . "', '" . (int) $itemID . "', '" . $itemType . "', '" . $db->escape($cleanedText) . "', '" . $datetime . "', '" . (int) $data['type'] . "', '" . $ip . "', '" . (int) $state . "'");

		$db->setQuery($query);

		try
		{
			if ($db->execute() === false)
			{
				$this->setError(JText::_('JERROR_ERROR'));

				return false;
			}

			$insertid = $db->insertid();
			$app->enqueueMessage($params->get('reviews_premod') == 1 ? JText::_('COM_KA_REVIEWS_SAVED_PREMOD') : JText::_('COM_KA_REVIEWS_SAVED'));
		}
		catch (RuntimeException $e)
		{
			KAComponentHelper::eventLog($e->getMessage());
			$this->setError(JText::_('JERROR_ERROR'));

			return false;
		}

		$this->sendEmails(
			array(
				'review'    => $cleanedText,
				'id'        => (int) $itemID,
				'ip'        => $ip,
				'datetime'  => $datetime,
				'insertid'  => $insertid,
				'item_type' => $itemType
			)
		);

		return true;
	}

	/**
	 * Send an email to specified users.
	 *
	 * NB! Call JFactory::getMailer() on each mail send to avoid adding previous recipient to last.
	 * E.g. first email to user1@domain, second to user2@domain, third to user3@domain. Third user will recieve one email
	 * with all three email adresses in 'To' field.
	 *
	 * @param   array  $data  An array of data.
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	protected function sendEmails($data)
	{
		$db        = $this->getDbo();
		$user      = JFactory::getUser();
		$config    = JFactory::getConfig();
		$params    = JComponentHelper::getParams('com_kinoarhiv');
		$itemTitle = '';

		if ($params->get('reviews_send_email') == 1 || $params->get('reviews_send_email_touser') == 1)
		{
			$itemURL = JRoute::_(JUri::getInstance()) . '&review=' . (int) $data['insertid'] . '#review-' . (int) $data['insertid'];
			$item    = (object) array();
			$subject = '';

			if ($data['item_type'] == 0)
			{
				JLoader::register('KinoarhivModelMovie', JPATH_COMPONENT . '/models/movie.php');

				$model = new KinoarhivModelMovie;
				$item  = $model->getMovieData();
			}
			elseif ($data['item_type'] == 1)
			{
				JLoader::register('KinoarhivModelAlbum', JPATH_COMPONENT . '/models/album.php');

				$model = new KinoarhivModelAlbum;
				$item  = $model->getAlbumData();
			}

			if ($item)
			{
				$itemTitle = KAContentHelper::formatItemTitle($item->title, '', $item->year);
				$subject  = JText::sprintf(
					'COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT',
					JText::_('COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT_' . $data['item_type']),
					$itemTitle
				);
			}
		}
		else
		{
			return false;
		}

		if ($params->get('reviews_send_email') == 1)
		{
			$_recipients = $params->get('reviews_emails');

			if (!empty($_recipients))
			{
				$_recipients = str_replace(' ', '', $params->get('reviews_emails'));
				$recipients  = explode(',', $_recipients);
				$adminURL    = JUri::base() . 'administrator/index.php?option=com_kinoarhiv&task=reviews.edit&item_type='
					. $data['item_type'] . '&id[]=' . $data['insertid'];
				$body        = JText::sprintf(
					'COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT',
					JText::_('COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT_' . $data['item_type']),
					'<a href="' . $itemURL . '" target="_blank">' . $itemTitle . '</a>'
				) . '<br />' . JText::sprintf(
					'COM_KA_REVIEWS_MAIL_INFO',
					$user->get('name'), $data['datetime'], $data['ip']
				) . '<p>' . $data['review'] . '</p>' . JText::_('COM_KA_REVIEWS_ADMIN_MAIL_BODY')
				. '<a href="' . $adminURL . '" target="_blank">' . $adminURL . '</a>';

				// Call JFactory::getMailer() on each mail send to avoid adding all recipient
				$sendToAdmin = JFactory::getMailer()->sendMail(
					$config->get('mailfrom'),
					$config->get('fromname'),
					$recipients,
					$subject,
					$body,
					true
				);

				if ($sendToAdmin)
				{
					KAComponentHelper::eventLog('Cannot send an email to administrator(s) while save review.');
				}
			}
			else
			{
				KAComponentHelper::eventLog('Cannot send an email to administrator(s) while save review. Empty list of recipients!');
			}
		}

		if ($params->get('reviews_send_email_touser') == 1 && !$user->get('isRoot'))
		{
			// Get Itemid for menu
			$query = $db->getQuery(true);

			$query->select('id')
				->from($db->quoteName('#__menu'))
				->where("link = 'index.php?option=com_kinoarhiv&view=profile'")
				->where("language IN(" . $db->quote(JFactory::getLanguage()->getTag()) . "," . $db->quote('*') . ")")
				->setLimit(1, 0);

			$db->setQuery($query);

			try
			{
				$menuItemid = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}

			$body       = JText::sprintf(
				'COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT',
				JText::_('COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT_' . $data['item_type']),
				'<a href="' . $itemURL . '" target="_blank">' . $itemTitle . '</a>'
			) . '<br /><p>' . $data['review'] . '</p>';

			if (!empty($menuItemid))
			{
				$tab        = $data['item_type'] == 0 ? 'movies' : 'albums';
				$profileURL = JRoute::_(
					JUri::base() . 'index.php?option=com_kinoarhiv&view=profile&page=reviews&tab=' . $tab . '&Itemid=' . (int) $menuItemid
				);
				$body .= JText::_('COM_KA_REVIEWS_ADMIN_MAIL_BODY') . '<a href="' . $profileURL . '" target="_blank">' . $profileURL . '</a>';

				// TODO Implement user unsubscribe functionality
				// $body .= JText::sprintf('COM_KA_REVIEWS_ADMIN_MAIL_UNSUB', '', '');
			}

			$sendToUser = JFactory::getMailer()->sendMail(
				$config->get('mailfrom'),
				$config->get('fromname'),
				$user->get('email'),
				$subject,
				$body,
				true
			);

			if ($sendToUser)
			{
				KAComponentHelper::eventLog('Cannot send an email to user while save review.');
			}
		}

		return true;
	}


	/**
	 * Method to delete review(s)
	 *
	 * @param   mixed  $id  An array of IDs or integer ID.
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function delete($id)
	{
		$app       = JFactory::getApplication();
		$db        = $this->getDbo();
		$user      = JFactory::getUser();
		$rowsTotal = count($id);

		// Values submited from 'profile' page.
		if (is_array($id) && $rowsTotal > 0)
		{
			$id = ArrayHelper::toInteger($id);
		}
		else
		{
			$id = array((int) $id);
		}

		// Check if user can delete only own review(s), super user can delete all.
		if ($user->get('isRoot'))
		{
			$where = $db->quoteName('id') . ' IN (' . implode(',', $id) . ')';
		}
		else
		{
			// Check if user delete only own review(s).
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__ka_reviews'))
				->where($db->quoteName('uid') . ' = ' . (int) $user->get('id'))
				->where($db->quoteName('id') . ' IN (' . implode(',', $id) . ')');

			$db->setQuery($query);

			$column    = $db->loadColumn();
			$rowsTotal = count($column);

			if ($rowsTotal == 0)
			{
				$this->setError(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));

				return false;
			}

			$where = $db->quoteName('id') . ' IN (' . implode(',', $id) . ')';
			$where .= ' AND ' . $db->quoteName('uid') . ' = ' . (int) $user->get('id');
		}

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ka_reviews'))
			->where($where);

		$db->setQuery($query);

		try
		{
			if ($db->execute() === false)
			{
				$this->setError(JText::_('JERROR_ERROR'));

				return false;
			}

			if ($rowsTotal > 1)
			{
				$app->enqueueMessage(JText::_('COM_KA_REVIEWS_DELETED_MANY'));
			}
			else
			{
				$app->enqueueMessage(JText::_('COM_KA_REVIEWS_DELETED'));
			}
		}
		catch (RuntimeException $e)
		{
			$this->setError(JText::_('JERROR_ERROR'));
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception)
		{
			$this->setError($return->getMessage());

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}
