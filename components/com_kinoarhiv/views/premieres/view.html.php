<?php defined('_JEXEC') or die;

class KinoarhivViewPremieres extends JViewLegacy {
	protected $state = null;
	protected $items = null;
	protected $pagination = null;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		$state = $this->get('State');
		$items = $this->get('Items');
		$list = $this->get('SelectList');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		$params = $app->getParams('com_kinoarhiv');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		// Prepare the data
		/*foreach ($items as &$item) {
			$item->year_str = ($item->year != '0000') ? ' ('.$item->year.')' : '';
			

			// Replace country BB-code
			$item->text = preg_replace_callback('#\[country\s+ln=(.+?)\](.*?)\[/country\]#i', function ($matches) {
				$html = JText::_($matches[1]);

				$cn = preg_replace('#\[cn=(.+?)\](.+?)\[/cn\]#', '<img src="'.JURI::base().'components/com_kinoarhiv/assets/themes/component/default/images/icons/countries/$1.png" border="0" alt="$2" class="ui-icon-country" /> $2', $matches[2]);

				return $html.$cn;
			}, $item->text);

			// Replace genres BB-code
			$item->text = preg_replace_callback('#\[genres\s+ln=(.+?)\](.*?)\[/genres\]#i', function ($matches) {
				return JText::_($matches[1]).$matches[2];
			}, $item->text);


			// Replace person BB-code
			$item->text = preg_replace_callback('#\[names\s+ln=(.+?)\](.*?)\[/names\]#i', function ($matches) {
				$html = JText::_($matches[1]);

				$name = preg_replace('#\[name=(.+?)\](.+?)\[/name\]#', '<a href="'.JRoute::_('index.php?option=com_kinoarhiv&view=name&id=$1&Itemid='.$this->itemid, false).'" title="$2">$2</a>', $matches[2]);

				return $html.$name;
			}, $item->text);

			if (empty($item->filename)) {
				$item->poster = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png';
				$item->poster_width = 128;
				$item->poster_height = 128;
				$item->y_poster = '';
			} else {
				$item->big_poster = JURI::base().$params->get('media_posters_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/posters/'.$item->filename;
				$item->poster = JURI::base().$params->get('media_posters_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/posters/thumb_'.$item->filename;
				$item->poster_width = (int)$params->get('size_x_posters');
				$orig_poster_size = explode('x', $item->dimension);
				$item->poster_height = floor(($item->poster_width * $orig_poster_size[1]) / $orig_poster_size[0]);
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
		}*/

		$this->params = &$params;
		$this->items = &$items;
		$this->selectlist = &$list;
		$this->pagination = &$pagination;
		$this->lang = &$lang;

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
		$title = $menu->title;

		// Create a new pathway object
		$path = (object)array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=premieres&Itemid='.$this->itemid
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
	}
}