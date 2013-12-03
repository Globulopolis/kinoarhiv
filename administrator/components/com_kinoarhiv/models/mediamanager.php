<?php defined('_JEXEC') or die;

class KinoarhivModelMediamanager extends JModelList {
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'g.id',
				'filename', 'g.filename',
				'dimension', 'g.dimension',
				'state', 'g.state',
				'poster_frontpage', 'g.poster_frontpage', 'g.frontpage',
				'access', 'g.access', 'access_level',
				'language', 'g.language');
		}

		parent::__construct($config);
	}

	public function getPath($section='', $type='', $tab=0, $id=0) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$section = empty($section) ? $app->input->get('section', '', 'word') : $section;
		$type = empty($type) ? $app->input->get('type', '', 'word') : $type;
		$tab = empty($tab) ? $app->input->get('tab', 0, 'int') : $tab;
		$id = empty($id) ? $app->input->get('id', 0, 'int') : $id;

		if ($section == 'movie') {
			$table = '#__ka_movies';

			if ($type == 'gallery') {
				if ($tab == 1) {
					$path = $params->get('media_wallpapers_root');
					$folder = 'wallpapers';
				} elseif ($tab == 2) {
					$path = $params->get('media_posters_root');
					$folder = 'posters';
				} elseif ($tab == 3) {
					$path = $params->get('media_scr_root');
					$folder = 'screenshots';
				}
			} elseif ($type == 'trailers') {
				$path = $params->get('media_trailers_root');
				$folder = '';
			}
		} elseif ($section == 'names') {
			$table = '#__ka_names';
		}

		$db->setQuery("SELECT `alias` FROM ".$db->quoteName($table)." WHERE `id` = ".(int)$id);
		$alias = $db->loadResult();

		$result = $path.DIRECTORY_SEPARATOR.JString::substr($alias, 0, 1).DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.$folder;

		return $result;
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout')) {
			$this->context .= '.' . $layout;
		}

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// force a language
		$forcedLanguage = $app->input->get('forcedLanguage');
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}

		// List state information.
		parent::populateState('g.id', 'asc');
	}

	public function getListQuery() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$query = $db->getQuery(true);
		$section = $app->input->get('section', '', 'word');
		$type = $app->input->get('type', '', 'word');
		$tab = $app->input->get('tab', 0, 'int');
		$id = $app->input->get('id', 0, 'int');

		if ($section == 'movie') {
			if ($type == 'gallery') {
				$query->select(
					$this->getState(
						'list.select',
						'`g`.`id`, `g`.`filename`, `g`.`dimension`, `g`.`movie_id`, `g`.`poster_frontpage`, `g`.`state`, `m`.`alias`'
					)
				);
				$query->from($db->quoteName('#__ka_movies_gallery').' AS `g`');
				$query->leftJoin($db->quoteName('#__ka_movies').' AS `m` ON `m`.`id` = `g`.`movie_id`');
				$query->where('`g`.`type` = '.$tab.' AND (`g`.`state` = 0 OR `g`.`state` = 1) AND `g`.`movie_id` = '.(int)$id);

				$orderCol = $this->state->get('list.ordering', 'g.id');
				$orderDirn = $this->state->get('list.direction', 'desc');
				$query->order($db->escape($orderCol . ' ' . strtoupper($orderDirn)));
			} elseif ($type == 'trailers') {
				$query->select(
					$this->getState(
						'list.select',
						'`g`.`id`, `g`.`title`, `g`.`embed_code`, `g`.`filename`, `g`.`w_h`, `g`.`duration`, `g`.`_captions`, `g`.`_subtitles`, `g`.`_chapters`, `g`.`frontpage`, `g`.`state`, `g`.`language`, `g`.`is_movie`'
					)
				);
				$query->from($db->quoteName('#__ka_trailers').' AS `g`');

				// Join over the language
				$query->select(' `l`.`title` AS `language_title`')
					->leftJoin($db->quoteName('#__languages') . ' AS `l` ON `l`.`lang_code` = `g`.`language`');

				// Join over the movie
				$query->select(' `m`.`alias`')
					->leftJoin($db->quoteName('#__ka_movies').' AS `m` ON `m`.`id` = `g`.`movie_id`');

				// Join over the asset groups.
				$query->select(' ag.title AS access_level')
					->leftJoin('#__viewlevels AS ag ON ag.id = g.access');

				$query->where('(`g`.`state` = 0 OR `g`.`state` = 1) AND `g`.`movie_id` = '.(int)$id);

				// Add the list ordering clause.
				$orderCol = $this->state->get('list.ordering', 'g.id');
				$orderDirn = $this->state->get('list.direction', 'desc');

				//sqlsrv change
				if ($orderCol == 'language') {
					$orderCol = 'l.title';
				}
				if ($orderCol == 'access_level') {
					$orderCol = 'ag.title';
				}
				$query->order($db->escape($orderCol . ' ' . $orderDirn));
			} elseif ($type == 'sounds') {
				$query = null;
			}
		} else {
			$query = null;
		}

		return $query;
	}

	public function saveImageInDB($image, $filename, $image_sizes, $type, $id) {
		$db = $this->getDBO();

		$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_movies_gallery')." (`id`, `filename`, `dimension`, `movie_id`, `type`, `poster_frontpage`, `state`)"
			. "\n VALUES ('', '".$filename."', '".$image_sizes[0].'x'.$image_sizes[1]."', '".(int)$id."', '".(int)$type."', '0', '1')");
		$result = $db->execute();

		return $result;
	}

	/**
	 * Method to publish or unpublish posters or trailer on movie info page(not on posters or trailers page)
	 *
	 * @param	int		 $action		  0 - unpublish from frontpage, 1 - publish poster on frontpage
	 *
	 * @return array
	 *
	 */
	public function publishOnFrontpage($action) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$type = $app->input->get('type', '', 'word');
		$movie_id = $app->input->get('id', 0, 'int');
		$id = $app->input->get('_id', array(), 'array');

		if ($type == 'gallery') {
			// Reset all values to 0
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies_gallery')." SET `poster_frontpage` = '0' WHERE `movie_id` = ".(int)$movie_id." AND `type` = 2");
			$db->execute();

			if (!isset($id[0]) || empty($id[0])) {
				$this->setError('Unknown ID');
				return $this->getError();
			}

			$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies_gallery')." SET `poster_frontpage` = '".(int)$action."' WHERE `id` = ".(int)$id[0]);
			$db->execute();
		} elseif ($type == 'trailers') {
			// We need to check if this is the movie to avoid errors when publishing a movie and trailer
			$db->setQuery("SELECT `is_movie` FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".(int)$id[0]);
			$is_movie = $db->loadResult();

			if ($is_movie == 0) {
				// Reset all values to 0
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `frontpage` = '0' WHERE `movie_id` = ".(int)$movie_id." AND `is_movie` = 0");
				$db->execute();
			} else {
				// Reset all values to 0
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `frontpage` = '0' WHERE `movie_id` = ".(int)$movie_id." AND `is_movie` = 1");
				$db->execute();
			}

			if (!isset($id[0]) || empty($id[0])) {
				$this->setError('Unknown ID');
				return $this->getError();
			}

			$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `frontpage` = '".(int)$action."' WHERE `id` = ".(int)$id[0]);
			$db->execute();
		}

		return $this->getError();
	}

	public function publish($action) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$type = $app->input->get('type', '', 'word');
		$movie_id = $app->input->get('id', 0, 'int');
		$id = $app->input->get('_id', array(), 'array');

		if ($type == 'gallery') {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies_gallery')." SET `state` = '".(int)$action."' WHERE `id` IN (".implode(',', $id).")");
			$db->execute();
		} elseif ($type == 'trailers') {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `state` = '".(int)$action."' WHERE `id` IN (".implode(',', $id).")");
			$db->execute();
		}

		return $this->getError();
	}

	public function remove() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$movie_id = $app->input->get('id', 0, 'int');
		$ids = $app->input->get('_id', array(), 'array');
		$type = $app->input->get('type', '', 'word');
		$tab = $app->input->get('tab', 0, 'int');
		$query = true;

		// A code block to remove files from the disk to be in the beginning.
		$db->setQuery("SELECT `g`.`id`, `g`.`filename`, `g`.`movie_id`, `m`.`alias`"
			. "\n FROM ".$db->quoteName('#__ka_movies_gallery')." AS `g`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_movies')." AS `m` ON `m`.`id` = `g`.`movie_id`"
			. "\n WHERE `g`.`id` IN (".implode(',', $ids).")");
		$filenames = $db->loadObjectList();

		if ($app->input->get('section', '', 'word') == 'movie') {
			if ($type == 'gallery') {
				if ($tab == 1) {
					$path = $params->get('media_wallpapers_root');
					$folder = 'wallpapers';
				} elseif ($tab == 2) {
					$path = $params->get('media_posters_root');
					$folder = 'posters';
				} elseif ($tab == 3) {
					$path = $params->get('media_scr_root');
					$folder = 'screenshots';
				}
			}
		}

		$errors = array();
		foreach ($filenames as $filename) {
			$_path = $path.DIRECTORY_SEPARATOR.JString::substr($filename->alias, 0, 1).DIRECTORY_SEPARATOR.$filename->movie_id.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$filename->filename;
			$_th_path = $path.DIRECTORY_SEPARATOR.JString::substr($filename->alias, 0, 1).DIRECTORY_SEPARATOR.$filename->movie_id.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.'thumb_'.$filename->filename;

			// Remove original image
			if (file_exists($_path) && is_file($_path)) {
				if (!unlink($_path)) {
					$errors[] = '<strong>ID: '.$filename->id.'; '.$filename->filename.'</strong>: Error deleting the image file.';
				}
			} else {
				$errors[] = '<strong>ID: '.$filename->id.'; '.$filename->filename.'</strong>: The image file doesn\'t exists.';
			}

			// Remove thumbnail
			if (file_exists($_th_path) && is_file($_th_path)) {
				if (!unlink($_th_path)) {
					$errors[] = '<strong>ID: '.$filename->id.'; thumb_'.$filename->filename.'</strong>: Error deleting the thumbnail image file.';
				}
			} else {
				$errors[] = '<strong>ID: '.$filename->id.'; thumb_'.$filename->filename.'</strong>: The thumbnail image file doesn\'t exists.';
			}

			$this->setError($errors);
		}

		// Deleting files from the database
		$db->setDebug(true);
		$db->lockTable('#__ka_movies_gallery');
		$db->transactionStart();

		foreach ($ids as $row_id) {
			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_movies_gallery')." WHERE `id` = ".(int)$row_id.";");
			$result = $db->execute();

			if ($result === false) {
				$query = false;
				break;
			}
		}

		if ($query === false) {
			$db->transactionRollback();
		} else {
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		return $this->getError();
	}

	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false) {
		// Handle the optional arguments.
		$options['control'] = JArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear) {
			return $this->_forms[$hash];
		}

		// Get the form.
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

		try {
			$form = JForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data']) {
				// Get the data for the form.
				$data = $this->loadFormData();
			} else {
				$data = array();
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Load the data into the form after the plugins have operated.
			$form->bind($data);

		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}

	protected function preprocessForm(JForm $form, $data, $group = 'content') {
		// Import the appropriate plugin group.
		JPluginHelper::importPlugin($group);

		// Get the dispatcher.
		$dispatcher = JEventDispatcher::getInstance();

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onContentPrepareForm', array($form, $data));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true)) {
			// Get the last error.
			$error = $dispatcher->getError();

			if (!($error instanceof Exception)) {
				throw new Exception($error);
			}
		}
	}

	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.mediamanager', 'mediamanager', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		return $this->getItem();
	}

	public function getItem($pk = null) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$query = $db->getQuery(true);
		$id = $app->input->get('item_id', 0, 'int');

		$query->select(
			$this->getState(
				'list.select',
				'`g`.`id`, `g`.`movie_id`, `g`.`title`, `g`.`embed_code`, `g`.`filename`, `g`.`w_h`, `g`.`duration`, `g`.`_captions`, `g`.`_subtitles`, `g`.`_chapters`, `g`.`frontpage`, `g`.`access`, `g`.`state`, `g`.`language`, `g`.`is_movie`'
			)
		);
		$query->from($db->quoteName('#__ka_trailers').' AS `g`');

		$query->select(' `l`.`title` AS `language_title`')
			->leftJoin($db->quoteName('#__languages') . ' AS `l` ON `l`.`lang_code` = `g`.`language`');

		$query->select(' ag.title AS access_level')
			->leftJoin('#__viewlevels AS ag ON ag.id = g.access');

		$query->where('`g`.`id` = '.$id);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	public function saveOrderTrailerVideofile() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$item_id = $app->input->get('item_id', 0, 'int');
		$items = $app->input->get('ord', array(), 'array');
		$success = true;
		$message = '';

		$db->setQuery("SELECT `filename` FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".$item_id);
		$result = $db->loadResult();

		if (empty($result)) {
			return json_encode(array('success'=>false, 'message'=>JText::_('JERROR')));
		}

		$result_arr = json_decode($result, true);
		$new_arr = (object)array();

		foreach ($items as $new_index=>$old_index) {
			foreach ($result_arr as $value) {
				$new_arr->$new_index = $result_arr[$old_index];
			}
		}

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `filename` = '".json_encode($new_arr)."' WHERE `id` = ".(int)$item_id);
		$query = $db->execute();

		if ($query !== true) {
			return json_encode(array('success'=>false, 'message'=>JText::_('JERROR')));
		}

		return json_encode(array('success'=>$success, 'message'=>$message));
	}

	public function removeTrailerVideofile() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = $app->input->get('id', 0, 'int');
		$item_id = $app->input->get('item_id', 0, 'int');
		$filename = $app->input->get('file', '', 'string');
		$success = true;
		$message = '';

		$db->setQuery("SELECT `filename` FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".$item_id);
		$result = $db->loadResult();

		if (empty($result)) {
			return json_encode(array('success'=>false, 'message'=>JText::_('JERROR')));
		}

		$result_arr = json_decode($result, true);
		$new_arr = array();

		foreach ($result_arr as $k=>$v) {
			if ($v != $filename) {
				$new_arr[] = $v;
			}
		}

		$new_arr = JArrayHelper::toObject($new_arr);

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `filename` = '".json_encode($new_arr)."' WHERE `id` = ".(int)$item_id);
		$query = $db->execute();

		if ($query !== true) {
			return json_encode(array('success'=>false, 'message'=>JText::_('JERROR')));
		}

		// Removing file
		if (unlink($this->getPath('movie', 'trailers', 0, $id).$filename) !== true) {
			$success = false;
			$message = JText::_('JERROR');
		}

		return json_encode(array('success'=>$success, 'message'=>$message));
	}
}
