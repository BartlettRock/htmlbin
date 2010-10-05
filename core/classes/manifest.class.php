<?php
/**
 * Manifest class container page
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
 * universal manifest
 * 
 * Represents list of absolute paths of 'magic' files defined by a 
 * regular expression
 * 
 * Now update to use a singular map as opposed to a granular map
 * 
 * requires kernel.class.php for cache
 *  
 * @package core
 * @author Callan Bryant
 */
class manifest
{
	/**
	 * directories to scan for magic files
	 */
	protected $dirs;
	
	/**
	 * regular expression (definition) of 'magic files'
	 * 
	 * the first group must correspond to the 'ID' of the magic file -
	 * in this case it is just the filename
	 * 
	 * for example /\/([a-z0-9_]+)\.leaf\.[a-z0-9]+$/i would match files
	 * [id].leaf.* with id in group 1
	 * 
	 * set it with the constructor
	 */
	protected $magic_regex;
	
	/**
	 * Actual manifest
	 */
	protected $map;
	 
	 /**
	  * Cache tag to store manifest under
	  */
	protected $tag;

	
	/**
	 * Core constructor
	 * 
	 * @author Callan Bryant
	 * @param string $magic_regex see declaration of $this->magic_regex
	 * @param mixed $dirs array/string of dir(s) to search
	 */
	public function __construct($magic_regex,$dirs)
	{
		//only one dir?
		if (is_string($dirs))
			//convert to array, one element
			$dirs			= array($dirs);
		
		//check to see if there are dirs to scan
		if($dirs === array() )
			throw new Exception('no dirs provided to scan');
			
		//add dirs to scan
		$this->dirs			= &$dirs;
				
		//set the magic regex (see declaration for description)
		$this->magic_regex	= &$magic_regex;
		
		//set cache tag, salted
		$this->tag			= sha1(__FILE__.'manifest'.$magic_regex);
		
		//load the manifest into memory...
		//try to find a cached version first
		if (!$this->map = kernel::$cache->get($this->tag))
			$this->create();		
	}


	/**
	 * returns the id given a filepath of a magic file
	 * 
	 * @param $filepath string filepath
	 * @return string id
	 */
	protected function get_id($filepath)
	{
		$matches		= array();
		preg_match($this->magic_regex,$filepath,$matches);
		
		//group 1 is the ID
		return $matches[1];
	}


	/**
	 * Populates manifest entries
	 * 
	 * also returns the full manifest - useful for creating visual maps
	 * @return array the full manifest - for other purposes
	 */
	public function create()
	{
		foreach ($this->dirs as $dir)
		{
			$iterator	= new RecursiveDirectoryIterator($dir);
			$files		= new RecursiveIteratorIterator($iterator);
			
			foreach ($files as $file)
			{
			    // see if the file is 'magic'
			    if(!preg_match($this->magic_regex,$file))
					continue;
			    
			    $id		= $this->get_id($file);
			
			    if(!isset($map[$id]))
				    //add only if magic file has unique name
				    $map[$id]	= (string)$file;
			    else
					throw new Exception('Magic file collision! ID: '.$id."  \n\n".$file." collides with \n".$map[$id]."\n\n");
			}
		}
		
		//update local
		$this->map		= &$map;
		
		//save to cache
		kernel::$cache->set($this->tag,$map);
	}
	
	/**
	 * returns a map of the entire manifest - id to file
	 * this is not the recommended way to read the manifest, as paths 
	 * are not verified
	 * @return array manifest
	 */
	 public function dump()
	 {
		return $this->map;
	 }

	/**
	 * returns the path to a magic file
	 * @param string magic filename
	 * @return string filepath
	 */
	public function get_path($id)
	{
		//try to get (cached) path
		$path	=& $this->map[$id];
		
		//file does not exist, either recently deleted/moved or never existed
		if (!file_exists($path))
		{
		
			//rebuild the manifest in the hope that the file is new or moved
			$this->create();
			
			//..try again
			@$path	=& $this->map[$id];
			
			if (!$path)
				//must not exist...
				throw new Exception('Magic file with id: "'.strip_tags($id).'" does not exist');
		}
		elseif (!is_readable($path))
			throw new Exception($path.' is not accessible. Check permissions.');
		else
			return $path;
	}
	
	/**
	 * allows overloading so manifest appears to have set vars
	 * 
	 * basically just an alias for get_path()
	 * 
	 * @param string id of file to get path to
	 * @return string corresponding path
	 */
	public function __get($id)
	{
		return $this->get_path($id);
	}

}

?>
