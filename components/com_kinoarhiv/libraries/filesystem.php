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
 * Class KAFilesystem
 *
 * @since  3.0
 */
class KAFilesystem
{
	/**
	 * @var    KAFilesystem  instance of this class
	 *
	 * @since  3.0
	 */
	protected static $instance;

	/**
	 * Constructor
	 *
	 * The constructor is protected to force the use of KAFilesystem::getInstance()
	 *
	 * @since  3.0
	 */
	protected function __construct()
	{
	}

	/**
	 * Method to get a patcher
	 *
	 * @return  KAFilesystem
	 *
	 * @since   3.0
	 */
	public static function getInstance()
	{
		if (!isset(static::$instance))
		{
			static::$instance = new static;
		}

		return static::$instance;
	}

	/**
	 * Sets-up headers and starts transfering.
	 * BEWARE!!! This method doesn't work correctly on 32bit servers. It's not possible to fix it! DO NOT USE 32bit platforms!
	 *
	 * @param   string   $file_path        Path to a file
	 * @param   integer  $throttle         Use throttle mechanism
	 * @param   array    $throttle_config  Throttle mechanism config. array('seconds'=>1, 'bytes'=>1024)
	 * @param   boolean  $disposition      Send or not 'Content-Disposition' header
	 * @param   boolean  $cache            Use cache
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function sendFile($file_path, $throttle=0, $throttle_config=array(), $disposition=true, $cache=true)
	{
		$file_path = JPath::clean($file_path);

		if (!is_readable($file_path))
		{
			throw new Exception('File not found or inaccessible!');
		}

		if (!array_key_exists('seconds', $throttle_config) && empty($throttle_config['seconds']))
		{
			$throttle_config['seconds'] = 0.1;
		}

		if (!array_key_exists('throttle_bytes', $throttle_config) && empty($throttle_config['throttle_bytes']))
		{
			$throttle_config['throttle_bytes'] = 0.1;
		}

		// Turn off output buffering to decrease cpu usage
		$this->cleanAll();

		// Required for IE, otherwise Content-Disposition may be ignored
		if (ini_get('zlib.output_compression'))
		{
			@ini_set('zlib.output_compression', 'Off');
		}

		header('Content-type: ' . $this->detectMime($file_path));

		if ($disposition)
		{
			header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
		}

		header('Accept-Ranges: bytes');

		if (!$cache)
		{
			header('Pragma: private');
			header('Cache-control: private, max-age=2592000');
			header('Expires: Mon, 01 Jan 1997 00:00:00 GMT');
		}
		else
		{
			$last_modified = filemtime($file_path);
			$etag = md5_file($file_path);
			$etag_header = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false;
			$modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;

			header('Pragma: cache');
			header('Cache-control: no-transform, public, max-age=2592000, s-maxage=7776000');
			header('Etag: ' . $etag);
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
			header('Expires: Mon, 01 Jan 2100 00:00:00 GMT');

			if (@strtotime($modified_since) == $last_modified || $etag_header == $etag)
			{
				header('HTTP/1.1 304 Not Modified');
				die();
			}
		}

		$file = @fopen($file_path, 'rb');
		$size = $this->getFilesize($file);

		// Multipart-download and download resuming support
		$valid_range = true;
		$range = 0;

		if (isset($_SERVER['HTTP_RANGE']))
		{
			// Check to correct HTTP_RANGE value
			if (!preg_match('/^bytes=((\d*-\d*,? ?)+)$/', $_SERVER['HTTP_RANGE'], $matches))
			{
				$valid_range = false;
			}

			if ($valid_range)
			{
				list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
				list($range) = explode(',', $range, 2);
				list($range, $range_end) = explode('-', $range);
				$range = (int) $range;

				if (!$range_end)
				{
					$range_end = $size - 1;
				}
				else
				{
					$range_end = (int) $range_end;
				}

				$new_length = $range_end - $range + 1;

				header('HTTP/1.1 206 Partial Content');
				header('Content-Length: ' . $new_length);
				header('Content-Range: bytes ' . $range . '-' . $range_end . '/' . $size);
			}
			else
			{
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				header('Content-Range: bytes */' . $size);
				die();
			}
		}
		else
		{
			$new_length = $size;
			header('HTTP/1.1 200 OK');
			header('Content-Length: ' . $size);
		}

		// Output the file itself
		$chunksize = ($throttle == 1) ? (int) $throttle_config['bytes'] : 1024 * 8;
		$bytes_send = 0;

		if ($file)
		{
			if (isset($_SERVER['HTTP_RANGE']) && $valid_range)
			{
				fseek($file, $range);
			}

			while (!feof($file) && (!connection_aborted()) && ($bytes_send < $new_length))
			{
				$buffer = fread($file, $chunksize);
				echo $buffer;
				flush();

				if ($throttle == 1)
				{
					usleep((float) $throttle_config['seconds'] * 1000000);
				}

				$bytes_send += strlen($buffer);
			}

			fclose($file);
		}
		else
		{
			throw new Exception('Error - cannot open file.');
		}

		die();
	}

	/**
	 * Clean all buffers
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	private function cleanAll()
	{
		while (ob_get_level())
		{
			ob_end_clean();
		}
	}

	/**
	 * Moves a folder and files.
	 *
	 * @param   mixed    $src   The path to the source folder or an array of paths. If $src is array when the folder content
	 *                          move into $dest.
	 * @param   mixed    $dest  The path to the destination folder or an array of paths.
	 * @param   boolean  $copy  If false when just copy content, copy and remove otherwise.
	 *
	 * @return  boolean
	 */
	public function move($src, $dest, $copy = false)
	{
		if (is_array($src))
		{
			foreach ($src as $key => $source)
			{
				if (is_array($dest))
				{
					$this->moveItem($source, $dest[$key], $copy);
				}
				else
				{
					$this->moveItem($source, str_replace(basename($dest), '', $dest) . basename($source), $copy);
				}
			}
		}
		else
		{
			$this->moveItem($src, $dest, $copy);
		}

		return true;
	}

	/**
	 * Moves a folder and files.
	 *
	 * @param   mixed    $src   The path to the source folder or an array of paths. If $src is array when the folder content
	 *                          move into $dest.
	 * @param   mixed    $dest  The path to the destination folder or an array of paths.
	 * @param   boolean  $copy  If false when just copy content, copy and remove otherwise.
	 *
	 * @return  boolean
	 */
	private function moveItem($src, $dest, $copy)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		// Create target folder only if folder not exists and source folder have a files(folder size more than zero bytes).
		if (!file_exists($dest) && $this->getFolderSize($src) > 0)
		{
			JFolder::create($dest);
		}

		foreach (glob($src . DIRECTORY_SEPARATOR . '*.*', GLOB_NOSORT) as $filename)
		{
			if (JFile::copy($filename, $dest . '/' . basename($filename)))
			{
				// Delete source file
				if (!$copy)
				{
					if (!JFile::delete($filename))
					{
						JLog::add(__METHOD__ . ': ' . JText::sprintf('JLIB_FILESYSTEM_DELETE_FAILED', $filename), JLog::WARNING, 'jerror');
						break;
					}
				}
			}
			else
			{
				JLog::add(__METHOD__ . ': ' . JText::_('JLIB_FILESYSTEM_ERROR_COPY_FAILED') . ': ' . $filename, JLog::WARNING, 'jerror');
				break;
			}
		}

		if (!$copy)
		{
			if (file_exists($src))
			{
				JFolder::delete($src);
			}
		}

		return true;
	}

	/**
	 * Get file size. See http://php.net/manual/en/function.filesize.php#115792
	 *
	 * @param   resource  $handle  Path to a file.
	 *
	 * @return  mixed (int|float) File size on success or false on error
	 *
	 * @since   3.0
	 */
	public function getFilesize($handle)
	{
		$result = false;

		if (is_resource($handle))
		{
			if (PHP_INT_SIZE < 8)
			{
				if (0 === fseek($handle, 0, SEEK_END))
				{
					$result = 0.0;
					$step = 0x7FFFFFFF;

					while ($step > 0)
					{
						if (0 === fseek($handle, -$step, SEEK_CUR))
						{
							$result += floatval($step);
						}
						else
						{
							$step >>= 1;
						}
					}
				}
			}
			elseif (0 === fseek($handle, 0, SEEK_END))
			{
				$result = ftell($handle);
			}
		}

		return $result;
	}

	/**
	 * Get the folder size in bytes
	 *
	 * @param   string   $path   Filesystem path to a folder.
	 * @param   boolean  $cache  Clear stat cache.
	 *
	 * @return  mixed    Folder size, false on error.
	 */
	public function getFolderSize($path, $cache = true)
	{
		$path = JPath::clean($path);

		if (!$path)
		{
			JLog::add(__METHOD__ . ': ' . JText::_('JLIB_FILESYSTEM_ERROR_DELETE_BASE_DIRECTORY'), JLog::WARNING, 'jerror');

			return false;
		}

		if ($cache)
		{
			clearstatcache();
		}

		$size = 0;

		if (is_readable($path))
		{
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $file)
			{
				$size += $file->getSize();
			}

			return (int) $size;
		}

		return false;
	}

	/**
	 * Get MIME-type of the file
	 *
	 * @param   string  $path  Path to a file.
	 *
	 * @return  string
	 *
	 * @since   3.0
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
			elseif (KAComponentHelper::functionExists('exif_imagetype') === true)
			{
				$mime = image_type_to_mime_type(exif_imagetype($path));
			}
		}

		return $mime;
	}

	/**
	 * Some mime-types.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function mimes()
	{
		return array(
			'swf'   => 'application/x-shockwave-flash',
			'mid'   => 'audio/midi',
			'midi'  => 'audio/midi',
			'mpga'  => 'audio/mpeg',
			'mp2'   => 'audio/mpeg',
			'mp3'   => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
			'aif'   => array('audio/x-aiff', 'audio/aiff'),
			'aiff'  => array('audio/x-aiff', 'audio/aiff'),
			'aifc'  => 'audio/x-aiff',
			'ram'   => 'audio/x-pn-realaudio',
			'rm'    => 'audio/x-pn-realaudio',
			'rpm'   => 'audio/x-pn-realaudio-plugin',
			'ra'    => 'audio/x-realaudio',
			'rv'    => 'video/vnd.rn-realvideo',
			'wav'   => array('audio/x-wav', 'audio/wave', 'audio/wav'),
			'bmp'   => array(
				'image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-xbitmap', 'image/x-win-bitmap',
				'image/x-windows-bmp', 'image/ms-bmp', 'image/x-ms-bmp', 'application/bmp', 'application/x-bmp',
				'application/x-win-bitmap'
			),
			'gif'   => 'image/gif',
			'jpeg'  => array('image/jpeg', 'image/pjpeg'),
			'jpg'   => array('image/jpeg', 'image/pjpeg'),
			'jpe'   => array('image/jpeg', 'image/pjpeg'),
			'jp2'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'j2k'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'jpf'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'jpg2'  => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'jpx'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'jpm'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'mj2'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'mjp2'  => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'png'   => array('image/png', 'image/x-png'),
			'xml'   => array('application/xml', 'text/xml', 'text/plain'),
			'mpeg'  => 'video/mpeg',
			'mpg'   => 'video/mpeg',
			'mpe'   => 'video/mpeg',
			'qt'    => 'video/quicktime',
			'mov'   => 'video/quicktime',
			'avi'   => array('video/x-msvideo', 'video/msvideo', 'video/avi', 'application/x-troff-msvideo'),
			'movie' => 'video/x-sgi-movie',
			'3g2'   => 'video/3gpp2',
			'3gp'   => array('video/3gp', 'video/3gpp'),
			'mp4'   => 'video/mp4',
			'm4a'   => 'audio/x-m4a',
			'f4v'   => array('video/mp4', 'video/x-f4v'),
			'flv'   => 'video/x-flv',
			'webm'  => 'video/webm',
			'aac'   => 'audio/x-acc',
			'm4u'   => 'application/vnd.mpegurl',
			'm3u'   => 'text/plain',
			'wmv'   => array('video/x-ms-wmv', 'video/x-ms-asf'),
			'au'    => 'audio/x-au',
			'ac3'   => 'audio/ac3',
			'flac'  => 'audio/x-flac',
			'ogg'   => array('audio/ogg', 'video/ogg', 'application/ogg'),
			'oga'   => 'audio/ogg',
			'ogv'   => array('video/ogg', 'application/ogg'),
			'wma'   => array('audio/x-ms-wma', 'video/x-ms-asf'),
			'srt'   => array('text/srt', 'text/plain'),
			'vtt'   => array('text/vtt', 'text/plain')
		);
	}
}
