<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Movies list controller class
 *
 * @since  3.0
 */
class KinoarhivControllerMovies extends JControllerLegacy
{
	/**
	 * Method to add a new record.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function add()
	{
		$this->edit(true);
	}

	/**
	 * Method to edit an existing record or add a new record.
	 *
	 * @param   boolean  $isNew  Variable to check if it's new item or not.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function edit($isNew = false)
	{
		$view = $this->getView('movies', 'html');
		$model = $this->getModel('movie');
		$view->setModel($model, true);

		if ($isNew === true)
		{
			$tpl = 'add';
		}
		elseif ($isNew === false)
		{
			$tpl = 'edit';
		}

		$view->display($tpl);
	}

	/**
	 * Proxy to KinoarhivControllerMovies::save()
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function save2new()
	{
		$this->save();
	}

	/**
	 * Proxy to KinoarhivControllerMovies::save()
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function apply()
	{
		$this->save();
	}

	/**
	 * Method to save a record.
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$document = JFactory::getDocument();
		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.create', 'com_kinoarhiv') && !$user->authorise('core.edit', 'com_kinoarhiv.movie'))
		{
			if ($document->getType() == 'html')
			{
				JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

				return;
			}
			else
			{
				echo json_encode(array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR')));

				return;
			}
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('movie');
		$data = $this->input->post->get('form', array(), 'array');
		$form = $model->getForm($data, false);

		if (!$form)
		{
			if ($document->getType() == 'html')
			{
				$app->enqueueMessage($model->getError(), 'error');

				return;
			}
			else
			{
				echo json_encode(array('success' => false, 'message' => $model->getError()));

				return;
			}
		}

		// Store data for use in KinoarhivModelMovie::loadFormData()
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', $data);
		$validData = $model->validate($form, $data, 'movie');

		if ($validData === false)
		{
			$errors = KAComponentHelper::renderErrors($model->getErrors(), $document->getType());

			if ($document->getType() == 'html')
			{
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=edit&id[]=' . $data['id']);

				return;
			}
			else
			{
				echo json_encode(array('success' => false, 'message' => $errors));

				return;
			}
		}

		$result = $model->save($validData);
		$session_data = $app->getUserState('com_kinoarhiv.movies.' . $user->id . '.data');

		if (!$result)
		{
			if ($document->getType() == 'html')
			{
				KAComponentHelper::renderErrors($model->getErrors(), 'html');

				// TODO id key should be changed to avoid a notice about undefined index
				$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=edit&id[]=' . $data['id']);

				return;
			}
			else
			{
				echo json_encode($session_data);

				return;
			}
		}

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.data', null);
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', null);

		if ($document->getType() == 'html')
		{
			$id = $session_data['data']['id'];

			// Set the redirect based on the task.
			switch ($this->getTask())
			{
				case 'save2new':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=add', $message);
					break;
				case 'apply':
					$this->setRedirect('index.php?option=com_kinoarhiv&controller=movies&task=edit&id[]=' . $id, $message);
					break;

				case 'save':
				default:
					$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', $message);
					break;
			}
		}
		else
		{
			echo json_encode($session_data);
		}
	}

	/**
	 * Method to save access rules for an item.
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function saveAccessRules()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv') && !JFactory::getUser()->authorise('core.edit.access', 'com_kinoarhiv'))
		{
			return array('success' => false, 'message' => JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$model = $this->getModel('movie');
		$result = $model->saveAccessRules();

		echo json_encode($result);

		return true;
	}

	/**
	 * Method to unpublish a list of items
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function unpublish()
	{
		$this->publish(true);
	}

	/**
	 * Method to publish a list of items
	 *
	 * @param   boolean  $isUnpublish  Action state
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function publish($isUnpublish = false)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_kinoarhiv.movie'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('movie');
		$result = $model->publish($isUnpublish);

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.movies.global.data', null);

		$message = $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED');
		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', $message);
	}

	/**
	 * Method to remove an item(s).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function remove()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.delete', 'com_kinoarhiv.movie'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('movie');
		$result = $model->remove();

		if ($result === false)
		{
			$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.movies.global.data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies', JText::_('COM_KA_ITEMS_DELETED_SUCCESS'));
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function cancel()
	{
		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.edit', 'com_kinoarhiv.movie'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.data', null);
		$app->setUserState('com_kinoarhiv.movies.' . $user->id . '.edit_data', null);

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies');
	}

	/**
	 * Method to get cast and crew list.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function getCast()
	{
		$model = $this->getModel('movie');
		$result = $model->getCast();

		echo json_encode($result);
	}

	/**
	 * Method to delete an item from cast and crew list.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function deleteCast()
	{
		$model = $this->getModel('movie');
		$result = $model->deleteCast();

		echo json_encode($result);
	}

	/**
	 * Method to get awards list.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function getAwards()
	{
		$model = $this->getModel('movie');
		$result = $model->getAwards();

		echo json_encode($result);
	}

	/**
	 * Method to get premieres list.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function getPremieres()
	{
		$model = $this->getModel('movie');
		$result = $model->getPremieres();

		echo json_encode($result);
	}

	/**
	 * Method to get releases list.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function getReleases()
	{
		$model = $this->getModel('movie');
		$result = $model->getReleases();

		echo json_encode($result);
	}

	/**
	 * Method to delete an award from awards list.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function deleteRelAwards()
	{
		$model = $this->getModel('movie');
		$result = $model->deleteRelAwards();

		echo json_encode($result);
	}

	/**
	 * Method to update ratings from movies sites.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function getRates()
	{
		$app = JFactory::getApplication();
		$param = $app->input->get('param', '', 'string');

		// Movie ID from Kinopoisk, Rottentomatoes or Metacritic
		$id = $app->input->get('id', '', 'string');

		// Movie ID from DB
		$movie_id = $app->input->get('movie_id', 0, 'int');

		$registry = new Registry;
		$config_rating_parser = JPath::clean(JPATH_COMPONENT_ADMINISTRATOR . '/config_rating_parser.xml');

		if (!is_file($config_rating_parser))
		{
			echo json_encode(
				array(
					'success'  => false,
					'votesum'  => 0,
					'votes'    => 0,
					'message'  => 'Configuration file not found!',
					'movie_id' => $movie_id
				)
			);

			return;
		}

		$parser_conf = $registry->loadFile($config_rating_parser, 'XML');
		$parser_conf = $parser_conf->toArray();

		$success = true;
		$message = '';
		$votesum = 0;
		$votes = 0;

		if ($param == 'imdb_vote' || $param == 'kp_vote')
		{
			$response = KAComponentHelper::getRemoteData(
				'http://www.kinopoisk.ru/rating/' . (int) $id . '.xml',
				$parser_conf['imdb']['headers'],
				30,
				array('curl', 'socket')
			);

			$xml = new SimpleXMLElement($response->body);

			if ($param == 'kp_vote')
			{
				$votesum = (string) $xml->kp_rating;
				$votes = (int) $xml->kp_rating['num_vote'];
			}
			elseif ($param == 'imdb_vote')
			{
				$votesum = (string) $xml->imdb_rating;
				$votes = (int) $xml->imdb_rating['num_vote'];
			}
		}
		elseif ($param == 'rt_vote')
		{
			$response = KAComponentHelper::getRemoteData(
				'http://www.rottentomatoes.com/m/' . $id . '/',
				$parser_conf['rottentomatoes']['headers'],
				30,
				array('curl', 'socket')
			);

			$dom = new DOMDocument('1.0', 'utf-8');
			@$dom->loadHTML($response->body);
			$xpath = new DOMXPath($dom);
			$rating = @$xpath->query($parser_conf['rottentomatoes']['patterns']['rating'])->item(0)->nodeValue;
			$score = @$xpath->query($parser_conf['rottentomatoes']['patterns']['score'])->item(0)->nodeValue;
			$rating = (int) $rating;
			$score = (int) $score;

			if (is_numeric($rating) && is_numeric($score))
			{
				$votesum = $rating;
				$votes = $score;
			}
			else
			{
				$message = JText::_('ERROR') . ': ' . JText::_('COM_KA_FIELD_MOVIE_RATES_EMPTY');
				$success = false;
			}
		}
		elseif ($param == 'mc_vote')
		{
			$response = KAComponentHelper::getRemoteData(
				'http://www.metacritic.com/movie/' . $id,
				$parser_conf['metacritic']['headers'],
				30,
				array('curl', 'socket')
			);

			$dom = new DOMDocument('1.0', 'utf-8');
			@$dom->loadHTML($response->body);
			$xpath = new DOMXPath($dom);
			$rating = @$xpath->query($parser_conf['metacritic']['patterns']['rating'])->item(0)->nodeValue;
			$score = @$xpath->query($parser_conf['metacritic']['patterns']['score'])->item(0)->nodeValue;
			$rating = (int) $rating;
			$score = (int) $score;

			if (is_numeric($rating) && is_numeric($score))
			{
				$votesum = $rating;
				$votes = $score;
			}
			else
			{
				$message = JText::_('ERROR') . ': ' . JText::_('COM_KA_FIELD_MOVIE_RATES_EMPTY');
				$success = false;
			}
		}

		echo json_encode(
			array(
				'success'  => $success,
				'votesum'  => $votesum,
				'votes'    => $votes,
				'message'  => $message,
				'movie_id' => $movie_id
			)
		);
	}

	/**
	 * Method to update images with rating from movies sites.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function updateRateImg()
	{
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit', 'com_kinoarhiv.movie'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		// Movie ID from DB
		$id = $app->input->get('id', 0, 'int');
		$votes = $app->input->get('votes', 0, 'int');
		$votesum = $app->input->get('votesum', '', 'string');
		$cmd = $app->input->get('elem', '', 'string');
		$text = array();
		$folder = '';

		if ($cmd == 'rt_vote')
		{
			// Rotten Tomatoes
			$text = array(
				0 => array('fontsize' => 10, 'text' => $votesum . '%', 'color' => '#333333'),
				1 => array('fontsize' => 7, 'text' => '( ' . $votes . ' )', 'color' => '#555555'),
			);
			$folder = 'rottentomatoes';
		}
		elseif ($cmd == 'mc_vote')
		{
			// Metacritic
			$text = array(
				0 => array('fontsize' => 10, 'text' => $votesum, 'color' => '#333333'),
				1 => array('fontsize' => 7, 'text' => $votes . ' Critics', 'color' => '#555555'),
			);
			$folder = 'metacritic';
		}
		elseif ($cmd == 'kp_vote')
		{
			// Kinopoisk
			$text = array(
				0 => array('fontsize' => 10, 'text' => round($votesum, $params->get('vote_summ_precision'), PHP_ROUND_HALF_UP), 'color' => '#333333'),
				1 => array('fontsize' => 7, 'text' => '( ' . $votes . ' )', 'color' => '#555555'),
			);
			$folder = 'kinopoisk';
		}
		elseif ($cmd == 'imdb_vote')
		{
			// IMDb
			$text = array(
				0 => array('fontsize' => 10, 'text' => round($votesum, $params->get('vote_summ_precision'), PHP_ROUND_HALF_UP), 'color' => '#333333'),
				1 => array('fontsize' => 7, 'text' => '( ' . $votes . ' )', 'color' => '#555555'),
			);
			$folder = 'imdb';
		}

		JLoader::register('KAImageHelper', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'image.php');
		$result = KAImageHelper::createRateImage($text);

		if (StringHelper::substr($params->get('media_rating_image_root_www'), 0, 1) == '/')
		{
			$rating_image_www = JUri::root() . StringHelper::substr($params->get('media_rating_image_root_www'), 1);
		}
		else
		{
			$rating_image_www = $params->get('media_rating_image_root_www');
		}

		$document->setMimeEncoding('application/json');

		echo json_encode(
			array(
				'success' => $result['success'],
				'message' => $result['message'],
				'image' => $rating_image_www . '/' . $folder . '/' . $id . '_big.png?' . time()
			)
		);
	}

	/**
	 * Method to save a person for cast and crew list.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	public function saveRelNames()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit', 'com_kinoarhiv.movie'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('relations');
		$result = $model->saveRelNames();

		echo json_encode($result);
	}

	/**
	 * Method to save an award for awards list.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	public function saveRelAwards()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit', 'com_kinoarhiv.movie'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$model = $this->getModel('relations');
		$result = $model->saveRelAwards();

		echo json_encode($result);
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function saveOrder()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('movies');
		$result = $model->saveOrder();

		echo json_encode($result);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function batch()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		if (!$user->authorise('core.create', 'com_kinoarhiv')
			&& !$user->authorise('core.edit', 'com_kinoarhiv.movie')
			&& !$user->authorise('core.edit.state', 'com_kinoarhiv.movie'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('id', array(), 'array');

		if (count($ids) != 0)
		{
			$model = $this->getModel('movies');
			$result = $model->batch();

			if ($result === false)
			{
				KAComponentHelper::renderErrors($model->getErrors(), 'html');
				$this->setRedirect('index.php?option=com_kinoarhiv&view=movies');

				return;
			}
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&view=movies');
	}

	/**
	 * Method to delete premiere(s) from premieres list.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	public function deletePremieres()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('premiere');
		$result = $model->deletePremieres();

		echo json_encode($result);
	}

	/**
	 * Method to delete release(s) from releases list.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	public function deleteReleases()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('release');
		$result = $model->deleteReleases();

		echo json_encode($result);
	}

	/**
	 * Method to encode item alias for using in filesystem paths and url.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function getFilesystemAlias()
	{
		JLoader::register('KAContentHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/content.php');

		$input = JFactory::getApplication()->input;

		echo KAContentHelper::getFilesystemAlias($input->get('form_movie_alias', '', 'string'), $input->get('form_movie_title', '', 'string'));
	}
}
