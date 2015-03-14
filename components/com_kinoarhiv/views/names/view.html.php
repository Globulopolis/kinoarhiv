<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivViewNames extends JViewLegacy {
	protected $items = null;
	protected $pagination = null;

	public function display($tpl = null) {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
		$activeFilters = $this->get('FiltersData');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		// Prepare the data
		foreach ($items as $key => $item) {
			$item->attribs = json_decode($item->attribs);

			// Compose a date string
			$date_range = '';
			if ($item->date_of_birth != '0000') {
				$date_range .= ' ('.$item->date_of_birth;

				if ($item->date_of_death != '0000') {
					$date_range .= ' - '.$item->date_of_death;
				}

				$date_range .= ')';
			}
			$items[$key]->date_range = $date_range;

			// Compose a title
			if (!empty($item->name) && !empty($item->latin_name)) {
				$items[$key]->title = $item->name.' / '.$item->latin_name;
			} elseif (!empty($item->name)) {
				$items[$key]->title = $item->name;
			} else {
				$items[$key]->title = $item->latin_name;
			}

			if (empty($item->filename)) {
				if ($item->gender == 0) { // Female
					$no_cover = 'no_name_cover_f';
				} else {
					$no_cover = 'no_name_cover_m';
				}
				$item->poster = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/'.$no_cover.'.png';
				$item->poster_width = 128;
				$item->poster_height = 128;
				$item->y_poster = '';
			} else {
				if (JString::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/') {
					$items[$key]->big_poster = JUri::base().JString::substr($params->get('media_actor_photo_root_www'), 1).'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/photo/'.$item->filename;
					$item->poster = JUri::base().JString::substr($params->get('media_actor_photo_root_www'), 1).'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/photo/thumb_'.$item->filename;
				} else {
					$items[$key]->big_poster = $params->get('media_actor_photo_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/photo/'.$item->filename;
					$item->poster = $params->get('media_actor_photo_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/photo/thumb_'.$item->filename;
				}
				$item->poster_width = (int)$params->get('size_x_posters');
				$orig_poster_size = explode('x', $item->dimension);
				$item->poster_height = floor(($item->poster_width * $orig_poster_size[1]) / $orig_poster_size[0]);
				$item->y_poster = ' y-poster';
			}
		}

		$this->params = &$params;
		$this->items['names'] = &$items;
		$this->pagination = &$pagination;
		$this->user = &$user;
		$this->lang = &$lang;
		$this->activeFilters = &$activeFilters;

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$pathway = $app->getPathway();
		$title = ($menu && $menu->title) ? $menu->title : JText::_('COM_KA_PERSONS');

		// Create a new pathway object
		$path = (object)array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=names&Itemid='.$this->itemid
		);

		$pathway->setPathway(array($path));
		$this->document->setTitle($title);

		if ($menu && $menu->params->get('menu-meta_description') != '') {
			$this->document->setDescription($menu->params->get('menu-meta_description'));
		} else {
			$this->document->setDescription($this->params->get('meta_description'));
		}

		if ($menu && $menu->params->get('menu-meta_keywords') != '') {
			$this->document->setMetadata('keywords', $menu->params->get('menu-meta_keywords'));
		} else {
			$this->document->setMetadata('keywords', $this->params->get('meta_keywords'));
		}

		if ($menu && $menu->params->get('robots') != '') {
			$this->document->setMetadata('robots', $menu->params->get('robots'));
		} else {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($this->params->get('generator') == 'none') {
			$this->document->setGenerator('');
		} elseif ($this->params->get('generator') == 'site') {
			$this->document->setGenerator($this->document->getGenerator());
		} else {
			$this->document->setGenerator($this->params->get('generator'));
		}
	}
}
