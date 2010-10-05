<?php
/**
 * RAM cache class page
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


// this cannot be in the constructor as the class requires the extension
// check to see if php5-memcache is installed (extension)
if (!extension_loaded('memcache'))
	throw new Exception
		('php memcache extension not loaded. On ubuntu/debian, install the package php5-memcache');

/**
 * RAM cache class
 * 
 * uses memory, memcached
 * 
 * Provides a means to store/retrieve temporary variables, arrays
 * or objects; all are refered to as objects in this context.
 * 
 * @package core
 * @author Callan Bryant
 */
class memcached_cache extends Memcache implements cache
{
	/**
	 * path to cache folder (rel. to root)
	 */
	protected $cache_dir;
	  
	/**
	 * Core constructor
	 * 
	 * @author Callan Bryant
	 * @param $cache_dir dir for cache
	 */
	public function __construct($ip)
	{
		$this->connect($ip);
	}
	
	/**
	 * calls parent set with correct param order
	 * 
	 * @param $tag string identifier for object
	 * @param $object mixed var to save
	 * @param $expiry int time is seconds
	 */
	public function set($tag,$object,$expiry = 0)
	{
		parent::set($tag,$object,false,$expiry);
	}
	
	/**
	 * calls parent get with correct param order
	 * 
	 * @param $tag string identifier for object
	 * @param $object mixed var to save
	 * @param $expiry int time is seconds
	 */
	public function get($tag)
	{
		return parent::get($tag,false);
	}
	
	/**
	 * calls parent delete with correct param order
	 * 
	 * @param $tag string identifier for object
	 * @param $object mixed var to save
	 * @param $expiry int time is seconds
	 */
	public function delete($tag)
	{
		parent::delete($tag,0);
	}
}
