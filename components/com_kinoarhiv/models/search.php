<?php defined('_JEXEC') or die;

class KinoarhivModelSearch extends JModelLegacy {
	/**
	 * Get initial data for lists in search form
	 *
	 * @return   object
	 *
	*/
	public function getItems() {
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$default_value = array(array('value' => '', 'text' => '-'));
		$items = (object)array(
			'movies' => (object)array(),
			'names'  => (object)array(),
		);

		// Get years for movies
		$db->setQuery("SELECT `year` FROM ".$db->quoteName('#__ka_movies')." GROUP BY `year`");
		$_from_year = $db->loadObjectList();
		$new_years_arr = array();

		foreach ($_from_year as $key=>$years) {
			$y = explode('-', str_replace(' ', '', $years->year));

			$new_years_arr[$key]['value'] = $y[0];
			$new_years_arr[$key]['text'] = $y[0];
			if (isset($y[1]) && !empty($y[1])) {
				$new_years_arr[$key]['value'] = $y[1];
				$new_years_arr[$key]['text'] = $y[1];
			}
		}

		rsort($new_years_arr);
		$new_years_arr = JArrayHelper::arrayUnique($new_years_arr);
		$items->movies->from_year = array_merge($default_value, $new_years_arr);
		$items->movies->to_year = &$items->movies->from_year;

		// Get the list of countries
		$db->setQuery("SELECT `id`, `name`, `code` FROM ".$db->quoteName('#__ka_countries')." WHERE `state` = 1 AND `language` IN (".$db->quote($lang->getTag()).",'*') ORDER BY `name` ASC");
		$countries = $db->loadObjectList();

		$items->movies->countries = array_merge(array((object)array('id' => '', 'name' => '', 'code' => '')), $countries);

		// Get the list of vendors
		$db->setQuery("SELECT `id`, `company_name`, `company_name_intl` FROM ".$db->quoteName('#__ka_vendors')." WHERE `state` = 1 AND `language` IN (".$db->quote($lang->getTag()).",'*')");
		$_vendors = $db->loadObjectList();

		foreach ($_vendors as $vendor) {
			$text = '';

			if ($vendor->company_name != '') {
				$text .= $vendor->company_name;
			}

			if ($vendor->company_name != '' && $vendor->company_name_intl != '') {
				$text .= ' / ';
			}

			if ($vendor->company_name_intl != '') {
				$text .= $vendor->company_name_intl;
			}

			$vendors[] = array('value' => $vendor->id, 'text' => $text);
		}

		$items->movies->vendors = array_merge(array(array('value' => '', 'text' => '')), $vendors);

		// Get the list of genres
		$db->setQuery("SELECT `id` AS `value`, `name` AS `text` FROM ".$db->quoteName('#__ka_genres')." WHERE `state` = 1 AND `language` IN (".$db->quote($lang->getTag()).",'*') AND `access` IN (".$groups.") ORDER BY `name` ASC");
		$items->movies->genres = $db->loadObjectList();

		// MPAA
		$items->movies->mpaa = array(
			array('value'=>'', 'text'=>'-'),
			array('value'=>'g', 'text'=>'G'),
			array('value'=>'gp', 'text'=>'GP'),
			array('value'=>'pg', 'text'=>'PG'),
			array('value'=>'pg-13', 'text'=>'PG-13'),
			array('value'=>'r', 'text'=>'R'),
			array('value'=>'nc-17', 'text'=>'NC-17')
		);

		// Russian age restict
		$items->movies->age_restrict = array(
			array('value'=>'-1', 'text'=>'-'),
			array('value'=>'0', 'text'=>'0+'),
			array('value'=>'6', 'text'=>'6+'),
			array('value'=>'12', 'text'=>'12+'),
			array('value'=>'16', 'text'=>'16+'),
			array('value'=>'18', 'text'=>'18+')
		);

		// Ukrainian age restict
		$items->movies->ua_rate = array(
			array('value'=>'-1', 'text'=>'-'),
			array('value'=>'0', 'text'=>JText::_('COM_KA_SEARCH_ADV_MOVIES_UA_RATE_0')),
			array('value'=>'1', 'text'=>JText::_('COM_KA_SEARCH_ADV_MOVIES_UA_RATE_1')),
			array('value'=>'2', 'text'=>JText::_('COM_KA_SEARCH_ADV_MOVIES_UA_RATE_2'))
		);

		// Budgets
		$db->setQuery("SELECT `budget` AS `value`, `budget` AS `text` FROM ".$db->quoteName('#__ka_movies')." WHERE `budget` != '' AND `state` = 1 AND `language` IN (".$db->quote($lang->getTag()).",'*') AND `access` IN (".$groups.") GROUP BY `budget` ORDER BY `budget` ASC");
		$budgets = $db->loadObjectList();

		$items->movies->from_budget = array_merge(
			array(array('value' => '', 'text' => '-')),
			$budgets
		);
		$items->movies->to_budget = &$items->movies->from_budget;

		$items->names->gender = array(
			array('value'=>'', 'text'=>'-'),
			array('value'=>'1', 'text'=>JText::_('COM_KA_SEARCH_ADV_NAMES_GENDER_M')),
			array('value'=>'0', 'text'=>JText::_('COM_KA_SEARCH_ADV_NAMES_GENDER_F'))
		);

		$items->names->birthcountry = &$items->movies->countries;

		// Amplua
		$db->setQuery("SELECT `id` AS `value`, `title` AS `text` FROM ".$db->quoteName('#__ka_names_career')." WHERE (`is_mainpage` = 1 OR `is_amplua` = 1) AND `language` IN (".$db->quote($lang->getTag()).",'*') GROUP BY `title` ORDER BY `ordering` DESC, `title` ASC");
		$amplua = $db->loadObjectList();

		$items->names->amplua = array_merge(
			array(array('value' => '', 'text' => '-')),
			$amplua
		);

		return $items;
	}

	
	/**
	 * Get the homepage Itemid for movies and names lists
	 *
	 * @return   array
	 *
	*/
	public function getHomeItemid() {
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$itemid = array('movies'=>0, 'names'=>0);

		$db->setQuery("SELECT `id` FROM ".$db->quoteName('#__menu')
			. "\n WHERE `link` = 'index.php?option=com_kinoarhiv&view=movies'"
				. " AND `type` = 'component'"
				. " AND `published` = 1"
				. " AND `access` IN (".$groups.")"
				. " AND `language` IN (".$db->quote($lang->getTag()).",'*')"
			. "\n LIMIT 1");
		$itemid['movies'] = $db->loadResult();

		$db->setQuery("SELECT `id` FROM ".$db->quoteName('#__menu')
			. "\n WHERE `link` = 'index.php?option=com_kinoarhiv&view=names'"
				. " AND `type` = 'component'"
				. " AND `published` = 1"
				. " AND `access` IN (".$groups.")"
				. " AND `language` IN (".$db->quote($lang->getTag()).",'*')"
			. "\n LIMIT 1");
		$itemid['names'] = $db->loadResult();

		return $itemid;
	}

