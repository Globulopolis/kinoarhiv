<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivControllerMovies extends JControllerLegacy {
	public function add() {
		$this->edit(true);
	}

	public function edit($isNew=false) {
		$view = $this->getView('movies', 'html');
		$model = $this->getModel('movie');
		$view->setModel($model, true);

		if ($isNew === true) {
			$tpl = 'add';
		} elseif ($isNew === false) {
			$tpl = 'edit';
		}

		$view->display($tpl);

		return $this;
	}

	public function save2new() {
		$this->save();
	}

	public function apply() {
		$this->save();
	}

	public function save() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv.movie')) {
			if ($document->getType() == 'html') {
				JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
				return;
			} else {
				$document->setName('response');
				echo json_encode(array('success'=>false, 'message'=>JText::_('JERROR_ALERTNOAUTHOR')));
				return;
			}
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('movie');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form) {
			if ($document->getType() == 'html') {
				$app->enqueueMessage($model->getError(), 'error');

				return false;
			} else {
				$document->setName('response');
				echo json_encode(array('success'=>false, 'message'=>$model->getError()));
				return;
			}
		}

		// Store data for use in KinoarhivModelMovie::loadFormData()
		$app->setUserState('com_kinoarhiv.movies.'.$user->id.'.edit_data', $data);
		$validData = $model->validate($form, $data, 'movie');

		if ($validData === false) {
			$errors = GlobalHelper::renderErrors($model->getErrors(), $document->getType());

			if ($document->getType() == 'html') {
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=edit&id[]='.$data['id']);

				return false;
			} else {
				$document->setName('response');
				echo json_encode(array('success'=>false, 'message'=>$errors));
				return;
			}
		}

		$result = $model->save($validData);
		$session_data = $app->getUserState('com_kinoarhiv.movies.'.$user->id.'.data');

		if (!$result) {
			if ($document->getType() == 'html') {
				GlobalHelper::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=edit&id[]='.$data['id']);

				return false;
			} else {
				$document->setName('response');
				echo json_encode($session_data);
				return;
			}
		}

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');
		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movies.'.$user->id.'.data', null);
		$app->setUserState('com_kinoarhiv.movies.'.$user->id.'.edit_data', null);

		if ($document->getType() == 'html') {
			$id = $session_data['data']['id'];

			// Set the redirect based on the task.
			switch ($this->getTask()) {
				case 'save2new':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=add', $message);
					break;
				case 'apply':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=edit&id[]='.$id, $message);
					break;

				case 'save':
				default:
					$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', $message);
					break;
			}
		} else {
			$document->setName('response');
			echo json_encode($session_data);
		}

		return true;
	}

	public function saveAccessRules() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv') && !JFactory::getUser()->authorise('core.edit.access', 'com_kinoarhiv')) {
			return array('success'=>false, 'message'=>JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$model = $this->getModel('movie');
		$result = $model->saveAccessRules();

		echo json_encode($result);
	}

	public function unpublish() {
		$this->publish(true);
	}

	public function publish($isUnpublish=false) {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_kinoarhiv.movie')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel('movie');
		$result = $model->publish($isUnpublish);

		if ($result === false) {
			$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');
			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.movies.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED'));
	}

	public function remove() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.delete', 'com_kinoarhiv.movie')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$model = $this->getModel('movie');
		$result = $model->remove();

		if ($result === false) {
			$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');
			return false;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.movies.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}

	public function cancel() {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.edit', 'com_kinoarhiv.movie')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.movies.'.$user->id.'.data', null);
		$app->setUserState('com_kinoarhiv.movies.'.$user->id.'.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies');
	}

	public function getCast() {
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('movie');
		$result = $model->getCast();

		echo json_encode($result);
	}

	public function deleteCast() {
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('movie');
		$result = $model->deleteCast();

		echo json_encode($result);
	}

	public function getAwards() {
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('movie');
		$result = $model->getAwards();

		echo json_encode($result);
	}

	public function getPremieres() {
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('movie');
		$result = $model->getPremieres();

		echo json_encode($result);
	}

	public function getReleases() {
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('movie');
		$result = $model->getReleases();

		echo json_encode($result);
	}

	public function deleteRelAwards() {
		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('movie');
		$result = $model->deleteRelAwards();

		echo json_encode($result);
	}

	public function getRates() {
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$param = $app->input->get('param', '', 'string');
		$id = $app->input->get('id', '', 'string');
		$success = true;
		$message = '';
		$votesum = 0; $votes = 0;

		if ($param == 'imdb_vote' || $param == 'kp_vote') {
			$headers = array(
				'Cookie'=>'PHPSESSID=2fe68b9818bf8339f46d4fb5eb4cd613; user_country=ru; noflash=false; mobile=no; mobile=no',
				'Host'=>'www.kinopoisk.ru',
				'Referer'=>'http://www.kinopoisk.ru/',
				'User-Agent'=>'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0'
			);
			$response = GlobalHelper::getRemoteData('http://www.kinopoisk.ru/rating/'.(int)$id.'.xml', $headers, 30, array('curl', 'socket'));

			$xml = new SimpleXMLElement($response->body);
			if ($param == 'kp_vote') {
				$votesum = (string)$xml->kp_rating;
				$votes = (int)$xml->kp_rating['num_vote'];
			} elseif ($param == 'imdb_vote') {
				$votesum = (string)$xml->imdb_rating;
				$votes = (int)$xml->imdb_rating['num_vote'];
			}
		} elseif ($param == 'rt_vote') {
			$headers = array(
				'Cookie'=>'ServerID=1323; instart=8; JSESSIONID=F44F3F597B674EB4E179EA4A4E5F7E51.localhost',
				'Host'=>'www.rottentomatoes.com',
				'Referer'=>'http://www.rottentomatoes.com/',
				'User-Agent'=>'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0'
			);
			$response = GlobalHelper::getRemoteData('http://www.rottentomatoes.com/m/'.$id.'/', $headers, 30, array('curl', 'socket'));

			// Find div with the rating
			if (preg_match('/<div class="col-xs-12">(.*?)<div class="col-xs-12/si', $response->body, $matches)) {
				preg_match('#<span itemprop="ratingValue">(.*?)<\/span>#si', $matches[1], $_votesum);
				preg_match('#<span itemprop="reviewCount ratingCount">(.*)<\/span>#si', $matches[1], $_votes);

				if (!isset($_votesum[1])) {
					$message = JText::_('ERROR').': '.JText::_('COM_KA_FIELD_MOVIE_RATES_EMPTY');
					$success = false;
				} else {
					$votesum = (int)$_votesum[1];
					$votes = (int)$_votes[1];
				}
			} else {
				$message = JText::_('ERROR').'! Someting wrong with a parser!';
				$success = false;
			}
		} elseif ($param == 'mc_vote') {
			$headers = array(
				'Cookie'=>'ctk=NTRkODU4NzljMzAzZjQwNWM2OGIyNzMzYTE4Mg%3D%3D; utag_main=v_id:014b6d19a0b10014d4feeb376aff0a048001a00d0086e$_sn:3$_ss:1$_st:1423471721686$_pn:1%3Bexp-session$ses_id:1423469921686%3Bexp-session; AMCV_10D31225525FF5790A490D4D%40AdobeOrg=-2017484664%7CMCMID%7C07680162007879004744428376133574352754%7CMCAID%7CNONE; s_vnum=1426056571361%26vn%3D3; s_getNewRepeat=1423469921834-Repeat; s_lv_undefined=1423469921834; prevPageType=product_overview; LDCLGFbrowser=a81db543-6173-4ca1-a45d-63880ff005ce; tmpid=1423469920701985',
				'Host'=>'www.metacritic.com',
				'Referer'=>'http://www.metacritic.com/',
				'User-Agent'=>'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0'
			);
			$response = GlobalHelper::getRemoteData('http://www.metacritic.com/movie/'.$id, $headers, 30, array('curl', 'socket'));

			// Finding the div with rating
			if (preg_match('/<div class="details main_details">(.*?)<div class="details side_details">/si', $response->body, $matches)) {
				preg_match('%<span itemprop="ratingValue">(.*?)<\/span>%si', $matches[1], $_votesum);
				preg_match('%<span itemprop="reviewCount">(.*?)<\/span>%si', $matches[1], $_votes);

				if (!isset($_votesum[1])) {
					$message = JText::_('ERROR').': '.JText::_('COM_KA_FIELD_MOVIE_RATES_EMPTY');
					$success = false;
				} else {
					$votesum = (int)$_votesum[1];
					$votes = (int)str_replace(' ', '', $_votes[1]);
				}
			} else {
				$message = JText::_('ERROR').'! Someting wrong with a parser!';
				$success = false;
			}
		}

		$document->setName('response');
		echo json_encode(array('success'=>$success, 'votesum'=>$votesum, 'votes'=>$votes, 'message'=>$message));
	}

	public function updateRateImg() {
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit', 'com_kinoarhiv.movie')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$votes = $app->input->get('votes', 0, 'int');
		$votesum = $app->input->get('votesum', '', 'string');
		$cmd = $app->input->get('elem', '', 'string');
		$success = true;
		$message = '';

		if ($cmd == 'rt_vote') { // Rotten Tomatoes
			$text = array(
				0=>array('fontsize'=>10, 'text'=>$votesum.'%', 'color'=>'#333333'),
				1=>array('fontsize'=>7, 'text'=>'( '.$votes.' )', 'color'=>'#555555'),
			);
		} elseif ($cmd == 'mc_vote') { // Metacritic
			$text = array(
				0=>array('fontsize'=>10, 'text'=>$votesum, 'color'=>'#333333'),
				1=>array('fontsize'=>7, 'text'=>$votes.' Critics', 'color'=>'#555555'),
			);
		} else {
			$text = array(
				0=>array('fontsize'=>10, 'text'=>round($votesum, $params->get('vote_summ_precision'), PHP_ROUND_HALF_UP), 'color'=>'#333333'),
				1=>array('fontsize'=>7, 'text'=>'( '.$votes.' )', 'color'=>'#555555'),
			);
		}

		JLoader::register('ImageHelper', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'image.php');
		$result = ImageHelper::createRateImage($text);

		$document->setMimeEncoding('application/json');
		$document->setName('response');
		echo json_encode(array('success'=>$result, 'message'=>$message));
	}

	public function saveRelNames() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit', 'com_kinoarhiv.movie')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('relations');
		$result = $model->saveRelNames();

		echo json_encode($result);
	}

	public function saveRelAwards() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit', 'com_kinoarhiv.movie')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('relations');
		$result = $model->saveRelAwards();

		echo json_encode($result);
	}

	public function saveOrder() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();

		$model = $this->getModel('movies');
		$result = $model->saveOrder();

		$document->setName('response');
		echo json_encode($result);
	}

	public function batch() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv.movie') && !$user->authorise('core.edit.state', 'com_kinoarhiv.movie')) {
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0) {
			$model = $this->getModel('movies');
			$result = $model->batch();

			if ($result === false) {
				GlobalHelper::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=movies');

				return false;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies');
	}

	public function deletePremieres() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('premiere');
		$result = $model->deletePremieres();

		echo json_encode($result);
	}

	public function deleteReleases() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$document = JFactory::getDocument();
		$document->setName('response');

		$model = $this->getModel('release');
		$result = $model->deleteReleases();

		echo json_encode($result);
	}
}
