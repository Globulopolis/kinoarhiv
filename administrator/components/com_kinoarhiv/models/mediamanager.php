<?php defined('_JEXEC') or die;

class KinoarhivModelMediamanager extends JModelList {
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'g.id',
				'filename', 'g.filename',
				'dimension', 'g.dimension',
				'state', 'g.state',
				'poster_frontpage', 'g.poster_frontpage');
		}

		parent::__construct($config);
	}

	public function getPath() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$section = $app->input->get('section', '', 'word');
		$type = $app->input->get('type', '', 'word');
		$tab = $app->input->get('tab', 0, 'int');
		$id = $app->input->get('id', 0, 'int');

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
				$orderDirn = $this->state->get('list.direction', 'asc');
				$query->order($db->escape($orderCol . ' ' . strtoupper($orderDirn)));
			} elseif ($type == 'trailers') {
				$query->select(
					$this->getState(
						'list.select',
						'`t`.`id`, `t`.`title`, `t`.`embed_code`, `t`.`filename`, `t`.`w_h`, `t`.`duration`, `t`.`_captions`, `t`.`_subtitles`, `t`.`_chapters`, `t`.`frontpage`, `t`.`access`, `t`.`state`, `t`.`language`, `t`.`is_movie`, `m`.`alias`'
					)
				);
				$query->from($db->quoteName('#__ka_trailers').' AS `t`');
				$query->leftJoin($db->quoteName('#__ka_movies').' AS `m` ON `m`.`id` = `t`.`movie_id`');
				$query->where('(`t`.`state` = 0 OR `t`.`state` = 1) AND `t`.`movie_id` = '.(int)$id);

				$orderCol = $this->state->get('list.ordering', 't.id');
				$orderDirn = $this->state->get('list.direction', 'asc');
				$query->order($db->escape($orderCol . ' ' . strtoupper($orderDirn)));
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
	 * Method to publish or unpublish posters on movie info page(not on posters page)
	 *
	 * @param	int		 $action		  0 - unpublish from frontpage, 1 - publish poster on frontpage
	 *
	 * @return array
	 *
	 */
	public function publishOnFrontpage($action) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$movie_id = $app->input->get('id', 0, 'int');
		$id = $app->input->get('_id', array(), 'array');

		// Reset all values to 0
		$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies_gallery')." SET `poster_frontpage` = '0' WHERE `movie_id` = ".(int)$movie_id." AND `type` = 2");
		$db->execute();

		if (!isset($id[0]) || empty($id[0])) {
			$this->setError('Unknown ID');
			return $this->getError();
		}

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies_gallery')." SET `poster_frontpage` = '".(int)$action."' WHERE `id` = ".(int)$id[0]);
		$db->execute();

		return $this->getError();
	}

	public function publish($action) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$movie_id = $app->input->get('id', 0, 'int');
		$id = $app->input->get('_id', array(), 'array');

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies_gallery')." SET `state` = '".(int)$action."' WHERE `id` IN (".implode(',', $id).")");
		$db->execute();

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
}
