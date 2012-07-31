<?php
namespace phplinter {
	class Config {
		/* @var int */
		protected $_flags = 0;
		/* @var array */
		protected $_options = array();
		/***/
		protected $_rules;
		/**
		----------------------------------------------------------------------+
		* FIXME
		* @param	string	Filename
		----------------------------------------------------------------------+
		*/
		public function __construct($filename=null) {
			$conf = null;
			if($filename) {
				if(file_exists($filename)) {
					$conf = json_decode(file_get_contents($filename), true);
					if(empty($conf)) {
						echo 'Unable to parse input file';
						switch (json_last_error()) {
							case JSON_ERROR_NONE:
								echo ' - No errors';
								break;
							case JSON_ERROR_DEPTH:
								echo ' - Maximum stack depth exceeded';
								break;
							case JSON_ERROR_STATE_MISMATCH:
								echo ' - Underflow or the modes mismatch';
								break;
							case JSON_ERROR_CTRL_CHAR:
								echo ' - Unexpected control character found';
								break;
							case JSON_ERROR_SYNTAX:
								echo ' - Syntax error, malformed JSON';
								break;
							case JSON_ERROR_UTF8:
								echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
								break;
							default:
								echo ' - Unknown error';
							break;
						}
						die("\n");
					}
				} else {
					die("Config-file '$filename' not found or not readable...\n");
				}
			}
			$this->_parse($conf);
		}
		/**
		----------------------------------------------------------------------+
		* FIXME
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
		* FIXME
		----------------------------------------------------------------------+
		*/
		public function setFlags($flags) {
			$this->_flags |= $flags;
			if($this->_flags & (OPT_VERBOSE | OPT_DEBUG)) {
				$this->_flags &= ~OPT_QUIET;
			}
		}
		/**
		----------------------------------------------------------------------+
		* FIXME
		----------------------------------------------------------------------+
		*/
		public function setOptions($opt) {
			$this->_options = array_merge($this->_options, $opt);
		}
		/**
		----------------------------------------------------------------------+
		* FIXME
		* @param	FIXME
		* @return   FIXME
		----------------------------------------------------------------------+
		*/
		public function match_rule($rule, $test) {
			$rule = $this->_rules[$rule];
			if(isset($rule['compare'])) {
				if(isset($rule['type'])) {
					switch($rule['type']) {
						case 'assoc': return in_array($test, array_keys($rule['compare']));
						case 'array': return in_array($test, $rule['compare']);
						case 'regex': return !preg_match($rule['compare'], $test);
					}
				}
				return ($test > $rule['compare']);
			}
			return false;
		}
		/**
		----------------------------------------------------------------------+
		* FIXME
		* @param	FIXME
		* @return   FIXME
		----------------------------------------------------------------------+
		*/
		public function ruleByFlag($flag) {
			foreach($this->_rules as $rule => $_) {
				if($_['flag'] === $flag) return $rule;
			}
			return null;
		}
		/**
		----------------------------------------------------------------------+
		* FIXME
		* @param	FIXME
		* @return   FIXME
		----------------------------------------------------------------------+
		*/
		public function setRule($rule, $data) {
			if(array_key_exists($rule, $this->_rules)) {
				$this->_rules[$rule] = array_merge($this->_rules[$rule], $data);
			}
		}
		/**
		----------------------------------------------------------------------+
		* FIXME
		* @param	FIXME
		* @return   FIXME
		----------------------------------------------------------------------+
		*/
		public function getRule($rule) {
			return $this->_rules[$rule];
		}
		/**
		----------------------------------------------------------------------+
		* @desc 	Report flag
		* @param	String	Flag
		* @return	Bool
		----------------------------------------------------------------------+
		*/
		public function report_on($flag) {
			if($this->check(OPT_ONLY_SECURITY)) {
				if($flag[0] == 'S' || in_array($flag, array('I2','I3')))
				return true;
				return false;
			}
			switch($flag[0]) {
				case 'C':
					return (!($this->check(OPT_NO_CONVENTION)));
				case 'W':
					return (!($this->check(OPT_NO_WARNING)));
				case 'R':
					return (!($this->check(OPT_NO_REFACTOR)));
				case 'E':
					return (!($this->check(OPT_NO_ERROR)));
				case 'I':
					return (!($this->check(OPT_NO_INFORMATION)));
				case 'D':
					return (!($this->check(OPT_NO_DEPRICATED)));
				case 'S':
					return (!($this->check(OPT_NO_SECURITY)));
				case 'F':
					return (!($this->check(OPT_NO_FORMATTING)));
			}
		}
		/**
		----------------------------------------------------------------------+
		* FIXME
		----------------------------------------------------------------------+
		*/
		protected function _parse($conf) {
			if($conf) {
				foreach(array(
					'target',
					'ignore',
					'extensions',
					'memory_limit',
					'skip_rules',
					'use_rules',
					'custom_rules',
				) as $_) 
				{
					if(isset($conf[$_]) && $conf[$_])
						$this->_options[$_] = $conf[$_];
				}
				foreach(array(
					'score_only' 	=> OPT_SCORE_ONLY,
					'quiet' 		=> OPT_QUIET
				) as $k => $_) 
				{
					if(isset($conf[$k]) && $conf[$k])
						$this->_flags |= $_;
				}
				if(isset($conf['information']))
					$this->_flags &= ~OPT_NO_INFORMATION;
				$this->_parse_filter($conf);
				$this->_parse_report($conf);
			}
			$this->_parse_rules($conf);
		}
		/**
		----------------------------------------------------------------------+
		* FIXME
		* @param	FIXME
		* @return   FIXME
		----------------------------------------------------------------------+
		*/
		protected function _parse_rules($conf) {
			$this->_rules = require dirname(__FILE__) . '/../rules/rules.php';
			if($r = $this->check('use_rules')) {
				foreach($this->_rules as &$_) {
					$_['used'] = in_array($_['flag'], $r);
				}
				unset($_);
			} elseif($r = $this->check('skip_rules')) {
				foreach($this->_rules as &$_) {
					if(in_array($_['flag'], $r)) {
						$_['used'] = false;
					}
				}
				unset($_);
			}
			if($r = $this->check('custom_rules')) {
				foreach($r as $or => $cmp) {
					foreach($this->_rules as &$_) {
						if($_['flag'] === $or) {
							$_['compare'] = $cmp;
						}
					}
				}
			}
		}
		/**
		----------------------------------------------------------------------+
		* FIXME
		----------------------------------------------------------------------+
		*/
		protected function _parse_filter($conf) {
			if(isset($conf['filter'])) {
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
					if(in_array($k, $conf['filter'])) {
						$this->_flags |= $_;
					}
				}
			}
		}
		/**
		----------------------------------------------------------------------+
		* FIXME
		----------------------------------------------------------------------+
		*/
		protected function _parse_report($conf) {
			if(isset($conf['report'])) {
				if(isset($conf['report']['type'])) {
					$this->_options['report'] = (array)$conf['report'];
					switch($conf['report']['type']) {
						case 'html':
							$this->_flags |= OPT_HTML_REPORT;
							break;
						case 'json':
							$this->_flags |= OPT_JSON_REPORT;
							break;
						default:
							exit("Unknown report type `{$conf['report']['type']}`\n");
					}
					if(!isset($conf['report']['out'])) {
						exit("Report needs `out` field\n");
					}
					$outdir = dirname($conf['report']['out']);
					if(!file_exists($outdir)) {
						exit("Report out: `$outdir` not found\n");
					}
				}
			}
			if(isset($conf['harvest'])) {
				$this->_flags |= OPT_HARVEST_DOCS;
				$this->_options['harvest'] = $conf['harvest'];
			}
		}
	}
}