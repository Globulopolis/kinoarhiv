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

		//$model   = $this->getModel('api');
		$model   = $this->getModel('api', '', array('item_state' => array(1, 0)));
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
	 * Parser API.
	 * For json data for movie by title: index.php?option=com_kinoarhiv&task=api.parser&action[imdb]=movie.search&title[imdb]={movie title}&format=json
	 * where task = controller.method; action = content_type.method; title = {movie title} or id = {movie id};
	 * lucky = 1|0 - if 1 when we search for first result and redirect to URL listed below. lucky has no effect w/o title variable.
	 * data = columns - data variable contain list of `fields` with data separated by commas. E.g. id,content_rating,plot,budget. If
	 * not set or empty when all data will be returned.
	 * Each variable(action, title/id, data, lucky) is an array for each parser type.
	 *
	 * Request json data by ID: index.php?option=com_kinoarhiv&task=api.parser&action[imdb]=movie.info&id[imdb]={movie id}&format=json
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   3.1
	 */
	public function parser()
	{
		if ($this->checkAccess() === false)
		{
			throw new Exception('Access denied', 403);
		}

		jimport('libraries.api.api', JPATH_COMPONENT);

		$api          = KAApi::getInstance();
		$filter       = JFilterInput::getInstance();
		$action       = $this->input->get('action', array(), 'array');
		$title        = $this->input->get('title', array(), 'array');
		$data         = $this->input->get('data', array(), 'array');
		$id           = $this->input->get('id', array(), 'array');
		$first_result = $this->input->get('lucky', array(), 'array');
		$result       = array();

		foreach ($action as $parser => $parser_action)
		{
			$parser         = $filter->clean($parser, 'word');
			$parser_action  = $filter->clean($parser_action, 'cmd');
			$parser_actions = explode('.', $parser_action);

			if (empty($parser) && (array_key_exists(0, $parser_actions) && array_key_exists(1, $parser_actions)))
			{
				break;
			}

			$_data             = isset($data[$parser]) ? $filter->clean($data[$parser], 'string') : '';
			$_title            = isset($title[$parser]) ? $filter->clean($title[$parser], 'string') : '';
			$_id               = isset($id[$parser]) ? $filter->clean($id[$parser], 'string') : '';
			$_first_result     = isset($first_result[$parser]) ? $filter->clean($first_result[$parser], 'int') : 0;
			$parser            = strtolower($parser);
			$parser_actions[0] = strtolower($filter->clean($parser_actions[0], 'word'));
			$parser_actions[1] = strtolower($filter->clean($parser_actions[1], 'word'));
			$method            = 'get' . ucfirst($parser_actions[0]) . ucfirst($parser_actions[1]);

			if ($parser_actions[1] == 'search' && !empty($_title))
			{
				// Get the first result and do search by ID
				if ($_first_result === 1)
				{
					$item_id = $api->getParser($parser)->$method($_title, true);

					if ($item_id !== false)
					{
						$_method = 'get' . ucfirst($parser_actions[0]) . 'Info';
						$result[$parser][$_id] = $api->getParser($parser)->$_method($item_id, $_data);
					}
					else
					{
						$result[$parser][$_id] = array();
					}
				}
				else
				{
					$result[$parser][$_id] = $api->getParser($parser)->$method($_title);
				}
			}
			elseif ($parser_actions[1] == 'info' && !empty($_id))
			{
				$result[$parser][$_id] = $api->getParser($parser)->$method($_id, $_data);
			}
			else
			{
				$result[$parser][$_id] = array('error' => 'Something wrong with an \'action\' query value.');
			}
		}

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
