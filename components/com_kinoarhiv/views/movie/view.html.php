<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivViewMovie extends JViewLegacy {
	protected $form;
	protected $item = null;
	protected $items = null;
	protected $filters = null;
	protected $pagination = null;
	protected $page;
	protected $params;
	protected $config;
	protected $user;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$this->page = $app->input->get('page', 'movie', 'cmd');
		$this->itemid = $app->input->get('Itemid');

		switch ($this->page) {
			case 'cast': $this->cast(); break;
			case 'wallpapers': $this->wallpp(); break;
			case 'posters': $this->posters(); break;
			case 'screenshots': $this->screenshots(); break;
			case 'awards': $this->awards(); break;
			case 'trailers': $this->trailers(); break;
			case 'soundtracks': $this->sound(); break;
			default: $this->info($tpl); break;
		}
	}

	/**
	 * Method to get and show movie info data.
	 */
	protected function info($tpl) {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		$item = $this->get('Data');
		$items = $this->get('Items');
		$form = $this->get('Form');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')) || is_null($item)) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$config = JFactory::getConfig();

		// Prepare the data
		$item->text = ''; // Workaround for plugin interaction. Article must contain $text item.
		$item->year_str = ($item->year != '0000') ? ' ('.$item->year.')' : '';

		if (empty($item->filename)) {
			$item->poster = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png';
			$item->y_poster = '';
		} else {
			if (JString::substr($params->get('media_posters_root_www'), 0, 1) == '/') {
				$item->poster = JUri::base().JString::substr($params->get('media_posters_root_www'), 1).'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/posters/thumb_'.$item->filename;
			} else {
				$item->poster = $params->get('media_posters_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/posters/thumb_'.$item->filename;
			}
			$item->y_poster = ' y-poster';
		}

		if (!empty($item->desc)) {
			$item->desc = str_replace("\n", "<br />", $item->desc);
			$item->desc = str_replace(array('[code]', '[/code]'), array('<pre>', '</pre>'), $item->desc);
		}

		$item->_length = strftime('%H:%M', strtotime($item->length));
		list($hours, $minutes) = explode(':', $item->_length);
		$item->_hr_length = $hours * 60 + $minutes;

		if (!empty($item->rate_sum_loc) && !empty($item->rate_loc)) {
			$plural = $lang->getPluralSuffixes($item->rate_loc);
			$item->rate_loc_c = round($item->rate_sum_loc / $item->rate_loc, (int)$params->get('vote_summ_precision'));
			$item->rate_loc_label = JText::sprintf('COM_KA_RATE_LOCAL_'.$plural[0], $item->rate_loc_c, (int)$params->get('vote_summ_num'), $item->rate_loc);
		} else {
			$item->rate_loc_c = 0;
			$item->rate_loc_label = JText::_('COM_KA_RATE_NO');
		}

		// Process slides
		if (($item->attribs->slider == '' && $params->get('slider') == 1) || $item->attribs->slider == 1) {
			if (!empty($item->slides)) {
				$_alias = JString::substr($item->alias, 0, 1);
				$file_path = $params->get('media_scr_root').DIRECTORY_SEPARATOR.$_alias.DIRECTORY_SEPARATOR.$item->id.DIRECTORY_SEPARATOR.'screenshots'.DIRECTORY_SEPARATOR;

				// Strip first slash
				if (strpos($params->get('media_posters_root'), '/', 0) === false) {
					$file_path = substr($file_path, 1);
				}

				foreach ($item->slides as $key => $slide) {
					if (file_exists($file_path.$slide->filename)) {
						// Original image
						if (JString::substr($params->get('media_scr_root_www'), 0, 1) == '/') {
							$item->slides[$key]->image = JUri::base().JString::substr($params->get('media_scr_root_www'), 1).'/'.$_alias.'/'.$item->id.'/screenshots/'.$slide->filename;
						} else {
							$item->slides[$key]->image = $params->get('media_scr_root_www').'/'.$_alias.'/'.$item->id.'/screenshots/'.$slide->filename;
						}

						// Thumbnail
						if (JString::substr($params->get('media_scr_root_www'), 0, 1) == '/') {
							$item->slides[$key]->th_image = JUri::base().JString::substr($params->get('media_scr_root_www'), 1).'/'.$_alias.'/'.$item->id.'/screenshots/thumb_'.$slide->filename;
						} else {
							$item->slides[$key]->th_image = $params->get('media_scr_root_www').'/'.$_alias.'/'.$item->id.'/screenshots/thumb_'.$slide->filename;
						}
						$item->slides[$key]->th_image_width = (int)$params->get('size_x_scr');
						$orig_scr_size = explode('x', $slide->dimension);
						$item->slides[$key]->th_image_height = floor(($item->slides[$key]->th_image_width * $orig_scr_size[1]) / $orig_scr_size[0]);
					}
				}
			}
		}

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$item->id.'&Itemid='.$this->itemid), false);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = &$params;
		$this->config = &$config;
		$this->item = &$item;
		$this->items = &$items; // Reviews
		$this->user = &$user;
		$this->pagination = &$pagination;
		$this->metadata = json_decode($item->metadata);
		$this->form = &$form;
		$this->lang = &$lang;

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid));

		parent::display($tpl);
	}

	/**
	 * Method to get and show full cast and crew.
	 */
	protected function cast() {
		$item = $this->get('Cast');

		if (count($errors = $this->get('Errors')) || is_null($item)) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		$item->year_str = ($item->year != '0000') ? ' ('.$item->year.')' : '';

		$this->params = &$params;
		$this->item = &$item;
		$this->metadata = json_decode($item->metadata);

		$this->_prepareDocument();
		$pathway = JFactory::getApplication()->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid));
		$pathway->addItem(JText::_('COM_KA_MOVIE_CREATORS'), JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id='.$this->item->id.'&Itemid='.$this->itemid));

		parent::display('cast');
	}

	protected function wallpp() {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('MovieData');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')) || is_null($items)) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		if (($item->attribs->tab_movie_wallpp === '' && $params->get('tab_movie_wallpp') === '0') || $item->attribs->tab_movie_wallpp === '0') {
			$id = $app->input->get('id', null, 'int');
			GlobalHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id.'&Itemid='.$this->itemid, false));
		}

		$item->year_str = ($item->year != '0000') ? ' ('.$item->year.')' : '';
		$item->text = '';

		// Check for files
		foreach ($items as $key=>$_item) {
			$file_path = $params->get('media_wallpapers_root').DIRECTORY_SEPARATOR.JString::substr($item->alias, 0, 1).DIRECTORY_SEPARATOR.$item->id.DIRECTORY_SEPARATOR.'wallpapers'.DIRECTORY_SEPARATOR;
			$items[$key]->th_image_width = 200;
			$items[$key]->th_image_height = 200;

			// Strip first slash
			if (strpos($params->get('media_posters_root'), '/', 0) === false) {
				$file_path = substr($file_path, 1);
			}

			if (!file_exists($file_path.$_item->filename)) {
				$items[$key]->image = 'javascript:void(0);';
				$items[$key]->th_image = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_wp.png';
			} else {
				if (JString::substr($params->get('media_wallpapers_root_www'), 0, 1) == '/') {
					$items[$key]->image = JUri::base().JString::substr($params->get('media_wallpapers_root_www'), 1).'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/wallpapers/'.$_item->filename;
				} else {
					$items[$key]->image = $params->get('media_wallpapers_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/wallpapers/'.$_item->filename;
				}

				if (file_exists($file_path.DIRECTORY_SEPARATOR.'thumb_'.$_item->filename)) {
					if (JString::substr($params->get('media_wallpapers_root_www'), 0, 1) == '/') {
						$items[$key]->th_image = JUri::base().JString::substr($params->get('media_wallpapers_root_www'), 1).'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/wallpapers/thumb_'.$_item->filename;
					} else {
						$items[$key]->th_image = $params->get('media_wallpapers_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/wallpapers/thumb_'.$_item->filename;
					}
					$items[$key]->th_image_width = (int)$params->get('size_x_wallpp');
					$orig_wp_size = explode('x', $_item->dimension);
					$items[$key]->th_image_height = floor(($items[$key]->th_image_width * $orig_wp_size[1]) / $orig_wp_size[0]);
				} else {
					$items[$key]->th_image = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_wp.png';
				}
			}
		}

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=wallpapers&id='.$item->id.'&Itemid='.$this->itemid), false);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = &$params;
		$this->item = &$item;
		$this->items = &$items;
		$this->filters = $this->getDimensionList();
		$this->pagination = &$pagination;
		$this->metadata = json_decode($item->metadata);

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid));
		$pathway->addItem(JText::_('COM_KA_MOVIE_TAB_WALLPP'), JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=wallpapers&id='.$this->item->id.'&Itemid='.$this->itemid));

		parent::display('wallpp');
	}

	protected function posters() {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('MovieData');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')) || is_null($items)) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		if (($item->attribs->tab_movie_posters === '' && $params->get('tab_movie_posters') === '0') || $item->attribs->tab_movie_posters === '0') {
			$id = $app->input->get('id', null, 'int');
			GlobalHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id.'&Itemid='.$this->itemid, false));
		}

		$item->year_str = ($item->year != '0000') ? ' ('.$item->year.')' : '';
		$item->text = '';

		// Check for files
		foreach ($items as $key=>$_item) {
			$file_path = $params->get('media_posters_root').DIRECTORY_SEPARATOR.JString::substr($item->alias, 0, 1).DIRECTORY_SEPARATOR.$item->id.DIRECTORY_SEPARATOR.'posters'.DIRECTORY_SEPARATOR;
			$items[$key]->th_image_width = 128;
			$items[$key]->th_image_height = 128;

			// Strip first slash
			if (strpos($params->get('media_posters_root'), '/', 0) === false) {
				$file_path = substr($file_path, 1);
			}

			if (!file_exists($file_path.$_item->filename)) {
				$items[$key]->image = 'javascript:void(0);';
				$items[$key]->th_image = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png';
			} else {
				if (JString::substr($params->get('media_posters_root_www'), 0, 1) == '/') {
					$items[$key]->image = JUri::base().JString::substr($params->get('media_posters_root_www'), 1).'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/posters/'.$_item->filename;
				} else {
					$items[$key]->image = $params->get('media_posters_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/posters/'.$_item->filename;
				}

				if (file_exists($file_path.DIRECTORY_SEPARATOR.'thumb_'.$_item->filename)) {
					if (JString::substr($params->get('media_posters_root_www'), 0, 1) == '/') {
						$items[$key]->th_image = JUri::base().JString::substr($params->get('media_posters_root_www'), 1).'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/posters/thumb_'.$_item->filename;
					} else {
						$items[$key]->th_image = $params->get('media_posters_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/posters/thumb_'.$_item->filename;
					}
					$items[$key]->th_image_width = (int)$params->get('size_x_posters');
					$orig_poster_size = explode('x', $_item->dimension);
					$items[$key]->th_image_height = floor(($items[$key]->th_image_width * $orig_poster_size[1]) / $orig_poster_size[0]);
				} else {
					$items[$key]->th_image = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png';
				}
			}
		}

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id='.$item->id.'&Itemid='.$this->itemid), false);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = &$params;
		$this->item = &$item;
		$this->items = &$items;
		$this->pagination = &$pagination;
		$this->metadata = json_decode($item->metadata);

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid));
		$pathway->addItem(JText::_('COM_KA_MOVIE_TAB_POSTERS'), JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id='.$this->item->id.'&Itemid='.$this->itemid));

		parent::display('posters');
	}

	protected function screenshots() {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('MovieData');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')) || is_null($items)) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		if (($item->attribs->tab_movie_scr === '' && $params->get('tab_movie_scr') === '0') || $item->attribs->tab_movie_scr === '0') {
			$id = $app->input->get('id', null, 'int');
			GlobalHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id.'&Itemid='.$this->itemid, false));
		}

		$item->year_str = ($item->year != '0000') ? ' ('.$item->year.')' : '';
		$item->text = '';

		// Check for files
		foreach ($items as $key=>$_item) {
			$file_path = $params->get('media_scr_root').DIRECTORY_SEPARATOR.JString::substr($item->alias, 0, 1).DIRECTORY_SEPARATOR.$item->id.DIRECTORY_SEPARATOR.'screenshots'.DIRECTORY_SEPARATOR;
			$items[$key]->th_image_width = 200;
			$items[$key]->th_image_height = 200;

			// Strip first slash
			if (strpos($params->get('media_posters_root'), '/', 0) === false) {
				$file_path = substr($file_path, 1);
			}

			if (!file_exists($file_path.$_item->filename)) {
				$items[$key]->image = 'javascript:void(0);';
				$items[$key]->th_image = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_wp.png';
			} else {
				if (JString::substr($params->get('media_scr_root_www'), 0, 1) == '/') {
					$items[$key]->image = JUri::base().JString::substr($params->get('media_scr_root_www'), 1).'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/screenshots/'.$_item->filename;
				} else {
					$items[$key]->image = $params->get('media_scr_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/screenshots/'.$_item->filename;
				}

				if (file_exists($file_path.DIRECTORY_SEPARATOR.'thumb_'.$_item->filename)) {
					if (JString::substr($params->get('media_scr_root_www'), 0, 1) == '/') {
						$items[$key]->th_image = JUri::base().JString::substr($params->get('media_scr_root_www'), 1).'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/screenshots/thumb_'.$_item->filename;
					} else {
						$items[$key]->th_image = $params->get('media_scr_root_www').'/'.JString::substr($item->alias, 0, 1).'/'.$item->id.'/screenshots/thumb_'.$_item->filename;
					}
					$items[$key]->th_image_width = (int)$params->get('size_x_scr');
					$orig_scr_size = explode('x', $_item->dimension);
					$items[$key]->th_image_height = floor(($items[$key]->th_image_width * $orig_scr_size[1]) / $orig_scr_size[0]);
				} else {
					$items[$key]->th_image = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_wp.png';
				}
			}
		}

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=screenshots&id='.$item->id.'&Itemid='.$this->itemid), false);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = &$params;
		$this->item = &$item;
		$this->items = &$items;
		$this->pagination = &$pagination;
		$this->metadata = json_decode($item->metadata);

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid));
		$pathway->addItem(JText::_('COM_KA_MOVIE_TAB_SCRSHOTS'), JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=screenshots&id='.$this->item->id.'&Itemid='.$this->itemid));

		parent::display('screenshots');
	}

	protected function awards() {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('Awards');

		if (count($errors = $this->get('Errors')) || is_null($item)) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		if (($item->attribs->tab_movie_awards === '' && $params->get('tab_movie_awards') === '0') || $item->attribs->tab_movie_awards === '0') {
			$id = $app->input->get('id', null, 'int');
			GlobalHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id.'&Itemid='.$this->itemid, false));
		}

		// Prepare the data
		$item->year_str = ($item->year != '0000') ? ' ('.$item->year.')' : '';
		$item->text = '';

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=awards&id='.$item->id.'&Itemid='.$this->itemid), false);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = &$params;
		$this->item = &$item;
		$this->metadata = json_decode($item->metadata);

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid));
		$pathway->addItem(JText::_('COM_KA_MOVIE_TAB_AWARDS'), JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=awards&id='.$this->item->id.'&Itemid='.$this->itemid));

		parent::display('awards');
	}

	/**
	 * Method to get and show trailers.
	 */
	protected function trailers() {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('Trailers');

		if (count($errors = $this->get('Errors')) || is_null($item)) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		if (($item->attribs->tab_movie_tr === '' && $params->get('tab_movie_tr') === '0') || $item->attribs->tab_movie_tr === '0') {
			$id = $app->input->get('id', null, 'int');
			GlobalHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id.'&Itemid='.$this->itemid, false));
		}

		// Check if player folder exists.
		if (!file_exists(JPATH_ROOT.'/components/com_kinoarhiv/assets/players/'.$params->get('player_type'))) {
			$player_layout = ($params->get('player_type') == '-1') ? 'trailer' : 'trailer_'.$params->get('player_type');
			GlobalHelper::eventLog(JText::sprintf('COM_KA_PLAYER_FOLDER_NOT_FOUND', $player_layout));

			$params->set('player_type', '-1');
		}

		$user = JFactory::getUser();
		$item->year_str = ($item->year != '0000') ? ' ('.$item->year.')' : '';
		$item->text = '';

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=trailers&id='.$item->id.'&Itemid='.$this->itemid), false);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = &$params;
		$this->item = &$item;
		$this->user = &$user;
		$this->metadata = json_decode($item->metadata);

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid));
		$pathway->addItem(JText::_('COM_KA_MOVIE_TAB_TRAILERS'), JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=trailers&id='.$this->item->id.'&Itemid='.$this->itemid));

		parent::display('trailers');
	}

	protected function sound() {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('Soundtracks');

		if (count($errors = $this->get('Errors')) || is_null($item)) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		if (($item->attribs->tab_movie_snd === '' && $params->get('tab_movie_snd') === '0') || $item->attribs->tab_movie_snd === '0') {
			$id = $app->input->get('id', null, 'int');
			GlobalHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$id.'&Itemid='.$this->itemid, false));
		}

		$item->year_str = ($item->year != '0000') ? ' ('.$item->year.')' : '';
		$item->text = '';

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=soundtracks&id='.$item->id.'&Itemid='.$this->itemid), false);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = &$params;
		$this->item = &$item;
		$this->metadata = json_decode($item->metadata);

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$this->item->id.'&Itemid='.$this->itemid));
		$pathway->addItem(JText::_('COM_KA_MOVIE_TAB_SOUND'), JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=soundtracks&id='.$this->item->id.'&Itemid='.$this->itemid));

		parent::display('soundtracks');
	}

	protected function getDimensionList() {
		$app = JFactory::getApplication();
		$active = $app->input->get('dim_filter', '0', 'string');
		$dimensions = $this->get('DimensionFilters');
		array_push($dimensions, array('width'=>'0', 'title'=>JText::_('COM_KA_FILTERS_DIMENSION_NOSORT')));

		// Build select
		$list = '<label for="dim_filter">'.JText::_('COM_KA_FILTERS_DIMENSION').'</label>
		<select name="dim_filter" id="dim_filter" class="inputbox" onchange="this.form.submit()" autocomplete="off">';
			foreach ($dimensions as $dimension) {
				$selected = ($dimension['width'] == $active) ? ' selected="selected"' : '';
				$list .= '<option value="'.$dimension['width'].'"'.$selected.'>'.$dimension['title'].'</option>';
			}
		$list .= '</select>';

		return array('dimensions.list' => $list);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$pathway = $app->getPathway();

		$title = ($menu && $menu->title && $menu->link == 'index.php?option=com_kinoarhiv&view=movies') ? $menu->title : JText::_('COM_KA_MOVIES');
		// Create a new pathway object
		$path = (object)array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=movies&Itemid='.$this->itemid
		);

		$pathway->setPathway(array($path));
		$this->document->setTitle($this->item->title);

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
