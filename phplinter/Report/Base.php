<?php
namespace phplinter\Report;
abstract class Base {
	protected $data;
	protected $config;
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