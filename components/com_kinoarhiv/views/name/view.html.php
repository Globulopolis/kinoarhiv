<?php defined('_JEXEC') or die;

/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */
class KinoarhivViewName extends JViewLegacy
{
	protected $item = null;
	protected $items = null;
	protected $filters = null;
	protected $page;

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$this->page = $app->input->get('page', '', 'cmd');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		switch ($this->page) {
			case 'wallpapers':
				$this->wallpp();
				break;
			case 'photos':
				$this->photo();
				break;
			case 'awards':
				$this->awards();
				break;
			default:
				$this->info($tpl);
				break;
		}
	}

	/**
	 * Method to get and show person info.
	 */
	protected function info($tpl)
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		$item = $this->get('Data');

		if (count($errors = $this->get('Errors')) || is_null($item)) {
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		// Prepare the data
		// Build title string
		$item->title = '';
		if ($item->name != '') {
			$item->title .= $item->name;
		}
		if ($item->name != '' && $item->latin_name != '') {
			$item->title .= ' / ';
		}
		if ($item->latin_name != '') {
			$item->title .= $item->latin_name;
		}

		// Build date string
		$item->dates = '';
		if ($item->date_of_birth != '0000') {
			$item->dates .= ' (' . $item->date_of_birth;
		}
		if ($item->date_of_death != '0000') {
			$item->dates .= ' - ' . $item->date_of_death;
		}
		$item->dates .= ')';

		if (empty($item->filename)) {
			if ($item->gender == 0) { // Female
				$no_cover = 'no_name_cover_f';
			} else {
				$no_cover = 'no_name_cover_m';
			}
			$item->poster = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/' . $no_cover . '.png';
			$item->y_poster = '';
		} else {
			if (JString::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/') {
				$item->poster = JUri::base() . JString::substr($params->get('media_actor_photo_root_www'), 1) . '/' . JString::substr($item->alias, 0, 1) . '/' . $item->id . '/photo/thumb_' . $item->filename;
			} else {
				$item->poster = $params->get('media_actor_photo_root_www') . '/' . JString::substr($item->alias, 0, 1) . '/' . $item->id . '/photo/thumb_' . $item->filename;
			}
			$item->y_poster = ' y-poster';
		}

		$lc_offset = JFactory::getConfig()->get('offset');
		$date_of_birth_1 = new DateTime($item->date_of_birth_raw . ' ' . date('H:i:s'), new DateTimeZone($lc_offset));
		$date_of_birth_2 = new DateTime('now', new DateTimeZone($lc_offset));
		$_interval = $date_of_birth_1->diff($date_of_birth_2);
		$interval = ($_interval->y > 100) ? substr($_interval->y, -2) : $_interval->y;
		$str_age = '';

		if ($interval >= 5 && $interval <= 14) {
			$str_age = JText::_('COM_KA_NAMES_AGE_01');
		} else {
			$interval = substr($_interval->y, -1);

			if ($interval == 0 || ($interval >= 5 && $interval <= 9))
				$str_age = JText::_('COM_KA_NAMES_AGE_01');
			if ($interval == 1)
				$str_age = JText::_('COM_KA_NAMES_AGE_02');
			if ($interval >= 2 && $interval <= 4)
				$str_age = JText::_('COM_KA_NAMES_AGE_03');
		}
		$item->date_of_birth_interval_str = $_interval->y . ' ' . $str_age;

		if (!empty($item->desc)) {
			$item->desc = str_replace("\n", "<br />", $item->desc);
		}

		$this->params = &$params;
		$this->item = &$item;
		$this->user = &$user;

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid));

		parent::display($tpl);
	}

	protected function wallpp()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('NameData');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')) || is_null($items)) {
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_name_wallpp === '' && $params->get('tab_name_wallpp') === '0') || $item->attribs->tab_name_wallpp === '0') {
			$id = $app->input->get('id', null, 'int');
			KAComponentHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $id . '&Itemid=' . $this->itemid, false));
		}

		// Build title string
		$item->title = '';
		if ($item->name != '') {
			$item->title .= $item->name;
		}
		if ($item->name != '' && $item->latin_name != '') {
			$item->title .= ' / ';
		}
		if ($item->latin_name != '') {
			$item->title .= $item->latin_name;
		}

		// Check for files
		if (count($items) > 0) {
			foreach ($items as $key => $_item) {
				$file_path = $params->get('media_actor_wallpapers_root') . DIRECTORY_SEPARATOR . JString::substr($item->alias, 0, 1) . DIRECTORY_SEPARATOR . $item->id . DIRECTORY_SEPARATOR . 'wallpapers' . DIRECTORY_SEPARATOR;
				$items[$key]->th_image_width = 200;
				$items[$key]->th_image_height = 200;

				if (!file_exists($file_path . $_item->filename)) {
					$items[$key]->image = 'javascript:void(0);';
					$items[$key]->th_image = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_wp.png';
				} else {
					if (JString::substr($params->get('media_actor_wallpapers_root_www'), 0, 1) == '/') {
						$items[$key]->image = JUri::base() . JString::substr($params->get('media_actor_wallpapers_root_www'), 1) . '/' . JString::substr($item->alias, 0, 1) . '/' . $item->id . '/wallpapers/' . $_item->filename;
					} else {
						$items[$key]->image = $params->get('media_actor_wallpapers_root_www') . '/' . JString::substr($item->alias, 0, 1) . '/' . $item->id . '/wallpapers/' . $_item->filename;
					}

					if (file_exists($file_path . DIRECTORY_SEPARATOR . 'thumb_' . $_item->filename)) {
						if (JString::substr($params->get('media_actor_wallpapers_root_www'), 0, 1) == '/') {
							$items[$key]->th_image = JUri::base() . JString::substr($params->get('media_actor_wallpapers_root_www'), 1) . '/' . JString::substr($item->alias, 0, 1) . '/' . $item->id . '/wallpapers/thumb_' . $_item->filename;
						} else {
							$items[$key]->th_image = $params->get('media_actor_wallpapers_root_www') . '/' . JString::substr($item->alias, 0, 1) . '/' . $item->id . '/wallpapers/thumb_' . $_item->filename;
						}
						$items[$key]->th_width = (int)$params->get('size_x_wallpp');
						$orig_img_size = explode('x', $_item->dimension);
						$items[$key]->th_height = floor(($items[$key]->th_width * $orig_img_size[1]) / $orig_img_size[0]);
					} else {
						$items[$key]->th_image = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_wp.png';
					}
				}
			}
		}

		$this->params = &$params;
		$this->item = &$item;
		$this->items = &$items;
		$this->filters = $this->getDimensionList();
		$this->pagination = &$pagination;

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid));
		$pathway->addItem(JText::_('COM_KA_MOVIE_TAB_WALLPP'), JRoute::_('index.php?option=com_kinoarhiv&view=name&page=wallpapers&id=' . $this->item->id . '&Itemid=' . $this->itemid));

		parent::display('wallpp');
	}

	protected function photo()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('NameData');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')) || is_null($items)) {
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_name_photos === '' && $params->get('tab_name_photos') === '0') || $item->attribs->tab_name_photos === '0') {
			$id = $app->input->get('id', null, 'int');
			KAComponentHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $id . '&Itemid=' . $this->itemid, false));
		}

		// Build title string
		$item->title = '';
		if ($item->name != '') {
			$item->title .= $item->name;
		}
		if ($item->name != '' && $item->latin_name != '') {
			$item->title .= ' / ';
		}
		if ($item->latin_name != '') {
			$item->title .= $item->latin_name;
		}

		// Check for files
		if (count($items) > 0) {
			foreach ($items as $key => $_item) {
				$file_path = $params->get('media_actor_photo_root') . DIRECTORY_SEPARATOR . JString::substr($item->alias, 0, 1) . DIRECTORY_SEPARATOR . $item->id . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR;
				$items[$key]->th_image_width = 128;
				$items[$key]->th_image_height = 128;

				if (!file_exists($file_path . $_item->filename)) {
					$items[$key]->image = 'javascript:void(0);';
					if ($_item->gender == 1) {
						$items[$key]->th_image = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_name_cover_m.png';
					} else {
						$items[$key]->th_image = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_name_cover_f.png';
					}
				} else {
					if (JString::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/') {
						$items[$key]->image = JUri::base() . JString::substr($params->get('media_actor_photo_root_www'), 1) . '/' . JString::substr($item->alias, 0, 1) . '/' . $item->id . '/photo/' . $_item->filename;
					} else {
						$items[$key]->image = $params->get('media_actor_photo_root_www') . '/' . JString::substr($item->alias, 0, 1) . '/' . $item->id . '/photo/' . $_item->filename;
					}

					if (file_exists($file_path . DIRECTORY_SEPARATOR . 'thumb_' . $_item->filename)) {
						if (JString::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/') {
							$items[$key]->th_image = JUri::base() . JString::substr($params->get('media_actor_photo_root_www'), 1) . '/' . JString::substr($item->alias, 0, 1) . '/' . $item->id . '/photo/thumb_' . $_item->filename;
						} else {
							$items[$key]->th_image = $params->get('media_actor_photo_root_www') . '/' . JString::substr($item->alias, 0, 1) . '/' . $item->id . '/photo/thumb_' . $_item->filename;
						}
						$items[$key]->th_width = (int)$params->get('size_x_photo');
						$orig_img_size = explode('x', $_item->dimension);
						$items[$key]->th_height = floor(($items[$key]->th_width * $orig_img_size[1]) / $orig_img_size[0]);
					} else {
						if ($_item->gender == 1) {
							$items[$key]->th_image = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_name_cover_m.png';
						} else {
							$items[$key]->th_image = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_name_cover_f.png';
						}
					}
				}
			}
		}

		$this->params = &$params;
		$this->item = &$item;
		$this->items = &$items;
		$this->pagination = &$pagination;

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid));
		$pathway->addItem(JText::_('COM_KA_NAMES_TAB_PHOTO'), JRoute::_('index.php?option=com_kinoarhiv&view=name&page=posters&id=' . $this->item->id . '&Itemid=' . $this->itemid));

		parent::display('photo');
	}

	protected function awards()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$items = $this->get('Awards');

		if (count($errors = $this->get('Errors')) || is_null($items)) {
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($items->attribs->tab_name_awards === '' && $params->get('tab_name_awards') === '0') || $items->attribs->tab_name_awards === '0') {
			$id = $app->input->get('id', null, 'int');
			KAComponentHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $id . '&Itemid=' . $this->itemid, false));
		}

		// Prepare the data
		// Build title string
		$items->title = '';
		if ($items->name != '') {
			$items->title .= $items->name;
		}
		if ($items->name != '' && $items->latin_name != '') {
			$items->title .= ' / ';
		}
		if ($items->latin_name != '') {
			$items->title .= $items->latin_name;
		}

		$this->params = &$params;
		$this->item = &$items;

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid));
		$pathway->addItem(JText::_('COM_KA_NAMES_TAB_AWARDS'), JRoute::_('index.php?option=com_kinoarhiv&view=name&page=awards&id=' . $this->item->id . '&Itemid=' . $this->itemid));

		parent::display('awards');
	}

	protected function getDimensionList()
	{
		$app = JFactory::getApplication();
		$active = $app->input->get('dim_filter', '0', 'string');
		$dimensions = $this->get('DimensionFilters');
		array_push($dimensions, array('width' => '0', 'title' => JText::_('COM_KA_FILTERS_DIMENSION_NOSORT')));

		// Build select
		$list = '<label for="dim_filter">' . JText::_('COM_KA_FILTERS_DIMENSION') . '</label>
		<select name="dim_filter" id="dim_filter" class="inputbox" onchange="this.form.submit()" autocomplete="off">';
		foreach ($dimensions as $dimension) {
			$selected = ($dimension['width'] == $active) ? ' selected="selected"' : '';
			$list .= '<option value="' . $dimension['width'] . '"' . $selected . '>' . $dimension['title'] . '</option>';
		}
		$list .= '</select>';

		return array('dimensions.list' => $list);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$pathway = $app->getPathway();

		$title = ($menu && $menu->title) ? $menu->title : JText::_('COM_KA_PERSONS');
		// Create a new pathway object
		$path = (object)array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=names&Itemid=' . $this->itemid
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
