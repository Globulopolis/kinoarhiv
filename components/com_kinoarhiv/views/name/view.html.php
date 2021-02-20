<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
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

	protected $pagination;

	protected $params;

	protected $user;

	protected $page;

	protected $itemid;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		$app          = JFactory::getApplication();
		$this->user   = JFactory::getUser();
		$this->page   = $app->input->get('page', '', 'cmd');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		if (method_exists($this, $this->page))
		{
			$this->{$this->page}();
		}
		else
		{
			$this->info($tpl);
		}
	}

	/**
	 * Method to get and show person info.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	protected function info($tpl)
	{
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item   = $this->get('Data');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		// Prepare the data
		// Build title string
		$item->title = KAContentHelper::formatItemTitle($item->name, $item->latin_name);

		$item->moviesItemid = KAContentHelper::getItemid('movies');
		$item->albumsItemid = KAContentHelper::getItemid('albums');

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

		$item->photo = KAContentHelper::getPersonPhoto($item, $params);

		$localeOffset      = JFactory::getConfig()->get('offset');
		$dateOfBirthFirst  = new DateTime($item->date_of_birth_raw . ' ' . date('H:i:s'), new DateTimeZone($localeOffset));
		$dateOfBirthSecond = new DateTime('now', new DateTimeZone($localeOffset));
		$_interval         = $dateOfBirthFirst->diff($dateOfBirthSecond);
		$interval          = ($_interval->y > 100) ? substr($_interval->y, -2) : $_interval->y;
		$ageString         = '';

		if ($interval >= 5 && $interval <= 14)
		{
			$ageString = JText::_('COM_KA_NAMES_AGE_01');
		}
		else
		{
			$interval = substr($_interval->y, -1);

			if ($interval == 0 || ($interval >= 5 && $interval <= 9))
			{
				$ageString = JText::_('COM_KA_NAMES_AGE_01');
			}

			if ($interval == 1)
			{
				$ageString = JText::_('COM_KA_NAMES_AGE_02');
			}

			if ($interval >= 2 && $interval <= 4)
			{
				$ageString = JText::_('COM_KA_NAMES_AGE_03');
			}
		}

		$item->date_of_birth_interval_str = $_interval->y . ' ' . $ageString;

		if (!empty($item->desc))
		{
			$item->desc = str_replace("\n", "<br />", $item->desc);
		}

		$this->params = $params;
		$this->item   = $item;

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title, JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display($tpl);
	}

	protected function wallpapers()
	{
		$app        = JFactory::getApplication();
		$params     = JComponentHelper::getParams('com_kinoarhiv');
		$item       = $this->get('NameData');
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_name_wallpp === '' && $params->get('tab_name_wallpp') === '0') || $item->attribs->tab_name_wallpp === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		// Build title string
		$item->title = KAContentHelper::formatItemTitle($item->name, $item->latin_name);

		foreach ($items as $_item)
		{
			$checkingPath = JPath::clean(
				$params->get('media_actor_wallpapers_root') . '/' . $item->fs_alias . '/' . $item->id . '/wallpapers/' . $_item->filename
			);

			if ($params->get('throttle_image_enable', 0) == 0)
			{
				$item->fs_alias = rawurlencode($item->fs_alias);

				if (!is_file($checkingPath))
				{
					$_item->image = 'javascript:void(0);';
					$_item->th_image = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_wp.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_wp.png',
						false
					);
					$_item->th_image_width = $dimension['width'];
					$_item->th_image_height = $dimension['height'];
				}
				else
				{
					if (StringHelper::substr($params->get('media_actor_wallpapers_root_www'), 0, 1) == '/')
					{
						$_item->image = JUri::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1) . '/'
							. $item->fs_alias . '/' . $item->id . '/wallpapers/' . $_item->filename;
						$_item->th_image = JUri::base() . StringHelper::substr($params->get('media_actor_photo_root_www'), 1) . '/'
							. $item->fs_alias . '/' . $item->id . '/wallpapers/thumb_' . $_item->filename;
					}
					else
					{
						$_item->image = $params->get('media_actor_wallpapers_root_www') . '/' . $item->fs_alias . '/'
							. $item->id . '/wallpapers/' . $_item->filename;
						$_item->th_image = $params->get('media_actor_wallpapers_root_www') . '/' . $item->fs_alias . '/'
							. $item->id . '/wallpapers/thumb_' . $_item->filename;
					}

					$dimension = KAContentHelper::getImageSize(
						$checkingPath,
						true,
						(int) $params->get('size_x_wallpp'),
						$_item->dimension
					);
					$_item->th_image_width = $dimension['width'];
					$_item->th_image_height = $dimension['height'];
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
					$checkingPath,
					true,
					(int) $params->get('size_x_wallpp'),
					$_item->dimension
				);
				$_item->th_image_width = $dimension['width'];
				$_item->th_image_height = $dimension['height'];
			}
		}

		$this->params     = $params;
		$this->item       = $item;
		$this->items      = $items;
		$this->filters    = $this->getDimensionList();
		$this->pagination = $pagination;

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_MOVIE_TAB_WALLPAPERS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=name&page=wallpapers&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('wallpp');
	}

	protected function photos()
	{
		$app        = JFactory::getApplication();
		$params     = JComponentHelper::getParams('com_kinoarhiv');
		$item       = $this->get('NameData');
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_name_photos === '' && $params->get('tab_name_photos') === '0') || $item->attribs->tab_name_photos === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		// Build title string
		$item->title = KAContentHelper::formatItemTitle($item->name, $item->latin_name);

		foreach ($items as $key => $value)
		{
			$items[$key]->photo = KAContentHelper::getPersonPhoto(
				(object) array(
					'id'        => $value->name_id,
					'fs_alias'  => $value->fs_alias,
					'filename'  => $value->filename,
					'gender'    => $value->gender,
					'dimension' => $value->dimension
				),
				$params
			);
		}

		$this->params     = $params;
		$this->item       = $item;
		$this->items      = $items;
		$this->pagination = $pagination;

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_NAMES_TAB_PHOTOS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=name&page=posters&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('photo');
	}

	protected function awards()
	{
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item   = $this->get('NameData');
		$items  = $this->get('Awards');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_name_awards === '' && $params->get('tab_name_awards') === '0') || $item->attribs->tab_name_awards === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		// Prepare the data
		// Build title string
		$item->title = KAContentHelper::formatItemTitle($item->name, $item->latin_name);

		$this->params = $params;
		$this->item   = $item;
		$this->items  = $items;

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=name&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_NAMES_TAB_AWARDS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=name&page=awards&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('awards');
	}

	protected function getDimensionList()
	{
		$app        = JFactory::getApplication();
		$active     = $app->input->get('dim_filter', '0', 'string');
		$dimensions = $this->get('DimensionFilters');
		array_push($dimensions, array('width' => '0', 'title' => JText::_('COM_KA_FILTERS_DIMENSION_NOSORT')));

		// Build select
		$list = '<label for="dim_filter">' . JText::_('COM_KA_FILTERS_DIMENSION') . '</label>
		<select name="dim_filter" id="dim_filter" class="inputbox" onchange="this.form.submit()" autocomplete="off">';

		foreach ($dimensions as $dimension)
		{
			$selected = ($dimension['width'] == $active) ? 'selected="selected"' : '';
			$list .= '<option value="' . $dimension['width'] . '" ' . $selected . '>' . $dimension['title'] . '</option>';
		}

		$list .= '</select>';

		return array('dimensions.list' => $list);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	protected function prepareDocument()
	{
		$app     = JFactory::getApplication();
		$menus   = $app->getMenu();
		$menu    = $menus->getActive();
		$pathway = $app->getPathway();

		$title = ($menu && $menu->title) ? $menu->title : JText::_('COM_KA_PERSONS');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=names&Itemid=' . $this->itemid
		);

		$pathway->setPathway(array($path));
		$titleAdd = empty($this->page) ? '' : ' - ' . JText::_('COM_KA_NAMES_TAB_' . StringHelper::ucwords($this->page));
		$this->document->setTitle($this->item->title . $titleAdd);

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
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
