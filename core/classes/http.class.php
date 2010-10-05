<?php
/**
 * Special server page - http
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
 * @package kernel
 * @author Callan Bryant <callan1990@googlemail.com>
 */

/**
 * http server class
 * 
 * Allows voswork to serve files other than normal script output.
 * For example, It can be used to download images, CSS or javascript - or more 
 * importantly, dynamically served files, possibly with authentication
 * It makes use of caching and acts as a normal webserver.
 *
 * Internal attributes must be set up. The destructor will commit and serve.
 * For local files, use load_local_file() to set sensible attibutes.
 * They can be overidden after, if necessary.
 * 
 * @todo http resume
 * @package kernel
 * @final
 * @see core.class.php
 * @author Callan Bryant
 */
class http
{
	
	public $handle,$mimetype,$name,$size,$etag;
	public $status		= null;
	public $position	= 0;
	
	// inline or attachment (browser view or force download)
	public $inline		= false;

	// should the client keep the file indefinitey?
	// if the file never changes, tell the client by setting this!
	public $persistent	= false;

	/**
	 * constructor
	 */
	public function __construct()
	{
		// filestats are cached, which may cause problems
		clearstatcache();
	}
	
	/**
	 * (magic)
	 * verfy the attributes given when they are set
	 * 
	 * @param $attr string attribute to set
	 */
	public function __set($attr,$value)
	{
		switch ($attr)
		{
			case 'handle':
				//no test...?
			break;
			
			case 'mimetype':
				if (!is_string($attr))
			 		throw new Exception('mimetype must be a string');
			break;
			
			case 'name':
				if (!is_string($attr))
			 		throw new Exception('name must be a string');
			break;
			
			case 'size':
				if (!is_int($attr))
			 		throw new Exception('size must be an integer');
			break;
			
			case 'etag':
				if (!is_string($attr))
			 		throw new Exception('etag must be a string');
			break;
			
			case 'status':
				if (!is_int($attr))
			 		throw new Exception('status must be a HTTP response code (integer)');
			break;
			
			case 'position':
				if (!is_int($attr))
			 		throw new Exception('name must be an integer');
			break;
		}
		
		// set it, it passed the tests
		$this->$attr	= $value;
	}
	
	/**
	 * Sets and checks all attributes to send later on destructor
	 * @param $filepath string local path of file to serve
	 */
	public function load_local_file($path)
	{
		if (!file_exists($path))
		{
			$this->status	= 404;
			return;
		}

		if (!is_readable($path))
		{
			$this->status	= 403;
			return;
		}

		// get file extension from path
		// automatically generate it
		$extension		= self::extension($path);
		
		// set appropiate mimetype header
		$this->mimetype	= $this->mimetype($extension);

		// guess based on extension wether the file should be inline or an attachment
		// can be changed after file is loaded
		if ($extension =='html' or $extension =='txt')
			$this->inline	= true;
		else
			$this->inline	= false;
		
		// set an appropiate name
		$this->name		= basename($path);
		
		// allow the browser to calculate file progress
		$this->size		= filesize($path);
		// sprintf: show correct file size over 2GB (convert to unsigned)
		$this->size		= sprintf( "%u",$this->size);
		
		
		// get Etags for 304 handling
		$current_etag	= self::etag($path);
		$client_etag	=& input::header('If-None-Match');
		
		// see if the client already has a current version of the file
		if ( $current_etag === $client_etag )
		{
			//client has same file
			//tell the client to use cache
			$this->status= 304;
			return;
		}
		
		// give the client an Etag for the file
		header('Etag: '.$current_etag);	

		// try to open a handle
		$this->handle		= @fopen($path, 'rb');
		if ($this->handle === false)
			throw new Exception('could not open file');

		$this->status		= 200;
	}

	
	/**
	 * returns the extension of a file
	 */
	public static function extension($file)
	{
		$matches		= array();
		preg_match('/\.([[:alnum:]]+)$/',$file,$matches);
		return  $matches[1];
	}

	/**
	 * returns the mimetype given a file extension
	 * @param $ext string file extension
	 * @return string mime type
	 */
	 public static function mimetype($extension)
	 {
		 switch($extension)
		 {
			case 'js' : return 'text/javascript';				break;
			case 'html':return 'text/html';						break;
			case 'mp3':	return 'audio/x-mp3';					break;
			case 'flv':	return 'video/x-flv';					break;
			case 'ogg':	return 'audio/ogg';						break;
			case 'ogv':	return 'video/ogg';						break;
			case 'css':	return 'text/css';						break;
			case '7z' :	return 'application/x-7z-compressed';	break;
			case 'exe':	return 'application/octet-stream';		break;
			case 'zip':	return 'application/zip';				break;
			case 'mp4':	return 'video/mp4';						break;
			case 'pdf':	return 'application/pdf';				break;
			case 'txt':	return 'text/plain';					break;
			case 'php':	return 'text/plain';					break;
			case 'doc':	return 'application/msword';			break;
			case 'xls':	return 'application/vnd.ms-excel';		break;
			case 'ppt':	return 'application/vnd.ms-powerpoint';	break;
			case 'gif':	return 'image/gif';						break;
			case 'png':	return 'image/png';						break;
			case 'jpg':	return 'image/jpeg';						break;
			//if the ext is not recognised, force the browser to download it as a binary file
			default:	return 'application/octet-stream';
		}		 
	 }
	 	
