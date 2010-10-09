<?php
/**
 * page for all constants
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
 * all constants are absolute
 * 
 * @package core
 * @author Callan Bryant <callan1990@googlemail.com>
 */

//paths - all absolute!
/**
 * root path of leafworks
 */
define('ROOT_DIR', dirname( dirname(__FILE__) ).'/');

/**
 * Var path
 */
define('VAR_DIR',ROOT_DIR.'var/');

/**
 * path to core files
 */
define('CORE_DIR',ROOT_DIR.'core/');

/**
 * Cache path
 */
define('CACHE_DIR',ROOT_DIR.'var/cache/');

/**
 * store path
 */
define('STORE_DIR',ROOT_DIR.'var/store/');

/**
 * app root path alias
 */
define('APP_DIR',ROOT_DIR.'app/');

/**
 * app root path alias
 */
define('PUBLIC_DIR',APP_DIR.'public/');

/**
 * path to live config file
 */
define('LIVE_CONFIG_FILE',VAR_DIR.'live.config.php');

/**
 * path to default config file
 */
define('DEFAULT_CONFIG_FILE',APP_DIR.'default.config.php');

//manifest regexes
/**
 * leaf files regular expression
 */
define('LEAF_REGEX','/([\-a-z0-9._ ]+)\.leaf\.[a-z0-9]+$/i');

/**
 * magic class files regular expression
 */
define('CLASS_REGEX','/([a-z0-9_]+)\.(class|interface)\.php$/i');


/**
 * Time (unix+microtime) of start, to time renders if required
 */
define('START_TIME',microtime(true));

/**
 * used on command line flag (for security purposes)
 */
define('CLI_MODE',defined('STDIN'));
?>
