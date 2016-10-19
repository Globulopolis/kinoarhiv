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

use Joomla\Registry\Registry;

/**
 * Component helper class
 *
 * @since  3.0
 */
class KAComponentHelper extends JComponentHelper
{
	/**
	 * Include some necessary JS into the HEAD of the document. Don't include if document format is not a html.
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public static function setHeadTags()
	{
		$document = JFactory::getDocument();
		$params = self::getParams('com_kinoarhiv');

		// Return nothing because JHtml::script doesn't work for JDocumentRaw
		if ($document->getType() != 'html')
		{
			return;
		}

		JHtml::_('stylesheet', JUri::base() . 'components/com_kinoarhiv/assets/css/style.css');
		JHtml::_('stylesheet', JUri::base() . 'components/com_kinoarhiv/assets/css/plugins.css');
		JHtml::_('stylesheet', JUri::root() . 'components/com_kinoarhiv/assets/themes/ui/' . $params->get('ui_theme') . '/jquery-ui.css');

		JHtml::_('jquery.framework');
		JHtml::_('script', JUri::base() . 'components/com_kinoarhiv/assets/js/jquery-ui.min.js');
		JHtml::_('script', JUri::root() . 'components/com_kinoarhiv/assets/js/ui.aurora.min.js');
		JHtml::_('script', JUri::root() . 'components/com_kinoarhiv/assets/js/js.cookie.min.js');
		JHtml::_('script', JUri::base() . 'components/com_kinoarhiv/assets/js/utils.js');

		JText::script('COM_KA_CLOSE', true);

		// Add some variables into the global scope for autocomplete
		JText::script('COM_KA_SEARCH_AJAX', true);
		$document->addScriptDeclaration("var ka_theme = '" . $params->get('ka_theme') . "', uri_root = '" . JUri::root() . "';");
	}

	/**
	 * Get data from remote server
	 *
	 * @param   string   $url        URL
	 * @param   null     $headers    Headers to send
	 * @param   integer  $timeout    Request timeout in seconds
	 * @param   string   $transport  Transport type
	 *
	 * @return  JHttpResponse
	 *
	 * @since 3.0
	 * @deprecated 3.1
	 */
	public static function getRemoteData($url, $headers = null, $timeout = 30, $transport = 'curl')
	{
		$options = new Registry;

		$http = JHttpFactory::getHttp($options, $transport);
		$response = $http->get($url, $headers, $timeout);

		return $response;
	}

	/**
	 * Load mediamanager assets
	 *
	 * @return void
	 */
	public static function loadMediamanagerAssets()
	{
		JHtml::_('stylesheet', JUri::base() . 'components/com_kinoarhiv/assets/css/mediamanager.css');
		JHtml::_('script', JUri::base() . 'components/com_kinoarhiv/assets/js/mediamanager/plupload.full.min.js');
		self::getScriptLanguage('', true, 'mediamanager', false, '_');
		JHtml::_('script', JUri::base() . 'components/com_kinoarhiv/assets/js/mediamanager/jquery.plupload.queue.min.js');
	}

	/**
	 * Just proxy for KALanguage::getScriptLanguage()
	 *
	 * @param   string   $file         Part of the filename w/o language tag and extension
	 * @param   string   $jhtml        Use JHtml::script() to load
	 * @param   string   $script_type  Type of the script(folder name in assets/js/i8n/)
	 * @param   boolean  $frontend     Load language file from the frontend if set to true
	 * @param   string   $separator    Separator, which is used for split two-letter language code and two-letter country
	 *                                 code. Usually separated by hyphens('-'). E.g. en-US, ru-RU
	 *
	 * @return void
	 */
	public static function getScriptLanguage($file, $jhtml, $script_type, $frontend, $separator='-')
	{
		JLoader::register('KALanguage', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'language.php');

		KALanguage::getScriptLanguage($file, $jhtml, $script_type, $frontend, $separator);
	}

