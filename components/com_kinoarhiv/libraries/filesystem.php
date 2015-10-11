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
 * Class KAFilesystem
 *
 * @since  3.0
 */
class KAFilesystem
{
	/**
	 * @var    array  instance of this class
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
	 * Sets-up headers and starts transfering bytes
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

		JResponse::setHeader('Content-type', $this->detectMime($file_path), true);

		if ($disposition)
		{
			JResponse::setHeader('Content-Disposition', 'inline; filename="' . basename($file_path) . '"');
		}

		JResponse::setHeader('Accept-Ranges', 'bytes');

		if (!$cache)
		{
			JResponse::setHeader('Pragma', 'private');
			JResponse::setHeader('Cache-control', 'private, max-age=2592000');
			JResponse::setHeader('Expires', 'Mon, 01 Jan 1997 00:00:00 GMT');
		}
		else
		{
			$last_modified = filemtime($file_path);
			$etag = md5_file($file_path);
			$etag_header = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false;
			$modified_since = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;

			JResponse::setHeader('Pragma', 'cache');
			JResponse::setHeader('Cache-control', 'no-transform, public, max-age=2592000, s-maxage=7776000');
			JResponse::setHeader('Etag', $etag);
			JResponse::setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
			JResponse::setHeader('Expires', 'Mon, 01 Jan 2100 00:00:00 GMT');

			if (@strtotime($modified_since) == $last_modified || $etag_header == $etag)
			{
				JResponse::setHeader('HTTP/1.1', '304 Not Modified');
				JResponse::sendHeaders();
				die();
			}
		}

		$file = @fopen($file_path, 'rb');
		$size = $this->getFilesize($file);

		// Multipart-download and download resuming support
		$valid_range = true;

		if (isset($_SERVER['HTTP_RANGE']))
		{
			// Check to correct HTTP_RANGE value
			if (!preg_match('/^bytes=((\d*-\d*,? ?)+)$/', $_SERVER['HTTP_RANGE'], $matches))
			{
				$valid_range = false;
			}

			if ($valid_range)
			{
				list($a, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
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

				JResponse::setHeader('HTTP/1.1', '206 Partial Content');
				JResponse::setHeader('Content-Length', $new_length);
				JResponse::setHeader('Content-Range', 'bytes ' . $range . '-' . $range_end . '/' . $size);
			}
			else
			{
				JResponse::setHeader('HTTP/1.1', '416 Requested Range Not Satisfiable');
				JResponse::setHeader('Content-Range', 'bytes */' . $size);
				JResponse::sendHeaders();
				die();
			}
		}
		else
		{
			$new_length = $size;
			JResponse::setHeader('Content-Length', $size);
		}

		JResponse::sendHeaders();

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
	 */
	private function cleanAll()
	{
		while (ob_get_level())
		{
			ob_end_clean();
		}
	}

	/**
	 * Get file size. See http://php.net/manual/en/function.filesize.php#115792
	 *
	 * @param   resource  $handle  Path to a file.
	 *
	 * @return  mixed (int|float) File size on success or false on error
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
}
