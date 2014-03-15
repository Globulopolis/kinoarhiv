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
		}

		$this->params = &$params;
		$this->items['movies'] = &$items;
		$this->pagination = &$pagination;
		$this->user = &$user;

		$this->_prepareDocument();

		//parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = '';
		$menu = $menus->getActive();

		$title = JText::_('COM_KA_MOVIES');
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
