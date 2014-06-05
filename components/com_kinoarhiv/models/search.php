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
		$db->setQuery("SELECT SUBSTRING(`year`, 1, 4) AS `value`, SUBSTRING(`year`, 1, 4) AS `text` FROM ".$db->quoteName('#__ka_movies')." GROUP BY `year` ORDER BY `year` DESC");
		$from_year = $db->loadObjectList();

		$items->movies->from_year = array_merge($default_value, $from_year);
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
			array('value'=>'', 'text'=>'-'),
			array('value'=>'0', 'text'=>'0+'),
			array('value'=>'6', 'text'=>'6+'),
			array('value'=>'12', 'text'=>'12+'),
			array('value'=>'16', 'text'=>'16+'),
			array('value'=>'18', 'text'=>'18+')
		);

		// Ukrainian age restict
		$items->movies->ua_rate = array(
			array('value'=>'', 'text'=>'-'),
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
		$_items = (object)array(
			'movies' => (object)array(
				'title' => $input->post->get('title', '', 'string'),
				'year' => $input->post->get('year', '', 'string'),
				'from_year' => $input->post->get('from_year', null, 'int'),
				'to_year' => $input->post->get('to_year', null, 'int'),
				'country' => $input->post->get('country', null, 'int'),
				'vendor' => $input->post->get('vendor', null, 'int'),
				'genre' => $input->post->get('genre', array(), 'array'),
				'mpaa' => $input->post->get('mpaa', '', 'word'),
				'age_restrict' => $input->post->get('age_restrict', null, 'int'),
				'ua_rate' => $input->post->get('age_restrict', null, 'int'),
				'rate' => (object)array(
					'min' => $input->post->get('min', 0, 'int'), 'max' => $input->post->get('max', 10, 'int')
				),
				'imdbrate' => (object)array(
					'min' => 6, 'max' => 10
				),
				'kprate' => (object)array(
					'min' => 6, 'max' => 10
				),
				'rtrate' => (object)array(
					'min' => 0, 'max' => 100
				),
				'from_budget' => '',
				'to_budget' => ''
			),
			'names'  => (object)array(
				'title' => '',
				'gender' => '',
				'mtitle' => '',
				'birthplace' => '',
				'birthcountry' => '',
				'amplua' => ''
			)
		);

		$items = new JRegistry;
		$items->loadObject($_items);
		$filters = $input->post->get('filters', $items, 'array');
echo '<pre>';
print_r($filters);
echo '</pre>';

		return $filters;
	}
}
