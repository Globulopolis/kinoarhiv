<?php defined('_JEXEC') or die;

class KinoarhivViewNames extends JViewLegacy {
	protected $state = null;
	protected $items = null;
	protected $pagination = null;

	public function display($tpl = null) {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		$state = $this->get('State');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}

		$params = $app->getParams('com_kinoarhiv');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		// Prepare the data
		foreach ($items as $key => $item) {
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
				$items[$key]->big_poster = JURI::base().$params->get('media_actor_photo_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/photo/'.$item->filename;
				$item->poster = JURI::base().$params->get('media_actor_photo_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/photo/thumb_'.$item->filename;
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

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = '';
		$menu = $menus->getActive();
		$pathway = $app->getPathway();

		$title = JText::_('COM_KA_PERSONS');
		// Create a new pathway object
		$path = (object)array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=names&Itemid='.$this->itemid
		);

		$pathway->setPathway(array($path));
		$this->document->setTitle($title);

		if ($menu->params->get('menu-meta_description') != '') {
			$this->document->setDescription($menu->params->get('menu-meta_description'));
		} else {
			$this->document->setDescription($this->params->get('meta_description'));
		}

		if ($menu->params->get('menu-meta_keywords') != '') {
			$this->document->setMetadata('keywords', $menu->params->get('menu-meta_keywords'));
		} else {
			$this->document->setMetadata('keywords', $this->params->get('meta_keywords'));
		}

		if ($menu->params->get('robots') != '') {
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

		// Add feed links
		if ($this->params->get('show_feed_link', 1)) {
			$link = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
