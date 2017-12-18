<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * Component helper class
 *
 * @since  3.0
 */
class KAComponentHelper
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

		if ($document->getType() != 'html')
		{
			return;
		}

		JHtml::_('jquery.framework');
		JHtml::_('bootstrap.framework');
		JHtml::_('script', 'media/com_kinoarhiv/js/core.min.js');
		JHtml::_('script', 'media/com_kinoarhiv/js/frontend.min.js');
		JHtml::_('script', 'media/com_kinoarhiv/js/ui.aurora.min.js');

		$params = JComponentHelper::getParams('com_kinoarhiv');

		if ($params->get('vegas_enable') == 1)
		{
			self::setPageBackground($params);
		}

		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/ui.aurora-' . $params->get('ui_theme') . '.css');
		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/component/themes/' . $params->get('ka_theme') . '/plugins.min.css');
		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/component/themes/' . $params->get('ka_theme') . '/styles.min.css');

		// Add some variables into the global scope
		$js_vars = array(
			'params' => array(
				'ka_theme' => $params->get('ka_theme')
			),
			'language' => array(
				'tag'                          => JFactory::getLanguage()->getTag(),
				'JGLOBAL_SELECT_AN_OPTION'     => JText::_('JGLOBAL_SELECT_AN_OPTION', true), // Default placeholder, if not set for Select2,
				'COM_KA_CLOSE'                 => JText::_('COM_KA_CLOSE', true),
				'JERROR_AN_ERROR_HAS_OCCURRED' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED', true),
				'COM_KA_NEWWINDOW_BLOCKED_A'   => JText::_('COM_KA_NEWWINDOW_BLOCKED_A', true),
				'COM_KA_NEWWINDOW_BLOCKED_B'   => JText::_('COM_KA_NEWWINDOW_BLOCKED_B', true),
			)
		);
		$document->addScriptDeclaration('var KA_vars = ' . json_encode($js_vars) . ';');
	}

	/**
	 * Return html structure for message.
	 *
	 * @param   string   $text        Text to display. Translated string.
	 * @param   string   $class       See http://getbootstrap.com/2.3.2/components.html#alerts
	 * @param   boolean  $close       Show close link.
	 * @param   string   $block_text  Text for display in block mode.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public static function showMsg($text, $class = 'alert-info', $close = false, $block_text = '')
	{
		if (stripos($class, 'alert-block') !== false && !empty($block_text))
		{
			$text = '<h4>' . $block_text . '</h4>' . $text;
		}

		$html = '<div class="alert ' . (string) $class . '">';

		if ($close)
		{
			$html .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
		}

		$html .= $text . '</div>';

		return $html;
	}

	/**
	 * Clean text with html.
	 *
	 * @param   string  $text   Text for clean.
	 * @param   string  $tags   String with allowed tags and their attributes.
	 * @param   array   $extra  Addtitional parameters for HTMLPurifier.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public static function cleanHTML($text, $tags = '', $extra = array())
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$cache_path = JPath::clean(JPATH_CACHE . '/kinoarhiv/DefinitionCache/Serializer');

		require_once JPath::clean(JPATH_COMPONENT . '/libraries/vendor/htmlpurifier/HTMLPurifier.standalone.php');

		$purifier_config = HTMLPurifier_Config::createDefault();

		if (!file_exists($cache_path))
		{
			if (!mkdir($cache_path, 0777, true))
			{
				self::eventLog('Failed to create definition cache folder at path: "' . $cache_path . '"');
			}
		}

		$purifier_config->set('Cache.SerializerPath', $cache_path);

		if (empty($tags))
		{
			$tags = $params->get('html_allowed_tags');
		}

		$purifier_config->set('HTML.Allowed', $tags);

		if (count($extra) > 0)
		{
			foreach ($extra as $key => $value)
			{
				$purifier_config->set($key, $value);
			}
		}

		$purifier = new HTMLPurifier($purifier_config);
		$clean_html = $purifier->purify($text);

		return $clean_html;
	}

	/**
	 * Logger
	 *
	 * @param   string  $message  Text to log.
	 * @param   mixed   $silent   Throw exception or not. True - throw, false - not, 'ui' - show message.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since  3.0
	 */
	public static function eventLog($message, $silent = true)
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$uri = JUri::getInstance();
		$user = JFactory::getUser();

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
				if ($silent == 'ui')
				{
					echo self::showMsg(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'alert-error');

					if ($user->get('isRoot'))
					{
						echo '<pre>' . $message . '</pre>';
					}
				}
				else
				{
					throw new Exception($message, 500);
				}
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
				if ($silent == 'ui')
				{
					echo self::showMsg(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'alert-error');

					if ($user->get('isRoot'))
					{
						echo '<pre>' . $message . '</pre>';
					}
				}
				else
				{
					throw new Exception($message, 500);
				}
			}
		}
	}

	/**
	 * Wrapper for JApplicationWeb::redirect() to use in the views
	 *
	 * @param   string  $url          The URL to redirect to. Can only be http/https URL
	 * @param   string  $message      The message to enqueue.
	 * @param   string  $messageType  The message type. Default is message.
	 *
	 * @return  mixed   False if url is empty, void otherwise
	 *
	 * @since  3.0
	 */
	public static function doRedirect($url = null, $message = null, $messageType = 'message')
	{
		if (!is_null($url))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($message, $messageType);
			$app->redirect($url);
		}

		return false;
	}

	/**
	 * Create a custom label html tag
	 *
	 * @param   string  $for      Input ID
	 * @param   string  $text     Label text.
	 * @param   string  $title    Label title.
	 * @param   string  $class    CSS classname(s).
	 * @param   array   $attribs  Additional HTML attributes
	 *
	 * @return   string
	 *
	 * @since  3.0
	 */
	public static function setLabel($for, $text, $title = '', $class = '', $attribs = array())
	{
		$title = !empty($title) ? ' title="' . JText::_($title) . '" ' : ' ';
		$class = !empty($class) ? ' class="' . $class . '" ' : ' ';
		$attrs = '';

		if (is_array($attribs) && func_num_args() == 5)
		{
			$attrs = Joomla\Utilities\ArrayHelper::toString($attribs);
		}

		return '<label id="' . $for . '-lbl"' . $class . 'for="' . $for . '"' . $title . $attrs . '>' . JText::_($text) . '</label>';
	}

	/**
	 * Load language files for JS scripts
	 *
	 * @param   string   $file       Part of the filename w/o language tag and extention. Filenames must follow by the
	 *                               next rules - filename[lang code].js. Leave empty if filename contain only language code.
	 *                               Example: $file[]en-US.js or $file[select2_locale_]da.js
	 * @param   string   $path       Path to folder.
	 * @param   boolean  $jhtml      Use JHtml::script() to load. Set this to false if need to load JS into raw document.
	 * @param   boolean  $lowercase  Convert language string to lowercase or not. If true when en-GB will be en-gb.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function getScriptLanguage($file, $path, $jhtml = true, $lowercase = false)
	{
		$lang = JFactory::getLanguage()->getTag();
		$lang = $lowercase ? Joomla\String\StringHelper::strtolower($lang) : $lang;
		$filename = $file . $lang . '.js';
		$basepath = JPATH_ROOT . '/' . $path . '/';
		$url = JPath::clean($path . '/', '/');

		if (is_file(JPath::clean($basepath . $filename)))
		{
			if ($jhtml)
			{
				JHtml::_('script', $url . $filename);
			}
			else
			{
				echo '<script src="' . $url . $filename . '" type="text/javascript"></script>' . "\n";
			}
		}
		elseif (is_file(JPath::clean($basepath . $file . substr($lang, 0, 2) . '.js')))
		{
			if ($jhtml)
			{
				JHtml::_('script', $url . $file . substr($lang, 0, 2) . '.js');
			}
			else
			{
				echo '<script src="' . $url . $file . substr($lang, 0, 2) . '.js" type="text/javascript"></script>' . "\n";
			}
		}
	}

	/**
	 * Tests if a function exists. Also handles the case where a function is disabled via Suhosin.
	 *
	 * @param   string  $function  Function name to check.
	 *
	 * @return  boolean
	 *
	 * @since  3.0
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

		return (bool) JFactory::getApplication()->input->$method->get($token, '', 'alnum');
	}

	/**
	 * Set page background on each compinent page using Vegas.
	 *
	 * @param   object  $params  Component parameters.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function setPageBackground($params)
	{
		JHtml::_('script', 'media/com_kinoarhiv/js/vegas.min.js');
		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/vegas.min.css');

		$document = JFactory::getDocument();
		$items = preg_split('/[\s*,\s*]*,+[\s*,\s*]*/', trim($params->get('vegas_bg')));
		$slides = array();

		foreach ($items as $item)
		{
			$slides[]['src'] = $item;
		}

		$document->addScriptDeclaration('
			jQuery(document).ready(function($){
				$("body").vegas({
					slides: ' . json_encode($slides) . ',
					delay: ' . (int) $params->get('vegas_slideshow_delay') * 1000 . ',
					overlay: "' . JUri::base() . 'media/com_kinoarhiv/images/overlays/' . $params->get('vegas_overlay') . '"
				});
			});
		');

		if ($params->get('vegas_bodybg_transparent') == 1)
		{
			$document->addScriptDeclaration('
				jQuery(document).ready(function($){
					$("' . $params->get('vegas_bodybg_selector') . '").css("background-color", "transparent");
				});
			');
		}
	}
}
