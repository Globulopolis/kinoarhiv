<?php defined('_JEXEC') or die;

class KinoarhivModelSearch extends JModelLegacy {
	public function getItems() {
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$default_value = array(array('value' => '', 'text' => '-'));
		$items = (object)array(
			'movies' => (object)array()
		);

		// Get years for movies
		$db->setQuery("SELECT SUBSTRING(`year`, 1, 4) AS `value`, SUBSTRING(`year`, 1, 4) AS `text` FROM ".$db->quoteName('#__ka_movies')." GROUP BY `year` ORDER BY `year` DESC");
		$from_year = $db->loadObjectList();

		$items->movies->from_year = array_merge($default_value, $from_year);
		$items->movies->to_year = &$items->movies->from_year;

		// Get the list of countries
		$db->setQuery("SELECT `id` AS `value`, `name` AS `text` FROM ".$db->quoteName('#__ka_countries')." WHERE `state` = 1 AND `language` IN (".$db->quote($lang->getTag()).",'*') ORDER BY `name` ASC");
		$countries = $db->loadObjectList();

		$items->movies->countries = array_merge($default_value, $countries);

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

		$items->movies->vendors = array_merge($default_value, $vendors);

		// Get the list of genres
		$db->setQuery("SELECT `id` AS `value`, `name` AS `text` FROM ".$db->quoteName('#__ka_genres')." WHERE `state` = 1 AND `language` IN (".$db->quote($lang->getTag()).",'*') AND `access` IN (".$groups.") ORDER BY `name` ASC");
		$genres = $db->loadObjectList();

		$items->movies->genres = array_merge(
			array(array('value' => '', 'text' => '')), // The default values must be an empty for proper displaying in the chosen multiselect
			$genres
		);

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
		$db->setQuery("SELECT `budget` AS `value`, `budget` AS `text` FROM ".$db->quoteName('#__ka_movies')." WHERE `state` = 1 AND `language` IN (".$db->quote($lang->getTag()).",'*') AND `access` IN (".$groups.") GROUP BY `budget` ORDER BY `budget` ASC");
		$budgets = $db->loadObjectList();

		$items->movies->from_budget = array_merge(
			array(array('value' => '', 'text' => '-')),
			$budgets
		);
		$items->movies->to_budget = &$items->movies->from_budget;

		return $items;
	}
}
