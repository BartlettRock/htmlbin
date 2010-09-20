<?php
/**
 * disk cache class page
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
 * disk cache class
 * 
 * uses disk
 * 
 * Provides a means to store/retrieve temporary variables, arrays
 * or objects; all are refered to as objects in this context.
 * 
 * @package core
 * @author Callan Bryant
 */
class disk_cache implements cache
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
	public function __construct($cache_dir = CACHE_DIR)
	{
		
		//set the super cache dir (forcing directory)
		$this->cache_dir	= $cache_dir.'/';
		
		//make sure cache dir is valid
		if ( !file_exists($this->cache_dir) )
			if (! @mkdir($this->cache_dir) )
				throw new Exception('Could not create cache dir');
	}

	/**
	 * returns file path for $tag
	 * @param $tag string tag of object
	 * @return string corresponding path of object
	 */
	protected function get_path($tag)
	{
		//sanitise $tag, give it an id - with salt so that if voswork
		//is put into a different dir, the manifest won't screw up
		$id		= sha1(__FILE__.$tag);
		
		//corresponding path:
		return $this->cache_dir.$id.'.tmp';
	}

	/**
	 * loads a previously saved object by $tag
	 * @param $tag string identifier for object
	 * @return mixed said object
	 */
	public function get($tag)
	{
		//file path to save to
		$path	= $this->get_path($tag);
		
		//check if cache entry is there
		if (!file_exists($path) )
			//failure, cache miss
			return null;
		

		//returns false if failed for any other reason
		$result	= @file_get_contents($path);
		
		if ($result ===false )//failed
			throw new Exception('failed to read cache for existing '.$tag);
		
		//convert back
		$array			= unserialize($result);
		
		$expiry			= &$array['expiry'];
		$object			= &$array['object'];
		
		//check expiry time
		//false = no expiry
		if ($expiry <= time() and $expiry !=false)
		{
			//cache miss, collect garbage
			delete($tag);
			return null;
		}
		//cache hit
		return $object;
	}
	
	/**
	 * saved a previously loaded object by $tag
	 * @param $tag string identifier for object
	 */
	public function set($tag,$object,$expiry = 0)
	{
		$path			= $this->get_path($tag);
		
		//convert to a serial object with metadata
		$array['object']= &$object;
		
		if ($expiry != 0)
			$array['expiry']= $expiry+time();
		else
			//object does not expire
			$array['expiry']= false;
		
		$serial			= serialize($array);
		
		//free mem
		unset($array);
		
		if (strlen($tag) > 250)
			throw new Exception('TAG must be under 250 chars!');
			
		if (strlen($serial) > 1024*1024)
			throw new Exception('Object must be significantly less than 1MB! It is a cache, not a filestore!');
				
		if (!is_numeric($expiry))
			throw new Exception('Expiry must be an integer');
			
		$result	= @file_put_contents($path,$serial, LOCK_EX);
		//returns false if failed
			
		if ( $result ===false )//failed
			throw new Exception('failed to save object '.$tag);
	}
		
	/**
	 * cache object deleter
	 * 
	 * @param $tag
	 */
	public function delete($tag)
	{
		$path	= $this->get_path($tag);
		
		//check if cache entry is there
		if (!file_exists($path) )
			//failure
			return false;
	
		//returns false if failed for any other reason
		$result	= @unlink($path);
			
		if ( $result ===false )//failed
			throw new Exception('failed to delete existing object '.$tag);
		return true;
	}
	
	/**
	 * cache garbage collector
	 *
	 * purges very old cache objects
	 * can be run by destructor (by chance)
	 * @param $max_age integer age in seconds (default 10 days)
	 */
	public function collect_garbage($max_age = 846000)
	{
		//calculate the max. timestamp for said age
		$min_stamp		= time()-$max_age;
			
		$handle		= @opendir($this->cache_dir);
		if ($handle === false)
			throw new Exception('Could not read cache dir for gc');

		while (($filename	= readdir($handle)) !== false)
		{
			$filepath		= $this->cache_dir.$filename;
				
			if (filemtime($filepath) <= $min_stamp)
				//too old
				if (strpos($filename,'.tmp') === 40)
					//...file is cache object, 40 chars then .tmp extension, (fast check)
					if (! @unlink($filepath) )
						throw new Exception('Error deleting cache object(s)');
		}
		closedir($handle);		
	}
	
	/**
	 * cache flusher
	 * Flushes _all_ cache objects
	 */
	public function flush()
	{
		$this->collect_garbage(0);
	}
	
	/**
	 * destructor - rarely runs garbage collector
	 * 
	 * (in destructor for performance reasons - after output)
	 */
	public function __destruct()
	{
		//non-zero value to run on average every...
		$run_every	= 100;
		
		//percent chance to run
		$chance		= 1/$run_every;
		
		if(rand() <= $chance)
			$this->collect_garbage();

	}
}
