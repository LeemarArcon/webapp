<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 * 
 * Copyright (c) 2020 Ronald M. Marasigan
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @copyright Copyright 2020 (https://ronmarasigan.github.io)
 * @version Version 1
 * @link https://lavalust.pinoywap.org
 * @license https://opensource.org/licenses/MIT MIT License
 */

if ( ! function_exists('load_class'))
{
	/**
	 * Class Loader to load all classes
	 * @param  string $class
	 * @param  string $directory Class directory
	 * @param  array $params    Class parameters if present
	 * @return void
	 */
	function &load_class($class, $directory = '', $params = NULL) {

		$LAVA = Registry::get_instance();
		$className = ucfirst(strtolower($class));

		if($LAVA->getObject($className) != NULL) {
			$object = $LAVA->getObject($className);
			return $object;
		}

		foreach (array(APP_DIR, SYSTEM_DIR) as $path)
    	{
			$fullPathName = $path . $directory  . DIRECTORY_SEPARATOR . $className . '.php';
					
			if (file_exists($fullPathName)) {
				if( ! class_exists($className, FALSE)) {
					require_once $fullPathName;
				}
			}
		}
		
		is_loaded($class);
		$LAVA->storeObject($className, isset($params) ? new $className($params) : new $className());
		$object = $LAVA->getObject($className);
		return $object;
	}
}

// --------------------------------------------------------------------

if ( ! function_exists('is_loaded'))
{
	/**
	 * Keeps track of which libraries have been loaded. This function is
	 * called by the load_class() function above
	 *
	 * @param	string
	 * @return	array
	 */
	function &is_loaded($class = '')
	{
		static $_is_loaded = array();

		if ($class !== '')
		{
			$_is_loaded[$class] = ucfirst(strtolower($class));
		}

		return $_is_loaded;
	}
}

if ( ! function_exists('show_404'))
{
	/**
	 * 404 Error Not Found
	 * @param  string $heading
	 * @param  string $message
	 * @param  string $page
	 * @return string
	 */
	function show_404($heading = NULL, $message = NULL, $page = NULL)
	{
		$errors =& load_class('Errors', 'core');
		return $errors->show_404($heading, $message, $page);
	}
}

if ( ! function_exists('show_error'))
{
	/**
	 * Show error for debugging
	 * @param  string $heading
	 * @param  string $message
	 * @param  string $error_code
	 * @return string
	 */
	function show_error($heading, $message, $template, $error_code)
	{
	  	$errors =& load_class('Errors', 'core');
	  	return $errors->show_error($heading, $message, $template = 'error_general', $error_code);
	}
}

if ( ! function_exists('_shutdown_handler'))
{
	/**
	 * For Debugging
	 * @return string
	 */
	function _shutdown_handler()
	{
		$last_error = error_get_last();
		if (isset($last_error) &&
			($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING)))
		{
			_error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}
}

if ( ! function_exists('_exception_handler'))
{
	/**
	 * For Debgging
	 * @param  string $e
	 * @return string
	 */
	function _exception_handler($e)
	{
		$exception =& load_class('Errors', 'core');
		$exception->show_exception($e);
	}
}

if ( ! function_exists('_error_handler'))
{
	/**
	 * For Debugging
	 * @param  string $errno
	 * @param  string $errstr
	 * @param  string $errfile
	 * @param  string $errline
	 * @return string
	 */
	function _error_handler($errno, $errstr, $errfile, $errline)
	{
		$error =& load_class('Errors', 'core');
		$error->show_php_error($errno, $errstr, $errfile, $errline);
	}
}

if ( ! function_exists('get_config'))
{
	/*
	 * ------------------------------------------------------
	 * Loads the main config.php file
	 * ------------------------------------------------------
	 */
	function &get_config()
	{
		static $config;

		if ( file_exists(APP_DIR . 'config/config.php') ) 
		{
			require_once APP_DIR . 'config/config.php';

			if ( isset($config) OR is_array($config) ) 
			{
				foreach( $config as $key => $val ) 
				{
					$config[$key] = $val;
				}

				return $config;
			}
		} else
			show_404('404 Not Found', 'The configuration file does not exist');
	}
}

if ( ! function_exists('config_item'))
{
	/*
	 * ------------------------------------------------------
	 * Config Item
	 * ------------------------------------------------------
	 */
	function config_item($item)
	{
		static $_config;

		if (empty($_config))
		{
			// references cannot be directly assigned to static variables, so we use an array
			$_config[0] =& get_config();
		}

		return isset($_config[0][$item]) ? $_config[0][$item] : NULL;
	}
}

if ( ! function_exists('autoload_config'))
{
	/*
	 * ------------------------------------------------------
	 * Loads the main autolaod.php file
	 * This is for autoloading of libraries, models, and helpers file
	 * ------------------------------------------------------
	 */
	function &autoload_config()
	{
		static $autoload;

		if ( file_exists(APP_DIR . 'config/autoload.php') ) 
		{
			require_once APP_DIR . 'config/autoload.php';

			if ( isset($autoload)  OR is_array($autoload) ) 
			{
				foreach( $autoload as $key => $val ) 
				{
					$autoload[$key] = $val;
				}

				return $autoload;
			}
		} else
			show_404('404 Not Found', 'The configuration file does not exist');
	}
}

if ( ! function_exists('database_config'))
{
	/*
	 * ------------------------------------------------------
	 * Loads the main database.php file
	 * Note: This will be used commonly inside Model file
	 * in the core folder
	 * ------------------------------------------------------
	 */
	function &database_config()
	{
		static $database;

		if ( file_exists(APP_DIR . 'config/database.php') ) 
		{
			require_once APP_DIR . 'config/database.php';

			if ( isset($database)  OR is_array($database) )
			{
				foreach( $database as $key => $val ) 
				{
					$database[$key] = $val;
				}

				return $database;
			}
		} else
			show_404('404 Not Found', 'The configuration file does not exist');
	}
}

if ( ! function_exists('route_config'))
{
	/*
	 * ------------------------------------------------------
	 * Loads the main routes.php file
	 * ------------------------------------------------------
	 */
	function &route_config()
	{
		static $route;

		if ( file_exists(APP_DIR . 'config/routes.php') ) 
		{
			require_once APP_DIR . 'config/routes.php';

			if ( isset($route)  OR is_array($route) )
			{
				foreach( $route as $key => $val ) 
				{
					$route[$key] = $val;
				}

				return $route;
			}
		} else
			show_404('404 Not Found', 'The configuration file does not exist');
	}
}


if ( ! function_exists('get_mimes'))
{
	/**
	 * Returns the MIME types array from config/mimes.php
	 *
	 * @return	array
	 */
	function &get_mimes()
	{
		static $_mimes;

		if (empty($_mimes))
		{
			$_mimes = file_exists(APP_DIR.'config/mimes.php')
				? include(APP_DIR.'config/mimes.php')
				: array();

			if (file_exists(APP_DIR.'config/mimes.php'))
			{
				$_mimes = array_merge($_mimes, include(APP_DIR.'config/mimes.php'));
			}
		}

		return $_mimes;
	}
}

if ( ! function_exists('html_escape'))
{
	/**
	 * Returns HTML escaped variable.
	 *
	 * @param	mixed	$var		The input string or array of strings to be escaped.
	 * @param	bool	$double_encode	$double_encode set to FALSE prevents escaping twice.
	 * @return	mixed			The escaped string or array of strings as a result.
	 */
	function html_escape($var, $double_encode = TRUE)
	{
		if (empty($var))
		{
			return $var;
		}

		if (is_array($var))
		{
			foreach (array_keys($var) as $key)
			{
				$var[$key] = html_escape($var[$key], $double_encode);
			}

			return $var;
		}

		return htmlspecialchars($var, ENT_QUOTES, config_item('charset'), $double_encode);
	}
}