	/**
	 * Get the values for inputs
	 *
	 * @return   object
	 *
	*/
	public function getActiveFilters() {
		$input = JFactory::getApplication()->input;
		$items = new JRegistry;

		if (array_key_exists('movies', $input->post->get('filters', array(), 'array'))) {
			$_items = $input->getArray(array(
				'filters' => array(
					'movies' => array(
						'title'=>'string',
						'year'=>'string',
						'from_year'=>'int',
						'to_year'=>'int',
						'country'=>'int',
						'vendor'=>'int',
						'genre'=>'array',
						'mpaa'=>'string',
						'age_restrict'=>'string',
						'ua_rate'=>'int',
						'rate'=>array('min'=>'int', 'max'=>'int'),
						'imdbrate'=>array('min'=>'int', 'max'=>'int'),
						'kprate'=>array('min'=>'int', 'max'=>'int'),
						'rtrate'=>array('min'=>'int', 'max'=>'int'),
						'from_budget'=>'string',
						'to_budget'=>'string'
					)
				)
			));

			$items->loadArray($_items);
		}

		if (array_key_exists('names', $input->post->get('filters', array(), 'array'))) {
			$_items = $input->getArray(array(
				'filters' => array(
					'names' => array(
						'title'=>'string',
						'gender'=>'alnum',
						'mtitle'=>'int',
						'birthday'=>'string',
						'birthplace'=>'string',
						'birthcountry'=>'int',
						'amplua'=>'int'
					)
				)
			));

			$items->loadArray($_items);
		}

		return $items;
	}
}
