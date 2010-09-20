<?php
/**
 * standard interface
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
 * this script is included by public/index.php for web output, and may 
 * be called directly for cli use
 * 
 * keeping the file here allows voswork to be upgraded just by swapping 
 * out core/
 * 
 * this script serves as the primary interface for 'magic' 'leaf' files
 * the syntax for the web interface is: 
 *		/index.php?parameter=value&anotherparameter2=another value
 * for example:
 *		/index.php.php?leaf=feed&key=432432df32
 * 
 * the syntax if called by the command line is:
 * 		php boostrap.php --parameter value anotherparameter value
 * for example:
 * 		php bootstrap.php --leaf feed
 * 
 * note, that when using the apache webserver, a call to the following
 * would be valid:
 * 		/?leaf=feed.rss&key=432432df32
 * 
 * Note, the query has to be url_encoded
 *
 * If the script is called without a leaf, it will load the 'default'
 * leaf (default.leaf.php)
 * 
 * @author Callan Bryant <callan1990@googlemail.com>
 * @package core
 */

//load constants for kernel and applications
require_once 'constants.php';

//load the main interface to core
require_once CORE_DIR.'classes/kernel.class.php';

//load the appropiate configuration
kernel::load_config();

//set the fallback exception handler
kernel::set_exception_handler();

//set the error reporting level, according to VERBOSE
kernel::set_error_reporting();

//initialise the best cache to kernel::$cache
kernel::init_cache();

//load the manifests
kernel::load_manifests();

//set up magic class file autoloading
kernel::define_autoloader();
?>
