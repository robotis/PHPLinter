<?php
namespace phplinter\Report;
abstract class Base {
	protected $data;
	protected $options;
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function set($data, $options) {
		$this->data = $data;
		$this->options = $options;
		return $this;
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public abstract function create();
}