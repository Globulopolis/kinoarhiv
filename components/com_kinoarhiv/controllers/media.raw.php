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

JLoader::register('KAFilesystem', JPath::clean(JPATH_COMPONENT . '/libraries/filesystem.php'));

/**
 * Media Controller class
 *
 * @since  3.1
 */
class KinoarhivControllerMedia extends JControllerLegacy
{
	/**
	 * Get content from filesystem.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function view()
	{
		$element = $this->input->get('element', '', 'word');
		$content = $this->input->get('content', '', 'word');
		$id      = $this->input->get('id', 0, 'int');

		header_remove('X-Powered-By');

		if ($id == 0)
		{
			header('HTTP/1.0 404 Not Found', true, 404);
			jexit();
		}

		if (!empty($element) && method_exists($this, $element))
		{
			if (function_exists('apache_setenv'))
			{
				// Disable gzip HTTP compression so it would not alter the transfer rate
				apache_setenv('no-gzip', '1');
			}

			// Disable the script timeout if supported by the server
			if (false === strpos(ini_get('disable_functions'), 'set_time_limit'))
			{
				// Suppress the warnings (in case of the safe_mode)
				@set_time_limit(0);
			}

			$this->$element($content);
		}
		else
		{
			header('HTTP/1.0 404 Not Found', true, 404);
			jexit();
		}
	}

	/**
	 * Get movie content.
	 *
	 * @param   string  $content  Content type(image).
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function movie($content)
	{
		$params    = JComponentHelper::getParams('com_kinoarhiv');
		$id        = $this->input->get('id', 0, 'int');
		$fsAlias   = $this->input->get('fa', '', 'string');
		$filename  = $this->input->get('fn', '', 'string');
		$thumbnail = $this->input->get('thumbnail', 0, 'int');

		if ($content === 'image')
		{
			$type     = $this->input->get('type', 2, 'int');
			$filename = basename($filename);
			$filename = ($thumbnail == 1) ? 'thumb_' . $filename : $filename;
			$path     = $this->getImagePath('movie', $type, $fsAlias, $id, $filename);

			if (!is_file($path))
			{
				$path = JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_movie_cover.png';
			}

			try
			{
				KAFilesystem::getInstance()->sendFile(
					$path,
					$params->get('throttle_image_enable'),
					array('seconds' => $params->get('throttle_image_sec'), 'bytes' => $params->get('throttle_image_bytes'))
				);
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
		else
		{
			die();
		}
	}

	/**
	 * Get person content.
	 *
	 * @param   string  $content  Content type(image).
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function name($content)
	{
		$params    = JComponentHelper::getParams('com_kinoarhiv');
		$id        = $this->input->get('id', 0, 'int');
		$fsAlias   = $this->input->get('fa', '', 'string');
		$filename  = $this->input->get('fn', '', 'string');
		$thumbnail = $this->input->get('thumbnail', 0, 'int');

		if ($content === 'image')
		{
			$type     = $this->input->get('type', 3, 'int');
			$gender   = $this->input->get('gender', 0, 'int');
			$filename = basename($filename);
			$filename = ($thumbnail == 1) ? 'thumb_' . $filename : $filename;
			$path     = $this->getImagePath('name', $type, $fsAlias, $id, $filename);

			if (!is_file($path))
			{
				$noCover = ($gender == 0) ? 'no_name_cover_f' : 'no_name_cover_m';
				$path = JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/' . $noCover . '.png';
			}

			try
			{
				KAFilesystem::getInstance()->sendFile(
					$path,
					$params->get('throttle_image_enable'),
					array('seconds' => $params->get('throttle_image_sec'), 'bytes' => $params->get('throttle_image_bytes'))
				);
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
		else
		{
			die();
		}
	}

	/**
	 * Get trailer content.
	 *
	 * @param   string  $content  Content type(image, video, subtitles, chapters).
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   3.1
	 */
	protected function trailer($content)
	{
		$params   = JComponentHelper::getParams('com_kinoarhiv');
		$id       = $this->input->get('id', 0, 'int');
		$itemID   = $this->input->get('item_id', 0, 'int');
		$fsAlias  = $this->input->get('fa', '', 'string');
		$filename = $this->input->get('fn', '', 'string');
		$filename = basename($filename);

		/** @var KinoarhivModelMovie $model */
		$model = $this->getModel('movie');

		if (!$model->getTrailerAccessLevel($itemID))
		{
			header('HTTP/1.0 403 Forbidden');
			die();
		}

		if ($content === 'image')
		{
			$type = $this->input->get('type', 2, 'int');
			$path = $this->getImagePath('trailer', $type, $fsAlias, $id, $filename);

			if (!is_file($path))
			{
				$path = JPATH_ROOT . '/media/com_kinoarhiv/images/video_off.png';
			}

			try
			{
				KAFilesystem::getInstance()->sendFile(
					$path,
					$params->get('throttle_image_enable'),
					array('seconds' => $params->get('throttle_image_sec'), 'bytes' => $params->get('throttle_image_bytes'))
				);
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
		elseif ($content === 'video')
		{
			if (KAComponentHelper::checkToken('get') === false)
			{
				header('HTTP/1.0 403 Forbidden');
				die();
			}

			$path = $this->getVideoPath(urldecode($fsAlias), $id, $filename);

			if (!is_file($path))
			{
				header('HTTP/1.0 404 Not Found');
				die();
			}

			try
			{
				KAFilesystem::getInstance()->sendFile(
					$path,
					$params->get('throttle_video_enable'),
					array('seconds' => $params->get('throttle_video_sec'), 'bytes' => $params->get('throttle_video_bytes')),
					false,
					false
				);
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
		elseif ($content === 'subtitles' || $content === 'chapters')
		{
			$path = $this->getVideoPath(urldecode($fsAlias), $id, $filename);

			if (!is_file($path))
			{
				header('HTTP/1.0 404 Not Found');
				die();
			}

			try
			{
				KAFilesystem::getInstance()->sendFile(
					$path,
					$params->get('throttle_video_enable'),
					array('seconds' => $params->get('throttle_video_sec'), 'bytes' => $params->get('throttle_video_bytes'))
				);
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
		else
		{
			die();
		}
	}

	/**
	 * Get music album content.
	 *
	 * @param   string  $content  Content type.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function album($content)
	{
		$params    = JComponentHelper::getParams('com_kinoarhiv');
		$id        = $this->input->get('id', 0, 'int');
		$fsAlias   = $this->input->get('fa', '', 'string');
		$filename  = $this->input->get('fn', '', 'string');
		$thumbnail = $this->input->get('thumbnail', 0, 'int');

		if ($content === 'image')
		{
			$type     = $this->input->get('type', 2, 'int');
			$filename = basename($filename);
			$filename = ($thumbnail == 1) ? 'thumb_' . $filename : $filename;
			$path     = $this->getImagePath('album', $type, $fsAlias, $id, $filename);

			if (!is_file($path))
			{
				$path = JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_album_cover.png';
			}

			try
			{
				KAFilesystem::getInstance()->sendFile(
					$path,
					$params->get('throttle_image_enable'),
					array('seconds' => $params->get('throttle_image_sec'), 'bytes' => $params->get('throttle_image_bytes'))
				);
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
		else
		{
			die();
		}
	}

	/**
	 * Method to get the filesystem path for image content
	 *
	 * @param   string   $content   Item type. Movie or person or trailer screenshot or album art.
	 * @param   integer  $type      Content image type. 1 - wallpapers, 2 - posters, 3 - screenshots
	 * @param   string   $fsAlias   Filesystem alias(`fs_alias` column).
	 * @param   integer  $itemID    Item ID.
	 * @param   string   $filename  File name.
	 *
	 * @return  string
	 *
	 * @since    3.1
	 */
	private function getImagePath($content, $type, $fsAlias, $itemID, $filename)
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$path   = '';

		if ($content === 'movie')
		{
			$middle = '/' . rawurlencode($fsAlias) . '/' . $itemID . '/';

			// 1-wallpapers, 2-posters, 3-screenshots
			if ($type == 1)
			{
				$path = $params->get('media_wallpapers_root') . $middle . 'wallpapers/';
			}
			elseif ($type == 2)
			{
				$path = $params->get('media_posters_root') . $middle . 'posters/';
			}
			elseif ($type == 3)
			{
				$path = $params->get('media_scr_root') . $middle . 'screenshots/';
			}
		}
		elseif ($content === 'name')
		{
			$middle = '/' . rawurlencode($fsAlias) . '/' . $itemID . '/';

			// 1-wallpapers, 2-posters, 3-photo
			if ($type == 1)
			{
				$path = $params->get('media_actor_wallpapers_root') . $middle . 'wallpapers/';
			}
			elseif ($type == 2)
			{
				$path = $params->get('media_actor_posters_root') . $middle . 'posters/';
			}
			elseif ($type == 3)
			{
				$path = $params->get('media_actor_photo_root') . $middle . 'photo/';
			}
		}
		elseif ($content === 'album')
		{
			// 1-front, 2-back, 3-artist, 4-cd
			if ($type == 1 || $type == 2 || $type == 3 || $type == 4)
			{
				$meta = KAContentHelper::getAlbumMetadata($itemID);

				if (!empty($meta['covers_path']))
				{
					$path = JPath::clean($meta['covers_path'] . '/');
				}
				else
				{
					$path = $params->get('media_music_images_root') . '/' . rawurlencode($fsAlias) . '/' . $itemID . '/';
				}
			}
		}
		elseif ($content === 'trailer')
		{
			$path = $params->get('media_trailers_root') . '/' . rawurlencode($fsAlias) . '/' . $itemID . '/';
		}

		return JPath::clean($path . $filename);
	}

	/**
	 * Method to get the filesystem path for trailers content
	 *
	 * @param   string   $fsAlias   Filesystem alias(`fs_alias` column).
	 * @param   integer  $itemID    Item ID.
	 * @param   string   $filename  File name.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	private function getVideoPath($fsAlias, $itemID, $filename)
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$path = $params->get('media_trailers_root') . '/' . rawurlencode($fsAlias) . '/' . $itemID . '/';

		return JPath::clean($path . $filename);
	}
}
