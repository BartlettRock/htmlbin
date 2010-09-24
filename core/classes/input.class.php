<?php
/**
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
 * 
 * @package core
 * @author Callan Bryant <callan1990@googlemail.com>
 */

/**
 * input class
 * 
 * Provides an easy and safe interface to both URL query arguments and
 * command line arguments.
 * 
 * CLI args in the format: php bleh.php --param value
 * 
 * Will automatically get the arguments from applicable
 * 
 * Also, allows interactive mode CLI input (STDIN) via scan()
 * 
 * @package core
 * @author Callan Bryant
 */
class input
{
	/**
	 * returns arguments in an array
	 * (CLI/URL mode supported)
	 * 
	 * @return array of arguments
	 */
	public static function all() 
	{
		//CLI mode?
		if (defined('STDIN'))
		{
			//get the single (long) option from the command line
			return self::cli();
		}
		//assume web mode...
		elseif(!get_magic_quotes_gpc())
			// inputs are NOT dirty (escaped ' etc)
			return $_REQUEST;
		else
		{
			//inputs are dirty, slashes need to be removed
			foreach ($_REQUEST as &$var)
				$var	= stripslashes($var);

			return $_REQUEST;
		}
	}
	
	/**
	 * gets a single variable from the URL or CLI
	 * 
	 * @param string $variable name of parameter
	 * @return string contents
	 */
	public function arg($variable)
	{
		$args	= self::all();
		
		if(isset($args[$variable]))
			return $args[$variable];
	}
	
	
	/**
	 * returns cli args from cli in the format:
	 * --param value --param2 value2
	 * (long format, currently no whitespace allowed in values)
	 * 
	 * @return array of cli args
	 */
	public static function cli()
	{
		//remove the filename from the 'query'
		$raw	= $_SERVER['argv'];
		array_shift($raw);
		
		//get the args, convert into string
		$query	= implode(' ',$raw);
		
		//get param/value pairs
		$pairs	= explode('--',$query);
		
		//kick off the empty string
		//this will prevent an offset error
		array_shift($pairs);
		
		$args	= array();
		
		//iterate over the pairs to fetch values
		foreach ($pairs as $pair)
		{
			list ($param,$value)	= explode(' ',$pair);
			
			//...add to args
			$args[$param]			= $value;
		}
		
		return $args;
	}
	
	/**
	 * simply returns the query string (for completeness)
	 * from CLI or URL
	 * 
	 * @return query string
	 */
	public function query()
	{
		//check for CLI mode...
		if(defined('STDIN'))
		{
			//CLI mode
			//get the args, convert into string
			$params		= $_SERVER['argv'];
			//take of the first element (filename)
			array_shift($params);
			//stitch it back together, so it is one string again
			return implode(' ',$params);
		}
		else	
			//web mode (has to be urldecoded, only get an request are)
			return urldecode($_SERVER['QUERY_STRING']); 
	}
	
	/**
	 * for cli mode, scans in a line when enter is pressed from STDIN
	 * Output is trimmed, so no newline.
	 * 
	 * @return string input
	 */
	public static function scan()
	{
		if (!CLI_MODE)
			throw new Exception('scan() is for CLI mode only');
		return trim(fgets(STDIN));
	}
	
	/**
	 * saves a single uploaded file to a location
	 * file must be POSTed.
	 * 
	 * @param string $path full file path including name
	 * @return int filesize
	 */
	public static function save_uploaded_file($path)
	{
		//get the single file info array, no matter the name
		$info	= reset($_FILES);
		
		if (!$info)
			throw new Exception('No file POSTed');
		
		//check for errors (switch: no need to break, Exception does that)
		// read http://www.php.net/manual/en/features.file-upload.common-pitfalls.php
		switch ($info['error'])
		{
			case UPLOAD_ERR_OK:
				//no error
				break;
			case UPLOAD_ERR_INI_SIZE:
				throw new Exception('The uploaded file exceeds the upload_max_filesize directive in php.ini');
			case UPLOAD_ERR_FORM_SIZE:
				throw new Exception('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form');
			case UPLOAD_ERR_PARTIAL:
				throw new Exception('The uploaded file was only partially uploaded');
			case UPLOAD_ERR_NO_FILE:
				throw new Exception('No file was uploaded');
			case UPLOAD_ERR_NO_TMP_DIR:
				throw new Exception('Missing a temporary folder');
			case UPLOAD_ERR_CANT_WRITE:
				throw new Exception('Failed to write file to disk');
			case UPLOAD_ERR_EXTENSION:
				throw new Exception('A PHP extension stopped the file upload');
			
		}
				
		//copy file
		if (!@move_uploaded_file($info['tmp_name'],$path))
			throw new Exception('Could not move uploaded file');
		
		return $info['size'];
	}

	/**
	 * returns a request header
	 * 
	 * @param string $name request header
	 * @return string contents
	 */
	public function header($name)
	{
		// for some reason, this is only supported as an apache module...
		// if this changes in the future, then it can be implemented here
		// transparently
		
		// convert the header name into php's silly superglobal $_SERVER 
		// array format
		$name	= str_replace('-','_',$name);
		$name	= strtoupper($name);
		$name	= 'HTTP_'.$name;

		if (isset($_SERVER[$name]))
			return $_SERVER[$name];
		else
			return false;
	}
}
