<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

/**
 * Class KinoarhivModelMediamanagerItem
 *
 * @since  3.0
 */
class KinoarhivModelMediamanagerItem extends JModelForm
{
	/**
	 * Method to save image information into DB. Accept gallery items for movie and poster for trailer.
	 *
	 * @param   string   $section      Section. (can be: movie, name, trailer, soundtrack)
	 * @param   integer  $item_id      Item ID(for trailer it's a trailer ID).
	 * @param   string   $filename     System filename.
	 * @param   array    $image_sizes  Array with the sizes. array(width, height)
	 * @param   mixed    $item_type    Item type.
	 *                                 For movie: 2 - poster, 1 - wallpaper, 3 - screenshot.
	 *                                 For name: 2 - poster, 1 - wallpaper, 3 - photo.
	 *                                 For trailer: null
	 * @param   integer  $frontpage    Item published on frontpage.
	 *
	 * @return  mixed  Last insert ID on success, false otherwise.
	 *
	 * @since   3.0
	 */
	public function saveImageInDB($section, $item_id, $filename, $image_sizes = array(), $item_type = null, $frontpage = 0)
	{
		$app         = JFactory::getApplication();
		$db          = $this->getDbo();
		$image_sizes = (count($image_sizes) == 0) ? array(0 => 0, 1 => 0) : $image_sizes;
		$dimension   = floor($image_sizes[0]) . 'x' . floor($image_sizes[1]);
		$insert_id   = '';

		if ($section == 'movie')
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_movies_gallery'), 'id')
				->columns($db->quoteName(array('id', 'filename', 'dimension', 'movie_id', 'type', 'frontpage', 'state')))
				->values("'', '" . $filename . "', '" . $dimension . "', '" . (int) $item_id . "', '" . (int) $item_type . "', '" . (int) $frontpage . "', '1'");
			$db->setQuery($query);

			try
			{
				$db->execute();
				$insert_id = $db->insertid();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			// Unpublish all items from frontpage and set last one to frontpage
			if ($frontpage == 1)
			{
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_movies_gallery'))
					->set($db->quoteName('frontpage') . " = 0")
					->where($db->quoteName('movie_id') . ' = ' . (int) $item_id . ' AND ' . $db->quoteName('type') . ' = 2')
					->where($db->quoteName('id') . ' != ' . (int) $insert_id);
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');

					return false;
				}
			}
		}
		elseif ($section == 'name')
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_names_gallery'), 'id')
				->columns($db->quoteName(array('id', 'filename', 'dimension', 'name_id', 'type', 'frontpage', 'state')))
				->values("'', '" . $filename . "', '" . $dimension . "', '" . (int) $item_id . "', '" . (int) $item_type . "', '" . (int) $frontpage . "', '1'");
			$db->setQuery($query);

