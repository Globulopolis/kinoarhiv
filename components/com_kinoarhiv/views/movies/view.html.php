<?php defined('_JEXEC') or die;

class KinoarhivViewMovies extends JViewLegacy {
	protected $state = null;
	protected $items = null;
	protected $pagination = null;

	public function display($tpl = null) {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		$state = $this->get('State');
		$items = $this->get('Items');
		$genres = $this->get('Genres');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}

		$params = $app->getParams('com_kinoarhiv');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		// Prepare the data
		foreach ($items as &$item) {
			$item->year_str = ($item->year != '0000') ? ' ('.$item->year.')' : '';

			// Replace country BB-code
			$item->text = preg_replace('/\[cn=(.*?)\](.*?)\[\/cn\]/isu', '<img src="'.JURI::base().'components/com_kinoarhiv/assets/themes/component/default/images/icons/countries/$1.png" border="0" alt="$2" class="ui-icon-country" /> $2', $item->text);

			// Replace person BB-code
			$item->text = preg_replace('/\[name=(.*?)\](.*?)\[\/name\]/isu', '<a href="'.JRoute::_('index.php?option=com_kinoarhiv&view=name&id=$1&Itemid='.$this->itemid, false).'" title="$2">$2</a>', $item->text);

			if (empty($item->filename)) {
				$item->poster = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png';
				$item->y_poster = '';
			} else {
				$item->big_poster = JURI::base().$params->get('media_posters_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/posters/'.$item->filename;
				$item->poster = JURI::base().$params->get('media_posters_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/posters/thumb_'.$item->filename;
				$item->y_poster = ' y-poster';
			}

			$item->plot = GlobalHelper::limitText($item->plot, $params->get('limit_text'));

			if ($params->get('ratings_show_frontpage') == 1) {
				if (!empty($item->rate_sum_loc) && !empty($item->rate_loc)) {
					$item->rate_loc = round($item->rate_sum_loc / $item->rate_loc, (int)$params->get('vote_summ_precision'));
					$item->rate_loc_label = $item->rate_loc.' '.JText::_('COM_KA_FROM').(int)$params->get('vote_summ_num');
					$item->rate_loc_label_class = ' has-rating';
				} else {
					$item->rate_loc = 0;
					$item->rate_loc_label = '<br />'.JText::_('COM_KA_RATE_NO');
					$item->rate_loc_label_class = ' no-rating';
				}
			}

			$item->event = new stdClass;
			$item->params = new JObject;
			$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$item->id.'&Itemid='.$this->itemid), false);

			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movies', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movies', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movies', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		$this->params = &$params;
		$this->items['movies'] = &$items;
		$this->items['genres'] = &$genres;
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

		$title = JText::_('COM_KA_MOVIES');
		// Create a new pathway object
		$path = (object)array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=movies&Itemid='.$this->itemid
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
