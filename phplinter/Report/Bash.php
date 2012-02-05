<?php
namespace phplinter\Report;
class Bash extends Base {
	/**
	----------------------------------------------------------------------+
	* @desc 	FIXME
	----------------------------------------------------------------------+
	*/
	public function create($report, $penaltys=null, $root=null) {
		$format="| {F} | {M} | `{W}` Line: {L}\n";
		$fcolors = array(
			'E' => 'red', 'W' => 'blue', 'C' =>'brown', 'D' => array(2, 'brown'),
			'I' => 'green', 'R' => 'purple', 'S' => 'cyan', 'F' => array(1, 'black')
		);
		foreach($report as $_) {
			$out = str_replace('{F}', $this->color(str_pad($_['flag'], 3), 
							   $fcolors[$_['flag'][0]]), $format);
			$out = str_replace('{M}', str_pad($_['message'], 50), $out);
			$out = str_replace('{W}', $_['where'], $out);
			$out = str_replace('{L}', $_['line'], $out);
			echo $out;
		}
		if($penaltys) {
			echo $this->score($penaltys);
		}
	}
	/**
	----------------------------------------------------------------------+
	* @desc 	Color format for bash shell.
	* 			colors = black/red/green/brown
	* 					 blue/purple/cyan/white
	* 			attrs = 1-8
	* @param	String
	* @param	String
	* @param	Mixed		color OR array(attr, color)
	* @param	Bool
	* @param	Int
	* @return 	String
	----------------------------------------------------------------------+
	*/
	public function color($msg, $color="black", $nl=false) {
		if($this->config->check(OPT_USE_COLOR)) {
			$attr = 0;
			if(is_array($color)) {
				$attr = intval($color[0]);
				$color = $color[1];
			}
			$tpl = "\033[%d;%dm%s\033[0m";
			if($nl) $tpl .= "\n";
			$codes = array(
					'black' => 30, 'red' => 31, 'green' => 32,
					'brown' => 33, 'blue' => 34, 'purple' => 35,
					'cyan' => 36, 'white' => 37
			);
			if(!isset($codes[$color])) $color = 'black';
			return sprintf($tpl, $attr, $codes[$color], $msg);
		}
		return $msg;
	}
}