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

/**
 * Kinoarhiv API class.
 *
 * @since  3.1
 */
class KinoarhivControllerApi extends JControllerLegacy
{
	/**
	 * Get data from DB
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   3.1
	 */
	public function data()
	{
		if ($this->checkAccess() === false)
		{
			throw new Exception('Access denied', 403);
		}

		$model   = $this->getModel('api', '', array('item_state' => array(1)));
		$content = $this->input->get('content', '', 'word');
		$method  = 'get' . ucfirst($content);

		if (method_exists($model, $method))
		{
			$result = $model->$method();
		}
		else
		{
			throw new Exception('Error', 500);
		}

		echo json_encode($result);
	}

	/**
	 * Method to update images with rating from movies sites.
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function updateRatingImage()
	{
		jimport('administrator.components.com_kinoarhiv.libraries.image', JPATH_ROOT);

		$input    = JFactory::getApplication()->input;
		$params   = JComponentHelper::getParams('com_kinoarhiv');
		$document = JFactory::getDocument();

		$document->setMimeEncoding('application/json');
		header('Content-disposition: inline', true);

		// Movie ID from DB
		$id      = $input->get('id', 0, 'int');
		$votes   = $input->get('votes', 0, 'int');
		$votesum = $input->get('votesum', '', 'string');
		$source  = $input->get('source', '', 'word');

		if ($source == 'rottentomatoes')
		{
			$text = array(
				0 => array('fontsize' => 10, 'text' => $votesum . '%', 'color' => '#333333'),
				1 => array('fontsize' => 7, 'text' => '( ' . $votes . ' )', 'color' => '#555555'),
			);
		}
		elseif ($source == 'metacritic')
		{
			$text = array(
				0 => array('fontsize' => 10, 'text' => $votesum, 'color' => '#333333'),
				1 => array('fontsize' => 7, 'text' => $votes . ' Critics', 'color' => '#555555'),
			);
		}
		elseif ($source == 'kinopoisk')
		{
			$text = array(
				0 => array('fontsize' => 10, 'text' => $votesum, 'color' => '#333333'),
				1 => array('fontsize' => 7, 'text' => '( ' . $votes . ' )', 'color' => '#555555'),
			);
		}
		elseif ($source == 'imdb')
		{
			$text = array(
				0 => array('fontsize' => 10, 'text' => $votesum, 'color' => '#333333'),
				1 => array('fontsize' => 7, 'text' => '( ' . $votes . ' )', 'color' => '#555555'),
			);
		}
		elseif ($source == 'myshows')
		{
			$text = array(
				0 => array('fontsize' => 10, 'text' => $votesum, 'color' => '#333333'),
				1 => array('fontsize' => 7, 'text' => '( ' . $votes . ' )', 'color' => '#555555'),
			);
		}
		else
		{
			echo json_encode(
				array(
					'success' => false,
					'message' => 'Unknown source!'
				)
			);

			return;
		}

		$image = new KAImage;
		$result = $image->createRateImage($id, $source, $text);

		if (StringHelper::substr($params->get('media_rating_image_root_www'), 0, 1) == '/')
		{
			$ratingImgWww = JUri::root() . StringHelper::substr($params->get('media_rating_image_root_www'), 1);
		}
		else
		{
			$ratingImgWww = $params->get('media_rating_image_root_www');
		}

		echo json_encode(
			array(
				'success' => $result['success'],
				'message' => $result['message'],
				'image'   => $ratingImgWww . '/' . $source . '/' . $id . '_big.png?' . time()
			)
		);
	}

	/**
	 * Parser API.
	 * For json data for movie by title:
	 * index.php?option=com_kinoarhiv&task=api.parser&action[imdb]=movie.search&title[imdb]={movie title}&format=json
	 * where task = controller.method; action = content_type.method; title = {movie title} or id = {movie id};
	 * lucky = 1|0 - if 1 when we search for first result and redirect to URL listed below. lucky has no effect w/o title variable.
	 * data = columns - data variable contain list of `fields` with data separated by commas. E.g. id,content_rating,plot,budget. If
	 * not set or empty when all data will be returned.
	 * Each variable(action, title/id, data, lucky) is an array for each parser type.
	 *
	 * Request json data by ID: index.php?option=com_kinoarhiv&task=api.parser&action[imdb]=movie.info&id[imdb]={movie id}&format=raw
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   3.1
	 */
	public function parser()
	{
		header_remove('X-Powered-By');
		$document = JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		header('Content-disposition: inline', true);

		jimport('components.com_kinoarhiv.libraries.api.api', JPATH_ROOT);

		$api             = KAApi::getInstance();
		$filter          = JFilterInput::getInstance();
		$parser          = $this->input->get('parser', '', 'word');
		$parserTask      = $this->input->get('parser_task', '', 'cmd');
		$parserTask      = explode('.', $parserTask);
		$parserType      = strtolower($filter->clean($parserTask[0], 'word'));
		$parserAction    = strtolower($filter->clean($parserTask[1], 'word'));
		$id              = $this->input->get('id', '', 'string');
		$method          = 'get' . ucfirst($parserAction);
		$result[$parser] = $api->getParser($parser)->$method($id, $parserType);

		echo json_encode($result);
	}

	/**
	 * Check if user has access to API.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	private function checkAccess()
	{
		if (!KAComponentHelper::checkToken() && !KAComponentHelper::checkToken('get'))
		{
			return false;
		}

		return true;
	}
}
