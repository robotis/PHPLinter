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
			'ignore',
			'extensions'
		) as $_) 
		{
			if($conf->$_)
				$this->_options[$_] = $conf->$_;
		}
		foreach(array(
			'verbose' => OPT_VERBOSE,
			'score_only' => OPT_SCORE_ONLY
		) as $k => $_) 
		{
			if(isset($conf->$k) && $conf->$k)
				$this->_flags |= $_;
		}
		if(isset($conf->filter)) {
			foreach(array(
				"information" => OPT_NO_INFORMATION,
				"conventions" => OPT_NO_CONVENTION,
				"warnings" => OPT_NO_WARNING,
				"refactor" => OPT_NO_REFACTOR,
				"errors" => OPT_NO_ERROR,
				"documentation" => OPT_NO_DEPRICATED,
				"security" => OPT_NO_SECURITY
			) as $k => $_) 
			{
				if(in_array($k, $conf->filter)) {
					$this->_flags |= $_;
				}
			}
		}
	}
}