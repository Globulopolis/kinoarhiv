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

use Joomla\Utilities\ArrayHelper;

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
	 */
	public static function setHeadTags()
	{
		$document = JFactory::getDocument();
		$params = self::getParams('com_kinoarhiv');

		if ($document->getType() != 'html')
		{
			return;
		}

		JHtml::_('jquery.framework');
		JHtml::_('script', 'components/com_kinoarhiv/assets/js/component.js');
		JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery-ui.min.js');

		if ($params->get('vegas_enable') == 1)
		{
			JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.vegas.min.js');
		}

		JHtml::_('stylesheet', 'components/com_kinoarhiv/assets/themes/ui/' . $params->get('ui_theme') . '/jquery-ui.css');
		JHtml::_('stylesheet', 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/css/plugin.css');
		JHtml::_('stylesheet', 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/css/style.css');
	}

	/**
	 * Return html structure for message. jQueryUI stylesheets required.
	 *
	 * @param   string   $text   Text for display.
	 * @param   array    $extra  Array of optional elements. $extra['icon'] - the icon type; $extra['type'] - the type of
	 *                           message. Can be 'highlight', 'error', 'disabled'.
	 * @param   boolean  $close  Show close link.
	 *
	 * @return  string
	 */
	public static function showMsg($text, $extra = array(), $close = false)
	{
		$icon = !isset($extra['icon']) ? 'info' : $extra['icon'];
		$type = !isset($extra['type']) ? 'highlight' : $extra['type'];

		if ($close)
		{
			$close_str = ' <a href="" class="ui-icon ui-icon-close" style="display: inline-block;" onclick="jQuery(this).closest(\'.ui-message\').remove(); return false;"></a>';
		}
		else
		{
			$close_str = '';
		}

		$html = '<div class="ui-message"><div class="ui-widget">
			<div class="ui-corner-all ui-state-' . $type . '" style="padding: 0 0.5em;">
				<div style="margin: 5px ! important;">
					<span class="ui-icon ui-icon-' . $icon . '" style="float: left; margin-right: 0.3em;"></span>
					<span style="overflow: hidden; display: block;">' . $text . $close_str . '</span>
				</div>
			</div>
		</div></div>';

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
	 */
	public static function cleanHTML($text, $tags = '', $extra = array())
	{
		$params = self::getParams('com_kinoarhiv');
		$cache_path = JPath::clean(JPATH_CACHE . '/kinoarhiv/DefinitionCache/Serializer');

		require_once JPath::clean(JPATH_COMPONENT . '/libraries/htmlpurifier/HTMLPurifier.standalone.php');

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
	 * Load CSS and Javascript for HTML5/Flash player
	 *
	 * @param   string  $player  Player type.
	 * @param   string  $key     License key.
	 *
	 * @return  mixed
	 */
	public static function loadPlayerAssets($player, $key = '')
	{
		$document = JFactory::getDocument();
		$player_path = 'components/com_kinoarhiv/assets/players/';

		$paths = array(
			'flowplayer'   => array(
				'css' => array(
					$player_path . 'flowplayer/skin/all-skins.css'
				),
				'js'  => array()
			),
			'mediaelement' => array(
				'css' => array(
					$player_path . 'mediaelement/mediaelementplayer.css'
				),
				'js'  => array()
			),
			'videojs'      => array(
				'css' => array(
					$player_path . 'videojs/video-js.css'
				),
				'js'  => array(
					$player_path . 'videojs/video.min.js'
				)
			)
		);

		if ($document->getType() == 'html')
		{
			foreach ($paths[$player] as $k => $v)
			{
				foreach ($v as $url)
				{
					if ($k == 'css')
					{
						JHtml::_('stylesheet', $url);
					}
					elseif ($k == 'js')
					{
						JHtml::_('script', $url);
					}
				}
			}

			if ($player == 'videojs')
			{
				$document->addScriptDeclaration("videojs.options.flash.swf='" . JURI::base() . $player_path . "videojs/video-js.swf';");
			}
			elseif ($player == 'mediaelement')
			{
				JHtml::script($player_path . 'mediaelement/mediaelement-and-player.min.js');
			}
			elseif ($player == 'flowplayer')
			{
				JHtml::script($player_path . 'flowplayer/flowplayer.min.js');
			}

			return true;
		}
		elseif ($document->getType() == 'raw')
		{
			$html = '';

			foreach ($paths[$player] as $k => $v)
			{
				foreach ($v as $url)
				{
					if ($k == 'css')
					{
						$html .= '<link href="' . $url . '" rel="stylesheet" type="text/css" />' . "\n";
					}
					elseif ($k == 'js')
					{
						$html .= "\t" . '<script src="' . $url . '" type="text/javascript"></script>' . "\n";
					}
				}
			}

			if ($player == 'flowplayer')
			{
				$html .= "\t" . '<script src="media/jui/js/jquery.js" type="text/javascript"></script>' . "\n";
				$html .= "\t" . '<script src="components/com_kinoarhiv/assets/players/flowplayer/flowplayer.min.js" type="text/javascript"></script>' . "\n";
			}

			echo $html;
		}

		return true;
	}

	/**
	 * Logger
	 *
	 * @param   string  $message  Text to log.
	 * @param   mixed   $silent   Throw exception or not. True - throw, false - not, 'ui' - show message.
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 */
	public static function eventLog($message, $silent = true)
	{
		$params = self::getParams('com_kinoarhiv');
		$uri = JURI::getInstance();
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
					echo self::showMsg(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), array('icon' => 'alert', 'type' => 'error'));

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
					echo self::showMsg(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), array('icon' => 'alert', 'type' => 'error'));

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
	 */
	public static function setLabel($for, $text, $title = '', $class = '', $attribs = array())
	{
		$title = !empty($title) ? ' title="' . JText::_($title) . '" ' : ' ';
		$class = !empty($class) ? ' class="' . $class . '" ' : ' ';
		$attrs = '';

		if (is_array($attribs) && func_num_args() == 5)
		{
			$attrs = ArrayHelper::toString($attribs);
		}

		return '<label id="' . $for . '-lbl"' . $class . 'for="' . $for . '"' . $title . $attrs . '>' . JText::_($text) . '</label>';
	}

	/**
	 * Load language files for JS scripts
	 *
	 * @param   string   $file   Part of the filename w/o language tag and extention. Filenames must follow by the
	 *                           next rules - filename[lang code].js. Leave empty if filename contain only language code.
	 *                           Example: en-US.js or select2_locale_da.js
	 * @param   string   $path   Path to the folder. Default searching in 'components/com_kinoarhiv/assets/' folder.
	 * @param   boolean  $root   Start search from root Joomla folder.
	 * @param   boolean  $jhtml  Use JHTML::script() to load. Set this to false if need to load JS into raw document.
	 *
	 * @return  void
	 */
	public static function getScriptLanguage($file, $path, $root=false, $jhtml=true)
	{
		$lang = JFactory::getLanguage()->getTag();
		$filename = $file . $lang . '.js';

		if ($root)
		{
			$basepath = JPATH_ROOT . '/' . $path . '/';
			$url = JPath::clean($path . '/', '/');
		}
		else
		{
			if (empty($path))
			{
				$path = 'js/i18n/';
			}

			$basepath = JPATH_COMPONENT . '/assets/' . $path . '/';
			$url = JPath::clean('components/com_kinoarhiv/assets/' . $path . '/', '/');
		}

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
}
