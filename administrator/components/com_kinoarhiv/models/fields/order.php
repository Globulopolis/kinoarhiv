<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('JPATH_BASE') or die;

class JFormFieldOrder extends JFormField
{
	protected $type = 'Order';

	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		if ($this->element['data'] == 'countries')
		{
			// Get some field values from the form.
			$country_id = (int) $this->form->getValue('country_id');
			$movie_id = (int) $this->form->getValue('movie_id');

			// Build the query for the ordering list.
			$query = "SELECT `rel`.`ordering` AS `value`, `cn`.`name` AS `text`"
				. "\n FROM #__ka_rel_countries AS `rel`"
				. "\n LEFT JOIN #__ka_countries AS `cn` ON `cn`.`id` = `rel`.`country_id`"
				. "\n WHERE `rel`.`movie_id` = " . (int) $movie_id
				. "\n ORDER BY `rel`.`ordering`";

			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $country_id ? 0 : 1);
		}
		elseif ($this->element['data'] == 'genres')
		{
			// Get some field values from the form.
			$genre_id = (int) $this->form->getValue('genre_id');
			$movie_id = (int) $this->form->getValue('movie_id');

			$query = "SELECT `rel`.`ordering` AS `value`, `g`.`name` AS `text`"
				. "\n FROM #__ka_rel_genres AS `rel`"
				. "\n LEFT JOIN #__ka_genres AS `g` ON `g`.`id` = `rel`.`genre_id`"
				. "\n WHERE `rel`.`movie_id` = " . (int) $movie_id
				. "\n ORDER BY `rel`.`ordering`";

			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $genre_id ? 0 : 1);
		}
		elseif ($this->element['data'] == 'premieres')
		{
			$input = JFactory::getApplication()->input;

			// Get some field values from the form.
			$premiere_id = $input->get('id', array(), 'array');
			$movie_id = (int) $this->form->getValue('movie_id');

			$query = "SELECT `ordering` AS `value`, CONCAT_WS(' | ', (DATE_FORMAT(`premiere_date`, '%Y-%m-%d')), (SELECT `name` FROM #__ka_countries WHERE `id` = `country_id`)) AS `text`"
				. "\n FROM #__ka_premieres"
				. "\n WHERE `movie_id` = " . (int) $movie_id
				. "\n ORDER BY `ordering`";

			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, (isset($premiere_id[0]) && !empty($premiere_id[0])) ? 0 : 1);
		}
		elseif ($this->element['data'] == 'releases')
		{
			$input = JFactory::getApplication()->input;

			// Get some field values from the form.
			$release_id = $input->get('id', array(), 'array');
			$movie_id = (int) $this->form->getValue('movie_id');

			$query = "SELECT `ordering` AS `value`, CONCAT_WS(' | ', (DATE_FORMAT(`release_date`, '%Y-%m-%d')), (SELECT `name` FROM #__ka_countries WHERE `id` = `country_id`)) AS `text`"
				. "\n FROM #__ka_releases"
				. "\n WHERE `movie_id` = " . (int) $movie_id
				. "\n ORDER BY `ordering`";

			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, (isset($release_id[0]) && !empty($release_id[0])) ? 0 : 1);
		}

		return implode($html);
	}
}