	/**
	 * Method to get an errors from $errors and enqueue or directly display them.
	 *
	 * @param   mixed    $errors  An Exception object or array.
	 * @param   string   $format  Document type format.
	 * @param   integer  $count   Number of errors to process.
	 *
	 * @return  string
	 */
	public static function renderErrors($errors, $format = 'html', $count = 3)
	{
		$app = JFactory::getApplication();
		$_errors = array();

		for ($i = 0, $n = count($errors); $i < $n && $i < $count; $i++)
		{
			if ($errors[$i] instanceof Exception)
			{
				if ($format == 'html')
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$_errors[] = $errors[$i]->getMessage();
				}
			}
			else
			{
				if ($format == 'html')
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
				else
				{
					$_errors[] = $errors[$i];
				}
			}
		}

		if ($format != 'html')
		{
			return implode('<br />', $_errors);
		}

		return true;
	}

	/**
	 * Logger
	 *
	 * @param   string  $message  Text to log.
	 * @param   mixed   $silent   Throw exception or not. Default do not throw.
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 */
	public static function eventLog($message, $silent = true)
	{
		$params = self::getParams('com_kinoarhiv');
		$uri = JUri::getInstance();

		$message = $message . "\t" . $uri->current() . '?' . $uri->getQuery();

		if ($params->get('logger') == 'syslog')
		{
			$backtrace = debug_backtrace();
			$stack = '';

			for ($i = 0, $n = count($backtrace); $i < $n; $i++)
			{
				$trace = $backtrace[$i];
				$class = isset($trace['class']) ? $trace['class'] : '';
				$type = isset($trace['type']) ? $trace['type'] : '';
				$stack .= "#" . $i . " " . $trace['file'] . "#" . $trace['line'] . " " . $class . $type . $trace['function'] . "\n";
			}

			openlog('com_kinoarhiv_log', LOG_PID, LOG_DAEMON);
			syslog(LOG_CRIT, $message . "\nBacktrace:\n" . $stack);
			closelog();

			if (!$silent || is_string($silent))
			{
				throw new Exception($message, 500);
			}
		}
		else
		{
			jimport('joomla.log.log');

			JLog::addLogger(
				array(
					'text_file' => 'com_kinoarhiv.errors.php'
				),
				JLog::ALL, 'com_kinoarhiv'
			);

			JLog::add($message, JLog::WARNING, 'com_kinoarhiv');

			if (!$silent || is_string($silent))
			{
				throw new Exception($message, 500);
			}
		}
	}

	/**
	 * Tests if a function exists. Also handles the case where a function is disabled via Suhosin.
	 *
	 * @param   string  $function  Function name to check.
	 *
	 * @return  boolean
	 */
	public static function functionExists($function)
	{
		if ($function == 'eval')
		{
			// Does not check suhosin.executor.eval.whitelist (or blacklist)
			if (extension_loaded('suhosin'))
			{
				return @ini_get("suhosin.executor.disable_eval") != "1";
			}

			return true;
		}

		$exists = function_exists($function);

		if (extension_loaded('suhosin'))
		{
			$blacklist = @ini_get("suhosin.executor.func.blacklist");

			if (!empty($blacklist))
			{
				$blacklistFunctions = array_map('strtolower', array_map('trim', explode(',', $blacklist)));

				return $exists && !in_array($function, $blacklistFunctions);
			}
		}

		return $exists;
	}

	/**
	 * Checks for a form token in the request.
	 *
	 * Use in conjunction with JHtml::_('form.token') or JSession::getFormToken.
	 *
	 * @param   string  $method  The request method in which to look for the token key.
	 *
	 * @return  boolean  True if found and valid, false otherwise.
	 *
	 * @since   3.0
	 */
	public static function checkToken($method = 'post')
	{
		$token = JSession::getFormToken();
		$app = JFactory::getApplication();

		return (bool) $app->input->$method->get($token, '', 'alnum');
	}
}
