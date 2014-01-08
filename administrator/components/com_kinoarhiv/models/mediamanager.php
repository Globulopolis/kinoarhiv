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

		$result = str_replace('\\', '/', $path.DIRECTORY_SEPARATOR.JString::substr($alias, 0, 1).DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.$folder);

		return $result;
	}

	public function getAlias($section, $id) {
		$db = $this->getDBO();
		$id = empty($id) ? $app->input->get('id', 0, 'int') : $id;
		$section = empty($section) ? $app->input->get('section', '', 'word') : $section;

		if ($section == 'movie') {
			$table = '#__ka_movies';
		} elseif ($section == 'names') {
			$table = '#__ka_names';
		}

		$db->setQuery("SELECT `alias` FROM ".$db->quoteName($table)." WHERE `id` = ".(int)$id);
		$alias = $db->loadResult();

		return $alias;
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
						'`g`.`id`, `g`.`title`, `g`.`embed_code`, `g`.`filename`, `g`.`w_h`, `g`.`duration`, `g`.`_subtitles`, `g`.`_chapters`, `g`.`frontpage`, `g`.`state`, `g`.`language`, `g`.`is_movie`'
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

	public function getItems() {
		$items = parent::getItems();
		$app = JFactory::getApplication();

		if ($app->isSite()) {
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++) {
				//Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups)) {
					unset($items[$x]);
				}
			}
		}

		return $items;
	}

	public function getItem($pk = null) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$query = $db->getQuery(true);
		$id = $app->input->get('item_id', 0, 'int');

		$query->select(
			$this->getState(
				'list.select',
				'`g`.`id`, `g`.`movie_id`, `g`.`title`, `g`.`embed_code`, `g`.`screenshot`, `g`.`filename`, `g`.`w_h`, `g`.`duration`, `g`.`_subtitles`, `g`.`_chapters`, `g`.`frontpage`, `g`.`access`, `g`.`state`, `g`.`language`, `g`.`is_movie`'
			)
		);
		$query->from($db->quoteName('#__ka_trailers').' AS `g`');

		$query->select(' `m`.`alias`')
			->leftJoin($db->quoteName('#__ka_movies').' AS `m` ON `m`.`id` = `g`.`movie_id`');

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
			return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
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
			return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		return json_encode(array('success'=>$success, 'message'=>$message));
	}

	public function saveDefaultTrailerSubtitlefile() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$item_id = $app->input->get('item_id', 0, 'int');
		$id = $app->input->get('default', 0, 'int'); // Item ID in array of subtitles which should be default
		$success = true;
		$message = '';

		$db->setQuery("SELECT `_subtitles` FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".$item_id);
		$result = $db->loadResult();

		if (empty($result)) {
			return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		$result_arr = json_decode($result);

		foreach ($result_arr as $key=>$value) {
			$result_arr->$key->default = ($key != $id) ? (bool)false : (bool)true;
		}

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `_subtitles` = '".json_encode($result_arr)."' WHERE `id` = ".(int)$item_id);
		$query = $db->execute();

		if ($query !== true) {
			return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		return json_encode(array('success'=>$success, 'message'=>$message));
	}

	public function saveOrderTrailerSubtitlefile() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$item_id = $app->input->get('item_id', 0, 'int');
		$items = $app->input->get('cord', array(), 'array');
		$success = true;
		$message = '';

		$db->setQuery("SELECT `_subtitles` FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".$item_id);
		$result = $db->loadResult();

		if (empty($result)) {
			return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		$result_arr = json_decode($result, true);
		$new_arr = (object)array();

		foreach ($items as $new_index=>$old_index) {
			foreach ($result_arr as $value) {
				$new_arr->$new_index = $result_arr[$old_index];
			}
		}

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `_subtitles` = '".json_encode($new_arr)."' WHERE `id` = ".(int)$item_id);
		$query = $db->execute();

		if ($query !== true) {
			return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		return json_encode(array('success'=>$success, 'message'=>$message));
	}

	/**
	 * Save info about chapter file into DB
	 *
	 * @param	string	$file		  	Filename
	 * @param	int		$trailer_id 	ID of the trailer
	 * @param	int		$movie_id 		ID of the movie
	 *
	 * @return	mixed	Last insert ID on INSERT or true on UPDATE
	 */
	public function saveChapters($file, $trailer_id, $movie_id) {
		$db = $this->getDBO();

		$db->setQuery("SELECT COUNT(`id`) FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".(int)$trailer_id);
		$total = $db->loadResult();

		$chapters = array('file'=>$file);

		if ($total == 0) {
			$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_trailers')." (`id`, `movie_id`, `title`, `embed_code`, `screenshot`, `filename`, `w_h`, `duration`, `_subtitles`, `_chapters`, `frontpage`, `access`, `state`, `language`, `is_movie`)"
				. "\n VALUES ('', '".(int)$movie_id."', '', '', '', '{}', '', '00:00:00', '{}', '".$chapters."', '0', '1', '0', 'language', '0')");
			$query = $db->execute();

			return $query ? (int)$db->insertid() : false;
		} else {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `_chapters` = '".json_encode($chapters)."' WHERE `id` = ".(int)$trailer_id);
			$query = $db->execute();

			return $query ? true : false;
		}
	}

	public function getSubtitleEdit() {
		JLoader::register('KALanguage', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'language.php');
		$language = new KALanguage();

		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$lang_list = $language::listOfLanguages();
		$trailer_id = $app->input->get('trailer_id', 0, 'int');
		$subtitle_id = $app->input->get('subtitle_id', 0, 'int');

		$db->setQuery("SELECT `_subtitles` FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".(int)$trailer_id);
		$result = $db->loadResult();

		$subtl_obj = json_decode($result);

		return array(
			'langs'=>$lang_list,
			'lang_code'=>$subtl_obj->$subtitle_id->lang_code,
			'lang'=>$subtl_obj->$subtitle_id->lang,
			'is_default'=>$subtl_obj->$subtitle_id->default,
			'trailer_id'=>$trailer_id,
			'subtitle_id'=>$subtitle_id
		);
	}

	public function saveSubtitles($edit=false, $file='', $trailer_id, $movie_id=0, $subtitle_id=null) {
		JLoader::register('KALanguage', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'language.php');
		$language = new KALanguage();
		$lang_list = $language::listOfLanguages();

		$app = JFactory::getApplication();
		$db = $this->getDBO();

		$db->setQuery("SELECT `_subtitles` FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".(int)$trailer_id);
		$result = $db->loadResult();

		if ($edit === true) {
			$subtl_obj = json_decode($result);
			$lang_data = json_decode($app->input->get('language', '', 'string'));
			$default = $app->input->get('default', 'false', 'string');
			$desc = $app->input->get('desc', '', 'string');
			$desc = $desc != '' ? ' '.$desc : '';

			if (isset($subtl_obj->$subtitle_id)) {
				if ($default == 'true') {
					// Set to false all 'default' flags
					foreach ($subtl_obj as $key=>$subtl) {
						$subtl_obj->$key->default = false;
					}

					$subtl_obj->$subtitle_id->default = true;
				}

				$subtl_obj->$subtitle_id->lang_code = $lang_data->lang_code;
				$subtl_obj->$subtitle_id->lang = $lang_data->lang.$desc;

				$alias = $this->getAlias('movie', $movie_id);
				$rn_dest_dir = $this->getPath('movie', 'trailers', 0, $movie_id);
				$old_filename = $rn_dest_dir.$subtl_obj->$subtitle_id->file;
				$ext = pathinfo($old_filename, PATHINFO_EXTENSION);
				$rn_filename = $alias.'-'.$trailer_id.'.subtitles.'.$lang_data->lang_code.'.'.$ext;
				$subtl_obj->$subtitle_id->file = $rn_filename;

				rename($old_filename, $rn_dest_dir.$rn_filename);
			}

			$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `_subtitles` = '".$db->escape(json_encode($subtl_obj))."' WHERE `id` = ".(int)$trailer_id);
			$query = $db->execute();

			return $query ? true : false;
		} else {
			// On 'else' condition we do nothing because no information about trailer exists in DB. In this situation files will be successfully uploaded, but not saved in DB.
			if (!empty($result) && count(json_decode($result)) > 0) {
				if (preg_match('#subtitles\.(.*?)\.#si', $file, $matches)) {
					$lang_code = strtolower($matches[1]);
				}

				$subtl_arr = json_decode($result, true);
				// Checking if lang allready exists and return false
				$lang_exists = false;
				foreach ($subtl_arr as $k=>$v) {
					if ($v['lang_code'] == $lang_code) {
						$lang_exists = true;
						return;
					}
				}

				if ($lang_exists) {
					return false;
				}

				$subtl_arr[] = array(
					'default'=> false,
					'lang_code'=>$lang_code,
					'lang'=>$lang_list[$lang_code],
					'file'=>$file
				);

				$subtl_obj = JArrayHelper::toObject($subtl_arr);
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `_subtitles` = '".$db->escape(json_encode($subtl_obj))."' WHERE `id` = ".(int)$trailer_id);
				$query = $db->execute();
			}
		}
	}

	public function create_screenshot() {
		JLoader::register('KAMedia', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'media.php');
		$media = KAMedia::getInstance();
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', 0, 'int');
		$trailer_id = $app->input->get('item_id', 0, 'int');
		$time = $app->input->get('time', '', 'string');

		$db->setQuery("SELECT `tr`.`screenshot`, `tr`.`filename`, `m`.`alias`"
			. "\n FROM ".$db->quoteName('#__ka_trailers')." AS `tr`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_movies')." AS `m` ON `m`.`id` = `tr`.`movie_id`"
			. "\n WHERE `tr`.`id` = ".(int)$trailer_id);
		$result = $db->loadObject();
		$files = json_decode($result->filename, true);

		$data = array(
			'folder'=>$this->getPath('movie', 'trailers', 0, $id),
			'screenshot'=>$result->screenshot,
			'filename'=>$files[0]['src'],
			'time'=>$time
		);

		if ($time != '00:00:00.000') {
			if (preg_match('#^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])\.[0-1][0-9][0-9]?$#', $time)) {
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `screenshot` = '".pathinfo($files[0]['src'], PATHINFO_FILENAME).".png' WHERE `id` = ".(int)$trailer_id);
				$query = $db->execute();

				return json_encode(array('file'=>$result->screenshot, 'output'=>$media->createScreenshot($data)));
			}
		} else {
			return 'error:'.JText::_('COM_KA_TRAILERS_VIDEO_SCREENSHOT_CREATE_TIME_ERR');
		}
	}

	public function saveVideo($file='', $trailer_id, $movie_id) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();

		$db->setQuery("SELECT `filename` FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".(int)$trailer_id);
		$result = $db->loadResult();

		if (!empty($result) && count(json_decode($result)) > 0) {
			
		}
	}

	public function removeTrailerFiles() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = $app->input->get('id', 0, 'int');
		$item_id = $app->input->get('item_id', 0, 'int');
		$filename = $app->input->get('file', '', 'string');
		$type = $app->input->get('type', '', 'word');
		$success = true;
		$message = '';

		if ($type == '') {
			return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
		}

		if ($type == 'video') {
			$db->setQuery("SELECT `filename` FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".$item_id);
			$result = $db->loadResult();

			if (empty($result)) {
				return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
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
				return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
			}

			// Removing file
			if (file_exists($this->getPath('movie', 'trailers', 0, $id).$filename) && @unlink($this->getPath('movie', 'trailers', 0, $id).$filename) !== true) {
				$success = false;
				$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
			}
		} elseif ($type == 'subtitle' || $type == 'subtitles') {
			$db->setQuery("SELECT `_subtitles` FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".$item_id);
			$result = $db->loadResult();

			if (empty($result)) {
				return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
			}

			$result_arr = json_decode($result, true);

			if ($app->input->get('all', 0, 'int') == 0) {
				$new_arr = array();

				foreach ($result_arr as $k=>$v) {
					if ($v['file'] != $filename) {
						$new_arr[] = $v;
					}
				}

				$new_arr = JArrayHelper::toObject($new_arr);

				$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `_subtitles` = '".json_encode($new_arr)."' WHERE `id` = ".(int)$item_id);
				$query = $db->execute();

				if ($query !== true) {
					return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
				}

				// Removing file
				if (file_exists($this->getPath('movie', 'trailers', 0, $id).$filename) && @unlink($this->getPath('movie', 'trailers', 0, $id).$filename) !== true) {
					$success = false;
					$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
				}
			} else {
				foreach ($result_arr as $val) {
					if (file_exists($this->getPath('movie', 'trailers', 0, $id).$val['file']) && @unlink($this->getPath('movie', 'trailers', 0, $id).$val['file']) !== true) {
						$success = false;
						$message .= JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
					}
				}

				$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `_subtitles` = '{}' WHERE `id` = ".(int)$item_id);
				$query = $db->execute();

				if ($query !== true) {
					$success = false;
					$message .= JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
				}
			}
		} elseif ($type == 'chapter' || $type == 'chapters') {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `_chapters` = '{}' WHERE `id` = ".(int)$item_id);
			$query = $db->execute();

			if ($query !== true) {
				return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
			}

			// Removing file
			if (file_exists($this->getPath('movie', 'trailers', 0, $id).$filename) && @unlink($this->getPath('movie', 'trailers', 0, $id).$filename) !== true) {
				$success = false;
				$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
			}
		} elseif ($type == 'image' || $type == 'images') {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_trailers')." SET `screenshot` = '' WHERE `id` = ".(int)$item_id);
			$query = $db->execute();

			if ($query !== true) {
				return json_encode(array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED')));
			}

			// Removing file
			if (file_exists($this->getPath('movie', 'trailers', 0, $id).$filename) && @unlink($this->getPath('movie', 'trailers', 0, $id).$filename) !== true) {
				$success = false;
				$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
			}
		}

		return json_encode(array('success'=>$success, 'message'=>$message));
	}
}
