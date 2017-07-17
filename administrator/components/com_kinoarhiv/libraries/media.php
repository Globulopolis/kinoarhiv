<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * Media processing library class
 *
 * @since  3.0
 */
class KAMedia
{
	protected static $instance;

	protected $params;

	/**
	 * Class constructor.
	 *
	 * @since  3.0
	 */
	public function __construct()
	{
		$this->params = JComponentHelper::getParams('com_kinoarhiv');
	}

	/**
	 * Returns a reference to the KAMedia object, only creating it if it doesn't already exist.
	 *
	 * @return  KAMedia
	 *
	 * @since  3.0
	 */
	public static function getInstance()
	{
		// Only create the object if it doesn't exist.
		if (empty(static::$instance))
		{
			static::$instance = new KAMedia;
		}

		return static::$instance;
	}

	/**
	 * Create screenshot from videofile
	 *
	 * @param   string  $folder    Path to the folder with file.
	 * @param   string  $filename  Videofile filename.
	 * @param   string  $time      Time.
	 *
	 * @return  mixed   Array with results or false otherwise
	 *
	 * @since  3.0
	 */
	public function createVideoScreenshot($folder, $filename, $time)
	{
		$app = JFactory::getApplication();
		$ffmpeg_path = JPath::clean($this->params->get('ffmpeg_path'));

		if (empty($ffmpeg_path))
		{
			$app->enqueueMessage(JText::_('COM_KA_MOVIES_GALLERY_ERROR_FILENOTFOUND') . ' ' . $ffmpeg_path, 'error');

			return false;
		}

		if (!KAComponentHelper::functionExists('shell_exec'))
		{
			$app->enqueueMessage('shell_exec() function not exists or safe mode or suhosin is On!', 'error');

			return false;
		}

		$check_lib = $this->checkLibrary($this->params->get('ffmpeg_path'));

		if ($check_lib !== true)
		{
			$app->enqueueMessage($check_lib[1], 'error');

			return false;
		}

		$finfo           = pathinfo($folder . $filename);
		$result_filename = $finfo['filename'] . '.png';
		$video_info      = $this->getVideoInfo($folder . $filename);

		if ($video_info === false)
		{
			$app->enqueueMessage(JText::_('ERROR'), 'error');

			return false;
		}

		/*
		 * To avoid some errors with ffmpeg(library doesn't understand folders or files with % in teir names)
		 * get temp Joomla folder, store screenshot to this folder and when copy to dest path.
		 */
		jimport('joomla.filesystem.file');

		$config     = JFactory::getConfig();
		$tmp_folder = JPath::clean($config->get('tmp_path') . '/');
		$video_info = json_decode($video_info);
		$scr_w      = (int) $this->params->get('player_width');
		$scr_h      = ($video_info->streams[0]->height * $scr_w) / $video_info->streams[0]->width;

		@set_time_limit(0);
		$cmd = $ffmpeg_path . ' -hide_banner -nostats -i ' . $folder . $filename . ' -ss ' . $time
			. ' -f image2 -vframes 1 -s ' . floor($scr_w) . 'x' . floor($scr_h) . ' ' . $tmp_folder . $result_filename . ' -y';

		if (IS_WIN)
		{
			$cmd .= ' 2>&1';
		}
		else
		{
			$cmd .= ' 2>%1';
		}

		$output = shell_exec($cmd);

		// Copy screenshot from tmp to dest folder. We need to copy/delete instead of move to avoid bugs on Windows platform.
		JFile::copy($tmp_folder . $result_filename, $folder . $result_filename);
		JFile::delete($tmp_folder . $result_filename);

		return array(
			'filename' => $result_filename,
			'stdout'   => '<pre>' . $cmd . '<br />' . $output . '</pre>'
		);
	}

	/**
	 * Get metadata information from video file
	 *
	 * @param   string  $path    Path to a file.
	 * @param   string  $stream  Stream number. v:0 - first video stream from file.
	 * @param   string  $format  Output format. See http://ffmpeg.org/ffprobe.html#Writers
	 *
	 * @return  string  JSON string.
	 *
	 * @since  3.0
	 */
	public function getVideoInfo($path, $stream = 'v:0', $format = 'json')
	{
		if (!KAComponentHelper::functionExists('shell_exec'))
		{
			die('shell_exec() function not exists or safe mode or suhosin is on!');
		}

		if (!is_file($path))
		{
			return false;
		}

		$check_lib = $this->checkLibrary($this->params->get('ffprobe_path'));

		if ($check_lib !== true)
		{
			return false;
		}

		$cmd = JPath::clean($this->params->get('ffprobe_path')) . ' -v quiet -print_format ' . (string) $format .
			' -show_streams -select_streams ' . $stream . ' "' . $path . '"';

		if (IS_WIN)
		{
			$cmd .= ' 2>&1';
		}
		else
		{
			$cmd .= ' 2>%1';
		}

		$output = shell_exec($cmd);

		return $output;
	}

