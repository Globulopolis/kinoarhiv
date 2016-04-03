<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Name View class
 *
 * @since  3.0
 */
class KinoarhivViewName extends JViewLegacy
{
	protected $item = null;

	protected $items = null;

	protected $params;

	protected $user;

	protected $page;

	protected $itemid;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		JLoader::register('KAContentHelper', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'content.php');

		$app = JFactory::getApplication();
		$this->page = $app->input->get('page', '', 'cmd');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		switch ($this->page)
		{
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
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 */
	protected function info($tpl)
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		$item = $this->get('Data');

		if (count($errors = $this->get('Errors')) || is_null($item))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		// Prepare the data
		// Build title string
		$item->title = KAContentHelper::formatItemTitle($item->name, $item->latin_name);

		// Build date string
		$item->dates = '';

		if ($item->date_of_birth != '0000')
		{
			$item->dates .= ' (' . $item->date_of_birth;
		}

		if ($item->date_of_death != '0000')
		{
			$item->dates .= ' - ' . $item->date_of_death;
		}

		$item->dates .= ')';

		if ($params->get('throttle_enable', 0) == 0)
		{
			$checking_path = JPath::clean(
				$params->get('media_actor_photo_root') . DIRECTORY_SEPARATOR . $item->fs_alias .
				DIRECTORY_SEPARATOR . $item->id . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR . $item->filename
			);
			$no_cover = ($item->gender == 0) ? 'no_name_cover_f' : 'no_name_cover_m';

			if (!is_file($checking_path))
			{
				$item->poster = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/' . $no_cover . '.png';
				$dimension = KAContentHelper::getImageSize(
					JPATH_COMPONENT . '/assets/themes/component/' . $params->get('ka_theme') . '/images/' . $no_cover . '.png',
					false
				);
				$item->poster_width = $dimension->width;
				$item->poster_height = $dimension->height;
			}
			else
			{
				$item->fs_alias = rawurlencode($item->fs_alias);

				if (StringHelper::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/')
				{
					$item->poster = JURI::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1) . '/'
						. $item->fs_alias . '/' . $item->id . '/photo/thumb_' . $item->filename;
				}
				else
				{
					$item->poster = $params->get('media_actor_photo_root_www') . '/' . $item->fs_alias . '/' . $item->id . '/photo/thumb_' . $item->filename;
				}

				$dimension = KAContentHelper::getImageSize(
					$item->poster,
					true,
					(int) $params->get('size_x_posters'),
					$item->dimension
				);
				$item->poster_width = $dimension->width;
				$item->poster_height = $dimension->height;
			}
		}
		else
		{
			$item->poster = JRoute::_(
				'index.php?option=com_kinoarhiv&task=media.view&element=name&content=image&type=3&id=' . $item->id .
				'&fa=' . urlencode($item->fs_alias) . '&fn=' . $item->filename . '&format=raw&Itemid=' . $this->itemid .
				'&thumbnail=1&gender=' . $item->gender
			);
			$dimension = KAContentHelper::getImageSize(
				JUri::base() . $item->poster,
				true,
				(int) $params->get('size_x_posters'),
				$item->dimension
			);
			$item->poster_width = $dimension->width;
			$item->poster_height = $dimension->height;
		}

		$lc_offset = JFactory::getConfig()->get('offset');
		$date_of_birth_1 = new DateTime($item->date_of_birth_raw . ' ' . date('H:i:s'), new DateTimeZone($lc_offset));
		$date_of_birth_2 = new DateTime('now', new DateTimeZone($lc_offset));
		$_interval = $date_of_birth_1->diff($date_of_birth_2);
		$interval = ($_interval->y > 100) ? substr($_interval->y, -2) : $_interval->y;
		$str_age = '';

		if ($interval >= 5 && $interval <= 14)
		{
			$str_age = JText::_('COM_KA_NAMES_AGE_01');
		}
		else
		{
			$interval = substr($_interval->y, -1);

			if ($interval == 0 || ($interval >= 5 && $interval <= 9))
			{
				$str_age = JText::_('COM_KA_NAMES_AGE_01');
			}

			if ($interval == 1)
			{
				$str_age = JText::_('COM_KA_NAMES_AGE_02');
			}

			if ($interval >= 2 && $interval <= 4)
			{
				$str_age = JText::_('COM_KA_NAMES_AGE_03');
			}
		}

		$item->date_of_birth_interval_str = $_interval->y . ' ' . $str_age;

		if (!empty($item->desc))
		{
			$item->desc = str_replace("\n", "<br />", $item->desc);
		}

		$this->params = $params;
		$this->item = $item;
		$this->user = $user;

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

		if (count($errors = $this->get('Errors')) || is_null($items))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_name_wallpp === '' && $params->get('tab_name_wallpp') === '0') || $item->attribs->tab_name_wallpp === '0')
		{
			$id = $app->input->get('id', null, 'int');
			KAComponentHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $id . '&Itemid=' . $this->itemid, false));
		}

		// Build title string
		$item->title = KAContentHelper::formatItemTitle($item->name, $item->latin_name);

		foreach ($items as $_item)
		{
			if ($params->get('throttle_image_enable', 0) == 0)
			{
				$checking_path = JPath::clean(
					$params->get('media_actor_wallpapers_root') . DIRECTORY_SEPARATOR . $item->fs_alias
					. DIRECTORY_SEPARATOR . $item->id . DIRECTORY_SEPARATOR . 'wallpapers' . DIRECTORY_SEPARATOR . $_item->filename
				);

				if (!is_file($checking_path))
				{
					$_item->image = 'javascript:void(0);';
					$_item->th_image = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_wp.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_COMPONENT . '/assets/themes/component/' . $params->get('ka_theme') . '/images/no_wp.png',
						false
					);
					$_item->th_image_width = $dimension->width;
					$_item->th_image_height = $dimension->height;
				}
				else
				{
					$_item->fs_alias = rawurlencode($item->fs_alias);

					if (StringHelper::substr($params->get('media_actor_wallpapers_root_www'), 0, 1) == '/')
					{
						$_item->image = JURI::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1) . '/'
							. $_item->fs_alias . '/' . $item->id . '/wallpapers/' . $_item->filename;
						$_item->th_image = JURI::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1) . '/'
							. $_item->fs_alias . '/' . $item->id . '/wallpapers/thumb_' . $_item->filename;
					}
					else
					{
						$_item->image = $params->get('media_actor_wallpapers_root_www') . '/' . $_item->fs_alias . '/'
							. $item->id . '/wallpapers/' . $_item->filename;
						$_item->th_image = $params->get('media_actor_wallpapers_root_www') . '/' . $_item->fs_alias . '/'
							. $item->id . '/wallpapers/thumb_' . $_item->filename;
					}

					$dimension = KAContentHelper::getImageSize(
							$_item->image,
						true,
						(int) $params->get('size_x_wallpp'),
						$_item->dimension
					);
					$_item->th_image_width = $dimension->width;
					$_item->th_image_height = $dimension->height;
				}
			}
			else
			{
				$_item->image = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=name&content=image&type=1&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $_item->filename . '&format=raw&Itemid=' . $this->itemid .
					'&gender=' . $item->gender
				);
				$_item->th_image = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=name&content=image&type=1&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $_item->filename . '&format=raw&Itemid=' . $this->itemid .
					'&thumbnail=1&gender=' . $item->gender
				);
				$dimension = KAContentHelper::getImageSize(
					JUri::base() . $_item->image,
					true,
					(int) $params->get('size_x_wallpp'),
					$_item->dimension
				);
				$_item->th_image_width = $dimension->width;
				$_item->th_image_height = $dimension->height;
			}
		}

		$this->params = $params;
		$this->item = $item;
		$this->items = $items;
		$this->filters = $this->getDimensionList();
		$this->pagination = $pagination;

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid));
		$pathway->addItem(
			JText::_('COM_KA_MOVIE_TAB_WALLPP'),
			JRoute::_('index.php?option=com_kinoarhiv&view=name&page=wallpapers&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('wallpp');
	}

	protected function photo()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('NameData');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')) || is_null($items))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_name_photos === '' && $params->get('tab_name_photos') === '0') || $item->attribs->tab_name_photos === '0')
		{
			$id = $app->input->get('id', null, 'int');
			KAComponentHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $id . '&Itemid=' . $this->itemid, false));
		}

		// Build title string
		$item->title = KAContentHelper::formatItemTitle($item->name, $item->latin_name);

		$no_cover = ($item->gender == 0) ? 'no_name_cover_f' : 'no_name_cover_m';

		foreach ($items as $_item)
		{
			if ($params->get('throttle_image_enable', 0) == 0)
			{
				$checking_path = JPath::clean(
					$params->get('media_actor_photo_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR
					. $item->id . DIRECTORY_SEPARATOR . 'photo' . DIRECTORY_SEPARATOR . $_item->filename
				);

				if (!is_file($checking_path))
				{
					$_item->image = 'javascript:void(0);';
					$_item->th_image = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme')
						. '/images/' . $no_cover . '.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_COMPONENT . '/assets/themes/component/' . $params->get('ka_theme') . '/images/' . $no_cover . '.png',
						false
					);
					$_item->th_image_width = $dimension->width;
					$_item->th_image_height = $dimension->height;
				}
				else
				{
					$_item->fs_alias = rawurlencode($item->fs_alias);

					if (StringHelper::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/')
					{
						$_item->image = JURI::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1) . '/'
							. $_item->fs_alias . '/' . $item->id . '/photo/' . $_item->filename;
						$_item->th_image = JURI::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1) . '/'
							. $_item->fs_alias . '/' . $item->id . '/photo/thumb_' . $_item->filename;
					}
					else
					{
						$_item->image = $params->get('media_actor_photo_root_www') . '/' . $_item->fs_alias . '/' . $item->id . '/photo/' . $_item->filename;
						$_item->th_image = $params->get('media_actor_photo_root_www') . '/' . $_item->fs_alias . '/' . $item->id . '/photo/thumb_' . $_item->filename;
					}

					$dimension = KAContentHelper::getImageSize(
							$_item->image,
						true,
						(int) $params->get('size_x_photo'),
						$_item->dimension
					);
					$_item->th_image_width = $dimension->width;
					$_item->th_image_height = $dimension->height;
				}
			}
			else
			{
				$_item->image = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=name&content=image&type=3&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $_item->filename . '&format=raw&Itemid=' . $this->itemid .
					'&gender=' . $item->gender
				);
				$_item->th_image = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=name&content=image&type=3&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $_item->filename . '&format=raw&Itemid=' . $this->itemid .
					'&thumbnail=1&gender=' . $item->gender
				);
				$dimension = KAContentHelper::getImageSize(
					JUri::base() . $_item->image,
					true,
					(int) $params->get('size_x_photo'),
					$_item->dimension
				);
				$_item->th_image_width = $dimension->width;
				$_item->th_image_height = $dimension->height;
			}
		}

		$this->params = $params;
		$this->item = $item;
		$this->items = $items;
		$this->pagination = $pagination;

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid));
		$pathway->addItem(
			JText::_('COM_KA_NAMES_TAB_PHOTO'),
			JRoute::_('index.php?option=com_kinoarhiv&view=name&page=posters&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('photo');
	}

	protected function awards()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('NameData');
		$items = $this->get('Awards');

		if (count($errors = $this->get('Errors')) || is_null($items))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_name_awards === '' && $params->get('tab_name_awards') === '0') || $item->attribs->tab_name_awards === '0')
		{
			$id = $app->input->get('id', null, 'int');
			KAComponentHelper::doRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $id . '&Itemid=' . $this->itemid, false));
		}

		// Prepare the data
		// Build title string
		$item->title = KAContentHelper::formatItemTitle($item->name, $item->latin_name);

		$this->params = $params;
		$this->item = $item;
		$this->items = $items;

		$this->_prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem($this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid));
		$pathway->addItem(
			JText::_('COM_KA_NAMES_TAB_AWARDS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=name&page=awards&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

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

		foreach ($dimensions as $dimension)
		{
			$selected = ($dimension['width'] == $active) ? ' selected="selected"' : '';
			$list .= '<option value="' . $dimension['width'] . '"' . $selected . '>' . $dimension['title'] . '</option>';
		}

		$list .= '</select>';

		return array('dimensions.list' => $list);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 */
	protected function _prepareDocument()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$pathway = $app->getPathway();

		$title = ($menu && $menu->title) ? $menu->title : JText::_('COM_KA_PERSONS');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=names&Itemid=' . $this->itemid
		);

		$pathway->setPathway(array($path));
		$this->document->setTitle($this->item->title);

		if ($menu && $menu->params->get('menu-meta_description') != '')
		{
			$this->document->setDescription($menu->params->get('menu-meta_description'));
		}
		else
		{
			$this->document->setDescription($this->params->get('meta_description'));
		}

		if ($menu && $menu->params->get('menu-meta_keywords') != '')
		{
			$this->document->setMetadata('keywords', $menu->params->get('menu-meta_keywords'));
		}
		else
		{
			$this->document->setMetadata('keywords', $this->params->get('meta_keywords'));
		}

		if ($menu && $menu->params->get('robots') != '')
		{
			$this->document->setMetadata('robots', $menu->params->get('robots'));
		}
		else
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($this->params->get('generator') == 'none')
		{
			$this->document->setGenerator('');
		}
		elseif ($this->params->get('generator') == 'site')
		{
			$this->document->setGenerator($this->document->getGenerator());
		}
		else
		{
			$this->document->setGenerator($this->params->get('generator'));
		}
	}
}
