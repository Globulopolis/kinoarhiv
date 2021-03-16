<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * Media processing library class
 *
 * @since  3.1
 */
class KAMedia
{
	/**
	 * Class instance
	 *
	 * @var  KAMedia
	 *
	 * @since 3.1
	 */
	protected static $instance;

	/**
	 * Component params
	 *
	 * @var  object
	 *
	 * @since 3.1
	 */
	protected $params;

	/**
	 * Class constructor.
	 *
	 * @since  3.1
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
	 * @since  3.1
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
	 * @since  3.1
	 */
	public function createVideoScreenshot($folder, $filename, $time)
	{
		$app = JFactory::getApplication();
		$ffmpegPath = JPath::clean($this->params->get('ffmpeg_path'));

		if (empty($ffmpegPath))
		{
			$app->enqueueMessage(JText::_('COM_KA_MOVIES_GALLERY_ERROR_FILENOTFOUND') . ' ' . $ffmpegPath, 'error');

			return false;
		}

		if (!KAComponentHelper::functionExists('shell_exec'))
		{
			$app->enqueueMessage('shell_exec() function not exists or safe mode or suhosin is On!', 'error');

			return false;
		}

		$checkLibrary = $this->checkLibrary($this->params->get('ffmpeg_path'));

		if ($checkLibrary !== true)
		{
			$app->enqueueMessage($checkLibrary[1], 'error');

			return false;
		}

		$finfo          = pathinfo($folder . $filename);
		$resultFilename = $finfo['filename'] . '.png';
		$videoInfo      = $this->getVideoInfo($folder . $filename);

		if ($videoInfo === false)
		{
			$app->enqueueMessage(JText::_('ERROR'), 'error');

			return false;
		}

		/*
		 * To avoid some errors with ffmpeg(library doesn't understand folders or files with % in teir names)
		 * get temp Joomla folder, store screenshot to this folder and when copy to dest path.
		 */
		jimport('joomla.filesystem.file');

		$config    = JFactory::getConfig();
		$tmpFolder = JPath::clean($config->get('tmp_path') . '/');
		$videoInfo = json_decode($videoInfo);
		$scrWidth  = (int) $this->params->get('player_width');
		$scrHeight = ($videoInfo->streams[0]->height * $scrWidth) / $videoInfo->streams[0]->width;

		@set_time_limit(0);
		$cmd = $ffmpegPath . ' -hide_banner -nostats -i ' . $folder . $filename . ' -ss ' . $time
			. ' -f image2 -vframes 1 -s ' . floor($scrWidth) . 'x' . floor($scrHeight) . ' ' . $tmpFolder . $resultFilename . ' -y';

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
		JFile::copy($tmpFolder . $resultFilename, $folder . $resultFilename);
		JFile::delete($tmpFolder . $resultFilename);

		return array(
			'filename' => $resultFilename,
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
	 * @since  3.1
	 */
	public function getVideoInfo($path, $stream = 'v:0', $format = 'json')
	{
		if (!KAComponentHelper::functionExists('shell_exec'))
		{
			die('shell_exec() function not exists or safe mode is on!');
		}

		if (!is_file($path))
		{
			return false;
		}

		$checkLibrary = $this->checkLibrary($this->params->get('ffprobe_path'));

		if ($checkLibrary !== true)
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

		return shell_exec($cmd);
	}

	/**
	 * Get video file duration
	 *
	 * @param   string   $path    Path to a file.
	 * @param   boolean  $format  Output format. If set to true, format result to 00:00:00:000, as is otherwise.
	 *
	 * @return  mixed   Array with results if error, string otherwise
	 *
	 * @since  3.1
	 */
	public function getVideoDuration($path, $format = false)
	{
		if (!KAComponentHelper::functionExists('shell_exec'))
		{
			die('shell_exec() function not exists or safe mode is on!');
		}

		if (!is_file($path))
		{
			return array(false, 'Video file not found at path ' . $path);
		}

		$checkLibrary = $this->checkLibrary($this->params->get('ffprobe_path'));

		if ($checkLibrary !== true)
		{
			return $checkLibrary;
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
	 * @since  3.1
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
	 * Check if media library(ffmpeg, ffprobe) exists or available.
	 *
	 * @return  getID3
	 *
	 * @since  3.1
	 */
	public function getID3Lib()
	{
		$helperapps = JPath::clean(JComponentHelper::getParams('com_kinoarhiv')->get('getid3_helperapps'));

		define('GETID3_HELPERAPPSDIR', $helperapps);

		jimport('components.com_kinoarhiv.libraries.vendor.getid3.getid3.getid3', JPATH_ROOT);

		return new getID3;
	}

	/**
	 * Method to parse SACD using console util.
	 * More info https://www.videohelp.com/software/sacd-extract
	 *
	 * @param   string   $path       Path to iso file.
	 * @param   boolean  $exportCue  Export cue if can.
	 *
	 * @return  array
	 *
	 * @since  3.1
	 */
	public function getSACDInfo($path, $exportCue = true)
	{
		if (!KAComponentHelper::functionExists('shell_exec'))
		{
			die('shell_exec() function not exists or safe mode is on!');
		}

		$path = JPath::clean($path);
		$exe  = JPath::clean(JComponentHelper::getParams('com_kinoarhiv')->get('getid3_helperapps'));

		$exportCue = $exportCue ? '--export-cue' : '';
		$cmd = $exe . ' --print ' . $exportCue . ' --input="' . $path . '"';

		if (IS_WIN)
		{
			$cmd .= ' 2>&1';
		}
		else
		{
			$cmd .= ' 2>%1';
		}

		JFolder::create(JFactory::getConfig()->get('tmp_path') . '/scan_audio/');

		// Change working directory to Joomla temp directory.
		chdir(JFactory::getConfig()->get('tmp_path') . '/scan_audio/');

		$output = shell_exec($cmd);

		// Try to find cue path.
		preg_match('#Exporting CUE sheet: \[(.*?)\]#is', $output, $matches);

		return array(
			'output' => $output,
			'cue'    => !empty($matches[1]) ? $matches[1] : ''
		);
	}

	/**
	 * Analyze cue file using getID3.
	 *
	 * @param   getID3  $getID3    getID3 class.
	 * @param   string  $filepath  Path to cue file.
	 * @param   array   $finfo     pathinfo array.
	 *
	 * @return  array|boolean
	 *
	 * @since  3.1
	 */
	public function analyzeCue($getID3, $filepath, $finfo)
	{
		/**
		 * Cue file can have different names.
		 * E.g. 'Dire Straits - Brothers In Arms-51.cue' or 'Dire Straits - Brothers In Arms-51.dff.cue'.
		 * Find first variant and if not found try to find second.
		 */
		if (is_file(dirname($filepath) . '/' . $finfo['filename'] . '.cue'))
		{
			$data = $getID3->analyze(dirname($filepath) . '/' . $finfo['filename'] . '.cue');
		}
		elseif (is_file($filepath . '.cue'))
		{
			$data = $getID3->analyze($filepath . '.cue');
		}
		else
		{
			// No cue sheet found, skip this file.
			return false;
		}

		return $data;
	}

	/**
	 * Get short album data from getID3 class.
	 *
	 * @param   array    $data  Array with data from getID3 class.
	 * @param   boolean  $cue   Get from cue.
	 *
	 * @return  array|boolean
	 *
	 * @since  3.1
	 */
	public function getAlbumData($data, $cue = false)
	{
		if ($cue)
		{
			$genres = !empty($data['cue']['comments']['genre']) ? $data['cue']['comments']['genre'][0] : '';
			$album  = array(
				'title'       => $data['cue']['title'],
				'performer'   => $data['cue']['performer'],
				'year'        => $data['cue']['comments']['date'][0],
				'genres'      => preg_split('#,|/#', $genres),
				'tracks_path' => $data['filepath']
			);
		}
		else
		{
			$genres = !empty($data['comments']['genre']) ? $data['comments']['genre'][0] : '';
			$album = array(
				'title'       => $data['comments']['album'][0],
				'performer'   => $data['comments']['artist'][0],
				'year'        => $data['comments']['date'][0],
				'genres'      => preg_split('#,|/#', $genres),
				'tracks_path' => $data['filepath']
			);
		}

		return $album;
	}

	/**
	 * Get tracks from cue or array from getID3 class.
	 *
	 * @param   array    $data      Array with data from getID3 class.
	 * @param   boolean  $cue       Analyze cue data.
	 * @param   string   $filename  Original file name.
	 *
	 * @return  array
	 *
	 * @since  3.1
	 */
	public function getTracks($data, $cue = false, $filename = '')
	{
		$tracks = array();

		if ($cue)
		{
			if (!empty($data['cue']['tracks']))
			{
				foreach ($data['cue']['tracks'] as $track)
				{
					$tracks[] = array(
						'title'        => $track['title'],
						'track_number' => $track['track_number'],
						'length'       => '',
						'isrc'         => !empty($track['isrc']) ? $track['isrc'] : '',
						'filename'     => !empty($filename) ? $filename : $track['datafile']['filename'],
						'is_playlist'  => true
					);
				}
			}
		}
		else
		{
			if (!empty($data['comments']))
			{
				// Preformat seconds and time
				$seconds = number_format($data['playtime_seconds'], 6, '.', '');
				$datetime = DateTime::createFromFormat('U.u', $seconds);
				$length = $datetime->format('H:i:s.u');

				$tracks = array(
					'title'        => $data['comments']['title'][0],
					'track_number' => $data['comments']['track_number'][0],
					'length'       => $length,
					'isrc'         => !empty($data['comments']['isrc']) ? $data['comments']['isrc'][0] : '',
					'filename'     => $data['filename'],
					'is_playlist'  => true
				);
			}
		}

		return $tracks;
	}
}
