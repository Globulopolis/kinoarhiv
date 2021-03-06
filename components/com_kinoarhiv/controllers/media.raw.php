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

/**
 * Media Controller class
 *
 * @since  3.0
 */
class KinoarhivControllerMedia extends JControllerLegacy
{
	public function view()
	{
		$app = JFactory::getApplication();
		$element = $this->input->get('element', '', 'word');
		$content = $this->input->get('content', '', 'word');

		header_remove('X-Powered-By');

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
			$app->setHeader('HTTP/1.0', '404 Not Found');
			$app->sendHeaders();
			exit(0);
		}
	}

	private function movie($content)
	{
		JLoader::register('KAFilesystem', JPath::clean(JPATH_COMPONENT . '/libraries/filesystem.php'));

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = $this->input->get('id', 0, 'int');
		$fs_alias = $this->input->get('fa', '', 'string');
		$filename = $this->input->get('fn', '', 'string');
		$thumbnail = $this->input->get('thumbnail', 0, 'int');

		if ($id == 0)
		{
			exit(0);
		}

		if ($content === 'image')
		{
			$type = $this->input->get('type', 2, 'int');
			$filename = ($thumbnail == 1) ? 'thumb_' . $filename : $filename;
			$path = $this->getImagePath('movie', $type, $fs_alias, $id, $filename);

			if (!file_exists($path) && !is_file($path))
			{
				$path = JPATH_COMPONENT . '/assets/themes/component/' . $params->get('ka_theme') . '/images/no_movie_cover.png';
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
			exit(0);
		}
	}

	private function name($content)
	{
		JLoader::register('KAFilesystem', JPath::clean(JPATH_COMPONENT . '/libraries/filesystem.php'));

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = $this->input->get('id', 0, 'int');
		$fs_alias = $this->input->get('fa', '', 'string');
		$filename = $this->input->get('fn', '', 'string');
		$thumbnail = $this->input->get('thumbnail', 0, 'int');

		if ($id == 0)
		{
			exit(0);
		}

		if ($content === 'image')
		{
			$type = $this->input->get('type', 3, 'int');
			$gender = $this->input->get('gender', 0, 'int');
			$filename = ($thumbnail == 1) ? 'thumb_' . $filename : $filename;
			$path = $this->getImagePath('name', $type, $fs_alias, $id, $filename);

			if (!file_exists($path) && !is_file($path))
			{
				$no_cover = ($gender == 0) ? 'no_name_cover_f' : 'no_name_cover_m';
				$path = JPATH_COMPONENT . '/assets/themes/component/' . $params->get('ka_theme') . '/images/' . $no_cover . '.png';
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
			exit(0);
		}
	}

	private function trailer($content)
	{
		JLoader::register('KAFilesystem', JPath::clean(JPATH_COMPONENT . '/libraries/filesystem.php'));

		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = $this->input->get('id', 0, 'int');
		$item_id = $this->input->get('item_id', 0, 'int');
		$fs_alias = $this->input->get('fa', '', 'string');
		$filename = $this->input->get('fn', '', 'string');

		if ($id == 0)
		{
			exit(0);
		}

		$model = $this->getModel('movie');

		if (!$model->getTrailerAccessLevel($item_id))
		{
			$app->setHeader('HTTP/1.0', '403 Forbidden');
			$app->sendHeaders();
			exit(0);
		}

		if ($content === 'image')
		{
			$type = $this->input->get('type', 2, 'int');
			$path = $this->getImagePath('trailer', $type, $fs_alias, $id, $filename);

			if (!file_exists($path) && !is_file($path))
			{
				$path = JPATH_COMPONENT . '/assets/themes/component/' . $params->get('ka_theme') . '/images/video_off.png';
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
				$app->setHeader('HTTP/1.0', '403 Forbidden');
				$app->sendHeaders();
				exit(0);
			}

			$path = $this->getVideoPath(urldecode($fs_alias), $id, $filename);

			if (!file_exists($path) && !is_file($path))
			{
				$app->setHeader('HTTP/1.0', '404 Not Found');
				$app->sendHeaders();
				exit(0);
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
			$path = $this->getVideoPath(urldecode($fs_alias), $id, $filename);

			if (!file_exists($path) && !is_file($path))
			{
				$app->setHeader('HTTP/1.0', '404 Not Found');
				$app->sendHeaders();
				exit(0);
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
			exit(0);
		}
	}

	/**
	 * Method to get the filesystem path for image content
	 *
	 * @param   string   $content   Item type. Movie or person or trailer screenshot or album art.
	 * @param   integer  $type      Content image type. 1 - wallpapers, 2 - posters, 3 - screenshots
	 * @param   string   $fs_alias  Filesystem alias(`fs_alias` column).
	 * @param   integer  $item_id   Item ID.
	 * @param   string   $filename  File name.
	 *
	 * @return  string
	 *
	 * @since    3.0
	 */
	private function getImagePath($content, $type, $fs_alias, $item_id, $filename)
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$path = '';

		if ($content === 'movie')
		{
			$middle = '/' . rawurlencode($fs_alias) . '/' . $item_id . '/';

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
			$middle = '/' . rawurlencode($fs_alias) . '/' . $item_id . '/';

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
		elseif ($content === 'trailer')
		{
			$path = $params->get('media_trailers_root') . '/' . rawurlencode($fs_alias) . '/' . $item_id . '/';
		}

		return JPath::clean($path . $filename);
	}

	/**
	 * Method to get the filesystem path for image content
	 *
	 * @param   string   $fs_alias  Filesystem alias(`fs_alias` column).
	 * @param   integer  $item_id   Item ID.
	 * @param   string   $filename  File name.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	private function getVideoPath($fs_alias, $item_id, $filename)
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$path = $params->get('media_trailers_root') . '/' . rawurlencode($fs_alias) . '/' . $item_id . '/';

		return JPath::clean($path . $filename);
	}
}
