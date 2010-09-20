<?php
/**
 * static function library for creating and viewing htmlbin pages
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
 *
 * @package main
 * @author Callan Bryant <callan1990@googlemail.com>
 */

/**
 * for now, just relies on cache. File support will be worked upon.
 */
class page
{
	/**
	 * returns page ID given source
	 * @param string $source HTML source code
	 * @return string ID of newly created page
	 */
	public static function id(&$source)
	{
		// return the last 5 characters of a random salted sha1 hash
		// this conforms to ID regex		
		return substr( sha1(__FILE__.$source) , -HASH_LENGTH);
	}

	/**
	 * echoes a page with an ID
	 * @param string $id of existing page
	 */
	public static function view($id)
	{
		// must set a variable rather than nest the function - otherwise
		// the Exception thrown in the path function won't get caught.....
		$valid_path	= self::path($id);

		$s 	= new http();
		$s->load_local_file($valid_path);
	}

	/**
	 * creates a page given the source, returns the ID for that page
	 * @param string $source HTML source code
	 * @return string ID of newly created page
	 */
	public static function create(&$source)
	{
		$id		= self::id($source);

		$file	= self::path($id);

		file_put_contents($file,$source);
		return $id;
	}

	/**
	 * removes all pages
	 */
	public static function flush()
	{
		foreach (glob(PAGES_DIR.'*.*') as $file )
			unlink ($file);
	}

	/**
	 * removes all pages that have not been accessed in MAX_AGE in seconds
	 */
	public static function purge()
	{
	}

	/**
	 * returns the path used to save the actual file given the ID
	 * @param string $id of a page
	 * @return string tag to save or cache the page with
	 */
	public static function path(&$id)
	{
		// valid ID is  HASH_LENGTH  hex chars
		$regex	= '/^[a-f0-9]{'.HASH_LENGTH.'}$/';

		// check the ID is valid first
		if (!preg_match ($regex,$id))
			throw new Exception('invalid ID given');

		return PAGES_DIR.'/'.$id.'.html';
	}
}


?>
