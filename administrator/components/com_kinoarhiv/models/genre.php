<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivModelGenre extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.genre', 'genre', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		return $this->getItems();
	}

	public function getItems() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$task = $app->input->get('task', '', 'cmd');

		$_id = $app->input->get('id', array(), 'array');
		$id = !empty($_id) ? $_id[0] : $app->input->get('id', null, 'int');

		$db->setQuery("SELECT `id`, `name`, `alias`, `stats`, `state`, `access`, `language`"
			. "\n FROM ".$db->quoteName('#__ka_genres')
			. "\n WHERE `id` = ".(int)$id);
		$result = $db->loadObject();

		return $result;
	}

	public function publish($isUnpublish) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;

		$db->setQuery("UPDATE ".$db->quoteName('#__ka_genres')." SET `state` = '".(int)$state."' WHERE `id` IN (".implode(',', $ids).")");

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function remove() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');

		$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_genres')." WHERE `id` IN (".implode(',', $ids).")");

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function apply($data) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->post->get('id', null, 'int');
		$alias = empty($data['alias']) ? JFilterOutput::stringURLSafe($data['name']) : JFilterOutput::stringURLSafe($data['alias']);

		if (empty($id)) {
			$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_genres')." (`id`, `name`, `alias`, `stats`, `state`, `access`, `language`)"
				. "\n VALUES ('', '".$db->escape($data['name'])."', '".$alias."', '0', '".$data['state']."', '".$data['access']."', '".$data['language']."')");
		} else {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_genres')
				. "\n SET `name` = '".$db->escape($data['name'])."', `alias` = '".$alias."', `stats` = '".$data['stats']."', `state` = '".$data['state']."', `access` = '".$data['access']."', `language1` = '".$data['language']."'"
				. "\n WHERE `id` = ".(int)$id);
		}

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function updateStat() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$gid = $app->input->get('id', array(), 'array');
		$boxchecked = $app->input->get('boxchecked', 0, 'int');

		if (count($gid) > 1) {
			if (count($gid) != $boxchecked) {
				return array('success'=>false);
			}

			$query = true;
			$db->setDebug(true);
			$db->lockTable('#__ka_genres');
			$db->transactionStart();

			foreach ($gid as $genre_id) {
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_genres')
				. "\n SET `stats` = (SELECT COUNT(`genre_id`) FROM ".$db->quoteName('#__ka_rel_genres')." WHERE `genre_id` = ".(int)$genre_id.")"
				. "\n WHERE `id` = ".(int)$genre_id.";");
				$_query = $db->execute();

				if ($_query === false) {
					$query = false;
					break;
				}
			}

			if ($query === false) {
				$db->transactionRollback();
				$this->setError('Commit failed!');
			} else {
				$db->transactionCommit();
			}

			$db->unlockTables();
			$db->setDebug(false);
			$total = 0;
		} else {
			if (empty($gid[0])) {
				return array('success'=>false, 'message'=>JText::_('COM_KA_GENRES_STATS_UPDATE_ERROR'));
			}

			$db->setQuery("UPDATE ".$db->quoteName('#__ka_genres')
				. "\n SET `stats` = (SELECT COUNT(`genre_id`) FROM ".$db->quoteName('#__ka_rel_genres')." WHERE `genre_id` = ".(int)$gid[0].")"
				. "\n WHERE `id` = ".(int)$gid[0]);
			$query = $db->execute();

			$db->setQuery("SELECT `stats` FROM ".$db->quoteName('#__ka_genres')." WHERE `id` = ".$gid[0]);
			$total = $db->loadResult();
		}

		$result = $query ? true : false;

		return array('success'=>$result, 'total'=>$total);
	}
}
