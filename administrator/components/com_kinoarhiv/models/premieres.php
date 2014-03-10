<?php defined('_JEXEC') or die;

JLoader::register('DatabaseHelper', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'database.php');

class KinoarhivModelPremieres extends JModelList {
	protected $context = null;

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'p.id',
				'title', 'm.title',
				'premiere_date', 'p.premiere_date',
				'name', 'c.name',
				'vendor', 'v.company_name', 'v.company_name_intl',
				'ordering', 'p.ordering');
		}

		parent::__construct($config);

		$this->context = strtolower($this->option.'.'.$this->getName().'.premieres');
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout')) {
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// List state information.
		parent::populateState('p.premiere_date', 'desc');
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	protected function getListQuery() {
		$db = $this->getDBO();

		$query = $db->getQuery(true);

		$query->select('`p`.`id`, `p`.`movie_id`, `p`.`vendor_id`, `p`.`premiere_date`, `p`.`country_id`, `p`.`info`, `p`.`ordering`')
			->from($db->quoteName('#__ka_premieres').' AS `p`')
		->select('`m`.`title`, `m`.`year`')
			->leftjoin($db->quoteName('#__ka_movies').' AS `m` ON `m`.`id` = `p`.`movie_id`')
		->select('`v`.`company_name`, `v`.`company_name_intl`')
			->leftjoin($db->quoteName('#__ka_vendors').' AS `v` ON `v`.`id` = `p`.`vendor_id`')
		->select('`c`.`name`')
			->leftjoin($db->quoteName('#__ka_countries').' AS `c` ON `c`.`id` = `p`.`country_id`');

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('p.id = ' . (int) substr($search, 3));
			} elseif (stripos($search, 'title:') === 0) {
				$search = trim(substr($search, 6));
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('m.title LIKE ' . $search);
			} elseif (stripos($search, 'country:') === 0) {
				$search = trim(substr($search, 8));
				
				if ($search == JText::_('COM_KA_PREMIERE_WORLD')) {
					$query->where('p.country_id = 0');
				} else {
					$search = $db->quote('%' . $db->escape($search, true) . '%');
					$query->where('c.name LIKE ' . $search);
				}
			} else {
				$search = trim(substr($search, 5));
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('p.premiere_date LIKE ' . $search);
			}
		}

		$orderCol = $this->state->get('list.ordering', 'p.premiere_date');
		$orderDirn = $this->state->get('list.direction', 'desc');
		if ($orderCol == 'p.ordering') {
			$orderCol = 'p.ordering ' . $orderDirn . ', p.premiere_date';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	public function savePremiere() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$movie_id = $app->input->get('movie_id', null, 'int');
		$data = $app->input->get('form', array(), 'array');
		$is_new = $app->input->get('new', 0, 'int');

		if (isset($data['p_vendor_id'][0]) && !empty($data['p_vendor_id'][0])) {
			if (count($data['p_country_id']) > 1) {
				$country_id = $data['p_country_id'][1];
			} else {
				$country_id = $data['p_country_id'][0];
			}

			// Prevent duplicates
			$db->setQuery("SELECT COUNT(`id`) FROM ".$db->quoteName('#__ka_premieres')." WHERE `movie_id` = ".$movie_id." AND `vendor_id` = ".$data['p_vendor_id'][0]." AND `country_id` = ".$country_id);
			$c = $db->loadResult();

			if ($c > 0 && $is_new != 0) {
				return array('success'=>false, 'message'=>'Error');
			}

			if ($is_new == 1) {
				$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_premieres')." (`id`, `movie_id`, `vendor_id`, `premiere_date`, `country_id`, `info`, `ordering`)"
					. "\n VALUES ('', '".$movie_id."', '".$data['p_vendor_id'][0]."', '".$data['p_premiere_date']."', '".$country_id."', '".$db->escape($data['p_info'])."', '".$data['p_ordering']."')");
			} else {
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_premieres')
					. "\n SET `vendor_id` = '".$data['p_vendor_id'][0]."', `premiere_date` = '".$data['p_premiere_date']."', `country_id` = '".$country_id."', `info` = '".$db->escape($data['p_info'])."', `ordering` = '".$data['p_ordering']."'"
					. "\n WHERE `id` = ".(int)$id);
			}

			try {
				$db->execute();
				return array('success'=>true, 'message'=>JText::_('COM_KA_SAVED'));
			} catch(Exception $e) {
				return array('success'=>false, 'message'=>$e->getMessage());
			}
		} else {
			return array('success'=>false, 'message'=>JText::_('COM_KA_FIELD_PREMIERE_VENDOR_REQUIRED'));
		}
	}

	public function deletePremieres() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('data', array(), 'array');
		$query = true;

		$db->setDebug(true);
		$db->lockTable('#__ka_premieres');
		$db->transactionStart();

		foreach ($data as $key=>$value) {
			$_name = explode('_', $value['name']);
			$item_id = $_name[3];

			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_premieres')." WHERE `id` = ".(int)$item_id.";");
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

		if ($query) {
			$success = true;
			$message = JText::_('COM_KA_ITEMS_DELETED_SUCCESS');
		} else {
			$success = false;
			$message = JText::_('COM_KA_ITEMS_DELETED_ERROR');
		}

		return array('success'=>$success, 'message'=>$message);
	}

	public function saveOrder() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('ord', array(), 'array');
		$movie_id = $app->input->post->get('movie_id', null, 'int');

		if (count($data) < 2) {
			return array('success'=>false, 'message'=>JText::_('COM_KA_SAVE_ORDER_AT_LEAST_TWO'));
		}

		$query = true;

		$db->setDebug(true);
		$db->lockTable('#__ka_premieres');
		$db->transactionStart();

		foreach ($data as $key=>$value) {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_premieres')." SET `ordering` = '".(int)$key."' WHERE `id` = ".(int)$value." AND `movie_id` = ".(int)$movie_id.";");
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

		if ($query) {
			$success = true;
			$message = JText::_('COM_KA_SAVED');
		} else {
			$success = false;
			$message = JText::_('COM_KA_SAVE_ORDER_ERROR');
		}

		return array('success'=>$success, 'message'=>$message);
	}
}
