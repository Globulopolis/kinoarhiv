<?php defined('_JEXEC') or die;

class KinoarhivModelReviews extends JModelLegacy {
	public function save() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$config = JFactory::getConfig();
		$params = $app->getParams('com_kinoarhiv');
		$type = $app->input->get('type', 0, 'int');
		$movie_id = $app->input->get('id', 0, 'int');
		$cleaned_text = GlobalHelper::cleanHTML($_REQUEST['form_editor']);
		$strip_tag = GlobalHelper::cleanHTML($_REQUEST['form_editor'], null);
		$code = $app->input->post->get('recaptcha_response_field', null, 'string');
		$ip = $_SERVER['REMOTE_ADDR'];

		if (JString::strlen($strip_tag) < $params->get('reviews_length_min') || JString::strlen($strip_tag) > $params->get('reviews_length_max')) {
			return array(
				'success' => false,
				'message' => JText::sprintf(JText::_('COM_KA_EDITOR_EMPTY'), $params->get('reviews_length_min'), $params->get('reviews_length_max'))
			);
		}

		if ($this->checkCaptcha($code)) {
			$datetime = date('Y-m-d H:i:s');
			$state = $params->get('reviews_premod') == 1 ? 0 : 1;
			$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_reviews')." (`id`, `uid`, `movie_id`, `review`, `r_datetime`, `type`, `ip`, `state`)"
				. "\n VALUES ('', '".(int)$user->get('id')."', '".(int)$movie_id."', '".$cleaned_text."', '".$datetime."', '".(int)$type."', '".$ip."', '".(int)$state."')");
			$query = $db->execute();
			$insertid = $db->insertid();

			if ($query) {
				$this->sendEmails(array(
					'review'=>$cleaned_text,
					'id'=>(int)$movie_id,
					'ip'=>$ip,
					'datetime'=>$datetime,
					'insertid'=>$insertid
				));

				return array(
					'success' => true,
					'message' => ($params->get('reviews_premod') == 1) ? JText::_('COM_KA_REVIEWS_SAVED_PREMOD') : JText::_('COM_KA_REVIEWS_SAVED')
				);
			} else {
				return array(
					'success' => false,
					'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')
				);
			}
		} else {
			return array(
				'success' => false,
				'message' => JText::_('COM_KA_EDITOR_EMPTY_CAPTCHA')
			);
		}
	}

	protected function checkCaptcha($code) {
		$app = JFactory::getApplication();
		$config = JFactory::getConfig();
		$params = $app->getParams('com_kinoarhiv');

		if ($config->get('captcha') != '0' && $params->get('reviews_save_captcha') == 1) {
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('captcha');

			$result = $dispatcher->trigger('onCheckAnswer', array('captcha', $code));
		} else {
			$result = array(true);
		}

		return $result[0];
	}

	/**
	 * Send an email to specified users
	 *
	 * @param   array  $data	An array of form array('review'=>$review, 'id'=>$id, 'ip'=>$ip, 'datetime'=>$datetime)
	 *
	 * @return  boolean
	 *
	*/
	protected function sendEmails($data) {
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$mailer = JFactory::getMailer();
		$config = JFactory::getConfig();
		$params = $app->getParams('com_kinoarhiv');
		$itemid = $app->input->get('Itemid', 0, 'int');

		if ($params->get('reviews_send_email') == 1) {
			$_recipients = $params->get('reviews_emails');

			if (empty($_recipients)) {
				$recipients = $config->get('mailfrom');
			} else {
				$_recipients = str_replace(' ', '', $params->get('reviews_emails'));
				$recipients = explode(',', $_recipients);
			}

			$subject = JText::sprintf('COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT', $app->input->post->get('movie_name', 'N/A', 'string'));
			$admin_url = JURI::base().'administrator/index.php?option=com_kinoarhiv&controller=reviews&task=edit&id[]='.$data['id'];
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
			$subject = JText::sprintf('COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT', $app->input->post->get('movie_name', 'N/A', 'string'));
			$admin_url = JURI::base().'index.php?option=com_kinoarhiv&view=profile&tab=reviews';
			$movie_url = JRoute::_(JURI::getInstance()).'&review='.$data['insertid'].'#review='.$data['insertid'];

			$body = JText::sprintf('COM_KA_REVIEWS_ADMIN_MAIL_SUBJECT', '<a href="'.$movie_url.'" target="_blank">'.$app->input->post->get('movie_name', 'N/A', 'string').'</a>').'<br />'.JText::sprintf('COM_KA_REVIEWS_MAIL_INFO', $user->get('name'), $data['datetime'], $data['ip']).'<p>'.$data['review'].'</p>'.JText::_('COM_KA_REVIEWS_ADMIN_MAIL_BODY').'<a href="'.$admin_url.'" target="_blank">'.$admin_url.'</a>';

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
		$review_id = $app->input->get('review_id', 0, 'int');
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
				$success = true;
				$message = JText::_('COM_KA_REVIEWS_DELETED');
			} else {
				$db->transactionRollback();
				$message = JText::_('JERROR_ERROR');
			}

			$db->unlockTables();
			$db->setDebug(false);
		} else {
			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_reviews')." WHERE ".$where."`id` = ".(int)$review_id);
			$result = $db->execute();

			if ($result) {
				$success = true;
				$message = JText::_('COM_KA_REVIEWS_DELETED');
			} else {
				$message = JText::_('JERROR_ERROR');
			}
		}

		return array('success'=>$success, 'message'=>$message);
	}
}
