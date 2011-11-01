<?php
/**
----------------------------------------------------------------------+
*  @desc			Path functions.
*  @file 			Path.php
*  @author 			Jóhann T. Maríusson <jtm@hi.is>
*  @since 		    May 17, 2010
*  @copyright     
*    phplinter is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
----------------------------------------------------------------------+
*/
namespace PHPLinter;
class Path {
	/**
	----------------------------------------------------------------------+
	* @desc 	Find all files under $directory that match $match and not
	* 			$ignore, and call $func with the filename as arg.
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$directory	String	root_path
	* @param	$func		String	user function
	* @param	$match		String	REGEX
	* @param	$ignore		String	REGEX
	* @since  	Mar 18, 2009
	----------------------------------------------------------------------+
	*/
	public static function scan($directory, $func, 
		$match, $ignore='/^(\..*|CVS)$/') {
		$files = self::find($directory, $match, $ignore);
		foreach($files as $_)
			call_user_func($func, $_);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Find all files under $directory that match $match and not
	* 			$ignore.
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$directory	String
	* @param	$match		String	REGEX
	* @param	$ignore		String	REGEX
	* @return	Array
	----------------------------------------------------------------------+
	*/
	public function find($directory, $match, $ignore=null) {
		ob_start();
		system('find ' . $directory . ' -type f');
		$files = explode("\n", ob_get_clean());
		foreach($files as $file) {
			if(isset($ignore) && preg_match($ignore, $file))
				continue;
			if(preg_match($match, $file))
				$out[] = $file;
		}
		return isset($out) ? $out : null;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Delete directory with all contents, OR all files that match
	* 			$root
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$root	String
	----------------------------------------------------------------------+
	*/
	public static function del_recursive($root) {
		$root = realpath($root);
		if(empty($root) || !file_exists($root)) 
			return false;
		$files = glob( $root . '/*', GLOB_MARK ); 
		foreach( $files as $file ){
	        if(is_dir( $file )) {
	            self::del_recursive( $file );
	        } else {
	            unlink( $file );
	        }
	    }
//	    if(is_dir($root)) {
//	    	rmdir( $root ); 
//	    }
	    return true;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Write to file.
	* @author 	Jóhann T. Maríusson <jtm@hi.is>
	* @param	$file		String
	* @param	$content	String
	* @param	$mode		String	[r,r+,w,w+,a,a+,x,x+][bt]
	* @param	$perm		Oct
	* @return	Bool
	----------------------------------------------------------------------+
	*/
	public static function write_file($file, $content, $mode='w', $perm=0664) {
		if($fp = fopen($file, $mode)) {
			fwrite($fp, $content);
			fclose($fp);	
			chmod($file, $perm);
			return true;
		}
		return false;
	}
}