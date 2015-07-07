<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

use Joomla\Utilities\ArrayHelper;

/**
 * Class GlobalHelper
 */
class GlobalHelper
{
	/**
	 * Include some necessary JS into the HEAD of the document. Don't include if document format is not a html.
	 * @return  void
	 */
	static function setHeadTags()
	{
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		if ($document->getType() != 'html') {
			return;
		}

		JHtml::_('jquery.framework');
		JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery-ui.min.js');
		if ($params->get('vegas_enable') == 1) {
			JHtml::_('script', 'components/com_kinoarhiv/assets/js/jquery.vegas.min.js');
		}

		$document->addHeadLink(JURI::base() . 'components/com_kinoarhiv/assets/themes/ui/' . $params->get('ui_theme') . '/jquery-ui.css', 'stylesheet', 'rel', array('type' => 'text/css'));
		$document->addHeadLink(JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/css/plugin.css', 'stylesheet', 'rel', array('type' => 'text/css'));
		$document->addHeadLink(JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/css/style.css', 'stylesheet', 'rel', array('type' => 'text/css'));

		if ($app->input->get('view', '', 'cmd') == 'movie') {
			$document->addHeadLink(JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/css/editor.css', 'stylesheet', 'rel', array('type' => 'text/css'));
		}
	}

	/**
	 * Return html structure for message. jQueryUI stylesheets required.
	 *
	 * @param   string  $text  Text for display.
	 * @param   array   $extra Array of optional elements. $extra['icon'] - the icon type; $extra['type'] - the type of
	 *                         message. Can be 'highlight', 'error', 'disabled'.
	 * @param   boolean $close Show close link.
	 *
	 * @return  string
	 */
	static function showMsg($text, $extra = array(), $close = false)
	{
		$icon = !isset($extra['icon']) ? 'info' : $extra['icon'];
		$type = !isset($extra['type']) ? 'highlight' : $extra['type'];
		if ($close) {
			$close_str = ' <a href="" class="ui-icon ui-icon-close" style="display: inline-block;" onclick="jQuery(this).closest(\'.ui-message\').remove(); return false;"></a>';
		} else {
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
	 * @param   string $text  Text for clean.
	 * @param   string $tags  String with allowed tags and their attributes.
	 * @param   array  $extra Addtitional parameters for HTMLPurifier.
	 *
	 * @return  string
	 */
	static function cleanHTML($text, $tags = '', $extra = array())
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$cache_path = JPATH_CACHE . DIRECTORY_SEPARATOR . 'kinoarhiv' . DIRECTORY_SEPARATOR . 'DefinitionCache' . DIRECTORY_SEPARATOR . 'Serializer';

		require_once(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'htmlpurifier' . DIRECTORY_SEPARATOR . 'HTMLPurifier.standalone.php');

		$purifier_config = HTMLPurifier_Config::createDefault();
		if (!file_exists($cache_path)) {
			if (!mkdir($cache_path, 0777, true)) {
				self::eventLog('Failed to create definition cache folder at path: "' . $cache_path . '"');
			}
		}
		$purifier_config->set('Cache.SerializerPath', $cache_path);

		if (empty($tags)) {
			$tags = $params->get('html_allowed_tags');
		}
		$purifier_config->set('HTML.Allowed', $tags);

		if (count($extra) > 0) {
			foreach ($extra as $key => $value) {
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
	 * @param   string $player Player type.
	 * @param   string $key    License key.
	 *
	 * @return  mixed
	 */
	static function loadPlayerAssets($player, $key = '')
	{
		$document = JFactory::getDocument();

		$paths = array(
			'flowplayer'   => array(
				'css' => array(
					'components/com_kinoarhiv/assets/players/flowplayer/skin/all-skins.css'
				),
				'js'  => array()
			),
			'jwplayer'     => array(
				'js' => array(
					'components/com_kinoarhiv/assets/players/jwplayer/jwplayer.js'
				)
			),
			'mediaelement' => array(
				'css' => array(
					'components/com_kinoarhiv/assets/players/mediaelement/mediaelementplayer.css'
				),
				'js'  => array()
			),
			'videojs'      => array(
				'css' => array(
					'components/com_kinoarhiv/assets/players/videojs/video-js.css'
				),
				'js'  => array(
					'components/com_kinoarhiv/assets/players/videojs/video.js'
				)
			)
		);

		if ($document->getType() == 'html') {
			foreach ($paths[$player] as $k => $v) {
				foreach ($v as $url) {
					if ($k == 'css') {
						$document->addHeadLink($url, 'stylesheet', 'rel', array('type' => 'text/css'));
					} elseif ($k == 'js') {
						$document->addScript($url);
					}
				}
			}

			if ($player == 'jwplayer') {
				$document->addScriptDeclaration("jwplayer.key='" . $key . "';");
			} elseif ($player == 'videojs') {
				$document->addScriptDeclaration("videojs.options.flash.swf = '" . JURI::base() . "components/com_kinoarhiv/assets/players/videojs/video-js.swf';");
			} elseif ($player == 'mediaelement') {
				JHtml::script('components/com_kinoarhiv/assets/players/mediaelement/mediaelement-and-player.min.js');
			} elseif ($player == 'flowplayer') {
				JHtml::script('components/com_kinoarhiv/assets/players/flowplayer/flowplayer.min.js');
			}

			return true;
		} elseif ($document->getType() == 'raw') {
			$html = '';

			foreach ($paths[$player] as $k => $v) {
				foreach ($v as $url) {
					if ($k == 'css') {
						$html .= '<link href="' . $url . '" rel="stylesheet" type="text/css" />' . "\n";
					} elseif ($k == 'js') {
						$html .= "\t" . '<script src="' . $url . '" type="text/javascript"></script>' . "\n";

						if ($player == 'jwplayer') {
							$html .= "\t" . '<script type="text/javascript">jwplayer.key="' . $key . '";</script>' . "\n";
						}
					}
				}
			}

			if ($player == 'flowplayer') {
				$html .= "\t" . '<script src="media/jui/js/jquery.js" type="text/javascript"></script>' . "\n";
				$html .= "\t" . '<script src="components/com_kinoarhiv/assets/players/flowplayer/flowplayer.min.js" type="text/javascript"></script>' . "\n";
			}

			echo $html;
		}

		return true;
	}

	/**
	 * Load CSS and Javascript for HTML5 editor

	 */
	static function loadEditorAssets()
	{
		$document = JFactory::getDocument();

		$document->addScript(JURI::base() . 'components/com_kinoarhiv/assets/js/editor.rules.advanced.min.js');
		$document->addScript(JURI::base() . 'components/com_kinoarhiv/assets/js/editor.min.js');
	}

	/**
	 * Logger
	 *
	 * @param   string $message Text to log.
	 * @param   mixed  $silent  Throw exception error or not. True - throw, false - not, 'ui' - show message.
	 *
	 * @throws  Exception
	 */
	static function eventLog($message, $silent = true)
	{
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$uri = JURI::getInstance();
		$user = JFactory::getUser();

		$message = $message . "\t" . $uri->current() . '?' . $uri->getQuery();

		if ($params->get('logger') == 'syslog') {
			$backtrace = debug_backtrace();
			$stack = '';

			for ($i = 0, $n = count($backtrace); $i < $n; $i++) {
				$trace = $backtrace[$i];
				$class = isset($trace['class']) ? $trace['class'] : '';
				$type = isset($trace['type']) ? $trace['type'] : '';
				$stack .= "#" . $i . " " . $trace['file'] . "#" . $trace['line'] . " " . $class . $type . $trace['function'] . "\n";
			}

			openlog('com_kinoarhiv_log', LOG_PID, LOG_DAEMON);
			syslog(LOG_CRIT, $message . "\nBacktrace:\n" . $stack);
			closelog();

			if (!$silent || is_string($silent)) {
				if ($silent == 'ui') {
					echo self::showMsg(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), array('icon' => 'alert', 'type' => 'error'));

					if ($user->get('isRoot')) {
						echo '<pre>' . $message . '</pre>';
					}
				} else {
					throw new Exception($message, 500);
				}
			}
		} else {
			jimport('joomla.log.log');

			JLog::addLogger(
				array(
					'text_file' => 'com_kinoarhiv.errors.php'
				),
				JLog::ALL, 'com_kinoarhiv'
			);

			JLog::add($message, JLog::WARNING, 'com_kinoarhiv');

			if (!$silent || is_string($silent)) {
				if ($silent == 'ui') {
					echo self::showMsg(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), array('icon' => 'alert', 'type' => 'error'));

					if ($user->get('isRoot')) {
						echo '<pre>' . $message . '</pre>';
					}
				} else {
					throw new Exception($message, 500);
				}
			}
		}
	}

	/**
	 * Wrapper for JApplicationWeb::redirect() to use in the views
	 *
	 * @param   string $url         The URL to redirect to. Can only be http/https URL
	 * @param   string $message     The message to enqueue.
	 * @param   string $messageType The message type. Default is message.
	 *
	 * @return  mixed     False if url is empty, void otherwise
	 */
	static function doRedirect($url = null, $message = null, $messageType = 'message')
	{
		if (!is_null($url)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage($message, $messageType);
			$app->redirect($url);
		}

		return false;
	}

	/**
	 * Create a custom label html tag
	 *
	 * @param   string $for     Input ID
	 * @param   string $text    Label text.
	 * @param   string $title   Label title.
	 * @param   string $class   CSS classname(s).
	 * @param   array  $attribs Additional HTML attributes
	 *
	 * @return   string
	 */
	static function setLabel($for, $text, $title = '', $class = '', $attribs = array())
	{
		$title = !empty($title) ? ' title="' . JText::_($title) . '"' : '';
		$class = !empty($class) ? ' class="' . $class . '"' : '';

		$attrs = '';
		if (is_array($attribs) && func_num_args() == 5) {
			$attrs = ArrayHelper::toString($attribs);
		}

		return '<label id="' . $for . '-lbl"' . $class . ' for="' . $for . '"' . $title . $attrs . '>' . JText::_($text) . '</label>';
	}

	/**
	 * Load language files for JS scripts
	 *
	 * @param   string $file        Part of the filename w/o language tag and extention
	 * @param   string $jhtml       Use JHTML::script() to load
	 * @param   string $script_type Type of the script(folder name in assets/js/i8n/)
	 */
	public static function getScriptLanguage($file, $jhtml, $script_type)
	{
		$lang = JFactory::getLanguage()->getTag();
		$filename = $file . $lang . '.js';
		$basepath = JPATH_COMPONENT . DIRECTORY_SEPARATOR;
		$url = JURI::base();

		if (file_exists($basepath . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . $script_type . DIRECTORY_SEPARATOR . $filename)) {
			if ($jhtml) {
				JHtml::_('script', $url . 'components/com_kinoarhiv/assets/js/i18n/' . $script_type . '/' . $filename);
			} else {
				echo '<script src="' . $url . 'components/com_kinoarhiv/assets/js/i18n/' . $script_type . '/' . $filename . '" type="text/javascript"></script>';
			}
		} elseif (file_exists($basepath . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . $script_type . DIRECTORY_SEPARATOR . $file . substr($lang, 0, 2) . '.js')) {
			if ($jhtml) {
				JHtml::_('script', $url . 'components/com_kinoarhiv/assets/js/i18n/' . $script_type . '/' . $file . substr($lang, 0, 2) . '.js');
			} else {
				echo '<script src="' . $url . 'components/com_kinoarhiv/assets/js/i18n/' . $script_type . '/' . $file . substr($lang, 0, 2) . '.js" type="text/javascript"></script>';
			}
		}
	}
}
