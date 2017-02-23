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
 * Premieres View class
 *
 * @since  3.0
 */
class KinoarhivViewPremieres extends JViewLegacy
{
	protected $items = null;

	protected $pagination = null;

	protected $user;

	protected $itemid;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		JLoader::register('KAContentHelper', JPath::clean(JPATH_COMPONENT . '/helpers/content.php'));

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$this->itemid = $app->input->get('Itemid', 0, 'int');
		$ka_theme = $params->get('ka_theme');
		$itemid = $this->itemid;
		$throttle_enable = $params->get('throttle_image_enable', 0);

		// Prepare the data
		foreach ($items as &$item)
		{
			$item->attribs = json_decode($item->attribs);

			// Replace country BB-code
			$item->text = preg_replace_callback('#\[country\s+ln=(.+?)\](.*?)\[/country\]#i', function ($matches) use ($ka_theme)
			{
				$html = JText::_($matches[1]);

				$cn = preg_replace('#\[cn=(.+?)\](.+?)\[/cn\]#', '<img src="media/com_kinoarhiv/images/icons/countries/$1.png" border="0" alt="$2" class="ui-icon-country" /> $2', $matches[2]);

				return $html . $cn;
			},
			$item->text
			);

			// Replace genres BB-code
			$item->text = preg_replace_callback('#\[genres\s+ln=(.+?)\](.*?)\[/genres\]#i', function ($matches)
			{
				return JText::_($matches[1]) . $matches[2];
			},
			$item->text
			);

			// Replace person BB-code
			$item->text = preg_replace_callback('#\[names\s+ln=(.+?)\](.*?)\[/names\]#i', function ($matches) use ($itemid)
			{
				$html = JText::_($matches[1]);

				$name = preg_replace('#\[name=(.+?)\](.+?)\[/name\]#', '<a href="' . JRoute::_('index.php?option=com_kinoarhiv&view=name&id=$1&Itemid=' . $itemid, false) . '" title="$2">$2</a>', $matches[2]);

				return $html . $name;
			},
			$item->text
			);

			if ($throttle_enable == 0)
			{
				$checking_path = JPath::clean($params->get('media_posters_root') . '/' . $item->fs_alias . '/' . $item->id . '/posters/' . $item->filename);

				if (!is_file($checking_path))
				{
					$item->poster = JUri::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_movie_cover.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_COMPONENT . '/assets/themes/component/' . $params->get('ka_theme') . '/images/no_movie_cover.png',
						false
					);
					$item->poster_width = $dimension->width;
					$item->poster_height = $dimension->height;
				}
				else
				{
					$item->fs_alias = rawurlencode($item->fs_alias);

					if (StringHelper::substr($params->get('media_posters_root_www'), 0, 1) == '/')
					{
						$item->poster = JUri::base() . StringHelper::substr($params->get('media_posters_root_www'), 1) . '/'
							. $item->fs_alias . '/' . $item->id . '/posters/thumb_' . $item->filename;
					}
					else
					{
						$item->poster = $params->get('media_posters_root_www') . '/' . $item->fs_alias . '/' . $item->id . '/posters/thumb_' . $item->filename;
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
					'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=2&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $item->filename . '&format=raw&Itemid=' . $itemid . '&thumbnail=1'
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

			$item->plot = JHtml::_('string.truncate', $item->plot, $params->get('limit_text'));

			if ($params->get('ratings_show_frontpage') == 1)
			{
				if (!empty($item->rate_sum_loc) && !empty($item->rate_loc))
				{
					$plural = $lang->getPluralSuffixes($item->rate_loc);
					$item->rate_loc_c = round($item->rate_sum_loc / $item->rate_loc, (int) $params->get('vote_summ_precision'));
					$item->rate_loc_label = JText::sprintf(
						'COM_KA_RATE_LOCAL_' . $plural[0],
						$item->rate_loc_c,
						(int) $params->get('vote_summ_num'),
						$item->rate_loc
					);
					$item->rate_loc_label_class = ' has-rating';
				}
				else
				{
					$item->rate_loc_c = 0;
					$item->rate_loc_label = '<br />' . JText::_('COM_KA_RATE_NO');
					$item->rate_loc_label_class = ' no-rating';
				}
			}

			$item->event = new stdClass;
			$item->params = new JObject;
			$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $item->id . '&Itemid=' . $this->itemid, false));

			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.premieres', &$item, &$params, 0));

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.premieres', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.premieres', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.premieres', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		$this->params = $params;
		$this->items = $items;
		$this->pagination = $pagination;
		$this->user = $user;

		$this->prepareDocument();

		parent::display($tpl);
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
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$pathway = $app->getPathway();

		$title = ($menu && $menu->title) ? $menu->title : JText::_('COM_KA_PREMIERES');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=premieres&Itemid=' . $this->itemid
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
