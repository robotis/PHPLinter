<?php
namespace phplinter\Report;
class JSON extends Base {
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function prepare() {
		$this->json = $this->config->check('json');
		if(isset($this->json['dry_run']))
			$this->dry_run = $this->json['dry_run'];
		return true;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function create($report, $penaltys=null, $root=null) {
		echo "JSON::create\n";
	}
}
