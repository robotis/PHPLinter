<?php
namespace phplinter;
class Config {
	/* @var int */
	protected $_flags = 0;
	/* @var array */
	protected $_options = array();
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function __construct($filename=null) {
		if($filename) {
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
			'score_only' 	=> OPT_SCORE_ONLY,
			'quiet' 		=> OPT_QUIET
		) as $k => $_) 
		{
			if(isset($conf->$k) && $conf->$k)
				$this->_flags |= $_;
		}
		if(isset($conf->information))
			$this->_flags &= ~OPT_NO_INFORMATION;
		
		$this->_parse_filter($conf);
		$this->_parse_report($conf);
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	protected function _parse_filter($conf) {
		if(isset($conf->filter)) {
			foreach(array(
				"information" 	=> OPT_NO_INFORMATION,
				"conventions" 	=> OPT_NO_CONVENTION,
				"warnings" 		=> OPT_NO_WARNING,
				"refactor" 		=> OPT_NO_REFACTOR,
				"errors" 		=> OPT_NO_ERROR,
				"documentation" => OPT_NO_DEPRICATED,
				"security" 		=> OPT_NO_SECURITY
			) as $k => $_)
			{
				if(in_array($k, $conf->filter)) {
					$this->_flags |= $_;
				}
			}
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	protected function _parse_report($conf) {
		if(isset($conf->report)) {
			if(isset($conf->report->html)) {
				$this->_flags |= OPT_HTML_REPORT;
				$this->_options['html'] = (array)$conf->report->html;
			}
			if(isset($conf->report->json)) {
				$this->_flags |= OPT_JSON_REPORT;
				$this->_options['json'] = (array)$conf->report->json;
			}
		}
	}
}