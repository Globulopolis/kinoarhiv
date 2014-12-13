<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

JLoader::register('DatabaseHelper', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'database.php');

class KinoarhivModelRelations extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.relations', 'relations', array('control' => 'form_r', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		return $this->getItem();
	}

	public function getItem() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$task = $app->input->get('param', '', 'cmd');

		if ($task == 'countries') {
			$country_id = $app->input->get('country_id', 0, 'int');
			$movie_id = $app->input->get('movie_id', 0, 'int');

			if (empty($country_id) || empty($movie_id)) {
				return array();
			}

			$db->setQuery("SELECT `country_id`, `movie_id`, `ordering`"
				. "\n FROM ".$db->quoteName('#__ka_rel_countries')
				. "\n WHERE `country_id` = ".(int)$country_id." AND `movie_id` = ".(int)$movie_id);
			$result = $db->loadObject();
		} elseif ($task == 'genres') {
			$genre_id = $app->input->get('genre_id', 0, 'int');
			$movie_id = $app->input->get('movie_id', 0, 'int');

			if (empty($genre_id) || empty($movie_id)) {
				return array();
			}

			$db->setQuery("SELECT `genre_id`, `movie_id`, `ordering`"
				. "\n FROM ".$db->quoteName('#__ka_rel_genres')
				. "\n WHERE `genre_id` = ".(int)$genre_id." AND `movie_id` = ".(int)$movie_id);
			$result = $db->loadObject();
		} elseif ($task == 'awards') {
			$award_id = $app->input->get('award_id', 0, 'int');
			$award_type = $app->input->get('award_type', 0, 'int');
			$item_id = $app->input->get('item_id', 0, 'int');

			if (empty($award_id) || empty($item_id)) {
				return array();
			}

			$db->setQuery("SELECT `id`, `item_id`, `award_id`, `desc`, `year`, `type`"
				. "\n FROM ".$db->quoteName('#__ka_rel_awards')
				. "\n WHERE `award_id` = ".(int)$award_id." AND `item_id` = ".(int)$item_id." AND `type` = ".(int)$award_type);
			$result = $db->loadObject();
		} elseif ($task == 'careers') {
			$career_id = $app->input->get('career_id', 0, 'int');
			$name_id = $app->input->get('name_id', 0, 'int');

			if (empty($career_id) || empty($name_id)) {
				return array();
			}

			$db->setQuery("SELECT `career_id`, `name_id`"
				. "\n FROM ".$db->quoteName('#__ka_rel_names_career')
				. "\n WHERE `career_id` = ".(int)$career_id." AND `name_id` = ".(int)$name_id);
			$result = $db->loadObject();
		}

		return $result;
	}

	public function getDataList($task) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$sidx = $app->input->get('sidx', '1', 'word');
		$sord = $app->input->get('sord', 'asc', 'word');
		$limit = $app->input->get('rows', 25, 'int');
		$page = $app->input->get('page', 0, 'int');
		$limitstart = $limit * $page - $limit;
		$result = (object)array();

		$query = $this->buildQuery($task);
		$where = $this->buildWhere($task);
		$order = $this->buildOrder($task, $sidx, $sord);

		$db->setQuery($query['total'].$where);
		$total = $db->loadResult();

		$total_pages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $total_pages) ? $total_pages : $page;

		$db->setQuery($query['rows']
			. $where
			. $order, $limitstart, $limit);
		$rows = $db->loadObjectList();

		$result = $this->preprocessRows($task, $rows);
		$result->page = $page;
		$result->total = $total_pages;
		$result->records = $total;

		return $result;
	}

	protected function buildQuery($task) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$query = array();

		if ($task == 'countries') {
			$query['total'] = "SELECT COUNT(`rel`.`country_id`)"
				. "\n FROM ".$db->quoteName('#__ka_rel_countries')." AS `rel`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_countries')." AS `cn` ON `cn`.`id` = `rel`.`country_id`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_movies')." AS `m` ON `m`.`id` = `rel`.`movie_id`";

			$query['rows'] = "SELECT `rel`.`country_id`, `rel`.`movie_id`, `rel`.`ordering`, `cn`.`name` AS `country`, `cn`.`id` AS `cn_id`, `m`.`title` AS `movie`, `m`.`year`"
				. "\n FROM ".$db->quoteName('#__ka_rel_countries')." AS `rel`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_countries')." AS `cn` ON `cn`.`id` = `rel`.`country_id`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_movies')." AS `m` ON `m`.`id` = `rel`.`movie_id`";
		} elseif ($task == 'genres') {
			$query['total'] = "SELECT COUNT(`rel`.`genre_id`)"
				. "\n FROM ".$db->quoteName('#__ka_rel_genres')." AS `rel`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_genres')." AS `g` ON `g`.`id` = `rel`.`genre_id`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_movies')." AS `m` ON `m`.`id` = `rel`.`movie_id`";

			$query['rows'] = "SELECT `rel`.`genre_id`, `rel`.`movie_id`, `rel`.`ordering`, `g`.`name` AS `genre`, `g`.`id` AS `g_id`, `m`.`title` AS `movie`, `m`.`year`"
				. "\n FROM ".$db->quoteName('#__ka_rel_genres')." AS `rel`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_genres')." AS `g` ON `g`.`id` = `rel`.`genre_id`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_movies')." AS `m` ON `m`.`id` = `rel`.`movie_id`";
		} elseif ($task == 'awards') {
			$award_type = $app->input->get('award_type', 0, 'int');

			if ($award_type == 0) {
				$join_cols = ", `m`.`title` AS `movie`, `m`.`year`";
				$join_left_table = "LEFT JOIN ".$db->quoteName('#__ka_movies')." AS `m` ON `m`.`id` = `rel`.`item_id`";
			} elseif ($award_type == 1) {
				$join_cols = ", `m`.`name`, `m`.`latin_name`, `m`.`date_of_birth`";
				$join_left_table = "LEFT JOIN ".$db->quoteName('#__ka_names')." AS `m` ON `m`.`id` = `rel`.`item_id`";
			}

			$query['total'] = "SELECT COUNT(`rel`.`award_id`)"
				. "\n FROM ".$db->quoteName('#__ka_rel_awards')." AS `rel`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_awards')." AS `a` ON `a`.`id` = `rel`.`award_id`"
				. "\n ".$join_left_table;

			$query['rows'] = "SELECT `rel`.`id`, `rel`.`award_id`, `rel`.`item_id`, `a`.`title` AS `award`, `a`.`id` AS `a_id`".$join_cols
				. "\n FROM ".$db->quoteName('#__ka_rel_awards')." AS `rel`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_awards')." AS `a` ON `a`.`id` = `rel`.`award_id`"
				. "\n ".$join_left_table;
		} elseif ($task == 'careers') {
			$query['total'] = "SELECT COUNT(`rel`.`career_id`)"
				. "\n FROM ".$db->quoteName('#__ka_rel_names_career')." AS `rel`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_names_career')." AS `c` ON `c`.`id` = `rel`.`career_id`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_names')." AS `n` ON `n`.`id` = `rel`.`name_id`";

			$query['rows'] = "SELECT `rel`.`career_id`, `rel`.`name_id`, `c`.`title` AS `career`, `c`.`id` AS `career_id`, `n`.`name`, `n`.`latin_name`, `n`.`date_of_birth`"
				. "\n FROM ".$db->quoteName('#__ka_rel_names_career')." AS `rel`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_names_career')." AS `c` ON `c`.`id` = `rel`.`career_id`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_names')." AS `n` ON `n`.`id` = `rel`.`name_id`";
		}

		return $query;
	}

	protected function buildOrder($task, $orderby, $order) {
		$db = $this->getDBO();
		$query = "\n ORDER BY ".$db->quoteName($orderby)." ".strtoupper($order);

		if ($task == 'countries' || $task == 'genres' || $task == 'names') {
			$query .= ", `ordering` ASC";
		}

		return $query;
	}

	protected function buildWhere($task) {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');
		$movie_id = $app->input->get('mid', 0, 'int');
		$name_id = $app->input->get('nid', 0, 'int');
		$search_field = $app->input->get('searchField', '', 'word');
		$search_operand = $app->input->get('searchOper', 'eq', 'cmd');
		$search_string = $app->input->get('searchString', '', 'string');
		$where = "";

		if ($task == 'countries') {
			// Process alias for some columns
			if ($search_field == 'country') {
				$search_field = 'name';
			} else if ($search_field == 'movie') {
				$search_field = 'title';
			}

			if (!empty($search_string)) {
				$where .= "\n WHERE ".DatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string));
			}

			if (!empty($id) && !empty($where)) {
				$where .= " AND `rel`.`country_id` = ".(int)$id;
			} elseif (!empty($id) && empty($where)) {
				$where .= "\n WHERE `rel`.`country_id` = ".(int)$id;
			}

			if (!empty($movie_id)) {
				if (!empty($where)) {
					$where .= " AND `rel`.`movie_id` = ".(int)$movie_id;
				} else {
					$where .= "\n WHERE `rel`.`movie_id` = ".(int)$movie_id;
				}
			}
		} elseif ($task == 'genres') {
			// Process alias for some columns
			if ($search_field == 'genre') {
				$search_field = 'name';
			} else if ($search_field == 'movie') {
				$search_field = 'title';
			}

			if (!empty($search_string)) {
				$where .= "\n WHERE ".DatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string));
			}

			if (!empty($id) && !empty($where)) {
				$where .= " AND `rel`.`genre_id` = ".(int)$id;
			} elseif (!empty($id) && empty($where)) {
				$where .= "\n WHERE `rel`.`genre_id` = ".(int)$id;
			}

			if (!empty($movie_id)) {
				if (!empty($where)) {
					$where .= " AND `rel`.`movie_id` = ".(int)$movie_id;
				} else {
					$where .= "\n WHERE `rel`.`movie_id` = ".(int)$movie_id;
				}
			}
		} elseif ($task == 'awards') {
			$award_type = $app->input->get('award_type', 0, 'int');

			// Process alias for some columns
			if ($search_field == 'award') {
				$search_field = 'a.title';
			} else if ($search_field == 'movie' || $search_field == 'title') {
				$search_field = 'm.title';
			}

			if (!empty($search_string)) {
				$where .= "\n WHERE `type` = ".$award_type." AND ".DatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string));
			} else {
				$where .= "\n WHERE `type` = ".$award_type;
			}

			if (!empty($id) && !empty($where)) {
				$where .= " AND `rel`.`award_id` = ".(int)$id;
			} elseif (!empty($id) && empty($where)) {
				$where .= "\n WHERE `rel`.`award_id` = ".(int)$id;
			}

			if (!empty($movie_id)) {
				if (!empty($where)) {
					$where .= " AND `m`.`id` = ".(int)$movie_id;
				} else {
					$where .= "\n WHERE `m`.`id` = ".(int)$movie_id;
				}
			}
		} elseif ($task == 'careers') {
			// Process alias for some columns
			if ($search_field == 'career') {
				$search_field = 'title';
			} else if ($search_field == 'name') {
				$search_field[] = 'name';
				$search_field[] = 'latin_name';
			}

			if (!empty($search_string)) {
				if (is_array($search_string)) {
					$where .= "\n WHERE ".DatabaseHelper::transformOperands($db->quoteName($search_field[0]), $search_operand, $db->escape($search_string))." AND ".DatabaseHelper::transformOperands($db->quoteName($search_field[1]), $search_operand, $db->escape($search_string));
				} else {
					$where .= "\n WHERE ".DatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string));
				}
			}

			if (!empty($id) && !empty($where)) {
				$where .= " AND `rel`.`career_id` = ".(int)$id;
			} elseif (!empty($id) && empty($where)) {
				$where .= "\n WHERE `rel`.`career_id` = ".(int)$id;
			}

			if (!empty($name_id)) {
				if (!empty($where)) {
					$where .= " AND `rel`.`name_id` = ".(int)$name_id;
				} else {
					$where .= "\n WHERE `rel`.`name_id` = ".(int)$name_id;
				}
			}
		}

		return $where;
	}

	protected function preprocessRows($task, &$rows) {
		$app = JFactory::getApplication();
		$result = (object)array();

		if ($task == 'countries') {
			foreach ($rows as $i=>$row) {
				$row->movie = ($row->year != '0000') ? $row->movie.' ('.$row->year.')' : $row->movie;

				$result->rows[$i]['id'] = $row->country_id.'_'.$row->movie_id;
				$result->rows[$i]['cell'] = array(
					$row->country,
					$row->country_id,
					$row->movie,
					$row->movie_id,
					$row->ordering
				);
			}
		} elseif ($task == 'genres') {
			foreach ($rows as $i=>$row) {
				$row->movie = ($row->year != '0000') ? $row->movie.' ('.$row->year.')' : $row->movie;

				$result->rows[$i]['id'] = $row->genre_id.'_'.$row->movie_id;
				$result->rows[$i]['cell'] = array(
					$row->genre,
					$row->genre_id,
					$row->movie,
					$row->movie_id,
					$row->ordering
				);
			}
		} elseif ($task == 'awards') {
			$award_type = $app->input->get('award_type', 0, 'int');

			foreach ($rows as $i=>$row) {
				if ($award_type == 0) {
					$title = ($row->year != '0000') ? $row->movie.' ('.$row->year.')' : $row->movie;
				} elseif ($award_type == 1) {
					$title = !empty($row->name) ? $row->name : '';
					$title .= (!empty($row->name) && !empty($row->latin_name)) ? ' / ': '';
					$title .= !empty($row->latin_name) ? $row->latin_name : '';
					$title .= ($row->date_of_birth != '0000-00-00') ? ' ('.$row->date_of_birth.')' : '';
				}

				$result->rows[$i]['id'] = $row->award_id.'_'.$row->item_id;
				$result->rows[$i]['cell'] = array(
					$row->award,
					$row->award_id,
					$title,
					$row->item_id,
					$row->id,
				);
			}
		} elseif ($task == 'careers') {
			foreach ($rows as $i=>$row) {
				$title = !empty($row->name) ? $row->name : '';
				$title .= (!empty($row->name) && !empty($row->latin_name)) ? ' / ': '';
				$title .= !empty($row->latin_name) ? $row->latin_name : '';
				$title .= ($row->date_of_birth != '0000-00-00') ? ' ('.$row->date_of_birth.')' : '';

				$result->rows[$i]['id'] = $row->career_id.'_'.$row->name_id;
				$result->rows[$i]['cell'] = array(
					$row->career,
					$row->career_id,
					$title,
					$row->name_id
				);
			}
		}

		return $result;
	}

	public function relations_remove() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$task = $app->input->get('param', '', 'cmd'); // It's really task
		$param = $app->input->get('task', '', 'cmd');
		$data = $app->input->post->get('data', array(), 'array');
		$award_type = $app->input->get('award_type', 0, 'int');
		$award_type_sql = "";
		$query = true;

		// Ordering of the columns must be the same as in database.
		if ($task == 'countries') {
			$table = '#__ka_rel_countries';
			$left_col = '`country_id`';
			$right_col = '`movie_id`';
		} elseif ($task == 'genres') {
			$table = '#__ka_rel_genres';
			$left_col = '`genre_id`';
			$right_col = '`movie_id`';
		} elseif ($task == 'awards') {
			$table = '#__ka_rel_awards';
			$left_col = '`award_id`';
			$right_col = '`item_id`';
			$award_type_sql = " AND `type` = ".(int)$award_type;
		} elseif ($task == 'careers') {
			$table = '#__ka_rel_names_career';
			$left_col = '`career_id`';
			$right_col = '`name_id`';
		}

		$db->setDebug(true);
		$db->lockTable($table);
		$db->transactionStart();

		foreach ($data as $key=>$value) {
			$name = explode('_', substr($value['name'], 9));

			$db->setQuery("DELETE FROM ".$db->quoteName($table)." WHERE ".$left_col." = ".(int)$name[0]." AND ".$right_col." = ".(int)$name[1].$award_type_sql.";");
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

	public function apply() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$data = $app->input->post->get('form_r', array(), 'array');
		$task = $app->input->post->get('param', '', 'cmd');
		$new = $app->input->get('new', '', 'int');
		$control_id = $app->input->post->get('control_id', array(), 'array');
		$control = array(); // Array holding a new control ids

		// Checking if we need insert new data instead of update
		if ($new == 1) {
			if ($task == 'countries') {
				$table = '#__ka_rel_countries';
				$control = array(0=>$data['country_id'], 1=>$data['movie_id']);
			} elseif ($task == 'genres') {
				$table = '#__ka_rel_genres';
				$control = array(0=>$data['genre_id'], 1=>$data['movie_id']);
			} elseif ($task == 'awards') {
				$table = '#__ka_rel_awards';
				$control = array(0=>$data['award_id'], 1=>$data['item_id']);
			} elseif ($task == 'careers') {
				$table = '#__ka_rel_names_career';
				$control = array(0=>$data['career_id'], 1=>$data['name_id']);
			}

			// Getting the columns for field list
			$cols_obj = $db->getTableColumns($table);
			$cols = "";
			$values = "";
			$i = 0;
			$cols_count = count($cols_obj);

			if ($cols_count != count($data)) {
				return array('success'=>false, 'message'=>JText::_('ERROR'), 'ids'=>$control);
			}

			foreach ($cols_obj as $col_name=>$type) {
				$cols .= $db->quoteName($col_name);
				$values .= "'".$db->escape($data[$col_name])."'";

				if ($i+1 != $cols_count) {
					$cols .= ', ';
					$values .= ', ';
				}
				$i++;
			}

			$db->setQuery("INSERT INTO ".$table." (".$cols.") VALUES (".$values.")");
			$query = $db->execute();

			$message = ($query === true) ? JText::_('COM_KA_ITEMS_SAVE_SUCCESS') : JText::_('COM_KA_ITEMS_ADD_ERROR');
		} elseif ($new == 0) {
			if ($task == 'countries') {
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_rel_countries')
					. "\n SET `country_id` = '".(int)$data['country_id']."', `movie_id` = '".(int)$data['movie_id']."', `ordering` = '".(int)$data['ordering']."'"
					. "\n WHERE `country_id` = ".(int)$control_id[0]." AND `movie_id` = ".(int)$control_id[1]);
				$query = $db->execute();

				$control = array(0=>$data['country_id'], 1=>$data['movie_id']);
			} elseif ($task == 'genres') {
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_rel_genres')
					. "\n SET `genre_id` = '".(int)$data['genre_id']."', `movie_id` = '".(int)$data['movie_id']."', `ordering` = '".(int)$data['ordering']."'"
					. "\n WHERE `genre_id` = ".(int)$control_id[0]." AND `movie_id` = ".(int)$control_id[1]);
				$query = $db->execute();

				$control = array(0=>$data['genre_id'], 1=>$data['movie_id']);
			} elseif ($task == 'awards') {
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_rel_awards')
					. "\n SET `award_id` = '".(int)$data['award_id']."', `item_id` = '".(int)$data['item_id']."', `desc` = '".$db->escape($data['desc'])."', `year` = '".(int)$data['year']."', `type` = '".(int)$data['type']."'"
					. "\n WHERE `award_id` = ".(int)$control_id[0]." AND `item_id` = ".(int)$control_id[1]);
				$query = $db->execute();

				$control = array(0=>$data['award_id'], 1=>$data['item_id']);
			}  elseif ($task == 'careers') {
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_rel_names_career')
					. "\n SET `career_id` = '".(int)$data['career_id']."', `name_id` = '".(int)$data['name_id']."'"
					. "\n WHERE `career_id` = ".(int)$control_id[0]." AND `name_id` = ".(int)$control_id[1]);
				$query = $db->execute();

				$control = array(0=>$data['career_id'], 1=>$data['name_id']);
			}

			$message = ($query === true) ? JText::_('COM_KA_ITEMS_SAVE_SUCCESS') : JText::_('COM_KA_ITEMS_EDIT_ERROR');
		}

		$success = ($query === true) ? true : false;

		return array('success'=>$success, 'message'=>$message, 'ids'=>$control);
	}

	public function saveOrder() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$param = $app->input->get('param', '', 'cmd');

		// The ID of the element that we drag. It's important: this ID controlling the group of the elements. E.g. if we drag the row with id 1_4(where 1 item ID and 4 the movie ID) we need to update the rows with the item ID 1 not 2 or 3, even if in the grid they exists.
		$_id = $app->input->get('id', '', 'string');
		$id = explode('_', $_id);
		// The IDs of the elements that we need to re-sort
		$_ids = $app->input->get('ids', '', 'string');
		$ids = explode(',', $_ids);
		$query = true;
		$i = 0;

		if ($param == 'countries') {
			$db->setDebug(true);
			$db->lockTable('#__ka_rel_countries');
			$db->transactionStart();

			foreach ($ids as $index=>$row_id) {
				$v = explode('_', $row_id);
				$country_id = $v[0]; $movie_id = $v[1];

				if ($movie_id == $id[1]) {
					$db->setQuery("UPDATE ".$db->quoteName('#__ka_rel_countries')." SET `ordering` = '".$i."' WHERE `country_id` = '".(int)$country_id."' AND `movie_id` = '".(int)$movie_id."';");
					$result = $db->execute();

					if ($result === false) {
						$query = false;
						break;
					}

					$i++;
				}
			}

			if ($query === false) {
				$db->transactionRollback();
			} else {
				$db->transactionCommit();
			}

			$db->unlockTables();
			$db->setDebug(false);

			$success = $query ? true : false;
		} elseif ($param == 'genres') {
			$db->setDebug(true);
			$db->lockTable('#__ka_rel_genres');
			$db->transactionStart();

			foreach ($ids as $index=>$row_id) {
				$v = explode('_', $row_id);
				$genre_id = $v[0]; $movie_id = $v[1];

				if ($movie_id == $id[1]) {
					$db->setQuery("UPDATE ".$db->quoteName('#__ka_rel_genres')." SET `ordering` = '".$i."' WHERE `genre_id` = '".(int)$genre_id."' AND `movie_id` = '".(int)$movie_id."';");
					$result = $db->execute();

					if ($result === false) {
						$query = false;
						break;
					}

					$i++;
				}
			}

			if ($query === false) {
				$db->transactionRollback();
			} else {
				$db->transactionCommit();
			}

			$db->unlockTables();
			$db->setDebug(false);

			$success = $query ? true : false;
		} elseif ($param == 'names') {
			$db->setDebug(true);
			$db->lockTable('#__ka_rel_names');
			$db->transactionStart();

			foreach ($ids as $index=>$row_id) {
				$v = explode('_', $row_id);
				$name_id = $v[0];
				$movie_id = $v[1];
				$type_id = $v[2];

				if ($movie_id == $id[1]) {
					// Build queries list only for one type group. E.g. only for artists
					if ($id[2] == $type_id) {
						$db->setQuery("UPDATE ".$db->quoteName('#__ka_rel_names')." SET `ordering` = '".$i."' WHERE `name_id` = ".(int)$name_id." AND `movie_id` = ".(int)$movie_id.";");
						$result = $db->execute();

						if ($result === false) {
							$query = false;
							break;
						}

						$i++;
					}
				}
			}

			if ($query === false) {
				$db->transactionRollback();
			} else {
				$db->transactionCommit();
			}

			$db->unlockTables();
			$db->setDebug(false);

			$success = $query ? true : false;
		} else {
			$success = false;
		}

		return array('success'=>$success);
	}

	public function saveRelNames() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$data = $app->input->getArray(array(
			'form'=>array(
				'type'=>			'array',
				'name_id'=>			'array',
				'dub_id'=>			'array',
				'role'=>			'string',
				'is_directors'=>	'int',
				'is_actors'=>		'int',
				'voice_artists'=>	'int',
				'ordering'=>		'int',
				'desc'=>			'string'
			)
		), $_POST);
		$isNew = $app->input->post->get('new', 1, 'int');
		$movie_id = $app->input->get('id', 0, 'int');
		$dub_id = isset($data['form']['dub_id'][0]) ? $data['form']['dub_id'][0] : 0;
		$message = '';

		if (count($data['form']['type']) == 0 || count($data['form']['name_id']) == 0) {
			return array('success'=>false, 'message'=>JText::_('COM_KA_REQUIRED'));
		}

		if (empty($data['form']['dub_id'][0])) {
			$data['form']['dub_id'][0] = 0;
		}

		if ($isNew == 1) {
			$db->setQuery("SELECT COUNT(`name_id`) FROM ".$db->quoteName('#__ka_rel_names')." WHERE `name_id` = ".(int)$data['form']['name_id'][0]." AND `movie_id` = ".(int)$movie_id);
			$total = $db->loadResult();

			if ($total > 0) {
				return array('success'=>false, 'message'=>JText::_('COM_KA_ITEMS_RELATION_DUPLICATE'));
			}

			$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_rel_names')." (`name_id`, `movie_id`, `type`, `role`, `dub_id`, `is_actors`, `voice_artists`, `is_directors`, `ordering`, `desc`)"
				. "\n VALUES ('".(int)$data['form']['name_id'][0]."', '".(int)$movie_id."', '".(int)$data['form']['type'][0]."', '".$data['form']['role']."', '".(int)$data['form']['dub_id'][0]."', '".(int)$data['form']['is_actors']."', '".(int)$data['form']['voice_artists']."', '".(int)$data['form']['is_directors']."', '".(int)$data['form']['ordering']."', '".$db->escape($data['form']['desc'])."')");
			$query = $db->execute();

			if ($query !== true) {
				$success = false;
				$message = JText::_('ERROR');
			} else {
				$success = true;
				$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');
			}
		} else {
			$_id = $app->input->get('id', '', 'string');
			$_id = substr($_id, 15);
			$id = explode('_', $_id);

			$db->setQuery("UPDATE ".$db->quoteName('#__ka_rel_names')." SET `type` = '".(int)$data['form']['type'][0]."', `role` = '".$data['form']['role']."', `dub_id` = '".(int)$data['form']['dub_id'][0]."', `is_actors` = '".(int)$data['form']['is_actors']."', `voice_artists` = '".(int)$data['form']['voice_artists']."', `is_directors` = '".(int)$data['form']['is_directors']."', `ordering` = '".(int)$data['form']['ordering']."', `desc` = '".$db->escape($data['form']['desc'])."'"
				. "\n WHERE `name_id` = ".(int)$data['form']['name_id'][0]." AND `movie_id` = ".(int)$movie_id);
			$query = $db->execute();

			if ($query !== true) {
				$success = false;
				$message = JText::_('ERROR');
			} else {
				$success = true;
				$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');
			}
		}

		return array('success'=>$success, 'message'=>$message);
	}

	public function saveRelAwards() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$data = $app->input->getArray(array(
			'form'=>array(
				'id' =>			'int',
				'award_id'=>	'array',
				'desc'=>		'raw',
				'year'=>		'int'
			)
		), $_POST);
		$isNew = $app->input->post->get('new', 1, 'int');
		$item_id = $app->input->get('id', 0, 'int');
		$type = $app->input->get('type', 0, 'int');
		$message = '';

		if (empty($data['form']['award_id']) || count($data['form']['award_id']) == 0) {
			return array('success'=>false, 'message'=>JText::_('COM_KA_REQUIRED'));
		}

		if ($isNew == 1) {
			$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_rel_awards')." (`id`, `item_id`, `award_id`, `desc`, `year`, `type`)"
				. "\n VALUES ('', '".(int)$item_id."', '".(int)$data['form']['award_id'][0]."', '".$db->escape($data['form']['desc'])."', '".$data['form']['year']."', '".(int)$type."')");
			$query = $db->execute();

			if ($query !== true) {
				$success = false;
				$message = JText::_('ERROR');
			} else {
				$success = true;
				$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');
			}
		} else {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_rel_awards')." SET `award_id` = '".(int)$data['form']['award_id'][0]."', `desc` = '".$db->escape($data['form']['desc'])."', `year` = '".$data['form']['year']."'"
				. "\n WHERE `id` = ".(int)$data['form']['id']);
			$query = $db->execute();

			if ($query !== true) {
				$success = false;
				$message = JText::_('ERROR');
			} else {
				$success = true;
				$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');
			}
		}

		return array('success'=>$success, 'message'=>$message);
	}
}
