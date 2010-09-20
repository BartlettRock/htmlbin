<?php
/**
 * main function library class container page
 *
 *     Voswork - A simple, fast PHP filesystem abstraction layer
 *     Voswork Copyright (C) 2010  Callan Bryant <callan1990@googlemail.com>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package core
 * @author Callan Bryant <callan1990@googlemail.com>
 */

/**
 * main static (general) function library
 * the interface to core
 * agnostic of autoloader!
 * 
 * low level, static on purpose. This is so there only can be one 'instance'
 * and the kernel can be called in different scripts without passing round
 * an object.
 *
 * @static
 * @package core
 * @author Callan Bryant
 */
class kernel
{
	//last leaf loaded, changed by load_leaf
	public static $leaf;
	
	//an instance of the best cache available - use kernel::init_cache()
	//to instantiate it.
	public static $cache;
	
	//manifests for leaf and class loading
	public static $leaves;
	public static $classes;

	
	/**
	 * creates leaf and class manifests. Must be called before loading such.
	 */
	public static function load_manifests()
	{
		require_once(CORE_DIR.'classes/manifest.class.php');

		//instantiate a new manifest matching leaves in the application dir
		self::$leaves	= new manifest(LEAF_REGEX,APP_DIR);

		//load a new manifest matching class files in correct dirs
		self::$classes	= new manifest(CLASS_REGEX,array(CORE_DIR,APP_DIR));
	}
	
	/**
	 * Loads the leaf file given in arguments
	 *
	 * if $leaf is manually specified, it will be loaded instead
	 *
	 * may be used by a leaf to load different leaves conditionally - for
	 * example to load a different default interface based on User-Agent.
	 *
	 * @param $leaf string leaf name to load
	 * @return path of leaf
	 */
	public static function load_leaf($leaf = 'default')
	{
		if (headers_sent())
				throw new Exception('Leaves can only be loaded before headers have been sent!');


		//find corresponding path
		$path		= self::$leaves->$leaf;

		//determine file type
		$ext		= end(explode('.',$path));

		//update the current (last loaded) leaf name
		self::$leaf	= $leaf;

		if ($ext === 'php')
		{
			//file is a dynamic php script, execute it
			//load the leaf in the correct directory
			chdir( dirname($path) );
			require_once $path;
		}
		else
		{
			//file is static, let http class handle it
			$s			= new http();
			//also overide filename
			$s->load_local_file($path);
			//override name
			$s->name	= $leaf.'.'.$ext;
		}
	}

	/**
	 * loads (by include) the main configuration for application
	 *
	 * trys var/config.php, if that does not exist, copies defualt
	 * configuration to said path, and loads it
	 *
	 */
	public static function load_config()
	{
		if (!file_exists(LIVE_CONFIG_FILE))
			//live might not exist
			if (file_exists(DEFAULT_CONFIG_FILE) )
			{
    				// default does, create new live
				copy(DEFAULT_CONFIG_FILE,LIVE_CONFIG_FILE);
				header('Content-Type: text/plain');
				echo 'First run - config file created: '.LIVE_CONFIG_FILE."\n";
				echo "Please edit this file, then refresh\n";
				// application must not run on default config
				die();
			}
			else
				//default does not exist so config not used
				return false;

		require_once LIVE_CONFIG_FILE;
		return true;
	}

	/**
	 * sets the best cache avaliable conforming to 'cache' interface
	 * 
	 * sets tp kernel::$cache; - all voswork apps must use this
	 * to avoid opening more than one socket and to automatically have the
	 * best one
	 * 
	 * @return $cache object
	 */
	public static function init_cache()
	{
		require_once CORE_DIR.'classes/caches/cache.interface.php';

		//if no config file was used, there will be an undefined constant error
		//this solves it.
		if (!defined('CACHE'))
			define('CACHE',		'disk');

		switch (CACHE)
		{
			case 'memcached':			
				require_once CORE_DIR.'classes/caches/memcached_cache.class.php';
				self::$cache		= new memcached_cache(MEMCACHED_IP);
			break;
			
			case 'disk':	
				require_once CORE_DIR.'classes/caches/disk_cache.class.php';
				self::$cache		= new disk_cache(CACHE_DIR);
			break;
			
			/* NOT IMPLEMENTED YET
			case 'mysql':	
				require_once CORE_DIR.'classes/caches/mysql_cache.class.php';
				self::$cache		= new mysql_cache(...);
			break;

			case 'sqlite':	
				require_once CORE_DIR.'classes/caches/sqlite_cache.class.php';
				self::$cache		= new sqlite_cache(...);
			break;
			*/

			case 'null':
			// sort out problem of config ambiguity
			case null:
				require_once CORE_DIR.'classes/caches/null_cache.class.php';
				self::$cache		= new null_cache();
			break;

			default:
				throw new Exception('Invalid cache type');
		}

	}

	/**
	 * registers the autoloader that uses the manifest (hence cache)
	 * for bootstrap
	 */
	public static function define_autoloader()
	{
		/**
		 * magic autoloader
		 *
		 * not to be called explicitly
		 *
		 * Note: Exceptions thrown in __autoload function cannot be caught
		 * in the catch block and results in a fatal error.
		 *
		 * @package core
		 * @author Callan Bryant
		 * @param string $class the class that needs to be loaded
		 */
		function __autoload($class)
		{
			require kernel::$classes->$class;
		}
	}

	/**
	 * gracefully catches an exception, displaying output if
	 * VERBOSE is true
	 *
	 * @param object $e exception to be caught
	 */
	public static function handle_exception($e)
	{
			//make the browser use a monospace font
			@header('Content-type:text/plain');

			if (VERBOSE or CLI_MODE)
			{
				echo "VOSWORK DEFAULT EXCEPTION\n\n";
				echo $e->getMessage()."\n\n";
				echo 'Line: '.$e->GetLine()."\n";
				echo 'File: '.$e->GetFile()."\n\n";
				echo 'Type: '.get_class($e)."\n\n\n";
				echo $e->GetTraceAsString()."\n";
			}else
				echo 'ERROR: Please contact an administrator';
			die();
	}

	/**
	 * sets the default exception handler for uncaught exceptions
	 * automatically catches them.
	 *
	 * think of it as defining failsafe exception handler.
	 */
	public static function set_exception_handler()
	{
		set_exception_handler(array('kernel','handle_exception'));
	}

	/**
	 * sets the error preporting level accouding to VERBOSE
	 */
	public static function set_error_reporting()
	{
		//will cause PHP NOTICE level error if config file is not used
		//this will fix it
		if(!defined('VERBOSE'))
			define('VERBOSE',true);

		if(!VERBOSE)
			error_reporting(0);
	}

	/**
	 * gets the (rough) execution time up to the point called in
	 * milliseconds as an integer 
	 *
	 * @return float time in milliseconds
	 */
	public static function elapsed()
	{
		return ceil((microtime(true) - START_TIME)*1000);
	}
}
?>