			try
			{
				$db->execute();
				$insert_id = $db->insertid();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			// Unpublish all items from frontpage and set last one to frontpage
			if ($frontpage == 1)
			{
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_names_gallery'))
					->set($db->quoteName('frontpage') . " = 0")
					->where($db->quoteName('name_id') . ' = ' . (int) $item_id . ' AND ' . $db->quoteName('type') . ' = 3')
					->where($db->quoteName('id') . ' != ' . (int) $insert_id);
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');

					return false;
				}
			}
		}
		elseif ($section == 'trailer')
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('screenshot') . " = '" . $db->escape($filename) . "'")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		else
		{
			return false;
		}

		return $insert_id;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure.
	 *
	 * @since   3.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_kinoarhiv.mediamanager', 'mediamanager', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The default data is an array.
	 *
	 * @since   3.0
	 */
	protected function loadFormData()
	{
		$app  = JFactory::getApplication();
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.trailers.' . JFactory::getUser()->id . '.edit_data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			if (empty($data['trailer']) && $app->input->getWord('type', '') == 'trailers')
			{
				$filters = (array) $app->getUserState('com_kinoarhiv.trailers.filter');
				$data['trailer'] = (object) array(
					'state'    => ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null),
					'language' => $app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null)),
					'access'   => $app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access')))
				);
			}
		}

		return $data;
	}

	/**
	 * Method to get a single record for trailer.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.0
	 */
	public function getItem()
	{
		$app = JFactory::getApplication();
		$type = $app->input->getWord('type', '');

		if ($type == 'video' || $type == 'subtitles' || $type == 'chapters' || $type == 'screenshot')
		{
			return $this->getTrailerFiles($type, $app->input->get('item_id', 0, 'int'), $app->input->get('item', '', 'alnum'));
		}

		$db = $this->getDbo();
		$item_id = $app->input->get('item_id', null, 'array');
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array('g.title', 'g.embed_code', 'g.screenshot', 'g.urls', 'g.resolution', 'g.dar', 'g.duration',
					'g.frontpage', 'g.access', 'g.state', 'g.language', 'g.is_movie'
				)
			)
		);

		$query->select($db->quoteName('g.id', 'item_id') . ',' . $db->quoteName('g.movie_id', 'id'))
			->from($db->quoteName('#__ka_trailers', 'g'));

		$query->select($db->quoteName('m.title', 'movie_title'))
			->select($db->quoteName(array('m.alias', 'm.fs_alias')))
			->leftJoin($db->quoteName('#__ka_movies', 'm') . ' ON m.id = g.movie_id');

		$query->select($db->quoteName('l.title', 'language_title'))
			->leftJoin($db->quoteName('#__languages', 'l') . ' ON l.lang_code = g.language');

		$query->select($db->quoteName('ag.title', 'access_level'))
			->leftJoin($db->quoteName('#__viewlevels', 'ag') . ' ON ag.id = g.access');

		$query->where($db->quoteName('g.id') . ' = ' . (int) $item_id[0]);

		$db->setQuery($query);

		try
		{
			$result['trailer'] = $db->loadObject();

			if ($result['trailer'])
			{
				$files = $this->getTrailerFiles('screenshot, video, subtitles, chapters', (int) $item_id[0], '', '');
				$result['trailer']->video = json_encode($files['video']);
				$result['trailer']->subtitles = json_encode($files['subtitles']);
				$result['trailer']->chapters = json_encode($files['chapters']);
			}
		}
		catch (RuntimeException $e)
		{
			return array();
		}

		return $result;
	}

	/**
	 * Method to get a list of gallery files.
	 *
	 * @param   string   $section   Type of the item. Can be 'movie' or 'name'.
	 * @param   string   $type      Type of the section. Can be 'gallery', 'trailers', 'soundtracks'
	 * @param   array    $item_ids  Items.
	 * @param   boolean  $item      Select gallery items by parent ID(movie id or name id). Default - select by gallery
	 *                              item ID($item_ids an gallery IDs).
	 *
	 * @return  object
	 *
	 * @throws  RuntimeException
	 * @since   3.1
	 */
	public function getGalleryFiles($section = '', $type = '', $item_ids = array(), $item = false)
	{
		$db      = $this->getDbo();
		$input   = JFactory::getApplication()->input;
		$section = !empty($section) ? $section : $input->get('section', '', 'word');
		$type    = !empty($type) ? $type : $input->get('type', '', 'word');
		$query   = $db->getQuery(true);

		if ($section == 'movie' && $type == 'gallery')
		{
			$table = '#__ka_movies_gallery';
		}
		elseif ($section == 'name' && $type == 'gallery')
		{
			$table = '#__ka_names_gallery';
		}
		else
		{
			throw new RuntimeException(JText::_('ERROR'), 500);
		}

		$query->select($db->quoteName(array('id', 'filename', $section . '_id', 'type')))
			->from($db->quoteName($table));

		if (!$item)
		{
			$query->where($db->quoteName('id') . ' IN (' . implode(',', $item_ids) . ')');
		}
		else
		{
			$query->where($db->quoteName($section . '_id') . ' IN (' . implode(',', $item_ids) . ')');
		}

		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), 500);
		}

		return $result;
	}

	/**
	 * Method to get a single record for trailer file or list of files. I. e. used in fileinfo_edit template.
	 *
	 * @param   string   $type         Content type. Can be 'video', 'subtitles', 'chapters', 'screenshot' or list separated by commas.
	 * @param   integer  $item_id      Trailer ID
	 * @param   mixed    $item         File ID. If it's an empty value then return all files.
	 * @param   string   $form_prefix  Fields group prefix. See mediamanager.xml form.
	 *
	 * @return  mixed  Array on success, false on failure.
	 *
	 * @since  3.0
	 */
	public function getTrailerFiles($type, $item_id, $item = '', $form_prefix = 'trailer_finfo_')
	{
		$db = $this->getDbo();
		$types = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', trim($type));

		// Return an empty array if we request data for new file.
		$is_new = JFactory::getApplication()->input->getInt('new', 0);

		if ($is_new == 1)
		{
			return array(
				$form_prefix . 'video'      => array(),
				$form_prefix . 'subtitles'  => array(),
				$form_prefix . 'chapters'   => array(),
				$form_prefix . 'screenshot' => array()
			);
		}

		$query = $db->getQuery(true)
			->select($db->quoteName(array('movie_id', 'screenshot', 'video', 'subtitles', 'chapters')))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $item_id);

		$db->setQuery($query);

		try
		{
			$columns = $db->loadAssoc();
		}
		catch (Exception $e)
		{
			return false;
		}

		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$folder = KAContentHelper::getPath('movie', 'trailers', null, $columns['movie_id']);
		$video = json_decode($columns['video'], true);
		$subtitles = json_decode($columns['subtitles'], true);
		$chapters = json_decode($columns['chapters'], true);

		foreach ($video as $v_key => $v_value)
		{
			$video[$v_key] = $v_value;
			$video[$v_key]['is_file'] = (!is_file($folder . $v_value['src'])) ? 0 : 1;
		}

		foreach ($subtitles as $s_key => $s_value)
		{
			$subtitles[$s_key] = $s_value;
			$subtitles[$s_key]['is_file'] = (!is_file($folder . $s_value['file'])) ? 0 : 1;
		}

		foreach ($chapters as $c_key => $c_value)
		{
			$chapters[$c_key] = $c_value;
			$chapters['is_file'] = (!is_file($folder . $c_value)) ? 0 : 1;
		}

		// Return only one result by ID, all otherwise.
		if ($item !== '')
		{
			if ($type == 'video')
			{
				$video = $video[$item];
			}
			elseif ($type == 'subtitles')
			{
				$subtitles = $subtitles[$item];
			}
		}

		$result = array(
			$form_prefix . 'video'      => $video,
			$form_prefix . 'subtitles'  => $subtitles,
			$form_prefix . 'chapters'   => $chapters,
			$form_prefix . 'screenshot' => array(
				'file'    => $columns['screenshot'],
				'is_file' => !is_file($folder . $columns['screenshot']) ? 0 : 1
			)
		);

		if (count($types) > 0)
		{
			foreach ($types as $value)
			{
				$keys[] = $form_prefix . $value;
			}

			$result = array_intersect_key($result, array_flip($keys));
		}

		return $result;
	}

	/**
	 * Remove a file associated with trailer.
	 *
	 * @param   string   $type     Content type
	 * @param   integer  $item_id  Trailer ID
	 * @param   mixed    $item     File ID or array of IDs
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function removeTrailerFiles($type, $item_id, $item = null)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName(array('movie_id', 'screenshot', 'video', 'subtitles', 'chapters')))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $item_id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadAssoc();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		$result = $result[$type];

		if ($type == 'video')
		{
			$result = json_decode($result, true);

			if (!is_array($item))
			{
				unset($result[$item]);
			}
			else
			{
				foreach ($result as $key => $value)
				{
					if (in_array($key, $item))
					{
						unset($result[$key]);
					}
				}
			}

			$result_obj = ArrayHelper::toObject($result);
			$json = json_encode($result_obj);

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('video') . " = '" . $json . "'")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$db->execute();

				return true;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		elseif ($type == 'subtitles')
		{
			$result = json_decode($result, true);

			if (!is_array($item))
			{
				unset($result[$item]);
			}
			else
			{
				foreach ($result as $key => $value)
				{
					if (in_array($key, $item))
					{
						unset($result[$key]);
					}
				}
			}

			$result_obj = ArrayHelper::toObject($result);
			$json = json_encode($result_obj);

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('subtitles') . " = '" . $json . "'")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$db->execute();

				return true;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		elseif ($type == 'chapters')
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('chapters') . " = '{}'")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$db->execute();

				return true;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		elseif ($type == 'screenshot')
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('screenshot') . " = ''")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$db->execute();

				return true;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		else
		{
			$app->enqueueMessage('Wrong type', 'error');

			return false;
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  True on success, False on error, lastInsertID on trailer save.
	 *
	 * @since   3.0
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$type = $app->input->get('type', '', 'word');
		$section = $app->input->get('section', '', 'word');
		$id = $app->input->get('id', 0, 'int');

		if ($section == 'movie')
		{
			if ($type == 'trailers')
			{
				if (empty($data['item_id']))
				{
					// Check if trailer with this title allready exists
					$query = $db->getQuery(true);

					$query->select('COUNT(id)')
						->from($db->quoteName('#__ka_trailers'))
						->where($db->quoteName('title') . " = '" . $db->escape(trim($data['title'])) . "'");

					$db->setQuery($query);
					$count = $db->loadResult();

					if ($count > 0)
					{
						$app->enqueueMessage(JText::_('COM_KA_TRAILERS_EXISTS'), 'error');

						return false;
					}

					$query = $db->getQuery(true);

					$query->insert($db->quoteName('#__ka_trailers'))
						->columns(
							$db->quoteName(
								array(
									'id', 'movie_id', 'title', 'embed_code', 'screenshot', 'urls', 'resolution',
									'dar', 'duration', 'video', 'subtitles', 'chapters', 'frontpage', 'access',
									'state', 'language', 'is_movie'
								)
							)
						)
						->values("'', '" . (int) $id . "', '" . $db->escape(trim($data['title'])) . "', "
							. "'" . $db->escape($data['embed_code']) . "', '', '" . $db->escape($data['urls']) . "', "
							. "'" . $db->escape($data['resolution']) . "', '" . $db->escape($data['dar']) . "', "
							. "'" . $db->escape($data['duration']) . "', '{}', '{}', '{}', '" . (int) $data['frontpage'] . "', "
							. "'" . (int) $data['access'] . "', '" . (int) $data['state'] . "', "
							. "'" . $data['language'] . "', '" . (int) $data['is_movie'] . "'");
				}
				else
				{
					$query = $db->getQuery(true);

					$query->update($db->quoteName('#__ka_trailers'))
						->set($db->quoteName('title') . " = '" . $db->escape($data['title']) . "'," . $db->quoteName('embed_code') . " = '" . $data['embed_code'] . "'")
						->set($db->quoteName('urls') . " = '" . $data['urls'] . "'," . $db->quoteName('resolution') . " = '" . $data['resolution'] . "'")
						->set($db->quoteName('dar') . " = '" . $data['dar'] . "'," . $db->quoteName('duration') . " = '" . $data['duration'] . "'")
						->set($db->quoteName('frontpage') . " = '" . (int) $data['frontpage'] . "'," . $db->quoteName('access') . " = '" . (int) $data['access'] . "'")
						->set($db->quoteName('state') . " = '" . (int) $data['state'] . "'," . $db->quoteName('language') . " = '" . $data['language'] . "'")
						->set($db->quoteName('is_movie') . " = '" . $data['is_movie'] . "'")
						->where($db->quoteName('id') . ' = ' . (int) $data['item_id']);
				}

				$db->setQuery($query);

				try
				{
					$db->execute();

					// We need to store LastInsertID in session for later use in controller.
					if (empty($data['item_id']))
					{
						$session_data = $app->getUserState('com_kinoarhiv.trailers.' . $user->id . '.edit_data');
						$session_data['trailer']['item_id'] = $db->insertid();
						$app->setUserState('com_kinoarhiv.trailers.' . $user->id . '.edit_data', $session_data);
					}
				}
				catch (Exception $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');

					return false;
				}
			}
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Removes an item.
	 *
	 * @param   string   $section  Type of the item. Can be 'movie' or 'name'.
	 * @param   string   $type     Type of the section. Can be 'gallery', 'trailers', 'soundtracks'
	 * @param   integer  $tab      Tab number from gallery(or empty value for 'trailers', 'soundtracks').
	 * @param   integer  $id       The item ID (movie or name).
	 * @param   array    $ids      Array of IDs to remove(file id).
	 *
	 * @return  boolean   True on success, false on error.
	 *
	 * @since   3.0
	 */
	public function remove($section, $type, $tab = 0, $id = 0, $ids = array())
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();

		if ($section == 'movie')
		{
			if ($type == 'gallery')
			{
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__ka_movies_gallery'))
					->where($db->quoteName('movie_id') . ' = ' . (int) $id)
					->where($db->quoteName('type') . ' = ' . (int) $tab)
					->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
			}
			elseif ($type == 'trailers')
			{
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__ka_trailers'))
					->where($db->quoteName('movie_id') . ' = ' . (int) $id)
					->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
			}
			else
			{
				$app->enqueueMessage('Wrong type', 'error');

				return false;
			}
		}
		elseif ($section == 'name')
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_names_gallery'))
				->where($db->quoteName('name_id') . ' = ' . (int) $id)
				->where($db->quoteName('type') . ' = ' . (int) $tab)
				->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
		}
		else
		{
			$app->enqueueMessage('Wrong section', 'error');

			return false;
		}

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Saves the manually set order of files on trailers edit page.
	 *
	 * @param   integer  $item_id  Trailer ID
	 * @param   array    $items    Array of items to sort.
	 * @param   string   $type     Content type.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function saveOrderTrailerFiles($item_id, $items, $type)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName($type))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . $item_id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadResult();

			if (empty($result))
			{
				return false;
			}
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		$result_arr = json_decode($result, true);
		$new_arr = (object) array();

		foreach ($items as $new_index => $old_index)
		{
			foreach ($result_arr as $value)
			{
				$new_arr->$new_index = $result_arr[$old_index];
			}
		}

		$query = $db->getQuery(true)
			->update($db->quoteName('#__ka_trailers'))
			->set($db->quoteName($type) . " = '" . json_encode($new_arr) . "'")
			->where($db->quoteName('id') . ' = ' . (int) $item_id);

		$db->setQuery($query);

		try
		{
			$db->execute();

			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Set and save default subtitle for trailer.
	 *
	 * @param   boolean  $isDefault  Action state
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function subtitleSetDefault($isDefault)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$id = $app->input->get('id', 0, 'int');

		// Should be null because items index can start from zero.
		$item = $app->input->get('item', null, 'int');

		if (empty($id) || is_null($item))
		{
			$app->enqueueMessage(JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_NOTSAVED'), 'error');

			return false;
		}

		$query = $db->getQuery(true)
			->select($db->quoteName('subtitles'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . $id);

		$db->setQuery($query);
		$result = $db->loadResult();

		if (empty($result))
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

			return false;
		}

		$result_arr = json_decode($result);

		foreach ($result_arr as $key => $value)
		{
			// Set all other items to false except selected
			if ($key != $item)
			{
				$result_arr->$key->default = false;
			}
			else
			{
				// Unset 'default' state from item which allready have 'default' state
				if ($isDefault === false)
				{
					$result_arr->$key->default = false;
				}
				else
				{
					$result_arr->$key->default = true;
				}
			}
		}

		$query = $db->getQuery(true)
			->update($db->quoteName('#__ka_trailers'))
			->set($db->quoteName('subtitles') . " = '" . json_encode($result_arr) . "'")
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$db->execute();

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

			return false;
		}
	}

	/**
	 * Method to save file info data.
	 * Used in upload and edit fileinfo dialog.
	 *
	 * @param   array  $data     The form data.
	 * @param   array  $options  Request vars.
	 *
	 * @return  boolean
	 *
	 * @since  3.1
	 */
	public function saveFileinfoData($data, $options = array())
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$type = array_key_exists('list', $options) ? $options['list'] : $app->input->get('list', '', 'word');
		$id = $app->input->get('id', 0, 'int');
		$item_id = $app->input->get('item_id', 0, 'int');
		$item = $app->input->get('item', null, 'int');
		$is_new = array_key_exists('new', $options) ? $options['new'] : $app->input->get('new', 0, 'int');

		// Select existing data
		$query = $db->getQuery(true)
			->select($db->quoteName($type))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $item_id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadResult();

			if (empty($result))
			{
				$is_new = 1;
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		if ($type == 'video')
		{
			$files = json_decode($result, true);
			$new_filename = JPath::clean($data['src']);
			$file = array(
				'src'        => $new_filename,
				'type'       => $data['type'],
				'resolution' => trim($data['resolution'])
			);

			if ($is_new == 0)
			{
				$old_filename = JPath::clean($files[$item]['src']);
				$files[$item] = $file;
			}
			else
			{
				$files[] = $file;
			}

			$file_obj = json_encode((object) $files);
		}
		elseif ($type == 'subtitles')
		{
			$files = json_decode($result, true);
			$new_filename = JPath::clean($data['file']);
			$file = array(
				'file'      => $new_filename,
				'lang'      => trim($data['lang']),
				'lang_code' => $data['lang_code'],
				'default'   => (bool) $data['default']
			);

			if ($is_new == 0)
			{
				$old_filename = JPath::clean($files[$item]['file']);
				$files[$item] = $file;
			}
			else
			{
				$files[] = $file;

				// Subtract 1 because foreach() count from 0.
				$item = count($files) - 1;
			}

			// Process 'default' flag for all subtitles
			foreach ($files as $key => $_item)
			{
				// Set default=false for all subtitles except current edited.
				if ($key !== $item)
				{
					$files[$key]['default'] = false;
				}
			}

			$file_obj = json_encode((object) $files);
		}
		elseif ($type == 'chapters')
		{
			$files = json_decode($result, true);

			if ($is_new == 0)
			{
				$old_filename = JPath::clean($files['file']);
			}

			$new_filename = JPath::clean($data['file']);
			$files['file'] = $new_filename;
			$file_obj = json_encode((object) $files);
		}
		elseif ($type == 'screenshot')
		{
			if ($is_new == 0)
			{
				$old_filename = JPath::clean($result);
			}

			$new_filename = JPath::clean($data['file']);
			$file_obj = $new_filename;
		}
		else
		{
			$app->enqueueMessage('Unknow content type', 'error');

			return false;
		}

		if ($is_new == 0)
		{
			// Rename the file
			$path = KAContentHelper::getPath('movie', 'trailers', null, $id);

			if ($old_filename !== $new_filename)
			{
				if (is_file($path . $old_filename))
				{
					if (@rename($path . $old_filename, $path . $new_filename) === false)
					{
						$app->enqueueMessage('Error while renaming file!', 'error');
					}
				}
				else
				{
					$app->enqueueMessage('Error while renaming file! File ' . $old_filename . ' not found', 'error');
				}
			}
		}

		$query = $db->getQuery(true)
			->update($db->quoteName('#__ka_trailers'))
			->set($db->quoteName($type) . " = '" . $db->escape($file_obj) . "'")
			->where($db->quoteName('id') . ' = ' . (int) $item_id);

		$db->setQuery($query);

		try
		{
			$db->execute();

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * Method to copy gallery items from one item to another.
	 *
	 * @param   string   $section   Section. (can be: movie, name, soundtrack)
	 * @param   string   $type      Item type.
	 * @param   integer  $from_id   ID to copy from.
	 * @param   string   $to_id     ID where to copy.
	 * @param   string   $from_tab  Content type.
	 *
	 * @return  boolean   True on success, false on error.
	 *
	 * @since   3.0
	 */
	public function copyfrom($section, $type, $from_id, $to_id, $from_tab)
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$query_result = true;

		// Update DB
		if ($type == 'gallery')
		{
			if ($section == 'movie')
			{
				$table = '#__ka_movies_gallery';
				$col = 'movie_id';
			}
			elseif ($section == 'name')
			{
				$table = '#__ka_names_gallery';
				$col = 'name_id';
			}
			else
			{
				return false;
			}

			// Get all table columns from $table
			$cols_obj = $db->getTableColumns($table);
			$_keys = $db->quoteName(array_keys($cols_obj));
			$cols = implode(', ', $_keys);
			$cols_count = count($_keys);

			// Get all rows $table
			$query = $db->getQuery(true)
				->select($cols)
				->from($db->quoteName($table))
				->where($db->quoteName($col) . ' = ' . (int) $from_id . ' AND ' . $db->quoteName('type') . ' = ' . (int) $from_tab);

				// Remove from result set rows with the same filename(avoid duplicates).
				$subquery = $db->getQuery(true)
					->select($db->quoteName('filename'))
					->from($db->quoteName($table))
					->where($db->quoteName($col) . ' = ' . (int) $to_id . ' AND ' . $db->quoteName('type') . ' = ' . (int) $from_tab);

			$query->where($db->quoteName('filename') . 'NOT IN (' . $subquery . ')');

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$db->setDebug(true);
			$db->lockTable($table);
			$db->transactionStart();

			foreach ($rows as $values)
			{
				$value = "";
				$i = 0;

				foreach ($values as $key => $val)
				{
					if ($key == 'id')
					{
						$value .= "''";
					}
					else
					{
						if ($key == $col)
						{
							$value .= "'" . (int) $to_id . "'";
						}
						else
						{
							$value .= "'" . $db->escape($val) . "'";
						}
					}

					if ($i + 1 != $cols_count)
					{
						$value .= ', ';
					}

					$i++;
				}

				$query = $db->getQuery(true)
					->insert($db->quoteName($table))
					->columns($cols)
					->values($value);

				$db->setQuery($query . ';');

				if ($db->execute() === false)
				{
					$query_result = false;
					break;
				}
			}

			if ($query_result === false)
			{
				$db->transactionRollback();
			}
			else
			{
				$db->transactionCommit();
			}

			$db->unlockTables();
			$db->setDebug(false);
		}
		else
		{
			$app->enqueueMessage('Wrong item type', 'error');

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
	 * @return  mixed   Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		// Include the plugins for the delete events.
		JPluginHelper::importPlugin($this->events_map['validate']);

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onUserBeforeDataValidation', array($form, &$data));

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
