<?php
/**
 * Main DEFAULT configuration file
 * 
 *     htmlbin Copyright (C) 2010  Callan Bryant <callan1990@googlemail.com>
 * 
 *     Based on Voswork - A simple, fast PHP filesystem abstraction layer
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
 * @autor Callan Bryant <callan1990@googlemail.com>
 * @package core
 */

// dir to store pages in (defaults to var/store/)
define('PAGES_DIR',STORE_DIR);

// maximum time to keep an unused page (default is 2 months)
define('MAX_AGE',2419200);

// page id length (increase for better collision immunity)
// with ~10000 articles, at 6 there is a ~1/1677 chance of a collision.
// each increment reduces the chance by ~16x. The maximum is 40.
define('HASH_LENGTH',6);

// verbose debug mode - will show exceptions if true
// useful to quieten for production use
define('VERBOSE',true);

// Cache actor
// Choose a type of global cache from the following options (ordered 
// by speed):
// * memcached - extremely fast - config below required
// * mysql (not implemented yet) - config below required
// * sqlite (not implemented yet) - config below required
// * disk - no setup required!
// * null - fake cache, effectively disabling the global cache. SLOW, for debugging only.
define ('CACHE'	,'disk');

// MEMCACHED CONFIG - only applicable if CACHE is memcached
// the php extension needs to be installed (sudo apt-get install php5-memcache)
// and the webserver needs to be reloaded
//define('MEMCACHED_IP',	false);


?>
