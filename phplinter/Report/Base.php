<?php
/**
----------------------------------------------------------------------+
*  @desc			Base Reporter
*  @file 			Base.php
*  @author 			Jóhann T. Maríusson <jtm@hi.is>
*  @since 		    Feb 6, 2012
*  @package 		PHPLinter
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
----------------------------------------------------------------------+
*/
namespace phplinter\Report;
abstract class Base {
	/** @var Config Object */
	protected $config;
	/** @var bool */
	protected $dry_run;
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function __construct(\phplinter\Config $config) {
		$this->config = $config;
		$this->dry_run = false;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Derive final score for one file
	* @param	$penalty	Float
	----------------------------------------------------------------------+
	*/
	public function score($penalty, $num=null) {
		if($penalty === false)
			return "No score available\n";
		$full = (is_numeric($num))
					? ($num * SCORE_FULL) 
					: SCORE_FULL;
		return sprintf("Score: %.2f out of %.2f\n",
						($full + $penalty), $full);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Derive final score for one file
	* @param	$penalty	Float
	----------------------------------------------------------------------+
	*/
	public function average($penalty, $num) {
		$full = $num * SCORE_FULL;
		return sprintf("Average score: %.2f\n",
						(($full + $penalty) / $num));
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function write($filename, $content) {
		if(!$this->dry_run) {
			if(!\phplinter\Path::write_file($filename, $content)) {
				die("Unable to create '$filename'...");
			}
		}
		if($this->config->check(OPT_VERBOSE))
			echo "Wrote to file `$filename`\n";
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function mkdir($path, $oct=0775, $rec=false) {
		if(!$this->dry_run) {
			if(!mkdir($path, $oct, $rec)) {
				die("Unable to create '$path'...");
			}
			if($this->config->check(OPT_VERBOSE))
				echo "Created directory `$path`\n";
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public abstract function create($report, $penaltys=null, $root=null);
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function prepare() { return true; }
}