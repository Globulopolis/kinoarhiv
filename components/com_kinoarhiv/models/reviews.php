<?php defined('_JEXEC') or die;

class KinoarhivModelReviews extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.reviews', 'reviews', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		return array();
	}

	public function save($data) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$movie_id = $app->input->get('id', 0, 'int');
		$strip_tag = GlobalHelper::cleanHTML($data['review'], null);

		if (JString::strlen($strip_tag) < $params->get('reviews_length_min') || JString::strlen($strip_tag) > $params->get('reviews_length_max')) {
			$this->setError(JText::sprintf(JText::_('COM_KA_EDITOR_EMPTY'), $params->get('reviews_length_min'), $params->get('reviews_length_max')));

			return false;
		}

		$cleaned_text = GlobalHelper::cleanHTML($data['review']);
		$datetime = date('Y-m-d H:i:s');
		$state = $params->get('reviews_premod') == 1 ? 0 : 1;
		$ip = '';

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip .= $_SERVER['HTTP_CLIENT_IP'].' ';
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip .= $_SERVER['HTTP_X_FORWARDED_FOR'].' ';
		}
		if (!empty($_SERVER['REMOTE_ADDR'])){
			$ip .= $_SERVER['REMOTE_ADDR'];
		}

		$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_reviews')." (`id`, `uid`, `movie_id`, `review`, `created`, `type`, `ip`, `state`)"
			. "\n VALUES ('', '".(int)$user->get('id')."', '".(int)$movie_id."', '".$db->escape($cleaned_text)."', '".$datetime."', '".(int)$data['type']."', '".$ip."', '".(int)$state."')");

		try {
			$db->execute();
			$app->enqueueMessage($params->get('reviews_premod') == 1 ? JText::_('COM_KA_REVIEWS_SAVED_PREMOD') : JText::_('COM_KA_REVIEWS_SAVED'));

			$insertid = $db->insertid();
		} catch(Exception $e) {
			GlobalHelper::eventLog($e->getMessage());

			return false;
		}

		$this->sendEmails(array(
			'review'=>$cleaned_text,
			'id'=>(int)$movie_id,
			'ip'=>$ip,
			'datetime'=>$datetime,
			'insertid'=>$insertid
		));

		return true;
	}

	/**
	 * Send an email to specified users
	 *
	 * @param   array    $data    An array of form array('review'=>$review, 'id'=>$id, 'ip'=>$ip, 'datetime'=>$datetime)
	 *
	 * @return  boolean
	 *
	*/
	protected function sendEmails($data) {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$mailer = JFactory::getMailer();
		$config = JFactory::getConfig();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		if ($params->get('reviews_send_email') == 1) {
			$_recipients = $params->get('reviews_emails');

			if (empty($_recipients)) {
				$recipients = $config->get('mailfrom');
			} else {
				$_recipients = str_replace(' ', '', $params->get('reviews_emails'));
				$recipients = explode(',', $_recipients);
			}

			$subject = JText::sprintf('COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT', $app->input->post->get('movie_name', 'N/A', 'string'));
			$admin_url = JURI::base().'administrator/index.php?option=com_kinoarhiv&controller=reviews&task=edit&id[]='.$data['insertid'];
			$movie_url = JRoute::_(JURI::getInstance()).'&review='.$data['insertid'].'#review='.$data['insertid'];

			$body = JText::sprintf('COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT', '<a href="'.$movie_url.'" target="_blank">'.$app->input->post->get('movie_name', 'N/A', 'string').'</a>').'<br />'.JText::sprintf('COM_KA_REVIEWS_MAIL_INFO', $user->get('name'), $data['datetime'], $data['ip']).'<p>'.$data['review'].'</p>'.JText::_('COM_KA_REVIEWS_ADMIN_MAIL_BODY').'<a href="'.$admin_url.'" target="_blank">'.$admin_url.'</a>';

			$mailer->sendMail(
				$config->get('mailfrom'),
				$config->get('fromname'),
				$recipients,
				$subject,
				$body,
				true
			);
		}

		if ($params->get('reviews_send_email_touser') == 1) {
			// Get Itemid for menu
			$db = $this->getDBO();
			$db->setQuery("SELECT `id` FROM ".$db->quoteName('#__menu')." WHERE `link` = 'index.php?option=com_kinoarhiv&view=profile' AND `language` IN(".$db->quote(JFactory::getLanguage()->getTag()).",".$db->quote('*').") LIMIT 1");
			$menu_itemid = $db->loadResult();

			$subject = JText::sprintf('COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT', $app->input->post->get('movie_name', 'N/A', 'string'));
			$uprofile_url = JURI::base().'index.php?option=com_kinoarhiv&view=profile&tab=reviews&Itemid='.(int)$menu_itemid;
			$movie_url = JRoute::_(JURI::getInstance().'&review='.(int)$data['insertid']).'#review-'.(int)$data['insertid'];

			$body = JText::sprintf('COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT', '<a href="'.$movie_url.'" target="_blank">'.$app->input->post->get('movie_name', 'N/A', 'string').'</a>').'<br />'.JText::sprintf('COM_KA_REVIEWS_MAIL_INFO', $user->get('name'), $data['datetime'], $data['ip']).'<p>'.$data['review'].'</p>'.JText::_('COM_KA_REVIEWS_ADMIN_MAIL_BODY').'<a href="'.$uprofile_url.'" target="_blank">'.$uprofile_url.'</a>';

			$mailer->sendMail(
				$config->get('mailfrom'),
				$config->get('fromname'),
				$user->get('email'),
				$subject,
				$body,
				true
			);
		}
	}

	public function delete() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$review_id = $app->input->get('review_id', null, 'int');
		$review_ids = $app->input->get('review_ids', array(), 'array');
		$success = false;

		if (!empty($review_ids)) {
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		}

		if ($user->get('isRoot')) {
			$where = "";
		} else {
			$where = "`uid` = ".$user->id." AND ";
		}

		if (!empty($review_ids)) {
			if (empty($review_ids)) {
				return false;
			}

			$query = true;
			$db->setDebug(true);
			$db->lockTable('#__ka_reviews');
			$db->transactionStart();

			foreach ($review_ids as $id) {
				$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_reviews')." WHERE ".$where."`id` = ".(int)$id.";");
				$result = $db->execute();

				if ($result === false) {
					$query = false;
					break;
				}
			}

			if ($query === true) {
				$db->transactionCommit();
				if (count($review_ids) > 1) {
					$app->enqueueMessage(JText::_('COM_KA_REVIEWS_DELETED_MANY'));
				} else {
					$app->enqueueMessage(JText::_('COM_KA_REVIEWS_DELETED'));
				}
			} else {
				$db->transactionRollback();
				$this->setError(JText::_('JERROR_ERROR'));
			}

			$db->unlockTables();
			$db->setDebug(false);

			if ($query === false) {
				return false;
			}
		} else {
			if (empty($review_id)) {
				return false;
			}

			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_reviews')." WHERE ".$where."`id` = ".(int)$review_id);
			try {
				$db->execute();
				$app->enqueueMessage(JText::_('COM_KA_REVIEWS_DELETED'));
			} catch(Exception $e) {
				$this->setError(JText::_('JERROR_ERROR'));
				GlobalHelper::eventLog(JText::_('JERROR_ERROR'));

				return false;
			}
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
	public function validate($form, $data, $group = null) {
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception) {
			$this->setError($return->getMessage());
			return false;
		}

		// Check the validation results.
		if ($return === false) {
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}
