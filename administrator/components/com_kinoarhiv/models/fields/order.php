<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('JPATH_BASE') or die;

/**
 * Form Field class for the Kinoarhiv.
 *
 * @since  3.0
 */
class JFormFieldOrder extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $type = 'Order';

	/**
	 * Method to get the field input.
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.0
	 */
	protected function getInput()
	{
		$db = JFactory::getDbo();
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
			$query = $db->getQuery(true)
				->select('rel.ordering AS value, cn.name AS text')
				->from($db->quoteName('#__ka_rel_countries', 'rel'))
				->join('LEFT', $db->quoteName('#__ka_countries', 'cn') . ' ON cn.id = rel.country_id')
				->where('rel.movie_id = ' . (int) $movie_id)
				->order('rel.ordering');

			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $country_id ? 0 : 1);
		}
		elseif ($this->element['data'] == 'genres')
		{
			// Get some field values from the form.
			$genre_id = (int) $this->form->getValue('genre_id');
			$movie_id = (int) $this->form->getValue('movie_id');

			$query = $db->getQuery(true)
				->select('rel.ordering AS value, g.name AS text')
				->from($db->quoteName('#__ka_rel_genres', 'rel'))
				->join('LEFT', $db->quoteName('#__ka_genres', 'g') . ' ON g.id = rel.genre_id')
				->where('rel.movie_id = ' . (int) $movie_id)
				->order('rel.ordering');

			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $genre_id ? 0 : 1);
		}
		elseif ($this->element['data'] == 'premieres')
		{
			$input = JFactory::getApplication()->input;

			// Get some field values from the form.
			$premiere_id = $input->get('id', array(), 'array');
			$movie_id = (int) $this->form->getValue('movie_id');

			$query = $db->getQuery(true)
				->select('ordering AS value')
				->select("CONCAT_WS(' | ', (DATE_FORMAT(premiere_date, '%Y-%m-%d')), (SELECT name FROM #__ka_countries WHERE id = country_id)) AS text")
				->from($db->quoteName('#__ka_premieres'))
				->where('movie_id = ' . (int) $movie_id)
				->order('ordering');

			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, (isset($premiere_id[0]) && !empty($premiere_id[0])) ? 0 : 1);
		}
		elseif ($this->element['data'] == 'releases')
		{
			$input = JFactory::getApplication()->input;

			// Get some field values from the form.
			$release_id = $input->get('id', array(), 'array');
			$movie_id = (int) $this->form->getValue('movie_id');

			$query = $db->getQuery(true)
				->select('ordering AS value')
				->select("CONCAT_WS(' | ', (DATE_FORMAT(release_date, '%Y-%m-%d')), (SELECT name FROM #__ka_countries WHERE id = country_id)) AS text")
				->from($db->quoteName('#__ka_releases'))
				->where('movie_id = ' . (int) $movie_id)
				->order('ordering');

			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, (isset($release_id[0]) && !empty($release_id[0])) ? 0 : 1);
		}

		return implode($html);
	}
}
