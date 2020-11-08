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
 * Albums feed View class
 *
 * @since  3.0
 */
class KinoarhivViewAlbums extends JViewLegacy
{
	protected $items = null;

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
		$items = $this->get('Items');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors));

			return false;
		}

		$app             = JFactory::getApplication();
		$document        = JFactory::getDocument();
		$params          = JComponentHelper::getParams('com_kinoarhiv');
		$feedEmail       = $app->get('feed_email', 'author');
		$siteEmail       = $app->get('mailfrom');
		$this->itemid    = $app->input->get('Itemid', 0, 'int');
		$throttle_enable = $params->get('throttle_image_enable', 0);

		// Used in preg_replace_callback
		$itemid = $this->itemid;

		$document->setTitle(JText::_('COM_KA_MUSIC_ALBUMS'));
		$document->setDescription($params->get('meta_description'));
		$document->link = JRoute::_('index.php?option=com_kinoarhiv&view=albums');

		if ($params->get('generator') == 'none')
		{
			$document->setGenerator('');
		}
		elseif ($params->get('generator') == 'site')
		{
			$document->setGenerator($document->getGenerator());
		}
		else
		{
			$document->setGenerator($params->get('generator'));
		}

		$app->input->set('limit', $app->get('feed_limit'));

		// Prepare the data
		foreach ($items as $row)
		{
			$title   = $this->escape(KAContentHelper::formatItemTitle($row->title, '', $row->year));
			$title   = html_entity_decode($title, ENT_COMPAT, 'UTF-8');
			$link    = JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $row->id . '&Itemid=' . $this->itemid);
			$attribs = json_decode($row->attribs);

			$item         = new JFeedItem;
			$item->title  = $title;
			$item->link   = $link;
			$item->author = ($attribs->show_author === '' && !empty($row->username)) ? $row->username : '';

			if ($feedEmail == 'site')
			{
				$item->authorEmail = $siteEmail;
			}
			elseif ($feedEmail === 'author')
			{
				$item->authorEmail = $row->author_email;
			}

			if ($throttle_enable == 0)
			{
				$checking_path = JPath::clean(
					$params->get('media_posters_root') . '/' . $row->fs_alias . '/' . $row->id . '/posters/' . $row->filename
				);

				if (!is_file($checking_path))
				{
					$row->poster = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_movie_cover.png';
				}
				else
				{
					$row->fs_alias = rawurlencode($row->fs_alias);

					if (StringHelper::substr($params->get('media_posters_root_www'), 0, 1) == '/')
					{
						$row->poster = JUri::base() . StringHelper::substr($params->get('media_posters_root_www'), 1) . '/'
							. $row->fs_alias . '/' . $row->id . '/posters/thumb_' . $row->filename;
					}
					else
					{
						$row->poster = $params->get('media_posters_root_www') . '/' . $row->fs_alias . '/' . $row->id . '/posters/thumb_' . $row->filename;
					}
				}
			}
			else
			{
				$row->poster = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=album&content=image&type=2&id=' . $row->id .
					'&fa=' . urlencode($row->fs_alias) . '&fn=' . $row->filename . '&format=raw&Itemid=' . $itemid . '&thumbnail=1'
				);
			}

			$item->description = '<div class="feed-description">
				<div class="poster"><img src="' . $row->poster . '" border="0" /></div>
			</div>';

			$document->addItem($item);
		}
	}
}
