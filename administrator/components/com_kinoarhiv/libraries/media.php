<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KAMedia {
	protected static $instance;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->params = JComponentHelper::getParams('com_kinoarhiv');
	}

	/**
	 * Returns a reference to the KAMedia object, only creating it if it doesn't already exist.
	 *
	 * @return  KAMedia
	 */
	public static function getInstance() {
		// Only create the object if it doesn't exist.
		if (empty(static::$instance)) {
			static::$instance = new KAMedia;
		}

		return static::$instance;
	}

	/**
	 * Create screenshot from videofile
	 *
	 * @param   array  $data  An array with the data. folder - path to the folder, screenshot - filename of the screenshot(if exists), filename - videofile.
	 *
	 * @return  array  Array with results
	 */
	public function createScreenshot($data) {
		if (!empty($data['screenshot']) && file_exists($data['folder'].$data['screenshot'])) {
			@unlink($data['folder'].$data['screenshot']);
		}

		$ffmpeg_path = JPath::clean($this->params->get('ffmpeg_path'));
		if ($ffmpeg_path != '') {
			if (!function_exists('shell_exec')) {
				die('Function is not exists or safe mode is on!');
			}

			$check_lib = $this->checkLibrary($this->params->get('ffmpeg_path'));
			if ($check_lib !== true) {
				return $check_lib;
			}

			$result_filename = pathinfo($data['filename'], PATHINFO_FILENAME).'.png';
			$video_info = $this->getVideoInfo($data['folder'].$data['filename']);

			if ($video_info[0] === false) {
				return array(false, $video_info[1]);
			}

			$video_info = json_decode($video_info);
			$scr_w = (int)$this->params->get('player_width');
			$scr_h = ($video_info->streams[0]->height * $scr_w) / $video_info->streams[0]->width;

			@set_time_limit(0);

			if (IS_WIN) {
				$output = shell_exec(escapeshellcmd($ffmpeg_path).' -hide_banner -nostats -i '.escapeshellcmd($data['folder'].$data['filename']).' -ss '.$data['time'].' -f image2 -vframes 1 -s '.floor($scr_w).'x'.floor($scr_h).' '.$data['folder'].$result_filename." 2>&1");
			} else {
				$output = shell_exec(escapeshellcmd($ffmpeg_path).' -hide_banner -nostats -i '.escapeshellcmd($data['folder'].$data['filename']).' -ss '.$data['time'].' -f image2 -vframes 1 -s '.floor($scr_w).'x'.floor($scr_h).' '.$data['folder'].$result_filename." 2>%1");
			}

			return array($result_filename, '<pre>'.$output.'</pre>');
		} else {
			die(JText::_('COM_KA_MOVIES_GALLERY_ERROR_FILENOTFOUND'));
		}
	}

	/**
	 * Create screenshot from videofile
	 *
	 * @param   string  $path  Path to a file.
	 *
	 * @return  string
	 */
	public function detectMime($path) {
		if (!empty($path) && file_exists($path)) {
			if (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_file($finfo, $path);
				finfo_close($finfo);
			} elseif (function_exists('mime_content_type')) {
				$mime = mime_content_type($path);
			}
		} else {
			$mime = 'text/plain';
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
	public function getVideoInfo($path, $stream='v:0', $format='json') {
		if (!function_exists('shell_exec')) {
			die('Function is not exists or safe mode is on!');
		}

		$check_lib = $this->checkLibrary($this->params->get('ffprobe_path'));
		if ($check_lib !== true) {
			return $check_lib;
		}

		if (IS_WIN) {
			$output = shell_exec(escapeshellcmd($this->params->get('ffprobe_path')).' -v quiet -print_format '.(string)$format.' -show_streams -select_streams '.$stream.' '.escapeshellcmd($path).' 2>&1');
		} else {
			$output = shell_exec(escapeshellcmd($this->params->get('ffprobe_path')).' -v quiet -print_format '.(string)$format.' -show_streams -select_streams '.$stream.' '.escapeshellcmd($path).' 2>%1');
		}

		return $output;
	}

	/**
	 * Get video file duration
	 *
	 * @param   string  $path    Path to a file.
	 * @param   string  $format  Output format. If set to true, format result to 00:00:00:000, as is otherwise.
	 *
	 * @return  mixed   Array with results if error, string otherwise
	 */
	public function getVideoDuration($path, $format=false) {
		if (!function_exists('shell_exec')) {
			die('Function is not exists or safe mode is on!');
		}

		$check_lib = $this->checkLibrary($this->params->get('ffprobe_path'));
		if ($check_lib !== true) {
			return $check_lib;
		}

		if (IS_WIN) {
			$output = shell_exec(escapeshellcmd($this->params->get('ffprobe_path')).' -loglevel error -show_format -show_streams '.escapeshellcmd($path).' -print_format json 2>&1');
		} else {
			$output = shell_exec(escapeshellcmd($this->params->get('ffprobe_path')).' -loglevel error -show_format -show_streams '.escapeshellcmd($path).' -print_format json 2>%1');
		}

		$object = json_decode($output);

		if ($format) {
			$seconds = round($object->format->duration);
			$duration = sprintf('%02d:%02d:%02d', ($seconds / 3600), ($seconds / 60 % 60), $seconds % 60);
		} else {
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
	public function checkLibrary($path) {
		$path = JPath::clean($path);

		if (!file_exists($path)) {
			$error = JText::sprintf('COM_KA_MEDIAMANAGER_FFMPEG_NOTFOUND', $path);
			JLog::add($error.' in '.__METHOD__, JLog::CRITICAL);

			return array(false, $error);
		}

		return true;
	}
}