	/**
	 * echos http status code page
	 * 
	 * (200, 404, 403, 304 supported)
	 * @param integer status code
	 */
	public static function status($code)
	{
		header('Content-Type: text/plain');
		switch($code)
		{
			case 200:
				$code	= '200 OK';
			break;
			case 304:
				$code	= '304 Not Modified';
			break;
			case 404:
				$code	= '404 Not Found';
			break;
			case 403:
				$code	= '403 Forbidden';
			break;
			case 301:
				$code	= '301 Moved Permanently';
			break;

			// unknown default to 500 to at least give something relevant
			default:
			case 500:
				$code	= '500 Internal Server Error';
			break;
		}
		header('HTTP/1.1 '.$code);
		echo($code."\n");
	}
	 
	/**
	 * main server method - outputs a file by the handle, without headers
	 * 
	 * low memory usage
	 * 
	 * @param string handle of file
	 */
	protected function read()
	{
		// stop all output buffering, to decrease memory usage and increase speed
		while (@ob_end_flush());
		
		// set the position of the file pointer (for HTTP resume)
		if (@fseek($this->handle,$this->position) !== 0 )
			throw new Exception('Seek/handle error');
		
		// loop through 4K at a time
		while (!feof($this->handle))
		{
			$chunk		= fread($this->handle, 4096);
			echo($chunk);
			// reset watchdog so script doesn't time out with slow connections
			set_time_limit(30);
		}

		fclose($this->handle);

		// flushes second buffer
		//ob_end_flush();
	}
	  
	/**
	 * returns an Etag for a file - that changes if modified
	 * 
	 * is fast (does not hash the file itself)
	 * @param $file string filepath
	 * @return string Etag
	 */
	public static function etag($filepath)
	{
		$mtime		= filemtime($filepath);
		$size		= filesize($filepath);
		$inode		= fileinode($filepath);
	 
		// hash them together with the file path
		return sha1($mtime.$size.$filepath);
	}
	  
	/**
	 * commit - send the file with the object's attributes
	 * only for when all attributes are valid, and there is a handle!
	 */
	 protected function commit()
	 {
		// it is important to unlock the session data to allow other
		// pages to use it and hence be able to load whilst the data
		// is being served by this class
		session_write_close();		

		if (isset($this->mimetype)) 	
			header('Content-type:'.$this->mimetype);
	 	
	 	// if html or text, it must be displayed inline
		if ($this->inline)
			header('Content-Disposition: inline;');		
		else		
			//tell the browser the filename and to download it as an attachment			
			header('Content-Disposition: attachment; filename="' . $this->name. '"');
		
		
		if (isset($this->size))
			// this breaks compatability with gz handling
			header('Content-Length: '.$this->size);

		// if the file never changes, tell the client!!
		$this->dictate_cache();

		// Etag
		if (isset($this->etag))
			header('Etag: '.$this->etag);
		
		// serve the actual file
		$this->read();
	 	
	 }

	/**
	 * redirects a relative or absolute url
	 * 
	 * @param $url string abs. or rel. url to redirect to
	 */
	public static function redirect($url)
	{
		if (strpos($url,'http://') !== 0)
			// relative URL (must be converted)
			$url	= 'http://'.$_SERVER['HTTP_HOST'].'/'.$url;

	

		// must provide a full absolute URL
		header('Location: '.$url);
		self::status(301);
	}

	/**
	 * tells the client to cache or not (none or ~infinite) based on 
	 * $this->persistent flag
	 */
	protected function dictate_cache()
	{
		if ($this->persistent)
		{
			// cache it client side for about 3 years, effectively ~infinite!
			header('Cache-Control: public, maxage=99999999');

			// depreciated old way for HTTP/1.0 (absolute, therefore flawed)	
			header('Expires: '.date('D, d M Y H:i:s', (time()+99999999)).' GMT');
		}
		else
		{
			// make it explicitly non-cachable
			header('Cache-Control: no-cache,must-revalidate');

			// depreciated old way for HTTP/1.0 (absolute, therefore flawed)
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		}
	}

	/**
	 * decide what to do based on the status code
	 */
	public function __destruct()
	{
		// does not work with cgi
		//if (headers_sent() )
			//throw new Exception('headers already sent, incorrect usage');
		
		switch($this->status)
		{
			case 200:
				// actually serve the file based on attributes
				// (the idea is to try to avoid this where possible for speed!)
				$this->commit();
			break;

			case 304:
				// if the persistent option is enabled after the first transfer
				// the client will only receive 304s and therefore will need to 
				// told as well! (not just on 200)
				$this->dictate_cache();
				$this->status(304);
			break;

			case null:
				throw new Exception('(HTTP) $this->status must be set');
			break;

			// catch all
			default:
				self::status($this->status);
		}
			
	}
}
?>
