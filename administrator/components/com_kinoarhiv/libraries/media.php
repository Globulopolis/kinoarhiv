<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
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
	 */
	public function __construct()
	{
		$this->params = JComponentHelper::getParams('com_kinoarhiv');
	}

	/**
	 * Returns a reference to the KAMedia object, only creating it if it doesn't already exist.
	 *
	 * @return  KAMedia
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
	 * @param   array  $data  An array with the data. folder - path to the folder, screenshot - filename of the
	 *                        screenshot(if exists), filename - videofile.
	 *
	 * @return  array  Array with results
	 */
	public function createScreenshot($data)
	{
		if (!empty($data['screenshot']) && file_exists($data['folder'] . $data['screenshot']))
		{
			@unlink($data['folder'] . $data['screenshot']);
		}

		$ffmpeg_path = JPath::clean($this->params->get('ffmpeg_path'));

		if ($ffmpeg_path !== '')
		{
			if (!KAComponentHelper::functionExists('shell_exec'))
			{
				die('shell_exec() function not exists or safe mode or suhosin is on!');
			}

			$check_lib = $this->checkLibrary($this->params->get('ffmpeg_path'));

			if ($check_lib !== true)
			{
				return false;
			}

			$result_filename = $data['filename'] . '.png';
			$video_info = $this->getVideoInfo($data['folder'] . $data['filename']);

			if ($video_info === false)
			{
				return false;
			}

			$video_info = json_decode($video_info);
			$scr_w = (int) $this->params->get('player_width');
			$scr_h = ($video_info->streams[0]->height * $scr_w) / $video_info->streams[0]->width;

			@set_time_limit(0);
			$cmd = $ffmpeg_path . ' -hide_banner -nostats -i ' . $data['folder'] . $data['filename'] . ' -ss ' . $data['time'] .
				' -f image2 -vframes 1 -s ' . floor($scr_w) . 'x' . floor($scr_h) . ' ' . $data['folder'] . $result_filename . ' -y';

			if (IS_WIN)
			{
				$cmd .= ' 2>&1';
			}
			else
			{
				$cmd .= ' 2>%1';
			}

			$output = shell_exec($cmd);

			return array($result_filename, '<pre>' . $cmd . '<br />' . $output . '</pre>');
		}
		else
		{
			die(JText::_('COM_KA_MOVIES_GALLERY_ERROR_FILENOTFOUND'));
		}
	}

	/**
	 * Get MIME-type of the file
	 *
	 * @param   string  $path  Path to a file.
	 *
	 * @return  string
	 */
	public function detectMime($path)
	{
		$mime = 'text/plain';

		if (!empty($path) && is_file($path))
		{
			if (KAComponentHelper::functionExists('finfo_open'))
			{
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_file($finfo, $path);
				finfo_close($finfo);
			}
			elseif (KAComponentHelper::functionExists('mime_content_type'))
			{
				$mime = mime_content_type($path);
			}
		}

		return $mime;
	}

	/**
	 * Get metadata information from video file
	 *
	 * @param   string  $path    Path to a file.
	 * @param   string  $stream  Stream number. v:0 - first video stream from file.
	 * @param   string  $format  Output format. See http://ffmpeg.org/ffprobe.html#Writers
	 *
	 * @return  mixed   Array with results if error, string otherwise
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

		$cmd = $this->params->get('ffprobe_path') . ' -v quiet -print_format ' . (string) $format .
			' -show_streams -select_streams ' . $stream . ' ' . $path;

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
				throw new Exception('Wrong file encoding! UTF-8 without BOM required!');
			}

			// Check file header
			if (strstr($content, 'WEBVTT') === false)
			{
				throw new Exception('\'WEBVTT\' reqired at file start!');
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
