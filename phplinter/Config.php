<?php
namespace phplinter;
class Config {
	protected $_flags = 0;
	protected $_options = array();
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function __construct($filename) {
		if(file_exists($filename)) {
			$conf = json_decode(file_get_contents($filename));
			if(empty($conf)) {
				die("Failed to parse '$filename', bad json...\n");
			}
		} else {
			die("Config-file '$filename' not found or not readable...\n");
		}
		$this->_parse($conf);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function check($opt) {
		if(is_numeric($opt)) {
			return $this->_flags & $opt;
		}
		if(isset($this->_options[$opt])) {
			return $this->_options[$opt];
		}
		return null;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function setFlags($flags) {
		$this->_flags |= $flags;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function setOptions($opt) {
		$this->_options = array_merge($this->_options, $opt);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	protected function _parse($conf) {
		foreach(array(
			'target',
			'verbose'
		) as $_) 
		{
			if($conf->$_)
				$this->_options[$_] = $conf->$_;
		}
		foreach(array(
			'verbose' => OPT_VERBOSE
		) as $k => $_) 
		{
			if($conf->$k)
				$this->_flags |= $_;
		}
	}
}