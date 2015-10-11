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

use Joomla\Registry\Registry;
use Joomla\String\String;

/**
 * Names View class
 *
 * @since  3.0
 */
class KinoarhivViewNames extends JViewLegacy
{
	protected $items = null;

	protected $pagination = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		if ($app->input->get('task', '', 'cmd') == 'search' && KAComponentHelper::checkToken() === true)
		{
			$this->activeFilters = $this->get('FiltersData');
		}
		else
		{
			$this->activeFilters = new Registry;
		}

		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		JLoader::register('KAContentHelper', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'content.php');

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$this->itemid = $app->input->get('Itemid', 0, 'int');
		$throttle_enable = $params->get('throttle_image_enable', 0);

		// Prepare the data
		foreach ($items as $key => $item)
		{
			$item->attribs = json_decode($item->attribs);

			// Compose a date string
			$date_range = '';

			if ($item->date_of_birth != '0000')
			{
				$date_range .= ' (' . $item->date_of_birth;

				if ($item->date_of_death != '0000')
				{
					$date_range .= ' - ' . $item->date_of_death;
				}

				$date_range .= ')';
			}

			$items[$key]->date_range = $date_range;

			// Compose a title
			if (!empty($item->name) && !empty($item->latin_name))
			{
				$items[$key]->title = $item->name . ' / ' . $item->latin_name;
			}
			elseif (!empty($item->name))
			{
				$items[$key]->title = $item->name;
			}
			else
			{
				$items[$key]->title = $item->latin_name;
			}

			if ($throttle_enable == 0)
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

					if (String::substr($params->get('media_actor_photo_root_www'), 0, 1) == '/')
					{
						$item->poster = JURI::base() . String::substr($params->get('media_actor_photo_root_www'), 1) . '/' . $item->fs_alias . '/' . $item->id . '/photo/thumb_' . $item->filename;
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
		}

		$this->params = $params;
		$this->items['names'] = $items;
		$this->pagination = $pagination;
		$this->user = $user;
		$this->lang = $lang;

		$this->_prepareDocument();

		parent::display($tpl);
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
		$this->document->setTitle($title);

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
