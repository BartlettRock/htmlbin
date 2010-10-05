<?php
/**
 * null cache class page
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

//example way to set default cache dir eg:
//define('CACHE_DIR','/tmp/cache');

/**
 * null cache - does not cache, always returns a cache miss
 * Useful for debugging. SLOW!
 * 
 * Provides a means to store/retrieve temporary variables, arrays
 * or objects; all are refered to as objects in this context.
 * 
 * @package core
 * @author Callan Bryant
 */
class null_cache implements cache
{
	/**
	 * loads a previously saved object by $tag
	 * @param $tag string identifier for object
	 * @return null - cache miss
	 */
	public function get($tag)
	{
		return null;
	}
	
	/**
	 * saved a previously loaded object by $tag
	 * @param $tag string identifier for object
	 */
	public function set($tag,$object,$expiry = 0)
	{
	}
		
	/**
	 * cache object deleter
	 * 
	 * @param $tag
	 */
	public function delete($tag)
	{
		return true;
	}
	
	/**
	 * cache flusher
	 * Flushes _all_ cache objects
	 */
	public function flush()
	{
	}
}