	/**
	 * Get video file duration
	 *
	 * @param   string   $path    Path to a file.
	 * @param   boolean  $format  Output format. If set to true, format result to 00:00:00:000, as is otherwise.
	 *
	 * @return  mixed   Array with results if error, string otherwise
	 *
	 * @since  3.0
	 */
	public function getVideoDuration($path, $format = false)
	{
		if (!KAComponentHelper::functionExists('shell_exec'))
		{
			die('shell_exec() function not exists or safe mode or suhosin is on!');
		}

		if (!is_file($path))
		{
			return array(false, 'Video file not found at path ' . $path);
		}

		$check_lib = $this->checkLibrary($this->params->get('ffprobe_path'));

		if ($check_lib !== true)
		{
			return $check_lib;
		}

		$cmd = $this->params->get('ffprobe_path') . ' -loglevel error -show_format -show_streams ' . $path . ' -print_format json';

		if (IS_WIN)
		{
			$cmd .= ' 2>&1';
		}
		else
		{
			$cmd .= ' 2>%1';
		}

		$output = shell_exec($cmd);
		$object = json_decode($output);

		if ($format)
		{
			$seconds = round($object->format->duration);
			$duration = sprintf('%02d:%02d:%02d', ($seconds / 3600), ($seconds / 60 % 60), $seconds % 60);
		}
		else
		{
			$duration = $object->format->duration;
		}

		return $duration;
	}

	/**
	 * Check if media library(ffmpeg, ffprobe) exists or available.
	 *
	 * @param   string  $path  Path to a file.
	 *
	 * @return  mixed   Array with results if error, true otherwise
	 *
	 * @since  3.0
	 */
	public function checkLibrary($path)
	{
		$path = JPath::clean($path);

		if (!file_exists($path))
		{
			$error = JText::sprintf('COM_KA_MEDIAMANAGER_FFMPEG_NOTFOUND', $path);
			JLog::add($error . ' in ' . __METHOD__, JLog::CRITICAL);

			return array(false, $error);
		}

		return true;
	}

	/**
	 * Validate subtitle file. Subrip (.srt), WebVTT (.vtt), Substation Alpha (.ass), Youtube Subtitles (.sbv)
	 * JSON (TED.com) Subtitles (.json)
	 *
	 * @param   string  $path      Path to a file.
	 * @param   string  $filename  Filename.
	 *
	 * @return  boolean  True on success
	 *
	 * @throws  Exception
	 *
	 * @since  3.1
	 */
	public function validateSubtitles($path, $filename)
	{
		// jimport('components.com_kinoarhiv.libraries.vendor.captioning.src.Captioning.Format', JPATH_ROOT);
	}

	/**
	 * Normalize some strings in WEBVTT file and save
	 *
	 * @param   string   $path          Path to a file.
	 * @param   string   $filename      Filename.
	 * @param   boolean  $replace       Overwrite existing file or not.
	 * @param   string   $new_filename  New filename.
	 *
	 * @return  boolean  True on success
	 *
	 * @throws  Exception
	 *
	 * @since  3.1
	 */
	public function normalizeVTT($path, $filename, $replace=true, $new_filename='')
	{
		$filepath = JPath::clean($path . $filename);

		if (!is_file($filepath))
		{
			throw new Exception('File not found or inaccessible!');
		}
		else
		{
			$content = file_get_contents($filepath);

			if (!mb_detect_encoding($content, 'UTF-8', true) || strpos($content, "\xEF\xBB\xBF") !== false)
			{
				throw new Exception('Wrong file encoding! UTF-8 encoding required! See https://w3c.github.io/webvtt/#file-structure');
			}

			// Check file header
			if (strstr($content, 'WEBVTT') === false)
			{
				throw new Exception('\'WEBVTT\' reqired at file start! See https://w3c.github.io/webvtt/#webvtt-file-body');
			}

			$content = preg_replace('#\d+[\s+]+(\d{2}):(\d{2}):(\d{2}),(\d{3})#sm', '$1:$2:$3.$4', $content);

			if ($replace)
			{
				file_put_contents($filepath, $content);
			}
			else
			{
				file_put_contents($path . $new_filename, $content);
			}
		}

		return true;
	}
}
